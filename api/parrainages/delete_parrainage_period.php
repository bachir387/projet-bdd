<?php
// File: parrainages/delete_parrainage_period.php

require_once '../config/config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Gestion de la requête OPTIONS (pré-vol CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Suppression de la période de parrainage (tous les enregistrements)
$stmt = $pdo->query("DELETE FROM periode_parrainage");

if ($stmt) {
    echo json_encode(["success" => true, "message" => "Période de parrainage supprimée."]);
} else {
    echo json_encode(["success" => false, "message" => "Erreur lors de la suppression de la période de parrainage."]);
}
?>
