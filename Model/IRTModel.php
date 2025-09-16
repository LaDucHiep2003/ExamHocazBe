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
            ea.attempt_id,
            att.user_id,
            ea.question_id,
            CASE 
                WHEN qo.is_correct = 1 AND ea.option_id = qo.id THEN 1
                ELSE 0
            END AS is_correct
        FROM exam_answers ea
        INNER JOIN exam_attempts att ON ea.attempt_id = att.id
        INNER JOIN questions q ON ea.question_id = q.id
        INNER JOIN question_options qo ON ea.option_id = qo.id
    ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Tạo mảng pivot: user_id → [Qid => 0/1]
        $matrix = [];
        foreach ($rows as $row) {
            $uid = $row['user_id'];
            $qid = 'Q' . $row['question_id'];
            if (!isset($matrix[$uid])) {
                $matrix[$uid] = [];
            }
            // Nếu user có nhiều đáp án cho 1 câu hỏi, chỉ cần 1 cái đúng là đúng
            $matrix[$uid][$qid] = max(isset($matrix[$uid][$qid]) ? $matrix[$uid][$qid] : 0, (int)$row['is_correct']);
        }

        // Lấy danh sách tất cả các câu hỏi
        $questionIds = [];
        foreach ($rows as $row) {
            $questionIds['Q' . $row['question_id']] = true;
        }
        $questionKeys = array_keys($questionIds);

        // Chuẩn hóa: user nào cũng có đủ cột câu hỏi
        foreach ($matrix as $uid => &$answers) {
            foreach ($questionKeys as $qid) {
                if (!isset($answers[$qid])) {
                    $answers[$qid] = 0;
                }
            }
            ksort($answers); // Giữ thứ tự cột
            $answers = array_merge(['user_id' => $uid], $answers);
        }

        return array_values($matrix);
    }


    public function getInformationStudent()
    {
        // 1. Lấy ma trận đáp án (pivot user x question)
        $results = $this->getMatrixAnswers();
        if (empty($results)) {
            return [];
        }

        // 2. Ghi ra file CSV cho R xử lý
        $csvPath = __DIR__ . "/../responses.csv";
        $fp = fopen($csvPath, 'w');
        fputcsv($fp, array_keys($results[0])); // header
        foreach ($results as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);

        // 3. Gọi script R để tính toán IRT
        $rScript = __DIR__ . "/../irt_model.R";
        $command = '"C:\\Program Files\\R\\R-4.5.0\\bin\\Rscript.exe" ' . escapeshellarg($rScript);
        shell_exec($command);

        // 4. Đọc file JSON kết quả
        $jsonPath = __DIR__ . "/../theta.json";
        if (!file_exists($jsonPath)) {
            return ["error" => "Không tìm thấy file kết quả từ R"];
        }
        $jsonData = file_get_contents($jsonPath);
        $thetaList = json_decode($jsonData, true);
        if (empty($thetaList)) return [];

        // 5. Lấy thêm thông tin user + lần thi gần nhất
        $userIds = array_column($thetaList, 'user_id');
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));

        $sql = "
        SELECT u.id AS user_id, u.full_name, u.email, MAX(a.start_time) AS latest_attempt
        FROM users u
        JOIN exam_attempts a ON u.id = a.user_id
        WHERE u.id IN ($placeholders)
        GROUP BY u.id, u.full_name, u.email
    ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($userIds);
        $userInfoList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $userInfoMap = [];
        foreach ($userInfoList as $info) {
            $userInfoMap[$info['user_id']] = $info;
        }

        // 6. Gộp kết quả cuối cùng
        $final = [];
        foreach ($thetaList as $row) {
            $uid = $row['user_id'];
            $theta = $row['theta'];

            $user = isset($userInfoMap[$uid])
                ? $userInfoMap[$uid]
                : ['name' => '', 'email' => '', 'latest_attempt' => null];

            $final[] = [
                'user_id'       => $uid,
                'full_name'          => $user['full_name'],
                'email'         => $user['email'],
                'latest_attempt'=> $user['latest_attempt'],
                'theta'         => $theta,
                'performance'       => $this->classifyHocLuc($theta)
            ];
        }

        return $final;
    }
    private function classifyHocLuc($theta)
    {
        if ($theta >= 1.0) return 'excellent';
        if ($theta >= 0.0) return 'good';
        if ($theta >= -1.0) return 'average';
        return 'poor';
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