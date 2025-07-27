<?php
session_start();
require 'includes/db.php';
require_once 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

\Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

if (!isset($_SESSION['pending_checkout'])) {
    header('Location: reservation.php');
    exit;
}

$data = $_SESSION['pending_checkout'];

$services = $data['services'] ?? [];
$email = $data['email'] ?? '';
$paiement = $data['paiement'] ?? 'stripe';
$total = $data['total'] ?? 0;
$acompte = $data['acompte'] ?? $total;

if (empty($services)) {
    die('Aucune prestation sélectionnée.');
}

$description = "Réservation : " . implode(', ', $services);

try {
    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => 'Coiffynoire - ' . ($paiement === 'sur_place' ? 'Acompte 10%' : 'Paiement intégral'),
                    'description' => $description,
                ],
                'unit_amount' => intval($acompte * 100),
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/cancel.php',
        'customer_email' => $email,
    ]);

    // Enregistre l'ID de la session Stripe pour récupérer le payment_intent plus tard
    $_SESSION['stripe_session_id'] = $checkout_session->id;

    header('Location: ' . $checkout_session->url);
    exit;

} catch (Exception $e) {
    echo 'Erreur Stripe : ' . $e->getMessage();
}



