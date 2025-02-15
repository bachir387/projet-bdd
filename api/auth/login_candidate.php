<?php
// File: candidats/login_candidate.php

require_once '../config/config.php';
require_once '../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

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

$email = trim($data->email);
$code_auth = trim($data->code_auth);

// Vérifier que le candidat existe dans la table "candidats"
$stmt = $pdo->prepare("SELECT * FROM candidats WHERE email = :email AND code_auth = :code_auth");
$stmt->execute([
    ":email" => $email,
    ":code_auth" => $code_auth
]);
$candidate = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$candidate) {
    echo json_encode(["success" => false, "message" => "Email ou code incorrect."]);
    exit();
}

// Générer un token JWT pour le candidat
$secret_key = "votre_cle_secrete";
$payload = [
    "iss"  => "http://localhost",
    "aud"  => "http://localhost",
    "iat"  => time(),
    "exp"  => time() + 3600,  // Expiration dans 1 heure
    "data" => [
        "id"      => $candidate["id"],
        "nom"     => $candidate["nom"],
        "prenom"  => $candidate["prenom"],
        "email"   => $candidate["email"]
    ]
];

$jwt = JWT::encode($payload, $secret_key, 'HS256');

echo json_encode([
    "success" => true,
    "message" => "Connexion réussie.",
    "token"   => $jwt
]);
?>
