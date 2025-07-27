<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current'] ?? '';
    $new     = $_POST['new'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    $stmt = $pdo->prepare("SELECT password FROM admins WHERE username = ?");
    $stmt->execute([$_SESSION['admin']]);
    $admin = $stmt->fetch();

    if (!$admin || hash('sha256', $current) !== $admin['password']) {
        $message = "❌ Ancien mot de passe incorrect.";
    } elseif ($new !== $confirm) {
        $message = "❌ Les mots de passe ne correspondent pas.";
    } else {
        $hashed = hash('sha256', $new);
        $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = ?");
        $stmt->execute([$hashed, $_SESSION['admin']]);
        $message = "✅ Mot de passe mis à jour avec succès.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Changer mot de passe - Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: sans-serif;
      background: #f5f5f5;
      padding: 2rem;
    }
    .box {
      background: #fff;
      max-width: 500px;
      margin: 3rem auto;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    }
    h1 {
      text-align: center;
      color: #e6c200;
    }
    form {
      display: flex;
      flex-direction: column;
    }
    input {
      padding: 0.7rem;
      margin: 0.5rem 0;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    button {
      background: #000;
      color: white;
      border: none;
      padding: 0.7rem;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
      margin-top: 1rem;
    }
    .msg {
      text-align: center;
      color: #333;
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>
  <div class="box">
    <h1>🔐 Changer mot de passe</h1>
    <?php if ($message): ?>
      <p class="msg"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <form method="post">
      <input type="password" name="current" placeholder="Mot de passe actuel" required>
      <input type="password" name="new" placeholder="Nouveau mot de passe" required>
      <input type="password" name="confirm" placeholder="Confirmer le mot de passe" required>
      <button type="submit">Mettre à jour</button>
      <a href="dashboard.php">← Retour au dashboard</a>
    </form>
  </div>
</body>
</html>
