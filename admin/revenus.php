<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['admin'])) header('Location: login.php');

// Sélection de la date filtrée
$date_filter = $_GET['date'] ?? date('Y-m-d');

// Dates distinctes disponibles
$dates = $pdo->query("
    SELECT DISTINCT date 
    FROM reservations 
    WHERE date IS NOT NULL 
    ORDER BY date(date) DESC
")->fetchAll(PDO::FETCH_COLUMN);

// Revenus par mois (hors remboursées)
$mois_stmt = $pdo->query("
    SELECT strftime('%Y-%m', date) AS mois, SUM(p.prix) AS total
    FROM reservations r
    JOIN prestations p ON r.services LIKE '%' || p.nom || '%'
    WHERE r.statut != 'remboursee'
    GROUP BY mois
    ORDER BY mois DESC
");
$mois_data = $mois_stmt->fetchAll(PDO::FETCH_ASSOC);

// Revenu total global
$total_global = $pdo->query("
    SELECT SUM(p.prix)
    FROM reservations r
    JOIN prestations p ON r.services LIKE '%' || p.nom || '%'
    WHERE r.statut != 'remboursee'
")->fetchColumn();

// Revenus du jour sélectionné
$revenus = $pdo->prepare("
    SELECT r.*, p.nom AS prestation, p.prix 
    FROM reservations r
    JOIN prestations p ON r.services LIKE '%' || p.nom || '%'
    WHERE r.date = ? AND r.statut != 'remboursee'
");
$revenus->execute([$date_filter]);
$rows = $revenus->fetchAll();
$total_jour = array_sum(array_column($rows, 'prix'));

// Remboursements du jour
$rembourses = $pdo->prepare("
    SELECT r.*, p.nom AS prestation, p.prix 
    FROM reservations r
    JOIN prestations p ON r.services LIKE '%' || p.nom || '%'
    WHERE r.date = ? AND r.statut = 'remboursee'
");
$rembourses->execute([$date_filter]);
$refunded = $rembourses->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title> Revenus – Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap" rel="stylesheet">
  <style>
    body { margin: 0; font-family: 'Open Sans', sans-serif; background: #fff; color: #111; }
    header { background: #000; color: #fff; padding: 1.2rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
    header h1 { font-family: 'Playfair Display', serif; font-size: 1.6rem; color: #e6c200; }
    nav { display: flex; flex-wrap: wrap; gap: 0.7rem; background: #111; padding: 1rem; justify-content: center; }
    nav a { color: #fff; text-decoration: none; padding: 0.6rem 1rem; border-radius: 8px; background: #222; font-size: 0.95rem; transition: 0.3s ease; }
    nav a.active, nav a:hover { background: #e6c200; color: #000; }

    .container { max-width: 1100px; margin: 2rem auto; background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 6px 18px rgba(0,0,0,0.08); }

    h2 { font-family: 'Playfair Display', serif; font-size: 1.8rem; margin-bottom: 1.2rem; text-align: center; color: #000; }
    .section { margin-bottom: 3rem; }

    .global-total { text-align: center; font-size: 1.2rem; font-weight: bold; background: #fdf8e6; color: #000; padding: 1rem; border: 1px solid #e6c200; border-radius: 10px; margin-bottom: 2rem; }

    table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    th, td { padding: 1rem; border-bottom: 1px solid #eee; text-align: left; font-size: 0.95rem; }
    th { background: #000; color: #fff; }
    tr:nth-child(even) { background: #f9f9f9; }

    .total { text-align: right; font-size: 1.2rem; font-weight: bold; margin-top: 1.5rem; }

    .filter-form { text-align: center; margin-bottom: 2rem; }
    select { padding: 0.6rem 1rem; font-size: 1rem; border-radius: 8px; border: 1px solid #ccc; }
    button { padding: 0.6rem 1.2rem; background: #000; color: #fff; border-radius: 8px; border: none; font-weight: bold; cursor: pointer; transition: 0.3s ease; }
    button:hover { background: #e6c200; color: #000; }

    @media (max-width: 768px) {
      th, td { font-size: 0.9rem; padding: 0.8rem; }
      h2 { font-size: 1.5rem; }
      .container { padding: 1rem; }
    }
  </style>
</head>
<body>

<header>
  <h1> Revenus </h1>
  <div>
    <a href="logout.php">Déconnexion</a>
  </div>
</header>

<nav>
  <a href="dashboard.php"> Dashboard</a>
</nav>

<div class="container">

  <!-- Revenus totaux -->
  <div class="section">
    <h2> Revenus totaux générés</h2>
    <div class="global-total">
      Total général (hors remboursements) : <strong><?= number_format($total_global, 2, ',', ' ') ?> €</strong>
    </div>
  </div>

  <!-- Revenus par mois -->
  <div class="section">
    <h2> Revenus par mois</h2>
    <table>
      <thead>
        <tr>
          <th>Mois</th>
          <th>Montant total (€)</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($mois_data as $row): ?>
          <tr>
            <td><?= date('F Y', strtotime($row['mois'] . '-01')) ?></td>
            <td><?= number_format($row['total'], 2, ',', ' ') ?> €</td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Revenus du jour -->
  <div class="section">
    <h2>📅 Revenus du <?= htmlspecialchars($date_filter) ?></h2>
    <div class="filter-form">
      <form method="get">
        <label for="date">Sélectionner une date :</label>
        <select name="date" id="date">
          <?php foreach ($dates as $d): ?>
            <option value="<?= $d ?>" <?= $d === $date_filter ? 'selected' : '' ?>><?= $d ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit">Voir</button>
      </form>
    </div>

    <table>
      <thead>
        <tr>
          <th>Client</th>
          <th>Email</th>
          <th>Téléphone</th>
          <th>Prestation</th>
          <th>Montant (€)</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($rows)): ?>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['name']) ?></td>
              <td><?= htmlspecialchars($r['email']) ?></td>
              <td><?= htmlspecialchars($r['phone']) ?></td>
              <td><?= htmlspecialchars($r['service']) ?></td>
              <td><?= number_format($r['prix'], 2, ',', ' ') ?> €</td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="5" style="text-align:center;">Aucun revenu ce jour-là.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
    <p class="total">Total du jour : <?= number_format($total_jour, 2, ',', ' ') ?> €</p>
  </div>

  <!-- Remboursements -->
  <?php if (count($refunded)): ?>
    <div class="section">
      <h2>❌ Remboursements du <?= htmlspecialchars($date_filter) ?></h2>
      <table>
        <thead>
          <tr>
            <th>Client</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th>Prestation</th>
            <th>Montant remboursé (€)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($refunded as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['name']) ?></td>
              <td><?= htmlspecialchars($r['email']) ?></td>
              <td><?= htmlspecialchars($r['phone']) ?></td>
              <td><?= htmlspecialchars($r['service']) ?></td>
              <td><?= number_format($r['prix'], 2, ',', ' ') ?> €</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

</div>
</body>
</html>






