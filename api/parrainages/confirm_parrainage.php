<?php
// File: parrainages/confirm_parrainage.php

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
if (!isset($data->email, $data->code_verification)) {
  echo json_encode(["success" => false, "message" => "Données incomplètes."]);
  exit();
}

$email = trim($data->email);
$code_verification = trim($data->code_verification);

// Rechercher le parrainage correspondant au parrain et au code
$stmt = $pdo->prepare("SELECT p.id AS parrainage_id FROM parrainages p
    JOIN parrains pa ON p.parrain_id = pa.id
    WHERE pa.email = :email AND p.code_verification = :code_verification");
$stmt->execute([
  ":email" => $email,
  ":code_verification" => $code_verification
]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$result) {
  echo json_encode(["success" => false, "message" => "Code de validation incorrect ou parrainage non trouvé."]);
  exit();
}

// Confirmer le parrainage : on met à NULL le code de validation
$stmt = $pdo->prepare("UPDATE parrainages SET code_verification = NULL WHERE id = :id");
$update = $stmt->execute([":id" => $result["parrainage_id"]]);
if ($update) {
  sendEmail($email, "Parrainage confirmé", "Votre parrainage a été confirmé avec succès.");
  echo json_encode(["success" => true, "message" => "Parrainage confirmé avec succès."]);
} else {
  echo json_encode(["success" => false, "message" => "Erreur lors de la confirmation du parrainage."]);
}
?>
