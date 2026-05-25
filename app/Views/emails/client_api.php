<?php
/**
 * Template : Alerte Sécurité — Nouvelle Clé API
 * Variables : $username, $apiKey, $ipAddress, $dateTime
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerte de Sécurité — KivuBoost</title>
</head>
<body style="margin:0;padding:0;background:#0a0d14;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;color:#c9d1d9;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#0a0d14;padding:48px 16px;">
  <tr>
    <td align="center">
      <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:580px;background:#0d1117;border:1px solid #1e2a3a;border-radius:8px;overflow:hidden;">

        <!-- En-tête -->
        <tr>
          <td style="background:#0d1117;border-bottom:3px solid #f59e0b;padding:36px 48px 28px;">
            <div style="font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:#f59e0b;margin-bottom:10px;">KivuBoost · Sécurité</div>
            <div style="font-size:24px;font-weight:700;color:#ffffff;line-height:1.25;">Alerte de sécurité</div>
            <div style="font-size:14px;color:#8b949e;margin-top:6px;">Une nouvelle clé API a été générée sur votre compte.</div>
          </td>
        </tr>

        <!-- Corps -->
        <tr>
          <td style="padding:36px 48px;">
            <p style="margin:0 0 24px;font-size:15px;line-height:1.7;color:#e2e8f0;">
              Bonjour <strong style="color:#ffffff;"><?= htmlspecialchars($username) ?></strong>,
            </p>
            <p style="margin:0 0 28px;font-size:14px;line-height:1.8;color:#8b949e;">
              Cette notification vous informe qu'une nouvelle clé d'accès à l'API KivuBoost a été créée pour votre compte. L'ancienne clé a été révoquée immédiatement. Si vous n'êtes pas à l'origine de cette action, prenez contact avec notre support sans délai.
            </p>

            <!-- Détails événement -->
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#060a10;border:1px solid #1e2a3a;border-radius:6px;margin-bottom:24px;">
              <tr>
                <td style="padding:22px 28px;">
                  <div style="font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#4b5563;margin-bottom:14px;">Détails de l'événement</div>
                  <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                      <td style="padding:7px 0;border-bottom:1px solid #1e2a3a;">
                        <span style="font-size:11px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Date et heure</span>
                        <span style="float:right;font-size:12px;font-weight:600;color:#e2e8f0;"><?= htmlspecialchars($dateTime) ?></span>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding:7px 0;">
                        <span style="font-size:11px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Adresse IP</span>
                        <span style="float:right;font-size:12px;font-weight:700;color:#e2e8f0;font-family:monospace;"><?= htmlspecialchars($ipAddress) ?></span>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>

            <!-- Clé API -->
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#060a10;border:1px solid #1e2a3a;border-left:3px solid #10b981;border-radius:4px;margin-bottom:28px;">
              <tr>
                <td style="padding:22px 28px;">
                  <div style="font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#10b981;margin-bottom:12px;">Nouvelle clé API</div>
                  <div style="font-size:12px;font-family:monospace;color:#ffffff;word-break:break-all;background:#0a0d14;padding:12px 14px;border-radius:4px;border:1px solid #1e2a3a;line-height:1.6;"><?= htmlspecialchars($apiKey) ?></div>
                  <div style="font-size:11px;color:#6b7280;margin-top:10px;">Ne communiquez jamais cette clé à un tiers. Elle donne un accès complet à votre compte via l'API.</div>
                </td>
              </tr>
            </table>

            <!-- Alerte action -->
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#0d1117;border:1px solid #2d1f1f;border-left:3px solid #ef4444;border-radius:4px;">
              <tr>
                <td style="padding:14px 18px;">
                  <p style="margin:0;font-size:12px;color:#9ca3af;line-height:1.6;">
                    <strong style="color:#ef4444;">Action requise :</strong>
                    Si vous n'avez pas initié cette opération, modifiez votre mot de passe immédiatement et contactez notre support technique.
                  </p>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Pied de page -->
        <tr>
          <td style="padding:24px 48px;background:#060a10;border-top:1px solid #1e2a3a;text-align:center;">
            <div style="font-size:12px;font-weight:700;color:#e2e8f0;margin-bottom:6px;">KivuBoost — Système de Sécurité</div>
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
