<?php
// config.php
$servername = "sql103.infinityfree.com"; // Vérifie bien le numéro sur ton panel
$username = "if0_41869322"; 
$password = "TON_MOT_DE_PASSE"; // Mets ton vrai mot de passe ici
$dbname = "if0_41869322_waste";  // Le nom de ta NOUVELLE base de données

$conn = new mysqli($servername, $username, $password, $dbname);

// Changement du jeu de caractères en utf8mb4 pour éviter les problèmes d'accents (ex: Yaoundé)
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("La connexion a échoué : " . $conn->connect_error);
}
?>
