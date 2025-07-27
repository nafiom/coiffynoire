<?php
session_start();
require '../includes/db.php';
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$jours = [
    'monday' => 'Lundi',
    'tuesday' => 'Mardi',
    'wednesday' => 'Mercredi',
    'thursday' => 'Jeudi',
    'friday' => 'Vendredi',
    'saturday' => 'Samedi',
    'sunday' => 'Dimanche'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($jours as $key => $label) {
        $start = $_POST[$key . '_start'] ?? '';
        $end = $_POST[$key . '_end'] ?? '';

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM horaires WHERE jour = ?");
        $stmt->execute([$key]);

        if ($stmt->fetchColumn() > 0) {
            $update = $pdo->prepare("UPDATE horaires SET debut = ?, fin = ? WHERE jour = ?");
            $update->execute([$start, $end, $key]);
        } else {
            $insert = $pdo->prepare("INSERT INTO horaires (jour, debut, fin) VALUES (?, ?, ?)");
            $insert->execute([$key, $start, $end]);
        }
    }

    $message = "Horaires mis à jour avec succès.";
}

$horaires = [];
$results = $pdo->query("SELECT * FROM horaires")->fetchAll();
foreach ($results as $row) {
    $horaires[$row['jour']] = $row;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Horaires </title> 
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; }

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
      flex-wrap: wrap;
      justify-content: space-between;
      align-items: center;
    }

    .header a {
      color: white;
      margin-left: 20px;
      text-decoration: none;
      font-weight: bold;
    }

    .nav {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      padding: 15px;
      background: #111;
      justify-content: center;
    }

    .nav a {
      background: #222;
      color: white;
      padding: 10px 15px;
      text-decoration: none;
      border-radius: 6px;
    }

    .nav a:hover, .nav a.active {
      background: #e6c200;
      color: #000;
    }

    h1 {
      text-align: center;
      color: #000;
      margin: 30px 0 10px;
      font-family: 'Playfair Display', serif;
      font-size: 1.8rem;
    }

    form {
      max-width: 700px;
      margin: auto;
      background: white;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    label {
      display: block;
      margin-top: 1.5rem;
      font-weight: bold;
      color: #333;
    }

    .time-row {
      display: flex;
      gap: 10px;
      margin-top: 5px;
      flex-wrap: wrap;
    }

    .time-row input[type="time"] {
      flex: 1 1 48%;
      padding: 0.6rem;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    .btn {
      margin-top: 2rem;
      background: #e6c200;
      color: #000;
      border: none;
      width: 100%;
      padding: 1rem;
      font-size: 1rem;
      font-weight: bold;
      border-radius: 30px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .btn:hover {
      background: #000;
      color: #fff;
    }

    .success {
      max-width: 700px;
      margin: 1.5rem auto;
      background: #d4edda;
      color: #155724;
      padding: 1rem;
      border-left: 5px solid green;
      border-radius: 6px;
      text-align: center;
    }

    @media (max-width: 600px) {
      .time-row input[type="time"] {
        flex: 1 1 100%;
      }

      .header, .nav {
        flex-direction: column;
        align-items: center;
      }

      .header a {
        margin: 10px 0 0;
      }
    }
  </style>
</head>
<body>
  <div class="header">
    <h2> Horaires </h2>
    <div>
      <a href="logout.php">Déconnexion</a>
    </div>
  </div>

  <div class="nav">
    <a href="dashboard.php"> Dashboard</a>
  </div>

  <h1>Définir les horaires d'ouverture</h1>

  <?php if (!empty($message)): ?>
    <div class="success"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <form method="post">
    <?php foreach ($jours as $dayKey => $dayLabel): 
      $start = $horaires[$dayKey]['debut'] ?? '09:00';
      $end = $horaires[$dayKey]['fin'] ?? '18:00';
    ?>
      <label><?= $dayLabel ?></label>
      <div class="time-row">
        <input type="time" name="<?= $dayKey ?>_start" value="<?= $start ?>">
        <input type="time" name="<?= $dayKey ?>_end" value="<?= $end ?>">
      </div>
    <?php endforeach; ?>
    <button class="btn" type="submit">💾 Enregistrer</button>
  </form>
</body>
</html>



