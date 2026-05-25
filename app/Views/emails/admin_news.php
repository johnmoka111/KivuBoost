<?php
/**
 * Template : Flash Info - Nouveaux Services / Messages Personnalisés
 * Variables attendues :
 *  - $username : Nom d'utilisateur (ou "Cher Client")
 *  - $title : Titre personnalisé de l'annonce
 *  - $content : Description / corps textuel du message
 *  - $actionUrl : URL d'action vers le dashboard KivuBoost
 *  - $actionText : Libellé du bouton (ex: "🚀 Propulser maintenant")
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>
</head>
<body style="margin: 0; padding: 0; background-color: #000000; font-family: 'Inter', -apple-system, BlinkMacSystemFont, Arial, sans-serif; color: #ffffff; -webkit-font-smoothing: antialiased;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #000000; padding: 40px 10px;">
        <tr>
            <td align="center">
                <!-- Main Card -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #0d1117; border: 1px solid #1a2332; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
                    <!-- Header/Logo -->
                    <tr>
                        <td align="center" style="padding: 40px 40px 20px 40px;">
                            <img src="https://placehold.co/120x120/0d1117/10b981?text=KivuBoost&font=inter" alt="KivuBoost Logo" style="width: 80px; height: 80px; border-radius: 50%; border: 2px solid #1a2332; box-shadow: 0 0 20px rgba(16, 185, 129, 0.2); object-cover: true;">
                            <h1 style="margin: 20px 0 0 0; color: #ffffff; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">NOUVEAUTÉ SMM</h1>
                            <p style="margin: 5px 0 0 0; color: #10b981; font-size: 11px; text-transform: uppercase; font-weight: bold; letter-spacing: 1.5px;">Flash Info KivuBoost</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 20px 40px 30px 40px; color: #e2e8f0; font-size: 15px; line-height: 1.6;">
                            <p style="margin: 0 0 15px 0; font-size: 16px; font-weight: 600; color: #ffffff;">Bonjour <?= htmlspecialchars($username) ?>,</p>
                            
                            <h2 style="color: #ffffff; font-size: 18px; font-weight: 700; margin: 0 0 15px 0; line-height: 1.4; border-left: 3px solid #10b981; padding-left: 10px;"><?= htmlspecialchars($title) ?></h2>
                            
                            <div style="color: #c9d1d9; margin-bottom: 25px;">
                                <?= nl2br(htmlspecialchars($content)) ?>
                            </div>
                            
                            <!-- Call to Action -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center" style="padding: 10px 0 20px 0;">
                                        <a href="<?= htmlspecialchars($actionUrl) ?>" style="background: linear-gradient(135deg, #10B981, #059669); color: #000000; font-weight: 800; font-size: 14px; text-decoration: none; border-radius: 12px; padding: 16px 36px; display: inline-block; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35); text-transform: uppercase; letter-spacing: 0.5px;"><?= htmlspecialchars($actionText) ?></a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 30px 40px; background-color: #050811; border-top: 1px solid #1a2332; color: #71717a; font-size: 11px;">
                            <p style="margin: 0 0 10px 0; color: #e2e8f0; font-weight: 600;">Propulsé par l'excellence</p>
                            <p style="margin: 0 0 15px 0; line-height: 1.5;">© <?= date('Y') ?> KivuBoost. Tous droits réservés.<br>TAL Communities · Avenue Patrice Lumumba, Bukavu, Sud-Kivu, RDC.</p>
                            <p style="margin: 0;">Vous recevez cet e-mail car vous êtes membre de la plateforme KivuBoost. Pour gérer vos préférences d'e-mails, rendez-vous dans vos paramètres de profil.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
