<?php
session_start();
include '../includes/db.php';
if (!isset($_SESSION['admin'])) header('Location: login.php');

$clients = $pdo->query("SELECT DISTINCT name, email, phone FROM reservations")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title> Clients </title> 
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
      padding: 1rem;
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

    .table-container {
      overflow-x: auto;
    }

    table {
      width: 100%;
      min-width: 600px;
      background: white;
      border-collapse: collapse;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    th {
      background: #000;
      color: white;
      padding: 15px;
      text-align: left;
    }

    td {
      padding: 15px;
      border-bottom: 1px solid #eee;
    }

    @media (max-width: 768px) {
      header h1 {
        font-size: 1.2rem;
      }

      nav {
        flex-direction: column;
        align-items: center;
      }

      th, td {
        font-size: 14px;
        padding: 10px;
      }

      h2 {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>

  <header>
    <h1> Clients </h1>
    <div>
      <a href="logout.php">Déconnexion</a>
    </div>
  </header>

  <nav>
    <a href="dashboard.php"> Dashboard</a>
  </nav>

  <main>
    <h2>Liste des clients</h2>
    <div class="table-container">
      <table>
        <tr>
          <th>Nom</th>
          <th>Email</th>
          <th>Téléphone</th>
        </tr>
        <?php foreach ($clients as $c): ?>
          <tr>
            <td><?= htmlspecialchars($c['name']) ?></td>
            <td><?= htmlspecialchars($c['email']) ?></td>
            <td><?= htmlspecialchars($c['phone']) ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    </div>
  </main>

</body>
</html>

