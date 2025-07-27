<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

require '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $filename = $_POST['image'] ?? '';

    if ($id && $filename) {
        $pdo->prepare("DELETE FROM galerie WHERE id = ?")->execute([$id]);
        if (file_exists("../uploads/$filename")) {
            unlink("../uploads/$filename");
        }
    }
}

header('Location: upload_galerie.php');
exit;
