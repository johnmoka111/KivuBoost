<?php
/**
 * ============================================================
 * KivuBoost — Configuration Centrale SMTP & Automatisation Mail
 * ============================================================
 * 
 * Ce fichier configure l'infrastructure d'envoi d'e-mails sécurisée 
 * via PHPMailer et Google SMTP avec un mot de passe d'application.
 */

// Utilisation des classes officielles de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- PARAMÈTRES DU SERVEUR SMTP (GMAIL SÉCURISÉ) ---
define('SMTP_HOST',       'smtp.gmail.com');
define('SMTP_PORT',       465);
define('SMTP_SECURE',     'ssl'); // SSL implicite
define('SMTP_AUTH',       true);
define('SMTP_USERNAME',   'johnmoka2024@gmail.com');
define('SMTP_PASSWORD',   'jmovybhisrziopny'); // Mot de passe d'application Google sécurisé
define('SMTP_FROM_EMAIL', 'johnmoka2024@gmail.com');
define('SMTP_FROM_NAME',  'KivuBoost');

/**
 * Fonction globale d'envoi d'emails professionnels thémés KivuBoost.
 * 
 * @param string $toEmail      Adresse email du destinataire
 * @param string $subject      Sujet du message
 * @param string $templateName Nom du gérabrit d'email (dans app/Views/emails/)
 * @param array  $dataArray    Données dynamiques à injecter dans le template
 * @return bool                True si l'envoi a réussi, False sinon
 */
function sendKivuBoostMail(string $toEmail, string $subject, string $templateName, array $dataArray): bool {
    // 1. Chargement explicite sans composer des classes PHPMailer
    require_once ROOT_PATH . '/app/Libraries/PHPMailer/Exception.php';
    require_once ROOT_PATH . '/app/Libraries/PHPMailer/PHPMailer.php';
    require_once ROOT_PATH . '/app/Libraries/PHPMailer/SMTP.php';

    $mail = new PHPMailer(true);
    $status = 'failed';
    $errorMessage = null;

    try {
        // 2. Configuration stricte du serveur SMTP
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = SMTP_AUTH;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;

        // 3. Option XAMPP Local : Contournement SSL si certificat cURL manquant en dev
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            ]
        ];

        // 4. En-têtes Universels & Encodage
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($toEmail);
        $mail->Subject = $subject;

        // 5. Rendu dynamique du template HTML thémé Premium Dark
        $templatePath = VIEW_PATH . '/emails/' . $templateName . '.php';
        if (!file_exists($templatePath)) {
            throw new Exception("Le gabarit d'e-mail '{$templateName}' est introuvable à l'adresse : {$templatePath}");
        }

        // Extraction des variables dynamiques pour les rendre disponibles dans le template
        extract($dataArray);
        
        // Démarrage de la temporisation de sortie pour capturer le code HTML
        ob_start();
        include $templatePath;
        $bodyContent = ob_get_clean();

        $mail->Body = $bodyContent;

        // Message alternatif pour les clients mail obsolètes
        $mail->AltBody = strip_tags(str_replace(['<br>', '<p>', '</div>'], ["\n", "\n\n", "\n"], $bodyContent));

        // 6. Envoi de l'e-mail
        $mail->send();
        $status = 'sent';
        $success = true;

    } catch (Exception $e) {
        $errorMessage = $mail->ErrorInfo ?: $e->getMessage();
        $success = false;

        // Enregistrement de l'erreur dans un journal de log local sécurisé pour le débogage
        $logDir = ROOT_PATH . '/app/writable/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . '/mailer.log';
        $logEntry = "[" . date('Y-m-d H:i:s') . "] ERREUR MAILER vers [{$toEmail}] avec le sujet [{$subject}]. Message : {$errorMessage}\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }

    // 7. Enregistrement automatique de la transaction dans la base de données
    try {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO email_logs (recipient, subject, template, status, error_message) 
            VALUES (:recipient, :subject, :template, :status, :error_message)
        ");
        $stmt->execute([
            ':recipient'     => $toEmail,
            ':subject'       => $subject,
            ':template'      => $templateName,
            ':status'        => $status,
            ':error_message' => $errorMessage
        ]);
    } catch (\PDOException $pdoEx) {
        // En cas d'erreur de base de données, on sauvegarde au moins le log d'erreur local
        $logDir = ROOT_PATH . '/app/writable/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . '/mailer.log';
        $logEntry = "[" . date('Y-m-d H:i:s') . "] ERREUR DB LOGGING : " . $pdoEx->getMessage() . "\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }

    return $success;
}
