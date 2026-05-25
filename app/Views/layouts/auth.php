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
    © <?= date('Y') ?> KivuBoost · Tous droits réservés
  </p>
</div>

</body>
</html>
