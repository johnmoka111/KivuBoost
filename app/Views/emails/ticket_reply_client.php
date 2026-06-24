<?php
/**
 * Template : Notification Client — Réponse Admin sur Ticket
 * Variables attendues :
 *  - $username   : Pseudo du client
 *  - $ticketId   : ID du ticket
 *  - $subject    : Sujet du ticket
 *  - $reply      : Message de réponse de l'admin
 *  - $ticketUrl  : URL vers le ticket côté client
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réponse à votre ticket #<?= $ticketId ?> — KivuBoost</title>
</head>
<body style="margin: 0; padding: 0; background-color: #000000; font-family: 'Inter', -apple-system, BlinkMacSystemFont, Arial, sans-serif;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #000000; padding: 40px 10px;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #0d1117; border: 1px solid #1a2332; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">

                    <!-- Hero Header -->
                    <tr>
                        <td align="center" style="padding: 45px 40px 30px 40px; background: linear-gradient(145deg, #000d14, #001020);">
                            <div style="font-size: 56px; margin-bottom: 16px; line-height: 1;">💬</div>
                            <h1 style="margin: 0 0 6px 0; color: #38bdf8; font-size: 24px; font-weight: 900; letter-spacing: -0.5px;">L'équipe vous a répondu !</h1>
                            <p style="margin: 0; color: #6b7280; font-size: 12px;">Votre ticket de support a reçu une nouvelle réponse.</p>
                        </td>
                    </tr>

                    <!-- Séparateur bleu -->
                    <tr><td style="height: 3px; background: linear-gradient(90deg, transparent, #0EA5E9, transparent);"></td></tr>

                    <!-- Badge Ticket ID -->
                    <tr>
                        <td align="center" style="padding: 30px 40px 20px 40px;">
                            <span style="background: rgba(14,165,233,0.15); border: 1px solid rgba(14,165,233,0.3); color: #38bdf8; font-size: 13px; font-weight: 700; padding: 8px 20px; border-radius: 30px; letter-spacing: 1px; font-family: monospace;">
                                TICKET #<?= (int)$ticketId ?> · <?= htmlspecialchars($subject) ?>
                            </span>
                        </td>
                    </tr>

                    <!-- Message de bienvenue -->
                    <tr>
                        <td style="padding: 0 40px 20px 40px;">
                            <p style="margin: 0; font-size: 14px; color: #cbd5e1; line-height: 1.7; text-align: center;">
                                Bonjour <strong style="color: #f8fafc;"><?= htmlspecialchars($username) ?></strong>,<br>
                                notre équipe de support a apporté une réponse à votre demande.
                            </p>
                        </td>
                    </tr>

                    <!-- Réponse de l'admin -->
                    <tr>
                        <td style="padding: 0 40px 25px 40px;">
                            <p style="margin: 0 0 10px 0; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: #6b7280;">Réponse de l'Équipe KivuBoost</p>
                            <div style="background: linear-gradient(135deg, rgba(14,165,233,0.05), rgba(56,189,248,0.03)); border: 1px solid rgba(14,165,233,0.2); border-radius: 12px; padding: 20px 24px;">
                                <div style="display: flex; align-items: center; margin-bottom: 12px;">
                                    <span style="font-size: 18px; margin-right: 8px;">🛡️</span>
                                    <span style="font-size: 11px; font-weight: 700; color: #38bdf8; text-transform: uppercase; letter-spacing: 1px;">Support KivuBoost</span>
                                </div>
                                <p style="margin: 0; font-size: 13px; color: #e2e8f0; line-height: 1.8; white-space: pre-wrap;"><?= htmlspecialchars($reply) ?></p>
                            </div>
                        </td>
                    </tr>

                    <!-- Bouton CTA -->
                    <tr>
                        <td align="center" style="padding: 0 40px 30px 40px;">
                            <a href="<?= htmlspecialchars($ticketUrl) ?>" style="background: linear-gradient(135deg, #0EA5E9, #0284C7); color: #ffffff; font-weight: 800; font-size: 14px; text-decoration: none; border-radius: 12px; padding: 16px 40px; display: inline-block; box-shadow: 0 4px 20px rgba(14, 165, 233, 0.4); text-transform: uppercase; letter-spacing: 0.5px;">
                                📨 Voir le Ticket Complet
                            </a>
                        </td>
                    </tr>

                    <!-- Info complémentaire -->
                    <tr>
                        <td style="padding: 0 40px 25px 40px;">
                            <p style="margin: 0; font-size: 12px; color: #4b5563; text-align: center; line-height: 1.6;">
                                Si vous avez d'autres questions, vous pouvez répondre directement à ce ticket<br>depuis votre espace client.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 25px 40px; background-color: #050811; border-top: 1px solid #1a2332; color: #71717a; font-size: 11px; line-height: 1.6;">
                            <p style="margin: 0 0 6px 0; color: #e2e8f0; font-weight: 700;">KivuBoost · Support Client</p>
                            <p style="margin: 0;">© <?= date('Y') ?> KivuBoost. Tous droits réservés.<br>TAL Communities · Bukavu, Sud-Kivu, RDC.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
