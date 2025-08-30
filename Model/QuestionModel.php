<?php
include_once __DIR__ . '/../Model/BaseModel.php';
include_once __DIR__ . "/../Connection/Connection.php";

class QuestionModel extends BaseModel
{
    protected $QuestionModel;
    protected $table;
    public function __construct()
    {
        $this->table = 'questions';
        $this->QuestionModel = new BaseModel($this->table);
        $this->conn = ConnectionDB::GetConnect();
    }
    public function index($sql = null)
    {
        // query câu hỏi
        $sql = "
        SELECT q.*, s.name, s.code
        FROM {$this->table} q
        INNER JOIN subjects s ON q.subject_id = s.id
        WHERE q.deleted = false
    ";
        $result = $this->QuestionModel->index($sql);

        // lấy danh sách question_id trong page hiện tại
        $questionIds = array_column($result['data'], 'id');
        if (empty($questionIds)) {
            $result['data'] = [];
            return $result;
        }

        // query options của các question đó
        $inQuery = implode(',', array_fill(0, count($questionIds), '?'));
        $sqlOptions = "
        SELECT o.question_id, o.option_text, o.is_correct
        FROM question_options o
        WHERE o.question_id IN ($inQuery)
    ";
        $stmt = $this->conn->prepare($sqlOptions);
        $stmt->execute($questionIds);
        $options = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // group options theo question_id
        $optionsByQuestion = [];
        foreach ($options as $opt) {
            $optionsByQuestion[$opt['question_id']][] = [
                'text' => $opt['option_text'],
                'is_correct' => (bool)$opt['is_correct']
            ];
        }

        // gắn answers vào từng question
        foreach ($result['data'] as &$q) {
            $q['answers'] = isset($optionsByQuestion[$q['id']]) ? $optionsByQuestion[$q['id']] : [];
        }

        return $result;
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