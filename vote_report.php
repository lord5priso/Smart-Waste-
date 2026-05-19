<?php
// vote_report.php
include('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);

    $sql = "UPDATE reports SET votes = votes + 1 WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Vote enregistré ! Merci pour votre confirmation."]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
}
$conn->close();
?>
