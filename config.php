<?php
// config.php lié à Clever Cloud
$host = "bxpxzcqwwefyyc6x3prx-mysql.services.clever-cloud.com"; 
$user = "uk2xfalelpagoqxc";                 
$pass = "T7APCCJydv9R3eYVY4LI";             
$dbname = "bxpxzcqwwefyyc6x3prx";         
$port = 3306; 

$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Encodage universel pour gérer proprement les accents des quartiers au Cameroun
$conn->set_charset("utf8mb4");
?>
