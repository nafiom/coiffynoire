<?php
require 'includes/db.php';
$prestations = $pdo->query("SELECT * FROM galerie ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Galerie - Coiffynoire</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Open Sans', sans-serif;
      background-color: #fff;
      color: #111;
      animation: fadeIn 1s ease-in-out;
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
      font-size: 2rem;
      color: #fff;
    }

    nav a {
      margin-left: 1rem;
      color: white;
      text-decoration: none;
      font-weight: bold;
      position: relative;
    }

    nav a::after {
      content: '';
      display: block;
      width: 0;
      height: 2px;
      background: #aaa;
      transition: 0.3s;
      position: absolute;
      bottom: -4px;
      left: 0;
    }

    nav a:hover::after {
      width: 100%;
    }

    h2 {
      text-align: center;
      font-family: 'Playfair Display', serif;
      font-size: 2.5rem;
      margin: 3rem 0 2rem;
      color: #000;
    }

    .gallery-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 2rem;
      padding: 2rem;
      max-width: 1200px;
      margin: 0 auto;
    }

    .gallery-item {
      background: #fafafa;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 8px 16px rgba(0,0,0,0.06);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      cursor: pointer;
    }

    .gallery-item:hover {
      transform: scale(1.03);
      box-shadow: 0 12px 22px rgba(0,0,0,0.1);
    }

    .gallery-item img {
      width: 100%;
      aspect-ratio: 4/3;
      object-fit: cover;
      transition: transform 0.4s ease;
      display: block;
    }

    .gallery-info {
      padding: 1rem;
    }

    .gallery-info h3 {
      margin: 0 0 0.5rem;
      font-family: 'Playfair Display', serif;
      font-size: 1.2rem;
      color: #000;
    }

    .gallery-info p {
      font-size: 0.95rem;
      color: #444;
    }

    /* Modal */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background-color: rgba(0,0,0,0.85);
      justify-content: center;
      align-items: center;
      animation: fadeIn 0.3s ease;
    }

    .modal-content {
      max-width: 90%;
      max-height: 90%;
      border-radius: 10px;
    }

    .modal img {
      width: 100%;
      height: auto;
      border-radius: 10px;
      box-shadow: 0 0 25px rgba(0,0,0,0.4);
    }

    .modal-close {
      position: absolute;
      top: 20px;
      right: 30px;
      color: #fff;
      font-size: 2.5rem;
      font-weight: bold;
      cursor: pointer;
    }

    footer {
      background: #111;
      color: #eee;
      text-align: center;
      padding: 2rem 1rem;
      font-size: 0.95rem;
    }

    footer a {
      color: #ccc;
      text-decoration: none;
      margin: 0 0.5rem;
    }

    footer a:hover {
      text-decoration: underline;
    }

    @keyframes fadeIn {
      from {opacity: 0; transform: translateY(10px);}
      to {opacity: 1; transform: translateY(0);}
    }

    @media (max-width: 600px) {
      h2 { font-size: 2rem; }
      .gallery-info h3 { font-size: 1rem; }
    }
  </style>
</head>
<body>
  <header>
    <h1>Coiffynoire</h1>
    <nav>
      <a href="index.php">Accueil</a>
      <a href="tarifs.php">Tarifs</a>
      <a href="reservation.php">Réserver</a>
      <a href="gallery.php">Galerie</a>
      <a href="contact.php">Contact</a>
    </nav>
  </header>

  <main>
    <h2>Galerie de nos prestations</h2>
    <div class="gallery-container">
      <?php foreach ($prestations as $p): ?>
        <div class="gallery-item" onclick="openModal('uploads/<?= htmlspecialchars($p['image']) ?>')">
          <img src="uploads/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['titre']) ?>">
          <div class="gallery-info">
            <h3><?= htmlspecialchars($p['titre']) ?></h3>
            <p><?= htmlspecialchars($p['description']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </main>

  <!-- Modal -->
  <div class="modal" id="imageModal" onclick="closeModal()">
    <span class="modal-close">&times;</span>
    <div class="modal-content">
      <img id="modalImage" src="" alt="Zoom image">
    </div>
  </div>

  <footer>
    <p>&copy; <?= date('Y') ?> Coiffynoire · Tous droits réservés</p>
    <p>
      <a href="index.php">Accueil</a> · 
      <a href="tarifs.php">Tarifs</a> · 
      <a href="reservation.php">Réserver</a> · 
      <a href="contact.php">Contact</a>
    </p>
  </footer>

  <script>
    function openModal(src) {
      document.getElementById('modalImage').src = src;
      document.getElementById('imageModal').style.display = 'flex';
    }

    function closeModal() {
      document.getElementById('imageModal').style.display = 'none';
    }

    document.querySelector('.modal-close').addEventListener('click', closeModal);
  </script>
</body>
</html>


