<?php
include_once __DIR__ . '/../Model/BaseModel.php';
class ClassRoomModel extends BaseModel
{
    protected $table;
    protected $ClassRoomModel;

    public function __construct()
    {
        $this->table = 'classrooms';
        $this->ClassRoomModel = new BaseModel($this->table);
    }

    public function index()
    {
        return $this->ClassRoomModel->index();
    }
}