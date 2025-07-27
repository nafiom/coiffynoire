<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
include '../includes/db.php';

$today = date('Y-m-d');
$today_reservations = $pdo->query("SELECT COUNT(*) FROM reservations WHERE date = '$today'")->fetchColumn();
$total_reservations = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
$this_month_reservations = $pdo->query("SELECT COUNT(*) FROM reservations WHERE date >= '" . date('Y-m-01') . "'")->fetchColumn();

$today_stmt = $pdo->prepare("SELECT * FROM reservations WHERE date = ? ORDER BY time");
$today_stmt->execute([$today]);
$today_bookings = $today_stmt->fetchAll();

// Derniers clients inscrits
$users_stmt = $pdo->query("SELECT name, email, created_at FROM users ORDER BY created_at DESC LIMIT 10");
$recent_users = $users_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Tableau de bord</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Open Sans', sans-serif;
      background-color: #fff;
      color: #111;
    }

    header {
      background: #000;
      color: white;
      padding: 1.5rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    header h1 {
      font-family: 'Playfair Display', serif;
      font-size: 1.8rem;
      color: #e6c200;
    }

    header a {
      color: white;
      text-decoration: none;
      font-weight: bold;
      margin-left: 1rem;
    }

    nav {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      background: #111;
      padding: 1rem 2rem;
    }

    nav a {
      color: #fff;
      text-decoration: none;
      padding: 0.6rem 1rem;
      border-radius: 8px;
      background: #222;
      transition: 0.3s ease;
    }

    nav a:hover {
      background: #e6c200;
      color: #000;
    }

    .stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 1.5rem;
      padding: 2rem;
    }

    .stat-card {
      background: #f4f4f4;
      padding: 2rem;
      border-radius: 15px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      text-align: center;
      transition: 0.3s ease;
    }

    .stat-card:hover {
      transform: scale(1.03);
      box-shadow: 0 6px 15px rgba(0,0,0,0.15);
    }

    .stat-number {
      font-size: 2.5rem;
      color: #e6c200;
      font-family: 'Playfair Display', serif;
      margin-bottom: 0.5rem;
    }

    h3 {
      text-align: center;
      font-size: 1.8rem;
      font-family: 'Playfair Display', serif;
      margin-top: 2rem;
    }

    table {
      width: 90%;
      margin: 2rem auto;
      border-collapse: collapse;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    th, td {
      padding: 1rem;
      text-align: left;
      font-size: 0.95rem;
    }

    th {
      background: #e6c200;
      color: #000;
      font-weight: bold;
    }

    tr:nth-child(even) {
      background: #f9f9f9;
    }

    tr:hover {
      background: #f1f1f1;
    }

    footer {
      text-align: center;
      padding: 2rem;
      background: #111;
      color: #eee;
      margin-top: 3rem;
    }

    footer a {
      color: #e6c200;
      text-decoration: none;
    }

    footer a:hover {
      text-decoration: underline;
    }

    @media(max-width: 768px) {
      nav {
        flex-direction: column;
        align-items: flex-start;
      }
    }
  </style>
</head>
<body>

<header>
  <h1>Admin </h1> 
  <div>
    <a href="logout.php">Déconnexion</a>
  </div>
</header>

<nav>
  <a href="dashboard.php"> Dashboard</a>
  <a href="mon_compte.php"> Mon compte</a>
  <a href="reservations.php"> Réservations</a>
  <a href="calendrier.php"> Calendrier</a>
  <a href="prestations.php"> Prestations</a>
  <a href="clients.php"> Clients</a>
  <a href="revenus.php"> Revenus</a>
  <a href="upload_galerie.php"> Galerie</a>
  <a href="messages.php"> Messages</a>
  <a href="horaires.php"> Horaires</a>
  <a href="employes.php"> Employés</a>
</nav>

<section class="stats">
  <div class="stat-card">
    <div class="stat-number"><?= $today_reservations ?></div>
    <div>Rendez-vous aujourd’hui</div>
  </div>
  <div class="stat-card">
    <div class="stat-number"><?= $this_month_reservations ?></div>
    <div>Réservations ce mois-ci</div>
  </div>
  <div class="stat-card">
    <div class="stat-number"><?= $total_reservations ?></div>
    <div>Total des réservations</div>
  </div>
</section>

<h3>📆 Rendez-vous du <?= date('d/m/Y') ?></h3>

<?php if (count($today_bookings) > 0): ?>
  <table>
    <tr>
      <th>Heure</th>
      <th>Client</th>
      <th>Service</th>
      <th>Téléphone</th>
    </tr>
    <?php foreach ($today_bookings as $booking): ?>
      <tr>
        <td><?= htmlspecialchars($booking['time']) ?></td>
        <td><?= htmlspecialchars($booking['name']) ?></td>
        <td><?= htmlspecialchars($booking['service']) ?></td>
        <td><?= htmlspecialchars($booking['phone']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php else: ?>
  <p style="text-align: center; color: #555; margin-top: 2rem;">Aucun rendez-vous prévu aujourd’hui.</p>
<?php endif; ?>

<h3>👥 Derniers clients inscrits</h3>
<?php if (count($recent_users) > 0): ?>
  <table>
    <tr>
      <th>Nom</th>
      <th>Email</th>
      <th>Date d’inscription</th>
    </tr>
    <?php foreach ($recent_users as $user): ?>
      <tr>
        <td><?= htmlspecialchars($user['name']) ?></td>
        <td><?= htmlspecialchars($user['email']) ?></td>
        <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php else: ?>
  <p style="text-align: center;">Aucun client inscrit pour le moment.</p>
<?php endif; ?>

<footer>
  <p>&copy; <?= date('Y') ?> Coiffynoire · Tous droits réservés · <a href="../index.php">Retour au site</a></p>
</footer>

</body>
</html>



