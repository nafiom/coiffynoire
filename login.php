<?php
require 'includes/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Email ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Connexion – Coiffynoire</title>
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
  <h2>Connexion</h2>
  <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
  <form method="POST">
    <input type="email" name="email" placeholder="Votre email" required>
    <input type="password" name="password" placeholder="Mot de passe" required>
    <button type="submit">Se connecter</button>
  </form>
  <p>Pas encore inscrit ? <a href="register.php">Créer un compte</a></p>
</div>

<footer>
  <p>&copy; <?= date('Y') ?> Coiffynoire · Tous droits réservés · <a href="index.php">Retour à l’accueil</a></p>
</footer>

</body>
</html>


