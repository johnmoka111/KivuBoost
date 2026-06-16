<?php
$base = rtrim(APP_BASE, '/');

// Calculs globaux
$totalRevenue = 0; $totalCost = 0; $totalProfit = 0; $totalOrders = 0;
foreach ($report as $row) {
    $totalRevenue += (float)$row['total_revenue'];
    $totalCost    += (float)$row['total_cost'];
    $totalProfit  += (float)$row['net_profit'];
    $totalOrders  += (int)$row['total_orders'];
}
$profitMargin = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 1) : 0;

// Max pour les barres de graphe
$maxRevenue = max(0.01, max(array_map(fn($r) => (float)$r['total_revenue'], $report ?: [['total_revenue' => 0]])));
$maxProfit  = max(0.01, max(array_map(fn($r) => abs((float)$r['net_profit']), $report ?: [['net_profit' => 0]])));
?>
<div class="max-w-6xl mx-auto">

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
      <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-2">
        <span class="w-2.5 h-6 rounded inline-block" style="background:#34d399"></span>
        Rapport Financier Mensuel
      </h1>
      <p class="text-xs text-gray-500 mt-1">Chiffre d'affaires, cout d'achat grossiste et benefice net reel par mois.</p>
    </div>
    <!-- Filtre periode -->
    <form method="GET" action="<?= $base ?>/admin/financial-report" class="flex items-center gap-2">
      <label class="text-[10px] text-gray-500 uppercase font-bold tracking-widest">Periode</label>
      <select name="months" onchange="this.form.submit()"
              class="px-3 py-2 rounded-xl text-xs text-white font-bold input-field">
        <?php foreach ([3, 6, 12, 24] as $m): ?>
          <option value="<?= $m ?>" <?= $months == $m ? 'selected' : '' ?>><?= $m ?> mois</option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>

  <!-- KPI Cards -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <?php
    $kpis = [
      ['label' => 'Chiffre d\'affaires', 'value' => '$' . number_format($totalRevenue, 2), 'color' => '#00d4ff', 'sub' => 'Total facture aux clients'],
      ['label' => 'Cout Grossiste',      'value' => '$' . number_format($totalCost, 2),    'color' => '#f87171', 'sub' => 'Total achete au fournisseur'],
      ['label' => 'Benefice Net',         'value' => '$' . number_format($totalProfit, 2),  'color' => '#34d399', 'sub' => 'CA - Cout grossiste'],
      ['label' => 'Marge nette',          'value' => $profitMargin . '%',                   'color' => '#a78bfa', 'sub' => 'Profit / CA x 100'],
    ];
    foreach ($kpis as $kpi): ?>
    <div class="rounded-2xl border p-4" style="background:#0d1117;border-color:#1a2332">
      <div class="text-[10px] uppercase font-bold tracking-widest mb-1" style="color:<?= $kpi['color'] ?>"><?= $kpi['label'] ?></div>
      <div class="text-2xl font-black text-white mb-0.5" style="color:<?= $kpi['color'] ?>"><?= $kpi['value'] ?></div>
      <div class="text-[10px] text-gray-600"><?= $kpi['sub'] ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php if (empty($report)): ?>
    <div class="rounded-2xl border p-16 text-center" style="background:#0d1117;border-color:#1a2332">
      <svg class="w-12 h-12 mx-auto mb-3 opacity-30 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
      </svg>
      <p class="text-gray-500 font-semibold text-sm">Aucune donnee de commande pour la periode selectionnee.</p>
      <p class="text-gray-700 text-xs mt-1">Les donnees apparaitront ici lorsque des commandes seront completees.</p>
    </div>
  <?php else: ?>

  <!-- Graphique en barres -->
  <div class="rounded-2xl border mb-8 overflow-hidden" style="background:#0d1117;border-color:#1a2332">
    <div class="px-5 py-4 border-b" style="border-color:#1a2332">
      <h2 class="text-sm font-bold text-white">Graphique CA vs Cout vs Benefice</h2>
    </div>
    <div class="p-5">
      <!-- Legende -->
      <div class="flex flex-wrap gap-4 mb-6 text-[10px] font-bold uppercase tracking-wider">
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm" style="background:#00d4ff"></span> CA (Ventes)</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm" style="background:#f87171"></span> Cout Grossiste</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm" style="background:#34d399"></span> Benefice Net</span>
      </div>

      <!-- Barres -->
      <div class="flex items-end gap-2 overflow-x-auto pb-2" style="min-height: 180px">
        <?php foreach ($report as $row):
          $revH    = $maxRevenue > 0 ? round(((float)$row['total_revenue'] / $maxRevenue) * 160, 1) : 0;
          $costH   = $maxRevenue > 0 ? round(((float)$row['total_cost']    / $maxRevenue) * 160, 1) : 0;
          $profitH = $maxRevenue > 0 ? round(abs((float)$row['net_profit'] / $maxRevenue) * 160, 1) : 0;
          $isNeg   = (float)$row['net_profit'] < 0;
        ?>
          <div class="flex flex-col items-center gap-1 shrink-0" style="min-width:<?= max(40, min(80, 480 / count($report))) ?>px">
            <div class="flex items-end gap-0.5" style="height:160px">
              <!-- CA -->
              <div class="rounded-t-sm w-3.5 transition-all" style="height:<?= $revH ?>px;background:#00d4ff;min-height:2px"
                   title="CA: $<?= number_format((float)$row['total_revenue'], 2) ?>"></div>
              <!-- Cout -->
              <div class="rounded-t-sm w-3.5 transition-all" style="height:<?= $costH ?>px;background:#f87171;min-height:2px"
                   title="Cout: $<?= number_format((float)$row['total_cost'], 2) ?>"></div>
              <!-- Profit -->
              <div class="rounded-t-sm w-3.5 transition-all" style="height:<?= $profitH ?>px;background:<?= $isNeg ? '#ef4444' : '#34d399' ?>;min-height:2px"
                   title="Benefice: $<?= number_format((float)$row['net_profit'], 2) ?>"></div>
            </div>
            <span class="text-[9px] text-gray-500 text-center leading-tight"><?= htmlspecialchars($row['month_label']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Tableau detaille -->
  <div class="rounded-2xl border" style="background:#0d1117;border-color:#1a2332">
    <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color:#1a2332">
      <h2 class="text-sm font-bold text-white">Detail par mois</h2>
      <span class="text-[10px] text-gray-500"><?= count($report) ?> mois</span>
    </div>

    <!-- Desktop -->
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr style="border-bottom:1px solid #1a2332" class="text-gray-500 text-[10px] font-semibold uppercase tracking-widest bg-[#0a0f1a]/30">
            <th class="text-left px-5 py-3">Mois</th>
            <th class="text-right px-5 py-3">Commandes</th>
            <th class="text-right px-5 py-3">CA (ventes)</th>
            <th class="text-right px-5 py-3">Cout Grossiste</th>
            <th class="text-right px-5 py-3">Benefice Net</th>
            <th class="text-right px-5 py-3">Marge %</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#1a2332]">
          <?php foreach ($report as $row):
            $margin = (float)$row['total_revenue'] > 0
              ? round(((float)$row['net_profit'] / (float)$row['total_revenue']) * 100, 1)
              : 0;
            $profitColor = (float)$row['net_profit'] >= 0 ? '#34d399' : '#f87171';
          ?>
          <tr class="hover:bg-white/[0.01] transition-colors">
            <td class="px-5 py-3.5 font-semibold text-white text-xs"><?= htmlspecialchars($row['month_label']) ?></td>
            <td class="px-5 py-3.5 text-right text-xs text-gray-300"><?= number_format((int)$row['total_orders']) ?></td>
            <td class="px-5 py-3.5 text-right text-xs font-semibold" style="color:#00d4ff">$<?= number_format((float)$row['total_revenue'], 2) ?></td>
            <td class="px-5 py-3.5 text-right text-xs text-red-400">$<?= number_format((float)$row['total_cost'], 2) ?></td>
            <td class="px-5 py-3.5 text-right text-xs font-black" style="color:<?= $profitColor ?>">
              $<?= number_format((float)$row['net_profit'], 2) ?>
            </td>
            <td class="px-5 py-3.5 text-right">
              <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full <?= $margin >= 0 ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20' ?>">
                <?= $margin ?>%
              </span>
            </td>
          </tr>
          <?php endforeach; ?>
          <!-- Ligne total -->
          <tr class="border-t-2" style="border-color:#2a3347;background:#0a0f1a">
            <td class="px-5 py-3.5 font-black text-white text-xs">Total (<?= $months ?> mois)</td>
            <td class="px-5 py-3.5 text-right text-xs font-black text-white"><?= number_format($totalOrders) ?></td>
            <td class="px-5 py-3.5 text-right text-xs font-black" style="color:#00d4ff">$<?= number_format($totalRevenue, 2) ?></td>
            <td class="px-5 py-3.5 text-right text-xs font-black text-red-400">$<?= number_format($totalCost, 2) ?></td>
            <td class="px-5 py-3.5 text-right text-xs font-black" style="color:<?= $totalProfit >= 0 ? '#34d399' : '#f87171' ?>">$<?= number_format($totalProfit, 2) ?></td>
            <td class="px-5 py-3.5 text-right">
              <span class="inline-block text-[10px] font-black px-2.5 py-1 rounded-full <?= $profitMargin >= 0 ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/20' : 'bg-red-500/10 text-red-300 border border-red-500/20' ?>">
                <?= $profitMargin ?>%
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Mobile cards -->
    <div class="block md:hidden divide-y divide-[#1a2332]">
      <?php foreach ($report as $row):
        $margin = (float)$row['total_revenue'] > 0
          ? round(((float)$row['net_profit'] / (float)$row['total_revenue']) * 100, 1) : 0;
        $profitColor = (float)$row['net_profit'] >= 0 ? '#34d399' : '#f87171';
      ?>
      <div class="p-4 space-y-2">
        <div class="flex items-center justify-between">
          <span class="font-bold text-white text-sm"><?= htmlspecialchars($row['month_label']) ?></span>
          <span class="text-[10px] font-bold px-2 py-0.5 rounded-full <?= $margin >= 0 ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20' ?>"><?= $margin ?>%</span>
        </div>
        <div class="grid grid-cols-3 gap-2 text-center">
          <div class="rounded-lg p-2" style="background:rgba(0,212,255,0.05)">
            <div class="text-[9px] text-gray-500 mb-0.5">CA</div>
            <div class="text-xs font-black" style="color:#00d4ff">$<?= number_format((float)$row['total_revenue'], 2) ?></div>
          </div>
          <div class="rounded-lg p-2" style="background:rgba(248,113,113,0.05)">
            <div class="text-[9px] text-gray-500 mb-0.5">Cout</div>
            <div class="text-xs font-black text-red-400">$<?= number_format((float)$row['total_cost'], 2) ?></div>
          </div>
          <div class="rounded-lg p-2" style="background:rgba(52,211,153,0.05)">
            <div class="text-[9px] text-gray-500 mb-0.5">Benefice</div>
            <div class="text-xs font-black" style="color:<?= $profitColor ?>">$<?= number_format((float)$row['net_profit'], 2) ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <?php endif; ?>
</div>
