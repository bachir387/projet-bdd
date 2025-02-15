<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Si la méthode est OPTIONS (pré-vol CORS), terminer l'exécution
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
require '../config/config.php';
require '../vendor/autoload.php';
use Firebase\JWT\JWT;

$secret_key = "votre_cle_secrete";
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email) && !empty($data->password)) {
    $query = "SELECT * FROM users WHERE email = :email";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":email", $data->email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($data->password, $user['password'])) {
        $payload = [
            "iss" => "http://localhost",
            "aud" => "http://localhost",
            "iat" => time(),
            "exp" => time() + (60 * 60), // Expire dans 1 heure
            "data" => [
                "id" => $user['id'],
                "name" => $user['name'],
                "email" => $user['email']
            ]
        ];
        $jwt = JWT::encode($payload, $secret_key, 'HS256');

        echo json_encode(["message" => "Connexion réussie", "token" => $jwt]);
    } else {
        echo json_encode(["message" => "Email ou mot de passe incorrect."]);
    }
} else {
    echo json_encode(["message" => "Données incomplètes."]);
}
?>
