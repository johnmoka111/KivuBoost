<?php
use App\Core\Auth;
use App\Core\Currency;
$pageTitle = 'Grille des Tarifs';
?>

<div class="mb-6">
  <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
    <svg class="w-8 h-8 text-[#00ff88]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
    Grille des Tarifs
  </h1>
  <p class="text-gray-400 text-sm mt-1">Explorez l'ensemble de nos services, filtrez par plateforme et découvrez nos prix compétitifs.</p>
</div>

<!-- ================= SEARCH & FILTER INTERFACE ================= -->
<div class="flex flex-col lg:flex-row gap-6">

  <!-- Left Sidebar: Filters (Collapsible on mobile) -->
  <div class="w-full lg:w-1/4 shrink-0">
    <div class="rounded-2xl border shadow-2xl overflow-hidden" style="background:#0d1117;border-color:#1a2332">
      <!-- Mobile toggle button -->
      <button type="button" class="lg:hidden w-full p-4 flex items-center justify-between text-white font-bold bg-[#0a0f1a] border-b" style="border-color:#1a2332" onclick="document.getElementById('mobile-filters').classList.toggle('hidden')">
        <span class="flex items-center gap-2">
          <svg class="w-4 h-4 text-[#00ff88]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
          Afficher les Filtres
        </span>
        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>

      <div id="mobile-filters" class="hidden lg:block p-5 space-y-4">
        <div class="hidden lg:block text-xs text-gray-500 font-bold uppercase tracking-wider mb-4 border-b pb-3" style="border-color:#1a2332">Filtres de recherche</div>
        
        <!-- Live Search -->
        <div class="mb-5">
          <label class="block text-xs font-semibold text-gray-400 mb-1.5" for="search-input">Rechercher</label>
          <div class="relative">
            <svg class="w-4 h-4 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" id="search-input" placeholder="Ex: abonnés, likes..." class="w-full pl-9 pr-3 py-3 lg:py-2 rounded-xl lg:rounded-lg text-sm transition-all focus:border-[#00ff88]" style="background:#0a0f1a;border:1px solid #1a2332;color:#e2e8f0;outline:none" oninput="filterServices()">
          </div>
        </div>

        <!-- Platform selector -->
        <div class="mb-5">
          <label class="block text-xs font-semibold text-gray-400 mb-1.5" for="filter-platform">Plateforme</label>
          <select id="filter-platform" class="w-full px-3 py-3 lg:py-2 rounded-xl lg:rounded-lg text-sm transition-all focus:border-[#00ff88]" style="background:#0a0f1a;border:1px solid #1a2332;color:#e2e8f0;outline:none" onchange="onPlatformChanged()">
            <!-- Hydrated dynamically -->
          </select>
        </div>

        <!-- Category selector -->
        <div class="mb-5">
          <label class="block text-xs font-semibold text-gray-400 mb-1.5" for="filter-category">Catégorie</label>
          <select id="filter-category" class="w-full px-3 py-3 lg:py-2 rounded-xl lg:rounded-lg text-sm transition-all focus:border-[#00ff88]" style="background:#0a0f1a;border:1px solid #1a2332;color:#e2e8f0;outline:none" onchange="filterServices()">
            <!-- Hydrated dynamically -->
          </select>
        </div>

        <!-- Price Range inputs -->
        <div class="mb-5">
          <label class="block text-xs font-semibold text-gray-400 mb-1.5">Prix maximum ($ / 1000)</label>
          <input type="number" id="filter-price-max" placeholder="Ex: 5.00" step="0.01" class="w-full px-3 py-3 lg:py-2 rounded-xl lg:rounded-lg text-sm transition-all focus:border-[#00ff88]" style="background:#0a0f1a;border:1px solid #1a2332;color:#e2e8f0;outline:none" oninput="filterServices()">
        </div>
      </div>
    </div>
  </div>

  <!-- Right Content: Live scrollable results -->
  <div class="w-full lg:w-3/4">
    <div class="rounded-2xl border flex flex-col h-[700px]" style="background:#0d1117;border-color:#1a2332">
      <!-- Results Header -->
      <div class="p-4 border-b flex items-center justify-between" style="border-color:#1a2332;background:#0a0f1a;border-radius:1rem 1rem 0 0;">
        <span id="results-count" class="text-sm font-bold text-white">0 services trouvés</span>
        <a href="<?= APP_BASE ?>/dashboard" class="text-xs font-bold px-3 py-1.5 rounded-lg bg-[#00ff88]/10 text-[#00ff88] hover:bg-[#00ff88]/20 transition-all border border-[#00ff88]/20">
          Passer une commande &rarr;
        </a>
      </div>

      <!-- Results List -->
      <div class="flex-1 overflow-y-auto divide-y" style="divide-color:#1a2332" id="results-container">
        <!-- Populated by JS -->
      </div>
    </div>
  </div>

</div>

<!-- ================= JS: FILTERING LOGIC ================= -->
<script>
// Récupérer les données PHP structurées
const rawServices = <?= json_encode($services) ?>;
const exchangeRate = <?= (float)\App\Models\Setting::get('usd_rate_cdf', '2850') ?>;

// Configuration des plateformes et de leurs métadonnées
const platformMeta = {
  All: { label: 'Toutes', iconColor: '#a0aec0', svg: '<svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>' },
  YouTube: { label: 'YouTube', iconColor: '#ef4444', svg: '<svg class="w-4 h-4 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.163a3.003 3.003 0 0 0-2.11-2.11C19.517 3.545 12 3.545 12 3.545s-7.517 0-9.388.508a3.003 3.003 0 0 0-2.11 2.11C0 8.033 0 12 0 12s0 3.967.502 5.837a3.003 3.003 0 0 0 2.11 2.11c1.871.508 9.388.508 9.388.508s7.517 0 9.388-.508a3.003 3.003 0 0 0 2.11-2.11C24 15.967 24 12 24 12s0-3.967-.502-5.837zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>' },
  Instagram: { label: 'Instagram', iconColor: '#ec4899', svg: '<svg class="w-4 h-4 text-pink-500 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051C.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>' },
  TikTok: { label: 'TikTok', iconColor: '#06b6d4', svg: '<svg class="w-4 h-4 text-cyan-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.03 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.17-2.86-.74-3.99-1.72-.08-.07-.15-.15-.24-.22v6.8c.03 2.1-.64 4.22-2.02 5.85-1.44 1.73-3.67 2.76-5.93 2.84-2.2-.02-4.4-.95-5.83-2.63-1.47-1.68-2.11-3.97-1.84-6.19.26-2.14 1.54-4.14 3.49-5.12 1.59-.83 3.42-1.07 5.2-1.01V9.73c-1.12-.04-2.28.16-3.25.75-1.07.62-1.82 1.74-2 2.97-.24 1.41.22 2.92 1.25 3.92 1.01.99 2.49 1.45 3.89 1.26 1.41-.18 2.69-1.09 3.25-2.4.24-.54.34-1.14.33-1.74V.02z"/></svg>' },
  Facebook: { label: 'Facebook', iconColor: '#3b82f6', svg: '<svg class="w-4 h-4 text-blue-500 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>' },
  LinkedIn: { label: 'LinkedIn', iconColor: '#1d4ed8', svg: '<svg class="w-4 h-4 text-blue-600 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>' },
  Snapchat: { label: 'Snapchat', iconColor: '#eab308', svg: '<svg class="w-4 h-4 text-yellow-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-4.48 0-8.1 3.62-8.1 8.1 0 1.34.34 2.6.94 3.7.12.22.18.47.18.73 0 .7-.37 1.34-1 1.7A8.066 8.066 0 0 0 .5 20.83c-.34.61.1 1.37.8 1.37h21.4c.7 0 1.14-.76.8-1.37a8.066 8.066 0 0 0-3.54-6.5c-.63-.36-1-1-1-1.7 0-.26.06-.51.18-.73.6-1.1.94-2.36.94-3.7C20.1 3.62 16.48 0 12 0zm5.1 14.6c.46.27.8.7.97 1.22.17.53.13 1.1-.1 1.58a4.99 4.99 0 0 1-4.22 2.6c-1.63 0-3.15-.65-4.22-1.78a2.97 2.97 0 0 1-.75-2c0-.52.2-1 .57-1.37l2.85-2.85c.37-.37.87-.58 1.4-.58s1.03.21 1.4.58l2.1 2.1c.37.37.58.87.58 1.4s-.21 1.03-.58 1.4l-2 2a1.002 1.002 0 0 1-1.42 0c-.39-.39-.39-1.02 0-1.42l1.3-1.3-.6-.6-1.3 1.3c-.78.78-.78 2.05 0 2.83s2.05.78 2.83 0l2-2c.78-.78.78-2.05 0-2.83l-2.1-2.1c-.39-.39-1.02-.39-1.42 0l-2.85 2.85c-.39.39-.39 1.02 0 1.42.39.39 1.02.39 1.42 0l1.45-1.45.6.6-1.45 1.45a3.003 3.003 0 0 1-4.24 0c-1.17-1.17-1.17-3.07 0-4.24l2.85-2.85c1.17-1.17 3.07-1.17 4.24 0l2.1 2.1c1.17 1.17 1.17 3.07 0 4.24l-2 2z"/></svg>' },
  Telegram: { label: 'Telegram', iconColor: '#38bdf8', svg: '<svg class="w-4 h-4 text-sky-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-1-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.2-.08-.06-.19-.04-.27-.02-.12.02-1.96 1.25-5.54 3.66-.52.36-1 .53-1.42.52-.47-.01-1.37-.26-2.03-.48-.82-.27-1.47-.42-1.42-.88.03-.24.35-.49.97-.74 3.82-1.66 6.37-2.75 7.63-3.27 3.62-1.5 4.38-1.76 4.87-1.77.11 0 .35.03.51.16.13.1.17.24.19.34.02.07.03.22.02.32z"/></svg>' },
  Spotify: { label: 'Spotify', iconColor: '#22c55e', svg: '<svg class="w-4 h-4 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.49 17.31c-.22.36-.68.48-1.04.26-2.875-1.758-6.495-2.156-10.758-1.18-.41.09-.82-.17-.91-.58-.09-.41.17-.82.58-.91 4.67-1.066 8.66-.617 11.87 1.346.36.22.48.68.26 1.04zm1.467-3.268c-.28.45-.87.6-1.32.32-3.29-2.02-8.3-2.61-12.18-1.43-.51.15-1.04-.14-1.2-.65-.15-.51.14-1.04.65-1.2 4.44-1.35 9.97-.7 13.73 1.61.45.28.6.87.32 1.32zm.125-3.414C15.22 8.35 8.87 8.14 5.2 9.26c-.57.17-1.17-.15-1.34-.72-.17-.57.15-1.17.72-1.34 4.22-1.28 11.23-1.04 15.68 1.6.51.3 1.69 1 .86 1.81-.3.51-1 .69-1.51.39z"/></svg>' },
  Threads: { label: 'Threads', iconColor: '#ffffff', svg: '<svg class="w-4 h-4 text-white shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12.5.02c-1.44 0-2.86.08-4.25.24A12 12 0 0 0 0 12c0 5.25 3.37 9.7 8.08 11.36.42.15.86.25 1.3.3.44.05.88.08 1.32.08 3.32 0 6.42-1.3 8.75-3.65a12.066 12.066 0 0 0 3.25-8.09c0-3.32-1.3-6.42-3.65-8.75A11.96 11.96 0 0 0 12.5.02zm0 2.2a9.7 9.7 0 0 1 7.15 3c2 2 3.1 4.75 3.1 7.6a9.766 9.766 0 0 1-2.63 6.55c-1.9 1.9-4.4 3-7.1 3-.36 0-.72-.02-1.08-.07-.36-.05-.71-.13-1.06-.25A9.8 9.8 0 0 1 2.2 12c0-4.25 2.73-7.85 6.55-9.2.35-.12.7-.2 1.06-.25.36-.05.72-.07 1.08-.07l1.6.04zM12 5.5a6.5 6.5 0 1 0 0 13 6.5 6.5 0 0 0 0-13zm0 2a4.5 4.5 0 1 1 0 9 4.5 4.5 0 0 1 0-9z"/></svg>' },
  SEO: { label: 'SEO/Web', iconColor: '#00d4ff', svg: '<svg class="w-4 h-4 text-[#00d4ff] shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>' },
  Autre: { label: 'Autre', iconColor: '#8b5cf6', svg: '<svg class="w-4 h-4 text-violet-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>' }
};

let parsedServicesList = [];
let currentPlatformFilter = 'All';

function getPlatformFromCategory(categoryName) {
  const cat = categoryName.toLowerCase();
  if (cat.includes('youtube') || cat.includes('yt')) return 'YouTube';
  if (cat.includes('instagram') || cat.includes('insta') || cat.includes('ig')) return 'Instagram';
  if (cat.includes('tiktok') || cat.includes('tk')) return 'TikTok';
  if (cat.includes('facebook') || cat.includes('fb')) return 'Facebook';
  if (cat.includes('telegram') || cat.includes('tg')) return 'Telegram';
  if (cat.includes('linkedin') || cat.includes('lnk')) return 'LinkedIn';
  if (cat.includes('snapchat') || cat.includes('snap')) return 'Snapchat';
  if (cat.includes('spotify') || cat.includes('spot')) return 'Spotify';
  if (cat.includes('threads')) return 'Threads';
  if (cat.includes('seo') || cat.includes('google') || cat.includes('web') || cat.includes('site')) return 'SEO';
  return 'Autre';
}

function initData() {
  parsedServicesList = [];
  const platformsSet = new Set();
  platformsSet.add('All');

  for (const category in rawServices) {
    const platform = getPlatformFromCategory(category);
    platformsSet.add(platform);
    
    rawServices[category].forEach(svc => {
      parsedServicesList.push({
        ...svc,
        categoryName: category,
        platform: platform
      });
    });
  }

  // Hydrate platform dropdown
  const platformSelect = document.getElementById('filter-platform');
  platformSelect.innerHTML = '';
  platformsSet.forEach(plat => {
    const opt = document.createElement('option');
    opt.value = plat;
    opt.textContent = platformMeta[plat] ? platformMeta[plat].label : plat;
    platformSelect.appendChild(opt);
  });

  onPlatformChanged();
}

// RG-4.1 : Formate le prix en USD et CDF simultanément
function formatDualPrice(usdPer1000) {
  const usd = parseFloat(usdPer1000);
  const cdf = Math.round(usd * exchangeRate);
  return {
    usd: '$' + usd.toFixed(3),
    cdf: cdf.toLocaleString('fr-FR') + ' Fc'
  };
}

function onPlatformChanged() {
  currentPlatformFilter = document.getElementById('filter-platform').value;
  
  // Update categories dropdown based on platform
  const catSelect = document.getElementById('filter-category');
  catSelect.innerHTML = '<option value="All">Toutes les catégories</option>';
  
  const catsSet = new Set();
  parsedServicesList.forEach(s => {
    if (currentPlatformFilter === 'All' || s.platform === currentPlatformFilter) {
      catsSet.add(s.categoryName);
    }
  });

  Array.from(catsSet).sort().forEach(cat => {
    const opt = document.createElement('option');
    opt.value = cat;
    opt.textContent = cat;
    catSelect.appendChild(opt);
  });

  filterServices();
}

function filterServices() {
  const searchQ  = document.getElementById('search-input').value.toLowerCase();
  const platQ    = document.getElementById('filter-platform').value;
  const catQ     = document.getElementById('filter-category').value;
  const maxPrice = parseFloat(document.getElementById('filter-price-max').value);

  const filtered = parsedServicesList.filter(s => {
    if (platQ !== 'All' && s.platform !== platQ) return false;
    if (catQ !== 'All' && s.categoryName !== catQ) return false;
    if (searchQ && !s.name.toLowerCase().includes(searchQ) && !s.categoryName.toLowerCase().includes(searchQ) && !s.id.toString().includes(searchQ)) return false;
    if (!isNaN(maxPrice) && parseFloat(s.calculated_rate) > maxPrice) return false;
    return true;
  });

  renderResults(filtered);
}

function renderResults(results) {
  const container = document.getElementById('results-container');
  const countEl   = document.getElementById('results-count');
  container.innerHTML = '';
  countEl.textContent = results.length + ' services trouvés';

  if (results.length === 0) {
    container.innerHTML = `
      <div class="p-8 text-center text-gray-500">
        <div class="text-3xl mb-2">🕵️</div>
        <p class="text-sm">Aucun service ne correspond à vos filtres.</p>
      </div>
    `;
    return;
  }

  results.forEach(s => {
    const meta = platformMeta[s.platform] || platformMeta['Autre'];
    const price = formatDualPrice(s.calculated_rate);
    const maintenanceBadge = (s.is_active != 1) ? `<span class="px-2 py-0.5 bg-red-500/10 text-red-500 border border-red-500/20 rounded text-[10px] uppercase font-bold tracking-widest ml-2">En Maintenance</span>` : '';

    const html = `
      <div class="p-4 hover:bg-white/[0.02] transition-colors flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start gap-3 flex-1">
          <div class="mt-1">${meta.svg}</div>
          <div>
            <div class="text-sm font-semibold text-white leading-snug">
              <span class="text-gray-500 font-mono mr-1">ID:${s.id}</span>
              ${s.name}
              ${maintenanceBadge}
            </div>
            <div class="text-xs text-gray-500 mt-1">${s.categoryName}</div>
          </div>
        </div>
        <div class="flex items-center gap-6 shrink-0 md:w-1/3 justify-between md:justify-end">
          <div class="text-right">
            <div class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Min - Max</div>
            <div class="text-xs text-gray-300 font-mono">${s.min_quantity} - ${s.max_quantity}</div>
          </div>
          <div class="text-right">
            <div class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Prix / 1000</div>
            <div class="text-sm font-bold font-mono" style="color:#00d4ff">${price.usd}</div>
            <div class="text-[10px] font-semibold font-mono mt-0.5" style="color:#a78bfa">${price.cdf} <span class="text-gray-600 font-normal">les 1000</span></div>
          </div>
        </div>
      </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
  });
}

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
  initData();
});
</script>
