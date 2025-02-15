<?php
require '../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Methods: POST, OPTIONS");
// header("Access-Control-Allow-Headers: Content-Type, Authorization");

// if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
//     http_response_code(200);
//     exit();
// }

$secret_key = "votre_cle_secrete";
$headers = getallheaders();
$token = $headers["Authorization"] ?? "";

if ($token) {
    try {
        $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
        echo json_encode(["message" => "Token valide", "user" => $decoded->data]);
    } catch (Exception $e) {
        echo json_encode(["message" => "Token invalide", "error" => $e->getMessage()]);
    }
} else {
    echo json_encode(["message" => "Token manquant"]);
}
?>
