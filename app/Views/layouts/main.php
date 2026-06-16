<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= App\Core\Auth::csrfToken() ?>">
  <title><?= htmlspecialchars($pageTitle ?? APP_NAME) ?> — KivuBoost</title>
  <meta name="description" content="KivuBoost — Panel SMM professionnel de référence en RDC. Boostez vos réseaux sociaux rapidement.">
  <link rel="icon" type="image/jpeg" href="<?= APP_BASE ?>/assets/logo.jpeg">
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


  <!-- Sidebar (Desktop uniquement) -->
  <aside id="sidebar-drawer" class="hidden md:flex fixed top-0 left-0 h-full w-64 border-r border-[#1a2332] z-50 flex-col" style="background:#0a0f1a">
    <!-- Logo -->
    <div class="px-6 py-6 border-b border-[#1a2332]">
      <a href="<?= $base ?>/dashboard" class="flex items-center gap-3">
        <img src="<?= $base ?>/assets/logo.jpeg" alt="KivuBoost" class="w-10 h-10 rounded-full object-cover shadow-[0_0_15px_rgba(0,255,136,0.2)] border border-[#1a2332]">
        <div>
          <div class="font-bold text-white text-base leading-tight">KivuBoost</div>
          <div class="text-[10px] text-gray-500 uppercase tracking-widest">SMM Panel</div>
        </div>
      </a>
    </div>

    <?php if (!Auth::isAdmin()): ?>
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
    <?php endif; ?>

    <!-- Navigation -->
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
      <?php if (!Auth::isAdmin()): ?>
      <a href="<?= $base ?>/dashboard" class="sidebar-item <?= isActive('/dashboard') || $currentPath === $base . '/' ? 'active' : '' ?> flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Propulser
      </a>
      <?php endif; ?>
      <a href="<?= $base ?>/history" class="sidebar-item <?= isActive('/history') ? 'active' : '' ?> flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        Mes Tableaux
      </a>
      <?php if (!Auth::isAdmin()): ?>
      <a href="<?= $base ?>/recharge" class="sidebar-item <?= isActive('/recharge') ? 'active' : '' ?> flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
        Alimenter mon Compte
      </a>
      <a href="<?= $base ?>/rewards" class="sidebar-item <?= isActive('/rewards') ? 'active' : '' ?> flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300">
        <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
        Fidélité & Cashback
      </a>
      <?php endif; ?>
      <a href="<?= $base ?>/services" class="sidebar-item <?= isActive('/services') ? 'active' : '' ?> flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        Grille des Tarifs
      </a>
      <a href="<?= $base ?>/actualites" class="sidebar-item <?= isActive('/actualites') ? 'active' : '' ?> flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300">
        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 12h6"/></svg>
        Actualités
      </a>
      <?php if (!empty(Auth::user()) && !Auth::isAdmin()): ?>
      <a href="<?= $base ?>/api-docs" class="sidebar-item <?= isActive('/api-docs') ? 'active' : '' ?> flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
        Connecteur API
      </a>
      <a href="<?= $base ?>/tickets" class="sidebar-item <?= isActive('/tickets') ? 'active' : '' ?> flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300">
        <svg class="w-4 h-4 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        Support & Tickets
      </a>
      <?php endif; ?>
      <a href="<?= $base ?>/profile" class="sidebar-item <?= isActive('/profile') ? 'active' : '' ?> flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        Profil
      </a>
      
      <?php if (Auth::isAdmin()): ?>
      <div class="pt-4 pb-1">
        <div class="text-[10px] font-semibold text-gray-600 uppercase tracking-widest px-3">Administration</div>
      </div>
      <?php
        $isAdminRegieActive = (isActive('/admin') === 'active')
          && (isActive('/admin/settings') !== 'active')
          && (isActive('/admin/audit') !== 'active')
          && (isActive('/admin/configuration') !== 'active');
      ?>
      <a href="<?= $base ?>/admin" class="sidebar-item <?= $isAdminRegieActive ? 'active' : '' ?> flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
        Régie Admin
      </a>
      <a href="<?= $base ?>/admin/configuration" class="sidebar-item <?= isActive('/admin/configuration') ? 'active' : '' ?> flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300">
        <svg class="w-4 h-4 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 016 0z"/></svg>
        Configuration
      </a>
      <a href="<?= $base ?>/admin/settings" class="sidebar-item <?= isActive('/admin/settings') ? 'active' : '' ?> flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300">
        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
        Paramètres des Marges
      </a>
      <a href="<?= $base ?>/admin/audit" class="sidebar-item <?= isActive('/admin/audit') ? 'active' : '' ?> flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300">
        <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Journal d'Audit
      </a>
      <a href="<?= $base ?>/admin/support" class="sidebar-item <?= isActive('/admin/support') ? 'active' : '' ?> flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300">
        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
        Agents WhatsApp
      </a>
      <?php
        // Badge ticket: count open tickets for admin
        try {
          $pdo = App\Core\Database::getInstance();
          $openTickets = (int)$pdo->query("SELECT COUNT(*) FROM support_tickets WHERE status = 'open'")->fetchColumn();
        } catch (\Throwable) { $openTickets = 0; }
      ?>
      <a href="<?= $base ?>/admin/tickets" class="sidebar-item <?= isActive('/admin/tickets') ? 'active' : '' ?> flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300">
        <svg class="w-4 h-4 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        <span class="flex-1">Tickets de Support</span>
        <?php if ($openTickets > 0): ?>
          <span class="ml-auto text-[9px] font-black px-1.5 py-0.5 rounded-full animate-pulse" style="background:rgba(251,191,36,0.15);color:#fbbf24;border:1px solid rgba(251,191,36,0.3)"><?= $openTickets ?></span>
        <?php endif; ?>
      </a>
      <a href="<?= $base ?>/admin/campaign" class="sidebar-item <?= isActive('/admin/campaign') ? 'active' : '' ?> flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300">
        <svg class="w-4 h-4 text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
        Diffuseur de Campagnes
      </a>
      <a href="<?= $base ?>/admin/pricing-rules" class="sidebar-item <?= isActive('/admin/pricing-rules') ? 'active' : '' ?> flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300">
        <svg class="w-4 h-4 text-violet-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        Regles de Tarification
      </a>
      <a href="<?= $base ?>/admin/financial-report" class="sidebar-item <?= isActive('/admin/financial-report') ? 'active' : '' ?> flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300">
        <svg class="w-4 h-4 text-emerald-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        Rapport Financier
      </a>
      <a href="<?= $base ?>/admin/actualites" class="sidebar-item <?= isActive('/admin/actualites') ? 'active' : '' ?> flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300">
        <svg class="w-4 h-4 text-pink-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Rédiger une Actualité
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
  <main class="flex-1 md:ml-64 flex flex-col min-h-screen w-full overflow-x-hidden">

    <!-- Top bar (Desktop - Admin Only) -->
    <?php if (Auth::isAdmin()): ?>
    <header class="hidden md:flex items-center justify-between px-6 py-3.5 border-b border-[#1a2332] sticky top-0 z-30" style="background:#0a0f1a;">
      <div>
        <h2 class="text-sm font-bold text-gray-400 uppercase tracking-wider"><?= $pageTitle ?? 'KivuBoost Administration' ?></h2>
      </div>
      <div class="flex items-center gap-4">
        <!-- Profil Admin -->
        <a href="<?= $base ?>/profile" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg hover:bg-white/[0.02] border border-transparent hover:border-[#1a2332] transition-all">
          <?php if (!empty($currentUser['avatar']) && file_exists(ROOT_PATH . '/public/uploads/avatars/' . $currentUser['avatar'])): ?>
            <img src="<?= $base ?>/public/uploads/avatars/<?= htmlspecialchars($currentUser['avatar']) ?>" class="w-7 h-7 rounded-full object-cover border border-[#00ff88]">
          <?php else: ?>
            <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-black uppercase" style="background:#00ff88">
              <?= strtoupper(substr($currentUser['username'] ?? 'A', 0, 1)) ?>
            </div>
          <?php endif; ?>
          <div class="text-left">
            <div class="text-xs font-semibold text-white leading-none"><?= htmlspecialchars($currentUser['username'] ?? '') ?></div>
            <div class="text-[9px] text-gray-500 capitalize mt-0.5"><?= htmlspecialchars($currentUser['role'] ?? 'admin') ?></div>
          </div>
        </a>

        <!-- Bouton Déconnexion -->
        <a href="<?= $base ?>/logout" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs text-gray-500 hover:text-red-400 hover:bg-red-400/5 border border-transparent hover:border-red-500/20 transition-all" title="Déconnexion">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
          </svg>
          <span>Déconnexion</span>
        </a>
      </div>
    </header>
    <?php endif; ?>

    <!-- Top bar (Mobile) -->
    <header class="md:hidden flex items-center justify-between px-4 py-3 border-b border-[#1a2332] sticky top-0 z-30" style="background:#0a0f1a">
      <div class="flex items-center gap-3">
        <!-- Logo -->
        <a href="<?= $base ?>/dashboard" class="flex items-center gap-2">
          <img src="<?= $base ?>/assets/logo.jpeg" alt="KivuBoost" class="w-8 h-8 rounded-full object-cover shadow-[0_0_10px_rgba(0,255,136,0.2)] border border-[#1a2332]">
          <span class="font-bold text-white text-sm">KivuBoost</span>
        </a>
      </div>
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

        <!-- Bouton Déconnexion Mobile -->
        <a href="<?= $base ?>/logout" class="p-1 text-gray-400 hover:text-red-400 transition-colors" title="Déconnexion">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
          </svg>
        </a>
      </div>
    </header>

    <!-- Toast Container (Top-right on Mobile, Bottom-right on Desktop) -->
    <div id="toast-container" class="fixed top-4 left-4 right-4 md:top-auto md:left-auto md:bottom-6 md:right-6 z-[9999] flex flex-col gap-3 max-w-sm pointer-events-none">
      <?php if ($flash): ?>
        <div class="toast-item pointer-events-auto flex items-start gap-3 px-4 py-3.5 rounded-xl border shadow-2xl transition-all duration-300 translate-y-2 opacity-0 scale-95"
             style="background:rgba(13,17,23,0.95); backdrop-filter:blur(12px);"
             data-type="<?= htmlspecialchars($flash['type']) ?>"
             data-message="<?= htmlspecialchars($flash['message']) ?>">
          
          <!-- Icon -->
          <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0 mt-0.5">
            <?php if ($flash['type'] === 'success'): ?>
              <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <?php else: ?>
              <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <?php endif; ?>
          </div>

          <!-- Message -->
          <div class="flex-1 text-xs font-semibold leading-normal text-white">
            <?= htmlspecialchars($flash['message']) ?>
          </div>

          <!-- Close Button -->
          <button onclick="dismissToast(this.closest('.toast-item'))" class="text-gray-500 hover:text-gray-300 transition-colors shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
      <?php endif; ?>
    </div>

    <!-- Page Content -->
    <div class="flex-1 px-4 py-4 md:px-6 md:py-6 pb-24 md:pb-6 w-full">
      <?= $content ?>
    </div>

    <!-- Footer -->
    <footer class="mt-auto py-6 border-t border-[#1a2332] text-center" style="background:#0a0f1a">
      <p class="text-[10px] sm:text-xs text-gray-500 font-medium">
        &copy; <?= date('Y') ?> KivuBoost. Tous droits réservés.
      </p>
    </footer>
  </main>
</div>

  <!-- Bottom Navigation Bar (Mobile Only) -->
  <?php include __DIR__ . '/bottom_nav.php'; ?>

  <!-- Support Hub component (WhatsApp, Facebook, Instagram) -->
  <?php include __DIR__ . '/support_hub.php'; ?>

  <!-- Toast Notification Logic -->
  <script>
  function showToast(type, message) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = 'toast-item pointer-events-auto flex items-start gap-3 px-4 py-3.5 rounded-xl border shadow-2xl transition-all duration-300 translate-y-2 opacity-0 scale-95';
    toast.style.background = 'rgba(13,17,23,0.95)';
    toast.style.backdropFilter = 'blur(12px)';
    
    const isSuccess = type === 'success';
    toast.style.borderColor = isSuccess ? 'rgba(16,185,129,0.3)' : 'rgba(239,68,68,0.3)';
    
    const icon = isSuccess 
      ? `<svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`
      : `<svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`;

    toast.innerHTML = `
      <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0 mt-0.5">
        ${icon}
      </div>
      <div class="flex-1 text-xs font-semibold leading-normal text-white font-sans">
        ${escapeHtmlForToast(message)}
      </div>
      <button onclick="dismissToast(this.closest('.toast-item'))" class="text-gray-500 hover:text-gray-300 transition-colors shrink-0">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    `;

    container.appendChild(toast);

    setTimeout(() => {
      toast.classList.remove('translate-y-2', 'opacity-0', 'scale-95');
      toast.classList.add('translate-y-0', 'opacity-100', 'scale-100');
    }, 10);

    setTimeout(() => {
      dismissToast(toast);
    }, 5000);
  }

  function dismissToast(toast) {
    if (!toast) return;
    toast.classList.remove('translate-y-0', 'opacity-100', 'scale-100');
    toast.classList.add('translate-y-2', 'opacity-0', 'scale-95');
    setTimeout(() => {
      toast.remove();
    }, 300);
  }

  function escapeHtmlForToast(str) {
    return str.replace(/[&<>'"]/g, 
      tag => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        "'": '&#39;',
        '"': '&quot;'
      }[tag] || tag)
    );
  }

  document.addEventListener('DOMContentLoaded', () => {
    const staticToast = document.querySelector('.toast-item[data-message]');
    if (staticToast) {
      const type = staticToast.dataset.type;
      staticToast.style.borderColor = type === 'success' ? 'rgba(16,185,129,0.3)' : 'rgba(239,68,68,0.3)';
      setTimeout(() => {
        staticToast.classList.remove('translate-y-2', 'opacity-0', 'scale-95');
        staticToast.classList.add('translate-y-0', 'opacity-100', 'scale-100');
      }, 100);
      setTimeout(() => {
        dismissToast(staticToast);
      }, 5500);
    }
  });
  </script>

</body>
</html>
