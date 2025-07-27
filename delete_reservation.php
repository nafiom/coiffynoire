<?php
// 🔁 delete_reservation.php — avec remboursement Stripe si nécessaire
require_once '../includes/db.php';
require_once '../includes/stripe_init.php'; // Ce fichier initialise Stripe avec les clés secrètes

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ?");
    $stmt->execute([$id]);
    $reservation = $stmt->fetch();

    if (!$reservation) {
        header("Location: reservations.php?error=notfound");
        exit;
    }

    // Si Stripe, tenter remboursement
    if ($reservation['paiement'] === 'stripe' && !empty($reservation['payment_intent'])) {
        try {
            $refund = \Stripe\Refund::create([
                'payment_intent' => $reservation['payment_intent']
            ]);

            $stmt = $pdo->prepare("UPDATE reservations SET statut = 'remboursee' WHERE id = ?");
            $stmt->execute([$id]);
        } catch (Exception $e) {
            header("Location: reservations.php?error=stripe_refund_failed");
            exit;
        }
    } else {
        // Paiement sur place → statut = annulee
        $stmt = $pdo->prepare("UPDATE reservations SET statut = 'annulee' WHERE id = ?");
        $stmt->execute([$id]);
    }

    header("Location: reservations.php?success=1");
    exit;
} else {
    header("Location: reservations.php?error=invalid");
    exit;
}
