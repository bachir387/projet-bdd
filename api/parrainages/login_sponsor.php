<?php
// File: parrainages/login_sponsor.php

require_once '../config/config.php';
require_once '../vendor/autoload.php';
use Firebase\JWT\JWT;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

$data = json_decode(file_get_contents("php://input"));
if (!isset($data->email, $data->code_auth)) {
  echo json_encode(["success" => false, "message" => "Données incomplètes."]);
  exit();
}

$stmt = $pdo->prepare("SELECT * FROM parrains WHERE email = :email AND code_auth = :code_auth");
$stmt->execute([
  ":email" => trim($data->email),
  ":code_auth" => trim($data->code_auth)
]);
$parrain = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$parrain) {
  echo json_encode(["success" => false, "message" => "Email ou code incorrect."]);
  exit();
}

$secret_key = "votre_cle_secrete";
$payload = [
  "iss"  => "http://localhost",
  "aud"  => "http://localhost",
  "iat"  => time(),
  "exp"  => time() + 3600, // expire dans 1 heure
  "data" => [
    "id"      => $parrain["id"],
    "nom"     => $parrain["nom"],
    "prenom"  => $parrain["prenom"],
    "email"   => $parrain["email"]
  ]
];

$jwt = JWT::encode($payload, $secret_key, 'HS256');
echo json_encode(["success" => true, "message" => "Connexion réussie.", "token" => $jwt]);
?>
