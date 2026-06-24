<?php
/**
 * Template : Notification Client — Commande Terminée ✅
 * Variables attendues :
 *  - $username    : Pseudo du client
 *  - $orderId     : ID formaté de la commande (ex: 00042)
 *  - $serviceName : Nom du service commandé
 *  - $quantity    : Quantité livrée
 *  - $cost        : Coût de la commande
 *  - $dashboardUrl: URL vers l'historique des commandes
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commande #<?= $orderId ?> terminée — KivuBoost</title>
</head>
<body style="margin: 0; padding: 0; background-color: #000000; font-family: 'Inter', -apple-system, BlinkMacSystemFont, Arial, sans-serif; -webkit-font-smoothing: antialiased;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #000000; padding: 40px 10px;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #0d1117; border: 1px solid #1a2332; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">

                    <!-- Hero Header -->
                    <tr>
                        <td align="center" style="padding: 45px 40px 30px 40px; background: linear-gradient(145deg, #000d07, #00150a);">
                            <div style="font-size: 64px; margin-bottom: 16px; line-height: 1;">🎉</div>
                            <h1 style="margin: 0 0 6px 0; color: #10b981; font-size: 26px; font-weight: 900; letter-spacing: -0.5px;">Commande Terminée !</h1>
                            <p style="margin: 0; color: #6b7280; font-size: 13px;">Votre service SMM a été livré avec succès.</p>
                        </td>
                    </tr>

                    <!-- Séparateur vert -->
                    <tr><td style="height: 3px; background: linear-gradient(90deg, transparent, #10B981, transparent);"></td></tr>

                    <!-- Badge commande -->
                    <tr>
                        <td align="center" style="padding: 30px 40px 20px 40px;">
                            <span style="background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.3); color: #10b981; font-size: 13px; font-weight: 700; padding: 8px 20px; border-radius: 30px; letter-spacing: 1px; font-family: monospace;">
                                COMMANDE #<?= htmlspecialchars($orderId) ?>
                            </span>
                        </td>
                    </tr>

                    <!-- Message personnalisé -->
                    <tr>
                        <td style="padding: 0 40px 20px 40px;">
                            <p style="margin: 0; font-size: 14px; color: #cbd5e1; line-height: 1.7; text-align: center;">
                                Bonjour <strong style="color: #f8fafc;"><?= htmlspecialchars($username) ?></strong>, 🙌<br>
                                votre commande a été entièrement traitée et livrée par nos fournisseurs.
                            </p>
                        </td>
                    </tr>

                    <!-- Détails de la commande -->
                    <tr>
                        <td style="padding: 0 40px 25px 40px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background: #0a0f1a; border: 1px solid #1a2332; border-radius: 12px;">
                                <tr><td style="padding: 20px;">
                                    <p style="margin: 0 0 14px 0; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: #6b7280;">Récapitulatif de la Livraison</p>

                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td style="padding: 8px 0; border-bottom: 1px solid #1a2332;">
                                                <span style="font-size: 11px; color: #6b7280; font-weight: 600;">Service</span>
                                                <span style="float: right; font-size: 12px; color: #e2e8f0; font-weight: 700;"><?= htmlspecialchars($serviceName) ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px 0; border-bottom: 1px solid #1a2332;">
                                                <span style="font-size: 11px; color: #6b7280; font-weight: 600;">Quantité livrée</span>
                                                <span style="float: right; font-size: 12px; color: #10b981; font-weight: 700;"><?= htmlspecialchars($quantity) ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px 0; border-bottom: 1px solid #1a2332;">
                                                <span style="font-size: 11px; color: #6b7280; font-weight: 600;">Coût débité</span>
                                                <span style="float: right; font-size: 12px; color: #e2e8f0; font-weight: 700;">$<?= htmlspecialchars($cost) ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px 0;">
                                                <span style="font-size: 11px; color: #6b7280; font-weight: 600;">Statut</span>
                                                <span style="float: right; font-size: 11px; color: #10b981; font-weight: 700; background: rgba(16,185,129,0.1); padding: 2px 10px; border-radius: 4px;">✅ Terminée</span>
                                            </td>
                                        </tr>
                                    </table>
                                </td></tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Bouton CTA -->
                    <tr>
                        <td align="center" style="padding: 0 40px 30px 40px;">
                            <a href="<?= htmlspecialchars($dashboardUrl) ?>" style="background: linear-gradient(135deg, #10B981, #059669); color: #000000; font-weight: 800; font-size: 14px; text-decoration: none; border-radius: 12px; padding: 16px 40px; display: inline-block; box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4); text-transform: uppercase; letter-spacing: 0.5px;">
                                📋 Voir mon Historique
                            </a>
                        </td>
                    </tr>

                    <!-- Message fidélité -->
                    <tr>
                        <td style="padding: 0 40px 25px 40px;">
                            <div style="background: rgba(16,185,129,0.05); border: 1px solid rgba(16,185,129,0.15); border-radius: 10px; padding: 14px 18px; text-align: center;">
                                <p style="margin: 0; font-size: 12px; color: #6b7280; line-height: 1.6;">
                                    🌟 Des <strong style="color: #10b981;">points de fidélité</strong> ont été ajoutés à votre compte.<br>
                                    Cumulez-les pour obtenir des crédits gratuits sur KivuBoost.
                                </p>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 25px 40px; background-color: #050811; border-top: 1px solid #1a2332; color: #71717a; font-size: 11px; line-height: 1.6;">
                            <p style="margin: 0 0 6px 0; color: #e2e8f0; font-weight: 700;">KivuBoost · Services SMM</p>
                            <p style="margin: 0;">© <?= date('Y') ?> KivuBoost. Tous droits réservés.<br>TAL Communities · Bukavu, Sud-Kivu, RDC.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
