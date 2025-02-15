<?php

require '../config/config.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$stmt = $pdo->query("SELECT * FROM candidats ORDER BY created_at DESC");
$candidats = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($candidats);
?>
