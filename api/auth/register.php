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

if (!empty($data->name) && !empty($data->email) && !empty($data->password)) {
    $hashed_password = password_hash($data->password, PASSWORD_BCRYPT);

    $query = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":name", $data->name);
    $stmt->bindParam(":email", $data->email);
    $stmt->bindParam(":password", $hashed_password);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Inscription réussie !"]);
    } else {
        echo json_encode(["message" => "Erreur lors de l'inscription."]);
    }
} else {
    echo json_encode(["message" => "Données incomplètes."]);
}
?>
