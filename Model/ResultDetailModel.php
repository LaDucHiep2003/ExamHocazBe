<?php
include_once __DIR__ . '/../Model/BaseModel.php';
class ResultDetailModel extends BaseModel
{
    protected $ResultDetailModel;
    protected $table;

    public function __construct()
    {
        $this->table = 'result_detail';
        $this->ResultDetailModel = new BaseModel($this->table);
    }
    public function detail($id)
    {
        return $this->ResultDetailModel->read($id);
    }
}