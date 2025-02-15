<?php
// File: electeurs/validate_list.php

require '../config/config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Vérifier s’il y a des erreurs d’importation
    $errors_count = $pdo->query("SELECT COUNT(*) FROM electeurs_erreurs")->fetchColumn();
    if ($errors_count > 0) {
        echo json_encode([
            "success" => false,
            "message" => "Impossible de valider, il y a des erreurs dans l'importation."
        ]);
        exit;
    }

    // 2. Lancement de la transaction
    $pdo->beginTransaction();

    // 3. Transfert des données validées depuis electeurs_temp vers electeurs
    //    Adaptez la liste des colonnes selon votre table electeurs.
    //    Ci-dessous, on suppose que electeurs_temp possède exactement ces colonnes :
    //    cni, numeroElecteur, prenom, nom, date_naissance, sexe, taille, lieu_naissance,
    //    date_delivrance, date_expiration, centre_enregistrement, adresse_domicile, codepays,
    //    region, departement, commune, lieu_vote, bureau_vote
    $stmt = $pdo->prepare("
        INSERT INTO electeurs (
            cni, numeroElecteur, prenom, nom, date_naissance, sexe, taille, lieu_naissance,
            date_delivrance, date_expiration, centre_enregistrement, adresse_domicile,
            codepays, region, departement, commune, lieu_vote, bureau_vote
        )
        SELECT
            cni, numeroElecteur, prenom, nom, date_naissance, sexe, taille, lieu_naissance,
            date_delivrance, date_expiration, centre_enregistrement, adresse_domicile,
            codepays, region, departement, commune, lieu_vote, bureau_vote
        FROM electeurs_temp
    ");

    if ($stmt->execute()) {
        // 4. Suppression des données temporaires
        $pdo->query("DELETE FROM electeurs_temp");
        $pdo->commit();
        echo json_encode([
            "success" => true,
            "message" => "Importation validée avec succès."
        ]);
    } else {
        // 5. En cas d’échec
        $pdo->rollBack();
        echo json_encode([
            "success" => false,
            "message" => "Erreur lors de la validation."
        ]);
    }
}
?>
