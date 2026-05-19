<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Connexion') ?> — BukavuBoost</title>
  <meta name="description" content="BukavuBoost — Connexion au panel SMM de Bukavu, RDC.">
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
  <!-- Logo -->
  <div class="text-center mb-8">
    <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl mb-4"
         style="background:linear-gradient(135deg,#00ff88,#00d4ff);box-shadow:0 0 30px rgba(0,255,136,0.3)">
      <svg class="w-7 h-7 text-black" fill="currentColor" viewBox="0 0 20 20">
        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
      </svg>
    </div>
    <h1 class="text-2xl font-bold text-white tracking-tight">BukavuBoost</h1>
    <p class="text-gray-500 text-sm mt-1">Panel SMM Professionnel — Bukavu, RDC</p>
  </div>

  <!-- Flash Message -->
  <?php if ($flash): ?>
  <div class="mb-4 px-4 py-3 rounded-xl text-sm
    <?= $flash['type'] === 'success'
        ? 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-400'
        : 'bg-red-500/10 border border-red-500/30 text-red-400' ?>">
    <?= htmlspecialchars($flash['message']) ?>
  </div>
  <?php endif; ?>

  <!-- Carte principale -->
  <div class="glass-card rounded-2xl p-8">
    <?= $content ?>
  </div>

  <!-- Footer -->
  <p class="text-center text-gray-600 text-xs mt-6">
    © <?= date('Y') ?> BukavuBoost · Bukavu, RDC · Tous droits réservés
  </p>
</div>

</body>
</html>
