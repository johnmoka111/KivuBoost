<?php
use App\Core\Auth;
$pageTitle = 'Connexion';
?>

<div class="mb-6 text-center">
  <img src="<?= APP_BASE ?>/assets/logo.jpeg" alt="KivuBoost Logo" class="w-16 h-16 rounded-full object-cover mx-auto mb-4 shadow-[0_0_15px_rgba(0,255,136,0.2)] border border-[#1a2332]">
  <h2 class="text-xl font-bold text-white">Connexion</h2>
  <p class="text-gray-500 text-sm mt-1">Accédez à votre espace KivuBoost</p>
</div>

<form method="POST" action="<?= APP_BASE ?>/login" class="space-y-4" novalidate>
  <?= Auth::csrfField() ?>

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
    <label class="block text-xs font-medium text-gray-400 mb-1.5" for="password">Mot de passe</label>
    <div class="relative">
      <input
        type="password"
        id="password"
        name="password"
        autocomplete="current-password"
        required
        placeholder="••••••••"
        class="input-field w-full px-4 py-3 rounded-xl text-sm pr-12"
      >
      <button type="button" onclick="togglePwd('password', this)"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors">
        <svg id="eye-password" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
      </button>
    </div>
  </div>

  <!-- Bouton -->
  <button type="submit" class="btn-primary w-full py-3 rounded-xl text-sm mt-2">
    Se connecter
  </button>
</form>

<div class="mt-6 pt-6 border-t border-[#1a2332] text-center">
  <p class="text-sm text-gray-500">
    Pas encore de compte ?
    <a href="<?= APP_BASE ?>/register" class="font-semibold transition-colors" style="color:#00ff88">
      Créer un compte
    </a>
  </p>
</div>

<script>
function togglePwd(fieldId, btn) {
  const field = document.getElementById(fieldId);
  field.type = field.type === 'password' ? 'text' : 'password';
  btn.style.color = field.type === 'text' ? '#00ff88' : '';
}
</script>
