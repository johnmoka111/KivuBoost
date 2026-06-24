<?php
/**
 * Template : Alerte Admin — Nouveau Ticket de Support
 * Variables attendues :
 *  - $username   : Pseudo du client
 *  - $ticketId   : ID du ticket
 *  - $subject    : Sujet du ticket
 *  - $message    : Premier message du ticket
 *  - $ticketUrl  : URL vers le ticket dans l'espace admin
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau Ticket #<?= $ticketId ?> — KivuBoost Admin</title>
</head>
<body style="margin: 0; padding: 0; background-color: #000000; font-family: 'Inter', -apple-system, BlinkMacSystemFont, Arial, sans-serif;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #000000; padding: 40px 10px;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #0d1117; border: 1px solid #1a2332; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">

                    <!-- Hero Header -->
                    <tr>
                        <td align="center" style="padding: 45px 40px 30px 40px; background: linear-gradient(145deg, #0d0014, #130020);">
                            <div style="font-size: 56px; margin-bottom: 16px; line-height: 1;">🎫</div>
                            <h1 style="margin: 0 0 6px 0; color: #a78bfa; font-size: 24px; font-weight: 900; letter-spacing: -0.5px;">Nouveau Ticket de Support</h1>
                            <p style="margin: 0; color: #6b7280; font-size: 12px;">Un client a ouvert un ticket qui nécessite votre attention.</p>
                        </td>
                    </tr>

                    <!-- Séparateur violet -->
                    <tr><td style="height: 3px; background: linear-gradient(90deg, transparent, #7C3AED, transparent);"></td></tr>

                    <!-- Badge Ticket ID -->
                    <tr>
                        <td align="center" style="padding: 30px 40px 20px 40px;">
                            <span style="background: rgba(124,58,237,0.15); border: 1px solid rgba(124,58,237,0.3); color: #a78bfa; font-size: 13px; font-weight: 700; padding: 8px 20px; border-radius: 30px; letter-spacing: 1px; font-family: monospace;">
                                TICKET #<?= (int)$ticketId ?>
                            </span>
                        </td>
                    </tr>

                    <!-- Détails du Ticket -->
                    <tr>
                        <td style="padding: 0 40px 25px 40px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background: #0a0f1a; border: 1px solid #1a2332; border-radius: 12px;">
                                <tr><td style="padding: 20px;">
                                    <p style="margin: 0 0 14px 0; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: #6b7280;">Informations du Ticket</p>

                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td style="padding: 8px 0; border-bottom: 1px solid #1a2332;">
                                                <span style="font-size: 11px; color: #6b7280; font-weight: 600;">Client</span>
                                                <span style="float: right; font-size: 12px; color: #e2e8f0; font-weight: 700;"><?= htmlspecialchars($username) ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px 0; border-bottom: 1px solid #1a2332;">
                                                <span style="font-size: 11px; color: #6b7280; font-weight: 600;">Sujet</span>
                                                <span style="float: right; font-size: 12px; color: #a78bfa; font-weight: 700;"><?= htmlspecialchars($subject) ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px 0;">
                                                <span style="font-size: 11px; color: #6b7280; font-weight: 600;">Date</span>
                                                <span style="float: right; font-size: 11px; color: #e2e8f0; font-weight: 600;"><?= date('d/m/Y H:i') ?></span>
                                            </td>
                                        </tr>
                                    </table>
                                </td></tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Message du client -->
                    <tr>
                        <td style="padding: 0 40px 25px 40px;">
                            <p style="margin: 0 0 10px 0; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: #6b7280;">Message du Client</p>
                            <div style="background: #050811; border-left: 3px solid #7C3AED; border-radius: 0 8px 8px 0; padding: 16px 20px;">
                                <p style="margin: 0; font-size: 13px; color: #cbd5e1; line-height: 1.7; white-space: pre-wrap;"><?= htmlspecialchars($message) ?></p>
                            </div>
                        </td>
                    </tr>

                    <!-- Bouton CTA -->
                    <tr>
                        <td align="center" style="padding: 0 40px 35px 40px;">
                            <a href="<?= htmlspecialchars($ticketUrl) ?>" style="background: linear-gradient(135deg, #7C3AED, #5B21B6); color: #ffffff; font-weight: 800; font-size: 14px; text-decoration: none; border-radius: 12px; padding: 16px 40px; display: inline-block; box-shadow: 0 4px 20px rgba(124, 58, 237, 0.4); text-transform: uppercase; letter-spacing: 0.5px;">
                                🔍 Voir & Répondre au Ticket
                            </a>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 25px 40px; background-color: #050811; border-top: 1px solid #1a2332; color: #71717a; font-size: 11px; line-height: 1.6;">
                            <p style="margin: 0 0 6px 0; color: #e2e8f0; font-weight: 700;">KivuBoost · Espace Administration</p>
                            <p style="margin: 0;">© <?= date('Y') ?> KivuBoost. Tous droits réservés.<br>Cet email est réservé à l'équipe administrative.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
