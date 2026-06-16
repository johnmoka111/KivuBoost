<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Connexion') ?> — KivuBoost</title>
  <meta name="description" content="KivuBoost — Connexion au panel SMM professionnel en RDC.">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: { extend: { fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] } } }
    }
  </script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    body { background: #050811; }
    .auth-bg {
      background: #050811;
      background-image:
        radial-gradient(ellipse 80% 50% at 50% -20%, rgba(0,255,136,0.08) 0%, transparent 60%),
        radial-gradient(ellipse 60% 40% at 80% 80%, rgba(0,212,255,0.05) 0%, transparent 50%);
    }
    .grid-lines {
      background-image:
        linear-gradient(rgba(0,255,136,0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0,255,136,0.03) 1px, transparent 1px);
      background-size: 40px 40px;
    }
    .glass-card {
      background: rgba(13,17,23,0.85);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(0,255,136,0.12);
      box-shadow: 0 0 40px rgba(0,255,136,0.05), 0 25px 50px rgba(0,0,0,0.5);
    }
    .input-field {
      background: #0a0f1a;
      border: 1px solid #1a2332;
      color: #e2e8f0;
      transition: border-color .2s, box-shadow .2s;
    }
    .input-field:focus {
      outline: none;
      border-color: rgba(0,255,136,0.5);
      box-shadow: 0 0 0 3px rgba(0,255,136,0.08);
    }
    .btn-primary {
      background: linear-gradient(135deg, #00ff88, #00c466);
      color: #050811;
      font-weight: 700;
      transition: all .2s;
      box-shadow: 0 4px 15px rgba(0,255,136,0.2);
    }
    .btn-primary:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 25px rgba(0,255,136,0.35);
    }
    .btn-primary:active { transform: translateY(0); }
  </style>
</head>
<body class="font-sans min-h-screen auth-bg grid-lines flex items-center justify-center p-4">

<?php
use App\Core\Controller;
$flash = Controller::getFlash();
?>

<div class="w-full max-w-md">

  <!-- Carte principale -->
  <div class="glass-card rounded-2xl p-8">
    <?= $content ?>
  </div>

  <!-- Footer -->
  <p class="text-center text-gray-600 text-xs mt-6">
    © <?= date('Y') ?> KivuBoost · Tous droits réservés
  </p>
</div>

<!-- Toast container (top of screen on auth pages) -->
<div id="toast-container" class="fixed top-4 left-4 right-4 z-[9999] flex flex-col gap-3 max-w-sm mx-auto pointer-events-none">
  <?php if ($flash): ?>
    <div class="toast-item pointer-events-auto flex items-start gap-3 px-4 py-3.5 rounded-xl border shadow-2xl transition-all duration-300 translate-y-2 opacity-0 scale-95"
         style="background:rgba(13,17,23,0.97); backdrop-filter:blur(12px);"
         data-type="<?= htmlspecialchars($flash['type']) ?>"
         data-message="<?= htmlspecialchars($flash['message']) ?>">
      <div class="w-5 h-5 flex items-center justify-center shrink-0 mt-0.5">
        <?php if ($flash['type'] === 'success'): ?>
          <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <?php else: ?>
          <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <?php endif; ?>
      </div>
      <div class="flex-1 text-xs font-semibold leading-normal" style="color:<?= $flash['type'] === 'success' ? '#34d399' : '#f87171' ?>">
        <?= htmlspecialchars($flash['message']) ?>
      </div>
      <button onclick="this.closest('.toast-item').remove()" class="text-gray-500 hover:text-gray-300 transition-colors shrink-0">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
  <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const toast = document.querySelector('.toast-item[data-message]');
  if (toast) {
    const type = toast.dataset.type;
    toast.style.borderColor = type === 'success' ? 'rgba(16,185,129,0.35)' : 'rgba(239,68,68,0.35)';
    setTimeout(() => {
      toast.classList.remove('translate-y-2', 'opacity-0', 'scale-95');
      toast.classList.add('translate-y-0', 'opacity-100', 'scale-100');
    }, 80);
    setTimeout(() => {
      toast.classList.add('translate-y-2', 'opacity-0', 'scale-95');
      setTimeout(() => toast.remove(), 300);
    }, 5500);
  }
});
</script>

</body>
</html>
