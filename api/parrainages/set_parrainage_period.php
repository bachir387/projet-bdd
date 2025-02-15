<?php
// File: parrainages/set_parrainage_period.php

require_once '../config/config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Gestion de la requête OPTIONS (pré-vol CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Lecture des données JSON envoyées dans le corps de la requête
$data = json_decode(file_get_contents("php://input"));

// Vérification de la présence des dates
if (!isset($data->date_debut) || !isset($data->date_fin)) {
    echo json_encode(["success" => false, "message" => "Les dates de début et de fin sont requises."]);
    exit();
}

$date_debut = $data->date_debut;
$date_fin   = $data->date_fin;

// Vérifier que la date de début est au moins 6 mois après aujourd'hui
$current_date = date('Y-m-d');
$min_start_date = date('Y-m-d', strtotime('+6 months', strtotime($current_date)));
if ($date_debut < $min_start_date) {
    echo json_encode(["success" => false, "message" => "La date de début doit être au moins 6 mois après aujourd'hui."]);
    exit();
}

// Vérifier que la date de fin est supérieure à la date de début
if ($date_fin <= $date_debut) {
    echo json_encode(["success" => false, "message" => "La date de fin doit être supérieure à la date de début."]);
    exit();
}

// Vérifier s'il existe déjà une période de parrainage
$stmt = $pdo->query("SELECT COUNT(*) as count FROM periode_parrainage");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if ($result['count'] > 0) {
    echo json_encode(["success" => false, "message" => "Une période de parrainage est déjà définie."]);
    exit();
}

// Insertion de la nouvelle période
$stmt = $pdo->prepare("INSERT INTO periode_parrainage (date_debut, date_fin) VALUES (:date_debut, :date_fin)");
$insertSuccess = $stmt->execute([
    ":date_debut" => $date_debut,
    ":date_fin"   => $date_fin
]);

if ($insertSuccess) {
    echo json_encode(["success" => true, "message" => "Période de parrainage enregistrée avec succès."]);
} else {
    echo json_encode(["success" => false, "message" => "Erreur lors de l'enregistrement de la période de parrainage."]);
}
?>
