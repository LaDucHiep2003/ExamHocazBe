<?php
include_once __DIR__ . '/../Model/BaseModel.php';
include_once __DIR__ . '/../Model/IRTModel.php';
class IRTController
{
    private $IRTModel;
    private $table;

    public function __construct()
    {
        $this->table = 'users';
        $this->IRTModel = new IRTModel();
    }

    public function getInformationStudent()
    {
        $result = $this->IRTModel->getInformationStudent();
        echo json_encode($result);
    }

    public function getInformationQuestion()
    {
        $result = $this->IRTModel->getInformationQuestion();
        echo json_encode($result);
    }
}




