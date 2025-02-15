<?php
// File: parrainages/get_parrainage_period.php

require_once '../config/config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Gestion de la requête OPTIONS (pré-vol CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Récupérer la dernière période définie
$stmt = $pdo->query("SELECT * FROM periode_parrainage ORDER BY created_at DESC LIMIT 1");
$period = $stmt->fetch(PDO::FETCH_ASSOC);

if ($period) {
    // Calculer le statut en fonction de la date actuelle
    $current_date = date('Y-m-d');
    if ($current_date < $period['date_debut']) {
        $status = "pending";
    } elseif ($current_date > $period['date_fin']) {
        $status = "closed";
    } else {
        $status = "active";
    }
    $period["status"] = $status;
    echo json_encode($period);
} else {
    echo json_encode(["success" => false, "message" => "Aucune période de parrainage définie."]);
}
?>
