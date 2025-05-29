<?php
include_once __DIR__ . '/../Model/BaseModel.php';
class QuestionModel extends BaseModel
{
    protected $QuestionModel;
    protected $table;
    public function __construct()
    {
        $this->table = 'questions';
        $this->QuestionModel = new BaseModel($this->table);
    }
    public function index()
    {
        return $this->QuestionModel->index();
    }

    public function createQuestion($data)
    {
        $conn = ConnectionDB::GetConnect();
        // Kiểm tra dữ liệu
        foreach ($data as $key => $value) {
            if ($key !== 'answerlist') { // Không áp dụng htmlspecialchars cho mảng answerlist
                $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }
        // Chuyển danh sách câu trả lời thành chuỗi JSON
        if (isset($data['answerlist']) && is_array($data['answerlist'])) {
            $data['answerlist'] = json_encode($data['answerlist'], JSON_UNESCAPED_UNICODE);
        }

        // Lấy tên cột và giá trị
        $columns = implode(",", array_keys($data));
        $values = ":" . implode(",:", array_keys($data));

        // Chuẩn bị câu lệnh SQL
        $query = $conn->prepare("INSERT INTO $this->table ($columns) VALUES ($values)");

        try {
            $query->execute($data);
        } catch (Throwable $e) {
            return false;
        }
        return true;
    }
    public function delete($id)
    {
        return $this->QuestionModel->delete($id);
    }
    public function detail($id)
    {
        return $this->QuestionModel->read($id);
    }

    public function edit($data, $id)
    {
        return $this->QuestionModel->update($data, $id);
    }

    public function getQuestionInExam($id){
        $conn = ConnectionDB::GetConnect();
        try {
            $query = $conn->prepare("select questions.* from questions 
                inner join question_exam on questions.id = question_exam.id_question 
                where id_exam=:id");
            $query->execute(['id'=>$id]);
        } catch (Throwable $e) {
            return null;
        }
        return $query->fetchAll();
    }
}