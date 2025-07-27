<?php
require 'includes/db.php';

try {
    $pdo->exec("DROP TABLE IF EXISTS reservations");
    $pdo->exec("DROP TABLE IF EXISTS messages");
    $pdo->exec("DROP TABLE IF EXISTS prestations");
    $pdo->exec("DROP TABLE IF EXISTS galerie");
    $pdo->exec("DROP TABLE IF EXISTS horaires");
    $pdo->exec("DROP TABLE IF EXISTS users");
    $pdo->exec("DROP TABLE IF EXISTS admins");
    $pdo->exec("DROP TABLE IF EXISTS effectifs");

    $pdo->exec("CREATE TABLE reservations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT NOT NULL,
        services TEXT NOT NULL,
        date TEXT NOT NULL,
        time TEXT NOT NULL,
        avec TEXT,
        total DECIMAL(10,2) NOT NULL,
        acompte DECIMAL(10,2),
        paiement TEXT NOT NULL,
        payment_intent TEXT,
        statut TEXT DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE prestations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nom TEXT NOT NULL,
        prix DECIMAL(10,2) NOT NULL,
        duree TEXT DEFAULT '1h',
        description TEXT DEFAULT ''
    )");

    $pdo->exec("CREATE TABLE admins (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        email TEXT NOT NULL,
        password TEXT NOT NULL
    )");

    $pdo->exec("CREATE TABLE galerie (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        titre TEXT NOT NULL,
        description TEXT,
        image TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE messages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        message TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE horaires (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        jour TEXT NOT NULL,
        debut TEXT NOT NULL,
        fin TEXT NOT NULL
    )");

    $jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    foreach ($jours as $jour) {
        $stmt = $pdo->prepare("INSERT INTO horaires (jour, debut, fin) VALUES (?, ?, ?)");
        $stmt->execute([$jour, '09:00', '18:00']);
    }

    $stmt = $pdo->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute(['admin', 'admin@coiffynoiree.fr', hash('sha256', 'admin123')]);
    echo "✅ Admin par défaut créé : admin / admin123<br>";

    $services = [
        ['Coupe', 25.00, '30min', 'Coupe de cheveux classique'],
        ['Brushing', 20.00, '20min', 'Brushing et mise en forme'],
        ['Coloration', 50.00, '1h30', 'Coloration complète des cheveux'],
        ['Mèches', 60.00, '2h', 'Mèches et reflets'],
        ['Permanente', 45.00, '2h30', 'Permanente pour cheveux bouclés']
    ];
    foreach ($services as $service) {
        $stmt = $pdo->prepare("INSERT INTO prestations (nom, prix, duree, description) VALUES (?, ?, ?, ?)");
        $stmt->execute($service);
    }

    $pdo->exec("CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE effectifs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        jour TEXT NOT NULL UNIQUE,
        nombre INTEGER DEFAULT 1
    )");

    $jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
    foreach ($jours as $jour) {
        $stmt = $pdo->prepare("INSERT INTO effectifs (jour, nombre) VALUES (?, ?)");
        $stmt->execute([$jour, 1]);
    }

    echo "✅ Base de données initialisée avec succès.";

} catch (PDOException $e) {
    die("❌ Erreur d'initialisation : " . $e->getMessage());
}








  


