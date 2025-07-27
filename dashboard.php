<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
require 'includes/db.php';

$user = $_SESSION['user'];

// Derniers rendez-vous
$stmt = $pdo->prepare("SELECT services, date, time FROM reservations WHERE email = ? ORDER BY date DESC, time DESC LIMIT 5");
$stmt->execute([$user['email']]);
$reservations = $stmt->fetchAll();

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = trim($_POST['name']);
    $new_password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Mise à jour du nom
    if (!empty($new_name) && $new_name !== $user['name']) {
        $pdo->prepare("UPDATE users SET name = ? WHERE email = ?")->execute([$new_name, $user['email']]);
        $_SESSION['user']['name'] = $new_name;
        $success .= "✅ Nom mis à jour. ";
    }

    // Mise à jour du mot de passe
    if (!empty($new_password) || !empty($confirm_password)) {
        if ($new_password !== $confirm_password) {
            $error = "❌ Les mots de passe ne correspondent pas.";
        } elseif (strlen($new_password) < 6) {
            $error = "❌ Le mot de passe doit contenir au moins 6 caractères.";
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password = ? WHERE email = ?")->execute([$hashed, $user['email']]);
            $success .= "✅ Mot de passe mis à jour.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mon espace</title>
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
      color: #fff;
      padding: 1.5rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }

    header h1 {
      font-family: 'Playfair Display', serif;
      font-size: 1.8rem;
      color: #fff;
    }

    header a {
      color: #fff;
      text-decoration: none;
      font-weight: bold;
      margin-left: 1rem;
    }

    main {
      padding: 2rem;
    }

    .welcome {
      text-align: center;
      margin-bottom: 2rem;
    }

    .welcome h2 {
      font-size: 2rem;
      font-family: 'Playfair Display', serif;
    }

    .section {
      max-width: 800px;
      margin: 2rem auto;
      background: #f9f9f9;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .section h3 {
      margin-bottom: 1rem;
      font-size: 1.5rem;
      color: #000;
      font-family: 'Playfair Display', serif;
      text-align: center;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }

    th, td {
      padding: 1rem;
      border-bottom: 1px solid #ddd;
    }

    th {
      background: #000;
      color: #fff;
      text-align: left;
    }

    tr:nth-child(even) {
      background: #fff;
    }

    .btn {
      display: inline-block;
      margin: 1rem auto;
      padding: 0.75rem 1.5rem;
      background: #fff;
      color: #000;
      font-weight: bold;
      border: 2px solid #000;
      border-radius: 8px;
      text-decoration: none;
      text-align: center;
      transition: 0.3s ease;
    }

    .btn:hover {
      background: #000;
      color: #fff;
    }

    form input {
      width: 100%;
      padding: 0.8rem;
      margin: 0.5rem 0 1rem;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 1rem;
    }

    form button {
      background: #000;
      color: #fff;
      font-weight: bold;
      border: none;
      border-radius: 8px;
      padding: 0.75rem 1.5rem;
      cursor: pointer;
      transition: 0.3s;
    }

    form button:hover {
      background: #444;
    }

    .success {
      background: #e6ffe6;
      color: #2d7a2d;
      padding: 0.8rem;
      border: 1px solid #2d7a2d;
      border-radius: 6px;
      text-align: center;
      margin-bottom: 1rem;
    }

    .error {
      background: #ffe6e6;
      color: #a40000;
      padding: 0.8rem;
      border: 1px solid #a40000;
      border-radius: 6px;
      text-align: center;
      margin-bottom: 1rem;
    }

    footer {
      text-align: center;
      padding: 2rem;
      background: #111;
      color: #eee;
      margin-top: 4rem;
    }

    footer a {
      color: #ccc;
      text-decoration: none;
    }

    footer a:hover {
      text-decoration: underline;
    }

    @media (max-width: 768px) {
      header {
        flex-direction: column;
        align-items: flex-start;
      }
      .section, main {
        padding: 1rem;
      }
      .btn, form button {
        width: 100%;
      }
    }
  </style>
</head>
<body>

<header>
  <h1>Mon espace</h1>
  <div>
    Bonjour, <?= htmlspecialchars($user['name']) ?> |
    <a href="logout.php">Déconnexion</a>
  </div>
</header>

<main>
  <div class="welcome">
    <h2>Bienvenue, <?= htmlspecialchars($user['name']) ?> !</h2>
    <p>Email : <?= htmlspecialchars($user['email']) ?></p>
  </div>

  <section class="section">
    <h3>📋 Mes informations personnelles</h3>

    <?php if ($success): ?>
      <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <label for="name">Nom complet :</label>
      <input type="text" name="name" id="name" value="<?= htmlspecialchars($user['name']) ?>" required>

      <label for="password">Nouveau mot de passe :</label>
      <input type="password" name="password" id="password" placeholder="Laisser vide pour ne pas changer">

      <label for="confirm_password">Confirmer le mot de passe :</label>
      <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirmer le mot de passe">

      <button type="submit">Mettre à jour</button>
    </form>
  </section>

  <section class="section">
    <h3>📅 Mes rendez-vous</h3>
    <?php if (count($reservations) > 0): ?>
      <table>
        <tr>
          <th>Prestations</th>
          <th>Date</th>
          <th>Heure</th>
        </tr>
        <?php foreach ($reservations as $res): ?>
          <tr>
            <td><?= htmlspecialchars($res['services']) ?></td>
            <td><?= htmlspecialchars(date('d/m/Y', strtotime($res['date']))) ?></td>
            <td><?= htmlspecialchars($res['time']) ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php else: ?>
      <p style="text-align: center;">Aucun rendez-vous enregistré.</p>
    <?php endif; ?>
  </section>

  <div style="text-align: center;">
    <a href="reservation.php" class="btn">📌 Réserver une prestation</a>
  </div>
</main>

<footer>
  <p>&copy; <?= date('Y') ?> Coiffynoire · Tous droits réservés · <a href="index.php">Retour au site</a></p>
</footer>

</body>
</html>





