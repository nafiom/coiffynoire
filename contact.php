<?php
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name && $email && $message) {
        $stmt = $pdo->prepare("INSERT INTO messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $message]);
        $confirmation = "✅ Votre message a bien été envoyé.";
    } else {
        $error = "❌ Tous les champs sont obligatoires.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Contact - Coiffynoire</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Open Sans', sans-serif;
      background: #fff;
      color: #111;
      animation: fadeIn 0.8s ease-in;
    }

    header {
      background: #000;
      color: #fff;
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
      margin-left: 1.2rem;
      color: #fff;
      text-decoration: none;
      font-weight: bold;
      transition: color 0.3s;
    }

    nav a:hover {
      color: #aaa;
    }

    main {
      padding: 3rem 1.5rem;
      max-width: 900px;
      margin: auto;
    }

    h2 {
      font-size: 2.5rem;
      font-family: 'Playfair Display', serif;
      text-align: center;
      margin-bottom: 1rem;
    }

    p {
      text-align: center;
      font-size: 1.1rem;
      margin-bottom: 2rem;
    }

    .contact-form {
      background: #f7f7f7;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }

    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
    }

    input, textarea {
      width: 100%;
      padding: 0.75rem;
      margin-bottom: 1.5rem;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 1rem;
    }

    textarea {
      resize: vertical;
      min-height: 100px;
    }

    .btn {
      display: inline-block;
      background: #000;
      color: #fff;
      padding: 0.9rem 2rem;
      border: none;
      border-radius: 50px;
      text-decoration: none;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .btn:hover {
      background: #444;
    }

    .success-message, .error-message {
      text-align: center;
      font-weight: bold;
      margin: 1rem auto 2rem;
      padding: 1rem;
      border-radius: 8px;
      max-width: 600px;
    }

    .success-message {
      background: #d4edda;
      color: #155724;
    }

    .error-message {
      background: #f8d7da;
      color: #721c24;
    }

    .contact-info {
      margin-top: 3rem;
      text-align: center;
      font-size: 1rem;
      color: #555;
    }

    .contact-info h3 {
      font-family: 'Playfair Display', serif;
      margin-bottom: 0.5rem;
      font-size: 1.3rem;
      color: #000;
    }

    footer {
      background: #111;
      color: #eee;
      padding: 2rem 1rem;
      text-align: center;
      margin-top: 4rem;
      font-size: 0.9rem;
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

    @media (max-width: 600px) {
      h2 { font-size: 2rem; }
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
      <a href="contact.php">Contact</a>
    </nav>
  </header>

  <main>
    <h2>Contactez-nous</h2>
    <p>Une question ? Besoin d’un conseil ou d’un renseignement ?</p>

    <?php if (!empty($confirmation)): ?>
      <div class="success-message"><?= htmlspecialchars($confirmation) ?></div>
    <?php elseif (!empty($error)): ?>
      <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form class="contact-form" method="post" action="">
      <label for="name">Nom</label>
      <input type="text" id="name" name="name" required>

      <label for="email">Email</label>
      <input type="email" id="email" name="email" required>

      <label for="message">Message</label>
      <textarea id="message" name="message" required></textarea>

      <button type="submit" class="btn">Envoyer le message</button>
    </form>

    <div class="contact-info">
      <h3>📍 Salon Coiffynoire</h3>
      <p>7 Rue de l'Élégance, 75000 Paris</p>
      <p>📞 01 23 45 67 89</p>
      <p>✉ contact@coiffynoire.fr</p>
      <p>🕓 Ouvert du mardi au samedi de 9h à 18h</p>
    </div>
  </main>

  <footer>
    <p>&copy; <?= date('Y') ?> Coiffynoire · Tous droits réservés</p>
    <p>
      <a href="index.php">Accueil</a> · 
      <a href="tarifs.php">Tarifs</a> · 
      <a href="reservation.php">Réserver</a> · 
      <a href="contact.php">Contact</a>
    </p>
  </footer>

</body>
</html>


