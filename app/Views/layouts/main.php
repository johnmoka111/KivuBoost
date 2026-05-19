<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? APP_NAME) ?> — BukavuBoost</title>
  <meta name="description" content="BukavuBoost — Panel SMM professionnel pour Bukavu, RDC. Boostez vos réseaux sociaux rapidement.">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            cyber: {
              bg:      '#050811',
              card:    '#0d1117',
              border:  '#1a2332',
              green:   '#00ff88',
              blue:    '#00d4ff',
              purple:  '#7c3aed',
            }
          },
          fontFamily: {
            sans: ['Inter', 'system-ui', 'sans-serif'],
            mono: ['JetBrains Mono', 'monospace'],
          },
          animation: {
            'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
            'glow':       'glow 2s ease-in-out infinite alternate',
          },
          keyframes: {
            glow: {
              '0%':   { boxShadow: '0 0 5px #00ff8830' },
              '100%': { boxShadow: '0 0 20px #00ff8860, 0 0 40px #00ff8820' },
            }
          }
        }
      }
    }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body { background-color: #050811; }
    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-track { background: #0d1117; }
    ::-webkit-scrollbar-thumb { background: #00ff8840; border-radius: 4px; }
    .nav-link-active { color: #00ff88; border-color: #00ff88; }
    .badge-pending    { background:#fbbf2415; color:#fbbf24; border:1px solid #fbbf2440; }
    .badge-processing { background:#00d4ff15; color:#00d4ff; border:1px solid #00d4ff40; }
    .badge-completed  { background:#00ff8815; color:#00ff88; border:1px solid #00ff8840; }
    .badge-canceled   { background:#ef444415; color:#ef4444; border:1px solid #ef444440; }
    .badge-partial    { background:#a78bfa15; color:#a78bfa; border:1px solid #a78bfa40; }
    .glass-card { background: rgba(13,17,23,0.9); backdrop-filter: blur(12px); border: 1px solid #1a2332; }
    .neon-border { border: 1px solid #00ff8830; box-shadow: 0 0 15px #00ff8810; }
    .btn-primary { background: linear-gradient(135deg, #00ff88, #00c466); color: #050811; font-weight: 700; transition: all .2s; }
    .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 20px #00ff8840; }
    .input-field { background:#0d1117; border:1px solid #1a2332; color:#e2e8f0; transition:border-color .2s; }
    .input-field:focus { outline:none; border-color:#00ff8860; box-shadow:0 0 0 2px #00ff8815; }
    .sidebar-item { transition: all .15s; border-left: 2px solid transparent; }
    .sidebar-item:hover, .sidebar-item.active { border-left-color:#00ff88; background:#00ff8808; color:#00ff88; }
  </style>
</head>
<body class="font-sans text-gray-100 min-h-screen" style="background:#050811">

<?php
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Currency;

$flash       = Controller::getFlash();
$currentUser = Auth::user();
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base        = APP_BASE;

function isActive(string $path): string {
    global $currentPath, $base;
    return str_contains($currentPath, $path) ? 'active' : '';
}
?>

<!-- ===== DESKTOP LAYOUT ===== -->
<div class="flex min-h-screen">

  <!-- Sidebar (Desktop) -->
  <aside class="hidden lg:flex flex-col w-64 fixed top-0 left-0 h-full border-r border-[#1a2332] z-40" style="background:#0a0f1a">
    <!-- Logo -->
    <div class="px-6 py-6 border-b border-[#1a2332]">
      <a href="<?= $base ?>/dashboard" class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:linear-gradient(135deg,#00ff88,#00d4ff)">
          <svg class="w-5 h-5 text-black" fill="currentColor" viewBox="0 0 20 20"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/></svg>
        </div>
        <div>
          <div class="font-bold text-white text-base leading-tight">BukavuBoost</div>
          <div class="text-[10px] text-gray-500 uppercase tracking-widest">SMM Panel</div>
        </div>
      </a>
    </div>

    <!-- User Info (Avec Avatar & Switch Devise) -->
    <div class="px-4 py-4 border-b border-[#1a2332] mx-2 mt-2 rounded-lg" style="background:#0d1117">
      <div class="flex items-center gap-3">
        <!-- Photo Profil -->
        <?php if (!empty($currentUser['avatar']) && file_exists(ROOT_PATH . '/public/uploads/avatars/' . $currentUser['avatar'])): ?>
          <img src="<?= $base ?>/public/uploads/avatars/<?= htmlspecialchars($currentUser['avatar']) ?>" 
               class="w-10 h-10 rounded-full object-cover border" style="border-color:#00ff88">
        <?php else: ?>
          <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold text-black uppercase" style="background:linear-gradient(135deg,#00ff88,#00c466)">
            <?= strtoupper(substr($currentUser['username'] ?? 'U', 0, 1)) ?>
          </div>
        <?php endif; ?>
        
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-white truncate"><?= htmlspecialchars($currentUser['username'] ?? '') ?></p>
          <p class="text-xs text-gray-400 capitalize"><?= htmlspecialchars($currentUser['role'] ?? 'user') ?></p>
        </div>
      </div>
      
      <!-- Solde avec bouton de bascule monétaire -->
      <div class="mt-3 pt-3 border-t border-[#1a2332] flex items-center justify-between">
        <div>
          <div class="text-xs text-gray-500 mb-0.5">Solde disponible</div>
          <div class="text-lg font-bold" style="color:#00ff88"><?= Currency::format((float)($currentUser['balance'] ?? 0)) ?></div>
        </div>
        <!-- Switcher -->
        <a href="<?= $base ?>/currency/switch" 
           title="Changer de devise (USD/CDF)"
           class="flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase border transition-all hover:bg-white/5 active:scale-95" 
           style="background:#0a0f1a;border-color:#1a2332;color:#00d4ff">
          <svg class="w-3 h-3 text-[#00d4ff] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
          <?= Currency::getActive() ?>
        </a>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
      <a href="<?= $base ?>/dashboard" class="sidebar-item <?= isActive('/dashboard') || $currentPath === $base . '/' ? 'active' : '' ?> flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Tableau de bord
      </a>
      <a href="<?= $base ?>/recharge" class="sidebar-item <?= isActive('/recharge') ? 'active' : '' ?> flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
        Recharger mon compte
      </a>
      <a href="<?= $base ?>/profile" class="sidebar-item <?= isActive('/profile') ? 'active' : '' ?> flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        Mon Compte / Photo
      </a>
      
      <?php if (Auth::isAdmin()): ?>
      <div class="pt-4 pb-1">
        <div class="text-[10px] font-semibold text-gray-600 uppercase tracking-widest px-3">Administration</div>
      </div>
      <a href="<?= $base ?>/admin" class="sidebar-item <?= isActive('/admin') ? 'active' : '' ?> flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        Espace Admin (Multi-API)
      </a>
      <?php endif; ?>
    </nav>

    <!-- Logout -->
    <div class="px-3 pb-6">
      <a href="<?= $base ?>/logout" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-500 hover:text-red-400 hover:bg-red-400/5 transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        Déconnexion
      </a>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 lg:ml-64 flex flex-col min-h-screen">

    <!-- Top bar (Mobile) -->
    <header class="lg:hidden flex items-center justify-between px-4 py-3 border-b border-[#1a2332] sticky top-0 z-30" style="background:#0a0f1a">
      <a href="<?= $base ?>/dashboard" class="flex items-center gap-2">
        <div class="w-7 h-7 rounded-md flex items-center justify-center" style="background:linear-gradient(135deg,#00ff88,#00d4ff)">
          <svg class="w-4 h-4 text-black" fill="currentColor" viewBox="0 0 20 20"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/></svg>
        </div>
        <span class="font-bold text-white text-sm">BukavuBoost</span>
      </a>
      <div class="flex items-center gap-3">
        <!-- Infos Monétaire en Mobile -->
        <div class="text-right">
          <div class="text-[9px] text-gray-500 flex items-center gap-1.5 justify-end">Solde · <a href="<?= $base ?>/currency/switch" class="inline-flex items-center gap-1 text-[#00d4ff] font-bold hover:underline"><?= Currency::getActive() ?> <svg class="w-2.5 h-2.5 text-[#00d4ff] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg></a></div>
          <div class="text-xs font-bold font-mono animate-pulse-slow" style="color:#00ff88"><?= Currency::format((float)($currentUser['balance'] ?? 0)) ?></div>
        </div>

        <!-- Photo de profil miniature mobile -->
        <a href="<?= $base ?>/profile">
          <?php if (!empty($currentUser['avatar']) && file_exists(ROOT_PATH . '/public/uploads/avatars/' . $currentUser['avatar'])): ?>
            <img src="<?= $base ?>/public/uploads/avatars/<?= htmlspecialchars($currentUser['avatar']) ?>" class="w-8 h-8 rounded-full object-cover border border-[#00ff88]">
          <?php else: ?>
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-black uppercase" style="background:#00ff88">
              <?= strtoupper(substr($currentUser['username'] ?? 'U', 0, 1)) ?>
            </div>
          <?php endif; ?>
        </a>
      </div>
    </header>

    <!-- Flash Message -->
    <?php if ($flash): ?>
    <div id="flash-msg" class="mx-4 mt-4 lg:mx-6 lg:mt-6 px-4 py-3 rounded-lg text-sm flex items-center justify-between
      <?= $flash['type'] === 'success'
          ? 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-400'
          : 'bg-red-500/10 border border-red-500/30 text-red-400' ?>">
      <span><?= htmlspecialchars($flash['message']) ?></span>
      <button onclick="document.getElementById('flash-msg').remove()" class="ml-4 opacity-60 hover:opacity-100">✕</button>
    </div>
    <?php endif; ?>

    <!-- Page Content -->
    <div class="flex-1 px-4 py-4 lg:px-6 lg:py-6 pb-24 lg:pb-6">
      <?= $content ?>
    </div>
  </main>
</div>

<!-- ===== MOBILE BOTTOM NAVIGATION ===== -->
<nav class="lg:hidden fixed bottom-0 left-0 right-0 border-t z-50 flex pb-safe" style="background:rgba(10,15,26,0.88);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-color:rgba(0,255,136,0.12);box-shadow:0 -8px 32px rgba(0,0,0,0.5)">
  <a href="<?= $base ?>/dashboard" class="flex-1 flex flex-col items-center py-3.5 text-xs gap-1.5 transition-all <?= isActive('/dashboard') || $currentPath === $base . '/' ? 'text-[#00ff88] font-bold drop-shadow-[0_0_8px_rgba(0,255,136,0.3)]' : 'text-gray-500 hover:text-gray-300' ?>">
    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:22px;height:22px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
    <span class="text-[10px] tracking-wide">Commander</span>
  </a>
  <a href="<?= $base ?>/recharge" class="flex-1 flex flex-col items-center py-3.5 text-xs gap-1.5 transition-all <?= isActive('/recharge') ? 'text-[#00ff88] font-bold drop-shadow-[0_0_8px_rgba(0,255,136,0.3)]' : 'text-gray-500 hover:text-gray-300' ?>">
    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:22px;height:22px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
    <span class="text-[10px] tracking-wide">Recharger</span>
  </a>
  <a href="<?= $base ?>/profile" class="flex-1 flex flex-col items-center py-3.5 text-xs gap-1.5 transition-all <?= isActive('/profile') ? 'text-[#00ff88] font-bold drop-shadow-[0_0_8px_rgba(0,255,136,0.3)]' : 'text-gray-500 hover:text-gray-300' ?>">
    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:22px;height:22px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
    <span class="text-[10px] tracking-wide">Profil</span>
  </a>
  <?php if (Auth::isAdmin()): ?>
  <a href="<?= $base ?>/admin" class="flex-1 flex flex-col items-center py-3.5 text-xs gap-1.5 transition-all <?= isActive('/admin') ? 'text-[#00ff88] font-bold drop-shadow-[0_0_8px_rgba(0,255,136,0.3)]' : 'text-gray-500 hover:text-gray-300' ?>">
    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:22px;height:22px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
    <span class="text-[10px] tracking-wide">Admin</span>
  </a>
  <?php endif; ?>
</nav>

</body>
</html>
