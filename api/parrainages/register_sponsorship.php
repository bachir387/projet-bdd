<?php
// File: parrainages/register_sponsorship.php

require_once '../config/config.php';
require_once '../utils/send_email.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

$data = json_decode(file_get_contents("php://input"));

// ----------------------------------------------------------------------
// 1. V�rifier la p�riode de parrainage :
//    Les parrains ne peuvent initier ni confirmer un parrainage qu'apr�s
//    le d�but et avant la fin de la p�riode
// ----------------------------------------------------------------------
$stmtPeriode = $pdo->query("SELECT date_debut, date_fin FROM periode_parrainage ORDER BY created_at DESC LIMIT 1");
$periode = $stmtPeriode->fetch(PDO::FETCH_ASSOC);
if (!$periode) {
    echo json_encode([
        "success" => false,
        "message" => "Aucune p�riode de parrainage n'est d�finie."
    ]);
    exit();
}

$currentDate = date('Y-m-d');
if ($currentDate < $periode['date_debut']) {
    echo json_encode([
        "success" => false,
        "message" => "La p�riode de parrainage n'a pas encore commenc�. Impossible de parrainer."
    ]);
    exit();
}
if ($currentDate > $periode['date_fin']) {
    echo json_encode([
        "success" => false,
        "message" => "La p�riode de parrainage est termin�e. Impossible de parrainer."
    ]);
    exit();
}

// ----------------------------------------------------------------------
// 2. V�rifier l'identit� du parrain (phase 1)
// ----------------------------------------------------------------------
if (!isset($data->numero_electeur, $data->numero_cin, $data->code_auth)) {
    echo json_encode([
        "success" => false,
        "message" => "Veuillez saisir votre num�ro d'�lecteur, votre num�ro de CIN et votre code d'authentification."
    ]);
    exit();
}

$numero_electeur = trim($data->numero_electeur);
$numero_cin      = trim($data->numero_cin);
$code_auth       = trim($data->code_auth);

// V�rifier l'identit� du parrain (jointure avec electeurs pour obtenir date_naissance, etc.)
$stmt = $pdo->prepare("
    SELECT p.id AS parrain_id, p.nom, p.prenom, e.date_naissance, p.bureau_vote, p.email
    FROM parrains p
    LEFT JOIN electeurs e ON p.numero_electeur = e.numeroElecteur
    WHERE p.numero_electeur = :numero_electeur
      AND p.numero_cin = :numero_cin
      AND p.code_auth = :code_auth
");
$stmt->execute([
    ":numero_electeur" => $numero_electeur,
    ":numero_cin"      => $numero_cin,
    ":code_auth"       => $code_auth
]);
$parrain = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$parrain) {
    echo json_encode([
        "success" => false,
        "message" => "Informations de parrain incorrectes."
    ]);
    exit();
}

// ----------------------------------------------------------------------
// 3. Phase de v�rification (aucun candidate_id n\u2019est fourni) => on renvoie
//    les infos du parrain pour affichage
// ----------------------------------------------------------------------
if (!isset($data->candidate_id)) {
    echo json_encode([
        "success" => true,
        "message" => "Identit� v�rifi�e.",
        "data" => [
            "nom"           => $parrain["nom"],
            "prenom"        => $parrain["prenom"],
            "date_naissance"=> $parrain["date_naissance"],
            "bureau_vote"   => $parrain["bureau_vote"],
            "email"         => $parrain["email"]
        ]
    ]);
    exit();
}

// ----------------------------------------------------------------------
// 4. Phase 2 : Enregistrement du parrainage
//    Le parrain fournit un candidate_id
// ----------------------------------------------------------------------
$candidate_id = $data->candidate_id;

// Si le champ sponsorship_code n'est pas fourni, on initie le parrainage
if (!isset($data->sponsorship_code)) {
    // V�rifier que le parrain n'a pas d�j� parrain�
    $stmt = $pdo->prepare("SELECT * FROM parrainages WHERE parrain_id = :parrain_id");
    $stmt->execute([":parrain_id" => $parrain["parrain_id"]]);
    if ($stmt->fetch()) {
        echo json_encode([
            "success" => false,
            "message" => "Vous avez d�j� parrain� un candidat."
        ]);
        exit();
    }

    // G�n�rer un code de validation � 5 chiffres
    $sponsorship_code = strval(rand(10000, 99999));

    // Ins�rer le parrainage
    $stmt = $pdo->prepare("
        INSERT INTO parrainages (parrain_id, candidat_id, code_verification)
        VALUES (:parrain_id, :candidat_id, :code_verification)
    ");
    $insert = $stmt->execute([
        ":parrain_id"      => $parrain["parrain_id"],
        ":candidat_id"     => $candidate_id,
        ":code_verification"=> $sponsorship_code
    ]);

    if ($insert) {
        sendEmail($parrain["email"], "Votre code de validation de parrainage", "Votre code de validation est : $sponsorship_code");
        echo json_encode([
            "success" => true,
            "message" => "Code de validation envoy� par email. Veuillez v�rifier votre bo�te mail."
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Erreur lors de l'initiation du parrainage."
        ]);
    }
    exit();
} else {
    // ----------------------------------------------------------------------
    // 5. Phase 3 : Confirmation du parrainage => le parrain fournit sponsorship_code
    // ----------------------------------------------------------------------
    $sponsorship_code = trim($data->sponsorship_code);

    $stmt = $pdo->prepare("
        SELECT p.id AS parrainage_id, c.nom AS candidate_nom, c.parti_politique
        FROM parrainages p
        JOIN candidats c ON p.candidat_id = c.id
        WHERE p.parrain_id = :parrain_id
          AND p.code_verification = :code_verification
          AND p.candidat_id = :candidat_id
    ");
    $stmt->execute([
        ":parrain_id"     => $parrain["parrain_id"],
        ":code_verification" => $sponsorship_code,
        ":candidat_id"    => $candidate_id
    ]);
    $sponsorship = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sponsorship) {
        echo json_encode([
            "success" => false,
            "message" => "Code de validation incorrect ou parrainage non trouv�."
        ]);
        exit();
    }

    // Confirmer le parrainage en mettant code_verification � NULL
    $stmt = $pdo->prepare("UPDATE parrainages SET code_verification = 00000 WHERE id = :id");
    $update = $stmt->execute([":id" => $sponsorship["parrainage_id"]]);

    if ($update) {
        $confirmationMsg = "Votre parrainage a �t� confirm�. Vous avez choisi le candidat {$sponsorship['candidate_nom']} du parti {$sponsorship['parti_politique']}.";
        sendEmail($parrain["email"], "Parrainage confirm�", $confirmationMsg);
        echo json_encode([
            "success" => true,
            "message" => "Parrainage confirm� avec succ�s."
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Erreur lors de la confirmation du parrainage."
        ]);
    }
    exit();
}
?>
