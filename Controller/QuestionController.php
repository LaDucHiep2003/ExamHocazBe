<?php
include_once __DIR__ . '/../Model/BaseModel.php';
include_once __DIR__ . '/../Model/QuestionModel.php';
class QuestionsController
{
    private $table;
    private $QuestionModel;
    public function __construct()
    {
        $this->table = 'questions';
        $this->QuestionModel = new QuestionModel();
    }
    public function index()
    {
        $result = $this->QuestionModel->index();
        echo json_encode(['questions' => $result]);
    }

    public function create()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$this->QuestionModel->createQuestion($data)) {
            echo json_encode(['message' => "Có lỗi xảy ra !"]);
        } else {
            echo json_encode(['message' => "Tạo câu hỏi thành công !"]);
        }
    }
    public function delete($id)
    {
        if ($id == 0) {
            echo json_encode(['message' => 'Câu hỏi không tồn tại !']);
        } else {
            if (!$this->QuestionModel->delete($id)) {
                echo json_encode(['message' => 'Có lỗi xảy ra !']);
            } else {
                echo json_encode(['message' => 'Xóa câu hỏi thành công !']);
            }
        }
    }
    public function detail($id)
    {
        $result = $this->QuestionModel->detail($id);
        echo json_encode(['data' => $result]);
    }

    public function edit($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);
        // kiểm tra dữ liệu tránh truyền script vào input
        foreach ($data as $key => $value) {
            if ($key !== 'answerlist') { // Không áp dụng htmlspecialchars cho mảng answerlist
                $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }
        // Chuyển danh sách câu trả lời thành chuỗi JSON
        if (isset($data['answerlist']) && is_array($data['answerlist'])) {
            $data['answerlist'] = json_encode($data['answerlist'], JSON_UNESCAPED_UNICODE);
        }
        if ($id == 0) {
            echo json_encode(['message' => 'Dữ liệu câu hỏi không tồn tại !']);
        } else {
            if (!$this->QuestionModel->edit($data, $id)) {
                echo json_encode(['message' => 'Cập nhật câu hỏi không thành công !']);
            } else {
                echo json_encode(['message' => 'Cập nhật câu hỏi thành công !']);
            }
        }
    }

    public function getQuestionInExam($id)
    {
        $result = $this->QuestionModel->getQuestionInExam($id);
        echo json_encode(['questions' => $result]);
    }
}