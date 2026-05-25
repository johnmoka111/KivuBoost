<?php
$basePath = rtrim(APP_BASE, '/');

function agentAvatar(string $basePath, ?string $path): string {
    if ($path && file_exists(__DIR__ . '/../../../public/' . $path)) {
        return $basePath . '/public/' . htmlspecialchars($path, ENT_QUOTES, 'UTF-8');
    }
    return 'https://ui-avatars.com/api/?background=0d1117&color=00ff88&bold=true&size=128&name=' . urlencode('?');
}

function agentAvatarNamed(string $basePath, ?string $path, string $name): string {
    if ($path && file_exists(__DIR__ . '/../../../public/' . $path)) {
        return $basePath . '/public/' . htmlspecialchars($path, ENT_QUOTES, 'UTF-8');
    }
    return 'https://ui-avatars.com/api/?background=0d1117&color=00ff88&bold=true&size=128&name=' . urlencode($name);
}

$waMain = htmlspecialchars($mainWhatsapp ?? '', ENT_QUOTES, 'UTF-8');
$fbUrl  = htmlspecialchars($facebookUrl  ?? '#', ENT_QUOTES, 'UTF-8');
$igUrl  = htmlspecialchars($instagramUrl ?? '#', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Équipe Support — KivuBoost</title>
  <meta name="description" content="Découvrez l'équipe d'assistance locale de KivuBoost. Nos agents sont disponibles sur WhatsApp pour vous accompagner.">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    * { box-sizing: border-box; }
    body { background: #000000; color: #fff; font-family: 'Inter', system-ui, sans-serif; }
    .card-hover { transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease; }
    .card-hover:hover { transform: translateY(-4px); border-color: #06b6d4; box-shadow: 0 8px 32px rgba(6,182,212,0.08); }
    .nav-blur { backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); }
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
  </style>
</head>
<body class="antialiased">

<!-- NAV -->
<nav class="nav-blur fixed top-0 left-0 right-0 z-50 bg-black/85 border-b border-zinc-900">
  <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between gap-4">
    <a href="<?= $basePath ?>/" class="flex items-center gap-2.5 shrink-0">
      <img src="<?= $basePath ?>/assets/logo.jpeg" alt="KivuBoost"
           class="h-9 w-9 rounded-full object-cover border border-zinc-800">
      <span class="font-extrabold text-base text-white tracking-tight">KivuBoost</span>
    </a>
    <div class="flex items-center gap-3">
      <a href="<?= $basePath ?>/" class="hidden sm:inline-flex items-center gap-1 text-zinc-400 hover:text-white text-sm font-medium transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Accueil
      </a>
      <a href="<?= $basePath ?>/register"
         class="px-4 py-2 bg-emerald-500 hover:bg-emerald-400 text-black font-bold text-sm rounded-xl transition-all hover:scale-[1.02]">
        Créer un compte
      </a>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="pt-28 pb-12 text-center px-4">
  <div class="inline-flex items-center gap-2 mb-4 px-3 py-1.5 rounded-full border border-cyan-500/30 bg-cyan-500/10">
    <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
    <span class="text-xs font-bold text-cyan-400 uppercase tracking-wider">Support Local & Disponible</span>
  </div>
  <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-none mt-2">
    Notre Équipe <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-cyan-400">de Support</span>
  </h1>
  <p class="mt-4 text-zinc-400 text-base sm:text-lg max-w-xl mx-auto leading-relaxed">
    Des agents locaux au Kivu et en RDC, disponibles pour vous aider directement sur WhatsApp.
  </p>
  <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
    <?php if ($waMain): ?>
    <a href="https://wa.me/<?= $waMain ?>" target="_blank" rel="noopener"
       class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-black font-bold text-sm rounded-xl transition-all hover:scale-[1.02] shadow-[0_4px_16px_rgba(16,185,129,0.2)]">
      <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.458L0 24zm5.824-3.414c1.657.984 3.284 1.503 4.908 1.504 5.485.002 9.948-4.457 9.951-9.94.002-2.656-1.031-5.152-2.905-7.028-1.875-1.877-4.371-2.91-7.006-2.91-5.48 0-9.94 4.457-9.943 9.942-.001 1.761.47 3.427 1.365 4.973l-1.011 3.693 3.769-.989z"/></svg>
      Contacter le support principal
    </a>
    <?php endif; ?>
    <?php if ($fbUrl && $fbUrl !== '#'): ?>
    <a href="<?= $fbUrl ?>" target="_blank" rel="noopener"
       class="inline-flex items-center gap-2 px-5 py-2.5 border border-zinc-800 bg-zinc-950 hover:border-cyan-600 text-zinc-300 hover:text-white font-semibold text-sm rounded-xl transition-colors">
      <svg class="w-4 h-4 text-cyan-400" fill="currentColor" viewBox="0 0 24 24"><path d="M9 8H7v3h2v9h4v-9h3.615L17 8h-4V6.157C13 5.374 13.5 5 14.143 5H17V0h-4c-3.26 0-5 1.83-5 4.857V8z"/></svg>
      Facebook
    </a>
    <?php endif; ?>
    <?php if ($igUrl && $igUrl !== '#'): ?>
    <a href="<?= $igUrl ?>" target="_blank" rel="noopener"
       class="inline-flex items-center gap-2 px-5 py-2.5 border border-zinc-800 bg-zinc-950 hover:border-pink-500 text-zinc-300 hover:text-white font-semibold text-sm rounded-xl transition-colors">
      <svg class="w-4 h-4 text-pink-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
      Instagram
    </a>
    <?php endif; ?>
  </div>
</section>

<!-- AGENTS GRID -->
<section class="max-w-7xl mx-auto px-4 pb-24">
  <?php if (empty($agents)): ?>
  <div class="flex flex-col items-center justify-center py-24 text-center">
    <div class="w-16 h-16 rounded-2xl bg-zinc-950 border border-zinc-900 flex items-center justify-center mb-4">
      <svg class="w-8 h-8 text-zinc-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
      </svg>
    </div>
    <p class="text-zinc-400 font-semibold">Aucun agent disponible pour le moment.</p>
    <p class="text-zinc-600 text-sm mt-1">Revenez bientôt !</p>
  </div>
  <?php else: ?>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
    <?php foreach ($agents as $agent): ?>
    <?php
      $wa      = htmlspecialchars($agent['whatsapp_number'], ENT_QUOTES, 'UTF-8');
      $aName   = htmlspecialchars($agent['name'], ENT_QUOTES, 'UTF-8');
      $aCity   = htmlspecialchars($agent['city'], ENT_QUOTES, 'UTF-8');
      $avatar  = agentAvatarNamed($basePath, $agent['photo_path'], $agent['name']);
    ?>
    <div class="card-hover group flex flex-col items-center text-center
                bg-zinc-950 border border-zinc-900 rounded-3xl p-6 sm:p-7">
      <!-- Avatar -->
      <div class="relative mb-5">
        <img src="<?= $avatar ?>"
             alt="Photo de <?= $aName ?>"
             class="w-24 h-24 rounded-full object-cover border-2 border-cyan-500/50 group-hover:border-cyan-400 transition-colors">
        <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-emerald-400 border-2 border-black
                     flex items-center justify-center" title="En ligne"></span>
      </div>
      <!-- Info -->
      <h3 class="text-base font-extrabold text-white leading-tight"><?= $aName ?></h3>
      <div class="flex items-center gap-1.5 mt-1.5 mb-5">
        <svg class="w-3.5 h-3.5 text-zinc-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <span class="text-xs text-zinc-500 font-medium"><?= $aCity ?></span>
      </div>
      <!-- CTA WhatsApp -->
      <a href="https://wa.me/<?= $wa ?>"
         target="_blank" rel="noopener noreferrer"
         class="w-full flex items-center justify-center gap-2
                py-3.5 bg-emerald-500 hover:bg-emerald-400 active:scale-95
                text-black font-bold text-sm rounded-2xl
                transition-all duration-200
                shadow-[0_4px_16px_rgba(16,185,129,0.15)] hover:shadow-[0_4px_20px_rgba(16,185,129,0.25)]">
        <svg class="w-4.5 h-4.5 shrink-0" style="width:18px;height:18px" fill="currentColor" viewBox="0 0 24 24">
          <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.458L0 24z"/>
        </svg>
        Discuter sur WhatsApp
      </a>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>

<!-- Floating hub -->
<?php include VIEW_PATH . '/layouts/support_hub.php'; ?>

</body>
</html>
