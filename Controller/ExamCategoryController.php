<?php
include_once __DIR__ . '/../Model/BaseModel.php';
include_once __DIR__ . '/../Model/ExamCategoryModel.php';
class ExamCategoryController
{
    private $ExamCategoryModel;
    private $table;
    public function __construct()
    {
        $this->table = 'categories';
        $this->ExamCategoryModel = new ExamCategoryModel();
    }
    public function index()
    {
        $result = $this->ExamCategoryModel->index();
        echo json_encode(['categories' => $result]);
    }
    public function create()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        // kiểm tra dữ liệu tránh truyền script vào input
        foreach ($data as $key => $value) {
            $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        if ($this->ExamCategoryModel->create($data) == false) {
            echo json_encode(['message' => "Có lỗi xảy ra !"]);
        } else {
            echo json_encode(['message' => "Tạo mới danh mục bài thi thành công !"]);
        }
    }

    public function delete($id)
    {
        if ($id == 0) {
            echo json_encode(['message' => 'Dạnh mục không tồn tại !']);
        } else {
            if ($this->ExamCategoryModel->delete($id) == false) {
                echo json_encode(['message' => 'Có lỗi xảy ra !']);
            } else {
                echo json_encode(['message' => 'Xóa danh mục thành công !']);
            }
        }
    }

    public function detail($id)
    {
        $result = $this->ExamCategoryModel->detail($id);
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
            echo json_encode(['message' => 'Dữ liệu danh mục bài thi không tồn tại !']);
        } else {
            if (!$this->ExamCategoryModel->edit($data, $id)) {
                echo json_encode(['message' => 'Cập nhật danh mục bài thi không thành công !']);
            } else {
                echo json_encode(['message' => 'Cập nhật danh mục bài thi thành công !']);
            }
        }
    }

}