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

if (!isset($data->numero_cin, $data->numero_electeur)) {
    echo json_encode(["message" => "Données incomplètes."]);
    exit;
}

// Vérifier si le parrain est enregistré
$stmt = $pdo->prepare("SELECT * FROM parrains WHERE numero_cin = :numero_cin AND numero_electeur = :numero_electeur");
$stmt->execute([":numero_cin" => $data->numero_cin, ":numero_electeur" => $data->numero_electeur]);
$parrain = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$parrain) {
    echo json_encode(["message" => "Aucun compte parrain trouvé avec ces informations."]);
    exit;
}

// Vérifier s'il a déjà parrainé
$stmt = $pdo->prepare("SELECT * FROM parrainages WHERE parrain_id = :parrain_id");
$stmt->execute([":parrain_id" => $parrain["id"]]);
if ($stmt->fetch()) {
    echo json_encode(["message" => "Vous avez déjà parrainé un candidat."]);
    exit;
}

echo json_encode([
    "nom" => $parrain["nom"],
    "prenom" => $parrain["prenom"],
    "bureau_vote" => $parrain["bureau_vote"],
    "code_auth" => true // Signale qu'il doit entrer son code
]);
?>
