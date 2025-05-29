<?php

class IRTModel
{
    protected $table;
    protected $IRTModel;
    protected $conn;

    public function __construct()
    {
        $this->table = 'users';
        $this->IRTModel = new BaseModel($this->table);
        $this->conn = ConnectionDB::GetConnect();
    }
    public function getMatrixAnswers()
    {
        $sql = "
            SELECT 
            r.id_user,
            rd.id_question,
            CASE 
                WHEN rd.answer = q.correct_answer THEN 1
                ELSE 0
            END AS is_correct
            FROM result_detail rd
            JOIN questions q ON rd.id_question = q.id
            JOIN results r ON rd.id_result = r.id
    ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Tạo mảng pivot
        $matrix = [];

        foreach ($rows as $row) {
            $id = $row['id_user'];
            $qid = 'Q' . $row['id_question'];
            $matrix[$id][$qid] = (int) $row['is_correct'];
        }
        // Chuẩn hóa để mỗi user có đủ tất cả các câu hỏi
        $questionIds = [];
        foreach ($rows as $row) {
            $questionIds['Q' . $row['id_question']] = true;
        }
        $questionKeys = array_keys($questionIds);
        foreach ($matrix as $id => &$answers) {
            foreach ($questionKeys as $qid) {
                if (!isset($answers[$qid])) {
                    $answers[$qid] = 0;
                }
            }
            ksort($answers); // Đảm bảo thứ tự cột
            $answers = array_merge(['id_user' => $id], $answers);
        }

        return array_values($matrix); // Reset chỉ số mảng
    }


    public function getInformationStudent()
    {
        $results = $this->getMatrixAnswers();
        if (empty($results)) {
            return [];
        }
        $csvPath = __DIR__ . "/../responses.csv";
        $fp = fopen($csvPath, 'w');

        fputcsv($fp, array_keys($results[0]));
        foreach ($results as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);
        $rScript = __DIR__ . "/../irt_model.R";
        //C:\Program Files\R\R-4.5.0\bin
        $command = '"C:\\Program Files\\R\\R-4.5.0\\bin\\Rscript.exe" ' . escapeshellarg($rScript);
        shell_exec($command);
        // 4. Đọc file kết quả JSON
        $jsonPath = __DIR__ . "/../theta.json";
        if (!file_exists($jsonPath)) {
            return ["error" => "Không tìm thấy file kết quả từ R"];
        }
        $jsonData = file_get_contents($jsonPath);
        $thetaList = json_decode($jsonData, true);
        if (empty($thetaList)) return [];

        // Lấy thêm thông tin người dùng và lần làm gần nhất
        $userIds = array_column($thetaList, 'user_id');
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $sql = "
            SELECT u.id AS user_id, u.name, u.email, MAX(r.created_at) AS latest_attempt
            FROM users u
            JOIN results r ON u.id = r.id_user
            WHERE u.id IN ($placeholders)
            GROUP BY u.id, u.name, u.email
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($userIds);
        $userInfoList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $userInfoMap = [];
        foreach ($userInfoList as $info) {
            $userInfoMap[$info['user_id']] = $info;
        }
        // Gộp thông tin
        $final = [];
        foreach ($thetaList as $row) {
            $uid = $row['user_id'];
            $theta = $row['theta'];
            $user = isset($userInfoMap[$uid]) ? $userInfoMap[$uid] : ['name' => '', 'email' => '', 'latest_attempt' => null];
            $final[] = [
                'user_id' => $uid,
                'name' => $user['name'],
                'email' => $user['email'],
                'latest_attempt' => $user['latest_attempt'],
                'theta' => $theta,
                'hoc_luc' => $this->classifyHocLuc($theta)
            ];
        }
        return $final;
    }
    private function classifyHocLuc($theta)
    {
        if ($theta >= 1.0) return 'Giỏi';
        if ($theta >= 0.0) return 'Khá';
        if ($theta >= -1.0) return 'Trung bình';
        return 'Yếu';
    }

    public function getInformationQuestion()
    {
        $results = $this->getMatrixAnswers();
        $csvPath = __DIR__ . "/../responses.csv";
        $fp = fopen($csvPath, 'w');
        fputcsv($fp, array_keys($results[0]));  // header
        foreach ($results as $row) {
            fputcsv($fp, $row);  // data
        }
        fclose($fp);
        $rScript = __DIR__ . "/../irt_question_model.R";
        $command = '"C:\\Program Files\\R\\R-4.5.0\\bin\\Rscript.exe" ' . escapeshellarg($rScript);
        shell_exec($command);
        $jsonPath = __DIR__ . "/../item_parameters.json";
        if (!file_exists($jsonPath)) {
            return ["error" => "Không tìm thấy file kết quả từ R"];
        }
        $jsonData = file_get_contents($jsonPath);
        $itemParams = json_decode($jsonData, true);
        foreach ($itemParams as &$item) {
            $item['quality'] = $this->classifyQuestion($item['a'], $item['b']);
        }
        if (empty($itemParams)) {
            return [];
        }
        return $itemParams;
    }

    function classifyQuestion($a, $b) {
        // Phân biệt
        if ($a < 0.5) {
            $discrimination = 'Ít phân biệt';
        } elseif ($a < 1) {
            $discrimination = 'Phân biệt trung bình';
        } else {
            $discrimination = 'Phân biệt tốt';
        }
        // Độ khó
        if ($b < -1) {
            $difficulty = 'Dễ';
        } elseif ($b > 1) {
            $difficulty = 'Khó';
        } else {
            $difficulty = 'Bình thường';
        }
        // Tổng hợp đánh giá
        if ($discrimination === 'Ít phân biệt') {
            return 'Câu hỏi kém chất lượng (ít phân biệt)';
        }
        if ($difficulty === 'Khó' && $discrimination === 'Phân biệt tốt') {
            return 'Câu hỏi khó & phân biệt tốt';
        }
        if ($difficulty === 'Dễ' && $discrimination === 'Phân biệt tốt') {
            return 'Câu hỏi dễ & phân biệt tốt';
        }
        return 'Câu hỏi bình thường';
    }

}