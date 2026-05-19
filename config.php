<?php
// config.php connecté à Aiven MySQL
$host = "mysql-4a9237a-smartwaste-fb237.a.aivencloud.com"; 
$user = "avnadmin";                 
$pass = "AVNS_2HYp5VXeSmiCD3LyfBO";             
$dbname = "defaultdb"; // C'est le nom de base par défaut fourni par Aiven
$port = 15663; // Ne pas oublier le port spécifique d'Aiven au lieu de 3306

$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Gestion propre des accents des quartiers
$conn->set_charset("utf8mb4");
?>
