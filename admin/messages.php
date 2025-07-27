<?php
session_start();
require '../includes/db.php';

$messages = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📥 Messages - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Open Sans', sans-serif;
            background: #f8f8f8;
            color: #333;
            padding: 1.5rem;
            margin: 0;
        }
        h1 {
            font-family: 'Playfair Display', serif;
            color: #000;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2rem;
        }
        .message {
            background: #fff;
            border-left: 5px solid #e6c200;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .message:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        .message h3 {
            margin: 0 0 0.5rem;
            color: #000;
            font-size: 1.1rem;
        }
        .message a {
            color: #000;
            text-decoration: underline;
        }
        .message p {
            margin: 0.5rem 0;
            line-height: 1.6;
            font-size: 1rem;
        }
        .date {
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.8rem;
        }
        .btn-back {
            display: inline-block;
            background: #000;
            color: #fff;
            padding: 0.7rem 1.5rem;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            margin-bottom: 2rem;
            transition: 0.3s ease;
        }
        .btn-back:hover {
            background: #e6c200;
            color: #000;
        }

        @media (max-width: 600px) {
            body {
                padding: 1rem;
            }

            .message {
                padding: 1rem;
            }

            h1 {
                font-size: 1.5rem;
            }

            .btn-back {
                display: block;
                text-align: center;
                width: 100%;
                padding: 1rem;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>

    <a href="dashboard.php" class="btn-back">← Retour au tableau de bord</a>

    <h1>📥 Messages reçus</h1>

    <?php if (count($messages) === 0): ?>
        <p style="text-align:center;">Aucun message pour le moment.</p>
    <?php else: ?>
        <?php foreach ($messages as $msg): ?>
            <div class="message">
                <h3><?= htmlspecialchars($msg['name']) ?> (<a href="mailto:<?= htmlspecialchars($msg['email']) ?>"><?= htmlspecialchars($msg['email']) ?></a>)</h3>
                <p><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                <div class="date">🕓 Reçu le <?= date('d/m/Y à H:i', strtotime($msg['created_at'])) ?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>

