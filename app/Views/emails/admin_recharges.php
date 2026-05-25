<?php
/**
 * Template : Confirmation — Portefeuille Crédité
 * Variables attendues :
 *  - $username        : Pseudo de l'utilisateur
 *  - $amount          : Montant déposé (float)
 *  - $currency        : Devise (USD ou CDF)
 *  - $smsToken        : Référence de transaction (ID SMS/Mobile Money)
 *  - $newBalance      : Nouveau solde après crédit (dans la devise affichée)
 *  - $historyUrl      : URL vers l'historique des recharges
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fonds crédités — KivuBoost</title>
</head>
<body style="margin: 0; padding: 0; background-color: #000000; font-family: 'Inter', -apple-system, BlinkMacSystemFont, Arial, sans-serif; -webkit-font-smoothing: antialiased;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #000000; padding: 40px 10px;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #0d1117; border: 1px solid #1a2332; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">

                    <!-- Hero Header -->
                    <tr>
                        <td align="center" style="padding: 45px 40px 30px 40px; background: linear-gradient(145deg, #000d07, #00150a);">
                            <div style="font-size: 56px; margin-bottom: 16px; line-height: 1;">✅</div>
                            <h1 style="margin: 0 0 6px 0; color: #10b981; font-size: 26px; font-weight: 900; letter-spacing: -0.5px;">Fonds Crédités !</h1>
                            <p style="margin: 0; color: #6b7280; font-size: 12px;">Votre portefeuille KivuBoost a été rechargé avec succès.</p>
                        </td>
                    </tr>

                    <!-- Séparateur vert -->
                    <tr><td style="height: 3px; background: linear-gradient(90deg, transparent, #10B981, transparent);"></td></tr>

                    <!-- Montant Central -->
                    <tr>
                        <td align="center" style="padding: 35px 40px 20px 40px;">
                            <p style="margin: 0 0 4px 0; font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700;">Montant crédité</p>
                            <p style="margin: 0; font-size: 52px; font-weight: 900; color: #10b981; font-family: 'Courier New', monospace; letter-spacing: -2px; line-height: 1.1;">
                                <?php
                                if ($currency === 'CDF') {
                                    echo number_format((float)$amount, 0, ',', ' ') . ' FC';
                                } else {
                                    echo '$' . number_format((float)$amount, 2);
                                }
                                ?>
                            </p>
                            <p style="margin: 8px 0 0 0; font-size: 12px; color: #4b5563; font-weight: 600;"><?= $currency === 'CDF' ? 'Francs Congolais' : 'Dollar Américain (USD)' ?></p>
                        </td>
                    </tr>

                    <!-- Détails de la transaction -->
                    <tr>
                        <td style="padding: 0 40px 25px 40px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background: #0a0f1a; border: 1px solid #1a2332; border-radius: 12px;">
                                <tr><td style="padding: 20px;">
                                    <p style="margin: 0 0 14px 0; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: #6b7280;">Détails de la Transaction</p>

                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td style="padding: 7px 0; border-bottom: 1px solid #1a2332;">
                                                <span style="font-size: 11px; color: #6b7280; font-weight: 600;">Référence</span>
                                                <span style="float: right; font-size: 11px; color: #e2e8f0; font-family: monospace; font-weight: 600; word-break: break-all;"><?= htmlspecialchars($smsToken) ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 7px 0; border-bottom: 1px solid #1a2332;">
                                                <span style="font-size: 11px; color: #6b7280; font-weight: 600;">Statut</span>
                                                <span style="float: right; font-size: 11px; color: #10b981; font-weight: 700; background: rgba(16,185,129,0.1); padding: 2px 8px; border-radius: 4px;">✓ Approuvée</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 7px 0;">
                                                <span style="font-size: 11px; color: #6b7280; font-weight: 600;">Date</span>
                                                <span style="float: right; font-size: 11px; color: #e2e8f0; font-weight: 600;"><?= date('d/m/Y H:i') ?></span>
                                            </td>
                                        </tr>
                                    </table>
                                </td></tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Nouveau Solde -->
                    <tr>
                        <td style="padding: 0 40px 30px 40px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background: linear-gradient(135deg, rgba(16,185,129,0.08), rgba(5,150,105,0.05)); border: 1px solid rgba(16,185,129,0.25); border-radius: 12px;">
                                <tr>
                                    <td style="padding: 18px 20px;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td>
                                                    <p style="margin: 0 0 3px 0; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: #10b981;">Nouveau Solde Disponible</p>
                                                    <p style="margin: 0; font-size: 24px; font-weight: 900; color: #ffffff; font-family: 'Courier New', monospace;">
                                                        <?php
                                                        if ($currency === 'CDF') {
                                                            echo number_format((float)$newBalance, 0, ',', ' ') . ' FC';
                                                        } else {
                                                            echo '$' . number_format((float)$newBalance, 2) . ' USD';
                                                        }
                                                        ?>
                                                    </p>
                                                </td>
                                                <td style="text-align: right; vertical-align: middle;">
                                                    <span style="font-size: 32px;">💰</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Bouton CTA -->
                    <tr>
                        <td align="center" style="padding: 0 40px 35px 40px;">
                            <a href="<?= htmlspecialchars($historyUrl) ?>" style="background: linear-gradient(135deg, #10B981, #059669); color: #000000; font-weight: 800; font-size: 14px; text-decoration: none; border-radius: 12px; padding: 16px 40px; display: inline-block; box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4); text-transform: uppercase; letter-spacing: 0.5px;">
                                📋 Voir l'Historique de mes Recharges
                            </a>
                        </td>
                    </tr>

                    <!-- Message de fidélité -->
                    <tr>
                        <td style="padding: 0 40px 20px 40px;">
                            <p style="margin: 0; font-size: 13px; color: #6b7280; text-align: center; line-height: 1.6;">
                                Merci de votre confiance, <strong style="color: #f8fafc;"><?= htmlspecialchars($username) ?></strong> ! 🙏<br>
                                Vous pouvez maintenant commander vos services SMM directement depuis votre tableau de bord.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 25px 40px; background-color: #050811; border-top: 1px solid #1a2332; color: #71717a; font-size: 11px; line-height: 1.6;">
                            <p style="margin: 0 0 6px 0; color: #e2e8f0; font-weight: 700;">KivuBoost · Portefeuille & Transactions</p>
                            <p style="margin: 0 0 10px 0;">© <?= date('Y') ?> KivuBoost. Tous droits réservés.<br>TAL Communities · Bukavu, Sud-Kivu, RDC.</p>
                            <p style="margin: 0;">Cet e-mail confirme une transaction validée par notre équipe administrative.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
