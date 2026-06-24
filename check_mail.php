<?php
// Script de diagnostic SMTP pour KivuBoost
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('VIEW_PATH', APP_PATH . '/Views');

require_once ROOT_PATH . '/config/mailer_config.php';
require_once ROOT_PATH . '/app/Libraries/PHPMailer/Exception.php';
require_once ROOT_PATH . '/app/Libraries/PHPMailer/PHPMailer.php';
require_once ROOT_PATH . '/app/Libraries/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "<h2>Diagnostic SMTP KivuBoost</h2>";
echo "Serveur SMTP configuré : " . SMTP_HOST . "<br>";
echo "Port SMTP configuré : " . SMTP_PORT . "<br>";
echo "Sécurité SMTP configurée : " . SMTP_SECURE . "<br>";
echo "Utilisateur SMTP configuré : " . SMTP_USERNAME . "<br><br>";

$mail = new PHPMailer(true);

try {
    // Activer le débogage SMTP verbeux
    $mail->SMTPDebug = 4;
    // Capturer la sortie du débogueur
    $mail->Debugoutput = function($str, $level) {
        echo htmlspecialchars($str) . "<br>";
    };

    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = SMTP_AUTH;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port       = SMTP_PORT;

    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true
        ]
    ];

    $mail->CharSet = 'UTF-8';
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress(SMTP_USERNAME); // S'envoyer le mail à soi-même pour tester
    $mail->Subject = "KivuBoost Test Mail - Diagnostic";
    $mail->Body    = "Ceci est un message de test envoyé par le script de diagnostic SMTP KivuBoost.";

    echo "<strong>Tentative de connexion et d'envoi...</strong><br><hr>";
    $mail->send();
    echo "<hr><strong>✓ Succès ! L'e-mail a été envoyé correctement.</strong>";
} catch (Exception $e) {
    echo "<hr><strong style='color:red;'>✗ Échec de l'envoi. Erreur : " . $e->getMessage() . "</strong>";
    echo "<br>Détail de l'erreur PHPMailer : " . $mail->ErrorInfo;
}
