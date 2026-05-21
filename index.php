<?php
// 1. Inclusion de la configuration de la base de données (Aiven)
include 'config.php';

// --- ALTERATION VERSION COMPATIBLE BDD ---
try {
    // 1. Essai d'ajout de la colonne lat
    $conn->query("ALTER TABLE `reports` ADD `lat` DECIMAL(10, 8) NULL AFTER `id`");
} catch (Exception $e) {
    // Si la colonne existe déjà, MySQL lève une exception, on l'ignore proprement
}

try {
    // 2. Essai d'ajout de la colonne lng
    $conn->query("ALTER TABLE `reports` ADD `lng` DECIMAL(11, 8) NULL AFTER `lat`");
} catch (Exception $e) {
    // Idem si lng existe déjà
}
// --- FIN DU SCRIPT D'ALTERATION ---


// --- SCRIPT D'INSTALLATION TEMPORAIRE BDD ---
// Ce script crée automatiquement les tables sur Aiven si elles n'existent pas encore
$sql_init = "
CREATE TABLE IF NOT EXISTS `reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `photo` VARCHAR(255) DEFAULT NULL,
  `mairie` VARCHAR(100) NOT NULL,
  `description` TEXT NOT NULL,
  `votes` INT DEFAULT 0,
  `status` ENUM('signaled', 'in_progress', 'cleaned') DEFAULT 'signaled',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `identifiant` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `mairie_associee` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `users` (`identifiant`, `password`, `mairie_associee`) 
VALUES ('douala5', 'kmer237', 'Douala V')
ON DUPLICATE KEY UPDATE `mairie_associee`='Douala V';
";

// Exécution de la requête multiple
if (isset($conn) && $conn) {
    if ($conn->multi_query($sql_init)) {
        do {
            if ($result = $conn->store_result()) { 
                $result->free(); 
            }
        } while ($conn->next_result());
    }
}
// --- FIN DU SCRIPT D'INSTALLATION ---

// 2. Reste de ton code index.php d'origine (Initialisation des sessions, HTML, etc.)
// session_start();
// ...
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Smart Waste - Accueil</title>
    <link rel="manifest" href="manifest.json">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        html, body {
            margin: 0; padding: 0; width: 100%; height: 100%;
            font-family: '-apple-system', BlinkMacSystemFont, sans-serif;
            background-color: #ffffff; display: flex; flex-direction: column;
            justify-content: space-between; align-items: center; box-sizing: border-box;
        }

        /* Top Header vert arrondi */
        .welcome-header {
            width: 100%; background: linear-gradient(135deg, #2ecc71, #27ae60);
            padding: 40px 0 30px 0; border-bottom-left-radius: 24px;
            border-bottom-right-radius: 24px; text-align: center; color: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .welcome-header .icon { font-size: 28px; margin-bottom: 5px; }
        .welcome-header h1 { margin: 0; font-size: 24px; font-weight: bold; letter-spacing: 0.5px; }

        /* Zone centrale de contenu */
        .main-content {
            width: 90%; max-width: 360px; text-align: center;
            display: flex; flex-direction: column; gap: 20px; flex-grow: 1;
            justify-content: center;
        }
        .welcome-text { font-size: 26px; font-weight: bold; color: #000000; margin-bottom: 10px; }

        /* Style des boutons de ta maquette */
        .btn-menu {
            width: 100%; padding: 16px; font-size: 18px; font-weight: bold;
            border: none; border-radius: 12px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 12px;
            color: white; box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            transition: transform 0.1s ease; -webkit-tap-highlight-color: transparent;
            text-decoration: none; box-sizing: border-box;
        }
        .btn-menu:active { transform: scale(0.97); }

        .btn-signalement { background-color: #f3700b; } /* Orange maquette */
        .btn-gestion { background-color: #1565c0; } /* Bleu maquette */

        /* Footer */
        .welcome-footer {
            width: 100%; text-align: center; padding-bottom: 30px;
            color: #7f8c8d; font-size: 15px;
        }
        .footer-logo { width: 40px; opacity: 0.7; margin-bottom: 8px; }
    </style>
</head>
<body>

    <div class="welcome-header">
        <div class="icon">
         <i class="fas fa-trash-restore-alt"></i>
         </div>
        <h1>Smart Waste</h1>
    </div>

    <div class="main-content">
        <div class="welcome-text">Bienvenue !</div>
        
        <a href="map.php" class="btn-menu btn-signalement">
             Signalement
        </a>
        
        <a href="admin.php" class="btn-menu btn-gestion">
            Gestion
        </a>
    </div>

    <div class="welcome-footer">
        <div style="font-size: 30px; margin-bottom: 5px;">🏙️</div>
        <div>Agissons pour une ville plus propre !</div>
    </div>

    <script>
        // Enregistrement du Service Worker pour que la PWA soit installable depuis l'accueil
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('sw.js')
            .then(() => console.log("Service Worker de l'accueil actif !"))
            .catch(err => console.warn(err));
        }
    </script>
</body>
</html>
