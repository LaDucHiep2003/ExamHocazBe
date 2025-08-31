<?php
include_once __DIR__ . "/../Connection/Connection.php";

class BaseModel
{
    protected $table;
    protected $conn;
    public function __construct($table)
    {
        $this->table = $table;
        $this->conn = ConnectionDB::GetConnect();
    }
    public function index($sql = null)
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;

        if ($sql === null) {
            $sql = "SELECT * FROM {$this->table} WHERE deleted = false";
        }

        // tổng số bản ghi (dùng subquery để đếm)
        $count_query = $this->conn->prepare("SELECT COUNT(*) as total FROM ($sql) AS sub");
        $count_query->execute();
        $record_total = $count_query->fetch(PDO::FETCH_ASSOC)['total'];
        $page_total = ceil($record_total / $limit);

        // query dữ liệu có phân trang
        $query = $this->conn->prepare("$sql LIMIT :limit OFFSET :offset");
        $query->bindParam(':limit', $limit, PDO::PARAM_INT);
        $query->bindParam(':offset', $offset, PDO::PARAM_INT);
        $query->execute();

        return [
            'data' => $query->fetchAll(PDO::FETCH_ASSOC),
            'limit' => $limit,
            'current_page' => $page,
            'total_page' => $page_total,
            'record_total' => $record_total
        ];
    }
    // create dữ liệu
    public function create($data)
    {
        // kiểm tra dữ liệu tránh truyền script vào input
        foreach ($data as $key => $value) {
            $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        // lấy tên cột từ data;
        $columns = implode(",", array_keys($data));
        // prepare giá trị truyền vào sql
        // lấy giá trị từ data
        $value = ":" . implode(",:", array_keys($data));
        // prepare query
        $query = $this->conn->prepare("insert into $this->table ($columns) values ($value) ");
        try {
            $query->execute($data);
        } catch (Throwable $e) {
            return false;
        }
        return true;
    }
    // read data
    public function read($id)
    {
        try {
            $query = $this->conn->prepare("select * from $this->table where id=:id and deleted = false");
            $query->execute(['id' => $id]);
        } catch (Throwable $e) {
            return null;
        }
        return $query->fetch();
    }
    // delete data
    public function delete($id)
    {
        try {
            $query = $this->conn->prepare("update $this->table
                set deleted = true 
                where id=:id");
            $query->execute(['id' => $id]);
        } catch (Throwable $e) {
            return false;
        }
        return true;
    }
    // update data
    public function update($data, $id)
    {
        // kiểm tra dữ liệu tránh truyền script vào input
        // foreach ($data as $key => $value) {
        //     $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        // }
        $string = "";
        $columns = implode(",", array_keys($data));
        $columns_set_name = explode(',', $columns);
        foreach ($columns_set_name as $row) {
            $string .= $row . '=:' . $row . ',';
        }
        $setClause = rtrim($string, ",");
        // ví dụ chuỗi string sẽ có dạng name=:name,....
        // echo $setClause;
        try {
            $query = $this->conn->prepare("update $this->table set $setClause where id=:id");
            $arrayId = ['id' => $id];
            //merge mảng để execute query
            $arrayData = array_merge($data, $arrayId);
            $query->execute($arrayData);
        } catch (Throwable $e) {
            return false;
            // echo json_encode($e);
        }
        return true;
        // echo json_encode(2);
    }
}