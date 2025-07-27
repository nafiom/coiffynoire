<?php
session_start();
require 'includes/db.php';
require 'includes/mailer.php';

date_default_timezone_set('Europe/Paris');

$connected = isset($_SESSION['user']);
$user = $connected ? $_SESSION['user'] : null;

$error = '';
$disponibles = [];
$prestations = $pdo->query("SELECT * FROM prestations ORDER BY nom")->fetchAll();

// Formulaire final
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $services = $_POST['services'] ?? [];
    $date     = $_POST['date'] ?? '';
    $time     = $_POST['time'] ?? '';
    $avec     = $_POST['avec'] ?? '';
    $paiement = $_POST['paiement'] ?? 'stripe';

    if (empty($name) || empty($email) || empty($phone) || empty($services) || empty($date) || empty($time)) {
        $error = 'Tous les champs sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } else {
        $total = 0;
        foreach ($services as $s) {
            foreach ($prestations as $p) {
                if ($p['nom'] === $s) $total += $p['prix'];
            }
        }

        $_SESSION['pending_checkout'] = [
            'name'     => $name,
            'email'    => $email,
            'phone'    => $phone,
            'services' => $services,
            'date'     => $date,
            'time'     => $time,
            'avec'     => $avec,
            'paiement' => $paiement,
            'total'    => $total,
            'acompte'  => $paiement === 'sur_place' ? round($total * 0.10, 2) : $total
        ];

        header("Location: checkout.php");
        exit;
    }
}

// Créneaux dispo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['services']) && isset($_POST['date']) && !isset($_POST['submit'])) {
    $duree_min = 0;
    foreach ($_POST['services'] as $service) {
        $stmt = $pdo->prepare("SELECT duree FROM prestations WHERE nom = ?");
        $stmt->execute([$service]);
        $duree = $stmt->fetchColumn();
        if (preg_match('/(\d+)h(\d*)/', $duree, $m)) {
            $duree_min += intval($m[1]) * 60 + intval($m[2] ?: 0);
        } elseif (strpos($duree, 'min') !== false) {
            $duree_min += intval(str_replace('min', '', $duree));
        }
    }

    $joursMap = [
        'monday'=>'lundi', 'tuesday'=>'mardi', 'wednesday'=>'mercredi',
        'thursday'=>'jeudi', 'friday'=>'vendredi', 'saturday'=>'samedi', 'sunday'=>'dimanche'
    ];
    $jourEN = strtolower(date('l', strtotime($_POST['date'])));
    $jourFR = $joursMap[$jourEN] ?? $jourEN;

    $stmt = $pdo->prepare("SELECT debut, fin FROM horaires WHERE jour = ?");
    $stmt->execute([$jourFR]);
    $horaire = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT nombre FROM effectifs WHERE jour = ?");
    $stmt->execute([$jourFR]);
    $capacite = (int) ($stmt->fetchColumn() ?: 1);

    if ($horaire) {
        $start = new DateTime($horaire['debut']);
        $end   = new DateTime($horaire['fin']);
        $now   = new DateTime();
        $selected_date = $_POST['date'];
        $today = date('Y-m-d');
        $current_time = $now->format('H:i');

        $all_slots = [];
        while ($start < $end) {
            $slot_end = clone $start;
            $slot_end->add(new DateInterval("PT{$duree_min}M"));
            $slot_str = $start->format('H:i');

            if ($slot_end <= $end && ($selected_date > $today || ($selected_date === $today && $slot_str > $current_time))) {
                $all_slots[] = $slot_str;
            }

            $start->add(new DateInterval("PT30M"));
        }

        $occupation = [];
        $stmt = $pdo->prepare("SELECT time, services FROM reservations WHERE date = ?");
        $stmt->execute([$selected_date]);
        $rdvs = $stmt->fetchAll();

        foreach ($rdvs as $rdv) {
            $rdv_start = new DateTime($selected_date . ' ' . $rdv['time']);
            $services = explode(',', $rdv['services']);
            $total_duration = 0;

            foreach ($services as $srv) {
                $stmt2 = $pdo->prepare("SELECT duree FROM prestations WHERE nom = ?");
                $stmt2->execute([trim($srv)]);
                $duree = $stmt2->fetchColumn();
                if (preg_match('/(\d+)h(\d*)/', $duree, $m)) {
                    $total_duration += intval($m[1]) * 60 + intval($m[2] ?: 0);
                } elseif (strpos($duree, 'min') !== false) {
                    $total_duration += intval(str_replace('min', '', $duree));
                }
            }

            $rdv_end = clone $rdv_start;
            $rdv_end->add(new DateInterval("PT{$total_duration}M"));

            $cursor = clone $rdv_start;
            while ($cursor < $rdv_end) {
                $h = $cursor->format('H:i');
                $occupation[$h] = ($occupation[$h] ?? 0) + 1;
                $cursor->add(new DateInterval("PT30M"));
            }
        }

        foreach ($all_slots as $slot) {
            $slot_time = new DateTime($selected_date . ' ' . $slot);
            $end_time = clone $slot_time;
            $end_time->add(new DateInterval("PT{$duree_min}M"));

            $valide = true;
            $check = clone $slot_time;
            while ($check < $end_time) {
                $h = $check->format('H:i');
                $nb = $occupation[$h] ?? 0;
                if ($nb >= $capacite) {
                    $valide = false;
                    break;
                }
                $check->add(new DateInterval("PT30M"));
            }

            if ($valide) {
                $disponibles[] = $slot;
            }
        }
    }
}
?>

<!-- HTML -->
  <!DOCTYPE html>
  <html lang="fr">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - Coiffynoire</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <style>
      body {
        margin: 0;
        background: #fff;
        font-family: 'Open Sans', sans-serif;
        color: #111;
      }
      header {
        background: #000;
        padding: 1.5rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #fff;
      }
      header h1 {
        font-family: 'Playfair Display', serif;
        font-size: 2rem;
      }
      nav a {
        color: white;
        text-decoration: none;
        margin-left: 1rem;
        font-weight: bold;
      }
      nav a:hover {
        color: lightgray;
      }
      .container {
        max-width: 600px;
        margin: 4rem auto;
        padding: 2rem;
        background: #f9f9f9;
        border-radius: 10px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
      }
      h1 {
        font-family: 'Playfair Display', serif;
        font-size: 2.2rem;
        text-align: center;
      }
      label {
        display: block;
        margin-top: 1rem;
        font-weight: bold;
      }
      input, select {
        width: 100%;
        padding: 0.7rem;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 1rem;
        margin-top: 0.4rem;
      }
      .error {
        background: #f8d7da;
        color: #721c24;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        border: 1px solid #f5c6cb;
      }
      button[type="submit"] {
        margin-top: 2rem;
        background: #fff;
        color: #000;
        padding: 0.8rem 1.5rem;
        border: 2px solid #000;
        border-radius: 50px;
        font-weight: bold;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
      }
      button[type="submit"]:hover {
        background: #000;
        color: #fff;
      }
      .bottom-links {
        text-align: center;
        margin-top: 1rem;
      }
      .bottom-links a {
        color: #e6c200;
        text-decoration: none;
        font-weight: bold;
      }
      .bottom-links a:hover {
        text-decoration: underline;
      }
      @media (max-width: 768px) {
        header {
          flex-direction: column;
          align-items: flex-start;
        }
        nav {
          margin-top: 1rem;
        }
        .container {
          margin: 2rem 1rem;
          padding: 1.5rem;
        }
      }
    </style>
  </head>
  <body>

  <header>
    <h1>Coiffynoire</h1>
    <nav>
      <a href="index.php">Accueil</a>
      <a href="tarifs.php">Tarifs</a>
      <a href="gallery.php">Galerie</a>
      <a href="contact.php">Contact</a>
    </nav>
  </header>

  <div class="container">
    <h1>Réserver un créneau</h1>

    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" id="reservation-form">
      <label>Nom *</label>
      <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? ($user['name'] ?? '')) ?>">

      <label>Email *</label>
      <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? ($user['email'] ?? '')) ?>">

      <label>Téléphone *</label>
      <input type="tel" name="phone" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">

      <label>Je souhaite être coiffé(e) par :</label>
      <input type="text" name="avec" placeholder="Nom du coiffeur/se" value="<?= htmlspecialchars($_POST['avec'] ?? '') ?>">

      <label>Prestations *</label>
      <select id="services" name="services[]" multiple required>
        <?php foreach ($prestations as $p): ?>
          <option value="<?= $p['nom'] ?>" <?= in_array($p['nom'], $_POST['services'] ?? []) ? 'selected' : '' ?>>
            <?= $p['nom'] ?> (<?= $p['duree'] ?>)
          </option>
        <?php endforeach; ?>
      </select>

      <label>Date *</label>
      <input type="date" name="date" id="date" min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($_POST['date'] ?? '') ?>" required>

      <label>Heure *</label>
      <select name="time" id="time" required>
        <option value="">-- Sélectionnez une heure --</option>
        <?php foreach ($disponibles as $h): ?>
          <option value="<?= $h ?>" <?= ($_POST['time'] ?? '') === $h ? 'selected' : '' ?>><?= $h ?></option>
        <?php endforeach; ?>
      </select>

      <label>Méthode de paiement *</label>
      <select name="paiement" required>
        <option value="stripe" <?= ($_POST['paiement'] ?? '') === 'stripe' ? 'selected' : '' ?>>Paiement en ligne (100%)</option>
        <option value="sur_place" <?= ($_POST['paiement'] ?? '') === 'sur_place' ? 'selected' : '' ?>>Paiement sur place (acompte 10%)</option>
      </select>

      <button type="submit" name="submit">Réserver</button>

      <div class="bottom-links">
        <a href="conditions_annulation.php">📄 Conditions d'annulation</a>
        <p>Fermé le dimanche</p>
      </div>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
  <script>
    const servicesSelect = new Choices('#services', {
      removeItemButton: true,
      placeholder: true,
      placeholderValue: 'Sélectionnez une ou plusieurs prestations',
      searchPlaceholderValue: 'Rechercher une prestation...'
    });

    const form = document.getElementById('reservation-form');
    const dateInput = document.getElementById('date');
    const timeSelect = document.getElementById('time');

    function fetchCreneaux() {
      const formData = new FormData(form);
      formData.delete('submit'); // on n'envoie pas le bouton de réservation

      fetch('', {
        method: 'POST',
        body: formData
      })
      .then(resp => resp.text())
      .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newTimeOptions = doc.querySelector('#time').innerHTML;
        timeSelect.innerHTML = newTimeOptions;
      });
    }

    dateInput.addEventListener('change', fetchCreneaux);
    document.getElementById('services').addEventListener('change', fetchCreneaux);
  </script>

  </body>
  </html>

















