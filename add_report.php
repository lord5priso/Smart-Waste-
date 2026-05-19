<?php
// add_report.php
include('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    $description = $_POST['description'];
    $mairie = $_POST['mairie'];
    $nomPhoto = null;

    // Gestion du téléversement de la photo
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $dossierDestination = 'uploads/';
        
        // Crée le dossier 'uploads' s'il n'existe pas encore
        if (!is_dir($dossierDestination)) {
            mkdir($dossierDestination, 0777, true);
        }

        // On génère un nom unique pour l'image (ex: dechet_171483920.jpg)
        $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $nomPhoto = 'dechet_' . time() . '_' . rand(100, 999) . '.' . $extension;
        $cheminComplet = $dossierDestination . $nomPhoto;

        // Déplacement du fichier temporaire vers le dossier final
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $cheminComplet)) {
            $nomPhoto = null; // Échec du transfert
        }
    }

    $description = $conn->real_escape_string($description);
    $mairie = $conn->real_escape_string($mairie);
    
    // On ajoute le nom de la photo dans la base de données
    $sql = "INSERT INTO reports (lat, lng, description, mairie, photo) VALUES ('$lat', '$lng', '$description', '$mairie', " . ($nomPhoto ? "'$nomPhoto'" : "NULL") . ")";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Signalement avec photo enregistré avec succès !"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
}
$conn->close();
?>

