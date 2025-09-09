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
        // Kiểm tra dữ liệu
        foreach ($data as $key => $value) {
            if ($key !== 'answers') { // Không áp dụng htmlspecialchars cho mảng answerlist
                $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }
        // Lưu riêng danh sách answers, rồi bỏ ra khỏi $data để tránh insert nhầm
        $answers = [];
        if (isset($data['answers']) && is_array($data['answers'])) {
            $answers = $data['answers'];
            unset($data['answers']);
        }
        if($data['essay_answer'] === ''){
            unset($data['essay_answer']);
        }

        // Lấy tên cột và giá trị
        $columns = implode(",", array_keys($data));
        $values  = ":" . implode(",:", array_keys($data));

        try {
            // 1. Insert vào bảng questions
            $query = $this->conn->prepare("INSERT INTO $this->table ($columns) VALUES ($values)");
            $query->execute($data);

            // Lấy id của question vừa tạo
            $questionId = $this->conn->lastInsertId();

            // 2. Insert các answer vào bảng question_options
            if (!empty($answers)) {
                $queryOption = $this->conn->prepare("
                INSERT INTO question_options (question_id, option_text, is_correct) 
                VALUES (:question_id, :option_text, :is_correct)
            ");

                foreach ($answers as $answer) {
                    $queryOption->execute([
                        'question_id' => $questionId,
                        'option_text' => $answer['text'],
                        'is_correct' => $answer['is_correct']
                    ]);
                }
            }
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
        // query câu hỏi
        $sql = "
            SELECT q.content,q.type,q.difficulty, q.subject_id, q.created_by,q.status, q.subject_id
            FROM {$this->table} q
            INNER JOIN subjects s ON q.subject_id = s.id
            WHERE q.deleted = false and q.id=:id
        ";
        $result = $this->conn->prepare($sql);
        $result->execute(['id' => $id]);
        $data = $result->fetch(PDO::FETCH_ASSOC);

        // query options của các question đó
        $sqlOptions = "
            SELECT o.question_id, o.option_text, o.is_correct
            FROM question_options o
            WHERE o.question_id=:id
        ";
        $stmt = $this->conn->prepare($sqlOptions);
        $stmt->execute(['id' => $id]);
        $options = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // group options theo question_id
        $optionsByQuestion = [];
        foreach ($options as $opt) {
            $optionsByQuestion[$opt['question_id']][] = [
                'text' => $opt['option_text'],
                'is_correct' => (bool)$opt['is_correct']
            ];
        }
        $data['answers'] = isset($optionsByQuestion[$id]) ? $optionsByQuestion[$id] : [];
        return $data;
    }

    public function edit($data, $id)
    {
        // Kiểm tra dữ liệu
        foreach ($data as $key => $value) {
            if ($key !== 'answers') {
                $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }

        // Tách answers ra
        $answers = [];
        if (isset($data['answers']) && is_array($data['answers'])) {
            $answers = $data['answers'];
            unset($data['answers']);
        }
        if (isset($data['essay_answer']) && $data['essay_answer'] === '') {
            unset($data['essay_answer']);
        }

        try {
            // 1. Update bảng questions
            $set = [];
            foreach ($data as $key => $value) {
                $set[] = "$key = :$key";
            }
            $set = implode(",", $set);

            $data['id'] = $id; // thêm id để bind vào WHERE
            $query = $this->conn->prepare("UPDATE $this->table SET $set WHERE id = :id");
            $query->execute($data);

            // 2. Xóa toàn bộ answers cũ
            $delete = $this->conn->prepare("DELETE FROM question_options WHERE question_id = :id");
            $delete->execute(['id' => $id]);

            // 3. Thêm lại các answer mới
            if (!empty($answers)) {
                $queryOption = $this->conn->prepare("
                INSERT INTO question_options (question_id, option_text, is_correct) 
                VALUES (:question_id, :option_text, :is_correct)
            ");

                foreach ($answers as $answer) {
                    $queryOption->execute([
                        'question_id' => $id,
                        'option_text' => $answer['text'],
                        'is_correct'  => $answer['is_correct']
                    ]);
                }
            }
        } catch (Throwable $e) {
            return false;
        }
        return true;
    }

    public function getQuestionInExam($id){
        try {
            $query = $this->conn->prepare("select questions.* from questions 
                inner join exam_questions on questions.id = exam_questions.question_id 
                where exam_id=:id");
            $query->execute(['id'=>$id]);
            $questions = $query->fetchAll(PDO::FETCH_ASSOC);

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
        } catch (Throwable $e) {
            return null;
        }
        return $questions;
    }
}