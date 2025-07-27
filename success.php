<?php
session_start();
require 'includes/db.php';
require 'includes/mailer.php';
require_once 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
\Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

if (!isset($_SESSION['pending_checkout']) || !isset($_GET['session_id'])) {
    header('Location: reservation.php');
    exit;
}

$data = $_SESSION['pending_checkout'];
$stripeSessionId = $_GET['session_id'];

try {
    $session = \Stripe\Checkout\Session::retrieve($stripeSessionId);
    $paymentIntentId = $session->payment_intent;
} catch (Exception $e) {
    die("Erreur Stripe : " . $e->getMessage());
}

$name     = trim($data['name']);
$email    = trim($data['email']);
$phone    = trim($data['phone']);
$services = $data['services'];
$date     = $data['date'];
$time     = $data['time'];
$avec     = $data['avec'] ?? '';
$total    = $data['total'];
$paiement = $data['paiement'];
$acompte  = ($paiement === 'sur_place') ? round($total * 0.10, 2) : $total;

// Vérifier la disponibilité du créneau
$joursMap = [
    'monday'    => 'lundi',
    'tuesday'   => 'mardi',
    'wednesday' => 'mercredi',
    'thursday'  => 'jeudi',
    'friday'    => 'vendredi',
    'saturday'  => 'samedi',
    'sunday'    => 'dimanche'
];
$jourFR = $joursMap[strtolower(date('l', strtotime($date)))] ?? 'lundi';
$stmt = $pdo->prepare("SELECT nombre FROM effectifs WHERE jour = ?");
$stmt->execute([$jourFR]);
$capacite = (int) $stmt->fetchColumn();

$check = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE date = ? AND time = ? AND statut = 'active'");
$check->execute([$date, $time]);
$nbExistantes = (int) $check->fetchColumn();

if ($nbExistantes >= $capacite) {
    unset($_SESSION['pending_checkout']);
    die('Ce créneau est déjà complet. Merci de choisir un autre horaire.');
}

try {
    // Enregistrement de la réservation
    $stmt = $pdo->prepare("INSERT INTO reservations (name, email, phone, services, date, time, avec, total, paiement, acompte, statut) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
    $stmt->execute([
        $name,
        $email,
        $phone,
        implode(', ', $services),
        $date,
        $time,
        $avec,
        $total,
        $paiement,
        $acompte
    ]);
    $reservationId = $pdo->lastInsertId();

    $update = $pdo->prepare("UPDATE reservations SET payment_intent = ? WHERE id = ?");
    $update->execute([$paymentIntentId, $reservationId]);

    // Email client HTML
    $html = '
    <div style="background:#000; padding:40px; font-family:Open Sans, sans-serif; color:#fff;">
      <div style="max-width:600px; margin:auto; background:#fff; border-radius:10px; padding:30px; color:#111;">
        <h1 style="font-family:\'Playfair Display\', serif; color:#000; font-size:24px;">Merci pour votre réservation</h1>
        <p style="font-size:16px; line-height:1.6;">
          Bonjour <strong>' . htmlspecialchars($name) . '</strong>,<br><br>
          Votre rendez-vous chez <strong>Coiffynoire</strong> est bien enregistré.<br><br>
          <strong>Détails de la réservation :</strong>
        </p>
        <ul style="font-size:15px; line-height:1.6; list-style:none; padding:0;">
          <li><strong>Date :</strong> ' . htmlspecialchars($date) . ' à ' . htmlspecialchars($time) . '</li>
          <li><strong>Prestations :</strong> ' . implode(', ', array_map('htmlspecialchars', $services)) . '</li>
          <li><strong>Coiffeur(se) :</strong> ' . ($avec ? htmlspecialchars($avec) : 'Non précisé') . '</li>
          <li><strong>Montant :</strong> ' . number_format($total, 2, ',', ' ') . ' €</li>
          <li><strong>Paiement :</strong> ' . ($paiement === 'stripe' ? 'En ligne (100%)' : 'Sur place (acompte de ' . number_format($acompte, 2, ',', ' ') . ' €)') . '</li>
        </ul>
        <p>Un email de confirmation vous a été envoyé.</p>
        <div style="margin-top:30px; text-align:center;">
          <a href="https://coiffynoire.fr" style="display:inline-block; padding:10px 20px; background:#000; color:#fff; border-radius:30px; text-decoration:none;">Retour au site</a>
        </div>
      </div>
    </div>';

    sendMail($email, "Confirmation de votre réservation - Coiffynoire", $html);

    // Email admin (texte brut)
    $adminMsg = "
    Nouvelle réservation :
    Nom : $name
    Email : $email
    Téléphone : $phone
    Date : $date à $time
    Prestations : " . implode(', ', $services) . "
    Coiffeur(se) : " . ($avec ?: 'Non précisé') . "
    Paiement : $paiement
    Montant : $total €
    ";
    sendMail("itsjessicaqueen@hotmail.it", "Nouvelle réservation - $name", nl2br($adminMsg));

    unset($_SESSION['pending_checkout'], $_SESSION['stripe_session_id']);
} catch (Exception $e) {
    die('Erreur lors de l’enregistrement : ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Confirmation - Coiffynoire</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: 'Open Sans', sans-serif;
      background: #f4f4f4;
      padding: 2rem;
      color: #111;
      text-align: center;
    }
    .box {
      background: #fff;
      max-width: 600px;
      margin: 3rem auto;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    h1 {
      color: #000;
      font-family: 'Playfair Display', serif;
      margin-bottom: 1.5rem;
    }
    p {
      font-size: 1.1rem;
      line-height: 1.6;
    }
    a {
      display: inline-block;
      margin-top: 2rem;
      padding: 0.8rem 1.5rem;
      border-radius: 30px;
      background: #000;
      color: #fff;
      text-decoration: none;
      font-weight: bold;
      transition: 0.3s;
    }
    a:hover {
      background: #e6c200;
      color: #000;
    }
  </style>
</head>
<body>
  <div class="box">
    <h1>Merci pour votre réservation</h1>
    <p>Une confirmation a été envoyée à : <strong><?= htmlspecialchars($email) ?></strong></p>
    <p>Nous avons hâte de vous accueillir très bientôt chez Coiffynoire.</p>
    <a href="index.php">Retour à l'accueil</a>
    <a href="conditions_annulation.php">Conditions d'annulation</a>
  </div>
</body>
</html>











