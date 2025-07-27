<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    die("ID manquant.");
}

$id = (int)$_GET['id'];
$pdo->prepare("UPDATE reservations SET statut = 'annulee' WHERE id = ?")->execute([$id]);

header('Location: reservations.php');
exit;
