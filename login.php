<?php
// login.php
session_start();
include('config.php');

$erreur = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = $conn->real_escape_string($_POST['identifiant']);
    $password = $_POST['password']; // Le mot de passe tapé

    $sql = "SELECT * FROM users WHERE identifiant = '$identifiant' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // On stocke les informations de l'agent dans la SESSION
        $_SESSION['agent_id'] = $user['id'];
        $_SESSION['agent_mairie'] = $user['mairie_associee'];

        // Connexion réussie -> Redirection vers la page admin
        header('Location: admin.php');
        exit();
    } else {
        $erreur = "Identifiant ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Mairie</title>
    <style>
        body { font-family: sans-serif; background: #f4f6f7; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 30px; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 90%; max-width: 360px; text-align: center; }
        h2 { color: #2c3e50; margin-bottom: 20px; }
        .input-field { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .btn-connect { width: 100%; background: #1565c0; color: white; border: none; padding: 14px; font-size: 16px; font-weight: bold; border-radius: 8px; cursor: pointer; }
        .error-msg { color: #e74c3c; margin-bottom: 15px; font-size: 14px; }
        .btn-back { display: inline-block; margin-top: 15px; color: #7f8c8d; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Accès Agent Communal</h2>
    
    <?php if(!empty($erreur)): ?>
        <div class="error-msg"><?= $erreur ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <input type="text" name="identifiant" class="input-field" placeholder="Identifiant de la mairie" required>
        <input type="password" name="password" class="input-field" placeholder="Mot de passe" required>
        <button type="submit" class="btn-connect">Se connecter</button>
    </form>

    <a href="index.php" class="btn-back">← Retour à l'accueil</a>
</div>

</body>
</html>
