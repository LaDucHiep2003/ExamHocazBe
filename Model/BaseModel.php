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
    // lấy dữ liệu
    public function index()
    {
        // phân trang
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        // lấy tổng số bản ghi trong table
        $count_query = $this->conn->prepare("SELECT COUNT(*) as total from $this->table where status = false");
        $count_query->execute();
        $record_total = $count_query->fetch(PDO::FETCH_ASSOC)['total'];
        // tổng số trang
        // ceil hàm lấy phần nguyên
        $page_total = ceil($record_total / $limit);
        // lấy danh sách có phân trang
        $query = $this->conn->prepare("select * from $this->table  where status = false LIMIT :limit OFFSET :offset");
        // $query = $this->conn->prepare("select * from $this->table");
        // gán các giá trị nguyên cho limit và offset
        $query->bindParam(':limit', $limit, PDO::PARAM_INT);
        $query->bindParam(':offset', $offset, PDO::PARAM_INT);
        // $query->execute([':limit' => (int)$limit, ':offset' => (int)$offset]);
        $query->execute();
        return ['data' => $query->fetchAll(), 'limit' => $limit, 'current_page' => $page, 'total_page' => $page_total, 'record_total' => $record_total];
        // return $query->fetchAll();

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
            $query = $this->conn->prepare("select * from $this->table where id=:id and status = false");
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
                set status = true 
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