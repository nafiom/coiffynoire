<?php
session_start();
include '../includes/db.php';
if (!isset($_SESSION['admin'])) header('Location: login.php');

// Suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    $stmt = $pdo->prepare("DELETE FROM prestations WHERE id = ?");
    $stmt->execute([$delete_id]);
}

// Ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nom'], $_POST['prix'])) {
    $nom         = trim($_POST['nom']);
    $prix        = floatval($_POST['prix']);
    $description = trim($_POST['description'] ?? '');
    $duree       = trim($_POST['duree'] ?? '1h');

    // Forcer l'ajout de "h" si ce n'est pas déjà présent
    if (!preg_match('/h$/i', $duree)) {
        $duree .= 'h';
    }

    $stmt = $pdo->prepare("INSERT INTO prestations (nom, prix, duree, description) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nom, $prix, $duree, $description]);
}

$services = $pdo->query("SELECT * FROM prestations ORDER BY nom")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Prestations </title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; }
    body {
      font-family: 'Open Sans', sans-serif;
      background: #f9f9f9;
      color: #111;
      margin: 0;
    }
    header {
      background: #000;
      color: white;
      padding: 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }
    header h1 {
      font-family: 'Playfair Display', serif;
      font-size: 1.5rem;
      color: #e6c200;
      margin: 0.5rem 0;
    }
    header a {
      color: white;
      text-decoration: none;
      margin-left: 1rem;
      font-weight: bold;
    }
    nav {
      display: flex;
      flex-wrap: wrap;
      gap: 0.7rem;
      background: #111;
      padding: 1rem 1rem;
      justify-content: center;
    }
    nav a {
      color: #fff;
      text-decoration: none;
      padding: 0.6rem 1rem;
      border-radius: 8px;
      background: #222;
      transition: 0.3s ease;
    }
    nav a.active,
    nav a:hover {
      background: #e6c200;
      color: #000;
    }
    main {
      padding: 2rem 1rem;
      max-width: 1000px;
      margin: auto;
    }
    h2 {
      font-family: 'Playfair Display', serif;
      font-size: 2rem;
      text-align: center;
      margin-bottom: 2rem;
    }
    form {
      background: white;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      margin-bottom: 3rem;
    }
    input, textarea, button, select {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 14px;
    }
    button {
      background: #000;
      color: #fff;
      border: none;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s ease;
    }
    button:hover {
      background: #e6c200;
      color: #000;
    }
    table {
      width: 100%;
      background: white;
      border-collapse: collapse;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      table-layout: fixed;
      word-wrap: break-word;
    }
    th, td {
      padding: 15px;
      text-align: left;
    }
    th {
      background: #000;
      color: white;
    }
    td {
      border-bottom: 1px solid #eee;
      vertical-align: top;
    }
    td strong {
      color: #000;
    }
    .delete-form {
      display: inline;
    }
    .delete-btn {
      background: #b30000;
      color: white;
      padding: 6px 10px;
      font-size: 13px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      margin-top: 0.5rem;
    }
    .delete-btn:hover {
      background: #ff3333;
    }
    @media (max-width: 768px) {
      nav { flex-direction: column; align-items: center; }
      form, table { padding: 1rem; }
      th, td { font-size: 14px; padding: 10px; }
      h2 { font-size: 1.5rem; }
    }
  </style>
</head>
<body>

  <header>
    <h1>Prestations</h1>
    <div>
      <a href="logout.php">Déconnexion</a>
    </div>
  </header>

  <nav>
    <a href="dashboard.php"> Dashboard</a>
  </nav>

  <main>
    <h2>Ajouter une prestation</h2>
    <form method="post">
      <input name="nom" placeholder="Nom du service" required>
      <input name="prix" type="number" step="0.01" placeholder="Prix (€)" required>
      <input name="duree" placeholder="Durée (ex: 1h, 2h30)" required>
      <textarea name="description" placeholder="Description du service" rows="3" required></textarea>
      <button type="submit">Ajouter</button>
    </form>

    <h2>Liste des prestations</h2>
    <table>
      <tr><th>Nom</th><th>Prix</th><th>Durée</th><th>Description</th><th>Action</th></tr>
      <?php foreach ($services as $s): ?>
        <tr>
          <td><strong><?= htmlspecialchars($s['nom']) ?></strong></td>
          <td><?= htmlspecialchars($s['prix']) ?> €</td>
          <td><?= htmlspecialchars($s['duree']) ?></td>
          <td><?= nl2br(htmlspecialchars($s['description'])) ?></td>
          <td>
            <form method="post" class="delete-form" onsubmit="return confirm('Confirmer la suppression ?')">
              <input type="hidden" name="delete_id" value="<?= $s['id'] ?>">
              <button class="delete-btn" type="submit">Supprimer</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </main>

</body>
</html>




