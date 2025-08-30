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
            $query = $this->conn->prepare("select * from $this->table where id=:id");
            $query->execute(['id' => $id]);
        } catch (Throwable $e) {
            return null;
        }
        return $query->fetch();
    }

    public function getQuestion($id)
    {
        try {
            $query = $this->conn->prepare("select questions.* from results
                inner join exams on results.id_exam = exams.id
                inner join question_exam on exams.id = question_exam.id_exam
                inner join questions on question_exam.id_question = questions.id
                where results.id=:id");
            $query->execute(['id' => $id]);
        } catch (Throwable $e) {
            return null;
        }
        return $query->fetchAll();
    }

    public function createResult($data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) continue;
            $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        $id_exam = $data['id_exam'];
        $details = $data['details'];
        $id_user = $data['id_user'];
        $blank_question = $data['blank_question'];
        $duration = $data['duration'];

        $queryQuestion = $this->conn->prepare("select count(*) from questions 
                inner join question_exam on questions.id = question_exam.id_question 
                where id_exam = :id_exam");
        $queryQuestion->execute(['id_exam' => $id_exam]);
        $countQuestion = $queryQuestion->fetchColumn();
        $scoreOneQuestion = $countQuestion > 0 ? round(10 / $countQuestion, 2) : 0;

        $correctCount = 0;
        $wrongCount = 0;

        foreach ($details as $userAnswer) {
            $queryCorrect = $this->conn->prepare("SELECT correct_answer FROM questions WHERE id = :id_question");
            $queryCorrect->execute(['id_question' => $userAnswer['id_question']]);
            $correctAnswer = $queryCorrect->fetchColumn();

            if ($correctAnswer && strtoupper($userAnswer['answer']) === strtoupper($correctAnswer)) {
                $correctCount++;
            } else {
                $wrongCount++;
            }
        }
        $totalScore = round($correctCount * $scoreOneQuestion, 2);

        try {
            $this->conn->beginTransaction();

            // Thêm dữ liệu vào bảng `result`
            $query = $this->conn->prepare("Insert into results(id_user, id_exam, score, duration, correct_question, incorrect_question, 
                    blank_question) VALUES (:id_user, :id_exam, :score, :duration, :correct_question, :incorrect_question, :blank_question)");
            $query->execute(['id_user' => $id_user, 'id_exam' => $id_exam, 'score' => $totalScore, 'duration' => $duration, 'correct_question' => $correctCount,
                    'incorrect_question' => $wrongCount,'blank_question' => $blank_question]);

            // Lấy ID của bản ghi vừa được chèn
            $id_results = $this->conn->lastInsertId();

            // Thêm dữ liệu vào bảng `result_detail`
            $detailQuery = $this->conn->prepare("INSERT INTO result_detail (id_result, id_question, answer) VALUES (:id_result, :id_question, :answer)");

            if(count($details) > 0) {
                foreach ($details as $detail) {
                    $detail['id_result'] = $id_results; // Gán `id_result` cho từng câu hỏi
                    $detailQuery->execute($detail);
                }
            }
            $this->conn->commit();
            return $id_results;
        } catch (Throwable $e) {
            $this->conn->rollBack();
            return 0;
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