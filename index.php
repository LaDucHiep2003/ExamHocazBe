<?php
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost:5173'));
$origin = str_replace("http://", "", $origin);
$origin = str_replace("https://", "", $origin);
$tmp = explode("/", $origin);
$origin = $tmp[0];
$origin = "http://" . $origin;
// header("Access-Control-Allow-Origin: $origin");
// header("Access-Control-Allow-Credentials: true");
// header('Access-Control-Allow-Headers: Authorization');
header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS,PUT,PATCH");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");
// header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');
// header('Access-Control-Allow-Credentials: true');
// header("Content-Type: multipart/form-data");
// header("Content-Type: image/png"); // Adjust as needed for other image types
//
include_once __DIR__ . '../Routes/router.php';
//$servername = "localhost";
//$username = "root";
//$password = "090503";
//$dbname = "examProject";
//
//try {
//    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
//    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//    echo "Connected successfully";
//} catch (PDOException $e) {
//    echo "Connection failed: " . $e->getMessage();
//}