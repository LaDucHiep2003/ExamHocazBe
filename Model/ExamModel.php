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
        return $this->ExamModel->index();
    }
    public function createExam($data)
    {
        $id_subject = $data['id_subject'];
        $query = $this->conn->prepare("INSERT INTO exams (title, description, id_chapter, duration, total_questions)
            VALUES (:title, :description, :id_chapter, :duration, :total_questions)
        ");
        try {
            $query->execute([
                'title' => $data['title'],
                'description' => $data['description'],
                'id_chapter' => $data['id_chapter'],
                'duration' => $data['duration'],
                'total_questions' => $data['total_questions'],
            ]);
            $exam_id = $this->conn->lastInsertId();

            $questionCount = isset($data['total_questions']) ? (int)$data['total_questions'] : 3; // Mặc định là 3 nếu không có dữ liệu

            $questionQuery = $this->conn->prepare("SELECT id FROM questions where id_subject=:id_subject ORDER BY RAND() LIMIT $questionCount");
            $questionQuery->execute(['id_subject' => $id_subject]);
            $questions = $questionQuery->fetchAll(PDO::FETCH_ASSOC);

            foreach ($questions as $question) {
                $examQuestionQuery = $this->conn->prepare("INSERT INTO question_exam (id_exam, id_question) VALUES (:id_exam, :id_question)");
                $examQuestionQuery->execute([
                    'id_exam' => $exam_id,
                    'id_question' => $question['id']
                ]);
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