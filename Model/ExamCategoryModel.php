<?php
include_once __DIR__ . '/../Model/BaseModel.php';
class ExamCategoryModel extends BaseModel
{
    protected $table;
    protected $ExamCategoryModel;
    protected $conn;

    public function __construct()
    {
        $this->table = 'categories';
        $this->conn = ConnectionDB::GetConnect();
        $this->ExamCategoryModel = new BaseModel($this->table);
    }

    public function index($sql = null)
    {
        return $this->ExamCategoryModel->index();
    }

    public function create($data)
    {
        return $this->ExamCategoryModel->create($data);
    }

    public function delete($id)
    {
        return $this->ExamCategoryModel->delete($id);
    }

    public function detail($id)
    {
        return $this->ExamCategoryModel->read($id);
    }

    public function edit($data, $id)
    {
        return $this->ExamCategoryModel->update($data, $id);
    }
}