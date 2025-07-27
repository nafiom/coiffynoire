<?php
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $service  = trim($_POST['service'] ?? '');
    $date     = trim($_POST['date'] ?? '');
    $time     = trim($_POST['time'] ?? '');

    if (!$name || !$email || !$phone || !$service || !$date || !$time) {
        echo 'missing';
        exit;
    }

    try {
        // Récupérer le prix du service
        $stmt = $pdo->prepare("SELECT prix FROM prestations WHERE nom = ?");
        $stmt->execute([$service]);
        $prix = $stmt->fetchColumn();

        if ($prix === false) {
            echo 'service_not_found';
            exit;
        }

        $acompte = round($prix * 0.10, 2);

        
        $stmt = $pdo->prepare("INSERT INTO reservations 
            (name, email, phone, services, date, time, total, acompte, paiement, statut)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'admin', 'active')");

        $success = $stmt->execute([
            $name, $email, $phone, $service, $date, $time,
            $prix, $acompte
        ]);

        echo $success ? 'ok' : 'error';
    } catch (Exception $e) {
        echo 'error: ' . $e->getMessage();
    }
} else {
    echo 'error';
}




