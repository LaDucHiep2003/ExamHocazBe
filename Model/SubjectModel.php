<?php
include_once __DIR__ . '/../Model/BaseModel.php';
class SubjectModel extends BaseModel
{
    protected $table;
    protected $SubjectModel;
    protected $conn;

    public function __construct()
    {
        $this->table = 'subjects';
        $this->conn = ConnectionDB::GetConnect();
        $this->SubjectModel = new BaseModel($this->table);
    }

    public function index($sql = null)
    {
        return $this->SubjectModel->index();
    }

//    public function getSubjectsOfCategory($id)
//    {
//        try {
//            $query = $this->conn->prepare("Select subjects.* from categories
//                inner join subjects on categories.id = subjects.id_category
//                where categories.id = :id and subjects.status = false");
//            $query->execute(['id' => $id]);
//        } catch (Throwable $e) {
//            return null;
//        }
//        return $query->fetchAll();
//    }
    public function getSubjectsOfCategory($id)
    {
        try {
            $query = $this->conn->prepare("Select subjects.* from categories
                inner join subjects on categories.id = subjects.id_category
                where categories.id = :id and subjects.status = false");
            $query->execute(['id' => $id]);
        } catch (Throwable $e) {
            return null;
        }
        return $query->fetchAll();
    }
    public function detail($id)
    {
        return $this->SubjectModel->read($id);
    }
    public function create($data)
    {
        return $this->SubjectModel->create($data);
    }
    public function edit($data, $id)
    {
        return $this->SubjectModel->update($data, $id);
    }
    public function delete($id)
    {
        return $this->SubjectModel->delete($id);
    }
}