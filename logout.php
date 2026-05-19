<?php
// logout.php
session_start();
session_unset();
session_destroy(); // Détruit la session sur le serveur

header('Location: index.php'); // Redirige vers la page d'accueil de l'application
exit();
?>
