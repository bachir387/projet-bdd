<?php

require '../config/config.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->email, $data->code_auth)) {
    echo json_encode(["message" => "Données incomplètes."]);
    exit;
}

// Vérifier le code d’authentification du parrain
$stmt = $pdo->prepare("SELECT * FROM parrains WHERE email = :email AND code_auth = :code_auth");
$stmt->execute([":email" => $data->email, ":code_auth" => $data->code_auth]);
$parrain = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$parrain) {
    echo json_encode(["message" => "Code d'authentification incorrect."]);
    exit;
}

// Récupérer la liste des candidats
$stmt = $pdo->query("SELECT id, nom, prenom, parti_politique, slogan, photo, couleur1, couleur2, couleur3 FROM candidats");
$candidats = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($candidats);
?>
