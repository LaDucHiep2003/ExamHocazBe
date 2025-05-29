<?php
include_once __DIR__ . '/../Model/BaseModel.php';


class ChapterModel
{
    protected $table;
    protected $ChapterModel;
    protected $conn;

    public function __construct()
    {
        $this->table = 'chapters';
        $this->conn = ConnectionDB::GetConnect();
        $this->ChapterModel = new BaseModel($this->table);
    }
    public function index()
    {
        return $this->ChapterModel->index();
    }
    public function create($data)
    {
        return $this->ChapterModel->create($data);
    }
    public function getChaptersOfSubject($id)
    {
        try {
            $query = $this->conn->prepare("Select chapters.* from subjects
                inner join chapters on subjects.id = chapters.id_subject
                where subjects.id = :id and chapters.status = false");
            $query->execute(['id' => $id]);
        } catch (Throwable $e) {
            return null;
        }
        return $query->fetchAll();
    }
    public function detail($id)
    {
        try {
            $query = $this->conn->prepare("Select chapters.*, subjects.id_category from chapters
                inner join subjects on chapters.id_subject = subjects.id
                where chapters.id=:id");
            $query->execute(['id' => $id]);
        } catch (Throwable $e) {
            return null;
        }
        return $query->fetch();
    }

    public function edit($data, $id)
    {
        return $this->ChapterModel->update($data, $id);
    }
    public function delete($id)
    {
        return $this->ChapterModel->delete($id);
    }
}