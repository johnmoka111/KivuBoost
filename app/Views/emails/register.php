<?php
/**
 * Template : Bienvenue — Confirmation d'inscription
 * Variables : $username, $userEmail, $loginUrl
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue sur KivuBoost</title>
</head>
<body style="margin:0;padding:0;background:#0a0d14;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;color:#c9d1d9;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#0a0d14;padding:48px 16px;">
  <tr>
    <td align="center">
      <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:580px;background:#0d1117;border:1px solid #1e2a3a;border-radius:8px;overflow:hidden;">

        <!-- En-tête -->
        <tr>
          <td style="background:#0d1117;border-bottom:3px solid #10b981;padding:36px 48px 28px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td>
                  <div style="font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:#10b981;margin-bottom:10px;">KivuBoost · SMM Panel</div>
                  <div style="font-size:24px;font-weight:700;color:#ffffff;line-height:1.25;">Compte créé avec succès</div>
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
              Votre compte KivuBoost a été enregistré. Vous avez désormais accès à l'ensemble de notre catalogue de services SMM — abonnés, vues, interactions — pour toutes les plateformes majeures.
            </p>

            <!-- Bloc identifiants -->
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#060a10;border:1px solid #1e2a3a;border-radius:6px;margin-bottom:32px;">
              <tr>
                <td style="padding:22px 28px;">
                  <div style="font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#4b5563;margin-bottom:14px;">Identifiants du compte</div>
                  <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                      <td style="padding:6px 0;border-bottom:1px solid #1e2a3a;">
                        <span style="font-size:11px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Nom d'utilisateur</span>
                        <span style="float:right;font-size:13px;font-weight:700;color:#ffffff;"><?= htmlspecialchars($username) ?></span>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding:6px 0;">
                        <span style="font-size:11px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Adresse e-mail</span>
                        <span style="float:right;font-size:13px;font-weight:600;color:#10b981;font-family:monospace;"><?= htmlspecialchars($userEmail) ?></span>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>

            <!-- Services disponibles -->
            <div style="font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#4b5563;margin-bottom:16px;">Ce qui vous attend</div>
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:32px;">
              <?php
              $items = [
                  ['Services SMM multi-plateformes',    'Abonnés, vues et interactions sur toutes les réseaux.'],
                  ['Recharge Mobile Money',             'Mpesa, Airtel Money, Orange Money et Vodacom.'],
                  ['Suivi en temps réel',               'Tableau de bord live pour chaque commande passée.'],
                  ['Infrastructure sécurisée',          'Données chiffrées, sessions protégées.'],
              ];
              foreach ($items as $item): ?>
              <tr>
                <td style="padding:8px 0;border-bottom:1px solid #1e2a3a;">
                  <table cellpadding="0" cellspacing="0" border="0"><tr>
                    <td style="width:8px;vertical-align:top;padding-top:5px;">
                      <div style="width:4px;height:4px;background:#10b981;border-radius:50%;"></div>
                    </td>
                    <td style="padding-left:12px;">
                      <div style="font-size:13px;font-weight:600;color:#e2e8f0;"><?= $item[0] ?></div>
                      <div style="font-size:11px;color:#6b7280;margin-top:2px;"><?= $item[1] ?></div>
                    </td>
                  </tr></table>
                </td>
              </tr>
              <?php endforeach; ?>
            </table>

            <!-- Bouton CTA -->
            <table cellpadding="0" cellspacing="0" border="0" width="100%">
              <tr>
                <td align="center" style="padding:8px 0;">
                  <a href="<?= htmlspecialchars($loginUrl) ?>"
                     style="display:inline-block;background:#10b981;color:#000000;font-size:13px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;text-decoration:none;padding:16px 48px;border-radius:4px;">
                    Accéder à mon tableau de bord
                  </a>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Avertissement sécurité -->
        <tr>
          <td style="padding:0 48px 28px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#0d1117;border:1px solid #2d3748;border-left:3px solid #f59e0b;border-radius:4px;">
              <tr>
                <td style="padding:14px 18px;">
                  <p style="margin:0;font-size:12px;color:#9ca3af;line-height:1.6;">
                    <strong style="color:#f59e0b;">Avertissement de sécurité :</strong>
                    Si vous n'avez pas créé ce compte, ignorez cet e-mail. Aucune action ne sera requise de votre part.
                  </p>
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
