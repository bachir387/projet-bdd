<?php
// File: parrainages/initiate_parrainage.php

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
if (!isset($data->email, $data->candidat_id)) {
  echo json_encode(["success" => false, "message" => "Données incomplètes."]);
  exit();
}

$email = trim($data->email);
$candidat_id = $data->candidat_id;

// Vérifier que le parrain existe via son email
$stmt = $pdo->prepare("SELECT * FROM parrains WHERE email = :email");
$stmt->execute([":email" => $email]);
$parrain = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$parrain) {
  echo json_encode(["success" => false, "message" => "Parrain introuvable."]);
  exit();
}

// Vérifier s'il a déjà parrainé
$stmt = $pdo->prepare("SELECT * FROM parrainages WHERE parrain_id = :parrain_id");
$stmt->execute([":parrain_id" => $parrain["id"]]);
if ($stmt->fetch()) {
  echo json_encode(["success" => false, "message" => "Vous avez déjà parrainé un candidat."]);
  exit();
}

// Générer un code de validation à 5 chiffres
$code_validation = strval(rand(10000, 99999));

// Insérer le parrainage dans la table parrainages
$stmt = $pdo->prepare("INSERT INTO parrainages (parrain_id, candidat_id, code_verification) VALUES (:parrain_id, :candidat_id, :code_verification)");
$insert = $stmt->execute([
  ":parrain_id" => $parrain["id"],
  ":candidat_id" => $candidat_id,
  ":code_verification" => $code_validation
]);

if ($insert) {
  // Envoyer le code par email au parrain
  sendEmail($email, "Code de validation de votre parrainage", "Votre code de validation est : $code_validation");
  echo json_encode(["success" => true, "message" => "Code de validation envoyé par email."]);
} else {
  echo json_encode(["success" => false, "message" => "Erreur lors de l'initiation du parrainage."]);
}
?>
