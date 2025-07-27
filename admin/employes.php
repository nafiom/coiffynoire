<?php
session_start();
require '../includes/db.php';
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$jours = [
    'lundi'     => 'Lundi',
    'mardi'     => 'Mardi',
    'mercredi'  => 'Mercredi',
    'jeudi'     => 'Jeudi',
    'vendredi'  => 'Vendredi',
    'samedi'    => 'Samedi',
    'dimanche'  => 'Dimanche'
];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($jours as $key => $label) {
        $nb = (int)($_POST[$key] ?? 1);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM effectifs WHERE jour = ?");
        $stmt->execute([$key]);
        if ($stmt->fetchColumn() > 0) {
            $update = $pdo->prepare("UPDATE effectifs SET nombre = ? WHERE jour = ?");
            $update->execute([$nb, $key]);
        } else {
            $insert = $pdo->prepare("INSERT INTO effectifs (jour, nombre) VALUES (?, ?)");
            $insert->execute([$key, $nb]);
        }
    }
    $message = "✔️ Effectifs mis à jour avec succès.";
}

// Chargement des effectifs
$effectifs = [];
foreach ($pdo->query("SELECT * FROM effectifs") as $row) {
    $effectifs[$row['jour']] = $row['nombre'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Effectifs - Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Open Sans', sans-serif;
      background: #f5f5f5;
      margin: 0;
      padding: 0;
    }

    .header {
      background: #000;
      color: white;
      padding: 20px;
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
      align-items: center;
    }

    .header a {
      color: white;
      text-decoration: none;
      margin-left: 15px;
      font-weight: bold;
    }

    .nav {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      background: #111;
      justify-content: center;
      padding: 15px;
    }

    .nav a {
      color: white;
      background: #222;
      padding: 10px 15px;
      text-decoration: none;
      border-radius: 6px;
      transition: all 0.2s ease;
    }

    .nav a:hover, .nav a.active {
      background: #e6c200;
      color: #000;
    }

    h1 {
      text-align: center;
      margin-top: 2rem;
      font-family: 'Playfair Display', serif;
      color: #000;
    }

    form {
      max-width: 600px;
      margin: auto;
      background: white;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    label {
      display: block;
      margin: 1.5rem 0 0.5rem;
      font-weight: bold;
      color: #333;
    }

    input[type="number"] {
      width: 100%;
      padding: 0.7rem;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 1rem;
    }

    button {
      margin-top: 2rem;
      background: #e6c200;
      color: black;
      border: none;
      padding: 1rem;
      font-size: 1rem;
      width: 100%;
      border-radius: 30px;
      cursor: pointer;
      font-weight: bold;
      transition: all 0.2s ease;
    }

    button:hover {
      background: #000;
      color: white;
    }

    .success {
      max-width: 600px;
      margin: 1.5rem auto;
      background: #d4edda;
      color: #155724;
      padding: 1rem;
      border-left: 5px solid green;
      border-radius: 6px;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="header">
    <h2> Effectifs </h2>
    <div>
      <a href="logout.php">Déconnexion</a>
    </div>
  </div>

  <div class="nav">
    <a href="dashboard.php"> Dashboard</a>
  </div>

  <h1>Nombre d'employés disponibles par jour</h1>

  <?php if (!empty($message)): ?>
    <div class="success"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <form method="post">
    <?php foreach ($jours as $key => $label): ?>
      <label for="<?= $key ?>"><?= $label ?></label>
      <input type="number" name="<?= $key ?>" id="<?= $key ?>" min="0" value="<?= $effectifs[$key] ?? 1 ?>">
    <?php endforeach; ?>
    <button type="submit">💾 Enregistrer les effectifs</button>
  </form>
</body>
</html>

