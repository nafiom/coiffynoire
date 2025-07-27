<?php
session_start();
require_once '../includes/db.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
        // Suppression depuis formulaire (POST)
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("DELETE FROM reservations WHERE id = ?");
        $stmt->execute([$id]);

        header('Location: reservations.php');
        exit;

    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
        // Suppression AJAX depuis le calendrier (GET)
        $id = intval($_GET['id']);
        $stmt = $pdo->prepare("DELETE FROM reservations WHERE id = ?");
        $stmt->execute([$id]);
        echo 'ok';
        exit;

    } else {
        echo 'error';
        exit;
    }
} catch (Exception $e) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        echo 'error';
    } else {
        die("❌ Erreur lors de la suppression : " . $e->getMessage());
    }
}

