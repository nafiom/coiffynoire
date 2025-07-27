<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendMail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $_ENV['MAIL_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USERNAME'];
        $mail->Password   = $_ENV['MAIL_PASSWORD']; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $_ENV['MAIL_PORT'];

        // Encodage UTF-8
        $mail->CharSet    = 'UTF-8';
        $mail->Encoding   = 'base64';

        // Expéditeur et destinataire
        $mail->setFrom($_ENV['MAIL_USERNAME'], $_ENV['MAIL_FROM_NAME']);
        $mail->addAddress($to);

        // Contenu HTML
        $mail->isHTML(true);
        $mail->Subject = mb_encode_mimeheader($subject, 'UTF-8', 'B');
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body); // Fallback texte brut

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email non envoyé. Erreur : {$mail->ErrorInfo}");
        return false;
    }
}

function sendConfirmationEmail($email, $name, $service, $date, $time) {
    $subject = "Confirmation de votre rendez-vous - Coiffynoire";

    $body = "
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #000; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .details { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
            .footer { text-align: center; color: #666; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>✂️ Coiffynoire</h1> 
                <h2>Confirmation de rendez-vous</h2>
            </div>
            <div class='content'>
                <p>Bonjour <strong>" . htmlspecialchars($name) . "</strong>,</p>
                <p>Nous vous confirmons votre rendez-vous chez Coiffynoire :</p> 

                <div class='details'>
                    <h3>📅 Détails de votre rendez-vous</h3>
                    <p><strong>Service :</strong> " . htmlspecialchars($service) . "</p>
                    <p><strong>Date :</strong> " . htmlspecialchars($date) . "</p>
                    <p><strong>Heure :</strong> " . htmlspecialchars($time) . "</p>
                </div>

                <p>Nous vous attendons avec plaisir pour votre rendez-vous.</p>
                <p>En cas d'empêchement, merci de nous prévenir au moins 24h à l'avance.</p>

                <div class='footer'>
                    <p>Coiffynoire<br>
                    📞 01 23 45 67 89<br>
                    📍 123 Rue de la Beauté, 75001 Paris</p>
                </div>
            </div>
        </div>
    </body>
    </html>";

    return sendMail($email, $subject, $body);
}
?>
