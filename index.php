<?php
session_start();
$connected = isset($_SESSION['user']);
include 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Coiffynoire</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap" rel="stylesheet">
  <!-- RemixIcon -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
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
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      z-index: 1000;
      transition: all 0.5s ease-in-out;
    }

    header h1 {
      font-family: 'Playfair Display', serif;
      font-size: 2.2rem;
      letter-spacing: 1px;
    }

    nav {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      align-items: center;
    }

    nav a {
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
      transition: 0.3s ease;
      position: absolute;
      bottom: -4px;
      left: 0;
    }

    nav a:hover::after {
      width: 100%;
    }

    .btn {
      background: #fff;
      color: #000;
      border: 2px solid #fff;
      padding: 0.5rem 1.2rem;
      border-radius: 30px;
      font-weight: bold;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .btn:hover {
      background: #000;
      color: #fff;
      border-color: #fff;
    }

    .burger {
      display: none;
      flex-direction: column;
      cursor: pointer;
    }

    .burger span {
      height: 3px;
      width: 25px;
      background: white;
      margin: 4px 0;
      transition: 0.3s;
    }

    @media (max-width: 768px) {
      nav {
        position: fixed;
        top: 72px;
        left: 0;
        right: 0;
        flex-direction: column;
        background: #000;
        display: none;
        text-align: center;
        padding: 1rem 0;
        z-index: 999;
      }

      nav.active {
        display: flex;
      }

      .burger {
        display: flex;
      }

      .btn {
        margin-top: 0.5rem;
      }
    }

    .hero, .section {
      animation: fadeIn 1s ease-in-out;
    }

    .hero {
      position: relative;
      height: 300px;
      display: flex;
      justify-content: center;
      align-items: center;
      background: #000;
      color: #fff;
      overflow: hidden;
    }

    .text-slide {
      position: absolute;
      font-size: 1.8rem;
      font-family: 'Playfair Display', serif;
      text-align: center;
      opacity: 0;
      transition: opacity 1s ease;
      padding: 0 1rem;
    }

    .text-slide.active {
      opacity: 1;
    }

    .section {
      padding: 4rem 2rem;
      text-align: center;
    }

    .section h3 {
      font-family: 'Playfair Display', serif;
      font-size: 2rem;
      margin-bottom: 1rem;
      color: #000;
    }

    .section p {
      max-width: 700px;
      margin: 0 auto;
      line-height: 1.7;
      color: #444;
      font-size: 1rem;
    }

    footer {
      background: #111;
      color: #ccc;
      padding: 2rem 1rem;
      text-align: center;
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
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>

<header id="mainHeader">
  <h1>Coiffynoire</h1>
  <div class="burger" onclick="toggleMenu()">
    <span></span><span></span><span></span>
  </div>
  <nav id="navLinks">
    <a href="index.php">Accueil</a>
    <?php if ($connected): ?>
      <a href="dashboard.php">Mon compte</a>
      <a href="logout.php">Se déconnecter</a>
    <?php else: ?>
      <a href="login.php">Connexion</a>
      <a href="register.php">Inscription</a>
    <?php endif; ?>
    <a href="tarifs.php">Tarifs</a>
    <a href="gallery.php">Galerie</a>
    <a href="contact.php">Contact</a>
    <a href="reservation.php" class="btn">Réserver</a>
  </nav>
</header>

<section class="hero">
  <div class="text-slide active">"L'élégance commence ici."</div>
  <div class="text-slide">"Offrez-vous une parenthèse de luxe et de style."</div>
  <div class="text-slide">"Coiffynoire – votre beauté, notre priorité."</div>
</section>

<section class="section">
  <h3>À propos de Coiffynoire</h3>
  <p>Installé au cœur de Paris, notre salon vous accueille dans une atmosphère élégante, minimaliste et chaleureuse.  
  Notre équipe de professionnels est dédiée à révéler votre beauté grâce à des soins capillaires haut de gamme.</p>
</section>

<section class="section">
  <h3>Nos horaires</h3>
  <p>
    Lundi - Vendredi : 9h00 - 18h00<br>
    Samedi : 10h00 - 17h00<br>
    Dimanche : Fermé
  </p>
</section>

<footer>
  <p>&copy; <?= date('Y') ?> Coiffynoire · Tous droits réservés</p>
  <p>
    <a href="contact.php">Contact</a> · 
    <a href="conditions_annulation.php">Politique d'annulation</a> · 
    <a href="tarifs.php">Tarifs</a> · 
    <a href="admin/login.php">Admin</a>
  </p>
  <p>01 23 45 67 89 · ✉ contact@coiffynoire.fr</p>
  <i class="ri-instagram-fill"></i>
</footer>

<script>
  function toggleMenu() {
    const nav = document.getElementById("navLinks");
    nav.classList.toggle("active");
  }

  // Fermer le menu burger après clic sur un lien
  document.querySelectorAll('#navLinks a').forEach(link => {
    link.addEventListener('click', () => {
      document.getElementById("navLinks").classList.remove("active");
    });
  });

  // Texte qui défile dans .hero
  let textIndex = 0;
  const texts = document.querySelectorAll('.text-slide');
  setInterval(() => {
    texts[textIndex].classList.remove('active');
    textIndex = (textIndex + 1) % texts.length;
    texts[textIndex].classList.add('active');
  }, 4000);

  // Ajuste le margin-top du body dynamiquement selon la hauteur du header
  window.addEventListener('DOMContentLoaded', () => {
    const header = document.getElementById('mainHeader');
    document.body.style.marginTop = header.offsetHeight + 'px';
  });
</script>

</body>
</html>









