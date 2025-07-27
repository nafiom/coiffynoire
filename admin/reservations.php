<?php
session_start();
require_once '../includes/db.php';

$today = date('Y-m-d');
$selectedDate = $_GET['date'] ?? '';
$search = trim($_GET['q'] ?? '');

// Construction de la requête dynamique
$sql = "SELECT * FROM reservations WHERE 1";
$params = [];

if ($selectedDate) {
    $sql .= " AND date = ?";
    $params[] = $selectedDate;
}

if ($search) {
    $sql .= " AND (name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY date DESC, time DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservations = $stmt->fetchAll();

$remboursées = array_filter($reservations, fn($r) => $r['statut'] === 'remboursee');
$autres = array_filter($reservations, fn($r) => $r['statut'] !== 'remboursee');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Réservations – Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Open+Sans&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Open Sans', sans-serif; background: #f5f5f5; margin: 0; }
    header { background: #000; color: #fff; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
    header h1 { font-family: 'Playfair Display', serif; color: #e6c200; margin: 0; }
    nav a { color: #fff; margin-left: 1rem; font-weight: bold; text-decoration: none; }
    nav a:hover { color: #e6c200; }
    .container { max-width: 1100px; margin: 2rem auto; background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1); }
    h2 { font-family: 'Playfair Display', serif; margin-bottom: 1.5rem; }
    .export-btn { background: #28a745; color: #fff; padding: 0.6rem 1.4rem; border-radius: 8px; border: none; font-weight: bold; cursor: pointer; margin-bottom: 1rem; }
    .export-btn:hover { background: #218838; }
    .filters form { display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; margin-bottom: 1rem; }
    .filters input { padding: 0.5rem; border-radius: 6px; border: 1px solid #ccc; font-size: 0.95rem; }
    .table-responsive { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; min-width: 900px; margin-bottom: 3rem; }
    th, td { padding: 0.75rem; border-bottom: 1px solid #ccc; text-align: left; font-size: 0.95rem; vertical-align: top; }
    th { background: #e6c200; color: black; position: sticky; top: 0; z-index: 1; }
    tr:nth-child(even) { background: #f9f9f9; }

    .btn-red, .btn-yellow, .btn-blue {
      padding: 0.4rem 0.8rem;
      border: none;
      border-radius: 6px;
      color: white;
      cursor: pointer;
      font-size: 0.85rem;
      display: inline-block;
      margin: 0.2rem 0;
      white-space: nowrap;
    }
    .btn-red { background: #dc3545; }
    .btn-red:hover { background: #c82333; }
    .btn-yellow { background: #ffc107; color: black; }
    .btn-yellow:hover { background: #e0a800; }
    .btn-blue { background: #007bff; }
    .btn-blue:hover { background: #0056b3; }

    .statut { font-weight: bold; text-transform: capitalize; }
    .statut.active { color: green; }
    .statut.annulee { color: #999; }
    .statut.remboursee { color: red; }

    @media (max-width: 768px) {
      table { font-size: 0.85rem; }
      .btn-red, .btn-yellow, .btn-blue {
        font-size: 0.75rem;
        padding: 0.3rem 0.6rem;
      }
    }
  </style>
</head>
<body>

<header>
  <h1> Réservations</h1>
  <nav>
    <a href="logout.php">Déconnexion</a>
    <a href="dashboard.php"> Dashboard</a>
  </nav>
</header>

<div class="container">
  <h2>Réservations en cours / annulées</h2>

  <div class="filters">
    <form method="get">
      <label for="date">Date :</label>
      <input type="date" id="date" name="date" value="<?= htmlspecialchars($selectedDate) ?>">

      <label for="q">🔍 Client ou email :</label>
      <input type="text" id="q" name="q" placeholder="ex: Marie, contact@mail.com" value="<?= htmlspecialchars($search) ?>">

      <button type="submit" class="export-btn">🔎 Filtrer</button>
    </form>

    <button class="export-btn" onclick="exportCSV()">📤 Exporter en CSV</button>
  </div>

  <div class="table-responsive">
    <table>
      <thead>
        <tr>
          <th>Date</th>
          <th>Heure</th>
          <th>Client</th>
          <th>Email</th>
          <th>Téléphone</th>
          <th>Service</th>
          <th>Montant (€)</th>
          <th>Acompte (€)</th>
          <th>Paiement</th>
          <th>Statut</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($autres) > 0): ?>
          <?php foreach ($autres as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['date']) ?></td>
              <td><?= htmlspecialchars($r['time']) ?></td>
              <td><?= htmlspecialchars($r['name']) ?></td>
              <td><?= htmlspecialchars($r['email']) ?></td>
              <td><?= htmlspecialchars($r['phone']) ?></td>
              <td><?= htmlspecialchars($r['service']) ?></td>
              <td><?= number_format($r['total'], 2, ',', ' ') ?> €</td>
              <td>
                <?= $r['paiement'] === 'admin' ? '—' : ($r['acompte'] ? number_format($r['acompte'], 2, ',', ' ') . ' €' : '—') ?>
              </td>
              <td>
                <?= $r['paiement'] === 'sur_place' ? 'Sur place' : ($r['paiement'] === 'admin' ? 'Ajout admin' : 'Stripe') ?>
              </td>
              <td class="statut <?= $r['statut'] ?>"><?= $r['statut'] ?></td>
              <td>
                <?php if ($r['statut'] === 'active'): ?>
                  <?php if ($r['date'] >= $today): ?>
                    <form method="post" action="delete_reservation.php" style="display:inline;" onsubmit="return confirm('Supprimer cette réservation ?')">
                      <input type="hidden" name="id" value="<?= $r['id'] ?>">
                      <button type="submit" class="btn-red">🗑 Supprimer</button>
                    </form>
                  <?php endif; ?>
                  <?php if ($r['paiement'] === 'stripe' && $r['payment_intent']): ?>
                    <a href="rembourser.php?id=<?= $r['id'] ?>" onclick="return confirm('Confirmer le remboursement ?')" class="btn-blue">💸 Rembourser</a>
                  <?php endif; ?>
                  <a href="annuler.php?id=<?= $r['id'] ?>" onclick="return confirm('Annuler cette réservation ?')" class="btn-yellow">⚠️ Annuler</a>
                <?php else: ?>
                  <em style="color: #888;">Aucune action</em>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="11" style="text-align:center;">Aucune réservation.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  function exportCSV() {
    window.location.href = 'export_csv.php';
  }
</script>

</body>
</html>














