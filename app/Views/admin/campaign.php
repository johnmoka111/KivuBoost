<?php
use App\Core\Auth;
Auth::requireAdmin();

$pageTitle = "Diffuseur de Campagnes & Flash Info";
?>

<div class="max-w-5xl mx-auto space-y-6">
  <!-- Back Link & Header -->
  <div class="flex items-center justify-between">
    <div>
      <a href="<?= APP_BASE ?>/admin" class="text-xs font-semibold text-gray-500 hover:text-emerald-400 flex items-center gap-1 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Retour à l'espace administration
      </a>
      <h1 class="text-2xl font-bold text-white tracking-tight mt-2 flex items-center gap-2">
        <svg class="w-6 h-6 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
        Diffuseur de Campagnes
      </h1>
      <p class="text-gray-500 text-xs mt-1">Communiquez en direct avec l'intégralité de vos clients KivuBoost via des e-mails thémés premium.</p>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <!-- Form Card -->
    <div class="lg:col-span-6 rounded-2xl border p-6 space-y-5 shadow-2xl" style="background:#0d1117;border-color:#1a2332">
      <h2 class="font-bold text-white text-sm border-b border-[#1a2332] pb-3 flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
        Composition de l'Annonce
      </h2>
      
      <form method="POST" action="<?= APP_BASE ?>/admin/campaign/send" class="space-y-4" id="campaign-form">
        <?= Auth::csrfField() ?>

        <!-- Sujet de l'Email -->
        <div>
          <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider" for="subject">Sujet du mail (Objet)</label>
          <input type="text" 
                 name="subject" 
                 id="subject" 
                 required 
                 placeholder="🔥 Nouveaux services YouTube disponibles sur KivuBoost !" 
                 class="input-field w-full px-3 py-2.5 rounded-xl text-sm"
                 oninput="updateEmailPreview()">
          <p class="text-[10px] text-gray-600 mt-1">Sujet visible par le client dans sa boîte de réception.</p>
        </div>

        <!-- Titre de l'Annonce (Thématique) -->
        <div>
          <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider" for="title">Titre de l'Annonce (Headline)</label>
          <input type="text" 
                 name="title" 
                 id="title" 
                 required 
                 placeholder="🚀 Boostez vos vidéos à Bukavu !" 
                 class="input-field w-full px-3 py-2.5 rounded-xl text-sm"
                 oninput="updateEmailPreview()">
          <p class="text-[10px] text-gray-600 mt-1">S'affiche en grand avec une bordure thématique émeraude.</p>
        </div>

        <!-- Corps du Message -->
        <div>
          <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider" for="content">Corps du Message (HTML autorisé)</label>
          <textarea name="content" 
                    id="content" 
                    rows="6" 
                    required 
                    placeholder="Nous venons d'intégrer des serveurs ultra-rapides pour vos commandes de vues YouTube. Bénéficiez de 15% de réduction cette semaine !" 
                    class="input-field w-full px-3 py-2.5 rounded-xl text-sm leading-relaxed"
                    oninput="updateEmailPreview()"></textarea>
          <p class="text-[10px] text-gray-600 mt-1">Utilisez des sauts de ligne pour aérer le message.</p>
        </div>

        <!-- Libellé du Bouton d'Action -->
        <div>
          <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider" for="action_text">Libellé du Bouton</label>
          <input type="text" 
                 name="action_text" 
                 id="action_text" 
                 placeholder="🚀 Propulser mon audience" 
                 class="input-field w-full px-3 py-2.5 rounded-xl text-sm"
                 oninput="updateEmailPreview()">
        </div>

        <!-- URL d'Action -->
        <div>
          <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider" for="action_url">URL de redirection (Redirection du bouton)</label>
          <input type="url" 
                 name="action_url" 
                 id="action_url" 
                 placeholder="<?= APP_URL ?>/dashboard" 
                 class="input-field w-full px-3 py-2.5 rounded-xl text-sm font-mono"
                 oninput="updateEmailPreview()">
          <p class="text-[10px] text-gray-600 mt-1">Par défaut : redirige vers le tableau de bord du client.</p>
        </div>

        <!-- Bouton de validation -->
        <div class="pt-2">
          <button type="submit" 
                  class="btn-primary w-full py-3 rounded-xl text-sm font-bold flex items-center justify-center gap-2 hover:brightness-110 shadow-lg shadow-emerald-500/20"
                  onclick="return confirm('Êtes-vous sûr de vouloir diffuser cet e-mail à l\'intégralité des clients de la plateforme ? Cette action est irréversible.')">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            Diffuser la campagne en masse
          </button>
        </div>
      </form>
    </div>

    <!-- Live Preview Column -->
    <div class="lg:col-span-6 space-y-3">
      <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider flex items-center gap-2">
        <svg class="w-4 h-4 text-emerald-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
        Aperçu en temps réel (Premium Dark Theme)
      </h3>

      <div class="rounded-2xl border p-2 overflow-hidden shadow-2xl border-[#1a2332]" style="background:#000000; max-height: 650px; overflow-y: auto;">
        <!-- Embedded Email Container Mockup -->
        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #000000; padding: 20px 10px;">
          <tr>
            <td align="center">
              <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 500px; background-color: #0d1117; border: 1px solid #1a2332; border-radius: 12px; overflow: hidden;">
                <!-- Header -->
                <tr>
                  <td align="center" style="padding: 25px 25px 15px 25px;">
                    <img src="https://placehold.co/120x120/0d1117/10b981?text=KivuBoost&font=inter" alt="Logo" style="width: 55px; height: 55px; border-radius: 50%; border: 2px solid #1a2332; object-fit: cover;">
                    <h1 style="margin: 12px 0 0 0; color: #ffffff; font-size: 18px; font-weight: 800; font-family: 'Inter', sans-serif;">NOUVEAUTÉ SMM</h1>
                    <p style="margin: 3px 0 0 0; color: #10b981; font-size: 9px; text-transform: uppercase; font-weight: bold; letter-spacing: 1px; font-family: 'Inter', sans-serif;">Flash Info KivuBoost</p>
                  </td>
                </tr>
                
                <!-- Content -->
                <tr>
                  <td style="padding: 15px 25px 25px 25px; color: #e2e8f0; font-size: 13px; line-height: 1.5; font-family: 'Inter', sans-serif;">
                    <p style="margin: 0 0 10px 0; font-size: 14px; font-weight: 600; color: #ffffff;">Bonjour <?= htmlspecialchars($user['username']) ?>,</p>
                    
                    <h2 id="prev-title" style="color: #ffffff; font-size: 15px; font-weight: 700; margin: 0 0 12px 0; line-height: 1.3; border-left: 3px solid #10b981; padding-left: 8px;">🚀 Boostez vos vidéos à Bukavu !</h2>
                    
                    <div id="prev-content" style="color: #c9d1d9; margin-bottom: 20px; white-space: pre-line;">Nous venons d'intégrer des serveurs ultra-rapides pour vos commandes de vues YouTube. Bénéficiez de 15% de réduction cette semaine !</div>
                    
                    <!-- Call to Action -->
                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                      <tr>
                        <td align="center" style="padding: 5px 0 10px 0;">
                          <a id="prev-action-btn" href="#" style="background: linear-gradient(135deg, #10B981, #059669); color: #000000; font-weight: 800; font-size: 12px; text-decoration: none; border-radius: 8px; padding: 12px 24px; display: inline-block; text-transform: uppercase; letter-spacing: 0.5px; font-family: 'Inter', sans-serif;">🚀 Propulser mon audience</a>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
                
                <!-- Footer -->
                <tr>
                  <td align="center" style="padding: 20px 25px; background-color: #050811; border-top: 1px solid #1a2332; color: #71717a; font-size: 9px; font-family: 'Inter', sans-serif; line-height: 1.4;">
                    <p style="margin: 0 0 5px 0; color: #e2e8f0; font-weight: 600;">Propulsé par l'excellence</p>
                    <p style="margin: 0 0 10px 0;">© <?= date('Y') ?> KivuBoost. Tous droits réservés.<br>Sud-Kivu, RDC.</p>
                    <p style="margin: 0;">Vous recevez cet e-mail car vous êtes membre de la plateforme KivuBoost.</p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
function updateEmailPreview() {
  const titleVal = document.getElementById('title').value.trim();
  const contentVal = document.getElementById('content').value.trim();
  const actionTextVal = document.getElementById('action_text').value.trim();
  const actionUrlVal = document.getElementById('action_url').value.trim();

  // Mettre à jour le titre
  document.getElementById('prev-title').textContent = titleVal || "🚀 Boostez vos vidéos à Bukavu !";
  
  // Mettre à jour le contenu
  document.getElementById('prev-content').textContent = contentVal || "Nous venons d'intégrer des serveurs ultra-rapides pour vos commandes de vues YouTube. Bénéficiez de 15% de réduction cette semaine !";
  
  // Mettre à jour le bouton
  const btn = document.getElementById('prev-action-btn');
  btn.textContent = actionTextVal || "🚀 Propulser mon audience";
  btn.href = actionUrlVal || "<?= APP_URL ?>/dashboard";
}
</script>
