<?php
include_once __DIR__ . '/../Model/BaseModel.php';
include_once __DIR__ . '/../Model/ExamModel.php';
class ExamController
{
    private $ExamModel;
    private $table;
    public function __construct()
    {
        $this->table = 'exams';
        $this->ExamModel = new ExamModel();
    }
    public function index()
    {
        $result = $this->ExamModel->index();
        echo json_encode(['exams' => $result]);
    }

    public function create()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$this->ExamModel->createExam($data)) {
            echo json_encode(['message' => "Có lỗi xảy ra !"]);
        } else {
            echo json_encode(['message' => "Tạo mới bài thi thành công !"]);
        }
    }

    public function delete($id)
    {
        if ($id == 0) {
            echo json_encode(['message' => 'Bài thi không tồn tại !']);
        } else {
            if (!$this->ExamModel->delete($id)) {
                echo json_encode(['message' => 'Có lỗi xảy ra !']);
            } else {
                echo json_encode(['message' => 'Xóa bài thi thành công !']);
            }
        }
    }

    public function detail($id)
    {
        $result = $this->ExamModel->detail($id);
        echo json_encode(['data' => $result]);
    }

    public function edit($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);
        // kiểm tra dữ liệu tránh truyền script vào input
        foreach ($data as $key => $value) {
            $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        if ($id == 0) {
            echo json_encode(['message' => 'Dữ liệu bài thi không tồn tại !']);
        } else {
            if (!$this->ExamModel->edit($data, $id)) {
                echo json_encode(['message' => 'Cập nhật bài thi không thành công !']);
            } else {
                echo json_encode(['message' => 'Cập nhật bài thi thành công !']);
            }
        }
    }

    public function getExamsOfChapter($id)
    {
        $result = $this->ExamModel->getExamsOfChapter($id);
        echo json_encode(['exams' => $result]);
    }
}