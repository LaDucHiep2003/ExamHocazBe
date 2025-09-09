<?php
include_once __DIR__ . '/../Model/BaseModel.php';
class ResultModel extends BaseModel
{
    protected $table;
    protected $ResultModel;
    protected $conn;

    public function __construct()
    {
        $this->table = 'results';
        $this->conn = ConnectionDB::GetConnect();
        $this->ResultModel = new BaseModel($this->table);
    }

    public function index($sql = null)
    {
        return $this->ResultModel->index();
    }

    public function detail($id)
    {
        try {
            // Lấy thông tin attempt
            $attemptQuery = $this->conn->prepare("
                SELECT ea.*, e.totalQuestions, e.name, ea.end_time, e.maxScore, e.passingScore, TIMESTAMPDIFF(MINUTE, ea.start_time, ea.end_time) as durationMinutes
                FROM exam_attempts ea
                INNER JOIN exams e ON ea.exam_id = e.id
                WHERE ea.id = :attempt_id
            ");
            $attemptQuery->execute(['attempt_id' => $id]);
            $attempt = $attemptQuery->fetch(PDO::FETCH_ASSOC);

            if (!$attempt) {
                return null;
            }

            // Lấy danh sách câu trả lời
            $answersQuery = $this->conn->prepare("
                SELECT question_id, option_id, is_correct
                FROM exam_answers
                WHERE attempt_id = :attempt_id
            ");
            $answersQuery->execute(['attempt_id' => $id]);
            $answers = $answersQuery->fetchAll(PDO::FETCH_ASSOC);

            $correctCount = 0;
            $wrongCount = 0;
            $answeredQuestions = [];

            foreach ($answers as $ans) {
                $answeredQuestions[] = $ans['question_id'];
                if ($ans['is_correct'] == 1) {
                    $correctCount++;
                } else {
                    $wrongCount++;
                }
            }

            // Tính số câu bỏ trống
            $emptyCount = $attempt['totalQuestions'] - count(array_unique($answeredQuestions));

            return [
                'exam_id'         => $attempt['exam_id'],
                'exam_name'       => $attempt['name'],
                'user_id'         => $attempt['user_id'],
                'correctCount'    => $correctCount,
                'wrongCount'      => $wrongCount,
                'emptyCount'      => $emptyCount,
                'end_time'        => $attempt['end_time'],
                'durationMinutes' => $attempt['durationMinutes'],
                'score'           => $attempt['score'],
                'passingScore'    => $attempt['passingScore'],
                'max_score'       => $attempt['maxScore'],
                'status'          => $attempt['status']
            ];

        } catch (Throwable $e) {
            return null;
        }
    }

    public function getQuestion($id)
    {
        try {
            // Lấy danh sách câu hỏi trong bài thi attempt này
            $questionsQuery = $this->conn->prepare("
            SELECT q.id, q.content
            FROM exam_answers ea
            INNER JOIN questions q ON ea.question_id = q.id
            WHERE ea.attempt_id = :attempt_id
            GROUP BY q.id, q.content
        ");
            $questionsQuery->execute(['attempt_id' => $id]);
            $questions = $questionsQuery->fetchAll(PDO::FETCH_ASSOC);

            // Lấy tất cả đáp án user đã chọn
            $answersQuery = $this->conn->prepare("
            SELECT question_id, option_id
            FROM exam_answers
            WHERE attempt_id = :attempt_id
        ");
            $answersQuery->execute(['attempt_id' => $id]);
            $answers = $answersQuery->fetchAll(PDO::FETCH_ASSOC);

            $userAnswersByQuestion = [];
            foreach ($answers as $ans) {
                $userAnswersByQuestion[$ans['question_id']][] = $ans['option_id'];
            }

            $questionDetails = [];
            foreach ($questions as $q) {
                $qid = $q['id'];

                // Lấy tất cả options của câu hỏi
                $optionQuery = $this->conn->prepare("
                    SELECT id, option_text, is_correct
                    FROM question_options
                    WHERE question_id = :qid
                ");
                $optionQuery->execute(['qid' => $qid]);
                $options = $optionQuery->fetchAll(PDO::FETCH_ASSOC);

                // User chọn option nào
                $userSelected = isset($userAnswersByQuestion[$qid]) ? $userAnswersByQuestion[$qid] : [];

                // Kiểm tra đúng/sai
                $correctOptionIds = array_column(
                    array_filter($options, function ($opt) {
                        return $opt['is_correct'] == 1;
                    }),
                    'id'
                );
                sort($userSelected);
                sort($correctOptionIds);
                $isUserCorrect = ($userSelected == $correctOptionIds);

                $questionDetails[] = [
                    'question_id'   => $qid,
                    'content'       => $q['content'],
                    'options'       => $options,
                    'userAnswers'   => $userSelected,
                    'isUserCorrect' => $isUserCorrect
                ];
            }

            return $questionDetails;

        } catch (Throwable $e) {
            return $e;
        }
    }

    public function createResult($data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) continue;
            $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        $exam_id = $data['exam_id'];
        $details = $data['details'];
        $user_id = $data['user_id'];
        $start_time = $data['start_time'];
        $end_time = $data['end_time'];
        $status = $data['status'];

        $queryQuestion = $this->conn->prepare("Select * from exams where id = :exam_id");
        $queryQuestion->execute(['exam_id' => $exam_id]);
        $Questions = $queryQuestion->fetch(PDO::FETCH_ASSOC);
        $scoreOneQuestion = round($Questions['maxScore'] / $Questions['totalQuestions'], 2);

        $correctCount = 0;

        foreach ($details as $question_id => $userAnswers) {
            // Lấy danh sách đáp án đúng từ question_options
            $queryCorrect = $this->conn->prepare("
                SELECT id 
                FROM question_options 
                WHERE question_id = :question_id AND is_correct = 1
            ");
            $queryCorrect->execute(['question_id' => $question_id]);
            $correctAnswers = $queryCorrect->fetchAll(PDO::FETCH_COLUMN);

            // So sánh: user chọn == đáp án đúng?
            sort($userAnswers);     // chuẩn hóa mảng
            sort($correctAnswers);

            if ($userAnswers == $correctAnswers) {
                $correctCount++;
            }
        }
        $totalScore = round($correctCount * $scoreOneQuestion, 2);

        try {
            $this->conn->beginTransaction();

            // Thêm dữ liệu vào bảng `result`
            $query = $this->conn->prepare("Insert into exam_attempts(exam_id, user_id, start_time, end_time, score, status) 
                VALUES (:exam_id, :user_id, :start_time, :end_time, :score, :status)");
            $query->execute(['exam_id' => $exam_id, 'user_id' => $user_id,'start_time' => $start_time, 'end_time' => $end_time, 'score' => $totalScore, 'status' => $status]);


            $attempt_id = $this->conn->lastInsertId();

            // 4. Thêm chi tiết câu trả lời
            $detailQuery = $this->conn->prepare("
                INSERT INTO exam_answers (attempt_id, question_id, option_id, is_correct) 
                VALUES (:attempt_id, :question_id, :option_id, :is_correct)
            ");

            foreach ($details as $question_id => $userAnswers) {
                // Lấy danh sách đáp án đúng
                $queryCorrect = $this->conn->prepare("
                SELECT id 
                FROM question_options 
                WHERE question_id = :question_id AND is_correct = 1
            ");
                $queryCorrect->execute(['question_id' => $question_id]);
                $correctAnswers = $queryCorrect->fetchAll(PDO::FETCH_COLUMN);

                sort($userAnswers);
                sort($correctAnswers);

                $isCorrect = ($userAnswers == $correctAnswers) ? 1 : 0;

                foreach ($userAnswers as $option_id) {
                    $detailQuery->execute([
                        'attempt_id' => $attempt_id,
                        'question_id' => $question_id,
                        'option_id' => $option_id,
                        'is_correct' => $isCorrect
                    ]);
                }
            }
            $this->conn->commit();
            return $attempt_id;
        } catch (Throwable $e) {
            $this->conn->rollBack();
            return $e;
        }
    }

    public function getResultOfUser($id)
    {
        try {
            $query = $this->conn->prepare("select * from results where id_user =:id");
            $query->execute(['id' => $id]);
        } catch (Throwable $e) {
            return null;
        }
        return $query->fetchAll();
    }

}