<?php
$basePath = rtrim(APP_BASE, '/');

function pubDate(string $d): string {
    $mois = ['','jan','fév','mar','avr','mai','juin','juil','août','sep','oct','nov','déc'];
    $dt = new DateTime($d);
    $j = $dt->format('j');
    $m = (int)$dt->format('n');
    $h = $dt->format('H:i');
    return $j . ' ' . $mois[$m] . ' ' . $dt->format('Y') . ' · ' . $h;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['title']) ?> — KivuBoost</title>
    <meta name="description" content="<?= htmlspecialchars($article['summary']) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        *{font-family:'Inter',sans-serif}body{background:#000}
        .nav-blur{backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px)}
        .article-body{color:#d4d4d8;line-height:1.85}
        .article-body h1,.article-body h2,.article-body h3{color:#fff;font-weight:700;margin:1.5rem 0 .75rem}
        .article-body h1{font-size:1.75rem}.article-body h2{font-size:1.4rem}.article-body h3{font-size:1.15rem}
        .article-body p{margin-bottom:1rem}
        .article-body a{color:#10b981;text-decoration:underline}
        .article-body ul,.article-body ol{padding-left:1.5rem;margin-bottom:1rem}
        .article-body li{margin-bottom:.4rem}
        .article-body blockquote{border-left:3px solid #10b981;padding-left:1rem;color:#71717a;font-style:italic;margin:1.5rem 0}
        .article-body pre{background:#18181b;border:1px solid #27272a;border-radius:.75rem;padding:1rem;overflow-x:auto;margin:1.5rem 0}
        .article-body code{font-family:monospace;background:#18181b;padding:.1rem .3rem;border-radius:.25rem;font-size:.875rem}
        .article-body img{max-width:100%;border-radius:.75rem;margin:1.5rem 0}
    </style>
</head>
<body class="min-h-screen text-white">

<nav class="fixed top-0 left-0 right-0 z-50 nav-blur bg-black/80 border-b border-zinc-800">
    <div class="max-w-4xl mx-auto px-4 h-16 flex items-center justify-between">
        <a href="<?= $basePath ?>/" class="flex items-center gap-2.5">
            <img src="<?= $basePath ?>/assets/logo.jpeg" alt="KivuBoost"
                 class="w-9 h-9 rounded-full object-cover border border-zinc-700">
            <span class="font-bold text-base text-white tracking-tight">KivuBoost</span>
        </a>
        <a href="<?= $basePath ?>/actualites" class="flex items-center gap-1.5 text-zinc-400 hover:text-emerald-400 text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Toutes les actualités
        </a>
    </div>
</nav>

<main class="pt-24 pb-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6">
        <p class="text-zinc-500 text-sm mb-4"><?= pubDate($article['created_at']) ?></p>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-white leading-tight mb-4"><?= htmlspecialchars($article['title']) ?></h1>
        <p class="text-zinc-400 text-lg leading-relaxed mb-8 pb-8 border-b border-zinc-800"><?= htmlspecialchars($article['summary']) ?></p>
        <?php if ($article['image_path']): ?>
            <img src="<?= $basePath . '/public/' . htmlspecialchars($article['image_path']) ?>"
                 alt="<?= htmlspecialchars($article['title']) ?>"
                 class="w-full h-64 sm:h-80 object-cover rounded-2xl mb-8 border border-zinc-800">
        <?php endif; ?>
        <div class="article-body"><?= $article['content'] ?></div>
        <div class="mt-12 pt-8 border-t border-zinc-800">
            <a href="<?= $basePath ?>/actualites"
               class="inline-flex items-center gap-2 px-5 py-2.5 border border-zinc-700 text-zinc-300 text-sm font-medium rounded-xl hover:border-emerald-500 hover:text-emerald-400 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Retour aux actualités
            </a>
        </div>
    </div>
</main>

<!-- Support Hub component (WhatsApp, Facebook, Instagram) -->
<?php include VIEW_PATH . '/layouts/support_hub.php'; ?>

</body>
</html>
