<?php
session_start();
require_once '../includes/db.php';
require_once '../vendor/autoload.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

\Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

// Vérifie que l'ID est présent
if (!isset($_GET['id'])) {
    die("❌ ID de réservation manquant.");
}

$id = (int)$_GET['id'];

// Récupère la réservation
$stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ?");
$stmt->execute([$id]);
$resa = $stmt->fetch();

if (!$resa) {
    die("❌ Réservation introuvable.");
}

// Si déjà remboursée ou annulée
if ($resa['statut'] === 'remboursee') {
    die("ℹ️ Cette réservation a déjà été remboursée.");
}

// Remboursement uniquement pour Stripe
if ($resa['paiement'] !== 'stripe') {
    die("❌ Cette réservation n'a pas été payée en ligne.");
}

// Lance le remboursement via Stripe
try {
    $refund = \Stripe\Refund::create([
        'payment_intent' => $resa['payment_intent']
    ]);

    // Met à jour la réservation comme remboursée
    $pdo->prepare("UPDATE reservations SET statut = 'remboursee' WHERE id = ?")->execute([$id]);

    // Notification
    echo "✅ Remboursement effectué avec succès.<br>";
    echo "<a href='dashboard.php'>⬅️ Retour au dashboard</a>";

} catch (Exception $e) {
    die("❌ Erreur Stripe : " . $e->getMessage());
}
