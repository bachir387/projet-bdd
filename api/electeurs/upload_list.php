<?php
// File: electeurs/upload_list.php

require '../config/config.php';         // Connexion à la BDD, etc.
// require '../auth/validate_token.php'; // Optionnel, si vous sécurisez l'upload par un token

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ----------------------------------------------------------------------
// 1. Vérifier la variable globale EtatUploadElecteurs dans la base
//    On suppose qu'on a une table global_settings(setting_key, setting_value).
// ----------------------------------------------------------------------
$stmt = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key = 'EtatUploadElecteurs'");
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$EtatUploadElecteurs = ($result && $result['setting_value'] === 'true');

if ($EtatUploadElecteurs) {
    echo json_encode(["success" => false, "message" => "Un upload a déjà été effectué. Impossible d'en faire un autre."]);
    exit();
}

// ----------------------------------------------------------------------
// 2. Vérifier la présence du fichier et le checksum
// ----------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_FILES["file"])) {
        echo json_encode(["success" => false, "message" => "Aucun fichier envoyé."]);
        exit;
    }

    $file = $_FILES["file"]["tmp_name"];
    $checksum_input = $_POST["checksum"] ?? "";

    // Vérification du checksum SHA256
    $checksum_actual = hash_file("sha256", $file);
    if ($checksum_input !== $checksum_actual) {
        echo json_encode(["success" => false, "message" => "Checksum invalide."]);
        exit;
    }

    // ----------------------------------------------------------------------
    // 3. Lecture du fichier CSV
    //    On suppose que le CSV contient, dans l'ordre :
    //    0:cni, 1:numeroElecteur, 2:prenom, 3:nom, 4:date_naissance, 5:sexe, 6:taille,
    //    7:lieu_naissance, 8:date_delivrance, 9:date_expiration, 10:centre_enregistrement,
    //    11:adresse_domicile, 12:codepays, 13:region, 14:departement, 15:commune,
    //    16:lieu_vote, 17:bureau_vote
    // ----------------------------------------------------------------------
    $handle = fopen($file, "r");
    if (!$handle) {
        echo json_encode(["success" => false, "message" => "Erreur lors de l'ouverture du fichier."]);
        exit;
    }

    // Ignorer la première ligne (en-têtes)
    fgetcsv($handle);

    $uploadSuccess = true;    // Pour savoir si tout se passe bien
    $uploadMessage = "Importation terminée."; // Message final
    $users_id = 1;            // ID du membre de la DGE (à adapter selon votre logique)
    $pdo->beginTransaction();

    while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
        // Récupération des champs
        $cni                = trim($data[0]);
        $numeroElecteur     = trim($data[1]);
        $prenom             = trim($data[2]);
        $nom                = trim($data[3]);
        $date_naissance     = trim($data[4]);
        $sexe               = trim($data[5]);
        $taille             = trim($data[6]);
        $lieu_naissance     = trim($data[7]);
        $date_delivrance    = trim($data[8]);
        $date_expiration    = trim($data[9]);
        $centre_enregistrement = trim($data[10]);
        $adresse_domicile   = trim($data[11]);
        $codepays           = trim($data[12]);
        $region             = trim($data[13]);
        $departement        = trim($data[14]);
        $commune            = trim($data[15]);
        $lieu_vote          = trim($data[16]);
        $bureau_vote        = trim($data[17]);

        // ----------------------------------------------------------------------
        // 4. Vérifications des données (conformément aux contraintes)
        // ----------------------------------------------------------------------
        // Vérification CNI (13 chars, commence par '1' si sexe=M, '2' si sexe=F)
        if (!preg_match("/^\d{13}$/", $cni)) {
            insertErreur($pdo, $cni, $numeroElecteur, "CNI invalide (13 chiffres attendus)", time());
            continue;
        }
        if (($sexe === 'M' && $cni[0] !== '1') || ($sexe === 'F' && $cni[0] !== '2')) {
            insertErreur($pdo, $cni, $numeroElecteur, "CNI et sexe non cohérents", time());
            continue;
        }

        // Vérification numeroElecteur (9 chiffres)
        if (!preg_match("/^\d{9}$/", $numeroElecteur)) {
            insertErreur($pdo, $cni, $numeroElecteur, "Numéro Electeur invalide (9 chiffres attendus)", time());
            continue;
        }

        // Vérification sexe
        if (!in_array($sexe, ["M", "F"])) {
            insertErreur($pdo, $cni, $numeroElecteur, "Sexe invalide (M ou F)", time());
            continue;
        }

        // Vérification taille (DECIMAL(4,1))
        if (!preg_match("/^\d{1,3}(\.\d)?$/", $taille)) {
            insertErreur($pdo, $cni, $numeroElecteur, "Taille invalide (DECIMAL(4,1))", time());
            continue;
        }

        // (Vous pouvez ajouter d'autres vérifications pour date_delivrance, date_expiration, etc.)

        // ----------------------------------------------------------------------
        // 5. Insertion dans la table temporaire (ou directement dans electeurs ?)
        //    Ici, on suppose que vous avez "electeurs_temp" avec les mêmes colonnes
        // ----------------------------------------------------------------------
        $stmt = $pdo->prepare("INSERT INTO electeurs_temp 
            (cni, numeroElecteur, prenom, nom, date_naissance, sexe, taille, lieu_naissance,
             date_delivrance, date_expiration, centre_enregistrement, adresse_domicile, codepays,
             region, departement, commune, lieu_vote, bureau_vote)
        VALUES
            (:cni, :numeroElecteur, :prenom, :nom, :date_naissance, :sexe, :taille, :lieu_naissance,
             :date_delivrance, :date_expiration, :centre_enregistrement, :adresse_domicile, :codepays,
             :region, :departement, :commune, :lieu_vote, :bureau_vote)");

        try {
            $stmt->execute([
                ":cni"                  => $cni,
                ":numeroElecteur"       => $numeroElecteur,
                ":prenom"               => $prenom,
                ":nom"                  => $nom,
                ":date_naissance"       => $date_naissance,
                ":sexe"                 => $sexe,
                ":taille"               => $taille,
                ":lieu_naissance"       => $lieu_naissance,
                ":date_delivrance"      => $date_delivrance,
                ":date_expiration"      => $date_expiration,
                ":centre_enregistrement"=> $centre_enregistrement,
                ":adresse_domicile"     => $adresse_domicile,
                ":codepays"             => $codepays ?: 'SEN', // Valeur par défaut
                ":region"               => $region,
                ":departement"          => $departement,
                ":commune"              => $commune,
                ":lieu_vote"            => $lieu_vote,
                ":bureau_vote"          => $bureau_vote
            ]);
        } catch (Exception $e) {
            insertErreur($pdo, $cni, $numeroElecteur, "Erreur SQL: " . $e->getMessage(), time());
            continue;
        }
    }

    fclose($handle);
    $pdo->commit();

    // ----------------------------------------------------------------------
    // 6. Insertion dans la table uploads
    //    On suppose qu'on a un user_id=1 (à adapter), un état 'succès'
    // ----------------------------------------------------------------------
    $uploadSuccess = true;  // si on n'a pas rollback
    $uploadEtat = $uploadSuccess ? 'succès' : 'échec';
    $uploadMessage = $uploadSuccess ? "Importation terminée." : "Importation avec erreurs.";

    $stmtUp = $pdo->prepare("INSERT INTO uploads (users_id, fichier_nom, checksum, adresse_ip, etat, message)
                             VALUES (:users_id, :fichier_nom, :checksum, :adresse_ip, :etat, :message)");
    $stmtUp->execute([
        ":users_id"     => $users_id,
        ":fichier_nom"  => $_FILES["file"]["name"],
        ":checksum"     => $checksum_actual,
        ":adresse_ip"   => $_SERVER['REMOTE_ADDR'],
        ":etat"         => $uploadEtat,
        ":message"      => $uploadMessage
    ]);

    // ----------------------------------------------------------------------
    // 7. Si succès, on met EtatUploadElecteurs à true dans global_settings
    // ----------------------------------------------------------------------
    if ($uploadSuccess) {
        $stmtSet = $pdo->prepare("UPDATE global_settings 
                                  SET setting_value = 'true' 
                                  WHERE setting_key = 'EtatUploadElecteurs'");
        $stmtSet->execute();
    }

    echo json_encode(["success" => $uploadSuccess, "message" => $uploadMessage]);
}

// Fonction pour enregistrer les erreurs d'importation
function insertErreur($pdo, $cni, $numeroElecteur, $erreur, $upload_attempt) {
    $stmt = $pdo->prepare("INSERT INTO electeurs_erreurs (numero_cin, numero_electeur, erreur, upload_attempt) 
                           VALUES (:cni, :numeroElecteur, :erreur, :upload_attempt)");
    $stmt->execute([
        ":cni"            => $cni,
        ":numeroElecteur" => $numeroElecteur,
        ":erreur"         => $erreur,
        ":upload_attempt" => $upload_attempt
    ]);
}
?>
