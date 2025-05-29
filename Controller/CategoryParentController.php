<?php
include_once __DIR__ . '/../Model/BaseModel.php';
include_once __DIR__ . '/../Model/CategoryParentModel.php';
class CategoryParentController
{
    private $CategoryParentModel;
    private $table;
    public function __construct()
    {
        $this->table = 'categoryparent';
        $this->CategoryParentModel = new CategoryParentModel();
    }
    public function index()
    {
        $result = $this->CategoryParentModel->index();
        echo json_encode(['categories' => $result]);
    }
    public function create()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        // kiểm tra dữ liệu tránh truyền script vào input
        foreach ($data as $key => $value) {
            $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        if ($this->CategoryParentModel->create($data) == false) {
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
            if ($this->CategoryParentModel->delete($id) == false) {
                echo json_encode(['message' => 'Có lỗi xảy ra !']);
            } else {
                echo json_encode(['message' => 'Xóa danh mục thành công !']);
            }
        }
    }

    public function detail($slug)
    {
        $result = $this->CategoryParentModel->detail($slug);
        echo json_encode(['data' => $result]);
    }
    public function detailById($id)
    {
        $result = $this->CategoryParentModel->detailById($id);
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
            if (!$this->CategoryParentModel->edit($data, $id)) {
                echo json_encode(['message' => 'Cập nhật danh mục bài thi không thành công !']);
            } else {
                echo json_encode(['message' => 'Cập nhật danh mục bài thi thành công !']);
            }
        }
    }
}