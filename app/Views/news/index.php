<?php
$basePath = rtrim(APP_BASE, '/');

function newsImg(string $base, ?string $path): string {
    if ($path) return $base . '/public/' . ltrim($path, '/');
    return 'https://placehold.co/800x450/18181b/10b981?text=KivuBoost&font=inter';
}

function pubDate(string $d): string {
    $mois = ['','jan','fév','mar','avr','mai','juin','juil','août','sep','oct','nov','déc'];
    $dt = new DateTime($d);
    $j = $dt->format('j');
    $m = (int)$dt->format('n');
    $h = $dt->format('H:i');
    return $j . ' ' . $mois[$m] . ' ' . $dt->format('Y') . ' · ' . $h;
}

// Détection de la page active pour le routage de l'affichage
$currentUri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$path = '/' . ltrim(str_replace($basePath, '', $currentUri), '/');
$isHome = ($path === '/' || $path === '');
$isNews = ($path === '/actualites' || str_starts_with($path, '/actualites/'));

// Récupération et fusion de toutes les actualités pour l'affichage en stories
$allArticles = [];
if (isset($featured) && $featured) {
    $allArticles[] = $featured;
}
if (!empty($rest)) {
    $allArticles = array_merge($allArticles, $rest);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= $isNews ? 'Actualités' : 'Accueil' ?> — KivuBoost</title>
    <meta name="description" content="Découvrez les dernières actualités et nouveautés de KivuBoost, la plateforme SMM de référence à Bukavu.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #000; }
        .nav-blur { backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); }
        .card-hover { transition: transform .2s, box-shadow .2s; }
        .card-hover:hover { transform: translateY(-3px); box-shadow: 0 16px 32px rgba(0,0,0,.5); }
        .img-scale img { transition: transform .35s; }
        .img-scale:hover img { transform: scale(1.04); }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-thumb { background: #10b981; border-radius: 2px; }
        .bottom-nav-link { transition: all 0.2s ease; }
        .bottom-nav-link.active { color: #10b981; }

        /* Custom scrollbar horizontal ultra-fine pour les stories */
        .scrollbar-thin::-webkit-scrollbar {
            height: 3px;
        }
        .scrollbar-thin::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.02);
        }
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: rgba(16, 185, 129, 0.25);
            border-radius: 99px;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: rgba(16, 185, 129, 0.50);
        }
    </style>
</head>
<body class="min-h-screen text-white">

<!-- NAVBAR -->
<nav class="fixed top-0 left-0 right-0 z-50 nav-blur bg-black/85 border-b border-zinc-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
        <a href="<?= $basePath ?>/" class="flex items-center gap-2.5">
            <img src="<?= $basePath ?>/assets/logo.jpeg"
                 alt="KivuBoost"
                 class="w-9 h-9 rounded-full object-cover border border-zinc-700">
            <span class="font-bold text-base text-white tracking-tight">KivuBoost</span>
        </a>

        <div class="hidden sm:flex items-center gap-4">
            <a href="<?= $basePath ?>/support"
               class="px-4 py-2 text-sm font-medium text-zinc-400 hover:text-emerald-400 transition-colors">
                Équipe Support
            </a>
            <a href="<?= $basePath ?>/login"
               class="px-4 py-2 text-sm font-semibold text-zinc-400 hover:text-white transition-colors">
                Se connecter
            </a>
            <a href="<?= $basePath ?>/register"
               class="px-5 py-2 text-sm font-bold bg-emerald-500 text-black rounded-xl hover:bg-emerald-400 transition-all hover:scale-[1.02] shadow-[0_4px_20px_rgba(16,185,129,0.15)]">
                Créer un compte
            </a>
        </div>

        <a href="<?= $basePath ?>/login"
           class="sm:hidden px-3 py-1.5 text-sm font-medium text-zinc-300 border border-zinc-700 rounded-lg">
            Connexion
        </a>
    </div>
</nav>

<!-- MAIN avec padding-bottom augmenté pour la bottom nav mobile -->
<main class="pt-20 lg:pt-28 pb-24 sm:pb-8">

    <?php if ($isHome): ?>
        <!-- ============================================================ -->
        <!-- SECTION STORIES HORIZONTALES (Exclusivité Page d'Accueil)   -->
        <!-- ============================================================ -->
        <?php if (!empty($allArticles)): ?>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 mb-8 mt-2">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span class="text-[10px] sm:text-xs font-bold tracking-wider text-emerald-400 uppercase">Flash Actus &amp; Stories</span>
                    </div>
                    <!-- Bouton tout voir -->
                    <a href="<?= $basePath ?>/actualites" class="text-zinc-500 hover:text-emerald-400 text-[10px] sm:text-xs font-semibold flex items-center gap-1 transition-colors">
                        Tout voir
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                <!-- Story Horizontal Container (Edge-to-Edge on Mobile) -->
                <div class="flex items-center gap-4 overflow-x-auto pb-3 pt-1 scrollbar-thin -mx-4 px-4 sm:mx-0 sm:px-0">
                    <?php foreach ($allArticles as $story): ?>
                        <a href="<?= $basePath ?>/actualites/<?= htmlspecialchars($story['slug']) ?>" 
                           class="group flex flex-col items-center shrink-0 focus:outline-none w-16 sm:w-20">
                            <!-- Story bubble ring with cyber gradient glow -->
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full p-[2px] bg-gradient-to-tr from-emerald-400 via-cyan-400 to-indigo-600 active:scale-95 transition-all duration-300 shadow-[0_0_12px_rgba(16,185,129,0.15)] group-hover:shadow-[0_0_18px_rgba(6,182,212,0.4)] group-hover:scale-105">
                                <div class="w-full h-full rounded-full bg-black p-[2px]">
                                    <img src="<?= newsImg($basePath, $story['image_path']) ?>" 
                                         alt="<?= htmlspecialchars($story['title']) ?>" 
                                         class="w-full h-full object-cover rounded-full">
                                </div>
                            </div>
                            <!-- Story Title -->
                            <span class="text-[9px] sm:text-[10px] text-zinc-400 text-center line-clamp-1 w-full font-medium mt-1.5 group-hover:text-emerald-400 transition-colors">
                                <?= htmlspecialchars($story['title']) ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- HERO SECTION -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-4 pb-12 text-center lg:text-left lg:flex lg:items-center lg:justify-between lg:gap-12">
            <div class="lg:w-[55%]">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white leading-none">
                    Boostez votre <br class="hidden lg:block">
                    visibilité avec <span class="text-emerald-400">KivuBoost</span>
                </h1>
                <p class="mt-4 text-zinc-400 text-sm sm:text-base lg:text-lg max-w-xl mx-auto lg:mx-0 leading-relaxed">
                    La plateforme SMM leader et de confiance au Kivu. Propulsez vos réseaux sociaux grâce à nos technologies automatisées, notre rapidité d'exécution et nos tarifs directs.
                </p>
            </div>
            <div class="hidden lg:block lg:w-[45%]">
                <div class="relative w-full aspect-[4/3] rounded-2xl overflow-hidden border border-zinc-800/80 shadow-[0_0_50px_rgba(16,185,129,0.05)]">
                    <img src="<?= $basePath ?>/assets/logo.jpeg" alt="KivuBoost Logo" class="w-full h-full object-cover opacity-90">
                </div>
            </div>
        </div>

        <!-- SECTION HISTOIRE & SOUTIEN -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12 border-t border-zinc-900/60">
            <div class="bg-zinc-950/40 border border-zinc-900 rounded-3xl p-6 sm:p-10 lg:p-12 relative overflow-hidden">
                <div class="absolute -right-16 -top-16 w-64 h-64 bg-emerald-500/5 rounded-full blur-3xl"></div>
                <div class="relative z-10 max-w-3xl">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-pulse"></span>
                        <span class="text-[10px] font-bold tracking-wider text-cyan-400 uppercase">Propulsé par l'excellence</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Notre Histoire &amp; Soutien Institutionnel</h2>
                    <p class="mt-4 text-zinc-400 text-sm sm:text-base leading-relaxed">
                        KivuBoost est né de la volonté de simplifier et de démocratiser l'accès au marketing numérique pour tous les entrepreneurs, créateurs de contenus et entreprises de la région du Kivu et de toute la RDC. 
                    </p>
                    <p class="mt-3 text-zinc-400 text-sm sm:text-base leading-relaxed">
                        Aujourd'hui, nous sommes fiers d'être soutenus, propulsés et encadrés par <strong>TAL Communities</strong>, l'entreprise pionnière engagée dans l'émergence technologique et l'impact communautaire au Kivu. Ce partenariat solide nous permet de garantir une stabilité opérationnelle inégalée, une conformité totale et un service client de premier ordre.
                    </p>
                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        <span class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Partenaire Officiel :</span>
                        <span class="text-xs font-extrabold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-3 py-1.5 rounded-lg">TAL COMMUNITIES</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION POURQUOI NOUS CHOISIR -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12 border-t border-zinc-900/60">
            <div class="text-center lg:text-left mb-10">
                <p class="text-zinc-500 text-xs sm:text-sm mb-2 uppercase tracking-wider font-semibold">Innovation &amp; Motivations</p>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Pourquoi Choisir KivuBoost ?</h2>
                <p class="mt-2 text-zinc-400 text-sm max-w-xl">
                    Nous combinons expertise locale et technologie globale pour vous offrir le meilleur service possible.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- USD/CDF multicurrency -->
                <div class="bg-zinc-950/20 border border-zinc-900 rounded-2xl p-6 hover:border-zinc-800 transition-colors">
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Multi-devises USD/CDF</h3>
                    <p class="text-zinc-400 text-xs leading-relaxed">
                        Fini les tracas de conversion. Alimentez votre compte et payez en toute transparence en Dollars Américains (USD) ou en Francs Congolais (CDF).
                    </p>
                </div>

                <!-- Connecteur API -->
                <div class="bg-zinc-950/20 border border-zinc-900 rounded-2xl p-6 hover:border-zinc-800 transition-colors">
                    <div class="w-12 h-12 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">API pour Développeurs</h3>
                    <p class="text-zinc-400 text-xs leading-relaxed">
                        Intégrez nos services directement dans vos propres applications ou sites web grâce à notre documentation claire et notre connecteur API dédié.
                    </p>
                </div>

                <!-- Refill en un clic -->
                <div class="bg-zinc-950/20 border border-zinc-900 rounded-2xl p-6 hover:border-zinc-800 transition-colors">
                    <div class="w-12 h-12 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89H17a1 1 0 011-1H3" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Garantie "Refill"</h3>
                    <p class="text-zinc-400 text-xs leading-relaxed">
                        Bénéficiez d'une tranquillité d'esprit absolue grâce à notre système de recharge "Refill" en un seul clic en cas de perte de followers ou d'abonnés.
                    </p>
                </div>

                <!-- Interface ultra-rapide -->
                <div class="bg-zinc-950/20 border border-zinc-900 rounded-2xl p-6 hover:border-zinc-800 transition-colors">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Interface Ultra-Rapide</h3>
                    <p class="text-zinc-400 text-xs leading-relaxed">
                        Commandez et gérez vos boosts en quelques secondes. Notre application est optimisée pour une vitesse d'exécution maximale même sur mobile.
                    </p>
                </div>
            </div>
        </div>

    <?php elseif ($isNews): ?>
        <!-- ============================================================ -->
        <!-- SECTION ACTUALITÉS (Uniquement sur la page Dédiée /actualites) -->
        <!-- ============================================================ -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-4 pb-8 lg:pb-12">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <p class="text-zinc-500 text-xs sm:text-sm uppercase tracking-wider font-semibold">Actualités &amp; Nouveautés</p>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight leading-tight">
                Ce qui se passe sur <span class="text-emerald-400">KivuBoost</span>
            </h2>
            <p class="text-zinc-400 text-xs sm:text-sm mt-1">Restez au courant de toutes les nouveautés de notre plateforme.</p>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6">

            <?php if (!$featured): ?>
                <div class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-zinc-900 border border-zinc-800 flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 12h6"/>
                        </svg>
                    </div>
                    <p class="text-zinc-400 font-medium">Aucune actualité publiée pour le moment.</p>
                    <p class="text-zinc-600 text-sm mt-1">Revenez bientôt !</p>
                </div>

            <?php else: ?>

                <!-- À LA UNE -->
                <article class="group mb-12 bg-zinc-900 border border-zinc-800/80 rounded-2xl overflow-hidden card-hover lg:h-[480px]">
                    <div class="flex flex-col lg:flex-row h-full">
                        <a href="<?= $basePath ?>/actualites/<?= htmlspecialchars($featured['slug']) ?>"
                           class="img-scale block lg:w-[55%] h-60 sm:h-72 lg:h-full overflow-hidden shrink-0">
                            <img src="<?= newsImg($basePath, $featured['image_path']) ?>"
                                 alt="<?= htmlspecialchars($featured['title']) ?>"
                                 class="w-full h-full object-cover">
                        </a>
                        <div class="lg:w-[45%] p-6 sm:p-8 lg:p-10 flex flex-col justify-center h-full">
                            <div class="flex items-center gap-2.5 mb-4">
                                <span class="text-[10px] font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-1 rounded-full uppercase tracking-wider">
                                    À LA UNE
                                </span>
                                <span class="text-zinc-500 text-xs font-medium"><?= pubDate($featured['created_at']) ?></span>
                            </div>
                            <h2 class="text-2xl sm:text-3xl lg:text-3xl font-extrabold text-white leading-snug mb-3 group-hover:text-emerald-400 transition-colors">
                                <a href="<?= $basePath ?>/actualites/<?= htmlspecialchars($featured['slug']) ?>">
                                    <?= htmlspecialchars($featured['title']) ?>
                                </a>
                            </h2>
                            <p class="text-zinc-400 text-sm leading-relaxed mb-6 line-clamp-3">
                                <?= htmlspecialchars($featured['summary']) ?>
                            </p>
                            <a href="<?= $basePath ?>/actualites/<?= htmlspecialchars($featured['slug']) ?>"
                               class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-500 text-black text-sm font-bold rounded-xl hover:bg-emerald-400 transition-colors w-fit shadow-[0_4px_20px_rgba(16,185,129,0.15)]">
                                Lire la suite
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </article>

                <!-- GRILLE SECONDAIRE -->
                <?php if (!empty($rest)): ?>
                    <h2 class="text-base font-bold text-zinc-400 mb-4 uppercase tracking-wider text-xs">Autres actualités</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        <?php foreach ($rest as $a): ?>
                            <article class="group bg-zinc-900 border border-zinc-800 rounded-2xl overflow-hidden card-hover flex flex-col">
                                <a href="<?= $basePath ?>/actualites/<?= htmlspecialchars($a['slug']) ?>"
                                   class="img-scale block overflow-hidden">
                                    <img src="<?= newsImg($basePath, $a['image_path']) ?>"
                                         alt="<?= htmlspecialchars($a['title']) ?>"
                                         class="w-full h-44 object-cover">
                                </a>
                                <div class="p-5 flex flex-col flex-1">
                                    <span class="text-zinc-600 text-xs mb-2"><?= pubDate($a['created_at']) ?></span>
                                    <h3 class="text-white font-bold text-sm leading-snug mb-2 group-hover:text-emerald-400 transition-colors line-clamp-2">
                                        <a href="<?= $basePath ?>/actualites/<?= htmlspecialchars($a['slug']) ?>">
                                            <?= htmlspecialchars($a['title']) ?>
                                        </a>
                                    </h3>
                                    <p class="text-zinc-500 text-sm leading-relaxed line-clamp-2 flex-1">
                                        <?= htmlspecialchars($a['summary']) ?>
                                    </p>
                                    <a href="<?= $basePath ?>/actualites/<?= htmlspecialchars($a['slug']) ?>"
                                       class="mt-4 inline-flex items-center gap-1.5 text-emerald-400 text-sm font-semibold hover:text-emerald-300 transition-colors">
                                        Lire la suite
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>

<!-- BOTTOM NAVIGATION BAR (mobile uniquement) -->
<nav class="sm:hidden fixed bottom-0 left-0 right-0 z-50 bg-black/95 backdrop-blur-md border-t border-zinc-800 py-2 px-4 safe-area-pb">
    <div class="flex items-center justify-around max-w-md mx-auto">
        <!-- Lien Accueil -->
        <a href="<?= $basePath ?>/" class="bottom-nav-link flex flex-col items-center gap-1 <?= $isHome ? 'active text-emerald-400' : 'text-zinc-400 hover:text-emerald-400' ?> transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="<?= $isHome ? '2.5' : '2' ?>" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span class="text-[10px] font-medium">Accueil</span>
        </a>

        <!-- Lien Actualités -->
        <a href="<?= $basePath ?>/actualites" class="bottom-nav-link flex flex-col items-center gap-1 <?= $isNews ? 'active text-emerald-400' : 'text-zinc-400 hover:text-emerald-400' ?> transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="<?= $isNews ? '2.5' : '2' ?>" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 12h6" />
            </svg>
            <span class="text-[10px] font-medium">Actus</span>
        </a>

        <!-- Lien Rejoindre KivuBoost (inscription) -->
        <a href="<?= $basePath ?>/register" class="bottom-nav-link flex flex-col items-center gap-1 text-zinc-400 hover:text-emerald-400 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            <span class="text-[10px] font-medium">Rejoindre</span>
        </a>

        <!-- Lien Support / Aide -->
        <a href="<?= $basePath ?>/support" class="bottom-nav-link flex flex-col items-center gap-1 text-zinc-400 hover:text-emerald-400 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-[10px] font-medium">Aide</span>
        </a>
    </div>
</nav>

<!-- Support Hub component (WhatsApp, Facebook, Instagram) -->
<?php include VIEW_PATH . '/layouts/support_hub.php'; ?>

<!-- petite sécurité pour éviter que le contenu soit caché sous la bottom nav sur les anciens mobiles -->
<style>
    @media (max-width: 640px) {
        main {
            padding-bottom: 5rem;
        }
        .safe-area-pb {
            padding-bottom: env(safe-area-inset-bottom, 0.5rem);
        }
    }
</style>

</body>
</html>