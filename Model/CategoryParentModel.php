<?php
include_once __DIR__ . '/../Model/BaseModel.php';
class CategoryParentModel
{
    protected $table;
    protected $CategoryParentModel;
    protected $conn;

    public function __construct()
    {
        $this->table = 'categoryparent';
        $this->conn = ConnectionDB::GetConnect();
        $this->CategoryParentModel = new BaseModel($this->table);
    }
    public function index()
    {
        return $this->CategoryParentModel->index();
    }

    public function create($data)
    {
        return $this->CategoryParentModel->create($data);
    }

    public function delete($id)
    {
        return $this->CategoryParentModel->delete($id);
    }

    public function detail($slug)
    {
        try {
            $query = $this->conn->prepare("select * from $this->table where slug=:slug and status = false");
            $query->execute(['slug' => $slug]);
        } catch (Throwable $e) {
            return null;
        }
        return $query->fetch();
    }
    public function detailById($id)
    {
        return $this->CategoryParentModel->read($id);
    }

    public function edit($data, $id)
    {
        return $this->CategoryParentModel->update($data, $id);
    }
}