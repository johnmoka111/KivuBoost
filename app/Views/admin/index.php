<?php
use App\Core\Auth;
use App\Core\Currency;
$pageTitle = 'Administration sécurisée';
?>

<!-- Titre Principal -->
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
      <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
      Espace Administration
    </h1>
    <p class="text-gray-500 text-xs mt-0.5">KivuBoost · Gestion multi-fournisseurs et tarification dynamique</p>
  </div>
  <div class="flex items-center gap-3">
    <!-- Solde API Fournisseur Actif -->
    <div class="px-4 py-2 rounded-xl border flex items-center gap-3 animate-pulse" id="provider-balance-card" style="background:#0d1117;border-color:#1a2332">
      <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:rgba(0,255,136,0.1)">
        <svg class="w-4 h-4 text-[#00ff88] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
      </div>
      <div>
        <select id="provider-balance-select" class="bg-transparent border-none text-[10px] text-gray-500 uppercase tracking-wider font-semibold focus:ring-0 p-0 cursor-pointer w-auto outline-none appearance-none" onchange="fetchProviderBalance(this.value)">
          <?php foreach ($allProviders as $p): ?>
            <?php if ($p['status'] == 1): ?>
              <option value="<?= $p['id'] ?>">ACTIF : <?= htmlspecialchars($p['name']) ?></option>
            <?php endif; ?>
          <?php endforeach; ?>
        </select>
        <div class="text-sm font-bold font-mono flex items-center gap-1.5" style="color:#00ff88" id="provider-balance-value">
          <span class="w-3.5 h-3.5 rounded-full border-2 border-[#00ff88]/30 border-t-[#00ff88] animate-spin"></span>
          <span class="text-xs text-gray-500 font-normal">Connexion...</span>
        </div>
      </div>
    </div>
    <!-- Bouton Rédiger actualité -->
    <a href="<?= APP_BASE ?>/admin/actualites"
       class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-black hover:opacity-90 transition-all shrink-0"
       style="background:#00ff88">
      <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
      </svg>
      <span class="hidden sm:inline">Rédiger une actualité</span>
      <span class="sm:hidden">Actualité</span>
    </a>
  </div>
</div>

<!-- ===== WIDGETS STATS GLOBALES ===== -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
  <!-- Recharge Pending -->
  <div class="rounded-xl p-4 border" style="background:#0d1117;border-color:#1a2332">
    <div class="text-xs text-gray-500 mb-1 uppercase tracking-wider">Dépôts en attente</div>
    <div class="text-2xl font-bold text-yellow-400"><?= (int)$stats['pending_recharges'] ?></div>
    <div class="text-[10px] text-gray-600 mt-1">Nécessite validation</div>
  </div>

  <!-- Total encaissé localement -->
  <div class="rounded-xl p-4 border" style="background:#0d1117;border-color:#1a2332">
    <div class="text-xs text-gray-500 mb-1 uppercase tracking-wider">Total Encaissé</div>
    <div class="text-2xl font-bold text-emerald-400 font-mono"><?= Currency::format((float)$stats['total_deposited']) ?></div>
    <div class="text-[10px] text-gray-600 mt-1">Recharges approuvées</div>
  </div>

  <!-- Commandes Traitées -->
  <div class="rounded-xl p-4 border" style="background:#0d1117;border-color:#1a2332">
    <div class="text-xs text-gray-500 mb-1 uppercase tracking-wider">Commandes Clients</div>
    <div class="text-2xl font-bold text-white"><?= (int)$stats['total_orders'] ?></div>
    <div class="text-[10px] text-gray-600 mt-1">Envoyées ou complétées</div>
  </div>

  <!-- Chiffre d'Affaires -->
  <div class="rounded-xl p-4 border" style="background:#0d1117;border-color:#1a2332">
    <div class="text-xs text-gray-500 mb-1 uppercase tracking-wider">Chiffre d'Affaires</div>
    <div class="text-2xl font-bold text-blue-400 font-mono"><?= Currency::format((float)$stats['total_revenue']) ?></div>
    <div class="text-[10px] text-gray-600 mt-1">Valeur des commandes</div>
  </div>
</div>

<!-- ===== NAVIGATION PAR ONGLETS (TABS) ===== -->
<div class="flex flex-wrap gap-2.5 mb-8 select-none" id="adminTabs">
  <button onclick="switchTab('recharges')" class="tab-btn px-4 py-2.5 text-sm font-semibold rounded-xl border border-transparent bg-transparent text-gray-400 hover:bg-white/5 transition-all flex items-center gap-1.5" id="tab-recharges">
    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
    Dépôts (<?= count($pendingRecharges) ?>)
  </button>
  <button onclick="switchTab('services')" class="tab-btn px-4 py-2.5 text-sm font-semibold rounded-xl border border-transparent bg-transparent text-gray-400 hover:bg-white/5 transition-all flex items-center gap-1.5" id="tab-services">
    <svg class="w-4 h-4 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
    Gestion des Tarifs
  </button>
  <button onclick="switchTab('service-mgmt')" class="tab-btn px-4 py-2.5 text-sm font-semibold rounded-xl border border-transparent bg-transparent text-gray-400 hover:bg-white/5 transition-all flex items-center gap-1.5" id="tab-service-mgmt">
    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
    Gestion des Services
  </button>
  <button onclick="switchTab('providers')" class="tab-btn px-4 py-2.5 text-sm font-semibold rounded-xl border border-transparent bg-transparent text-gray-400 hover:bg-white/5 transition-all flex items-center gap-1.5" id="tab-providers">
    <svg class="w-4 h-4 text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
    Grossistes & Multi-API
  </button>
  <button onclick="switchTab('users')" class="tab-btn px-4 py-2.5 text-sm font-semibold rounded-xl border border-transparent bg-transparent text-gray-400 hover:bg-white/5 transition-all flex items-center gap-1.5" id="tab-users">
    <svg class="w-4 h-4 text-orange-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
    Clients & Soldes
  </button>
  <button onclick="switchTab('orders')" class="tab-btn px-4 py-2.5 text-sm font-semibold rounded-xl border border-transparent bg-transparent text-gray-400 hover:bg-white/5 transition-all flex items-center gap-1.5" id="tab-orders">
    <svg class="w-4 h-4 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
    Commandes Clients
  </button>
</div>

<!-- ========================================== -->
<!-- 1. ONGLET: RECHARGES PENDING -->
<!-- ========================================== -->
<div id="content-recharges" class="tab-content">
  <div class="rounded-2xl border overflow-hidden" style="background:#0d1117;border-color:#1a2332">
    <div class="px-5 py-4 border-b border-[#1a2332] flex items-center justify-between">
      <h2 class="font-bold text-white text-sm">Demandes de recharge en attente de vérification</h2>
      <span class="text-xs text-yellow-500 font-semibold"><?= count($pendingRecharges) ?> en attente</span>
    </div>

    <?php if (empty($pendingRecharges)): ?>
      <div class="text-center py-16 text-gray-600">
        <svg class="w-12 h-12 text-[#00ff88] mx-auto mb-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-sm">Aucune demande de recharge en attente.</p>
        <p class="text-xs mt-0.5 text-gray-600">Tous les clients sont servis !</p>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto w-full">
        <table class="w-full text-sm text-left">
          <thead>
            <tr class="text-gray-500 border-b border-[#1a2332] bg-[#0a0f1a]/50 text-xs uppercase tracking-wider">
              <th class="px-5 py-3">Client</th>
              <th class="px-5 py-3">Montant</th>
              <th class="px-5 py-3">Réseau & Référence</th>
              <th class="px-5 py-3">Date</th>
              <th class="px-5 py-3 text-right">Actions rapides</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#1a2332]">
            <?php foreach ($pendingRecharges as $r): ?>
            <tr class="hover:bg-white/[0.01] transition-all">
              <td class="px-5 py-4">
                <div class="font-medium text-white"><?= htmlspecialchars($r['username']) ?></div>
                <div class="text-xs text-gray-500"><?= htmlspecialchars($r['email']) ?></div>
              </td>
              <td class="px-5 py-4">
                <div class="text-emerald-400 font-bold font-mono">$<?= number_format((float)$r['amount'], 2) ?></div>
              </td>
              <td class="px-5 py-4">
                <span class="inline-block text-xs bg-white/5 border border-white/10 rounded px-2 py-0.5 text-gray-300 font-medium mb-1">
                  <?= htmlspecialchars($r['network']) ?>
                </span>
                <div class="text-xs text-gray-400 font-mono select-all"><?= htmlspecialchars($r['transaction_id']) ?></div>
              </td>
              <td class="px-5 py-4 text-xs text-gray-500">
                <?= date('d/m/Y H:i', strtotime($r['created_at'])) ?>
              </td>
              <td class="px-5 py-4 text-right">
                <div class="inline-flex gap-2">
                  <form method="POST" action="<?= APP_BASE ?>/admin/recharge/approve">
                    <?= Auth::csrfField() ?>
                    <input type="hidden" name="recharge_id" value="<?= $r['id'] ?>">
                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold text-black hover:opacity-90 transition-all shrink-0" style="background:#00ff88">
                      Approuver
                    </button>
                  </form>
                  <form method="POST" action="<?= APP_BASE ?>/admin/recharge/reject" onsubmit="return promptRejectReason(this)">
                    <?= Auth::csrfField() ?>
                    <input type="hidden" name="recharge_id" value="<?= $r['id'] ?>">
                    <input type="hidden" name="reason" id="reason-<?= $r['id'] ?>" value="">
                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold text-red-400 bg-red-500/10 border border-red-500/30 hover:bg-red-500/20 transition-all shrink-0">
                      Rejeter
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- ========================================== -->
<!-- 2. ONGLET: GESTION DES TARIFS -->
<!-- ========================================== -->
<div id="content-services" class="tab-content hidden">
  <div class="rounded-2xl border" style="background:#0d1117;border-color:#1a2332">
    <div class="px-5 py-4 border-b border-[#1a2332] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
      <div>
        <h3 class="font-bold text-white text-sm">Gestion des Tarifs Globaux</h3>
        <p class="text-xs text-gray-500 mt-0.5">Ajustez les prix de vente pour les clients locaux de Bukavu</p>
      </div>
      <div class="flex items-center gap-2">
        <form method="POST" action="<?= APP_BASE ?>/admin/services/sync" class="flex items-center gap-2">
          <?= Auth::csrfField() ?>
          <select name="provider_id" required class="bg-[#0a0f1a] border border-[#1a2332] rounded-lg px-2.5 py-1.5 text-xs text-gray-300">
            <option value="">— Grossiste —</option>
            <?php foreach ($allProviders as $p): ?>
              <?php if ($p['status'] == 1): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
              <?php endif; ?>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold text-black flex items-center gap-1.5 hover:opacity-90" style="background:#00d4ff">
            <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18"/></svg>
            Synchro
          </button>
        </form>
        <button type="button" onclick="switchTab('service-mgmt')" class="px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 hover:opacity-90" style="background:rgba(0,255,136,0.12);color:#00ff88;border:1px solid rgba(0,255,136,0.3)">
          <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
          Ajouter un service
        </button>
      </div>
    </div>

    <!-- ====== BARRE DE FILTRAGE ====== -->
    <div class="px-4 py-3 border-b space-y-2.5" style="border-color:#1a2332;background:#0a0f18">
      <div class="flex flex-wrap items-center gap-2">
        <button type="button"
                class="filter-provider active px-3 py-1.5 rounded-lg text-xs font-bold border transition-all"
                data-provider="all"
                style="background:rgba(0,255,136,0.12);color:#00ff88;border-color:rgba(0,255,136,0.35)">
          Tous les fournisseurs
        </button>
        <!-- Pills par grossiste (uniquement les fournisseurs actifs) -->
        <?php foreach ($allProviders as $p): ?>
        <?php if ($p['status'] != 1) continue; ?>
        <button type="button"
                class="filter-provider px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all hover:border-[#00d4ff]/50 hover:text-white"
                data-provider="<?= $p['id'] ?>"
                style="background:#0d1117;color:#9ca3af;border-color:#1a2332">
          <?= htmlspecialchars($p['name']) ?>
          <span class="ml-1 text-[9px] font-normal opacity-60 provider-count-<?= $p['id'] ?>"></span>
        </button>
        <?php endforeach; ?>

        <div class="relative ml-auto flex items-center gap-2">
          <select id="svc-sort" class="bg-[#0d1117] border border-[#1a2332] text-gray-400 text-[10px] font-bold rounded-lg px-2 py-1.5 focus:outline-none focus:border-[#00ff88]/40 uppercase tracking-wider">
            <option value="default">Trier par</option>
            <option value="price_asc">Achat : Moins cher d'abord</option>
            <option value="price_desc">Achat : Plus cher d'abord</option>
          </select>
          <div class="relative">
            <svg class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="search" id="svc-search" placeholder="Rechercher un service..." autocomplete="off"
                   class="bg-[#0d1117] border border-[#1a2332] text-gray-200 text-xs rounded-lg pl-8 pr-3 py-1.5 w-48 focus:outline-none focus:border-[#00ff88]/40 placeholder-gray-600">
          </div>
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-1.5">
        <?php
        $platforms = [
          ['key'=>'all',       'label'=>'Toutes',    'color'=>'#a0aec0'],
          ['key'=>'youtube',   'label'=>'YouTube',   'color'=>'#ef4444'],
          ['key'=>'instagram', 'label'=>'Instagram', 'color'=>'#ec4899'],
          ['key'=>'tiktok',    'label'=>'TikTok',    'color'=>'#06b6d4'],
          ['key'=>'facebook',  'label'=>'Facebook',  'color'=>'#3b82f6'],
          ['key'=>'spotify',   'label'=>'Spotify',   'color'=>'#22c55e'],
          ['key'=>'twitter',   'label'=>'Twitter/X', 'color'=>'#e2e8f0'],
          ['key'=>'telegram',  'label'=>'Telegram',  'color'=>'#38bdf8'],
          ['key'=>'snapchat',  'label'=>'Snapchat',  'color'=>'#eab308'],
          ['key'=>'linkedin',  'label'=>'LinkedIn',  'color'=>'#1d4ed8'],
          ['key'=>'threads',   'label'=>'Threads',   'color'=>'#d1d5db'],
          ['key'=>'autre',     'label'=>'Autre',     'color'=>'#8b5cf6'],
        ];
        foreach ($platforms as $pl): ?>
        <button type="button"
                class="filter-platform px-2.5 py-1 rounded-full text-[10px] font-bold border transition-all hover:opacity-100 <?= $pl['key'] === 'all' ? 'active' : 'opacity-60' ?>"
                data-platform="<?= $pl['key'] ?>"
                style="color:<?= $pl['color'] ?>;border-color:<?= $pl['color'] ?>40;background:<?= $pl['color'] ?>18">
          <?= $pl['label'] ?>
        </button>
        <?php endforeach; ?>

        <div class="w-px h-4 bg-[#1a2332] mx-1"></div>
        <button type="button" class="filter-visibility px-2.5 py-1 rounded-full text-[10px] font-bold border transition-all opacity-60" data-visibility="all"
                style="color:#a0aec0;border-color:#a0aec040;background:#a0aec018">Tous</button>
        <button type="button" class="filter-visibility active px-2.5 py-1 rounded-full text-[10px] font-bold border transition-all" data-visibility="1"
                style="color:#00ff88;border-color:#00ff8840;background:#00ff8818">Visibles</button>
        <button type="button" class="filter-visibility px-2.5 py-1 rounded-full text-[10px] font-bold border transition-all opacity-60" data-visibility="0"
                style="color:#ef4444;border-color:#ef444440;background:#ef444418">Masqués</button>
        <span id="filter-count" class="ml-auto text-[10px] text-gray-500 font-mono"></span>
      </div>
    </div>
    <!-- ====== FIN BARRE DE FILTRAGE ====== -->

    <!-- Barre d'actions en lot -->
    <div id="bulk-action-bar" class="hidden px-4 py-2.5 border-b flex items-center gap-3 flex-wrap" style="border-color:#1a2332;background:rgba(0,212,255,0.04)">
      <span class="text-xs font-semibold text-[#00d4ff] flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <span id="bulk-count">0</span> service(s) sélectionné(s)
      </span>
      <div class="flex items-center gap-2 ml-auto flex-wrap">
        <button type="button" onclick="bulkToggle(1)" class="px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 transition-all hover:brightness-110" style="background:rgba(0,255,136,0.15);color:#00ff88;border:1px solid rgba(0,255,136,0.3)">
          <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
          Rendre Visible
        </button>
        <button type="button" onclick="bulkToggle(0)" class="px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 transition-all hover:brightness-110" style="background:rgba(245,158,11,0.15);color:#f59e0b;border:1px solid rgba(245,158,11,0.3)">
          <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
          Masquer aux Clients
        </button>
        <button type="button" onclick="bulkDelete()" class="px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 transition-all hover:brightness-110" style="background:rgba(239,68,68,0.15);color:#ef4444;border:1px solid rgba(239,68,68,0.3)">
          <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
          Supprimer la Sélection
        </button>
      </div>
    </div>
    <div class="overflow-x-auto w-full max-h-[600px] overflow-y-auto">
      <table class="w-full text-sm text-left">
        <thead>
          <tr class="text-gray-500 border-b border-[#1a2332] text-xs uppercase bg-[#0a0f1a]/50">
            <th class="px-2 py-2 w-8"><input type="checkbox" id="chk-all" class="rounded bg-[#0a0f1a] border-[#1a2332] text-emerald-500" title="Tout sélectionner"></th>
            <th class="px-2 py-2">Catégorie &amp; Nom</th>
            <th class="px-2 py-2">Grossiste</th>
            <th class="px-2 py-2">Prix d'Achat</th>
            <th class="px-2 py-2">Prix de Vente</th>
            <th class="px-2 py-2">Marge / 1k</th>
            <th class="px-2 py-2 text-center">Visible</th>
            <th class="px-2 py-2 text-right">Actions</th>
          </tr>
        </thead>
        <tbody id="services-tbody" class="divide-y divide-[#1a2332]">
          <!-- Rendu progressif en JS -->
        </tbody>
        <script>
          window.ALL_SERVICES = <?= json_encode($allServices) ?>;
          window.CSRF_FIELD = <?= json_encode(Auth::csrfField()) ?>;
        </script>
      </table>
    </div>
  </div>
</div>

<!-- ========================================== -->
<!-- 2b. ONGLET: GESTION DES SERVICES -->
<!-- ========================================== -->
<div id="content-service-mgmt" class="tab-content hidden">
  <div class="max-w-2xl mx-auto">
    <!-- En-tête -->
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h2 id="form-service-title" class="text-lg font-bold text-white">Créer un service</h2>
        <p class="text-xs text-gray-500 mt-0.5">Ajoutez ou modifiez un service manuellement dans le catalogue</p>
      </div>
      <button type="button" id="btn-cancel-svc" onclick="cancelServiceEdit()" class="hidden items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold border transition-all" style="border-color:#1a2332;color:#94a3b8;background:transparent">
        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        Annuler les modifications
      </button>
    </div>

    <form method="POST" action="<?= APP_BASE ?>/admin/services/save" id="form-service" class="rounded-2xl border p-6 space-y-5" style="background:#0d1117;border-color:#1a2332">
      <?= Auth::csrfField() ?>
      <input type="hidden" name="id" id="svc-id" value="0">

      <!-- Grossiste -->
      <div>
        <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider" for="svc-provider">Fournisseur lié</label>
        <select name="provider_id" id="svc-provider" required class="input-field w-full px-3 py-2.5 rounded-xl text-sm">
          <option value="">— Sélectionner le grossiste —</option>
          <?php foreach ($allProviders as $p): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (<?= $p['status'] ? 'Actif' : 'Maintenance' ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Catégorie + ID Externe -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider" for="svc-category">Catégorie</label>
          <input type="text" name="category" id="svc-category" required placeholder="Instagram, TikTok..." class="input-field w-full px-3 py-2.5 rounded-xl text-sm">
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider" for="svc-external-id">ID Service Externe</label>
          <input type="number" name="external_service_id" id="svc-external-id" required placeholder="125" class="input-field w-full px-3 py-2.5 rounded-xl text-sm font-mono">
        </div>
      </div>

      <!-- Nom du service -->
      <div>
        <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider" for="svc-name">Nom du service</label>
        <input type="text" name="name" id="svc-name" required placeholder="ex: Abonnés réels Bukavu..." class="input-field w-full px-3 py-2.5 rounded-xl text-sm">
      </div>

      <!-- Quantités -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider" for="svc-min">Quantité Min</label>
          <input type="number" name="min_quantity" id="svc-min" required value="10" class="input-field w-full px-3 py-2.5 rounded-xl text-sm font-mono">
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider" for="svc-max">Quantité Max</label>
          <input type="number" name="max_quantity" id="svc-max" required value="10000" class="input-field w-full px-3 py-2.5 rounded-xl text-sm font-mono">
        </div>
      </div>

      <!-- Prix -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider" for="svc-buying">Prix d'Achat ($)</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">$</span>
            <input type="number" name="original_rate" id="svc-buying" step="0.0001" required placeholder="0.1200" class="input-field w-full pl-7 pr-3 py-2.5 rounded-xl text-sm font-mono">
          </div>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider" for="svc-selling">Prix de Vente ($)</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">$</span>
            <input type="number" name="calculated_rate" id="svc-selling" step="0.0001" required placeholder="1.5000" class="input-field w-full pl-7 pr-3 py-2.5 rounded-xl text-sm font-mono">
          </div>
        </div>
      </div>

      <!-- Statut -->
      <div class="flex items-center gap-3 p-3 rounded-xl" style="background:#0a0f1a;border:1px solid #1a2332">
        <input type="checkbox" name="is_active" id="svc-active" value="1" checked class="rounded border-[#1a2332] bg-[#0a0f1a] text-emerald-500 focus:ring-emerald-500/20">
        <div>
          <label class="text-sm font-semibold text-white cursor-pointer" for="svc-active">Service actif et visible</label>
          <p class="text-xs text-gray-500 mt-0.5">Les clients pourront commander ce service depuis le dashboard</p>
        </div>
      </div>

      <!-- Bouton -->
      <div class="pt-2">
        <button type="submit" class="btn-primary w-full py-3 rounded-xl text-sm font-bold">Enregistrer le service</button>
      </div>
    </form>
  </div>
</div>


<!-- ========================================== -->
<!-- 3. ONGLET: GROSSISTES & MULTI-API -->
<!-- ========================================== -->
<div id="content-providers" class="tab-content hidden">
  <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <!-- Liste des fournisseurs -->
    <div class="xl:col-span-2 rounded-2xl border" style="background:#0d1117;border-color:#1a2332">
      <div class="px-5 py-4 border-b border-[#1a2332]">
        <h3 class="font-bold text-white text-sm">Fournisseurs SMM Partenaires</h3>
      </div>
      <div class="overflow-x-auto w-full">
        <table class="w-full text-sm text-left">
          <thead>
            <tr class="text-gray-500 border-b border-[#1a2332] text-xs uppercase bg-[#0a0f1a]/50">
              <th class="px-5 py-3">Nom</th>
              <th class="px-5 py-3">URL API</th>
              <th class="px-5 py-3">Statut serveur</th>
              <th class="px-5 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#1a2332]">
            <?php foreach ($allProviders as $p): ?>
            <tr class="hover:bg-white/[0.01] transition-all">
              <td class="px-5 py-4 font-semibold text-white">
                <?= htmlspecialchars($p['name']) ?>
              </td>
              <td class="px-5 py-4 font-mono text-xs text-gray-400 select-all">
                <?= htmlspecialchars($p['api_url']) ?>
              </td>
              <td class="px-5 py-4">
                <span class="text-xs px-2 py-0.5 rounded font-bold uppercase
                  <?= $p['status'] ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20' ?>">
                  <?= $p['status'] ? 'Opérationnel' : 'Hors-ligne' ?>
                </span>
              </td>
              <td class="px-5 py-4 text-right">
                <div class="inline-flex gap-2">
                  <button type="button" onclick="editProvider(<?= htmlspecialchars(json_encode($p)) ?>)" class="text-xs text-blue-400 hover:underline">
                    Modifier
                  </button>
                  <form method="POST" action="<?= APP_BASE ?>/admin/providers/delete" onsubmit="return confirm('Supprimer ce fournisseur ? Tous les services associés seront effacés.')">
                    <?= Auth::csrfField() ?>
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button type="submit" class="text-xs text-red-500 hover:underline">
                      Supprimer
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Formulaire d'ajout de fournisseur -->
    <div class="rounded-2xl border p-5 h-fit" style="background:#0d1117;border-color:#1a2332">
      <h3 id="form-prov-title" class="font-bold text-white text-sm mb-4">Ajouter un Fournisseur SMM</h3>
      <form method="POST" action="<?= APP_BASE ?>/admin/providers/save" id="form-provider" class="space-y-4">
        <?= Auth::csrfField() ?>
        <input type="hidden" name="id" id="prov-id" value="0">

        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5" for="prov-name">Nom du grossiste</label>
          <input type="text" name="name" id="prov-name" required placeholder="ex: SmmFollows" class="input-field w-full px-3 py-2.5 rounded-xl text-sm">
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5" for="prov-url">URL de l'API</label>
          <input type="url" name="api_url" id="prov-url" required placeholder="https://monsitesmm.com/api/v2" class="input-field w-full px-3 py-2.5 rounded-xl text-sm font-mono">
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5" for="prov-key">Clé secrète API</label>
          <input type="password" name="api_key" id="prov-key" required placeholder="Clé API..." class="input-field w-full px-3 py-2.5 rounded-xl text-sm font-mono">
        </div>

        <div class="flex items-center gap-2">
          <input type="checkbox" name="status" id="prov-status" value="1" checked class="rounded border-[#1a2332] bg-[#0a0f1a] text-emerald-500 focus:ring-emerald-500/20">
          <label class="text-xs text-gray-300" for="prov-status">Serveur en ligne & actif</label>
        </div>

        <div class="flex gap-2 pt-2">
          <button type="submit" class="btn-primary flex-1 py-2.5 rounded-xl text-xs font-bold">Sauvegarder</button>
          <button type="button" onclick="cancelProviderEdit()" class="hidden border border-[#1a2332] text-gray-400 px-3 py-2.5 rounded-xl text-xs hover:bg-white/5" id="btn-cancel-prov">Annuler</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ========================================== -->
<!-- 4. ONGLET: USERS (AJUSTEMENT SOLDE) -->
<!-- ========================================== -->
<div id="content-users" class="tab-content hidden">
  <div class="rounded-2xl border" style="background:#0d1117;border-color:#1a2332">
    <div class="px-5 py-4 border-b border-[#1a2332] flex items-center justify-between">
      <h3 class="font-bold text-white text-sm">Gestion des Utilisateurs</h3>
    </div>
    <div class="overflow-x-auto w-full">
      <table class="w-full text-sm text-left">
        <thead>
          <tr class="text-gray-500 border-b border-[#1a2332] text-xs uppercase bg-[#0a0f1a]/50">
            <th class="px-5 py-3">Pseudo / Email</th>
            <th class="px-5 py-3">Rôle</th>
            <th class="px-5 py-3">Solde Actuel</th>
            <th class="px-5 py-3">Inscrit le</th>
            <?php if (Auth::isSuperAdmin()): ?>
            <th class="px-5 py-3 text-right">Ajuster le solde</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#1a2332]">
          <?php foreach ($allUsers as $u): ?>
          <tr class="hover:bg-white/[0.01] transition-all">
            <td class="px-5 py-4">
              <div class="font-semibold text-white"><?= htmlspecialchars($u['username']) ?></div>
              <div class="text-xs text-gray-500"><?= htmlspecialchars($u['email']) ?></div>
            </td>
            <td class="px-5 py-4">
              <span class="text-xs px-2 py-0.5 rounded font-bold uppercase
                <?= $u['role'] === 'superadmin' ? 'bg-purple-500/10 text-purple-400 border border-purple-500/20' :
                   ($u['role'] === 'admin' ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' : 'bg-gray-500/10 text-gray-400') ?>">
                <?= htmlspecialchars($u['role']) ?>
              </span>
            </td>
            <td class="px-5 py-4">
              <div class="font-bold font-mono" style="color:#00ff88">$<?= number_format((float)$u['balance'], 2) ?></div>
            </td>
            <td class="px-5 py-4 text-xs text-gray-500">
              <?= date('d/m/Y', strtotime($u['created_at'])) ?>
            </td>
            <?php if (Auth::isSuperAdmin()): ?>
            <td class="px-5 py-4 text-right">
              <form method="POST" action="<?= APP_BASE ?>/admin/users/balance" class="inline-flex gap-2 items-center justify-end">
                <?= Auth::csrfField() ?>
                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                <input type="number" name="amount" required step="0.01" placeholder="ex: 10 ou -5" class="input-field w-20 px-2 py-1 rounded text-xs text-center font-mono">
                <select name="wallet_currency" class="bg-[#0a0f1a] border border-[#1a2332] rounded px-1.5 py-1 text-xs text-gray-300">
                  <option value="USD">USD</option>
                  <option value="CDF">CDF</option>
                </select>
                <button type="submit" class="px-2 py-1 rounded bg-[#00ff88] text-black text-xs font-bold hover:opacity-90">Ajuster</button>
              </form>
            </td>
            <?php endif; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
<!-- Configuration is now on a separate page -->

<!-- ===== JAVASCRIPT GESTIONNAIRE DES TABS ET DES FORMULAIRES ===== -->
<script>
function switchTab(tabId) {
  document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.classList.remove('bg-[#00ff88]/10', 'border-[#00ff88]/30', 'text-[#00ff88]');
    btn.classList.add('border-transparent', 'bg-transparent', 'text-gray-400');
  });

  const content = document.getElementById('content-' + tabId);
  if (content) {
    content.classList.remove('hidden');
  }
  const activeBtn = document.getElementById('tab-' + tabId);
  if (activeBtn) {
    activeBtn.classList.remove('border-transparent', 'bg-transparent', 'text-gray-400');
    activeBtn.classList.add('bg-[#00ff88]/10', 'border-[#00ff88]/30', 'text-[#00ff88]');
  }

  localStorage.setItem('activeAdminTab', tabId);
}

document.addEventListener('DOMContentLoaded', () => {
  // Priorité : paramètre URL ?tab= > localStorage > défaut 'recharges'
  const urlParams = new URLSearchParams(window.location.search);
  const tabFromUrl = urlParams.get('tab');
  const activeTab = tabFromUrl || localStorage.getItem('activeAdminTab') || 'recharges';
  switchTab(activeTab);
});

function promptRejectReason(form) {
  const reason = prompt("Raison du rejet de la transaction :", "Transaction non validée par SMS ou référence incorrecte.");
  if (reason === null) return false;
  const hiddenInput = form.querySelector('input[name="reason"]');
  hiddenInput.value = reason;
  return true;
}

// Édition de service
function editService(svc) {
  document.getElementById('form-service-title').textContent = "Modifier le service #" + svc.id;
  document.getElementById('svc-id').value = svc.id;
  document.getElementById('svc-provider').value = svc.provider_id;
  document.getElementById('svc-category').value = svc.category;
  document.getElementById('svc-external-id').value = svc.external_service_id;
  document.getElementById('svc-name').value = svc.name;
  document.getElementById('svc-min').value = svc.min_quantity;
  document.getElementById('svc-max').value = svc.max_quantity;
  document.getElementById('svc-buying').value = svc.original_rate;
  document.getElementById('svc-selling').value = svc.calculated_rate;
  document.getElementById('svc-active').checked = parseInt(svc.is_active) === 1;

  document.getElementById('btn-cancel-svc').classList.remove('hidden');
  switchTab('service-mgmt');
  document.getElementById('svc-category').focus();
}

function cancelServiceEdit() {
  document.getElementById('form-service-title').textContent = "Créer un service";
  document.getElementById('form-service').reset();
  document.getElementById('svc-id').value = "0";
  document.getElementById('btn-cancel-svc').classList.add('hidden');
  switchTab('services');
}

// Édition de Fournisseur (Provider)
function editProvider(prov) {
  document.getElementById('form-prov-title').textContent = "Modifier le fournisseur #" + prov.id;
  document.getElementById('prov-id').value = prov.id;
  document.getElementById('prov-name').value = prov.name;
  document.getElementById('prov-url').value = prov.api_url;
  document.getElementById('prov-key').value = prov.api_key;
  document.getElementById('prov-status').checked = parseInt(prov.status) === 1;

  document.getElementById('btn-cancel-prov').classList.remove('hidden');
  document.getElementById('prov-name').focus();
}

function cancelProviderEdit() {
  document.getElementById('form-prov-title').textContent = "Ajouter un Fournisseur SMM";
  document.getElementById('form-provider').reset();
  document.getElementById('prov-id').value = "0";
  document.getElementById('btn-cancel-prov').classList.add('hidden');
}

const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const BASE_URL   = '<?= APP_BASE ?>';

// =====================================================
// BULK ACTIONS (masquer / afficher / supprimer en lot)
// =====================================================
// =====================================================
// RENDU PROGRESSIF ET ULTRA-RAPIDE (PRÉVENTIF DU GEL DOM)
// =====================================================
(function initLazyServiceList() {
  const services = window.ALL_SERVICES || [];
  const csrfField = window.CSRF_FIELD || '';
  const appBase = '<?= APP_BASE ?>';
  const tbody = document.getElementById('services-tbody');
  const scrollContainer = tbody?.closest('.overflow-y-auto');

  if (!tbody || !scrollContainer) return;

  // Pré-calculer la plateforme pour chaque service pour aller encore plus vite
  services.forEach(s => {
    const cat = (s.category + ' ' + s.name).toLowerCase();
    let platform = 'autre';
    if (cat.includes('youtube') || cat.includes('yt'))          platform = 'youtube';
    else if (cat.includes('instagram') || cat.includes('ig'))   platform = 'instagram';
    else if (cat.includes('tiktok'))                            platform = 'tiktok';
    else if (cat.includes('facebook') || cat.includes('fb'))    platform = 'facebook';
    else if (cat.includes('spotify'))                           platform = 'spotify';
    else if (cat.includes('twitter') || cat.includes('x.com'))  platform = 'twitter';
    else if (cat.includes('telegram'))                          platform = 'telegram';
    else if (cat.includes('snapchat'))                          platform = 'snapchat';
    else if (cat.includes('linkedin'))                          platform = 'linkedin';
    else if (cat.includes('threads'))                           platform = 'threads';
    s._platform = platform;
    s._haystack = (s.name + ' ' + s.category).toLowerCase();
    s.is_active = (parseInt(s.is_active) === 1);
  });

  // État du filtrage et de la pagination
  const filters = { provider: 'all', platform: 'all', visibility: '1', search: '', sort: 'default' };
  let currentFilteredList = [];
  let renderedCount = 0;
  const CHUNK_SIZE = 150; // Nombre de lignes chargées à la fois

  // État des sélections en lot
  const selectedIds = new Set();

  // --- Filtrer la liste globale en mémoire ---
  function getFilteredList() {
    let filtered = services.filter(s => {
      // Si "Tous les fournisseurs", on ne montre que ceux dont le fournisseur est opérationnel (status == 1) ou non défini (manuel)
      let providerOperational = true;
      if (filters.provider === 'all') {
          if (s.provider_status !== undefined && s.provider_status !== null && String(s.provider_status) !== '1') {
              providerOperational = false;
          }
      }

      const matchProvider   = filters.provider === 'all' ? providerOperational : String(s.provider_id) === String(filters.provider);
      const matchPlatform   = filters.platform  === 'all' || s._platform === filters.platform;
      const matchVisibility = filters.visibility === 'all' || 
                              (filters.visibility === '1' && s.is_active) || 
                              (filters.visibility === '0' && !s.is_active);
      const matchSearch     = filters.search === '' || s._haystack.includes(filters.search);

      return matchProvider && matchPlatform && matchVisibility && matchSearch;
    });

    if (filters.sort === 'price_asc') {
        filtered.sort((a, b) => parseFloat(a.original_rate) - parseFloat(b.original_rate));
    } else if (filters.sort === 'price_desc') {
        filtered.sort((a, b) => parseFloat(b.original_rate) - parseFloat(a.original_rate));
    }

    return filtered;
  }

  // --- Rendre un bloc de lignes dans le DOM ---
  function renderNextChunk() {
    if (renderedCount >= currentFilteredList.length) return;

    const end = Math.min(renderedCount + CHUNK_SIZE, currentFilteredList.length);
    const chunk = currentFilteredList.slice(renderedCount, end);
    const htmlChunks = [];

    chunk.forEach(s => {
      const margin = parseFloat(s.calculated_rate) - parseFloat(s.original_rate);
      const marginClass = margin > 0 ? 'text-emerald-400' : 'text-red-400';
      const marginSign = margin >= 0 ? '+' : '';
      const isChecked = selectedIds.has(String(s.id)) ? 'checked' : '';
      const rowOpacityClass = !s.is_active ? 'opacity-40 bg-red-500/[0.02]' : '';
      const invisibleBadge = !s.is_active ? `<span class="svc-invisible-badge text-[9px] font-bold uppercase tracking-wider text-red-400 bg-red-500/10 px-1.5 py-0.5 rounded mt-0.5 inline-block">Invisible</span>` : '';
      const toggleBg = s.is_active ? 'bg-emerald-500' : 'bg-gray-600';
      const toggleTransform = s.is_active ? 'translate-x-4' : 'translate-x-1';
      const toggleTitle = s.is_active ? 'Cliquer pour masquer aux clients' : 'Cliquer pour rendre visible';
      
      const formattedOriginalRate = parseFloat(s.original_rate).toFixed(4);
      const formattedCalculatedRate = parseFloat(s.calculated_rate).toFixed(4);
      const formattedMargin = Math.abs(margin).toFixed(4);

      // JSON stringified de façon sécurisée pour onclick
      const serviceJson = JSON.stringify(s).replace(/"/g, '&quot;');

      htmlChunks.push(`
        <tr class="svc-row hover:bg-white/[0.02] transition-all ${rowOpacityClass}" data-id="${s.id}" data-provider-id="${s.provider_id}">
          <td class="px-2 py-2">
            <input type="checkbox" class="svc-chk rounded bg-[#0a0f1a] border-[#1a2332] text-emerald-500" value="${s.id}" ${isChecked}>
          </td>
          <td class="px-2 py-2">
            <div class="text-[10px] text-gray-500 font-semibold uppercase tracking-wider">${escapeHtml(s.category)}</div>
            <div class="text-white font-medium text-xs truncate max-w-[180px]" title="${escapeHtml(s.name)}">${escapeHtml(s.name)}</div>
            ${invisibleBadge}
          </td>
          <td class="px-2 py-2 text-xs text-gray-400 font-mono">
            ${escapeHtml(s.provider_name || 'Manuel')}
          </td>
          <td class="px-2 py-2 font-mono text-gray-500 text-xs">$${formattedOriginalRate}</td>
          <td class="px-2 py-2">
            <form method="POST" action="${appBase}/admin/services/update-price" class="flex items-center gap-1">
              ${csrfField}
              <input type="hidden" name="id" value="${s.id}">
              <div class="relative">
                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-600 text-xs">$</span>
                <input type="number" name="calculated_rate" step="0.0001" value="${formattedCalculatedRate}" class="bg-[#0a0f1a] border border-[#1a2332] text-white text-xs font-mono rounded w-20 pl-4 pr-1 py-1 focus:border-[#00ff88]/50">
              </div>
              <button type="submit" class="bg-white/5 border border-white/10 hover:border-emerald-500 hover:text-emerald-400 text-gray-400 p-1.5 rounded shrink-0">
                <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
              </button>
            </form>
          </td>
          <td class="px-2 py-2 font-bold font-mono text-xs ${marginClass}">
            ${marginSign}$${formattedMargin}
          </td>
          <td class="px-2 py-2 text-center">
            <button type="button"
                    class="svc-toggle relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none ${toggleBg}"
                    data-id="${s.id}"
                    data-active="${s.is_active ? '1' : '0'}"
                    title="${toggleTitle}">
              <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform ${toggleTransform}"></span>
            </button>
          </td>
          <td class="px-2 py-2 text-right">
            <div class="flex flex-col xl:flex-row items-end xl:items-center justify-end gap-1.5">
              <button type="button" onclick="editService(${serviceJson})" class="text-xs text-blue-400 hover:underline">Éditer</button>
              <button type="button" onclick="confirmDeleteService(${s.id})" class="text-xs text-red-500 hover:underline">Supprimer</button>
            </div>
          </td>
        </tr>
      `);
    });

    // Insertion directe ultra-optimisée
    const tempDiv = document.createElement('tbody');
    tempDiv.innerHTML = htmlChunks.join('');
    
    while (tempDiv.firstChild) {
      tbody.appendChild(tempDiv.firstChild);
    }

    renderedCount = end;
  }

  // --- Reset complet et render ---
  function resetAndRender() {
    tbody.innerHTML = '';
    currentFilteredList = getFilteredList();
    renderedCount = 0;
    renderNextChunk();
    
    // Mettre à jour le compteur
    const countEl = document.getElementById('filter-count');
    if (countEl) {
      const total = services.length;
      const visible = currentFilteredList.length;
      countEl.textContent = visible < total ? `${visible} / ${total} services` : `${total} services`;
    }

    // Réinitialiser le scroll à zéro
    scrollContainer.scrollTop = 0;

    // Décocher le checkbox global s'il n'y a plus rien ou si les éléments affichés ne sont pas tous sélectionnés
    const chkAll = document.getElementById('chk-all');
    if (chkAll) {
      chkAll.checked = currentFilteredList.length > 0 && currentFilteredList.every(s => selectedIds.has(String(s.id)));
    }
  }

  // --- Défilement infini / Scroll infini ---
  scrollContainer.addEventListener('scroll', () => {
    // Si on arrive à 80px du bas du conteneur de défilement, on charge le chunk suivant
    if (scrollContainer.scrollTop + scrollContainer.clientHeight >= scrollContainer.scrollHeight - 80) {
      renderNextChunk();
    }
  });

  // --- Initialiser les compteurs par fournisseur dans les pills ---
  function initProviderCounts() {
    const counts = {};
    services.forEach(s => {
      counts[s.provider_id] = (counts[s.provider_id] || 0) + 1;
    });
    document.querySelectorAll('.filter-provider[data-provider]').forEach(btn => {
      const pid = btn.dataset.provider;
      if (pid === 'all') return;
      const span = btn.querySelector('span');
      if (span) span.textContent = counts[pid] ? `(${counts[pid]})` : '';
    });
  }

  // --- Style actif d'un bouton de filtre ---
  function setActive(group, activeBtn) {
    document.querySelectorAll(`.${group}`).forEach(btn => {
      btn.classList.add('opacity-60');
      btn.style.removeProperty('box-shadow');
    });
    activeBtn.classList.remove('opacity-60');
    activeBtn.style.boxShadow = '0 0 0 2px currentColor inset';
  }

  // --- Écoutes de filtres ---
  document.querySelectorAll('.filter-provider').forEach(btn => {
    btn.addEventListener('click', () => {
      filters.provider = btn.dataset.provider;
      setActive('filter-provider', btn);
      resetAndRender();
    });
  });

  document.querySelectorAll('.filter-platform').forEach(btn => {
    btn.addEventListener('click', () => {
      filters.platform = btn.dataset.platform;
      setActive('filter-platform', btn);
      resetAndRender();
    });
  });

  document.querySelectorAll('.filter-visibility').forEach(btn => {
    btn.addEventListener('click', () => {
      filters.visibility = btn.dataset.visibility;
      setActive('filter-visibility', btn);
      resetAndRender();
    });
  });

  // Recherche avec debounce
  let searchTimer;
  const searchInput = document.getElementById('svc-search');
  if (searchInput) {
    searchInput.addEventListener('input', () => {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(() => {
        filters.search = searchInput.value.toLowerCase().trim();
        resetAndRender();
      }, 150);
    });
    searchInput.addEventListener('keydown', e => {
      if (e.key === 'Escape') { searchInput.value = ''; filters.search = ''; resetAndRender(); }
    });
  }

  // Écoute du tri par prix
  const sortSelect = document.getElementById('svc-sort');
  if (sortSelect) {
    sortSelect.addEventListener('change', () => {
      filters.sort = sortSelect.value;
      resetAndRender();
    });
  }

  // --- Cliquer sur un switch de visibilité individuel (par délégation) ---
  tbody.addEventListener('click', async (e) => {
    const btn = e.target.closest('.svc-toggle');
    if (!btn) return;
    e.preventDefault();

    const id       = btn.dataset.id;
    const newState = btn.dataset.active === '1' ? 0 : 1;

    try {
      const body = new URLSearchParams({ id, is_active: newState, _token: CSRF_TOKEN });
      const res  = await fetch(BASE_URL + '/admin/services/toggle-status', { method: 'POST', body });
      const json = await res.json();

      if (json.success) {
        window.applyToggleUI(btn, newState === 1);
        showToast(newState === 1 ? 'Service rendu visible aux clients.' : 'Service masqué aux clients.', newState === 1 ? 'success' : 'warning');
      } else {
        showToast('' + (json.error ?? 'Erreur inconnue.'), 'error');
      }
    } catch (err) {
      showToast('Erreur réseau. Veuillez réessayer.', 'error');
    }
  });

  // --- Gérer la sélection des cases à cocher en lot ---
  tbody.addEventListener('change', (e) => {
    if (e.target.classList.contains('svc-chk')) {
      const id = e.target.value;
      if (e.target.checked) {
        selectedIds.add(id);
      } else {
        selectedIds.delete(id);
      }
      updateBulkBar();
    }
  });

  // Sélectionner tout (uniquement ceux qui matchent le filtre actuel)
  document.getElementById('chk-all')?.addEventListener('change', function() {
    if (this.checked) {
      currentFilteredList.forEach(s => selectedIds.add(String(s.id)));
    } else {
      currentFilteredList.forEach(s => selectedIds.clear());
    }
    // Mettre à jour les checkboxes visibles
    tbody.querySelectorAll('.svc-chk').forEach(chk => {
      chk.checked = selectedIds.has(chk.value);
    });
    updateBulkBar();
  });

  function updateBulkBar() {
    const bar = document.getElementById('bulk-action-bar');
    const countEl = document.getElementById('bulk-count');
    if (bar) {
      bar.classList.toggle('hidden', selectedIds.size === 0);
      if (countEl) countEl.textContent = selectedIds.size;
    }
  }

  // --- Fonctions globales d'actions de lot connectées à la mémoire locale ---
  window.getSelectedIds = function() {
    return Array.from(selectedIds);
  };

  // Exposer les méthodes d'UI pour le toggle individuel
  window.applyToggleUI = function(btn, isActive) {
    const knob = btn.querySelector('span');
    const row = btn.closest('tr.svc-row');
    const id = btn.dataset.id;
    
    // Mettre à jour l'objet en mémoire
    const s = services.find(item => String(item.id) === String(id));
    if (s) s.is_active = isActive;

    if (isActive) {
      btn.classList.replace('bg-gray-600', 'bg-emerald-500');
      knob.classList.replace('translate-x-1', 'translate-x-4');
      btn.title = 'Cliquer pour masquer aux clients';
      btn.dataset.active = '1';
      if (row) {
        row.classList.remove('opacity-40', 'bg-red-500/[0.02]');
        row.querySelector('.svc-invisible-badge')?.remove();
      }
    } else {
      btn.classList.replace('bg-emerald-500', 'bg-gray-600');
      knob.classList.replace('translate-x-4', 'translate-x-1');
      btn.title = 'Cliquer pour rendre visible';
      btn.dataset.active = '0';
      if (row) {
        row.classList.add('opacity-40', 'bg-red-500/[0.02]');
      }
    }
  };

  // Surcharger bulkToggle et bulkDelete pour réagir proprement avec le rendu virtuel
  window.bulkToggle = async function(isActive) {
    const ids = window.getSelectedIds();
    if (!ids.length) return;
    const label = isActive ? 'visible' : 'invisible';
    const ok = await showConfirm({
      title: isActive ? 'Rendre visible' : 'Masquer aux clients',
      message: `Vous êtes sur le point de rendre <strong>${ids.length} service(s)</strong> ${label} pour vos clients.`,
      confirmLabel: isActive ? 'Rendre visible' : 'Masquer',
      type: isActive ? 'info' : 'warning'
    });
    if (!ok) return;

    const body = new URLSearchParams({ is_active: isActive, _token: CSRF_TOKEN, ids: JSON.stringify(ids) });

    try {
      const res = await fetch(BASE_URL + '/admin/services/bulk-toggle', { method: 'POST', body });
      const json = await res.json();
      if (json.success) {
        // Mettre à jour en mémoire
        ids.forEach(id => {
          const s = services.find(item => String(item.id) === String(id));
          if (s) s.is_active = (isActive === 1);
        });
        selectedIds.clear();
        document.getElementById('chk-all').checked = false;
        resetAndRender();
        updateBulkBar();
        showToast(`${json.affected} service(s) mis à jour.`, 'success');
      }
    } catch (err) {
      showToast('Erreur réseau.', 'error');
    }
  };

  window.bulkDelete = async function() {
    const ids = window.getSelectedIds();
    if (!ids.length) return;
    const ok = await showConfirm({
      title: 'Suppression définitive',
      message: `Vous êtes sur le point de supprimer <strong>${ids.length} service(s)</strong> de façon permanente. Cette action est irréversible.`,
      confirmLabel: 'Supprimer',
      type: 'danger'
    });
    if (!ok) return;

    const body = new URLSearchParams({ _token: CSRF_TOKEN, ids: JSON.stringify(ids) });

    try {
      const res = await fetch(BASE_URL + '/admin/services/bulk-delete', { method: 'POST', body });
      const json = await res.json();
      if (json.success) {
        // Retirer de la mémoire globale
        window.ALL_SERVICES = window.ALL_SERVICES.filter(s => !ids.includes(String(s.id)));
        // Rafraîchir la référence locale
        services.length = 0;
        services.push(...window.ALL_SERVICES);
        
        selectedIds.clear();
        document.getElementById('chk-all').checked = false;
        resetAndRender();
        updateBulkBar();
        initProviderCounts();
        showToast(`${json.affected} service(s) supprimé(s).`, 'success');
      }
    } catch (err) {
      showToast('Erreur réseau.', 'error');
    }
  };

  window.confirmDeleteService = async function(id) {
    const ok = await showConfirm({
      title: 'Supprimer ce service',
      message: 'Ce service sera supprimé définitivement. Cette action est irréversible.',
      confirmLabel: 'Supprimer',
      type: 'danger'
    });
    if (!ok) return;
    const body = new URLSearchParams({ id, _csrf: CSRF_TOKEN });
    fetch(BASE_URL + '/admin/services/delete', { method: 'POST', body })
      .then(r => {
        if (r.redirected || r.ok) {
          window.ALL_SERVICES = window.ALL_SERVICES.filter(s => String(s.id) !== String(id));
          services.length = 0;
          services.push(...window.ALL_SERVICES);
          resetAndRender();
          initProviderCounts();
          showToast('Service supprimé.', 'success');
        }
      });
  };

  function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
  }

  // Lancement initial
  initProviderCounts();
  resetAndRender();
})();

// =====================================================
// MODALE DE CONFIRMATION PREMIUM
// =====================================================
function showConfirm({ title = 'Confirmation', message = '', confirmLabel = 'Confirmer', cancelLabel = 'Annuler', type = 'info' } = {}) {
  return new Promise(resolve => {
    // Thèmes
    const themes = {
      info:    { accent: '#00ff88', bg: 'rgba(0,255,136,0.08)', icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`, btnClass: 'background:#00ff88;color:#050811;' },
      warning: { accent: '#f59e0b', bg: 'rgba(245,158,11,0.08)', icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`, btnClass: 'background:#f59e0b;color:#050811;' },
      danger:  { accent: '#ef4444', bg: 'rgba(239,68,68,0.08)',  icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>`, btnClass: 'background:#ef4444;color:#fff;' },
    };
    const t = themes[type] || themes.info;

    // Overlay
    const overlay = document.createElement('div');
    overlay.style.cssText = 'position:fixed;inset:0;z-index:99999;display:flex;align-items:center;justify-content:center;padding:1rem;background:rgba(5,8,17,0.75);backdrop-filter:blur(8px);opacity:0;transition:opacity .2s ease;';

    // Carte
    const card = document.createElement('div');
    card.style.cssText = `background:#0d1117;border:1px solid ${t.accent}30;border-radius:1.25rem;padding:2rem;max-width:420px;width:100%;box-shadow:0 25px 60px rgba(0,0,0,0.6),0 0 0 1px ${t.accent}15;transform:scale(0.93) translateY(12px);transition:transform .25s cubic-bezier(.34,1.56,.64,1),opacity .2s ease;opacity:0;`;

    card.innerHTML = `
      <div style="display:flex;align-items:flex-start;gap:1rem;margin-bottom:1.25rem;">
        <div style="flex-shrink:0;width:2.75rem;height:2.75rem;border-radius:0.75rem;display:flex;align-items:center;justify-content:center;background:${t.bg};color:${t.accent};border:1px solid ${t.accent}25;">${t.icon}</div>
        <div style="flex:1;">
          <div style="font-size:1rem;font-weight:700;color:#fff;margin-bottom:0.35rem;line-height:1.3;">${title}</div>
          <div style="font-size:0.8125rem;color:#94a3b8;line-height:1.6;">${message}</div>
        </div>
      </div>
      <div style="height:1px;background:#1a2332;margin-bottom:1.25rem;"></div>
      <div style="display:flex;gap:0.625rem;justify-content:flex-end;">
        <button id="modal-cancel" style="padding:0.6rem 1.25rem;border-radius:0.625rem;border:1px solid #1a2332;background:transparent;color:#94a3b8;font-size:0.8125rem;font-weight:600;cursor:pointer;transition:all .15s;" onmouseover="this.style.borderColor='#374151';this.style.color='#e2e8f0'" onmouseout="this.style.borderColor='#1a2332';this.style.color='#94a3b8'">${cancelLabel}</button>
        <button id="modal-confirm" style="padding:0.6rem 1.25rem;border-radius:0.625rem;border:none;${t.btnClass}font-size:0.8125rem;font-weight:700;cursor:pointer;transition:transform .15s,box-shadow .15s;box-shadow:0 4px 15px ${t.accent}30;" onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 6px 20px ${t.accent}50'" onmouseout="this.style.transform='';this.style.boxShadow='0 4px 15px ${t.accent}30'">${confirmLabel}</button>
      </div>
    `;

    overlay.appendChild(card);
    document.body.appendChild(overlay);

    const close = (result) => {
      card.style.transform = 'scale(0.93) translateY(12px)';
      card.style.opacity = '0';
      overlay.style.opacity = '0';
      setTimeout(() => { overlay.remove(); resolve(result); }, 220);
    };

    requestAnimationFrame(() => {
      overlay.style.opacity = '1';
      card.style.opacity = '1';
      card.style.transform = 'scale(1) translateY(0)';
    });

    card.querySelector('#modal-confirm').addEventListener('click', () => close(true));
    card.querySelector('#modal-cancel').addEventListener('click',  () => close(false));
    overlay.addEventListener('click', e => { if (e.target === overlay) close(false); });
    document.addEventListener('keydown', function esc(e) {
      if (e.key === 'Escape') { document.removeEventListener('keydown', esc); close(false); }
    });
  });
}

// =====================================================
// TOAST NOTIFICATIONS ULTRA-PREMIUM (STACKING & GLOW)
// =====================================================
function showToast(msg, type = 'success') {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'fixed bottom-6 right-6 z-[9999] flex flex-col gap-3 pointer-events-none max-w-sm w-full';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = 'pointer-events-auto flex items-center gap-3 px-4 py-3.5 rounded-2xl shadow-[0_10px_30px_rgba(0,0,0,0.5)] border transition-all duration-300 transform translate-x-10 opacity-0 backdrop-blur-md relative overflow-hidden';
  
  const themes = {
    success: {
      border: 'rgba(0, 255, 136, 0.3)',
      bg: 'rgba(13, 17, 23, 0.95)',
      color: '#00ff88',
      glow: '0 0 15px rgba(0, 255, 136, 0.15)',
      icon: `<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`
    },
    warning: {
      border: 'rgba(245, 158, 11, 0.3)',
      bg: 'rgba(13, 17, 23, 0.95)',
      color: '#f59e0b',
      glow: '0 0 15px rgba(245, 158, 11, 0.15)',
      icon: `<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`
    },
    error: {
      border: 'rgba(239, 68, 68, 0.3)',
      bg: 'rgba(13, 17, 23, 0.95)',
      color: '#ef4444',
      glow: '0 0 15px rgba(239, 68, 68, 0.15)',
      icon: `<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`
    }
  };

  const theme = themes[type] || themes.success;

  toast.style.borderColor = theme.border;
  toast.style.backgroundColor = theme.bg;
  toast.style.color = '#ffffff';
  toast.style.boxShadow = `0 10px 30px rgba(0,0,0,0.5), ${theme.glow}`;
  
  toast.innerHTML = `
    <div style="color: ${theme.color}">${theme.icon}</div>
    <div class="flex-1 text-xs font-semibold select-none leading-relaxed">${msg}</div>
    <button type="button" class="text-white/20 hover:text-white/60 transition-colors shrink-0">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
    <div class="absolute bottom-0 left-0 h-[2px] w-full transition-all duration-[3700ms] linear" style="background: ${theme.color}; width: 100%;"></div>
  `;

  toast.querySelector('button').addEventListener('click', () => {
    toast.classList.add('translate-x-10', 'opacity-0');
    setTimeout(() => toast.remove(), 300);
  });

  container.appendChild(toast);

  requestAnimationFrame(() => {
    toast.classList.remove('translate-x-10', 'opacity-0');
    toast.classList.add('translate-x-0', 'opacity-100');
  });

  setTimeout(() => {
    const bar = toast.querySelector('.absolute');
    if (bar) bar.style.width = '0%';
  }, 50);

  setTimeout(() => {
    toast.classList.add('translate-x-10', 'opacity-0');
    setTimeout(() => toast.remove(), 300);
  }, 4000);
}


<!-- ================================================ -->
<!-- ONGLET: COMMANDES CLIENTS -->
<!-- ================================================ -->
</script>

<div id="content-orders" class="tab-content hidden">
  <div class="rounded-2xl border overflow-hidden" style="background:#0d1117;border-color:#1a2332">

    <!-- Header + Barre de recherche -->
    <div class="px-5 py-4 border-b border-[#1a2332] flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:rgba(0,212,255,0.1)">
          <svg class="w-4 h-4" style="color:#00d4ff" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div>
          <h2 class="font-bold text-white text-sm">Toutes les Commandes</h2>
          <p class="text-[10px] text-gray-500" id="orders-count-label"><?= count($recentOrders) ?> commande(s) au total</p>
        </div>
      </div>

      <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
        <!-- Barre de recherche -->
        <div class="relative flex-1 sm:w-64">
          <input type="text" id="admin-order-search"
                 placeholder="Rechercher ID, Réf, Username..."
                 oninput="filterAdminOrders()"
                 class="w-full text-xs pl-8 pr-3 py-2 rounded-lg border focus:outline-none"
                 style="background:#0a0f1a;border-color:#1a2332;color:#e2e8f0">
          <svg class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <!-- Filtre statut -->
        <select id="admin-order-status" onchange="filterAdminOrders()"
                class="text-xs px-3 py-2 rounded-lg border text-gray-300 focus:outline-none"
                style="background:#0a0f1a;border-color:#1a2332">
          <option value="">Tous les statuts</option>
          <option value="Pending">En attente</option>
          <option value="Processing">En cours</option>
          <option value="Completed">Complété</option>
          <option value="Canceled">Annulé</option>
          <option value="Partial">Partiel</option>
        </select>
        <!-- Bouton Sync Statuts -->
        <button type="button" id="btn-sync-statuses" onclick="fetchSyncStatuses()"
                class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-bold transition-all hover:brightness-110 shrink-0"
                style="background:rgba(0,212,255,0.12);color:#00d4ff;border:1px solid rgba(0,212,255,0.3)">
          <svg id="sync-icon" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 0021.21 8H18M4 4v5h.582m0 0a8.001 8.001 0 0015.356 2M4 9.582A8.001 8.001 0 0119.418 14H16m4 0v5h-.582m0 0a8.001 8.001 0 01-15.356-2M20 19v-5h-.582"/></svg>
          <span id="sync-label">Sync Statuts</span>
        </button>
      </div>
    </div>

    <?php if (empty($recentOrders)): ?>
      <div class="text-center py-16 text-gray-600">
        <p class="text-sm">Aucune commande pour le moment.</p>
      </div>
    <?php else: ?>

      <!-- Table Desktop -->
      <div class="hidden md:block overflow-x-auto w-full">
        <table class="w-full text-sm text-left" id="admin-orders-table">
          <thead>
            <tr class="text-gray-500 border-b border-[#1a2332] bg-[#0a0f1a]/50 text-xs uppercase tracking-wider">
              <th class="px-5 py-3">ID</th>
              <th class="px-5 py-3">Client</th>
              <th class="px-5 py-3">Service</th>
              <th class="px-5 py-3">Quantité</th>
              <th class="px-5 py-3">Montant</th>
              <th class="px-5 py-3">Réf. Externe</th>
              <th class="px-5 py-3">Statut</th>
              <th class="px-5 py-3">Date</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#1a2332]" id="admin-orders-tbody">
            <?php foreach ($recentOrders as $o):
              $st = strtolower($o['status'] ?? '');
              $badgeCls = match($st) {
                'completed'  => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                'processing' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                'pending'    => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
                'canceled'   => 'bg-red-500/10 text-red-400 border-red-500/20',
                'partial'    => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                default      => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
              };
            ?>
            <tr class="hover:bg-white/[0.01] transition-all admin-order-row"
                data-status="<?= htmlspecialchars($o['status'] ?? '') ?>"
                data-search="<?= strtolower(str_pad($o['id'], 5, '0', STR_PAD_LEFT) . ' ' . ($o['username'] ?? '') . ' ' . ($o['external_order_id'] ?? '') . ' ' . ($o['service_name'] ?? '')) ?>">
              <td class="px-5 py-3.5">
                <span class="font-mono text-xs font-bold text-white">#<?= str_pad($o['id'], 5, '0', STR_PAD_LEFT) ?></span>
              </td>
              <td class="px-5 py-3.5">
                <span class="text-xs font-semibold text-[#00d4ff]"><?= htmlspecialchars($o['username'] ?? '—') ?></span>
              </td>
              <td class="px-5 py-3.5">
                <div class="text-white text-xs font-semibold max-w-[180px] truncate"><?= htmlspecialchars($o['service_name'] ?? '—') ?></div>
              </td>
              <td class="px-5 py-3.5 text-white text-xs font-mono"><?= number_format((int)$o['quantity']) ?></td>
              <td class="px-5 py-3.5 font-bold text-xs font-mono" style="color:#00ff88">$<?= number_format((float)$o['cost'], 3) ?></td>
              <td class="px-5 py-3.5 font-mono text-xs text-gray-500"><?= htmlspecialchars($o['external_order_id'] ?? '—') ?></td>
              <td class="px-5 py-3.5">
                <span class="inline-flex items-center gap-1 text-[10px] px-2.5 py-1 rounded-full font-semibold border <?= $badgeCls ?>">
                  <?php if ($st === 'processing'): ?><span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse inline-block"></span><?php endif; ?>
                  <?= htmlspecialchars($o['status'] ?? '') ?>
                </span>
              </td>
              <td class="px-5 py-3.5">
                <div class="text-xs text-gray-400"><?= date('d/m/Y', strtotime($o['created_at'])) ?></div>
                <div class="text-[10px] text-gray-600"><?= date('H:i', strtotime($o['created_at'])) ?></div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <!-- Message vide après filtrage -->
        <div id="admin-orders-empty" class="hidden text-center py-12 text-gray-600">
          <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
          <p class="text-sm">Aucune commande ne correspond à votre recherche.</p>
        </div>
      </div>

      <!-- Cards Mobile -->
      <div class="md:hidden p-4 space-y-3" id="admin-orders-cards">
        <?php foreach ($recentOrders as $o):
          $st = strtolower($o['status'] ?? '');
          $badgeCls = match($st) {
            'completed'  => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            'processing' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
            'pending'    => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
            'canceled'   => 'bg-red-500/10 text-red-400 border-red-500/20',
            'partial'    => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
            default      => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
          };
        ?>
        <div class="p-4 rounded-xl border admin-order-card"
             style="background:#0d1117;border-color:#1a2332"
             data-status="<?= htmlspecialchars($o['status'] ?? '') ?>"
             data-search="<?= strtolower(str_pad($o['id'], 5, '0', STR_PAD_LEFT) . ' ' . ($o['username'] ?? '') . ' ' . ($o['external_order_id'] ?? '') . ' ' . ($o['service_name'] ?? '')) ?>">
          <div class="flex items-start justify-between mb-3 gap-2">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1">
                <span class="text-[10px] font-mono font-bold text-gray-500 bg-[#1a2332]/50 px-1.5 py-0.5 rounded">#<?= str_pad($o['id'], 5, '0', STR_PAD_LEFT) ?></span>
                <span class="text-[#00d4ff] text-xs font-bold truncate"><?= htmlspecialchars($o['username'] ?? '—') ?></span>
              </div>
              <div class="text-sm font-semibold text-white leading-snug break-words"><?= htmlspecialchars($o['service_name'] ?? '—') ?></div>
            </div>
            <span class="inline-block text-[10px] px-2 py-0.5 rounded-full font-semibold border shrink-0 <?= $badgeCls ?>">
              <?= htmlspecialchars($o['status'] ?? '') ?>
            </span>
          </div>
          <div class="flex items-center justify-between border-t pt-2 mt-1" style="border-color:#1a2332">
            <div>
              <div class="text-[10px] text-gray-500 uppercase tracking-wider">Quantité</div>
              <div class="text-white font-mono text-xs font-bold"><?= number_format((int)$o['quantity']) ?></div>
            </div>
            <div class="text-right">
              <div class="text-[10px] text-gray-500 uppercase tracking-wider">Montant</div>
              <div class="font-mono text-xs font-bold" style="color:#00ff88">$<?= number_format((float)$o['cost'], 3) ?></div>
            </div>
            <div class="text-right">
              <div class="text-[10px] text-gray-500 uppercase tracking-wider">Date</div>
              <div class="text-gray-400 text-[10px]"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></div>
            </div>
          </div>
          <?php if (!empty($o['external_order_id'])): ?>
          <div class="mt-2 text-[10px] text-gray-600 font-mono">Réf: <?= htmlspecialchars($o['external_order_id']) ?></div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <div id="admin-orders-cards-empty" class="hidden text-center py-10 text-gray-600 text-sm">Aucun résultat.</div>
      </div>

    <?php endif; ?>
  </div>
</div>

<script>
function filterAdminOrders() {
  const search = (document.getElementById('admin-order-search')?.value || '').toLowerCase().trim();
  const status = document.getElementById('admin-order-status')?.value || '';

  // Desktop rows
  const rows = document.querySelectorAll('.admin-order-row');
  let visibleCount = 0;
  rows.forEach(row => {
    const matchSearch = !search || row.dataset.search.includes(search);
    const matchStatus = !status || row.dataset.status === status;
    const show = matchSearch && matchStatus;
    row.style.display = show ? '' : 'none';
    if (show) visibleCount++;
  });
  const emptyDesktop = document.getElementById('admin-orders-empty');
  if (emptyDesktop) emptyDesktop.classList.toggle('hidden', visibleCount > 0);

  // Mobile cards
  const cards = document.querySelectorAll('.admin-order-card');
  let visibleCards = 0;
  cards.forEach(card => {
    const matchSearch = !search || card.dataset.search.includes(search);
    const matchStatus = !status || card.dataset.status === status;
    const show = matchSearch && matchStatus;
    card.style.display = show ? '' : 'none';
    if (show) visibleCards++;
  });
  const emptyMobile = document.getElementById('admin-orders-cards-empty');
  if (emptyMobile) emptyMobile.classList.toggle('hidden', visibleCards > 0);

  // Update label
  const label = document.getElementById('orders-count-label');
  if (label) label.textContent = visibleCount + ' commande(s) trouvée(s)';
}

// Chargement asynchrone du solde du grossiste actif pour éviter le gel de la page
window.fetchProviderBalance = function(providerId = null) {
  const cardEl  = document.getElementById('provider-balance-card');
  const valEl   = document.getElementById('provider-balance-value');
  
  if (cardEl) cardEl.classList.add('animate-pulse');
  if (valEl) {
    valEl.innerHTML = '<span class="w-3.5 h-3.5 rounded-full border-2 border-[#00ff88]/30 border-t-[#00ff88] animate-spin"></span><span class="text-xs text-gray-500 font-normal">Connexion...</span>';
  }

  let url = '<?= APP_BASE ?>/admin/provider-balance';
  if (providerId) {
    url += '?provider_id=' + providerId;
  }

  fetch(url)
    .then(response => response.json())
    .then(data => {
      if (cardEl) cardEl.classList.remove('animate-pulse');
      
      if (data.success && data.balance !== null && data.balance !== undefined) {
        const selectEl = document.getElementById('provider-balance-select');
        if (selectEl && data.provider_id) {
          selectEl.value = data.provider_id;
        }
        const formatted = parseFloat(data.balance).toLocaleString('en-US', { style: 'currency', currency: 'USD' });
        if (valEl) valEl.textContent = formatted;
      } else {
        if (valEl) valEl.innerHTML = '<span class="text-xs text-red-500 font-bold">API Inaccessible</span>';
      }
    })
    .catch(err => {
      if (cardEl) cardEl.classList.remove('animate-pulse');
      if (valEl) valEl.innerHTML = '<span class="text-xs text-red-500 font-bold">Hors-ligne</span>';
    });
};

document.addEventListener('DOMContentLoaded', () => {
  const selectEl = document.getElementById('provider-balance-select');
  fetchProviderBalance(selectEl ? selectEl.value : null);
});

// =====================================================
// SYNC STATUTS COMMANDES (AJAX)
// =====================================================
window.fetchSyncStatuses = async function() {
  const btn   = document.getElementById('btn-sync-statuses');
  const icon  = document.getElementById('sync-icon');
  const label = document.getElementById('sync-label');

  if (!btn || btn.disabled) return;

  btn.disabled = true;
  if (icon) icon.classList.add('animate-spin');
  if (label) label.textContent = 'Synchronisation...';

  try {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const token = csrfMeta ? csrfMeta.content : '';

    const res = await fetch('<?= APP_BASE ?>/admin/orders/sync-statuses', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': token },
      body: '_token=' + encodeURIComponent(token)
    });

    const data = await res.json();

    if (data.success) {
      showToast('success', '✓ ' + data.message);

      // Mise à jour visuelle des badges de statut si des commandes ont changé
      if (data.stats && (data.stats.completed + data.stats.canceled + data.stats.partial) > 0) {
        setTimeout(() => window.location.reload(), 2500);
      }
    } else {
      showToast('error', '⚠ ' + (data.error || 'Erreur lors de la synchronisation.'));
    }
  } catch (err) {
    showToast('error', '⚠ Impossible de contacter le serveur.');
  } finally {
    if (btn) btn.disabled = false;
    if (icon) icon.classList.remove('animate-spin');
    if (label) label.textContent = 'Sync Statuts';
  }
};
</script>
