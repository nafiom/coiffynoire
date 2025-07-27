<?php
require 'includes/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetchColumn() > 0) {
        $error = "Cet email est déjà utilisé.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $password]);
        $_SESSION['user'] = ['name' => $name, 'email' => $email];
        header('Location: dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Inscription – Chic Coiffure</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Open Sans', sans-serif;
      background: #fff;
      color: #111;
    }

    header {
      background: #000;
      color: white;
      padding: 1.5rem 2rem;
      text-align: center;
    }

    header h1 {
      font-family: 'Playfair Display', serif;
      font-size: 2rem;
    }

    .container {
      max-width: 500px;
      margin: 2rem auto;
      padding: 2rem;
      background: #f9f9f9;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      font-family: 'Playfair Display', serif;
      font-size: 1.8rem;
      margin-bottom: 1.5rem;
      color: #000;
    }

    form {
      display: flex;
      flex-direction: column;
    }

    input {
      padding: 0.75rem;
      margin-bottom: 1rem;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 1rem;
    }

    button {
      background: #000;
      color: #fff;
      font-weight: bold;
      padding: 0.75rem;
      border: 2px solid #000;
      border-radius: 8px;
      cursor: pointer;
      transition: 0.3s ease;
    }

    button:hover {
      background: #fff;
      color: #000;
    }

    .error {
      color: red;
      text-align: center;
      margin-bottom: 1rem;
    }

    p {
      text-align: center;
      margin-top: 1rem;
    }

    a {
      color: #000;
      text-decoration: none;
      font-weight: bold;
    }

    a:hover {
      text-decoration: underline;
    }

    footer {
      text-align: center;
      padding: 2rem;
      background: #111;
      color: #eee;
      margin-top: 3rem;
    }

    footer a {
      color: #ccc;
      text-decoration: none;
    }

    footer a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<header>
  <h1>Coiffynoire</h1>
</header>

<div class="container">
  <h2>Créer un compte</h2>
  <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
  <form method="POST">
    <input type="text" name="name" placeholder="Votre nom complet" required>
    <input type="email" name="email" placeholder="Votre adresse email" required>
    <input type="password" name="password" placeholder="Choisissez un mot de passe" required>
    <button type="submit">S'inscrire</button>
  </form>
  <p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
</div>

<footer>
  <p>&copy; <?= date('Y') ?> Coiffynoire · Tous droits réservés · <a href="index.php">Retour à l’accueil</a></p>
</footer>

</body>
</html>


