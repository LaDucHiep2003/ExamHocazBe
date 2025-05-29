<?php
include_once __DIR__ . '/../Model/BaseModel.php';
include_once __DIR__ . '/../Model/ClassRoomModel.php';
class ClassRoomController
{
    private $ClassRoomModel;
    private $table;

    public function __construct()
    {
        $this->table = 'classrooms';
        $this->ClassRoomModel = new ClassRoomModel();
    }

    public function index()
    {
        $result = $this->ClassRoomModel->index();
        echo json_encode(['classroom' => $result]);
    }
}