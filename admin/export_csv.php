<?php
session_start();
require_once '../includes/db.php';

$filename = 'reservations_' . date('Y-m-d_H-i') . '.csv';

header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=$filename");

$output = fopen("php://output", "w");

// En-têtes CSV
fputcsv($output, ['ID', 'Nom', 'Email', 'Téléphone', 'Service', 'Date', 'Heure', 'Coiffeur', 'Total (€)', 'Acompte (€)', 'Paiement', 'Créé le']);

// Récupération des réservations
$stmt = $pdo->query("SELECT * FROM reservations ORDER BY created_at DESC");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['id'],
        $row['name'],
        $row['email'],
        $row['phone'],
        $row['service'],
        $row['date'],
        $row['time'],
        $row['avec'],
        number_format($row['total'], 2, '.', ''),
        $row['acompte'] !== null ? number_format($row['acompte'], 2, '.', '') : '',
        $row['paiement'],
        $row['created_at']
    ]);
}

fclose($output);
exit;
