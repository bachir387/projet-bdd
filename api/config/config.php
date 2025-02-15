<?php
$host = "localhost";
$db_name = "parrainagefinal";
$username = "pdge";
$password = "pdge-2025";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $exception) {
    die("Erreur de connexion à la base de données : " . $exception->getMessage());
}

function getParrainageStatus($pdo) {
    $stmt = $pdo->query("SELECT * FROM periode_parrainage ORDER BY created_at DESC LIMIT 1");
    $periode = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$periode) return ["status" => "inactive"];

    $date_actuelle = date('Y-m-d');
    if ($date_actuelle < $periode['date_debut']) return ["status" => "pending"];
    if ($date_actuelle > $periode['date_fin']) return ["status" => "closed"];

    return ["status" => "active"];
}

?>
