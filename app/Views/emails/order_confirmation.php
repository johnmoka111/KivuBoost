<?php
/**
 * Template : Confirmation de commande
 * Variables : $username, $orderId, $serviceName, $quantity, $cost, $link, $dashboardUrl
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de Commande - KivuBoost</title>
</head>
<body style="margin:0;padding:0;background:#0a0d14;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;color:#c9d1d9;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#0a0d14;padding:48px 16px;">
  <tr>
    <td align="center">
      <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:580px;background:#0d1117;border:1px solid #1e2a3a;border-radius:8px;overflow:hidden;">

        <!-- En-tête -->
        <tr>
          <td style="background:#0d1117;border-bottom:3px solid #00d4ff;padding:36px 48px 28px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td>
                  <div style="font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:#00d4ff;margin-bottom:10px;">KivuBoost · Commande</div>
                  <div style="font-size:24px;font-weight:700;color:#ffffff;line-height:1.25;">Commande Confirmée</div>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Corps -->
        <tr>
          <td style="padding:36px 48px;">
            <p style="margin:0 0 20px;font-size:15px;line-height:1.7;color:#e2e8f0;">
              Bonjour <strong style="color:#ffffff;"><?= htmlspecialchars($username) ?></strong>,
            </p>
            <p style="margin:0 0 28px;font-size:14px;line-height:1.8;color:#8b949e;">
              Nous vous confirmons la bonne réception de votre commande <strong>#<?= htmlspecialchars($orderId) ?></strong>. Elle est actuellement en cours de traitement.
            </p>

            <!-- Détails de la commande -->
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#060a10;border:1px solid #1e2a3a;border-radius:6px;margin-bottom:32px;">
              <tr>
                <td style="padding:22px 28px;">
                  <div style="font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#4b5563;margin-bottom:14px;">Détails</div>
                  <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                      <td style="padding:8px 0;border-bottom:1px solid #1e2a3a;">
                        <span style="font-size:11px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Service</span>
                        <div style="font-size:13px;font-weight:700;color:#ffffff;margin-top:4px;"><?= htmlspecialchars($serviceName) ?></div>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding:8px 0;border-bottom:1px solid #1e2a3a;">
                        <span style="font-size:11px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Cible</span>
                        <div style="font-size:12px;font-weight:400;color:#00d4ff;margin-top:4px;word-break:break-all;"><?= htmlspecialchars($link) ?></div>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding:8px 0;border-bottom:1px solid #1e2a3a;">
                        <span style="font-size:11px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Quantité</span>
                        <span style="float:right;font-size:13px;font-weight:700;color:#ffffff;"><?= htmlspecialchars($quantity) ?></span>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding:8px 0;">
                        <span style="font-size:11px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Coût total</span>
                        <span style="float:right;font-size:13px;font-weight:700;color:#10b981;">$<?= htmlspecialchars($cost) ?></span>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>

            <!-- Bouton CTA -->
            <table cellpadding="0" cellspacing="0" border="0" width="100%">
              <tr>
                <td align="center" style="padding:8px 0;">
                  <a href="<?= htmlspecialchars($dashboardUrl) ?>"
                     style="display:inline-block;background:#00d4ff;color:#000000;font-size:13px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;text-decoration:none;padding:16px 48px;border-radius:4px;">
                    Suivre ma commande
                  </a>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Pied de page -->
        <tr>
          <td style="padding:24px 48px;background:#060a10;border-top:1px solid #1e2a3a;text-align:center;">
            <div style="font-size:12px;font-weight:700;color:#e2e8f0;margin-bottom:6px;">KivuBoost — Plateforme SMM</div>
            <div style="font-size:11px;color:#4b5563;line-height:1.7;">
              &copy; <?= date('Y') ?> KivuBoost. Tous droits réservés.<br>
              TAL Communities &middot; Bukavu, Sud-Kivu, R.D. Congo
            </div>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>
</body>
</html>
