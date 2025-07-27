<?php
session_start();
if (!isset($_SESSION['admin'])) {
    http_response_code(401);
    exit;
}

require '../includes/db.php';

try {
    $stmt = $pdo->query("SELECT * FROM reservations ORDER BY date, time");
    $events = [];

    foreach ($stmt as $row) {
        $events[] = [
            'id' => $row['id'],
            'title' => $row['service'] . ' – ' . $row['name'],
            'start' => $row['date'] . 'T' . $row['time'],
            'description' => $row['phone'] . ' – ' . $row['email']
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($events);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur base de données']);
}
?>


