<?php
// File: candidats/get_candidate.php

require_once '../config/config.php';

// Ajout des en-têtes CORS pour autoriser les requêtes cross-origin
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Gestion de la requête OPTIONS (pré-vol CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Vérification de la présence du paramètre "id" dans l'URL
if (!isset($_GET['id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Identifiant du candidat requis."
    ]);
    exit();
}

$candidateId = $_GET['id'];

try {
    // Récupération des détails du candidat en fonction de l'identifiant
    $stmt = $pdo->prepare("SELECT * FROM candidats WHERE id = :id");
    $stmt->execute([":id" => $candidateId]);
    $candidate = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($candidate) {
        echo json_encode($candidate);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Candidat introuvable."
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur lors de la récupération du candidat.",
        "error"   => $e->getMessage()
    ]);
}
?>
