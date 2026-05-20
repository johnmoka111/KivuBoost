<?php
use App\Core\Auth;
$pageTitle = 'Créer un compte';
?>

<div class="mb-6 text-center">
  <img src="<?= APP_BASE ?>/assets/logo.jpeg" alt="BukavuBoost Logo" class="w-16 h-16 rounded-full object-cover mx-auto mb-4 shadow-[0_0_15px_rgba(0,255,136,0.2)] border border-[#1a2332]">
  <h2 class="text-xl font-bold text-white">Créer un compte</h2>
  <p class="text-gray-500 text-sm mt-1">Rejoignez BukavuBoost et boostez vos réseaux</p>
</div>

<form method="POST" action="<?= APP_BASE ?>/register" class="space-y-4" novalidate>
  <?= Auth::csrfField() ?>

  <!-- Nom d'utilisateur -->
  <div>
    <label class="block text-xs font-medium text-gray-400 mb-1.5" for="username">Nom d'utilisateur</label>
    <input
      type="text"
      id="username"
      name="username"
      autocomplete="username"
      required
      placeholder="monpseudo"
      maxlength="50"
      class="input-field w-full px-4 py-3 rounded-xl text-sm"
      value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
    >
  </div>

  <!-- Email -->
  <div>
    <label class="block text-xs font-medium text-gray-400 mb-1.5" for="email">Adresse Email</label>
    <input
      type="email"
      id="email"
      name="email"
      autocomplete="email"
      required
      placeholder="votre@email.com"
      class="input-field w-full px-4 py-3 rounded-xl text-sm"
      value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
    >
  </div>

  <!-- Mot de passe -->
  <div>
    <label class="block text-xs font-medium text-gray-400 mb-1.5" for="password">Mot de passe <span class="text-gray-600">(min. 8 caractères)</span></label>
    <div class="relative">
      <input
        type="password"
        id="password"
        name="password"
        autocomplete="new-password"
        required
        minlength="8"
        placeholder="••••••••"
        class="input-field w-full px-4 py-3 rounded-xl text-sm pr-12"
        oninput="checkStrength(this.value)"
      >
      <button type="button" onclick="togglePwd('password', this)"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
      </button>
    </div>
    <!-- Barre de force -->
    <div class="mt-1.5 h-1 w-full rounded-full overflow-hidden bg-[#1a2332]">
      <div id="strength-bar" class="h-full rounded-full transition-all duration-300" style="width:0%;background:#ef4444"></div>
    </div>
  </div>

  <!-- Confirmer mot de passe -->
  <div>
    <label class="block text-xs font-medium text-gray-400 mb-1.5" for="password_confirm">Confirmer le mot de passe</label>
    <div class="relative">
      <input
        type="password"
        id="password_confirm"
        name="password_confirm"
        autocomplete="new-password"
        required
        placeholder="••••••••"
        class="input-field w-full px-4 py-3 rounded-xl text-sm pr-12"
      >
      <button type="button" onclick="togglePwd('password_confirm', this)"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
      </button>
    </div>
  </div>

  <button type="submit" class="btn-primary w-full py-3 rounded-xl text-sm mt-2">
    Créer mon compte
  </button>
</form>

<div class="mt-6 pt-6 border-t border-[#1a2332] text-center">
  <p class="text-sm text-gray-500">
    Déjà inscrit ?
    <a href="<?= APP_BASE ?>/login" class="font-semibold transition-colors" style="color:#00ff88">
      Se connecter
    </a>
  </p>
</div>

<script>
function togglePwd(fieldId, btn) {
  const f = document.getElementById(fieldId);
  f.type = f.type === 'password' ? 'text' : 'password';
  btn.style.color = f.type === 'text' ? '#00ff88' : '';
}
function checkStrength(val) {
  const bar = document.getElementById('strength-bar');
  let score = 0;
  if (val.length >= 8)  score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;
  const colors = ['#ef4444','#f97316','#eab308','#00ff88'];
  const widths  = ['25%','50%','75%','100%'];
  bar.style.width  = score > 0 ? widths[score-1] : '0%';
  bar.style.background = score > 0 ? colors[score-1] : '#ef4444';
}
</script>
