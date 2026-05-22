<?php
// add_report.php

// 1. Inclusion de la configuration de la base de données
require 'config.php';

// Initialisation de la réponse JSON par défaut (échec)
$response = [
    'status' => 'error',
    'message' => 'Une erreur inconnue est survenue.'
];

// 2. Vérification que la requête est bien en POST (soumission de formulaire)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 3. Récupération et nettoyage des données textes (Sécurité contre injections)
    // Nous récupérons la latitude et la longitude envoyées par FormData dans app.js
    $lat = isset($_POST['lat']) ? $conn->real_escape_string($_POST['lat']) : null;
    $lng = isset($_POST['lng']) ? $conn->real_escape_string($_POST['lng']) : null;
    $description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : '';
    $mairie = isset($_POST['mairie']) ? $conn->real_escape_string($_POST['mairie']) : '';

    // Validation minimale
    if (!$lat || !$lng || empty($mairie)) {
        $response['message'] = "Coordonnées GPS ou lieu manquants.";
        echo json_encode($response);
        exit;
    }

    // 4. Gestion sécurisée de la photo (Upload)
    $nomPhoto = null; // Par défaut, pas de photo

    // Vérification si un fichier 'photo' a été envoyé sans erreur
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        
        // Dossier de destination sur le serveur Render
        $dossierDestination = 'uploads/';
        
        // Sécurité : On s'assure que le dossier existe (en cas de .gitkeep supprimé)
        if (!is_dir($dossierDestination)) {
            @mkdir($dossierDestination, 0777, true);
        }

        // Génération d'un nom de fichier unique et propre
        $fileNameOriginal = $_FILES['photo']['name'];
        $extension = pathinfo($fileNameOriginal, PATHINFO_EXTENSION);
        // Exemple : dechet_1779404858.jpg
        $nouveauNom = 'dechet_' . time() . '.' . $extension;
        $cheminComplet = $dossierDestination . $nouveauNom;

        // TENTATIVE DE DÉPLACEMENT SÉCURISÉE (@ pour cacher les alertes PHP)
        // Sur Render en mode gratuit, l'écriture sur disque est souvent bloquée.
        if (@move_uploaded_file($_FILES['photo']['tmp_name'], $cheminComplet)) {
            // Succès de l'upload !
            $nomPhoto = $nouveauNom;
        } else {
            // ÉCHEC DE L'UPLOAD (Problème de permission standard sur Render Cloud)
            // L'image n'est pas sauvegardée, mais nous ne bloquons pas l'enregistrement en BDD.
            // On peut logguer l'erreur discrètement si nécessaire côté serveur.
            // error_log("Échec d'upload photo sur Render (Permission Denied)");
        }
    } else {
        // Pas de photo envoyée ou erreur dans l'envoi de l'image
    }

    // 5. Insertion dans la base de données Aiven
    // Nous utilisons une requête préparée (Prepared Statement) pour un maximum de sécurité.
    // Les colonnes lat, lng, description, mairie, et photo doivent exister.
    $stmt = $conn->prepare("INSERT INTO reports (lat, lng, description, mairie, photo) VALUES (?, ?, ?, ?, ?)");
    
    // "ddsss" signifie : d (decimal/double) pour lat, d pour lng, s (string) pour description, s pour mairie, s pour photo
    $stmt->bind_param("ddsss", $lat, $lng, $description, $mairie, $nomPhoto);

    if ($stmt->execute()) {
        // SUCCÈS TOTAL DE L'ENREGISTREMENT
        if ($nomPhoto) {
            $response['status'] = 'success';
            $response['message'] = 'Signalement avec photo enregistré avec succès !';
        } else {
            // Succès SQL mais l'image a échoué (ou n'était pas là)
            $response['status'] = 'success';
            $response['message'] = 'Signalement enregistré, mais la photo n\'a pas pu être sauvegardée (problème de stockage serveur).';
        }
    } else {
        // ÉCHEC SQL (Mauvais nom de colonne, etc.)
        $response['message'] = "Erreur de base de données : " . $stmt->error;
    }

    $stmt->close();
} else {
    $response['message'] = "Méthode de requête non autorisée (POST attendu).";
}

// 6. Fermeture de la connexion et renvoi de la réponse JSON propre au JavaScript
$conn->close();
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
?>
