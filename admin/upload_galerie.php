<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
require '../includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($titre) || !isset($_FILES['image'])) {
        $error = "Titre et image obligatoires.";
    } else {
        $file = $_FILES['image'];
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $error = "Format d'image non autorisé.";
        } else {
            $newName = uniqid('img_') . '.' . $ext;
            $destination = '../uploads/' . $newName;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $stmt = $pdo->prepare("INSERT INTO galerie (titre, description, image) VALUES (?, ?, ?)");
                $stmt->execute([$titre, $description, $newName]);
                $success = "✅ Image ajoutée avec succès !";
            } else {
                $error = "❌ Erreur lors de l’upload.";
            }
        }
    }
}

$images = $pdo->query("SELECT * FROM galerie ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Galerie</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; }
    body {
      font-family: 'Open Sans', sans-serif;
      background: #f5f5f5;
      margin: 0;
      padding: 0;
    }

    .header {
      background: #000;
      color: white;
      padding: 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }

    .nav {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 10px;
      background: #111;
      padding: 1rem;
    }

    .nav a {
      background: #222;
      color: white;
      padding: 0.6rem 1rem;
      text-decoration: none;
      border-radius: 8px;
      transition: 0.3s ease;
    }

    .nav a.active,
    .nav a:hover {
      background: #e6c200;
      color: black;
    }

    h1, h2 {
      text-align: center;
      color: #000;
      margin-top: 2rem;
    }

    form {
      max-width: 600px;
      margin: 2rem auto;
      background: white;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    label {
      display: block;
      margin-top: 1rem;
      font-weight: bold;
    }

    input[type="text"], textarea, input[type="file"] {
      width: 100%;
      padding: 0.7rem;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 1rem;
    }

    .btn {
      background: #000;
      color: #fff;
      padding: 0.8rem 1.5rem;
      margin-top: 1.5rem;
      border-radius: 50px;
      border: none;
      cursor: pointer;
      font-size: 1rem;
    }

    .btn:hover {
      background: #e6c200;
      color: #000;
    }

    .preview {
      margin-top: 1rem;
      max-width: 300px;
      border-radius: 10px;
    }

    .gallery-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 1rem;
      margin: 2rem;
    }

    .gallery-item {
      background: white;
      border-radius: 10px;
      overflow: hidden;
      position: relative;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      padding: 0.5rem;
    }

    .gallery-item img {
      width: 100%;
      display: block;
      border-radius: 8px;
    }

    .delete-btn {
      position: absolute;
      top: 8px;
      right: 8px;
      background: red;
      color: white;
      border: none;
      border-radius: 50%;
      width: 28px;
      height: 28px;
      font-size: 18px;
      cursor: pointer;
    }

    .success, .error {
      max-width: 600px;
      margin: 1rem auto;
      padding: 1rem;
      text-align: center;
      border-radius: 8px;
      font-weight: bold;
    }

    .success { background: #d4edda; color: #155724; }
    .error { background: #f8d7da; color: #721c24; }

    @media (max-width: 600px) {
      .header h1 {
        font-size: 1.2rem;
      }

      form, .success, .error {
        margin: 1rem;
        padding: 1rem;
      }

      .btn {
        width: 100%;
      }

      .nav {
        flex-direction: column;
        align-items: center;
      }
    }
  </style>
</head>
<body>

  <div class="header">
    <h1>🖼️ Galerie </h1>
    <div>
      <a href="dashboard.php" style="color: white; margin-right: 20px;"> Dashboard</a>
      <a href="logout.php" style="color: white;">Déconnexion</a>
    </div>
  </div>

  <div class="nav">
    <a href="dashboard.php"> Dashboard</a>
  </div>

  <h2>Ajouter une image à la galerie</h2>

  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <label for="titre">Titre *</label>
    <input type="text" name="titre" required>

    <label for="description">Description</label>
    <textarea name="description" rows="4"></textarea>

    <label for="image">Image *</label>
    <input type="file" name="image" accept="image/*" onchange="previewImage(event)" required>
    <img id="preview" class="preview" style="display:none" alt="Aperçu">

    <button type="submit" class="btn">Ajouter à la galerie</button>
  </form>

  <h2>Galerie actuelle</h2>
  <div class="gallery-grid">
    <?php foreach ($images as $img): ?>
      <div class="gallery-item">
        <form method="POST" action="supprimer_image.php" onsubmit="return confirm('Supprimer cette image ?')">
          <input type="hidden" name="id" value="<?= $img['id'] ?>">
          <input type="hidden" name="image" value="<?= $img['image'] ?>">
          <button type="submit" class="delete-btn">×</button>
        </form>
        <img src="../uploads/<?= htmlspecialchars($img['image']) ?>" alt="<?= htmlspecialchars($img['titre']) ?>">
      </div>
    <?php endforeach; ?>
  </div>

  <script>
    function previewImage(event) {
      const input = event.target;
      const preview = document.getElementById('preview');
      if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          preview.src = e.target.result;
          preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
      }
    }
  </script>
</body>
</html>


