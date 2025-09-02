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
                   created_by,status, type,maxScore, passingScore, totalQuestions, difficulty )
                   VALUES (
                        :name, :description, :subject_id, :start_time, :end_time, :duration_minutes,
                        :created_by, :status, :type, :maxScore, :passingScore, :totalQuestions, :difficulty
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
                'difficulty' => $data['difficulty']
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

    public function edit($data, $id)
    {
        return $this->ExamModel->update($data, $id);
    }
    public function detail($id)
    {
        try {
            $query = $this->conn->prepare("Select exams.*,subjects.id as id_subject, subjects.id_category  from exams
                inner join chapters on exams.id_chapter = chapters.id
                inner join subjects on chapters.id_subject = subjects.id
                where exams.id = :id");
            $query->execute(['id' => $id]);
        } catch (Throwable $e) {
            return null;
        }
        return $query->fetch();
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