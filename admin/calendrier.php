<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
require_once '../includes/db.php';


$services = $pdo->query("SELECT * FROM prestations ORDER BY nom")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Calendrier – Admin </title>

  <!-- FullCalendar -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.8/locales/fr.global.min.js"></script>

  <!-- Polices -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap" rel="stylesheet">

  <style>
    * { box-sizing: border-box; }
    body {
      font-family: 'Open Sans', sans-serif;
      margin: 0;
      background: #fff;
      color: #111;
    }
    header {
      background: #000;
      color: #fff;
      padding: 1.2rem 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }
    header h1 {
      font-family: 'Playfair Display', serif;
      font-size: 1.6rem;
      color: #e6c200;
      margin: .5rem 0;
    }
    header a {
      margin-left: 1rem;
      color: #fff;
      text-decoration: none;
      font-weight: bold;
    }
    nav {
      display: flex;
      flex-wrap: wrap;
      gap: .7rem;
      background: #111;
      padding: 1rem;
      justify-content: center;
    }
    nav a {
      color: #fff;
      text-decoration: none;
      padding: .6rem 1rem;
      border-radius: 8px;
      background: #222;
      transition: .3s ease;
      font-size: .95rem;
    }
    nav a.active,
    nav a:hover {
      background: #e6c200;
      color: #000;
    }
    #calendar {
      max-width: 1000px;
      margin: 2rem auto;
      background: #fff;
      padding: 1rem;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,.1);
    }
    .modal {
      display: none;
      position: fixed;
      inset: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(0,0,0,.5);
      backdrop-filter: blur(2px);
      justify-content: center;
      align-items: center;
      padding: 1rem;
      z-index: 1000;
    }
    .modal-content {
      background: #fff;
      padding: 2rem 1.5rem;
      border-radius: 12px;
      max-width: 420px;
      width: 100%;
      box-shadow: 0 8px 20px rgba(0,0,0,.2);
      display: flex;
      flex-direction: column;
    }
    .modal-content h3 {
      margin: 0 0 1rem;
      font-family: 'Playfair Display', serif;
      font-size: 1.3rem;
      text-align: center;
    }
    input, select {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
    }
    .modal-buttons {
      display: flex;
      gap: 1rem;
      margin-top: 1rem;
    }
    .modal-buttons button {
      flex: 1;
      padding: 12px;
      border: none;
      border-radius: 8px;
      font-size: 14px;
      font-weight: bold;
      cursor: pointer;
      transition: .3s ease;
    }
    .modal-buttons button[type="submit"] {
      background: #000;
      color: #fff;
    }
    .modal-buttons button[type="submit"]:hover {
      background: #e6c200;
      color: #000;
    }
    .modal-buttons button[type="button"] {
      background: #aaa;
      color: #fff;
    }
    .modal-buttons button[type="button"]:hover {
      background: #555;
    }
    @media (max-width: 768px) {
      header { flex-direction: column; align-items: flex-start; }
      nav    { flex-direction: column; align-items: center; }
      #calendar { margin: 1rem; padding: 1rem .5rem; }
      .modal-content { padding: 1rem; }
      input, select { font-size: 1rem; }
    }
  </style>
</head>
<body>

  <header>
    <h1>Calendrier</h1>
    <div><a href="logout.php">Déconnexion</a></div>
  </header>

  <nav>
    <a href="dashboard.php">🏠 Dashboard</a>
  </nav>

  <div id="calendar"></div>

  <!-- MODAL -->
  <div class="modal" id="modal">
    <div class="modal-content">
      <h3>Ajouter un rendez-vous</h3>
      <form id="rdv-form">
        <input type="text"  name="name"  placeholder="Nom du client" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="tel"   name="phone" placeholder="Téléphone" required>

        <select name="service" required>
          <option value="">Choisissez un service</option>
          <?php foreach ($services as $service): ?>
            <option value="<?= htmlspecialchars($service['nom']) ?>">
              <?= htmlspecialchars($service['nom']) ?> – <?= number_format($service['prix'], 2) ?> €
            </option>
          <?php endforeach; ?>
        </select>

        <input type="date" name="date" required>
        <input type="time" name="time" required>

        <div class="modal-buttons">
          <button type="submit">Ajouter</button>
          <button type="button" onclick="closeModal()">Annuler</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    const modal = document.getElementById('modal');
    function closeModal() { modal.style.display = 'none'; }

    document.addEventListener('DOMContentLoaded', () => {
      const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',
        locale: 'fr',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: 'reservations_api.php',
        dateClick: info => {
          document.querySelector('input[name="date"]').value = info.dateStr;
          document.querySelector('input[name="time"]').value = '10:00';
          modal.style.display = 'flex';
        },
        eventClick: info => {
          const details = info.event.extendedProps.description || '';
          if (confirm(`🧑 ${info.event.title}\n📧 ${details}\n\n🗑️ Supprimer ce rendez-vous ?`)) {
            fetch(`delete_reservation.php?id=${info.event.id}`)
              .then(r => r.text())
              .then(res => {
                if (res.trim() === 'ok') {
                  calendar.refetchEvents();
                  alert('❌ Réservation supprimée.');
                } else {
                  alert('Erreur lors de la suppression.');
                }
              })
              .catch(() => alert('Erreur de connexion au serveur.'));
          }
        }
      });

      calendar.render();

      document.getElementById('rdv-form').addEventListener('submit', e => {
        e.preventDefault();
        const data = new FormData(e.target);

        fetch('add_reservation.php', { method: 'POST', body: data })
          .then(r => r.text())
          .then(res => {
            if (res.trim() === 'ok') {
              closeModal();
              calendar.refetchEvents();
              alert('✅ Rendez-vous ajouté avec succès !');
              e.target.reset();
            } else {
              alert('❌ Erreur lors de l\'ajout du rendez-vous');
            }
          })
          .catch(() => alert('❌ Erreur de connexion'));
      });
    });
  </script>
</body>
</html>









