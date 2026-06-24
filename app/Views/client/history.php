<?php
use App\Core\Auth;
use App\Core\Currency;
$pageTitle = 'Mes Commandes';

// Utilisation des statistiques globales issues du contrôleur
$totalSpentUsd  = $orderStats['spent'] ?? 0.0;
$totalCompleted = $orderStats['completed'] ?? 0;
$totalPending   = $orderStats['pending'] ?? 0;
$totalOrdersCount = $orderStats['total'] ?? 0;
?>

<!-- ===== HEADER STATS ===== -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
  <div class="rounded-xl p-4 border" style="background:#0d1117;border-color:#1a2332">
    <div class="text-[10px] text-gray-500 mb-1 uppercase tracking-widest font-semibold">Total Commandes</div>
    <div class="text-2xl font-bold text-white"><?= $totalOrdersCount ?></div>
  </div>
  <div class="rounded-xl p-4 border" style="background:#0d1117;border-color:#1a2332">
    <div class="text-[10px] text-gray-500 mb-1 uppercase tracking-widest font-semibold">Complétées</div>
    <div class="text-2xl font-bold text-emerald-400"><?= $totalCompleted ?></div>
  </div>
  <div class="rounded-xl p-4 border" style="background:#0d1117;border-color:#1a2332">
    <div class="text-[10px] text-gray-500 mb-1 uppercase tracking-widest font-semibold">En Cours</div>
    <div class="text-2xl font-bold text-yellow-400"><?= $totalPending ?></div>
  </div>
  <div class="rounded-xl p-4 border" style="background:#0d1117;border-color:#1a2332">
    <div class="text-[10px] text-gray-500 mb-1 uppercase tracking-widest font-semibold">Total Dépensé</div>
    <div class="text-xl font-bold" style="color:#00ff88"><?= Currency::format((float)$totalSpentUsd) ?></div>
  </div>
</div>

<!-- ===== NAVIGATION PAR ONGLETS (TABS) ===== -->
<div class="flex gap-2.5 mb-6 select-none" id="historyTabs">
  <button onclick="switchHistoryTab('orders')" class="hist-tab-btn px-4 py-2.5 text-xs font-bold rounded-xl border bg-[#00d4ff]/10 border-[#00d4ff]/30 text-[#00d4ff] transition-all flex items-center gap-1.5" id="hist-tab-orders">
    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
    Mes Commandes (<?= $totalOrdersCount ?>)
  </button>
  <button onclick="switchHistoryTab('recharges')" class="hist-tab-btn px-4 py-2.5 text-xs font-bold rounded-xl border border-transparent bg-transparent text-gray-400 hover:bg-white/5 transition-all flex items-center gap-1.5" id="hist-tab-recharges">
    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    Mes Dépôts & Recharges (<?= count($recharges) ?>)
  </button>
</div>

<!-- ===== HISTORIQUE DES COMMANDES ===== -->
<div id="content-orders" class="hist-tab-content">
  <div class="rounded-2xl border" style="background:#0d1117;border-color:#1a2332">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 px-5 py-4 border-b" style="border-color:#1a2332">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:rgba(0,212,255,0.1)">
          <svg class="w-4 h-4" style="color:#00d4ff" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
        </div>
        <h2 class="text-base font-bold text-white">Mes Commandes</h2>
      </div>
      <div class="flex flex-col sm:flex-row items-center justify-between w-full sm:w-auto gap-3">
        <!-- Recherche rapide -->
        <div class="relative w-full sm:w-48">
          <input type="text" id="searchOrder" onkeyup="filterOrders()" placeholder="Rechercher (ID, Service)..." 
                 class="w-full text-xs pl-8 pr-3 py-1.5 rounded-lg border focus:outline-none"
                 style="background:#0a0f1a;border-color:#1a2332;color:#e2e8f0">
          <svg class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <!-- Filtre rapide -->
        <select id="statusFilter" onchange="filterOrders()"
                class="w-full sm:w-auto text-xs px-3 py-1.5 rounded-lg border text-gray-300 focus:outline-none"
                style="background:#0a0f1a;border-color:#1a2332">
          <option value="">Tous les statuts</option>
          <option value="Pending">En attente</option>
          <option value="Processing">En cours</option>
          <option value="Completed">Complété</option>
          <option value="Canceled">Annulé</option>
          <option value="Partial">Partiel</option>
        </select>
        <span class="text-xs text-gray-500 whitespace-nowrap hidden sm:inline"><?= $totalOrdersCount ?> cmd(s)</span>
      </div>
    </div>

    <?php if (empty($orders)): ?>
      <div class="text-center py-16 text-gray-600">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="text-sm font-medium text-gray-500">Aucune commande pour le moment.</p>
        <p class="text-xs text-gray-700 mt-1">Vos commandes SMM apparaîtront ici.</p>
      </div>
    <?php else: ?>

      <!-- ====== TABLE DESKTOP ====== -->
      <div class="hidden md:block overflow-x-auto w-full">
        <table class="w-full text-sm" id="ordersTable">
          <thead>
            <tr style="border-bottom:1px solid #1a2332">
              <th class="text-left px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Commande</th>
              <?php if (Auth::isAdmin()): ?>
              <th class="text-left px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Client</th>
              <?php endif; ?>
              <th class="text-left px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Service</th>
              <th class="text-left px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Lien Cible</th>
              <th class="text-left px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Quantité</th>
              <th class="text-left px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Coût</th>
              <th class="text-left px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Statut</th>
              <th class="text-left px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Date</th>
            </tr>
          </thead>
          <tbody class="divide-y" style="divide-color:#1a2332">
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
            <tr class="hover:bg-white/[0.02] transition-colors order-row" data-status="<?= htmlspecialchars($order['status']) ?>">

              <!-- ID + Ref externe -->
              <td class="px-4 py-3.5">
                <div class="font-mono text-xs font-bold text-white order-id">#<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></div>
                <?php if (!empty($order['external_order_id'])): ?>
                <div class="text-[10px] text-gray-600 mt-0.5 font-mono">Réf: <?= htmlspecialchars($order['external_order_id']) ?></div>
                <?php endif; ?>
              </td>

              <!-- Client (admin only) -->
              <?php if (Auth::isAdmin()): ?>
              <td class="px-4 py-3.5">
                <span class="text-xs font-semibold text-white"><?= htmlspecialchars($order['username'] ?? '—') ?></span>
              </td>
              <?php endif; ?>

              <!-- Service -->
              <td class="px-4 py-3.5 max-w-[220px]">
                <div class="text-xs font-semibold text-white leading-snug"><?= htmlspecialchars($order['service_name'] ?? '—') ?></div>
                <?php if (!empty($order['category'])): ?>
                <span class="inline-block mt-1 text-[10px] px-2 py-0.5 rounded-full font-medium bg-[#1a2332] text-gray-400">
                  <?= htmlspecialchars($order['category']) ?>
                </span>
                <?php endif; ?>
              </td>

              <!-- Lien cible -->
              <td class="px-4 py-3.5 max-w-[180px]">
                <a href="<?= htmlspecialchars($order['link']) ?>" target="_blank" rel="noopener"
                   class="text-xs hover:underline block truncate" style="color:#00d4ff"
                   title="<?= htmlspecialchars($order['link']) ?>">
                  <?= htmlspecialchars(parse_url($order['link'], PHP_URL_HOST) ?: $order['link']) ?>
                </a>
              </td>

              <!-- Quantité -->
              <td class="px-4 py-3.5">
                <span class="text-xs font-mono font-bold text-white"><?= number_format($order['quantity']) ?></span>
              </td>

              <!-- Coût -->
              <td class="px-4 py-3.5">
                <span class="text-xs font-mono font-bold" style="color:#00ff88">
                  <?= Currency::format((float)$order['cost'], 3) ?>
                </span>
              </td>

              <!-- Statut -->
              <td class="px-4 py-3.5">
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
              </td>

              <!-- Date -->
              <td class="px-4 py-3.5">
                <div class="text-xs text-gray-400"><?= date('d/m/Y', strtotime($order['created_at'])) ?></div>
                <div class="text-[10px] text-gray-600"><?= date('H:i', strtotime($order['created_at'])) ?></div>
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
        <div class="p-4 rounded-xl border order-row transition-all shadow-sm" style="background:#0d1117; border-color:#1a2332;" data-status="<?= htmlspecialchars($order['status']) ?>">
          <div class="flex items-start justify-between mb-3 gap-2">
            <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1.5">
              <span class="text-[10px] font-mono font-bold text-gray-500 bg-[#1a2332]/50 px-1.5 py-0.5 rounded order-id">#<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></span>
              <?php if (!empty($order['category'])): ?>
              <span class="text-[10px] px-2 py-0.5 rounded-full font-medium bg-[#1a2332] text-gray-400 truncate">
                <?= htmlspecialchars($order['category']) ?>
              </span>
              <?php endif; ?>
            </div>
            <div class="text-sm font-semibold text-white leading-snug break-words order-service"><?= htmlspecialchars($order['service_name'] ?? '—') ?></div>
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

      <!-- Contrôles de pagination -->
      <?php if ($totalPages > 1): ?>
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-5 py-4 border-t" style="border-color:#1a2332; background:#0a0f1a/30">
          <div class="text-xs text-gray-500">
            Affichage de la page <span class="text-white font-semibold"><?= $page ?></span> sur <span class="text-white font-semibold"><?= $totalPages ?></span> (<?= $totalOrders ?> commandes)
          </div>
          <div class="flex items-center gap-1.5">
            <?php if ($page > 1): ?>
              <a href="?page=<?= $page - 1 ?>" class="px-3 py-1.5 rounded-lg border text-xs font-semibold text-gray-300 hover:bg-white/5 transition-all" style="border-color:#1a2332">
                Précédent
              </a>
            <?php else: ?>
              <span class="px-3 py-1.5 rounded-lg border text-xs font-semibold text-gray-600 cursor-not-allowed" style="border-color:#1a2332">
                Précédent
              </span>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            for ($i = $start; $i <= $end; $i++):
              $isActive = ($i === $page);
              $btnStyle = $isActive 
                ? 'background:#00d4ff/10; border-color:#00d4ff/30; color:#00d4ff' 
                : 'border-color:#1a2332; color:#a1a1aa; background:transparent';
              $activeClass = $isActive ? '' : 'hover:bg-white/5';
            ?>
              <a href="?page=<?= $i ?>" class="px-3 py-1.5 rounded-lg border text-xs font-mono font-bold transition-all <?= $activeClass ?>" style="<?= $btnStyle ?>">
                <?= $i ?>
              </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
              <a href="?page=<?= $page + 1 ?>" class="px-3 py-1.5 rounded-lg border text-xs font-semibold text-gray-300 hover:bg-white/5 transition-all" style="border-color:#1a2332">
                Suivant
              </a>
            <?php else: ?>
              <span class="px-3 py-1.5 rounded-lg border text-xs font-semibold text-gray-600 cursor-not-allowed" style="border-color:#1a2332">
                Suivant
              </span>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

    <?php endif; ?>
  </div>
</div>

<!-- ===== HISTORIQUE DES RECHARGES / DÉPÔTS ===== -->
<div id="content-recharges" class="hist-tab-content hidden">
  <div class="rounded-2xl border" style="background:#0d1117;border-color:#1a2332">
    <!-- Header -->
    <div class="flex items-center gap-2 px-5 py-4 border-b" style="border-color:#1a2332">
      <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:rgba(16,185,129,0.1)">
        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <h2 class="text-base font-bold text-white">Historique de mes dépôts</h2>
    </div>

    <?php if (empty($recharges)): ?>
      <div class="text-center py-16 text-gray-600">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-30 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm font-medium text-gray-500">Aucun dépôt enregistré.</p>
        <p class="text-xs text-gray-700 mt-1">Vos demandes de recharge apparaîtront ici.</p>
      </div>
    <?php else: ?>

      <!-- Desktop View -->
      <div class="hidden md:block overflow-x-auto w-full">
        <table class="w-full text-sm">
          <thead>
            <tr style="border-bottom:1px solid #1a2332" class="text-gray-500 text-[10px] font-semibold uppercase tracking-widest bg-[#0a0f1a]/30">
              <th class="text-left px-5 py-3">Réseau / Méthode</th>
              <th class="text-left px-5 py-3">Montant original</th>
              <th class="text-left px-5 py-3">Référence / SMS</th>
              <th class="text-left px-5 py-3">Statut</th>
              <th class="text-left px-5 py-3">Date</th>
              <th class="text-left px-5 py-3">Détails / Motif</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#1a2332]">
            <?php foreach ($recharges as $r):
              $statusVal = strtolower($r['status']);
              $badgeClass = match($statusVal) {
                  'pending'  => 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20',
                  'approved' => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
                  'rejected' => 'bg-red-500/10 text-red-400 border border-red-500/20',
                  default    => 'bg-gray-500/10 text-gray-400 border border-gray-500/20',
              };
              $statusLabel = match($statusVal) {
                  'pending'  => 'En attente',
                  'approved' => 'Approuvé',
                  'rejected' => 'Rejeté',
                  default    => htmlspecialchars($r['status']),
              };
            ?>
            <tr class="hover:bg-white/[0.01] transition-all">
              <td class="px-5 py-4 font-semibold text-white">
                <?= htmlspecialchars($r['network']) ?>
              </td>
              <td class="px-5 py-4">
                <div class="font-bold font-mono text-emerald-400">
                  <?= $r['currency'] === 'CDF' ? number_format((float)$r['amount'], 0, ',', ' ') . ' CDF' : '$' . number_format((float)$r['amount'], 2) ?>
                </div>
              </td>
              <td class="px-5 py-4 font-mono text-xs text-gray-400 select-all">
                <?= htmlspecialchars($r['transaction_id']) ?>
              </td>
              <td class="px-5 py-4">
                <span class="inline-flex items-center gap-1 text-[10px] px-2.5 py-1 rounded-full font-semibold <?= $badgeClass ?>">
                  <?= $statusLabel ?>
                </span>
              </td>
              <td class="px-5 py-4 text-xs text-gray-400">
                <?= date('d/m/Y H:i', strtotime($r['created_at'])) ?>
              </td>
              <td class="px-5 py-4 text-xs text-gray-500">
                <?= !empty($r['notes']) ? htmlspecialchars($r['notes']) : '<span class="italic text-gray-700">—</span>' ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Mobile View -->
      <div class="md:hidden p-4 space-y-4 bg-[#0a0f1a] rounded-b-2xl">
        <?php foreach ($recharges as $r):
          $statusVal = strtolower($r['status']);
          $badgeClass = match($statusVal) {
              'pending'  => 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20',
              'approved' => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
              'rejected' => 'bg-red-500/10 text-red-400 border border-red-500/20',
              default    => 'bg-gray-500/10 text-gray-400 border border-gray-500/20',
          };
          $statusLabel = match($statusVal) {
              'pending'  => 'En attente',
              'approved' => 'Approuvé',
              'rejected' => 'Rejeté',
              default    => htmlspecialchars($r['status']),
          };
        ?>
        <div class="p-4 rounded-xl border flex flex-col gap-3" style="background:#0d1117;border-color:#1a2332">
          <!-- Nom / Méthode & Statut -->
          <div class="flex items-center justify-between">
            <span class="font-bold text-white text-sm"><?= htmlspecialchars($r['network']) ?></span>
            <span class="inline-flex items-center gap-1 text-[10px] px-2.5 py-1 rounded-full font-semibold <?= $badgeClass ?>">
              <?= $statusLabel ?>
            </span>
          </div>

          <!-- Montant & Référence -->
          <div class="flex items-center justify-between border-t border-b py-2.5 my-0.5" style="border-color:#1a2332">
            <div>
              <div class="text-[9px] text-gray-500 uppercase tracking-wider font-semibold">Montant</div>
              <div class="font-bold font-mono text-sm text-emerald-400">
                <?= $r['currency'] === 'CDF' ? number_format((float)$r['amount'], 0, ',', ' ') . ' CDF' : '$' . number_format((float)$r['amount'], 2) ?>
              </div>
            </div>
            <div class="text-right">
              <div class="text-[9px] text-gray-500 uppercase tracking-wider font-semibold">Référence / ID</div>
              <div class="text-xs font-mono text-gray-300 select-all"><?= htmlspecialchars($r['transaction_id']) ?></div>
            </div>
          </div>

          <!-- Date & Raison/Notes -->
          <div class="flex flex-col gap-1 text-xs">
            <div class="flex justify-between text-[11px]">
              <span class="text-gray-500">Date de demande :</span>
              <span class="text-gray-400 font-semibold"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></span>
            </div>
            <?php if (!empty($r['notes'])): ?>
            <div class="mt-1 bg-[#0a0f1a] p-2.5 rounded-lg border border-[#1a2332] text-xs text-gray-400">
              <span class="text-[9px] text-gray-500 block uppercase font-bold mb-0.5">Note de l'administration :</span>
              <?= htmlspecialchars($r['notes']) ?>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

    <?php endif; ?>
  </div>
</div>

<script>
function switchHistoryTab(tabId) {
  document.querySelectorAll('.hist-tab-content').forEach(el => el.classList.add('hidden'));
  document.querySelectorAll('.hist-tab-btn').forEach(btn => {
    btn.classList.remove('bg-[#00d4ff]/10', 'border-[#00d4ff]/30', 'text-[#00d4ff]');
    btn.classList.add('border-transparent', 'bg-transparent', 'text-gray-400');
  });

  const content = document.getElementById('content-' + tabId);
  if (content) {
    content.classList.remove('hidden');
  }
  const activeBtn = document.getElementById('hist-tab-' + tabId);
  if (activeBtn) {
    activeBtn.classList.remove('border-transparent', 'bg-transparent', 'text-gray-400');
    activeBtn.classList.add('bg-[#00d4ff]/10', 'border-[#00d4ff]/30', 'text-[#00d4ff]');
  }
}

function filterOrders() {
  const status = document.getElementById('statusFilter').value;
  const search = document.getElementById('searchOrder').value.toLowerCase();
  
  document.querySelectorAll('.order-row').forEach(row => {
    let show = true;
    
    // Status match
    if (status && row.dataset.status !== status) {
      show = false;
    }
    
    // Search match
    if (show && search) {
      const textContent = row.textContent.toLowerCase();
      if (!textContent.includes(search)) {
        show = false;
      }
    }
    
    row.style.display = show ? '' : 'none';
  });
}
</script>
