<?php
// 1. DÉMARRAGE DE LA SESSION ET SÉCURISATION
session_start();

// Si l'agent n'est pas connecté, on le redirige immédiatement vers la page de connexion
if (!isset($_SESSION['agent_id'])) {
    header('Location: login.php');
    exit();
}

include('config.php');

// Récupération de la mairie associée à l'agent connecté
$mairieAgent = $_SESSION['agent_mairie'];

// 2. REQUÊTE FILTRÉE POUR L'AGENT
// L'agent ne voit que les signalements qui correspondent exactement à sa commune
$mairieAgentEscaped = $conn->real_escape_string($mairieAgent);
$sqlReports = "SELECT * FROM reports WHERE mairie = '$mairieAgentEscaped' ORDER BY created_at DESC";
$resultReports = $conn->query($sqlReports);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Waste - Administration Communale</title>
    <style>
        body { font-family: '-apple-system', BlinkMacSystemFont, sans-serif; background: #f4f6f7; padding: 20px; margin: 0; }
        .container { max-width: 1100px; margin: auto; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        
        /* Header de la page admin */
        .admin-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f2f6; padding-bottom: 15px; margin-bottom: 20px; }
        h1 { color: #2c3e50; font-size: 22px; margin: 0; }
        .welcome-badge { background: #e8f8f5; color: #117a65; padding: 6px 12px; border-radius: 20px; font-weight: bold; font-size: 14px; }
        
        .btn-logout { background: #e74c3c; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: bold; text-decoration: none; font-size: 14px; }
        
        /* Statuts badges */
        .status { padding: 6px 12px; border-radius: 50px; color: white; font-size: 11px; font-weight: bold; display: inline-block; }
        .status-signaled { background: #e74c3c; }
        .status-in_progress { background: #f39c12; }
        .status-cleaned { background: #2ecc71; }

        /* Tableau */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 14px; border-bottom: 1px solid #e1e8ed; text-align: left; }
        th { background: #f8f9fa; color: #7f8c8d; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
        td { color: #2c3e50; font-size: 15px; }
        
        img { width: 70px; height: 55px; object-fit: cover; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        
        select { padding: 8px; border-radius: 6px; border: 1px solid #ccd1d1; background: white; font-size: 14px; color: #2c3e50; }
        .btn-delete { background: none; border: none; font-size: 18px; cursor: pointer; margin-left: 10px; -webkit-tap-highlight-color: transparent; }
    </style>
</head>
<body>

<div class="container">
    <div class="admin-header">
        <div>
            <h1>Espace Gestion Technique</h1>
            <span class="welcome-badge">Mairie de : <?= htmlspecialchars($mairieAgent) ?></span>
        </div>
        <a href="logout.php" class="btn-logout">Se déconnecter 🚪</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Photo</th>
                <th>Description / Quartier</th>
                <th>Votes</th>
                <th>Statut Actuel</th>
                <th>Changer l'État</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if($resultReports && $resultReports->num_rows > 0): ?>
                <?php while($row = $resultReports->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php if($row['photo']): ?>
                            <a href="uploads/<?= htmlspecialchars($row['photo']) ?>" target="_blank">
                                <img src="uploads/<?= htmlspecialchars($row['photo']) ?>" alt="Déchet signalé">
                            </a>
                        <?php else: ?>
                            <span style="color:#95a5a6; font-size:13px; font-style:italic;">Aucune</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span style="font-size: 13px; color: #7f8c8d; display: block; margin-bottom: 4px;">📍 Repéré à : <?= htmlspecialchars($row['mairie']) ?></span>
                        <b><?= htmlspecialchars($row['description']) ?></b>
                    </td>
                    <td style="font-weight: bold; color: #e74c3c;">🔥 <?= $row['votes'] ?></td>
                    <td>
                        <span class="status status-<?= $row['status'] ?>">
                            <?= ($row['status'] == 'signaled') ? '🔴 SIGNALÉ' : (($row['status'] == 'in_progress') ? '🟡 EN COURS' : '🟢 NETTOYÉ') ?>
                        </span>
                    </td>
                    <td>
                        <select onchange="updateStatus(<?= $row['id'] ?>, this.value)">
                            <option value="signaled" <?= ($row['status'] == 'signaled') ? 'selected' : '' ?>>🔴 Signalé</option>
                            <option value="in_progress" <?= ($row['status'] == 'in_progress') ? 'selected' : '' ?>>🟡 En cours</option>
                            <option value="cleaned" <?= ($row['status'] == 'cleaned') ? 'selected' : '' ?>>🟢 Nettoyé</option>
                        </select>
                    </td>
                    <td>
                        <button class="btn-delete" onclick="deleteReport(<?= $row['id'] ?>)" title="Supprimer définitivement">🗑️</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding: 40px; color: #7f8c8d;">
                        🎉 Aucun dépôt d'ordures actif à signaler dans votre secteur !
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
// Modification de l'état via AJAX (sans recharger manuellement la page)
function updateStatus(id, newStatus) {
    let formData = new FormData();
    formData.append('id', id);
    formData.append('status', newStatus);

    fetch('update_status.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            location.reload(); // Recharge la page pour mettre à jour les couleurs des badges et la colonne updated_at
        } else {
            alert("Une erreur est survenue lors de la mise à jour.");
        }
    })
    .catch(err => console.error("Erreur:", err));
}

// Suppression d'un faux signalement ou doublon abusif
function deleteReport(id) {
    if(confirm("Voulez-vous supprimer définitivement ce signalement de la base de données ?")) {
        let formData = new FormData();
        formData.append('id', id);

        fetch('delete_report.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                location.reload();
            }
        })
        .catch(err => console.error("Erreur:", err));
    }
}
</script>

</body>
</html>

