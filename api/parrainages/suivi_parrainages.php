<?php
// File: candidats/suivi_parrainages.php

require_once '../config/config.php';
require_once '../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Récupération du token depuis l'en-tête Authorization
$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    echo json_encode(["success" => false, "message" => "Token manquant."]);
    exit();
}
$authHeader = $headers['Authorization'];
$token = trim(preg_replace('/Bearer\s/', '', $authHeader));
$secret_key = "votre_cle_secrete";

try {
    $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Token invalide.", "error" => $e->getMessage()]);
    exit();
}

$candidateId = $decoded->data->id;

// Récupérer l'évolution journalière des parrainages pour ce candidat
$query = "SELECT DATE(created_at) AS date, COUNT(*) AS count 
          FROM parrainages 
          WHERE candidat_id = :candidateId 
          GROUP BY DATE(created_at) 
          ORDER BY date ASC";
$stmt = $pdo->prepare($query);
$stmt->execute([":candidateId" => $candidateId]);
$dailyCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer le nombre total de parrainages
$queryTotal = "SELECT COUNT(*) AS total 
               FROM parrainages 
               WHERE candidat_id = :candidateId";
$stmtTotal = $pdo->prepare($queryTotal);
$stmtTotal->execute([":candidateId" => $candidateId]);
$totalRow = $stmtTotal->fetch(PDO::FETCH_ASSOC);
$total = $totalRow['total'];

echo json_encode([
    "success" => true,
    "total_parrainages" => (int)$total,
    "evolution_daily" => $dailyCounts
]);
?>
