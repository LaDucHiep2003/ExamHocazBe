<?php
include_once __DIR__ . '/../Model/BaseModel.php';
class ExamModel extends BaseModel
{
    protected $table;
    protected $ExamModel;
    protected $conn;

    public function __construct()
    {
        $this->table = 'exams';
        $this->conn = ConnectionDB::GetConnect();
        $this->ExamModel = new BaseModel($this->table);
    }

    public function index($sql = null)
    {
        $sql = "
            SELECT e.*, s.name as subject_name, u.full_name as creator, u.id as user_id
            FROM {$this->table} e
            INNER JOIN subjects s ON e.subject_id = s.id
            INNER JOIN users u ON e.created_by = u.id
            WHERE e.deleted = false
        ";
        $result = $this->ExamModel->index($sql);
        return $result;
    }
    public function createExam($data)
    {
        $subject_id = $data['subject_id'];
        $query = $this->conn->prepare("INSERT INTO exams (name, description, subject_id, start_time, end_time,duration_minutes, 
                   created_by,status, type,maxScore, passingScore, totalQuestions, difficulty, questionSource )
                   VALUES (
                        :name, :description, :subject_id, :start_time, :end_time, :duration_minutes,
                        :created_by, :status, :type, :maxScore, :passingScore, :totalQuestions, :difficulty, :questionSource
                    )
        ");
        try {
            $query->execute([
                'name' => $data['name'],
                'description' => $data['description'],
                'subject_id' => $data['subject_id'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'duration_minutes' => $data['duration_minutes'],
                'created_by' => $data['created_by'],
                'status' => $data['status'],
                'type' => $data['type'],
                'maxScore' => $data['maxScore'],
                'passingScore' => $data['passingScore'],
                'totalQuestions' => $data['totalQuestions'],
                'difficulty' => $data['difficulty'],
                'questionSource' => $data['questionSource']
            ]);
            $exam_id = $this->conn->lastInsertId();

            if($data['questionSource'] === 'manual'){
                $questionCount = isset($data['totalQuestions']) ? (int)$data['totalQuestions'] : 3; // Mặc định là 3 nếu không có dữ liệu
                $questionQuery = $this->conn->prepare("SELECT id FROM questions where subject_id=:subject_id and deleted = false ORDER BY RAND() LIMIT $questionCount");
                $questionQuery->execute(['subject_id' => $subject_id]);
                $questions = $questionQuery->fetchAll(PDO::FETCH_ASSOC);

                foreach ($questions as $question) {
                    $examQuestionQuery = $this->conn->prepare("INSERT INTO exam_questions (exam_id, question_id) VALUES (:exam_id, :question_id)");
                    $examQuestionQuery->execute([
                        'exam_id' => $exam_id,
                        'question_id' => $question['id']
                    ]);
                }
            }else{
                foreach ($data['selectedQuestions'] as $question) {
                    $examQuestionQuery = $this->conn->prepare("INSERT INTO exam_questions (exam_id, question_id) VALUES (:exam_id, :question_id)");
                    $examQuestionQuery->execute([
                        'exam_id' => $exam_id,
                        'question_id' => $question
                    ]);
                }
            }

        } catch (Throwable $e) {
            // Xử lý lỗi nếu có
            return false;
        }

        return true;
    }

    public function delete($id)
    {
        return $this->ExamModel->delete($id);
    }

    public function edit($data)
    {
        $subject_id = $data['subject_id'];
        $id = $data['id'];

        $query = $this->conn->prepare("
        UPDATE exams 
        SET name = :name,
            description = :description,
            subject_id = :subject_id,
            start_time = :start_time,
            end_time = :end_time,
            duration_minutes = :duration_minutes,
            status = :status,
            type = :type,
            maxScore = :maxScore,
            passingScore = :passingScore,
            totalQuestions = :totalQuestions,
            difficulty = :difficulty,
            questionSource = :questionSource
            
        WHERE id = :id AND deleted = false
    ");

        try {
            // Update exam
            $query->execute([
                'name' => $data['name'],
                'description' => $data['description'],
                'subject_id' => $data['subject_id'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'duration_minutes' => $data['duration_minutes'],
                'status' => $data['status'],
                'type' => $data['type'],
                'maxScore' => $data['maxScore'],
                'passingScore' => $data['passingScore'],
                'totalQuestions' => $data['totalQuestions'],
                'difficulty' => $data['difficulty'],
                'questionSource' => $data['questionSource'],
                'id' => $id
            ]);

            // Xóa hết câu hỏi cũ của exam
            $deleteQuery = $this->conn->prepare("DELETE FROM exam_questions WHERE exam_id = :exam_id");
            $deleteQuery->execute(['exam_id' => $id]);

            // Thêm lại câu hỏi mới
            if ($data['questionSource'] === 'manual') {
                $questionCount = isset($data['totalQuestions']) ? (int)$data['totalQuestions'] : 3;
                $questionQuery = $this->conn->prepare("
                SELECT id FROM questions 
                WHERE subject_id = :subject_id AND deleted = false 
                ORDER BY RAND() 
                LIMIT $questionCount
            ");
                $questionQuery->execute(['subject_id' => $subject_id]);
                $questions = $questionQuery->fetchAll(PDO::FETCH_ASSOC);

                foreach ($questions as $question) {
                    $examQuestionQuery = $this->conn->prepare("
                    INSERT INTO exam_questions (exam_id, question_id) 
                    VALUES (:exam_id, :question_id)
                ");
                    $examQuestionQuery->execute([
                        'exam_id' => $id,
                        'question_id' => $question['id']
                    ]);
                }
            } else {
                foreach ($data['selectedQuestions'] as $question) {
                    $examQuestionQuery = $this->conn->prepare("
                    INSERT INTO exam_questions (exam_id, question_id) 
                    VALUES (:exam_id, :question_id)
                ");
                    $examQuestionQuery->execute([
                        'exam_id' => $id,
                        'question_id' => $question
                    ]);
                }
            }
        } catch (Throwable $e) {
            // Xử lý lỗi
            return false;
        }

        return true;
    }
    public function detail($id)
    {
        try {
            // Lấy thông tin chi tiết bài thi
            $examQuery = $this->conn->prepare("
                SELECT e.*, s.name as subject_name
                FROM exams e
                INNER JOIN subjects s ON e.subject_id = s.id
                WHERE e.id = :id AND e.deleted = false
            ");
            $examQuery->execute(['id' => $id]);
            $exam = $examQuery->fetch(PDO::FETCH_ASSOC);
            
            if (!$exam) {
                return null;
            }
            
            // Lấy danh sách câu hỏi của bài thi
            $questionsQuery = $this->conn->prepare("
                SELECT q.*
                FROM questions q
                INNER JOIN exam_questions eq ON q.id = eq.question_id
                WHERE eq.exam_id = :exam_id AND q.deleted = false
            ");
            $questionsQuery->execute(['exam_id' => $id]);
            $questions = $questionsQuery->fetchAll(PDO::FETCH_ASSOC);

            foreach ($questions as &$question) {
                $optionsQuery = $this->conn->prepare("
                    SELECT o.id, o.option_text AS text, o.is_correct
                    FROM question_options o
                    WHERE o.question_id = :question_id
                    ORDER BY o.id ASC
                ");
                $optionsQuery->execute(['question_id' => $question['id']]);
                $question['answers'] = $optionsQuery->fetchAll(PDO::FETCH_ASSOC);
            }
            
            // Gộp thông tin bài thi và câu hỏi
            $exam['selectedQuestions'] = array_column($questions, 'id');
            $exam['questions'] = $questions;
            
            return $exam;
            
        } catch (Throwable $e) {
            return null;
        }
    }
    public function getExamsOfChapter($id)
    {
        try {
            $query = $this->conn->prepare("select exams.* from exams
                inner join chapters on exams.id_chapter = chapters.id
                where chapters.id = :id");
            $query->execute(['id' => $id]);
        } catch (Throwable $e) {
            return null;
        }
        return $query->fetchAll();
    }
}