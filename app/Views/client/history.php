<?php
use App\Core\Auth;
use App\Core\Currency;
$pageTitle = 'Mes Tableaux de Bord';

// Calcul du total dépensé
$totalSpentUsd = array_sum(array_column($orders, 'cost'));
?>

<!-- ===== HEADER STATS ===== -->
<div class="grid grid-cols-2 gap-4 mb-6">
  <!-- Total commandes -->
  <div class="rounded-xl p-4 border transition-all hover:border-gray-700" style="background:#0d1117;border-color:#1a2332">
    <div class="text-xs text-gray-500 mb-1 uppercase tracking-wider font-semibold">Commandes Totales</div>
    <div class="text-2xl font-bold text-white"><?= count($orders) ?></div>
  </div>

  <!-- Dépensé -->
  <div class="rounded-xl p-4 border transition-all hover:border-gray-700" style="background:#0d1117;border-color:#1a2332">
    <div class="text-xs text-gray-500 mb-1 uppercase tracking-wider font-semibold">Total Dépensé</div>
    <div class="text-2xl font-bold text-white">
      <?= Currency::format((float)$totalSpentUsd) ?>
    </div>
  </div>
</div>

<!-- ===== HISTORIQUE DES COMMANDES ===== -->
<div class="rounded-2xl border mt-6" style="background:#0d1117;border-color:#1a2332">
  <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:#1a2332">
    <div class="flex items-center gap-2">
      <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(0,212,255,0.1)">
        <svg class="w-4 h-4" style="color:#00d4ff" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
      </div>
      <h2 class="text-base font-bold text-white">Mes Commandes</h2>
    </div>
    <span class="text-xs text-gray-500"><?= count($orders) ?> commande(s)</span>
  </div>

  <?php if (empty($orders)): ?>
    <div class="text-center py-16 text-gray-600">
      <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
      </svg>
      <p class="text-sm">Aucune commande pour le moment.</p>
    </div>
  <?php else: ?>

    <!-- Table Desktop -->
    <div class="hidden lg:block overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr style="border-bottom:1px solid #1a2332">
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
            <?php if (Auth::isAdmin()): ?>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Client</th>
            <?php endif; ?>
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
            <td class="px-5 py-3.5 text-gray-500 font-mono text-xs">#<?= $order['id'] ?></td>
            <?php if (Auth::isAdmin()): ?>
            <td class="px-5 py-3.5 text-white text-xs font-semibold"><?= htmlspecialchars($order['username'] ?? '—') ?></td>
            <?php endif; ?>
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
                  'pending'    => 'badge-pending',
                  'processing' => 'badge-processing',
                  'completed'  => 'badge-completed',
                  'canceled'   => 'badge-canceled',
                  'partial'    => 'badge-partial',
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

    <!-- Cards Mobile (Optimisé Sans Scroll) -->
    <div class="lg:hidden divide-y" style="divide-color:#1a2332">
      <?php foreach ($orders as $order): ?>
      <?php
      $badgeClass = match(strtolower($order['status'])) {
          'pending'    => 'badge-pending',
          'processing' => 'badge-processing',
          'completed'  => 'badge-completed',
          'canceled'   => 'badge-canceled',
          'partial'    => 'badge-partial',
          default      => 'badge-pending',
      };
      ?>
      <div class="px-4 py-4">
        <div class="flex items-start justify-between mb-2">
          <div class="flex-1 min-w-0 pr-3">
            <div class="text-sm font-semibold text-white truncate"><?= htmlspecialchars($order['service_name'] ?? '—') ?></div>
            <div class="text-xs text-gray-500 mt-0.5 truncate"><?= htmlspecialchars($order['category'] ?? '') ?></div>
          </div>
          <span class="<?= $badgeClass ?> text-xs px-2.5 py-1 rounded-full font-medium shrink-0">
            <?= htmlspecialchars($order['status']) ?>
          </span>
        </div>
        <div class="flex flex-wrap gap-4 text-xs mt-3 text-gray-400">
          <span>Qté : <strong class="text-white"><?= number_format($order['quantity']) ?></strong></span>
          <span>Coût : <strong style="color:#00ff88"><?= Currency::format((float)$order['cost'], 3) ?></strong></span>
          <span>Date : <strong class="text-white"><?= date('d/m H:i', strtotime($order['created_at'])) ?></strong></span>
        </div>
        <a href="<?= htmlspecialchars($order['link']) ?>" target="_blank" rel="noopener"
           class="inline-block mt-3 text-xs hover:underline truncate max-w-full text-[#00d4ff]">
          <?= htmlspecialchars($order['link']) ?>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- ===== HISTORIQUE DES ABONNEMENTS ===== -->
<div class="rounded-2xl border mt-6" style="background:#0d1117;border-color:#1a2332">
  <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:#1a2332">
    <div class="flex items-center gap-2">
      <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(0,255,136,0.1)">
        <svg class="w-4 h-4" style="color:#00ff88" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H17"/>
        </svg>
      </div>
      <h2 class="text-base font-bold text-white">Mes Abonnements Automatiques</h2>
    </div>
    <span class="text-xs text-gray-500"><?= count($subscriptions ?? []) ?> abonnement(s)</span>
  </div>

  <?php if (empty($subscriptions)): ?>
    <div class="text-center py-16 text-gray-600">
      <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H17"/>
      </svg>
      <p class="text-sm">Aucun abonnement actif pour le moment.</p>
    </div>
  <?php else: ?>

    <!-- Table Desktop -->
    <div class="hidden lg:block overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr style="border-bottom:1px solid #1a2332">
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
            <?php if (Auth::isAdmin()): ?>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Client</th>
            <?php endif; ?>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Service</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Compte Cible</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Quantité Min - Max</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Posts Restants</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Statut</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date de création</th>
          </tr>
        </thead>
        <tbody class="divide-y" style="divide-color:#1a2332">
          <?php foreach ($subscriptions as $sub): ?>
          <tr class="hover:bg-white/[0.02] transition-colors">
            <td class="px-5 py-3.5 text-gray-500 font-mono text-xs">#<?= $sub['id'] ?></td>
            <?php if (Auth::isAdmin()): ?>
            <td class="px-5 py-3.5 text-white text-xs font-semibold"><?= htmlspecialchars($sub['client_username'] ?? '—') ?></td>
            <?php endif; ?>
            <td class="px-5 py-3.5">
              <div class="text-white text-xs font-semibold"><?= htmlspecialchars($sub['service_name'] ?? '—') ?></div>
              <div class="text-gray-500 text-xs mt-0.5"><?= htmlspecialchars($sub['category'] ?? '') ?></div>
            </td>
            <td class="px-5 py-3.5 font-bold text-[#00d4ff] text-xs">
              @<?= htmlspecialchars($sub['username']) ?>
            </td>
            <td class="px-5 py-3.5 text-white text-xs font-mono"><?= number_format($sub['min_quantity']) ?> - <?= number_format($sub['max_quantity']) ?></td>
            <td class="px-5 py-3.5 text-white text-xs font-mono"><?= (int)$sub['posts'] ?> posts</td>
            <td class="px-5 py-3.5">
              <span class="px-2.5 py-1 rounded-full font-medium text-xs bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                <?= htmlspecialchars($sub['status']) ?>
              </span>
            </td>
            <td class="px-5 py-3.5 text-gray-500 text-xs">
              <?= date('d/m/Y H:i', strtotime($sub['created_at'])) ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Cards Mobile -->
    <div class="lg:hidden divide-y" style="divide-color:#1a2332">
      <?php foreach ($subscriptions as $sub): ?>
      <div class="px-4 py-4">
        <div class="flex items-start justify-between mb-2">
          <div class="flex-1 min-w-0 pr-3">
            <div class="text-sm font-semibold text-white truncate"><?= htmlspecialchars($sub['service_name'] ?? '—') ?></div>
            <div class="text-xs text-gray-500 mt-0.5 truncate"><?= htmlspecialchars($sub['category'] ?? '') ?></div>
          </div>
          <span class="px-2.5 py-1 rounded-full font-medium text-xs bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 shrink-0">
            <?= htmlspecialchars($sub['status']) ?>
          </span>
        </div>
        <div class="flex flex-wrap gap-4 text-xs mt-3 text-gray-400">
          <span>Compte : <strong class="text-[#00d4ff]">@<?= htmlspecialchars($sub['username']) ?></strong></span>
          <span>Qté : <strong class="text-white"><?= number_format($sub['min_quantity']) ?>-<?= number_format($sub['max_quantity']) ?></strong></span>
          <span>Posts : <strong class="text-white"><?= (int)$sub['posts'] ?></strong></span>
          <span>Date : <strong class="text-white"><?= date('d/m H:i', strtotime($sub['created_at'])) ?></strong></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
