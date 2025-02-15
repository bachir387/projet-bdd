<?php
// File: dashboard_stats.php

require_once '../config/config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Récupérer le nombre total de parrains
    $stmt = $pdo->query("SELECT COUNT(*) AS total_parrains FROM parrains");
    $totalParrains = $stmt->fetch(PDO::FETCH_ASSOC)['total_parrains'];

    // Récupérer le nombre total de candidats
    $stmt = $pdo->query("SELECT COUNT(*) AS total_candidates FROM candidats");
    $totalCandidates = $stmt->fetch(PDO::FETCH_ASSOC)['total_candidates'];

    // Récupérer le nombre de parrainages par candidat
    // On effectue une jointure pour obtenir le nom et prénom du candidat
    $stmt = $pdo->query("
        SELECT c.id, c.nom, c.prenom, COUNT(p.id) AS total_parrainages
        FROM candidats c
        LEFT JOIN parrainages p ON c.id = p.candidat_id
        GROUP BY c.id, c.nom, c.prenom
        ORDER BY c.nom ASC
    ");
    $sponsorshipStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "total_parrains" => $totalParrains,
        "total_candidates" => $totalCandidates,
        "sponsorship_stats" => $sponsorshipStats
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur lors de la récupération des statistiques: " . $e->getMessage()
    ]);
}
?>
