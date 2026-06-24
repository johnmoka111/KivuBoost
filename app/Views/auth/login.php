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

<!-- Séparateur -->
<div class="relative my-5">
  <div class="absolute inset-0 flex items-center">
    <div class="w-full border-t border-[#1a2332]"></div>
  </div>
  <div class="relative flex justify-center">
    <span class="px-3 text-xs text-gray-600 font-medium" style="background:#0d1117">ou continuer avec</span>
  </div>
</div>

<!-- Bouton Google -->
<a href="<?= APP_BASE ?>/auth/google"
   class="flex items-center justify-center gap-3 w-full py-3 px-4 rounded-xl border border-[#1a2332] text-sm font-semibold text-white transition-all duration-200 hover:border-white/20 hover:bg-white/5 active:scale-[0.98]"
   style="background:#0a0f1a">
  <!-- Logo Google SVG officiel -->
  <svg width="18" height="18" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
    <path fill="none" d="M0 0h48v48H0z"/>
  </svg>
  Se connecter avec Google
</a>

<div class="mt-5 pt-5 border-t border-[#1a2332] text-center">
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
