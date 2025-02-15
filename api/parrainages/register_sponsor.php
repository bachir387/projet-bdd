<?php
// File: parrainages/register_sponsor.php

require_once '../config/config.php';
require_once '../utils/send_email.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Lecture des données JSON envoyées
$data = json_decode(file_get_contents("php://input"));

// ----------------------------------------------------------------------
// 1. Vérifier la période de parrainage :
//    Les parrains ne peuvent s’inscrire qu’après le début de la période
// ----------------------------------------------------------------------
$stmtPeriode = $pdo->query("SELECT date_debut, date_fin FROM periode_parrainage ORDER BY created_at DESC LIMIT 1");
$periode = $stmtPeriode->fetch(PDO::FETCH_ASSOC);
if (!$periode) {
    echo json_encode([
        "success" => false,
        "message" => "Aucune période de parrainage n'est définie."
    ]);
    exit();
}

$currentDate = date('Y-m-d');
// On suppose que la période est active si currentDate >= date_debut et <= date_fin
if ($currentDate < $periode['date_debut']) {
    // Période pas encore commencée => on bloque
    echo json_encode([
        "success" => false,
        "message" => "La période de parrainage n'a pas encore commencé. Impossible de s'inscrire comme parrain."
    ]);
    exit();
}
if ($currentDate > $periode['date_fin']) {
    // Période terminée => on bloque
    echo json_encode([
        "success" => false,
        "message" => "La période de parrainage est terminée. Impossible de s'inscrire comme parrain."
    ]);
    exit();
}

// ----------------------------------------------------------------------
// 2. Vérifier que toutes les informations requises sont fournies
// ----------------------------------------------------------------------
if (
    !isset($data->numero_electeur, $data->numero_cin, $data->nom, $data->bureau_vote, 
           $data->prenom, $data->telephone, $data->email)
) {
    echo json_encode([
        "success" => false,
        "message" => "Veuillez saisir toutes les informations requises : numéro d’électeur, numéro de CIN, nom, prénom, bureau de vote, téléphone et email."
    ]);
    exit();
}

$numero_electeur = trim($data->numero_electeur);
$numero_cin      = trim($data->numero_cin);
$nom             = trim($data->nom);
$prenom          = trim($data->prenom);
$bureau_vote     = trim($data->bureau_vote);
$telephone       = trim($data->telephone);
$email           = trim($data->email);

// ----------------------------------------------------------------------
// 3. Vérifier que l'électeur figure dans la table electeurs
// ----------------------------------------------------------------------
$stmt = $pdo->prepare("SELECT * FROM electeurs WHERE numeroElecteur = :numero_electeur AND cni = :numero_cin");
$stmt->execute([
    ":numero_electeur" => $numero_electeur,
    ":numero_cin"      => $numero_cin
]);
$electeur = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$electeur) {
    echo json_encode([
        "success" => false,
        "message" => "Les informations fournies ne correspondent pas à un électeur inscrit."
    ]);
    exit();
}

// ----------------------------------------------------------------------
// 4. Vérifier que le téléphone ou l'email ne sont pas déjà utilisés par un autre parrain
// ----------------------------------------------------------------------
$stmt = $pdo->prepare("SELECT * FROM parrains WHERE telephone = :telephone OR email = :email");
$stmt->execute([
    ":telephone" => $telephone,
    ":email"     => $email
]);
if ($stmt->fetch()) {
    echo json_encode([
        "success" => false,
        "message" => "Le téléphone ou l'email est déjà utilisé par un autre parrain."
    ]);
    exit();
}

// ----------------------------------------------------------------------
// 5. Vérifier qu'il n'existe pas déjà un parrain pour ce numéro d'électeur
// ----------------------------------------------------------------------
$stmt = $pdo->prepare("SELECT * FROM parrains WHERE numero_electeur = :numero_electeur");
$stmt->execute([":numero_electeur" => $numero_electeur]);
if ($stmt->fetch()) {
    echo json_encode([
        "success" => false,
        "message" => "Ce parrain est déjà enregistré."
    ]);
    exit();
}

// ----------------------------------------------------------------------
// 6. Générer le code d'authentification (6 chiffres)
// ----------------------------------------------------------------------
$code_auth = strval(rand(100000, 999999));

// ----------------------------------------------------------------------
// 7. Insérer le profil dans la table parrains
// ----------------------------------------------------------------------
$stmt = $pdo->prepare("INSERT INTO parrains (numero_electeur, numero_cin, nom, prenom, bureau_vote, telephone, email, code_auth)
                       VALUES (:numero_electeur, :numero_cin, :nom, :prenom, :bureau_vote, :telephone, :email, :code_auth)");
$insert = $stmt->execute([
    ":numero_electeur" => $numero_electeur,
    ":numero_cin"      => $numero_cin,
    ":nom"             => $nom,
    ":prenom"          => $prenom,
    ":bureau_vote"     => $bureau_vote,
    ":telephone"       => $telephone,
    ":email"           => $email,
    ":code_auth"       => $code_auth
]);

if ($insert) {
    // Envoi du code par email
    sendEmail($email, "Votre code d'authentification", "Votre code d'authentification est : $code_auth");
    echo json_encode([
        "success" => true,
        "message" => "Profil de parrain enregistré avec succès. Un code d'authentification a été envoyé sur votre email."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Erreur lors de l'enregistrement du profil de parrain."
    ]);
}
?>
