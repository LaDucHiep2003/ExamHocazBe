<?php
include_once __DIR__ . '/../Model/BaseModel.php';
include_once __DIR__ . '/../Model/ResultDetailModel.php';
class ResultDetailController
{
    private $table;
    private $ResultDetailModel;

    public function __construct()
    {
        $this->table = 'result_detail';
        $this->ResultDetailModel = new ResultDetailModel();
    }
    public function detail($id)
    {
        $result = $this->ResultDetailModel->detail($id);
        echo json_encode(['data' => $result]);
    }
}