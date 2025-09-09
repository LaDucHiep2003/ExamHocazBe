<?php
include_once __DIR__ . '/../Model/BaseModel.php';

require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UserModel extends BaseModel
{
    protected $table;
    protected $UserModel;
    protected $conn;

    public function __construct()
    {
        $this->table = 'users';
        $this->UserModel = new BaseModel($this->table);
        $this->conn = ConnectionDB::GetConnect();
    }

    public function index($sql = null)
    {
        $sql = "select s.*, r.name as role_name from users s
            inner join role r on s.role_id = r.id where s.deleted = false";
        return $this->UserModel->index($sql);
    }

    public function detail($id)
    {
        return $this->UserModel->read($id);
    }

    public function edit($data)
    {
        return $this->UserModel->update($data);
    }
    public function delete($id)
    {
        return $this->UserModel->delete($id);
    }

    public function register($data)
    {
        $email = $data['email'];
        try {
            $this->conn->beginTransaction();
            $query = $this->conn->prepare("select id from $this->table where email=:email");
            $query->execute(['email' => $email]);
            if ($query->rowCount() > 0) {
                echo json_encode(['message' => 'Email đã tồn tại']);
            } else {
                $this->UserModel->create($data);
                echo json_encode(['message' => 'Đăng ký tài khoản thành công']);
            }
            $this->conn->commit();
        } catch (Throwable $e) {
            $this->conn->rollBack();
            echo json_encode(['message' => $e]);
        }
    }

    public function login($data)
    {
        $key = getenv('Key');
        try {
            $email = $data['email'];
            $pass = md5($data['password']);
            $query = $this->conn->prepare("select users.*, role.code from users Inner join role on users.role_id = role.id where users.email=:email and users.password=:password LIMIT 1");
            $query->execute(['email' => $email, 'password' => $pass]);
            $user = $query->fetch(PDO::FETCH_ASSOC);
            if ($query->rowCount() > 0) {
                $timeCreate = time();
                $timeExpire = time() + 86400;
                $payload = [
                    'iat' => $timeCreate,
                    'exp' => $timeExpire,
                    'data' => [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'full_name' => $user['full_name'],
                        'role_id' => $user['role_id'],
                        'roles' => $user['code'],
                        'username' => $user['username'],
                        'type_account' => 'account'
                    ]
                ];
                $jwt = JWT::encode($payload, $key, 'HS256');
                echo json_encode([
                    'message' => 'Đăng nhập thành công !',
                    'jwt' => $jwt,
                ]);
            } else {
                echo json_encode(['message' => 'Đăng nhập thất bại ! Tài khoản hoặc mật khẩu không chính xác']);
            }
        } catch (Throwable $e) {
            echo json_encode(['message' => "Có lỗi xảy ra " . $e]);
        }
    }

    public function getUserFromToken($headers)
    {
        $key = getenv('Key');
        try {
            if (!isset($headers['Authorization'])) {
                echo json_encode(['message' => 'Authorization header không tồn tại']);
                http_response_code(401);
                return;
            }

            $authHeader = $headers['Authorization'];
            if (strpos($authHeader, 'Bearer ') !== 0) {
                echo json_encode(['message' => 'Authorization header không hợp lệ']);
                http_response_code(401);
                return;
            }

            // Lấy token từ Header
            $jwt = str_replace('Bearer ', '', $authHeader);

            // Giải mã token
            $decoded = JWT::decode($jwt, new Key($key, 'HS256'));

            // Trích xuất thông tin người dùng từ payload
            $userData = (array) $decoded->data;
            echo json_encode([
                'message' => 'Token hợp lệ',
                'user' => $userData
            ]);
            http_response_code(200);
        } catch (Throwable $e) {
            echo json_encode([
                'message' => 'Token không hợp lệ hoặc đã hết hạn',
                'error' => $e->getMessage()
            ]);
            http_response_code(401);
        }
    }

}