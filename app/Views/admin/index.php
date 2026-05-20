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
    <p class="text-gray-500 text-xs mt-0.5">BukavuBoost · Gestion multi-fournisseurs et tarification dynamique</p>
  </div>
  <div class="flex items-center gap-3">
    <!-- Solde API Fournisseur Actif -->
    <div class="px-4 py-2 rounded-xl border flex items-center gap-3" style="background:#0d1117;border-color:#1a2332">
      <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:rgba(0,255,136,0.1)">
        <svg class="w-4 h-4 text-[#00ff88] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
      </div>
      <div>
        <div class="text-[10px] text-gray-500 uppercase tracking-wider">Grossiste Actif</div>
        <div class="text-sm font-bold font-mono" style="color:#00ff88">
          <?= isset($providerBalance['balance']) ? '$' . number_format((float)$providerBalance['balance'], 2) : 'N/A' ?>
        </div>
      </div>
    </div>
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
<div class="flex border-b border-[#1a2332] mb-6 overflow-x-auto select-none" id="adminTabs">
  <button onclick="switchTab('recharges')" class="tab-btn px-5 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-400 hover:text-white transition-all whitespace-nowrap active flex items-center gap-1.5" id="tab-recharges">
    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
    Dépôts (<?= count($pendingRecharges) ?>)
  </button>
  <button onclick="switchTab('services')" class="tab-btn px-5 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-400 hover:text-white transition-all whitespace-nowrap flex items-center gap-1.5" id="tab-services">
    <svg class="w-4 h-4 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
    Gestion des Tarifs & Services
  </button>
  <button onclick="switchTab('providers')" class="tab-btn px-5 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-400 hover:text-white transition-all whitespace-nowrap flex items-center gap-1.5" id="tab-providers">
    <svg class="w-4 h-4 text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
    Grossistes & Multi-API
  </button>
  <button onclick="switchTab('users')" class="tab-btn px-5 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-400 hover:text-white transition-all whitespace-nowrap flex items-center gap-1.5" id="tab-users">
    <svg class="w-4 h-4 text-orange-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
    Clients & Soldes
  </button>
  <?php if (Auth::isSuperAdmin()): ?>
  <button onclick="switchTab('settings')" class="tab-btn px-5 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-400 hover:text-white transition-all whitespace-nowrap flex items-center gap-1.5" id="tab-settings">
    <svg class="w-4 h-4 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
    Configuration
  </button>
  <?php endif; ?>
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
      <div class="overflow-x-auto">
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
<!-- 2. ONGLET: GESTION DES TARIFS (SERVICES) -->
<!-- ========================================== -->
<div id="content-services" class="tab-content hidden">
  <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <!-- Liste & Tarification Rapide -->
    <div class="xl:col-span-2 rounded-2xl border" style="background:#0d1117;border-color:#1a2332">
      <div class="px-5 py-4 border-b border-[#1a2332] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
          <h3 class="font-bold text-white text-sm">Gestion des Tarifs Globaux</h3>
          <p class="text-xs text-gray-500 mt-0.5">Ajustez les prix de vente pour les clients locaux de Bukavu</p>
        </div>
        <div class="flex items-center gap-2">
          <!-- Synchronisation instantanée -->
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
        </div>
      </div>
      <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
        <table class="w-full text-sm text-left">
          <thead>
            <tr class="text-gray-500 border-b border-[#1a2332] text-xs uppercase bg-[#0a0f1a]/50">
              <th class="px-4 py-3">Catégorie & Nom</th>
              <th class="px-4 py-3">Grossiste</th>
              <th class="px-4 py-3">Prix d'Achat</th>
              <th class="px-4 py-3">Prix de Vente (Bukavu)</th>
              <th class="px-4 py-3">Marge nette / 1k</th>
              <th class="px-4 py-3 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#1a2332]">
            <?php foreach ($allServices as $s): ?>
            <?php 
              $margin = $s['calculated_rate'] - $s['original_rate'];
              $marginClass = $margin > 0 ? 'text-emerald-400' : 'text-red-400';
            ?>
            <tr class="hover:bg-white/[0.01] transition-all <?= !$s['is_active'] ? 'opacity-55' : '' ?>">
              <td class="px-4 py-3.5">
                <div class="text-xs text-gray-500 font-semibold"><?= htmlspecialchars($s['category']) ?></div>
                <div class="text-white font-medium text-xs truncate max-w-[200px]" title="<?= htmlspecialchars($s['name']) ?>"><?= htmlspecialchars($s['name']) ?></div>
              </td>
              <td class="px-4 py-3.5 text-xs text-gray-400 font-mono">
                <?= htmlspecialchars($s['provider_name'] ?? 'Manuel') ?>
              </td>
              <td class="px-4 py-3.5 font-mono text-gray-500 text-xs">$<?= number_format((float)$s['original_rate'], 4) ?></td>
              <!-- Formulaire d'ajustement tarifaire instantané -->
              <td class="px-4 py-3.5">
                <form method="POST" action="<?= APP_BASE ?>/admin/services/update-price" class="flex items-center gap-1">
                  <?= Auth::csrfField() ?>
                  <input type="hidden" name="id" value="<?= $s['id'] ?>">
                  <div class="relative">
                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-600 text-xs">$</span>
                    <input type="number" name="calculated_rate" step="0.0001" value="<?= (float)$s['calculated_rate'] ?>" class="bg-[#0a0f1a] border border-[#1a2332] text-white text-xs font-mono rounded w-20 pl-4 pr-1 py-1 text-left focus:border-[#00ff88]/50">
                  </div>
                  <button type="submit" class="flex items-center justify-center text-[10px] bg-white/5 border border-white/10 hover:border-emerald-500 hover:text-emerald-400 text-gray-400 p-1.5 rounded shrink-0">
                    <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                  </button>
                </form>
              </td>
              <td class="px-4 py-3.5 font-bold font-mono text-xs <?= $marginClass ?>">
                <?= $margin >= 0 ? '+' : '' ?>$<?= number_format($margin, 4) ?>
              </td>
              <td class="px-4 py-3.5 text-right">
                <div class="inline-flex gap-2">
                  <button type="button" onclick="editService(<?= htmlspecialchars(json_encode($s)) ?>)" class="text-xs text-blue-400 hover:underline">
                    Éditer
                  </button>
                  <form method="POST" action="<?= APP_BASE ?>/admin/services/delete" onsubmit="return confirm('Supprimer ce service ?')">
                    <?= Auth::csrfField() ?>
                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
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

    <!-- Formulaire d'édition manuelle de service -->
    <div class="rounded-2xl border p-5 h-fit" style="background:#0d1117;border-color:#1a2332">
      <h3 id="form-service-title" class="font-bold text-white text-sm mb-4">Ajout / Édition de Service</h3>
      <form method="POST" action="<?= APP_BASE ?>/admin/services/save" id="form-service" class="space-y-4">
        <?= Auth::csrfField() ?>
        <input type="hidden" name="id" id="svc-id" value="0">

        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5" for="svc-provider">Grossiste lié</label>
          <select name="provider_id" id="svc-provider" required class="input-field w-full px-3 py-2.5 rounded-xl text-sm">
            <option value="">— Sélectionner le grossiste —</option>
            <?php foreach ($allProviders as $p): ?>
              <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (<?= $p['status'] ? 'Actif' : 'Maintenance' ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-medium text-gray-400 mb-1.5" for="svc-category">Catégorie</label>
            <input type="text" name="category" id="svc-category" required placeholder="Instagram, TikTok..." class="input-field w-full px-3 py-2.5 rounded-xl text-sm">
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-400 mb-1.5" for="svc-external-id">ID Service Externe</label>
            <input type="number" name="external_service_id" id="svc-external-id" required placeholder="125" class="input-field w-full px-3 py-2.5 rounded-xl text-sm font-mono">
          </div>
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5" for="svc-name">Nom du service à Bukavu</label>
          <input type="text" name="name" id="svc-name" required placeholder="ex: Abonnés réels Bukavu..." class="input-field w-full px-3 py-2.5 rounded-xl text-sm">
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-medium text-gray-400 mb-1.5" for="svc-min">Qté Min</label>
            <input type="number" name="min_quantity" id="svc-min" required value="10" class="input-field w-full px-3 py-2.5 rounded-xl text-sm font-mono">
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-400 mb-1.5" for="svc-max">Qté Max</label>
            <input type="number" name="max_quantity" id="svc-max" required value="10000" class="input-field w-full px-3 py-2.5 rounded-xl text-sm font-mono">
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-medium text-gray-400 mb-1.5" for="svc-buying">Prix d'Achat ($)</label>
            <input type="number" name="original_rate" id="svc-buying" step="0.0001" required placeholder="0.1200" class="input-field w-full px-3 py-2.5 rounded-xl text-sm font-mono">
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-400 mb-1.5" for="svc-selling">Prix de Vente local ($)</label>
            <input type="number" name="calculated_rate" id="svc-selling" step="0.0001" required placeholder="1.5000" class="input-field w-full px-3 py-2.5 rounded-xl text-sm font-mono">
          </div>
        </div>

        <div class="flex items-center gap-2">
          <input type="checkbox" name="is_active" id="svc-active" value="1" checked class="rounded border-[#1a2332] bg-[#0a0f1a] text-emerald-500 focus:ring-emerald-500/20">
          <label class="text-xs text-gray-300" for="svc-active">Service actif</label>
        </div>

        <div class="flex gap-2 pt-2">
          <button type="submit" class="btn-primary flex-1 py-2.5 rounded-xl text-xs font-bold">Enregistrer</button>
          <button type="button" onclick="cancelServiceEdit()" class="hidden border border-[#1a2332] text-gray-400 px-3 py-2.5 rounded-xl text-xs hover:bg-white/5" id="btn-cancel-svc">Annuler</button>
        </div>
      </form>
    </div>
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
      <div class="overflow-x-auto">
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
    <div class="overflow-x-auto">
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
                <button type="submit" class="px-2 py-1 rounded bg-[#00ff88] text-black text-xs font-bold hover:opacity-90">Ajuster</button>
              </form>
            </td>
            <?php endif; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ========================================== -->
<!-- 5. ONGLET: CONFIGURATION (SUPERADMIN ONLY) -->
<!-- ========================================== -->
<?php if (Auth::isSuperAdmin()): ?>
<div id="content-settings" class="tab-content hidden">
  <form method="POST" action="<?= APP_BASE ?>/admin/settings/update" class="space-y-6">
    <?= Auth::csrfField() ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

      <!-- Configuration Générale & Réception Bukavu -->
      <div class="rounded-2xl border p-5 space-y-4" style="background:#0d1117;border-color:#1a2332">
        <h3 class="font-bold text-white text-sm border-b pb-2 mb-4 border-[#1a2332] flex items-center gap-1.5">
          <svg class="w-4 h-4 text-[#00ff88] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
          Paramètres Généraux & Mobile Money
        </h3>
        
        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5">Nom de la Plateforme</label>
          <input type="text" name="site_name" value="<?= htmlspecialchars($allSettings['site_name'] ?? 'BukavuBoost') ?>" class="input-field w-full px-3 py-2.5 rounded-xl text-sm">
        </div>

        <input type="hidden" name="markup_percentage" value="0">

        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5">Taux de change (1 USD = X CDF) pour le switch monétaire et les recharges en Francs Congolais</label>
          <input type="number" name="usd_rate_cdf" required value="<?= htmlspecialchars($allSettings['usd_rate_cdf'] ?? '2800') ?>" class="input-field w-full px-3 py-2.5 rounded-xl text-sm font-mono">
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-medium text-gray-400 mb-1.5">Numéro M-Pesa (Vodacom)</label>
            <input type="text" name="mpesa_number" value="<?= htmlspecialchars($allSettings['mpesa_number'] ?? '+243999999999') ?>" class="input-field w-full px-3 py-2.5 rounded-xl text-sm font-mono">
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-400 mb-1.5">Numéro Airtel Money</label>
            <input type="text" name="airtel_number" value="<?= htmlspecialchars($allSettings['airtel_number'] ?? '+243888888888') ?>" class="input-field w-full px-3 py-2.5 rounded-xl text-sm font-mono">
          </div>
        </div>

        <div class="grid grid-cols-1 gap-3">
          <div>
            <label class="block text-xs font-medium text-gray-400 mb-1.5">Numéro Orange Money</label>
            <input type="text" name="orange_number" value="<?= htmlspecialchars($allSettings['orange_number'] ?? '') ?>" class="input-field w-full px-3 py-2.5 rounded-xl text-sm font-mono">
          </div>
        </div>
      </div>

      <!-- Configuration Agrégateurs (PawaPay / VisaPay) -->
      <div class="rounded-2xl border p-5 space-y-4" style="background:#0d1117;border-color:#1a2332">
        <h3 class="font-bold text-white text-sm border-b pb-2 mb-4 border-[#1a2332] flex items-center gap-1.5">
          <svg class="w-4 h-4 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          Intégrations futures (PawaPay / VisaPay)
        </h3>
        
        <div class="pt-2">
          <div class="flex items-center justify-between">
            <label class="text-xs font-medium text-gray-300">Activer l'API PawaPay (Mobile Money RDC automatique)</label>
            <input type="checkbox" name="pawapay_enabled" value="1" <?= ($allSettings['pawapay_enabled'] ?? '0') === '1' ? 'checked' : '' ?> class="rounded border-[#1a2332] bg-[#0a0f1a] text-emerald-500 focus:ring-emerald-500/20">
          </div>
          <div class="mt-2 grid grid-cols-2 gap-2">
            <input type="text" name="pawapay_api_key" value="<?= htmlspecialchars($allSettings['pawapay_api_key'] ?? '') ?>" placeholder="Clé API PawaPay" class="input-field w-full px-2.5 py-2 rounded-lg text-xs font-mono">
            <input type="password" name="pawapay_secret" value="<?= htmlspecialchars($allSettings['pawapay_secret'] ?? '') ?>" placeholder="Secret PawaPay" class="input-field w-full px-2.5 py-2 rounded-lg text-xs font-mono">
          </div>
        </div>

        <div class="border-t border-[#1a2332] pt-4">
          <div class="flex items-center justify-between">
            <label class="text-xs font-medium text-gray-300">Activer l'API VisaPay (Cartes bancaires)</label>
            <input type="checkbox" name="visapay_enabled" value="1" <?= ($allSettings['visapay_enabled'] ?? '0') === '1' ? 'checked' : '' ?> class="rounded border-[#1a2332] bg-[#0a0f1a] text-emerald-500 focus:ring-emerald-500/20">
          </div>
          <div class="mt-2 grid grid-cols-2 gap-2">
            <input type="text" name="visapay_api_key" value="<?= htmlspecialchars($allSettings['visapay_api_key'] ?? '') ?>" placeholder="Clé API VisaPay" class="input-field w-full px-2.5 py-2 rounded-lg text-xs font-mono">
            <input type="password" name="visapay_secret" value="<?= htmlspecialchars($allSettings['visapay_secret'] ?? '') ?>" placeholder="Secret VisaPay" class="input-field w-full px-2.5 py-2 rounded-lg text-xs font-mono">
          </div>
        </div>
      </div>

    </div>

    <button type="submit" class="btn-primary w-full py-3 rounded-xl text-sm font-bold shadow-lg">
      Mettre à jour toute la configuration globale
    </button>
  </form>
</div>
<?php endif; ?>

<!-- ===== JAVASCRIPT GESTIONNAIRE DES TABS ET DES FORMULAIRES ===== -->
<script>
function switchTab(tabId) {
  document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.classList.remove('border-b-2', 'text-white');
    btn.style.borderColor = 'transparent';
  });

  const content = document.getElementById('content-' + tabId);
  if (content) {
    content.classList.remove('hidden');
  }
  const activeBtn = document.getElementById('tab-' + tabId);
  if (activeBtn) {
    activeBtn.classList.add('border-b-2', 'text-white');
    activeBtn.style.borderColor = '#00ff88';
  }

  localStorage.setItem('activeAdminTab', tabId);
}

document.addEventListener('DOMContentLoaded', () => {
  const activeTab = localStorage.getItem('activeAdminTab') || 'recharges';
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
  document.getElementById('svc-category').focus();
}

function cancelServiceEdit() {
  document.getElementById('form-service-title').textContent = "Créer un service";
  document.getElementById('form-service').reset();
  document.getElementById('svc-id').value = "0";
  document.getElementById('btn-cancel-svc').classList.add('hidden');
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
</script>
