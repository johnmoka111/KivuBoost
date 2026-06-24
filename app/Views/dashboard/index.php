<?php
use App\Core\Auth;
use App\Core\Currency;
$pageTitle = 'Formulaire de commande';

// Calcul du total dépensé
$totalSpentUsd = array_sum(array_column($orders, 'cost'));
?>

<!-- ===== HEADER STATS ===== -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <!-- Solde avec sélecteur de devise intégré -->
  <div class="col-span-1 rounded-xl p-4 border transition-all hover:scale-[1.01]"
       style="background:#0d1117;border-color:#1a2332;box-shadow:0 0 25px rgba(0,255,136,0.05)">
    <!-- Titre + sélecteur -->
    <div class="flex items-center justify-between mb-2">
      <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Mon Solde</div>
      <!-- Mini sélecteur de devise -->
      <div class="relative" id="dash-currency-wrapper">
        <button onclick="toggleDashCurrency()"
                class="flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-extrabold uppercase border transition-all hover:bg-white/5 active:scale-95"
                style="background:#0a0f1a;border-color:#1a2332;color:#00d4ff">
          <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
          <span id="dash-currency-label"><?= Currency::getActive() ?></span>
          <svg class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <!-- Dropdown devise -->
        <div id="dash-currency-dropdown"
             class="hidden absolute right-0 top-full mt-1.5 z-50 rounded-xl border shadow-2xl overflow-hidden"
             style="background:#0d1117;border-color:#1a2332;width:210px;max-height:250px;overflow-y:auto">
          <?php
          $activeCur  = Currency::getActive();
          $balanceUsd = (float)$user['balance'];
          foreach (Currency::all() as $code => $info):
            $rate      = Currency::getRate($code);
            $converted = $balanceUsd * $rate;
            $noDecimal = ['CDF','XAF','XOF','RWF','BIF','UGX','TZS','GNF','NGN'];
            $d         = in_array($code, $noDecimal) ? 0 : 2;
            $fmtAmt    = number_format($converted, $d, ',', ' ') . ' ' . $info['symbol'];
            $isActive  = $code === $activeCur;
          ?>
          <button onclick="dashSelectCurrency('<?= $code ?>')"
                  class="w-full flex items-center justify-between px-3 py-2 text-left transition-colors text-xs gap-2 hover:bg-white/5 <?= $isActive ? 'text-[#00ff88]' : 'text-gray-300' ?>"
                  style="<?= $isActive ? 'background:rgba(0,255,136,0.05)' : '' ?>">
            <div class="flex items-center gap-2 min-w-0">
              <span class="text-sm leading-none"><?= $info['flag'] ?></span>
              <div class="min-w-0">
                <div class="font-bold truncate"><?= $code ?></div>
                <div class="text-[10px] text-gray-500 truncate"><?= $info['name'] ?></div>
              </div>
            </div>
            <div class="font-bold text-right shrink-0 <?= $isActive ? 'text-[#00ff88]' : 'text-gray-400' ?>"><?= $fmtAmt ?></div>
          </button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <!-- Montant -->
    <div id="dash-balance-amount" class="text-2xl font-bold" style="color:#00ff88">
      <?= Currency::format((float)$user['balance']) ?>
    </div>
    <a href="<?= APP_BASE ?>/recharge"
       class="inline-flex items-center gap-1.5 mt-2 text-xs font-semibold px-3 py-1.5 rounded-lg transition-all hover:opacity-90"
       style="color:#050811;background:#00ff88;box-shadow:0 0 10px rgba(0,255,136,0.2)">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
      </svg>
      Recharger
    </a>
  </div>

  <!-- Total commandes -->
  <div class="col-span-1 rounded-xl p-4 border transition-all hover:scale-[1.01] flex flex-col justify-between" style="background:#0d1117;border-color:#1a2332">
    <div>
      <div class="flex items-center justify-between mb-2 lg:mb-4">
        <div class="text-[10px] text-gray-400 font-bold tracking-wider uppercase">Commandes</div>
        <div class="w-6 h-6 lg:w-8 lg:h-8 rounded-full flex items-center justify-center bg-purple-500/10 text-purple-400">
          <svg class="w-3 h-3 lg:w-4 lg:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        </div>
      </div>
      <div class="text-xl lg:text-3xl font-black text-white" id="stat-orders"><?= count($orders) ?></div>
    </div>
    <a href="<?= APP_BASE ?>/history" class="inline-flex items-center gap-1 mt-3 text-[10px] font-semibold text-purple-400 hover:text-purple-300 transition-colors">
      Voir l'historique
      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </a>
  </div>

  <!-- Dépensé -->
  <div class="col-span-1 rounded-xl p-4 border transition-all hover:scale-[1.01] flex flex-col justify-between" style="background:#0d1117;border-color:#1a2332">
    <div>
      <div class="flex items-center justify-between mb-2 lg:mb-4">
        <div class="text-[10px] text-gray-400 font-bold tracking-wider uppercase">Dépensé</div>
        <div class="w-6 h-6 lg:w-8 lg:h-8 rounded-full flex items-center justify-center bg-[#00d4ff]/10 text-[#00d4ff]">
          <svg class="w-3 h-3 lg:w-4 lg:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
      </div>
      <div class="text-xl lg:text-3xl font-black text-white" id="stat-spent"><?= Currency::format((float)$totalSpentUsd) ?></div>
    </div>
    <div class="inline-flex items-center gap-1 mt-3 text-[10px] font-semibold text-gray-500">
      Sur vos commandes
    </div>
  </div>

  <!-- Services dispo -->
  <div class="rounded-xl p-4 border transition-all hover:border-gray-700" style="background:#0d1117;border-color:#1a2332">
    <div class="text-xs text-gray-500 mb-1 uppercase tracking-wider font-semibold">Services</div>
    <div class="text-2xl font-bold text-white"><?= array_sum(array_map('count', $services)) ?></div>
    <div class="text-xs text-gray-600 mt-1">Disponibles en ligne</div>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <!-- ================= LEFT: ORDER FORM ================= -->
  <div class="lg:col-span-2 space-y-6">
    <div class="rounded-2xl p-4 md:p-6 border w-full" style="background:#0d1117;border-color:#1a2332">
      <!-- Tabs header -->
      <div class="relative w-full">
        <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-4 border-b select-none scrollbar-hide relative z-0" style="border-color:#1a2332; scrollbar-width: none;">
          <button type="button" id="tab-new-order" onclick="switchTab('new-order')" class="px-4 py-3 md:py-2 text-xs font-bold rounded-lg flex items-center gap-1.5 shrink-0 transition-all"
                style="background:rgba(0,255,136,0.1);color:#00ff88">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          NOUVELLE COMMANDE
        </button>
        <button type="button" id="tab-favorites" onclick="switchTab('favorites')" class="px-4 py-3 md:py-2 text-xs font-bold text-gray-500 hover:text-gray-300 rounded-lg flex items-center gap-1.5 shrink-0 transition-all">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
          MES PRÉFÉRÉS
        </button>
        <button type="button" id="tab-mass-order" onclick="switchTab('mass-order')" class="px-4 py-3 md:py-2 text-xs font-bold text-gray-500 hover:text-gray-300 rounded-lg flex items-center gap-1.5 shrink-0 transition-all">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
          ORDRE DE MASSE
        </button>

        </div>
        <!-- Gradient indicator for horizontal scroll on mobile -->
        <div class="md:hidden absolute right-0 top-0 bottom-4 w-12 pointer-events-none" style="background: linear-gradient(to left, #0d1117, transparent);"></div>
      </div>

      <!-- Container: Search Modal trigger -->
      <div id="search-trigger-container">
        <!-- Quick Search / Filter Bar -->
        <div class="relative mb-5 flex items-center gap-2 p-3 rounded-xl cursor-pointer hover:border-gray-600 transition-all border"
             style="background:#0a0f1a;border-color:#1a2332"
             onclick="openSearchModal()">
          <div class="flex items-center gap-3 text-gray-400 flex-1">
            <svg class="w-5 h-5 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <div class="text-xs text-gray-400 select-none">
              Services de recherche et de navigation <span class="hidden md:inline text-gray-600">• Filtres, prix, plateformes et plus encore</span>
            </div>
          </div>
          <button type="button" class="px-2.5 py-1 text-[10px] font-bold rounded bg-emerald-500/10 text-[#00ff88] border border-emerald-500/20 uppercase tracking-wide shrink-0">
             Filtres
          </button>
        </div>
      </div>

      <!-- VIEW 1 & 2: NOUVELLE COMMANDE & FAVORITES -->
      <div id="new-order-container">
        <!-- Empty Favorites Placeholder -->
        <div id="favorites-empty-placeholder" class="hidden text-center py-12 text-gray-500 space-y-4">
          <div class="text-4xl"></div>
          <p class="text-sm">Vous n'avez pas encore ajouté de services à vos préférés.</p>
          <p class="text-xs max-w-sm mx-auto leading-relaxed">
            Pour ajouter un service, cliquez sur le bouton <strong class="text-yellow-400">★ Favori</strong> à côté du choix de service dans l'onglet <strong>Nouvelle Commande</strong>.
          </p>
        </div>

        <form method="POST" action="<?= APP_BASE ?>/orders/place" id="orderForm" class="space-y-4">
          <?= Auth::csrfField() ?>

          <!-- Category Dropdown -->
          <div id="category_select_group">
            <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider" for="category_select">
              Catégorie
            </label>
            <select id="category_select" class="w-full px-4 py-3 rounded-xl text-sm"
                    style="background:#0a0f1a;border:1px solid #1a2332;color:#e2e8f0"
                    onchange="onCategoryChanged()">
              <!-- Hydrated by JS -->
            </select>
          </div>

          <!-- Service Dropdown -->
          <div id="service_id_group">
            <div class="flex items-center justify-between mb-1.5">
              <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider" for="service_id">
                Service
              </label>
              <button type="button" id="favorite-btn" onclick="toggleFavoriteCurrentService()" class="text-xs text-gray-500 hover:text-yellow-400 flex items-center gap-1 transition-all select-none focus:outline-none">
                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24" id="favorite-star-icon">
                  <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                </svg>
                <span id="favorite-btn-text">Ajouter aux Favoris</span>
              </button>
            </div>
            <select name="service_id" id="service_id" required
                    class="w-full px-4 py-3 rounded-xl text-sm"
                    style="background:#0a0f1a;border:1px solid #1a2332;color:#e2e8f0"
                    onchange="onServiceChanged()">
              <!-- Hydrated by JS -->
            </select>
          </div>

          <!-- Service Detail Dynamic Card -->
          <div id="service-description-card" class="hidden rounded-xl p-4 border space-y-3" style="background:#0a0f1a;border-color:#1a2332">
            <div class="text-xs font-bold text-white border-b pb-2 flex items-center gap-1.5" style="border-color:#1a2332">
              <svg class="w-4 h-4 text-[#00ff88]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              Description &amp; Garanties du Service
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
              <div class="flex items-center gap-2 text-gray-400">
                <span class="text-base"></span>
                <div>
                  <div class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Démarrage</div>
                  <strong id="desc-start-time" class="text-white">—</strong>
                </div>
              </div>
              <div class="flex items-center gap-2 text-gray-400">
                <span class="text-base"></span>
                <div>
                  <div class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Vitesse</div>
                  <strong id="desc-speed" class="text-white">—</strong>
                </div>
              </div>
              <div class="flex items-center gap-2 text-gray-400">
                <span class="text-base"></span>
                <div>
                  <div class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Garantie</div>
                  <strong id="desc-refill" class="text-white">—</strong>
                </div>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Lien -->
            <div>
              <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider" for="link">
                Lien du profil / publication
              </label>
              <input type="url" name="link" id="link" required
                     placeholder="https://www.instagram.com/monprofil"
                     class="w-full px-4 py-3 rounded-xl text-sm"
                     style="background:#0a0f1a;border:1px solid #1a2332;color:#e2e8f0;transition:border-color .2s"
                     onfocus="this.style.borderColor='rgba(0,255,136,0.5)'"
                     onblur="this.style.borderColor='#1a2332'">
            </div>

            <!-- Quantité -->
            <div>
              <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider" for="quantity">
                Quantité
              </label>
              <input type="number" name="quantity" id="quantity" required min="1"
                     placeholder="ex: 1000"
                     class="w-full px-4 py-3 rounded-xl text-sm"
                     style="background:#0a0f1a;border:1px solid #1a2332;color:#e2e8f0;transition:border-color .2s"
                     onfocus="this.style.borderColor='rgba(0,255,136,0.5)'"
                     onblur="this.style.borderColor='#1a2332'"
                     oninput="updatePrice()">
            </div>
          </div>

          <!-- Info service sélectionné -->
          <div id="service-info" class="hidden px-4 py-3 rounded-xl text-xs" style="background:rgba(0,212,255,0.05);border:1px solid rgba(0,212,255,0.15)">
            <div class="flex flex-wrap gap-4 text-gray-400">
              <span>Minimum : <strong id="info-min" class="text-white">—</strong></span>
              <span>Maximum : <strong id="info-max" class="text-white">—</strong></span>
              <span>Prix/1000 : <strong id="info-price" class="text-[#00d4ff]">—</strong></span>
            </div>
          </div>
          <!-- Calcul prix temps réel -->
          <div id="price-preview" class="hidden px-4 py-4 rounded-xl border" style="background:rgba(0,255,136,0.04);border-color:rgba(0,255,136,0.2)">
            <div class="flex items-center justify-between">
              <div>
                <div class="text-xs text-gray-500 mb-1">Coût total estimé</div>
                <div class="text-2xl font-bold" style="color:#00ff88"><span id="total-price">—</span></div>
              </div>
              <div class="text-right">
                <div class="text-xs text-gray-500 mb-1">Votre solde après</div>
                <div class="text-lg font-semibold" id="balance-after" style="color:#00d4ff">—</div>
              </div>
            </div>
            <div id="balance-warning" class="hidden mt-3 text-xs text-red-400 flex items-center gap-1.5">
              <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
              </svg>
              Solde insuffisant — <a href="<?= APP_BASE ?>/recharge" style="color:#00d4ff" class="hover:underline">Recharger maintenant</a>
            </div>
          </div>

          <button type="submit" id="submit-btn"
                  class="w-full py-3.5 rounded-xl text-sm font-bold transition-all hover:brightness-110 active:scale-[0.99]"
                  style="background:linear-gradient(135deg,#00ff88,#00c466);color:#050811;box-shadow:0 4px 15px rgba(0,255,136,0.2)">
            Passer la commande
          </button>
        </form>
      </div>

      <!-- VIEW 3: ORDRE DE MASSE -->
      <div id="mass-order-container" class="hidden">
        <form method="POST" action="<?= APP_BASE ?>/orders/mass-place" class="space-y-4">
          <?= Auth::csrfField() ?>
          <div>
            <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider" for="mass_order">
              Lignes de commande en masse
            </label>
            <textarea name="mass_order" id="mass_order" required rows="8"
                      placeholder="service_id | quantité | lien&#10;Exemple:&#10;135 | 1000 | https://www.tiktok.com/@nom/video/12345&#10;135 | 500 | https://www.tiktok.com/@nom/video/67890"
                      class="w-full px-4 py-3 rounded-xl text-sm font-mono"
                      style="background:#0a0f1a;border:1px solid #1a2332;color:#e2e8f0;transition:border-color .2s;height:180px;"
                      onfocus="this.style.borderColor='rgba(0,255,136,0.5)'"
                      onblur="this.style.borderColor='#1a2332'"></textarea>
          </div>
          
          <div class="rounded-xl p-4 border text-xs text-gray-400 leading-relaxed space-y-1.5" style="background:#0a0f1a;border-color:#1a2332">
            <span class="font-bold text-white flex items-center gap-1.5">
               Comment l'utiliser :
            </span>
            <p>1. Entrez chaque commande sur une ligne distincte.</p>
            <p>2. Séparez l'ID du service, la quantité et le lien cible par une barre verticale <code>|</code>.</p>
            <p>3. Assurez-vous d'avoir un solde suffisant pour toutes les commandes cumulées.</p>
          </div>

          <button type="submit"
                  class="w-full py-3.5 rounded-xl text-sm font-bold transition-all hover:brightness-110 active:scale-[0.99]"
                  style="background:linear-gradient(135deg,#00ff88,#00c466);color:#050811;box-shadow:0 4px 15px rgba(0,255,136,0.2)">
            Passer la commande de masse
          </button>
        </form>
      </div>


    </div>
  </div>

  <!-- ================= RIGHT: SIDEBAR FILTERS ================= -->
  <div class="space-y-6 order-first lg:order-none">
    <!-- Filter card -->
    <div class="rounded-2xl p-5 border" style="background:#0d1117;border-color:#1a2332">
      <div class="flex items-center justify-between pb-3 border-b mb-4" style="border-color:#1a2332">
        <div class="flex items-center gap-2">
          <svg class="w-4 h-4 text-[#00ff88]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
          <span class="text-sm font-bold text-white">Filtre</span>
        </div>
        <div class="flex items-center gap-1 bg-white/[0.02] p-1 rounded-lg border" style="border-color:#1a2332">
          <button type="button" id="filter-tab-platforms" onclick="switchFilterTab('platforms')" class="px-2.5 py-1 text-[10px] rounded bg-[#00ff88]/10 text-[#00ff88] font-bold border border-[#00ff88]/20 transition-all">Plateformes</button>
          <button type="button" id="filter-tab-countries" onclick="switchFilterTab('countries')" class="px-2.5 py-1 text-[10px] rounded text-gray-500 font-semibold hover:text-white transition-all border border-transparent">Pays</button>
        </div>
      </div>

      <!-- Platform Filter Chips -->
      <div class="flex overflow-x-auto lg:flex-wrap gap-2 transition-all pb-2 scrollbar-hide" id="platform-chips-container" style="scrollbar-width: none;">
        <!-- Hydrated dynamically by JS -->
      </div>

      <!-- Country Filter Chips -->
      <div class="flex overflow-x-auto lg:flex-wrap gap-2 hidden transition-all pb-2 scrollbar-hide" id="country-chips-container" style="scrollbar-width: none;">
        <!-- Hydrated dynamically by JS -->
      </div>
    </div>

    <!-- Instructions card -->
    <div class="rounded-2xl p-5 border text-xs space-y-3" style="background:#0d1117;border-color:#1a2332">
      <div class="font-bold text-white flex items-center gap-2">
        <span></span> Information &amp; Guide
      </div>
      <p class="text-gray-400 leading-relaxed">
        Suivez ces consignes simples pour garantir la livraison rapide de vos commandes :
      </p>
      <ul class="space-y-2 text-gray-500 pl-4 list-disc">
        <li>Assurez-vous que votre compte ou publication soit en mode <strong>Public</strong>.</li>
        <li>Ne commandez pas deux services différents sur le même lien en même temps (attendez la fin du premier).</li>
        <li>Format attendu : <code>https://instagram.com/nom</code> ou <code>https://tiktok.com/@nom/video/...</code></li>
      </ul>
    </div>
  </div>
</div>

<!-- ===== HISTORIQUE DES COMMANDES ===== -->
<div class="rounded-2xl border mt-6" style="background:#0d1117;border-color:#1a2332">
  <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 px-5 py-4 border-b" style="border-color:#1a2332">
    <div class="flex items-center gap-2">
      <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:rgba(0,212,255,0.1)">
        <svg class="w-4 h-4" style="color:#00d4ff" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
      </div>
      <h2 class="text-base font-bold text-white">Mes Commandes Récentes</h2>
    </div>
    <span class="text-xs text-gray-500 whitespace-nowrap hidden sm:inline"><?= count($orders) ?> cmd(s)</span>
  </div>

  <?php if (empty($orders)): ?>
    <div class="text-center py-16 text-gray-600">
      <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
      </svg>
      <p class="text-sm">Aucune commande pour le moment.</p>
      <p class="text-xs mt-1">Sélectionnez un service ci-dessus pour commencer !</p>
    </div>
  <?php else: ?>

    <!-- Table Desktop -->
    <div class="hidden md:block overflow-x-auto w-full">
      <table class="w-full text-sm">
        <thead>
          <tr style="border-bottom:1px solid #1a2332">
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Service</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Lien cible</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Quantité</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Montant</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Statut</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date de création</th>
          </tr>
        </thead>
        <tbody class="divide-y" style="divide-color:#1a2332">
          <?php foreach ($orders as $order): ?>
          <tr class="hover:bg-white/[0.02] transition-colors">
            <td class="px-5 py-3.5 text-gray-500 font-mono text-xs">#<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></td>
            <td class="px-5 py-3.5">
              <div class="text-white text-xs font-semibold"><?= htmlspecialchars($order['service_name'] ?? '—') ?></div>
              <div class="text-gray-500 text-xs mt-0.5"><?= htmlspecialchars($order['category'] ?? '') ?></div>
            </td>
            <td class="px-5 py-3.5">
              <a href="<?= htmlspecialchars($order['link']) ?>" target="_blank" rel="noopener"
                 class="text-xs hover:underline truncate block max-w-[200px]" style="color:#00d4ff">
                <?= htmlspecialchars(parse_url($order['link'], PHP_URL_HOST) ?? 'Lien') ?>...
              </a>
            </td>
            <td class="px-5 py-3.5 text-white text-xs font-mono"><?= number_format($order['quantity']) ?></td>
            <td class="px-5 py-3.5 text-xs font-bold font-mono" style="color:#00ff88">
              <?= Currency::format((float)$order['cost'], 3) ?>
            </td>
            <td class="px-5 py-3.5">
              <?php
              $badgeClass = match(strtolower($order['status'])) {
                  'pending'   => 'badge-pending',
                  'processing' => 'badge-processing',
                  'completed' => 'badge-completed',
                  'canceled'  => 'badge-canceled',
                  'partial'   => 'badge-partial',
                  default      => 'badge-pending',
              };
              ?>
              <span class="<?= $badgeClass ?> text-xs px-2.5 py-1 rounded-full font-medium">
                <?= htmlspecialchars($order['status']) ?>
              </span>
            </td>
            <td class="px-5 py-3.5 text-gray-500 text-xs">
              <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- ====== CARDS MOBILE ====== -->
    <div class="md:hidden p-4 space-y-4 bg-[#0a0f1a] rounded-b-2xl">
      <?php foreach ($orders as $order): ?>
      <?php
        $statusVal = strtolower($order['status']);
        $badgeClass = match($statusVal) {
            'pending'    => 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20',
            'processing' => 'bg-blue-500/10 text-blue-400 border border-blue-500/20',
            'completed'  => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
            'canceled'   => 'bg-red-500/10 text-red-400 border border-red-500/20',
            'partial'    => 'bg-orange-500/10 text-orange-400 border border-orange-500/20',
            default      => 'bg-gray-500/10 text-gray-400 border border-gray-500/20',
        };
        $statusLabel = match($statusVal) {
            'pending'    => 'En attente',
            'processing' => 'En cours',
            'completed'  => 'Complété',
            'canceled'   => 'Annulé',
            'partial'    => 'Partiel',
            default      => htmlspecialchars($order['status']),
        };
      ?>
      <div class="p-4 rounded-xl border transition-all shadow-sm" style="background:#0d1117; border-color:#1a2332;">
        <div class="flex items-start justify-between mb-3 gap-2">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1.5">
              <span class="text-[10px] font-mono font-bold text-gray-500 bg-[#1a2332]/50 px-1.5 py-0.5 rounded">#<?= $order['id'] ?></span>
              <?php if (!empty($order['category'])): ?>
              <span class="text-[10px] px-2 py-0.5 rounded-full font-medium bg-[#1a2332] text-gray-400 truncate">
                <?= htmlspecialchars($order['category']) ?>
              </span>
              <?php endif; ?>
            </div>
            <div class="text-sm font-semibold text-white leading-snug break-words"><?= htmlspecialchars($order['service_name'] ?? '—') ?></div>
          </div>
        </div>

        <!-- Lien -->
        <div class="mb-3 bg-[#0a0f1a] rounded-lg p-2 border border-[#1a2332]">
          <a href="<?= htmlspecialchars($order['link']) ?>" target="_blank" rel="noopener"
             class="text-xs hover:underline truncate block text-[#00d4ff] flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
            <?= htmlspecialchars($order['link']) ?>
          </a>
        </div>

        <!-- Méta infos -->
        <div class="flex items-center justify-between border-t pt-3 mt-1" style="border-color:#1a2332">
          <div class="flex flex-col gap-1">
            <span class="text-[10px] text-gray-500 uppercase font-semibold tracking-wider">Quantité</span>
            <strong class="text-white font-mono text-sm"><?= number_format($order['quantity']) ?></strong>
          </div>
          <div class="flex flex-col gap-1 text-right">
            <span class="text-[10px] text-gray-500 uppercase font-semibold tracking-wider">Montant</span>
            <strong class="font-mono text-sm" style="color:#00ff88"><?= Currency::format((float)$order['cost'], 3) ?></strong>
          </div>
        </div>
        
        <div class="flex items-center justify-between mt-3">
           <span class="inline-flex items-center gap-1 text-[10px] px-2.5 py-1 rounded-full font-semibold <?= $badgeClass ?>">
            <?php if ($statusVal === 'processing'): ?>
              <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse inline-block"></span>
            <?php elseif ($statusVal === 'pending'): ?>
              <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 inline-block"></span>
            <?php elseif ($statusVal === 'completed'): ?>
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span>
            <?php endif; ?>
            <?= $statusLabel ?>
          </span>
          <span class="text-[10px] text-gray-500"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>


<!-- ================= SEARCH & FILTER MODAL (IMAGE 5 STYLE) ================= -->
<div id="search-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4" style="background:rgba(5,8,17,0.75);backdrop-filter:blur(15px);-webkit-backdrop-filter:blur(15px);">
  <!-- Modal box -->
  <div class="relative w-full max-w-4xl h-[90vh] sm:h-[80vh] flex flex-col rounded-2xl border overflow-hidden shadow-2xl transition-all"
       style="background:#0d1117;border-color:#1a2332;box-shadow:0 15px 40px rgba(0,0,0,0.6)">
    
    <!-- Modal header with live search input -->
    <div class="p-4 border-b flex items-center gap-3" style="border-color:#1a2332;background:#0a0f1a">
      <svg class="w-5 h-5 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      <input type="text" id="modal-search-input"
             placeholder="Services de recherche, par exemple « abonnés », « vues »..."
             class="flex-1 bg-transparent text-sm text-white focus:outline-none placeholder-gray-600"
             oninput="filterModalServices()">
      <button type="button" onclick="closeSearchModal()" class="w-7 h-7 rounded-full flex items-center justify-center text-gray-500 hover:text-white transition-all bg-white/[0.04] hover:bg-white/[0.08]">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <!-- Modal Body (Two-columns layout) -->
    <div class="flex-1 flex flex-col md:flex-row overflow-hidden relative">
      <!-- Mobile Filter Toggle -->
      <button type="button" class="md:hidden w-full p-3 flex items-center justify-between text-white font-bold bg-[#0a0f1a] border-b z-10" style="border-color:#1a2332" onclick="document.getElementById('modal-filters-sidebar').classList.toggle('hidden')">
        <span class="flex items-center gap-2">
          <svg class="w-4 h-4 text-[#00ff88]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
          Options de filtrage
        </span>
        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>

      <!-- Left sidebar: Filter options -->
      <div id="modal-filters-sidebar" class="hidden md:block w-full md:w-1/3 p-4 border-b md:border-b-0 md:border-r overflow-y-auto space-y-4 shrink-0 absolute md:relative w-full h-full md:h-auto z-20" style="border-color:#1a2332;background:#0d1117">
        <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider hidden md:block">Filtres</div>
        
        <!-- Platform selector -->
        <div>
          <label class="block text-xs font-semibold text-gray-400 mb-1.5" for="modal-platform">Plateforme</label>
          <select id="modal-platform" class="w-full px-3 py-2 rounded-lg text-xs"
                  style="background:#0a0f1a;border:1px solid #1a2332;color:#e2e8f0"
                  onchange="onModalPlatformChanged()">
            <!-- Hydrated dynamically -->
          </select>
        </div>

        <!-- Category selector -->
        <div>
          <label class="block text-xs font-semibold text-gray-400 mb-1.5" for="modal-category">Catégorie</label>
          <select id="modal-category" class="w-full px-3 py-2 rounded-lg text-xs"
                  style="background:#0a0f1a;border:1px solid #1a2332;color:#e2e8f0"
                  onchange="filterModalServices()">
            <!-- Hydrated dynamically -->
          </select>
        </div>

        <!-- Price Range inputs -->
        <div>
          <label class="block text-xs font-semibold text-gray-400 mb-1.5">Prix ($ / 1000)</label>
          <div class="grid grid-cols-2 gap-2">
            <input type="number" id="modal-price-min" placeholder="Min" step="0.01" class="w-full px-3 py-2 rounded-lg text-xs"
                   style="background:#0a0f1a;border:1px solid #1a2332;color:#e2e8f0" oninput="filterModalServices()">
            <input type="number" id="modal-price-max" placeholder="Max" step="0.01" class="w-full px-3 py-2 rounded-lg text-xs"
                   style="background:#0a0f1a;border:1px solid #1a2332;color:#e2e8f0" oninput="filterModalServices()">
          </div>
        </div>

        <!-- Quantity Range inputs -->
        <div>
          <label class="block text-xs font-semibold text-gray-400 mb-1.5">Quantité admise</label>
          <div class="grid grid-cols-2 gap-2">
            <input type="number" id="modal-qty-min" placeholder="Min" class="w-full px-3 py-2 rounded-lg text-xs"
                   style="background:#0a0f1a;border:1px solid #1a2332;color:#e2e8f0" oninput="filterModalServices()">
            <input type="number" id="modal-qty-max" placeholder="Max" class="w-full px-3 py-2 rounded-lg text-xs"
                   style="background:#0a0f1a;border:1px solid #1a2332;color:#e2e8f0" oninput="filterModalServices()">
          </div>
        </div>

        <!-- Favorites filter mock -->
        <label class="flex items-center gap-2 cursor-pointer select-none">
          <input type="checkbox" id="modal-favorites-checkbox" class="rounded text-[#00ff88] focus:ring-0" style="background:#0a0f1a;border-color:#1a2332" onchange="filterModalServices()">
          <span class="text-xs text-gray-400">Favoris uniquement</span>
        </label>
      </div>

      <!-- Right content: Live scrollable results -->
      <div class="flex-1 flex flex-col overflow-hidden bg-[#070b12]">
        <div class="flex-1 overflow-y-auto divide-y" style="divide-color:#1a2332" id="modal-results-container">
          <!-- Populated by JS -->
        </div>

        <!-- Results footer info bar -->
        <div class="p-3 bg-[#0a0f1a] border-t flex items-center justify-between text-xs text-gray-500 font-semibold" style="border-color:#1a2332">
          <div><span id="modal-results-count" class="text-white">0</span> services filtrés</div>
          <div>Cliquez sur un service pour l'importer</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ================= JS: MULTI-API FILTERING LOGIC ================= -->
<script>
// Récupérer les données PHP structurées
const rawServices    = <?= json_encode($services) ?>;
const userBalanceUsd = <?= (float)$user['balance'] ?>;
const activeCurrency = '<?= Currency::getActive() ?>';
const exchangeRate   = <?= (float)\App\Models\Setting::get('usd_rate_cdf', '2800') ?>;

// Toutes les devises disponibles (pour la conversion JS côté client)
const allCurrencies  = <?= json_encode(array_map(fn($v) => [
  'symbol'   => $v['symbol'],
  'name'     => $v['name'],
  'flag'     => $v['flag'],
  'fallback' => $v['fallback'],
], Currency::all())) ?>;

// --- Sélecteur de devise du Dashboard ---
function toggleDashCurrency() {
  const dd = document.getElementById('dash-currency-dropdown');
  dd.classList.toggle('hidden');
}
document.addEventListener('click', function(e) {
  const w = document.getElementById('dash-currency-wrapper');
  if (w && !w.contains(e.target)) {
    const dd = document.getElementById('dash-currency-dropdown');
    if (dd) dd.classList.add('hidden');
  }
});
function dashSelectCurrency(code) {
  fetch('<?= APP_BASE ?>/currency/switch?to=' + code)
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        document.getElementById('dash-currency-label').textContent   = code;
        document.getElementById('dash-balance-amount').textContent    = data.formatted;
        // Mettre aussi à jour le solde dans le sidebar si présent
        const sidebarBal = document.getElementById('balance-display');
        if (sidebarBal) sidebarBal.textContent = data.formatted;
        const sidebarLabel = document.getElementById('currency-active-label');
        if (sidebarLabel) sidebarLabel.textContent = code;
        document.getElementById('dash-currency-dropdown').classList.add('hidden');
      }
    })
    .catch(() => { window.location.href = '<?= APP_BASE ?>/currency/switch?to=' + code; });
}

// Configuration des plateformes et de leurs métadonnées
const platformMeta = {
  All: { label: 'All', iconColor: '#a0aec0', svg: '<svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>' },
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

// Variable d'état globale de filtrage
let currentPlatformFilter = 'All';
let parsedServicesList    = [];

// Classifier les catégories dans leurs plateformes
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

// Initialisation globale des données et chips
function initDashboardFilters() {
  parsedServicesList = [];
  const platformCounts = { All: 0, YouTube: 0, Instagram: 0, TikTok: 0, Facebook: 0, LinkedIn: 0, Snapchat: 0, Telegram: 0, Spotify: 0, Threads: 0, SEO: 0, Autre: 0 };
  const countryCounts = { All: 0 };

  // Aplatir l'objet PHP
  for (const category in rawServices) {
    const list = rawServices[category];
    const platform = getPlatformFromCategory(category);
    const country = getCountryFromCategory(category);
    
    list.forEach(svc => {
      const flattened = {
        ...svc,
        categoryName: category,
        platform: platform,
        country: country
      };
      parsedServicesList.push(flattened);
      platformCounts[platform]++;
      platformCounts['All']++;

      if (country) {
        if (!countryCounts[country]) countryCounts[country] = 0;
        countryCounts[country]++;
      }
      countryCounts['All']++;
    });
  }

  // Générer les chips de plateforme
  const pContainer = document.getElementById('platform-chips-container');
  pContainer.innerHTML = '';

  for (const platKey in platformMeta) {
    const count = platformCounts[platKey] || 0;
    if (platKey !== 'All' && count === 0) continue; 

    const meta = platformMeta[platKey];
    const chip = document.createElement('div');
    chip.className = `platform-chip flex items-center justify-between w-[200px] lg:w-full shrink-0 px-3.5 py-2.5 rounded-xl border text-xs cursor-pointer select-none transition-all ${platKey === 'All' ? 'active' : ''}`;
    chip.dataset.platform = platKey;
    chip.onclick = () => filterByPlatform(platKey);

    chip.innerHTML = `
      <div class="flex items-center gap-2">
        ${meta.svg}
        <span class="text-white font-medium">${meta.label}</span>
      </div>
      <span class="text-[10px] px-2 py-0.5 rounded-full font-bold" style="background:rgba(255,255,255,0.05);color:#a0aec0">${count}</span>
    `;
    pContainer.appendChild(chip);
  }

  // Générer les chips de pays
  const cContainer = document.getElementById('country-chips-container');
  cContainer.innerHTML = '';

  const countryIcon = `<svg class="w-4 h-4 text-orange-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;

  for (const country in countryCounts) {
    const count = countryCounts[country];
    if (country !== 'All' && count === 0) continue;

    const chip = document.createElement('div');
    chip.className = `country-chip flex items-center justify-between w-[180px] lg:w-full shrink-0 px-3.5 py-2.5 rounded-xl border text-xs cursor-pointer select-none transition-all ${country === 'All' ? 'active' : ''}`;
    chip.dataset.country = country;
    chip.onclick = () => filterByCountry(country);

    chip.innerHTML = `
      <div class="flex items-center gap-2">
        ${country === 'All' ? platformMeta['All'].svg : countryIcon}
        <span class="text-white font-medium">${country === 'All' ? 'Tous les Pays' : country}</span>
      </div>
      <span class="text-[10px] px-2 py-0.5 rounded-full font-bold" style="background:rgba(255,255,255,0.05);color:#a0aec0">${count}</span>
    `;
    cContainer.appendChild(chip);
  }

  // Hydrater le formulaire principal
  filterByPlatform('All');
}

function switchFilterTab(tab) {
  const pTab = document.getElementById('filter-tab-platforms');
  const cTab = document.getElementById('filter-tab-countries');
  const pContainer = document.getElementById('platform-chips-container');
  const cContainer = document.getElementById('country-chips-container');

  if (tab === 'platforms') {
    pTab.className = "px-2.5 py-1 text-[10px] rounded bg-[#00ff88]/10 text-[#00ff88] font-bold border border-[#00ff88]/20 transition-all";
    cTab.className = "px-2.5 py-1 text-[10px] rounded text-gray-500 font-semibold hover:text-white transition-all border border-transparent";
    pContainer.classList.remove('hidden');
    cContainer.classList.add('hidden');
    filterByPlatform(currentPlatformFilter);
  } else {
    cTab.className = "px-2.5 py-1 text-[10px] rounded bg-orange-500/10 text-orange-400 font-bold border border-orange-500/20 transition-all";
    pTab.className = "px-2.5 py-1 text-[10px] rounded text-gray-500 font-semibold hover:text-white transition-all border border-transparent";
    cContainer.classList.remove('hidden');
    pContainer.classList.add('hidden');
    filterByCountry(currentCountryFilter);
  }
}

// Extraction du pays basé sur la catégorie
function getCountryFromCategory(categoryName) {
  const match = categoryName.match(/(France|USA|UK|Brazil|India|Russia|Germany|Nigeria|Arab|Africa|Global)/i);
  return match ? match[1].charAt(0).toUpperCase() + match[1].slice(1).toLowerCase() : null;
}

let currentCountryFilter = 'All';

// Filtrage par pays sélectionné
function filterByCountry(country) {
  currentCountryFilter = country;
  
  document.querySelectorAll('.country-chip').forEach(chip => {
    if (chip.dataset.country === country) {
      chip.style.background = 'rgba(249,115,22,0.08)'; // orange-500/10
      chip.style.borderColor = '#fb923c'; // orange-400
      chip.querySelector('span.text-white').style.color = '#fb923c';
    } else {
      chip.style.background = 'rgba(255,255,255,0.01)';
      chip.style.borderColor = '#1a2332';
      chip.querySelector('span.text-white').style.color = '#ffffff';
    }
  });

  const catSelect = document.getElementById('category_select');
  catSelect.innerHTML = '';

  const matchingCategories = new Set();
  parsedServicesList.forEach(s => {
    if (country === 'All' || s.country === country) {
      matchingCategories.add(s.categoryName);
    }
  });

  if (matchingCategories.size === 0) {
    catSelect.innerHTML = '<option value="">— Aucun service disponible —</option>';
    document.getElementById('service_id').innerHTML = '';
    return;
  }

  matchingCategories.forEach(catName => {
    const opt = document.createElement('option');
    opt.value = catName;
    opt.textContent = catName;
    catSelect.appendChild(opt);
  });

  onCategoryChanged();
}

// Filtrage par plateforme sélectionnée
function filterByPlatform(platformKey) {
  currentPlatformFilter = platformKey;
  
  // Activer visuellement la chip active
  document.querySelectorAll('.platform-chip').forEach(chip => {
    if (chip.dataset.platform === platformKey) {
      chip.style.background = 'rgba(0,255,136,0.08)';
      chip.style.borderColor = '#00ff88';
      chip.querySelector('span.text-white').style.color = '#00ff88';
    } else {
      chip.style.background = 'rgba(255,255,255,0.01)';
      chip.style.borderColor = '#1a2332';
      chip.querySelector('span.text-white').style.color = '#ffffff';
    }
  });

  // Filtrer les catégories correspondantes
  const catSelect = document.getElementById('category_select');
  catSelect.innerHTML = '';

  const matchingCategories = new Set();
  parsedServicesList.forEach(s => {
    if (platformKey === 'All' || s.platform === platformKey) {
      matchingCategories.add(s.categoryName);
    }
  });

  if (matchingCategories.size === 0) {
    catSelect.innerHTML = '<option value="">— Aucun service disponible —</option>';
    document.getElementById('service_id').innerHTML = '';
    return;
  }

  matchingCategories.forEach(catName => {
    const opt = document.createElement('option');
    opt.value = catName;
    opt.textContent = catName;
    catSelect.appendChild(opt);
  });

  onCategoryChanged();
}

// Changement de catégorie sur le formulaire
function onCategoryChanged() {
  if (currentActiveTab === 'favorites') {
    onFavoritesCategoryChanged();
    return;
  }
  const catName = document.getElementById('category_select').value;
  const svcSelect = document.getElementById('service_id');
  svcSelect.innerHTML = '';

  const filtered = parsedServicesList.filter(s => s.categoryName === catName);

  filtered.forEach(svc => {
    const opt = document.createElement('option');
    opt.value = svc.id;
    opt.dataset.priceUsd = svc.calculated_rate;
    opt.dataset.min = svc.min_quantity;
    opt.dataset.max = svc.max_quantity;
    opt.dataset.name = svc.name;
    opt.textContent = `ID:${svc.id} — ${svc.name} — ${formatCurrency(parseFloat(svc.calculated_rate), 3)}/1000`;
    svcSelect.appendChild(opt);
  });

  onServiceChanged();
}

// Générateur automatique de description haut de gamme basé sur le service
function generateDescription(svcName, platform) {
  const name = svcName.toLowerCase();
  let start = 'Instantané (0-15 min)';
  let speed = '15 000 par jour';
  let refill = 'Garantie de recharge 30 jours';

  if (name.includes('views') || name.includes('vues') || name.includes('play')) {
    start = 'Rapide (0-1 heure)';
    speed = '50 000 - 100 000 / jour';
    refill = 'Garantie Sans perte (À vie)';
  } else if (name.includes('likes') || name.includes('j\'aime') || name.includes('coeurs')) {
    start = 'Instantané (0-5 min)';
    speed = '20 000 / jour';
    refill = 'Bouton de recharge actif';
  } else if (name.includes('followers') || name.includes('abonnés') || name.includes('membres')) {
    start = 'Progressif (1-12 heures)';
    speed = '5 000 - 10 000 / jour';
    refill = 'Garantie de non-diminution 30 jours';
  } else if (name.includes('custom') || name.includes('comment') || name.includes('avis')) {
    start = 'Manuel (1-2 heures)';
    speed = '1 000 / jour';
    refill = '100% profils réels';
  }

  return { start, speed, refill };
}

// Fonction pour mettre à jour le placeholder du lien selon le service et la plateforme
function updateLinkPlaceholder(svcName, platform) {
  const linkInput = document.getElementById('link');
  if (!linkInput) return;

  const name = svcName.toLowerCase();
  const plat = platform || '';

  const defaultPlaceholders = {
    YouTube: 'https://www.youtube.com/watch?v=abcdef',
    Instagram: 'https://www.instagram.com/monprofil',
    TikTok: 'https://www.tiktok.com/@monprofil',
    Facebook: 'https://www.facebook.com/monprofil',
    Telegram: 'https://t.me/moncanal',
    LinkedIn: 'https://www.linkedin.com/in/monprofil',
    Snapchat: 'https://www.snapchat.com/add/moncompte',
    Spotify: 'https://open.spotify.com/track/abcdef',
    Threads: 'https://www.threads.net/@monprofil',
    SEO: 'https://www.mon-site-web.com',
    Autre: 'https://www.exemple.com/lien'
  };

  if (plat === 'YouTube') {
    if (name.includes('subscriber') || name.includes('abon') || name.includes('chaine') || name.includes('channel')) {
      linkInput.placeholder = 'https://www.youtube.com/@nom_de_chaine';
    } else {
      linkInput.placeholder = 'https://www.youtube.com/watch?v=abcdef';
    }
  } else if (plat === 'Instagram') {
    if (name.includes('like') || name.includes('vue') || name.includes('view') || name.includes('post') || name.includes('photo') || name.includes('video') || name.includes('reel')) {
      linkInput.placeholder = 'https://www.instagram.com/p/abcdef';
    } else {
      linkInput.placeholder = 'https://www.instagram.com/monprofil';
    }
  } else if (plat === 'TikTok') {
    if (name.includes('vue') || name.includes('view') || name.includes('like') || name.includes('video') || name.includes('share')) {
      linkInput.placeholder = 'https://www.tiktok.com/@monprofil/video/123456789';
    } else {
      linkInput.placeholder = 'https://www.tiktok.com/@monprofil';
    }
  } else if (plat === 'Facebook') {
    if (name.includes('like') || name.includes('vue') || name.includes('view') || name.includes('post') || name.includes('photo') || name.includes('video') || name.includes('partage')) {
      linkInput.placeholder = 'https://www.facebook.com/monprofil/posts/123456789';
    } else {
      linkInput.placeholder = 'https://www.facebook.com/monprofil';
    }
  } else if (plat === 'Spotify') {
    if (name.includes('playlist')) {
      linkInput.placeholder = 'https://open.spotify.com/playlist/abcdef';
    } else if (name.includes('artist') || name.includes('artiste')) {
      linkInput.placeholder = 'https://open.spotify.com/artist/abcdef';
    } else {
      linkInput.placeholder = 'https://open.spotify.com/track/abcdef';
    }
  } else {
    linkInput.placeholder = defaultPlaceholders[plat] || defaultPlaceholders['Autre'];
  }
}

// Changement de service sur le formulaire
function onServiceChanged() {
  const svcSelect = document.getElementById('service_id');
  const opt = svcSelect.options[svcSelect.selectedIndex];

  if (!opt) {
    document.getElementById('service-info').classList.add('hidden');
    document.getElementById('service-description-card').classList.add('hidden');
    return;
  }

  const priceUsd = parseFloat(opt.dataset.priceUsd || 0);
  const minQ     = parseInt(opt.dataset.min || 0);
  const maxQ     = parseInt(opt.dataset.max || 0);
  const name     = opt.dataset.name || '';

  // Mettre à jour l'info card
  document.getElementById('info-min').textContent = minQ.toLocaleString();
  document.getElementById('info-max').textContent = maxQ.toLocaleString();
  document.getElementById('info-price').textContent = formatCurrency(priceUsd, 3);
  document.getElementById('service-info').classList.remove('hidden');

  // Générer & hydrater la description
  const plat = getPlatformFromCategory(document.getElementById('category_select').value);
  const desc = generateDescription(name, plat);

  document.getElementById('desc-start-time').textContent = desc.start;
  document.getElementById('desc-speed').textContent      = desc.speed;
  document.getElementById('desc-refill').textContent     = desc.refill;
  document.getElementById('service-description-card').classList.remove('hidden');

  // Mettre à jour le placeholder du lien
  updateLinkPlaceholder(name, plat);

  updatePrice();
  updateFavoriteButtonState();
}

// Calcul du coût et validation du solde
function formatCurrency(amountUsd, decimals = 2) {
  if (activeCurrency === 'CDF') {
    const amtCdf = amountUsd * exchangeRate;
    return Math.round(amtCdf).toLocaleString('fr-FR') + ' CDF';
  }
  return '$' + amountUsd.toFixed(decimals);
}

function updatePrice() {
  const sel      = document.getElementById('service_id');
  const qty      = parseInt(document.getElementById('quantity').value) || 0;
  const opt      = sel.options[sel.selectedIndex];

  if (!opt) return;

  const priceUsd = parseFloat(opt.dataset.priceUsd || 0);
  const preview  = document.getElementById('price-preview');
  const warning  = document.getElementById('balance-warning');
  const btn      = document.getElementById('submit-btn');

  if (priceUsd > 0 && qty > 0) {
    const totalUsd   = (priceUsd * qty) / 1000;
    const balAfter   = userBalanceUsd - totalUsd;

    document.getElementById('total-price').textContent  = formatCurrency(totalUsd, 3);
    document.getElementById('balance-after').textContent = formatCurrency(balAfter, 2);
    document.getElementById('balance-after').style.color = balAfter >= 0 ? '#00d4ff' : '#ef4444';
    preview.classList.remove('hidden');

    if (balAfter < 0) {
      warning.classList.remove('hidden');
      btn.disabled = true;
      btn.style.opacity = '0.5';
      btn.style.cursor  = 'not-allowed';
    } else {
      warning.classList.add('hidden');
      btn.disabled = false;
      btn.style.opacity = '1';
      btn.style.cursor  = 'pointer';
    }
  } else {
    preview.classList.add('hidden');
  }
}

// ================= MODAL SEARCH ENGINE & FILTER LOGIC =================

function openSearchModal() {
  const modal = document.getElementById('search-modal');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
  document.getElementById('modal-search-input').focus();

  // Remplir les sélecteurs de filtre de la modale
  const platSelect = document.getElementById('modal-platform');
  platSelect.innerHTML = '<option value="All">Toutes les Plateformes</option>';
  for (const k in platformMeta) {
    if (k === 'All') continue;
    platSelect.innerHTML += `<option value="${k}">${platformMeta[k].label}</option>`;
  }

  // Sélectionner la plateforme courante du filtre sidebar par défaut
  platSelect.value = currentPlatformFilter;
  onModalPlatformChanged();
}

function closeSearchModal() {
  const modal = document.getElementById('search-modal');
  modal.classList.remove('flex');
  modal.classList.add('hidden');
}

function onModalPlatformChanged() {
  const plat = document.getElementById('modal-platform').value;
  const catSelect = document.getElementById('modal-category');
  catSelect.innerHTML = '<option value="All">Toutes les Catégories</option>';

  const matchingCategories = new Set();
  parsedServicesList.forEach(s => {
    if (plat === 'All' || s.platform === plat) {
      matchingCategories.add(s.categoryName);
    }
  });

  matchingCategories.forEach(catName => {
    catSelect.innerHTML += `<option value="${catName}">${catName}</option>`;
  });

  filterModalServices();
}

function filterModalServices() {
  const query     = document.getElementById('modal-search-input').value.toLowerCase();
  const plat      = document.getElementById('modal-platform').value;
  const cat       = document.getElementById('modal-category').value;
  const priceMin  = parseFloat(document.getElementById('modal-price-min').value) || 0;
  const priceMax  = parseFloat(document.getElementById('modal-price-max').value) || 999999;
  const qtyMin    = parseInt(document.getElementById('modal-qty-min').value) || 0;
  const qtyMax    = parseInt(document.getElementById('modal-qty-max').value) || 99999999;
  const favOnly   = document.getElementById('modal-favorites-checkbox').checked;

  const resultsContainer = document.getElementById('modal-results-container');
  resultsContainer.innerHTML = '';

  const filtered = parsedServicesList.filter(s => {
    if (plat !== 'All' && s.platform !== plat) return false;
    if (cat !== 'All' && s.categoryName !== cat) return false;
    if (query && !s.name.toLowerCase().includes(query) && !s.categoryName.toLowerCase().includes(query)) return false;
    
    const price = parseFloat(s.calculated_rate);
    if (price < priceMin || price > priceMax) return false;
    
    const minQ = parseInt(s.min_quantity);
    const maxQ = parseInt(s.max_quantity);
    if (minQ < qtyMin || maxQ > qtyMax) return false;

    return true;
  });

  document.getElementById('modal-results-count').textContent = filtered.length;

  if (filtered.length === 0) {
    resultsContainer.innerHTML = '<div class="text-center py-12 text-gray-500 text-xs">Aucun service ne correspond à ces critères.</div>';
    return;
  }

  filtered.forEach(s => {
    const meta = platformMeta[s.platform] || platformMeta['Autre'];
    const row = document.createElement('div');
    row.className = "flex items-center gap-3 p-4 hover:bg-white/[0.03] active:bg-white/[0.05] transition-all cursor-pointer border-b";
    row.style.borderColor = '#1a2332';
    row.onclick = () => selectServiceFromModal(s.id, s.categoryName, s.platform);

    row.innerHTML = `
      <div class="w-8 h-8 rounded-full flex items-center justify-center bg-white/[0.03] shrink-0 border border-white/[0.05]">
        ${meta.svg}
      </div>
      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-1.5 flex-wrap">
          <span class="text-[10px] px-1.5 py-0.5 rounded font-mono font-bold bg-emerald-500/10 text-[#00ff88]">ID:${s.id}</span>
          <span class="text-xs font-bold text-white truncate max-w-[250px] sm:max-w-md">${s.name}</span>
        </div>
        <div class="text-[10px] text-gray-500 mt-1 truncate">${s.categoryName} • Min: ${s.min_quantity.toLocaleString()} - Max: ${s.max_quantity.toLocaleString()}</div>
      </div>
      <div class="text-right shrink-0">
        <div class="text-xs font-bold text-[#00ff88]">${formatCurrency(parseFloat(s.calculated_rate), 3)}</div>
        <div class="text-[9px] text-gray-500 uppercase tracking-wider font-semibold mt-0.5">les 1000</div>
      </div>
      <svg class="w-4 h-4 text-gray-600 shrink-0 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
    `;

    resultsContainer.appendChild(row);
  });
}

function selectServiceFromModal(serviceId, categoryName, platform) {
  // 1. Fermer la modale
  closeSearchModal();

  // 2. Sélectionner la plateforme dans le sidebar filtre
  filterByPlatform(platform);

  // 3. Assigner la catégorie
  const catSelect = document.getElementById('category_select');
  catSelect.value = categoryName;
  onCategoryChanged();

  // 4. Assigner le service exact
  const svcSelect = document.getElementById('service_id');
  svcSelect.value = serviceId;
  onServiceChanged();

  // 5. Focaliser et animer la quantité
  const qtyInput = document.getElementById('quantity');
  qtyInput.focus();
  qtyInput.classList.add('ring-2', 'ring-[#00ff88]');
  setTimeout(() => qtyInput.classList.remove('ring-2', 'ring-[#00ff88]'), 1500);
}

// ================= TABS & FAVORITES & SUBSCRIPTIONS LOGIC =================
let currentActiveTab = 'new-order';

function switchTab(tabId) {
  currentActiveTab = tabId;
  
  const tabs = ['new-order', 'favorites', 'mass-order', 'subscription'];
  tabs.forEach(t => {
    const btn = document.getElementById(`tab-${t}`);
    if (!btn) return;
    if (t === tabId) {
      btn.style.background = 'rgba(0,255,136,0.1)';
      btn.style.color = '#00ff88';
    } else {
      btn.style.background = 'transparent';
      btn.style.color = '#a0aec0';
    }
  });

  document.getElementById('new-order-container').classList.add('hidden');
  document.getElementById('mass-order-container').classList.add('hidden');
  document.getElementById('subscription-container').classList.add('hidden');
  document.getElementById('favorites-empty-placeholder').classList.add('hidden');
  
  if (tabId === 'new-order' || tabId === 'favorites') {
    document.getElementById('search-trigger-container').classList.remove('hidden');
  } else {
    document.getElementById('search-trigger-container').classList.add('hidden');
  }

  if (tabId === 'new-order') {
    document.getElementById('new-order-container').classList.remove('hidden');
    document.getElementById('orderForm').classList.remove('hidden');
    document.getElementById('favorite-btn').classList.remove('hidden');
    filterByPlatform(currentPlatformFilter);
  } else if (tabId === 'favorites') {
    document.getElementById('new-order-container').classList.remove('hidden');
    
    const favorites = getFavoriteServices();
    if (favorites.length === 0) {
      document.getElementById('orderForm').classList.add('hidden');
      document.getElementById('favorites-empty-placeholder').classList.remove('hidden');
    } else {
      document.getElementById('orderForm').classList.remove('hidden');
      document.getElementById('favorite-btn').classList.add('hidden');
      hydrateFavoritesForm();
    }
  } else if (tabId === 'mass-order') {
    document.getElementById('mass-order-container').classList.remove('hidden');
  } else if (tabId === 'subscription') {
    document.getElementById('subscription-container').classList.remove('hidden');
    initSubscriptionForm();
  }
}

function getFavoriteServices() {
  return JSON.parse(localStorage.getItem('favorite_services') || '[]');
}

function setFavoriteServices(favorites) {
  localStorage.setItem('favorite_services', JSON.stringify(favorites));
}

function isServiceFavorited(serviceId) {
  return getFavoriteServices().includes(parseInt(serviceId));
}

function toggleFavoriteCurrentService() {
  const svcSelect = document.getElementById('service_id');
  if (!svcSelect) return;
  const svcId = parseInt(svcSelect.value);
  if (!svcId) return;

  let favorites = getFavoriteServices();
  if (favorites.includes(svcId)) {
    favorites = favorites.filter(id => id !== svcId);
    showNotification('Service retiré des favoris !', 'info');
  } else {
    favorites.push(svcId);
    showNotification('Service ajouté aux favoris !', 'success');
  }
  setFavoriteServices(favorites);
  updateFavoriteButtonState();
}

function updateFavoriteButtonState() {
  const svcSelect = document.getElementById('service_id');
  const starIcon = document.getElementById('favorite-star-icon');
  const btnText = document.getElementById('favorite-btn-text');
  if (!svcSelect || !starIcon || !btnText) return;

  const svcId = parseInt(svcSelect.value);
  if (!svcId) {
    document.getElementById('favorite-btn').style.opacity = '0.5';
    return;
  }
  document.getElementById('favorite-btn').style.opacity = '1';

  if (isServiceFavorited(svcId)) {
    starIcon.style.color = '#eab308';
    btnText.textContent = 'Retirer des Favoris';
    btnText.style.color = '#eab308';
  } else {
    starIcon.style.color = '#6b7280';
    btnText.textContent = 'Ajouter aux Favoris';
    btnText.style.color = '#6b7280';
  }
}

function hydrateFavoritesForm() {
  const catSelect = document.getElementById('category_select');
  if (!catSelect) return;
  catSelect.innerHTML = '';
  
  const favorites = getFavoriteServices();
  const favoriteServices = parsedServicesList.filter(s => favorites.includes(parseInt(s.id)));
  
  const matchingCategories = new Set();
  favoriteServices.forEach(s => {
    matchingCategories.add(s.categoryName);
  });

  if (matchingCategories.size === 0) {
    catSelect.innerHTML = '<option value="">— Aucun favori trouvé —</option>';
    document.getElementById('service_id').innerHTML = '';
    return;
  }

  matchingCategories.forEach(catName => {
    const opt = document.createElement('option');
    opt.value = catName;
    opt.textContent = catName;
    catSelect.appendChild(opt);
  });

  onFavoritesCategoryChanged();
}

function onFavoritesCategoryChanged() {
  const catName = document.getElementById('category_select').value;
  const svcSelect = document.getElementById('service_id');
  if (!svcSelect) return;
  svcSelect.innerHTML = '';

  const favorites = getFavoriteServices();
  const filtered = parsedServicesList.filter(s => s.categoryName === catName && favorites.includes(parseInt(s.id)));

  filtered.forEach(svc => {
    const opt = document.createElement('option');
    opt.value = svc.id;
    opt.dataset.priceUsd = svc.calculated_rate;
    opt.dataset.min = svc.min_quantity;
    opt.dataset.max = svc.max_quantity;
    opt.dataset.name = svc.name;
    opt.textContent = `ID:${svc.id} — ${svc.name} — ${formatCurrency(parseFloat(svc.calculated_rate), 3)}/1000`;
    svcSelect.appendChild(opt);
  });

  onServiceChanged();
}

function initSubscriptionForm() {
  const catSelect = document.getElementById('sub_category_select');
  if (!catSelect) return;
  catSelect.innerHTML = '';

  const matchingCategories = new Set();
  parsedServicesList.forEach(s => {
    matchingCategories.add(s.categoryName);
  });

  matchingCategories.forEach(catName => {
    const opt = document.createElement('option');
    opt.value = catName;
    opt.textContent = catName;
    catSelect.appendChild(opt);
  });

  onSubCategoryChanged();
}

function onSubCategoryChanged() {
  const catName = document.getElementById('sub_category_select').value;
  const svcSelect = document.getElementById('sub_service_id');
  if (!svcSelect) return;
  svcSelect.innerHTML = '';

  const filtered = parsedServicesList.filter(s => s.categoryName === catName);

  filtered.forEach(svc => {
    const opt = document.createElement('option');
    opt.value = svc.id;
    opt.textContent = `ID:${svc.id} — ${svc.name} — ${formatCurrency(parseFloat(svc.calculated_rate), 3)}/1000`;
    svcSelect.appendChild(opt);
  });

  onSubServiceChanged();
}

function onSubServiceChanged() {
  // Subscription rate representation or notes can go here if needed.
}

function showNotification(message, type = 'success') {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'fixed bottom-5 right-5 z-[200] flex flex-col gap-2 pointer-events-none';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = 'px-4 py-3 rounded-xl text-xs font-bold text-white shadow-xl transition-all duration-300 transform translate-y-2 opacity-0 pointer-events-auto border flex items-center gap-2';
  
  if (type === 'success') {
    toast.style.background = 'rgba(0,255,136,0.1)';
    toast.style.borderColor = 'rgba(0,255,136,0.3)';
    toast.style.color = '#00ff88';
    toast.innerHTML = '<span></span> ' + message;
  } else {
    toast.style.background = 'rgba(0,212,255,0.1)';
    toast.style.borderColor = 'rgba(0,212,255,0.3)';
    toast.style.color = '#00d4ff';
    toast.innerHTML = '<span>ℹ️</span> ' + message;
  }

  container.appendChild(toast);

  setTimeout(() => {
    toast.classList.remove('translate-y-2', 'opacity-0');
  }, 10);

  setTimeout(() => {
    toast.classList.add('opacity-0', 'translate-y-2');
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

function handleUrlServiceSelection() {
  const urlParams = new URLSearchParams(window.location.search);
  const serviceId = urlParams.get('service_id');
  if (!serviceId) return;

  const svc = parsedServicesList.find(s => s.id == serviceId);
  if (!svc) return;

  // Use the robust modal selection helper
  selectServiceFromModal(svc.id, svc.categoryName, svc.platform);
  
  // Show a nice Toast notification to guide the user
  showNotification(`Service "${svc.name}" sélectionné ! Remplissez les informations pour commander.`, 'success');
}

// Initialiser le dashboard au chargement
window.addEventListener('DOMContentLoaded', () => {
  initDashboardFilters();
  updateFavoriteButtonState();
  handleUrlServiceSelection();
});
</script>
