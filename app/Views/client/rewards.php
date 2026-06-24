<?php
use App\Core\Auth;
use App\Core\Currency;
use App\Models\Loyalty;

$pageTitle = 'Fidelite & Cashback';

$claimedPoints = (int)($user['lifetime_points'] ?? 0) - (int)($user['loyalty_points'] ?? 0);
$claimedCash   = $claimedPoints / 100.0;
$redeemValue   = (float)(($user['loyalty_points'] ?? 0) / 100.0);
$canRedeem     = (int)($user['loyalty_points'] ?? 0) >= 500;
$pointsNeeded  = max(0, 500 - (int)($user['loyalty_points'] ?? 0));
?>

<!-- ===== HEADER STATS ===== -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
  <div class="rounded-xl p-4 border" style="background:#0d1117;border-color:#1a2332">
    <div class="text-[10px] text-gray-500 mb-1 uppercase tracking-widest font-semibold">Total Depensé</div>
    <div class="text-2xl font-bold" style="color:#00ff88"><?= Currency::format((float)$totalSpent) ?></div>
    <div class="text-[9px] text-gray-600 mt-0.5">Sur commandes complétées</div>
  </div>
  <div class="rounded-xl p-4 border" style="background:#0d1117;border-color:#1a2332">
    <div class="text-[10px] text-gray-500 mb-1 uppercase tracking-widest font-semibold">Points Disponibles</div>
    <div class="text-2xl font-bold text-cyan-400"><?= number_format((int)($user['loyalty_points'] ?? 0)) ?></div>
    <div class="text-[9px] text-gray-600 mt-0.5">Échangeables en crédits</div>
  </div>
  <div class="rounded-xl p-4 border" style="background:#0d1117;border-color:#1a2332">
    <div class="text-[10px] text-gray-500 mb-1 uppercase tracking-widest font-semibold">Points Cumulés</div>
    <div class="text-2xl font-bold text-white"><?= number_format((int)($user['lifetime_points'] ?? 0)) ?></div>
    <div class="text-[9px] text-gray-600 mt-0.5">Total points à vie</div>
  </div>
  <div class="rounded-xl p-4 border" style="background:#0d1117;border-color:#1a2332">
    <div class="text-[10px] text-gray-500 mb-1 uppercase tracking-widest font-semibold">Cashback</div>
    <div class="text-2xl font-bold text-emerald-400"><?= number_format($currentTier['rate'] * 100, 2) ?>%</div>
    <div class="text-[9px] text-gray-600 mt-0.5"><?= $currentTier['name'] ?> — <?= $currentTier['pts_per_100'] ?> pts / 100$</div>
  </div>
</div>

<!-- ===== GRILLE PRINCIPALE ===== -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">

  <!-- CARTE ECHANGE -->
  <div class="md:col-span-2 rounded-xl border flex flex-col" style="background:#0d1117;border-color:#1a2332">
    <!-- Header -->
    <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:#1a2332">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:rgba(0,212,255,0.1)">
          <svg class="w-4 h-4" style="color:#00d4ff" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <h2 class="text-sm font-bold text-white">Convertir mes Points</h2>
      </div>
      <span class="text-[10px] text-gray-500 font-mono">100 pts = 1.00 USD</span>
    </div>

    <!-- Contenu -->
    <div class="p-5 flex-1 flex flex-col justify-between">
      <div>
        <!-- Compteur de points -->
        <div class="flex items-baseline gap-2 mb-5">
          <span class="text-5xl font-black text-cyan-400 font-mono"><?= number_format((int)($user['loyalty_points'] ?? 0)) ?></span>
          <span class="text-xs text-gray-500 font-semibold uppercase tracking-wider">points disponibles</span>
        </div>

        <!-- Barre de progression vers le seuil -->
        <?php $barPct = min(100, round(((int)($user['loyalty_points'] ?? 0) / 500) * 100)); ?>
        <div class="mb-1 flex justify-between text-[10px] text-gray-500">
          <span>Progression vers l'échange</span>
          <span><?= $barPct ?>% — min. 500 pts</span>
        </div>
        <div class="w-full h-2 bg-gray-900 rounded-full overflow-hidden mb-5 border" style="border-color:#1a2332">
          <div class="h-full rounded-full transition-all duration-700"
               style="width:<?= $barPct ?>%;background:linear-gradient(90deg,#06b6d4,#10b981)"></div>
        </div>

        <!-- Info valeur -->
        <div class="grid grid-cols-2 gap-3 mb-5">
          <div class="rounded-lg px-4 py-3 border" style="background:#0a0f1a;border-color:#1a2332">
            <div class="text-[9px] text-gray-500 uppercase tracking-widest mb-1">Valeur estimée</div>
            <div class="text-base font-black text-emerald-400"><?= Currency::format($redeemValue) ?></div>
          </div>
          <div class="rounded-lg px-4 py-3 border" style="background:#0a0f1a;border-color:#1a2332">
            <div class="text-[9px] text-gray-500 uppercase tracking-widest mb-1">Points échangés</div>
            <div class="text-base font-black text-amber-400"><?= number_format($claimedPoints) ?> pts</div>
          </div>
        </div>
      </div>

      <!-- Bouton -->
      <?php if ($canRedeem): ?>
        <form id="redeem-form" action="<?= APP_BASE ?>/rewards/redeem" method="POST"
              onsubmit="return handleRedeem(this);">
          <?= Auth::csrfField() ?>
          <button id="redeem-btn" type="submit"
                  class="w-full py-3 rounded-xl text-sm font-black text-black uppercase tracking-wider hover:opacity-90 transition-all"
                  style="background:linear-gradient(135deg,#00ff88,#00c466)">
            Echanger maintenant
          </button>
        </form>
        <p class="text-[10px] text-center text-emerald-400 font-medium mt-2">Votre solde sera crédité instantanément.</p>
        <script>
        function handleRedeem(form) {
            if (form.dataset.submitted === '1') {
                return false; // Bloquer la double soumission
            }
            if (!confirm('Convertir vos points en crédit solde ?')) {
                return false;
            }
            form.dataset.submitted = '1';
            var btn = document.getElementById('redeem-btn');
            btn.disabled = true;
            btn.textContent = '⏳ Traitement en cours...';
            btn.style.opacity = '0.6';
            btn.style.cursor = 'not-allowed';
            return true;
        }
        </script>
      <?php else: ?>
        <button disabled
                class="w-full py-3 rounded-xl text-xs font-bold text-gray-600 uppercase tracking-wider cursor-not-allowed border"
                style="background:#0a0f1a;border-color:#1a2332">
          Echanger maintenant
        </button>
        <p class="text-[10px] text-center text-amber-500 font-medium mt-2">
          Encore <strong><?= $pointsNeeded ?> points</strong> pour débloquer l'échange.
        </p>
      <?php endif; ?>
    </div>
  </div>

  <!-- CARTE NIVEAU -->
  <div class="rounded-xl border flex flex-col" style="background:#0d1117;border-color:#1a2332">
    <div class="flex items-center gap-2 px-5 py-4 border-b" style="border-color:#1a2332">
      <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:rgba(6,182,212,0.1)">
        <svg class="w-4 h-4" style="color:#06b6d4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
        </svg>
      </div>
      <h2 class="text-sm font-bold text-white">Mon Grade</h2>
    </div>
    <div class="p-5 flex-1 flex flex-col justify-between">
      <!-- Grade actuel -->
      <div class="text-center mb-5">
        <div class="inline-flex flex-col items-center gap-1 px-5 py-3 rounded-xl border" style="background:#0a0f1a;border-color:#1a2332">
          <span class="text-[9px] text-gray-500 uppercase tracking-widest font-semibold">Grade actuel</span>
          <span class="text-xl font-black text-white"><?= htmlspecialchars($currentTier['name']) ?></span>
          <span class="text-xs font-bold text-emerald-400"><?= number_format($currentTier['rate'] * 100, 2) ?>% cashback</span>
        </div>
      </div>

      <!-- Progression -->
      <div class="mb-4">
        <div class="flex justify-between text-[10px] text-gray-500 mb-1">
          <span class="text-cyan-400 font-bold"><?= htmlspecialchars($currentTier['name']) ?></span>
          <span class="font-bold"><?= $nextTier ? htmlspecialchars($nextTier['name']) : 'Max' ?></span>
        </div>
        <div class="w-full h-2.5 bg-gray-900 rounded-full overflow-hidden border" style="border-color:#1a2332">
          <div class="h-full rounded-full transition-all duration-700"
               style="width:<?= $progressPercent ?>%;background:linear-gradient(90deg,#06b6d4,#10b981)"></div>
        </div>
        <div class="text-center mt-2">
          <span class="text-lg font-black text-white"><?= $progressPercent ?>%</span>
        </div>
      </div>

      <!-- Message prochain grade -->
      <div class="rounded-lg px-3 py-2.5 border text-center" style="background:#0a0f1a;border-color:#1a2332">
        <?php if ($nextTier): ?>
          <p class="text-[10px] text-gray-400 leading-relaxed">
            Encore <span class="text-cyan-400 font-bold"><?= Currency::format($nextSpentDiff) ?></span>
            pour atteindre <span class="text-white font-bold"><?= htmlspecialchars($nextTier['name']) ?></span>
          </p>
        <?php else: ?>
          <p class="text-[10px] text-emerald-400 font-semibold">Grade maximum atteint !</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- ===== TABLEAU DES GRADES ===== -->
<div class="rounded-xl border mb-4 overflow-hidden" style="background:#0d1117;border-color:#1a2332">
  <div class="flex items-center gap-2 px-5 py-4 border-b" style="border-color:#1a2332">
    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:rgba(251,191,36,0.1)">
      <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
      </svg>
    </div>
    <h2 class="text-sm font-bold text-white">Tableau des Grades de Fidélité</h2>
  </div>
  <!-- Table Desktop -->
  <div class="hidden md:block overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr style="border-bottom:1px solid #1a2332">
          <th class="text-left px-5 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Grade</th>
          <th class="text-left px-5 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Dépenses min.</th>
          <th class="text-left px-5 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Taux Cashback</th>
          <th class="text-left px-5 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Points / 100 $</th>
          <th class="text-right px-5 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Statut</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (Loyalty::$tiers as $t):
          $isCurrent = ($t['name'] === $currentTier['name']);
        ?>
          <tr style="border-bottom:1px solid <?= $isCurrent ? 'rgba(6,182,212,0.15)' : '#1a2332' ?>;background:<?= $isCurrent ? 'rgba(6,182,212,0.04)' : 'transparent' ?>">
            <td class="px-5 py-3.5 font-bold text-white text-xs"><?= htmlspecialchars($t['name']) ?></td>
            <td class="px-5 py-3.5 text-gray-400 text-xs">
              <?= $t['min_spent'] == 0 ? '0 $ (départ)' : '≥ ' . number_format($t['min_spent'], 0) . ' $' ?>
            </td>
            <td class="px-5 py-3.5 text-emerald-400 font-bold text-xs"><?= number_format($t['rate'] * 100, 2) ?> %</td>
            <td class="px-5 py-3.5 text-cyan-400 text-xs font-medium"><?= $t['pts_per_100'] ?> pts</td>
            <td class="px-5 py-3.5 text-right">
              <?php if ($isCurrent): ?>
                <span class="text-[9px] px-2 py-0.5 rounded font-black uppercase tracking-wider" style="background:rgba(6,182,212,0.1);color:#06b6d4;border:1px solid rgba(6,182,212,0.25)">VOUS</span>
              <?php else: ?>
                <span class="text-[9px] text-gray-700">—</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <!-- Cards Mobile -->
  <div class="md:hidden divide-y" style="divide-color:#1a2332">
    <?php foreach (Loyalty::$tiers as $t):
      $isCurrent = ($t['name'] === $currentTier['name']);
    ?>
      <div class="px-4 py-3.5 flex items-center justify-between" style="<?= $isCurrent ? 'background:rgba(6,182,212,0.04)' : '' ?>">
        <div>
          <div class="text-xs font-bold text-white flex items-center gap-2">
            <?= htmlspecialchars($t['name']) ?>
            <?php if ($isCurrent): ?>
              <span class="text-[8px] px-1.5 py-0.5 rounded font-black" style="background:rgba(6,182,212,0.15);color:#06b6d4">VOUS</span>
            <?php endif; ?>
          </div>
          <div class="text-[10px] text-gray-500 mt-0.5"><?= $t['min_spent'] == 0 ? 'Entrée libre' : '≥ ' . number_format($t['min_spent'], 0) . ' $' ?></div>
        </div>
        <div class="text-right">
          <div class="text-xs font-black text-emerald-400"><?= number_format($t['rate'] * 100, 2) ?>%</div>
          <div class="text-[10px] text-gray-500"><?= $t['pts_per_100'] ?> pts / 100$</div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- ===== HISTORIQUE DES POINTS ===== -->
<div class="rounded-xl border overflow-hidden" style="background:#0d1117;border-color:#1a2332">
  <div class="flex items-center gap-2 px-5 py-4 border-b" style="border-color:#1a2332">
    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:rgba(0,255,136,0.1)">
      <svg class="w-4 h-4" style="color:#00ff88" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
    </div>
    <h2 class="text-sm font-bold text-white">Historique des Points</h2>
    <span class="ml-auto text-[10px] text-gray-600"><?= count($logs) ?> entrée(s)</span>
  </div>

  <?php if (empty($logs)): ?>
    <div class="text-center py-14 text-gray-600">
      <svg class="w-10 h-10 mx-auto mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <p class="text-sm font-medium text-gray-500">Aucun historique disponible.</p>
      <p class="text-xs text-gray-700 mt-1">Passez des commandes pour commencer à accumuler des points.</p>
    </div>
  <?php else: ?>
    <!-- Table Desktop -->
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr style="border-bottom:1px solid #1a2332">
            <th class="text-left px-5 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Activité</th>
            <th class="text-left px-5 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Points</th>
            <th class="text-right px-5 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($logs as $log):
            $isPositive = ($log['points'] >= 0);
          ?>
            <tr class="hover:bg-white/[0.01] transition-colors" style="border-bottom:1px solid #1a2332">
              <td class="px-5 py-3.5 text-gray-300 text-xs font-medium"><?= htmlspecialchars($log['description']) ?></td>
              <td class="px-5 py-3.5 text-xs font-black <?= $isPositive ? 'text-emerald-400' : 'text-amber-400' ?>">
                <?= $isPositive ? '+' : '' ?><?= number_format((int)$log['points']) ?> pts
              </td>
              <td class="px-5 py-3.5 text-right text-[10px] text-gray-500">
                <?= date('d M Y, H:i', strtotime($log['created_at'])) ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <!-- Cards Mobile -->
    <div class="md:hidden divide-y" style="divide-color:#1a2332">
      <?php foreach ($logs as $log):
        $isPositive = ($log['points'] >= 0);
      ?>
        <div class="px-4 py-3.5 flex items-start justify-between gap-3">
          <div class="flex-1 min-w-0">
            <div class="text-xs text-gray-300 font-medium truncate"><?= htmlspecialchars($log['description']) ?></div>
            <div class="text-[10px] text-gray-600 mt-0.5"><?= date('d M Y, H:i', strtotime($log['created_at'])) ?></div>
          </div>
          <div class="text-xs font-black shrink-0 <?= $isPositive ? 'text-emerald-400' : 'text-amber-400' ?>">
            <?= $isPositive ? '+' : '' ?><?= number_format((int)$log['points']) ?> pts
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
