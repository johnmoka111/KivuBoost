<?php
/**
 * bottom_nav.php — Barre de Navigation Basse Mobile (md:hidden)
 * 
 * Boutons :
 *  1. Commandes  → /history
 *  2. Tarifs     → /services
 *  3. [CENTRAL]  Propulser → /dashboard  (mini-menu contextuel)
 *  4. Alimenter  → /recharge
 *  5. Profil     → /profile  (ou Régie → /admin si admin)
 */

use App\Core\Auth;

$base       = defined('APP_BASE') ? APP_BASE : '';
$currentUri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
// Supprimer le prefix du base si présent
$path = '/' . ltrim(str_replace($base, '', $currentUri), '/');

/**
 * Détecte si un chemin est actif.
 */
$isActive = fn(string $route): bool => $path === $route || str_starts_with($path, $route . '/');

$isAdmin = Auth::isAdmin();
?>

<!-- ===== BOTTOM NAV (Mobile Only) ===== -->
<nav class="md:hidden fixed bottom-0 left-0 right-0 z-50 border-t border-gray-800/80"
     style="background:rgba(5,8,17,0.96);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);">

  <!-- Mini-menu contextuel du bouton Central (caché par défaut) -->
  <div id="boost-context-menu"
       class="absolute bottom-20 left-1/2 -translate-x-1/2 hidden z-[60] transition-all duration-200"
       style="min-width:200px">
    <div class="rounded-2xl border overflow-hidden shadow-2xl shadow-emerald-500/10"
         style="background:#0d1117;border-color:#1a2332">
      <div class="px-3 py-2 border-b border-[#1a2332]">
        <p class="text-[10px] text-gray-500 font-semibold uppercase tracking-widest text-center">Action rapide</p>
      </div>
      <a href="<?= $base ?>/dashboard"
         onclick="closeBoostMenu()"
         class="flex items-center gap-3 px-4 py-3 hover:bg-white/[0.04] transition-colors border-b border-[#1a2332]">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0" style="background:rgba(0,255,136,0.1)">
          <svg class="w-4 h-4" style="color:#00ff88" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
          </svg>
        </div>
        <div>
          <div class="text-xs font-bold text-white">Boost Express</div>
          <div class="text-[10px] text-gray-500">Nouvelle commande rapide</div>
        </div>
      </a>
      <a href="<?= $base ?>/history"
         onclick="closeBoostMenu()"
         class="flex items-center gap-3 px-4 py-3 hover:bg-white/[0.04] transition-colors <?= $isAdmin ? 'border-b border-[#1a2332]' : '' ?>">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0" style="background:rgba(0,212,255,0.1)">
          <svg class="w-4 h-4" style="color:#00d4ff" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
        </div>
        <div>
          <div class="text-xs font-bold text-white">Suivre mes liens</div>
          <div class="text-[10px] text-gray-500">Voir l'historique des commandes</div>
        </div>
      </a>
      <?php if ($isAdmin): ?>
      <a href="<?= $base ?>/admin/campaign"
         onclick="closeBoostMenu()"
         class="flex items-center gap-3 px-4 py-3 hover:bg-white/[0.04] transition-colors border-b border-[#1a2332]">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0" style="background:rgba(124,58,237,0.15)">
          <svg class="w-4 h-4" style="color:#a78bfa" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
          </svg>
        </div>
        <div>
          <div class="text-xs font-bold text-white">Diffuseur de Campagnes</div>
          <div class="text-[10px] text-gray-500">Envoyer des e-mails groupés</div>
        </div>
      </a>
      <a href="<?= $base ?>/admin/actualites"
         onclick="closeBoostMenu()"
         class="flex items-center gap-3 px-4 py-3 hover:bg-white/[0.04] transition-colors">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0" style="background:rgba(244,114,182,0.12)">
          <svg class="w-4 h-4" style="color:#f472b6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
          </svg>
        </div>
        <div>
          <div class="text-xs font-bold text-white">Rédiger une Actualité</div>
          <div class="text-[10px] text-gray-500">Publier sur la page Actus</div>
        </div>
      </a>
      <?php endif; ?>
    </div>
    <!-- Petite flèche décorative -->
    <div class="flex justify-center mt-1">
      <div class="w-3 h-3 rotate-45 border-b border-r border-[#1a2332]" style="background:#0d1117;"></div>
    </div>
  </div>

  <!-- Overlay transparent pour fermer le menu en cliquant à l'extérieur -->
  <div id="boost-overlay" class="hidden fixed inset-0 z-[55]" onclick="closeBoostMenu()"></div>

  <!-- Grille des 5 boutons -->
  <div class="grid grid-cols-5 items-end justify-items-center h-16 px-1">

    <!-- BTN 1 : Commandes (Mes Tableaux) -->
    <a href="<?= $base ?>/history"
       class="relative flex flex-col items-center justify-center gap-1 w-full h-full transition-all active:scale-90
              <?= $isActive('/history') ? 'text-emerald-400' : 'text-gray-500 hover:text-gray-300' ?>">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="<?= $isActive('/history') ? '2.5' : '1.8' ?>"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
      </svg>
      <span class="text-[9px] font-semibold tracking-wide leading-none">Commandes</span>
      <?php if ($isActive('/history')): ?>
        <span class="absolute bottom-1 w-1 h-1 rounded-full bg-emerald-400"></span>
      <?php endif; ?>
    </a>

    <!-- BTN 2 : Tarifs -->
    <a href="<?= $base ?>/services"
       class="relative flex flex-col items-center justify-center gap-1 w-full h-full transition-all active:scale-90
              <?= $isActive('/services') ? 'text-cyan-400' : 'text-gray-500 hover:text-gray-300' ?>">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="<?= $isActive('/services') ? '2.5' : '1.8' ?>"
              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
      </svg>
      <span class="text-[9px] font-semibold tracking-wide leading-none">Tarifs</span>
      <?php if ($isActive('/services')): ?>
        <span class="absolute bottom-1 w-1 h-1 rounded-full bg-cyan-400"></span>
      <?php endif; ?>
    </a>

    <!-- BTN 3 : CENTRAL — Propulser -->
    <div class="flex flex-col items-center justify-center w-full h-full relative -mt-5">
      <button onclick="toggleBoostMenu()"
              class="w-14 h-14 rounded-full flex items-center justify-center transition-all active:scale-90 border-4 border-[#050811]
                     shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50"
              style="background:linear-gradient(135deg,#00ff88,#00c466);color:#050811"
              aria-label="Propulser">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
      </button>
      <span class="text-[9px] font-semibold text-emerald-400 tracking-wide mt-0.5 leading-none">Propulser</span>
    </div>

    <!-- BTN 4 : Alimenter (Recharge) -->
    <a href="<?= $base ?>/recharge"
       class="relative flex flex-col items-center justify-center gap-1 w-full h-full transition-all active:scale-90
              <?= $isActive('/recharge') ? 'text-yellow-400' : 'text-gray-500 hover:text-gray-300' ?>">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="<?= $isActive('/recharge') ? '2.5' : '1.8' ?>"
              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <span class="text-[9px] font-semibold tracking-wide leading-none">Alimenter</span>
      <?php if ($isActive('/recharge')): ?>
        <span class="absolute bottom-1 w-1 h-1 rounded-full bg-yellow-400"></span>
      <?php endif; ?>
    </a>

    <!-- BTN 5 : Profil (ou Régie si admin) -->
    <?php if ($isAdmin): ?>
      <a href="<?= $base ?>/admin"
         class="relative flex flex-col items-center justify-center gap-1 w-full h-full transition-all active:scale-90
                <?= $isActive('/admin') ? 'text-purple-400' : 'text-gray-500 hover:text-gray-300' ?>">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="<?= $isActive('/admin') ? '2.5' : '1.8' ?>"
                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="<?= $isActive('/admin') ? '2.5' : '1.8' ?>"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <span class="text-[9px] font-semibold tracking-wide leading-none">Régie</span>
        <?php if ($isActive('/admin')): ?>
          <span class="absolute bottom-1 w-1 h-1 rounded-full bg-purple-400"></span>
        <?php endif; ?>
      </a>
    <?php else: ?>
      <a href="<?= $base ?>/profile"
         class="relative flex flex-col items-center justify-center gap-1 w-full h-full transition-all active:scale-90
                <?= $isActive('/profile') ? 'text-blue-400' : 'text-gray-500 hover:text-gray-300' ?>">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="<?= $isActive('/profile') ? '2.5' : '1.8' ?>"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <span class="text-[9px] font-semibold tracking-wide leading-none">Profil</span>
        <?php if ($isActive('/profile')): ?>
          <span class="absolute bottom-1 w-1 h-1 rounded-full bg-blue-400"></span>
        <?php endif; ?>
      </a>
    <?php endif; ?>

  </div>

  <!-- Safe area pour les téléphones avec notch bas (iPhone, etc.) -->
  <div class="h-safe-area-inset-bottom" style="height:env(safe-area-inset-bottom, 0px)"></div>
</nav>

<script>
function toggleBoostMenu() {
  const menu    = document.getElementById('boost-context-menu');
  const overlay = document.getElementById('boost-overlay');
  const isHidden = menu.classList.contains('hidden');
  if (isHidden) {
    menu.classList.remove('hidden');
    overlay.classList.remove('hidden');
    // Petite animation d'entrée
    menu.style.opacity = '0';
    menu.style.transform = 'translateX(-50%) translateY(8px)';
    requestAnimationFrame(() => {
      menu.style.transition = 'opacity 0.2s, transform 0.2s';
      menu.style.opacity = '1';
      menu.style.transform = 'translateX(-50%) translateY(0)';
    });
  } else {
    closeBoostMenu();
  }
}

function closeBoostMenu() {
  const menu    = document.getElementById('boost-context-menu');
  const overlay = document.getElementById('boost-overlay');
  menu.style.opacity = '0';
  menu.style.transform = 'translateX(-50%) translateY(8px)';
  setTimeout(() => {
    menu.classList.add('hidden');
    overlay.classList.add('hidden');
    menu.style.transform = '';
    menu.style.opacity  = '';
    menu.style.transition = '';
  }, 180);
}
</script>
