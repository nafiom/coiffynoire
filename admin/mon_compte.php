<?php
session_start();
require_once '../includes/db.php';

$adminUsername = $_SESSION['admin'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_email = trim($_POST['email']);

    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide.";
    } else {
        // Mise à jour de l'email
        $update = $pdo->prepare("UPDATE admins SET email = ? WHERE username = ?");
        $update->execute([$new_email, $adminUsername]);
        $success = " Adresse email mise à jour avec succès.";
    }
}

// Récupérer les infos actuelles
$stmt = $pdo->prepare("SELECT email FROM admins WHERE username = ?");
$stmt->execute([$adminUsername]);
$admin = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mon compte - Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Open Sans', sans-serif;
      background: #f8f8f8;
      padding: 2rem;
      color: #111;
    }

    .container {
      max-width: 600px;
      margin: 3rem auto;
      background: #fff;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    }

    h1 {
      font-family: 'Playfair Display', serif;
      text-align: center;
      margin-bottom: 2rem;
      color: #e6c200;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 1.2rem;
    }

    label {
      font-weight: bold;
    }

    input[type="email"] {
      padding: 0.6rem;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 1rem;
    }

    button {
      background: #e6c200;
      color: black;
      border: none;
      padding: 0.7rem;
      font-weight: bold;
      font-size: 1rem;
      border-radius: 30px;
      cursor: pointer;
      transition: 0.3s;
    }

    button:hover {
      background: #000;
      color: white;
    }

    .message {
      text-align: center;
      margin-top: 1rem;
      font-weight: bold;
    }

    .error {
      color: red;
    }

    .success {
      color: green;
    }

    a {
      display: block;
      text-align: center;
      margin-top: 2rem;
      text-decoration: none;
      color: #444;
    }
  </style>
</head>
<body>

<div class="container">
  <h1>👤 Mon compte admin</h1>

  <?php if ($error): ?>
    <div class="message error"><?= $error ?></div>
  <?php elseif ($success): ?>
    <div class="message success"><?= $success ?></div>
  <?php endif; ?>

  <form method="POST">
    <label for="email">Adresse email</label>
    <input type="email" name="email" id="email" value="<?= htmlspecialchars($admin['email']) ?>" required>

    <button type="submit"> Mettre à jour l'adresse</button>
  </form>

  <a href="changer_mdp.php"> Modifier mon mot de passe</a>
  <a href="dashboard.php">← Retour au dashboard</a>
</div>

</body>
</html>



