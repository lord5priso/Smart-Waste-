<?php
// get_reports.php
include('config.php');
header('Content-Type: application/json');

// REQUÊTE AJUSTÉE : 
// On sélectionne les points qui ne sont pas nettoyés 
// OU ceux qui sont nettoyés mais depuis moins de 30 minutes.
$sql = "SELECT id, lat, lng, description, mairie, votes, status 
        FROM reports 
        WHERE status != 'cleaned' 
        OR (status = 'cleaned' AND updated_at >= NOW() - INTERVAL 30 MINUTE)";

$result = $conn->query($sql);

$reports = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }
}

echo json_encode($reports);
$conn->close();
?>

