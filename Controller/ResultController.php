<?php
include_once __DIR__ . '/../Model/BaseModel.php';
include_once __DIR__ . '/../Model/ResultModel.php';
class ResultController
{
    private $table;
    private $ResultModel;

    public function __construct()
    {
        $this->table = 'results';
        $this->ResultModel = new ResultModel();
    }

    public function index()
    {
        $result = $this->ResultModel->index();
        echo json_encode(['results' => $result]);
    }

    public function detail($id)
    {
        $result = $this->ResultModel->detail($id);
        echo json_encode(['data' => $result]);
    }
    public function getQuestions($id)
    {
        $result = $this->ResultModel->getQuestion($id);
        echo json_encode(['questions' => $result]);
    }
    public function create()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $result = $this->ResultModel->createResult($data);
        if ($result <= 0) {
            http_response_code(500);
            echo json_encode(['message' => "Có lỗi xảy ra!"]);
        } else {
            http_response_code(200);
            echo json_encode(['id_result' => $result]);
        }
    }

    public function getResultOfUser($id)
    {
        $result = $this->ResultModel->getResultOfUser($id);
        echo json_encode(['data' => $result]);
    }
}