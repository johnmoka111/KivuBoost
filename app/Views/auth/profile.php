<?php
use App\Core\Auth;
$pageTitle = 'Mon Profil';
?>

<div class="max-w-xl mx-auto">

  <!-- Header -->
  <div class="mb-6">
    <h1 class="text-xl font-bold text-white flex items-center gap-2">
      <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg> Mon Profil Utilisateur
    </h1>
    <p class="text-gray-500 text-sm mt-1">Gérez vos informations de connexion et votre photo d'identité</p>
  </div>

  <!-- Formulaire principal -->
  <div class="rounded-2xl p-6 border" style="background:#0d1117;border-color:#1a2332;box-shadow:0 10px 30px rgba(0,0,0,0.2)">
    <form method="POST" action="<?= APP_BASE ?>/profile/update" enctype="multipart/form-data" class="space-y-6">
      <?= Auth::csrfField() ?>

      <!-- Zone Avatar / Téléversement Photo -->
      <div class="flex flex-col sm:flex-row items-center gap-5 pb-5 border-b" style="border-color:#1a2332">
        <div class="relative group select-none cursor-pointer" onclick="document.getElementById('avatar-upload').click()">
          <?php if (!empty($user['avatar']) && file_exists(ROOT_PATH . '/public/uploads/avatars/' . $user['avatar'])): ?>
            <img src="<?= APP_BASE ?>/public/uploads/avatars/<?= htmlspecialchars($user['avatar']) ?>" 
                 id="avatarPreview"
                 class="w-24 h-24 rounded-full object-cover border-2 transition-all group-hover:opacity-75"
                 style="border-color:#00ff88">
          <?php else: ?>
            <div id="avatarFallback"
                 class="w-24 h-24 rounded-full flex items-center justify-center font-bold text-black text-3xl uppercase border-2 transition-all group-hover:opacity-75"
                 style="background:#00ff88;border-color:#00ff88">
              <?= substr(htmlspecialchars($user['username']), 0, 1) ?>
            </div>
            <!-- Dynamic Preview element (hidden initially) -->
            <img src="" id="avatarPreview" class="w-24 h-24 rounded-full object-cover border-2 border-[#00ff88] hidden">
          <?php endif; ?>
          
          <div class="absolute inset-0 flex items-center justify-center bg-black/40 rounded-full opacity-0 group-hover:opacity-100 transition-all pointer-events-none text-xs text-white font-semibold">
            Changer
          </div>
        </div>

        <div class="flex-1 text-center sm:text-left">
          <button type="button" onclick="document.getElementById('avatar-upload').click()" class="flex items-center gap-1.5 text-xs font-semibold text-[#00ff88] uppercase tracking-wider mb-2 cursor-pointer hover:underline justify-center sm:justify-start">
            <svg class="w-4 h-4 text-[#00ff88] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Téléverser une nouvelle photo
          </button>
          <input type="file" name="avatar" id="avatar-upload" accept="image/*" class="hidden" onchange="previewImage(this)">
          <p class="text-xs text-gray-500 mt-1">Recommandé : Format carré (PNG, JPG, max 3 Mo).</p>
        </div>
      </div>

      <!-- Informations utilisateur -->
      <div class="space-y-4">
        <!-- Pseudo -->
        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5" for="username">Nom d'utilisateur</label>
          <input type="text" name="username" id="username" required
                 value="<?= htmlspecialchars($user['username']) ?>"
                 class="w-full px-4 py-3 rounded-xl text-sm"
                 style="background:#0a0f1a;border:1px solid #1a2332;color:#e2e8f0;transition:border-color .2s"
                 onfocus="this.style.borderColor='rgba(0,255,136,0.5)'"
                 onblur="this.style.borderColor='#1a2332'">
        </div>

        <!-- Email -->
        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5" for="email">Adresse Email</label>
          <input type="email" name="email" id="email" required
                 value="<?= htmlspecialchars($user['email']) ?>"
                 class="w-full px-4 py-3 rounded-xl text-sm"
                 style="background:#0a0f1a;border:1px solid #1a2332;color:#e2e8f0;transition:border-color .2s"
                 onfocus="this.style.borderColor='rgba(0,255,136,0.5)'"
                 onblur="this.style.borderColor='#1a2332'">
        </div>

        <!-- Nouveau Mot de Passe -->
        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5" for="password">Nouveau mot de passe <span class="text-gray-600">(laisser vide pour ne pas modifier)</span></label>
          <input type="password" name="password" id="password"
                 placeholder="••••••••"
                 class="w-full px-4 py-3 rounded-xl text-sm"
                 style="background:#0a0f1a;border:1px solid #1a2332;color:#e2e8f0;transition:border-color .2s"
                 onfocus="this.style.borderColor='rgba(0,255,136,0.5)'"
                 onblur="this.style.borderColor='#1a2332'">
        </div>
      </div>

      <!-- Bouton d'enregistrement -->
      <button type="submit"
              class="w-full py-3.5 rounded-xl text-sm font-bold transition-all"
              style="background:linear-gradient(135deg,#00ff88,#00c466);color:#050811;box-shadow:0 4px 15px rgba(0,255,136,0.2)">
        Sauvegarder mes modifications
      </button>
    </form>
  </div>

</div>

<!-- ===== PREVIEW JAVASCRIPT ===== -->
<script>
function previewImage(input) {
  const file = input.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const preview = document.getElementById('avatarPreview');
      const fallback = document.getElementById('avatarFallback');
      
      preview.src = e.target.result;
      preview.classList.remove('hidden');
      if (fallback) {
        fallback.classList.add('hidden');
      }
    }
    reader.readAsDataURL(file);
  }
}
</script>
