<?php
include_once __DIR__ . '/../Model/BaseModel.php';
include_once __DIR__ . '/../Model/ChapterModel.php';

class ChapterController
{
    private $ChapterModel;
    private $table;
    public function __construct()
    {
        $this->table = 'chapters';
        $this->ChapterModel = new ChapterModel();
    }
    public function index()
    {
        $result = $this->ChapterModel->index();
        echo json_encode(['chapters' => $result]);
    }
    public function create()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        // kiểm tra dữ liệu tránh truyền script vào input
        foreach ($data as $key => $value) {
            $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        if (!$this->ChapterModel->create($data)) {
            echo json_encode(['message' => "Có lỗi xảy ra !"]);
        } else {
            echo json_encode(['message' => "Tạo mới chương thành công !"]);
        }
    }

    public function getChaptersOfSubject($id)
    {
        $result = $this->ChapterModel->getChaptersOfSubject($id);
        echo json_encode(['chapters' => $result]);
    }
    public function detail($id)
    {
        $result = $this->ChapterModel->detail($id);
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
            if (!$this->ChapterModel->edit($data, $id)) {
                echo json_encode(['message' => 'Cập nhật danh mục bài thi không thành công !']);
            } else {
                echo json_encode(['message' => 'Cập nhật danh mục bài thi thành công !']);
            }
        }
    }
    public function delete($id)
    {
        if ($id == 0) {
            echo json_encode(['message' => 'Dạnh mục không tồn tại !']);
        } else {
            if (!$this->ChapterModel->delete($id)) {
                echo json_encode(['message' => 'Có lỗi xảy ra !']);
            } else {
                echo json_encode(['message' => 'Xóa chương thành công !']);
            }
        }
    }
}