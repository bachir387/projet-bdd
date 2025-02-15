<?php
// File: candidats/generate_code.php

require_once '../config/config.php';
require_once '../utils/send_email.php';

// Définition des en-têtes CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Gestion de la requête OPTIONS (pré-vol CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Lecture des données envoyées (attendu un JSON avec l'ID du candidat)
$data = json_decode(file_get_contents("php://input"));
if (!isset($data->candidateId)) {
    echo json_encode([
        "success" => false,
        "message" => "Données incomplètes: 'candidateId' requis."
    ]);
    exit();
}

$candidateId = $data->candidateId;

// Récupérer les informations du candidat dans la base
$stmt = $pdo->prepare("SELECT id, email FROM candidats WHERE id = :id");
$stmt->execute([":id" => $candidateId]);
$candidate = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$candidate) {
    echo json_encode([
        "success" => false,
        "message" => "Candidat introuvable."
    ]);
    exit();
}

// Générer un nouveau code d'authentification (par exemple, un code à 6 chiffres)
$newCode = strval(rand(100000, 999999));

// Mettre à jour le code d'authentification dans la table candidats
$updateStmt = $pdo->prepare("UPDATE candidats SET code_auth = :code_auth WHERE id = :id");
$updateSuccess = $updateStmt->execute([
    ":code_auth" => $newCode,
    ":id" => $candidateId
]);

if ($updateSuccess) {
    // Envoyer le nouveau code par email au candidat
    sendEmail($candidate["email"], "Nouveau code d'authentification", "Votre nouveau code d'authentification est : $newCode");

    echo json_encode([
        "success" => true,
        "newCode" => $newCode,
        "message" => "Nouveau code généré et envoyé par email."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Erreur lors de la mise à jour du code d'authentification."
    ]);
}
?>
