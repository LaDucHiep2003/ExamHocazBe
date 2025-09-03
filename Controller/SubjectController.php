<?php
include_once __DIR__ . '/../Model/BaseModel.php';
include_once __DIR__ . '/../Model/SubjectModel.php';
class SubjectController
{
    private $SubjectModel;
    private $table;

    public function __construct()
    {
        $this->table = 'subjects';
        $this->SubjectModel = new SubjectModel();
    }
    public function index()
    {
        $result = $this->SubjectModel->index();
        echo json_encode(['subjects' => $result]);
    }
    public function getSubjectsOfCategory($id)
    {
        $result = $this->SubjectModel->getSubjectsOfCategory($id);
        echo json_encode(['subjects' => $result]);
    }
    public function create()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        // kiểm tra dữ liệu tránh truyền script vào input
        foreach ($data as $key => $value) {
            $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        if (!$this->SubjectModel->create($data)) {
            echo json_encode(['message' => "Có lỗi xảy ra !"]);
        } else {
            echo json_encode(['message' => "Tạo mới môn học thành công !"]);
        }
    }
    public function detail($id)
    {
        $result = $this->SubjectModel->detail($id);
        echo json_encode(['data' => $result]);
    }
    public function edit()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        // kiểm tra dữ liệu tránh truyền script vào input
        foreach ($data as $key => $value) {
            $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        if (!$this->SubjectModel->edit($data)) {
            echo json_encode(['message' => 'Error !']);
        } else {
            echo json_encode(['message' => 'Cập nhật môn học thành công !']);
        }
    }
    public function delete($id)
    {
        if ($id == 0) {
            echo json_encode(['message' => 'Dạnh mục không tồn tại !']);
        } else {
            if (!$this->SubjectModel->delete($id)) {
                echo json_encode(['message' => 'Có lỗi xảy ra !']);
            } else {
                echo json_encode(['message' => 'Xóa môn học thành công !']);
            }
        }
    }
}