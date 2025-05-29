<?php
class ConnectionDB
{
    public static function loadEnv($path)
    {
        if (!file_exists($path)) {
            throw new Exception("File .env not found: $path");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Bỏ qua dòng bắt đầu bằng dấu #
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Tách key và value
            list($key, $value) = explode('=', $line, 2);

            // Loại bỏ các khoảng trắng
            $key = trim($key);
            $value = trim($value);

            // Gán biến môi trường
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
    public static function GetConnect()
    {
        // đọc file env
        self::loadEnv(__DIR__ . '/../.env');
        $dbConnection = getenv('DB_CONNECTION');
        $dbHost = getenv('DB_HOST');
        $dbName = getenv('DB_DATABASE');
        $dbUser = getenv('DB_USERNAME');
        $dbPass = getenv('DB_PASSWORD');
        try {
            $conn = new PDO("$dbConnection:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            $conn->exec("set names utf8");
        } catch (Throwable $e) {
            echo json_encode("Error in connect database".$e);
        }
        return $conn;
    }
}