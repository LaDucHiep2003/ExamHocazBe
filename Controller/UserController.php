<?php
include_once __DIR__ . '/../Model/BaseModel.php';
include_once __DIR__ . '/../Model/UserModel.php';
class UserController
{
    private $UserModel;
    private $table;

    public function __construct()
    {
        $this->table = 'users';
        $this->UserModel = new UserModel();
    }

    public function index()
    {
        $result = $this->UserModel->index();
        echo json_encode(['users' => $result]);
    }

    public function detail($id)
    {
        $result = $this->UserModel->detail($id);
        echo json_encode(['data' => $result]);
    }

    public function edit()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $data['password'] = md5($data['password']);

        foreach ($data as $key => $value) {
            $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        if (!$this->UserModel->edit($data)) {
            echo json_encode(['message' => 'Cập nhật tài khoản không thành công !']);
        } else {
            echo json_encode(['message' => 'Cập nhật tài khoản thành công !']);
        }
    }
    public function delete($id)
    {
        if ($id == 0) {
            echo json_encode(['message' => 'tài khoản không tồn tại !']);
        } else {
            if (!$this->UserModel->delete($id)) {
                echo json_encode(['message' => 'Có lỗi xảy ra !']);
            } else {
                echo json_encode(['message' => 'Xóa tài khoản thành công !']);
            }
        }
    }

    public function login(){
        $data = json_decode(file_get_contents("php://input"),true);
        $this->UserModel->login($data);
    }

    public function register()
    {
        $data = json_decode(file_get_contents("php://input"),true);
        $data['password'] = md5($data['password']);
        $this->UserModel->register($data);
    }

    public function getUserFromToken()
    {
        $headers = getallheaders(); // Lấy tất cả headers từ request
        $this->UserModel->getUserFromToken($headers);
    }
}