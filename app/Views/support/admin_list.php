<?php
$openCount     = count(array_filter($tickets, fn($t) => $t['status'] === 'open'));
$answeredCount = count(array_filter($tickets, fn($t) => $t['status'] === 'answered'));
$closedCount   = count(array_filter($tickets, fn($t) => $t['status'] === 'closed'));
?>
<div class="max-w-5xl mx-auto">

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
      <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-2">
        <span class="w-2.5 h-6 rounded bg-purple-400 inline-block"></span>
        Gestion des Tickets Support
      </h1>
      <p class="text-xs text-gray-500 mt-1">Répondez aux demandes d'assistance de vos clients.</p>
    </div>
  </div>

  <!-- Stats rapides -->
  <div class="grid grid-cols-3 gap-4 mb-8">
    <div class="rounded-xl p-4 border text-center" style="background:#0d1117;border-color:#1a2332">
      <div class="text-[10px] text-yellow-400 uppercase font-bold tracking-widest mb-1">En Attente</div>
      <div class="text-3xl font-black text-yellow-300"><?= $openCount ?></div>
    </div>
    <div class="rounded-xl p-4 border text-center" style="background:#0d1117;border-color:#1a2332">
      <div class="text-[10px] text-emerald-400 uppercase font-bold tracking-widest mb-1">Répondus</div>
      <div class="text-3xl font-black text-emerald-300"><?= $answeredCount ?></div>
    </div>
    <div class="rounded-xl p-4 border text-center" style="background:#0d1117;border-color:#1a2332">
      <div class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">Fermés</div>
      <div class="text-3xl font-black text-gray-300"><?= $closedCount ?></div>
    </div>
  </div>

  <!-- Table des tickets -->
  <div class="rounded-2xl border" style="background:#0d1117;border-color:#1a2332">
    <div class="px-5 py-4 border-b flex items-center gap-2" style="border-color:#1a2332">
      <svg class="w-4 h-4 text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
      </svg>
      <h2 class="text-sm font-bold text-white">Tous les Tickets (<?= count($tickets) ?>)</h2>
    </div>

    <?php if (empty($tickets)): ?>
      <div class="text-center py-16 text-gray-600">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-30 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        <p class="text-sm font-medium text-gray-500">Aucun ticket de support pour le moment.</p>
      </div>
    <?php else: ?>

      <!-- Desktop Table -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr style="border-bottom:1px solid #1a2332" class="text-gray-500 text-[10px] font-semibold uppercase tracking-widest bg-[#0a0f1a]/30">
              <th class="text-left px-5 py-3">ID</th>
              <th class="text-left px-5 py-3">Client</th>
              <th class="text-left px-5 py-3">Sujet</th>
              <th class="text-left px-5 py-3">Statut</th>
              <th class="text-left px-5 py-3">Date</th>
              <th class="text-right px-5 py-3">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#1a2332]">
            <?php foreach ($tickets as $t):
              $status = strtolower($t['status']);
              $statusLabel = match($status) {
                  'open'     => 'En attente',
                  'answered' => 'Répondu',
                  'closed'   => 'Fermé',
                  default    => $t['status']
              };
              $badgeClass = match($status) {
                  'open'     => 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20',
                  'answered' => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
                  'closed'   => 'bg-gray-500/10 text-gray-400 border border-gray-500/20',
                  default    => 'bg-zinc-800 text-gray-400'
              };
            ?>
            <tr class="hover:bg-white/[0.01] transition-colors <?= $status === 'open' ? 'bg-yellow-500/[0.02]' : '' ?>">
              <td class="px-5 py-4 font-mono text-xs text-gray-500">#<?= $t['id'] ?></td>
              <td class="px-5 py-4">
                <span class="flex items-center gap-2">
                  <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-black text-black shrink-0" style="background:linear-gradient(135deg,#00d4ff,#0066ff)">
                    <?= strtoupper(substr($t['username'], 0, 1)) ?>
                  </span>
                  <span class="font-semibold text-white text-xs"><?= htmlspecialchars($t['username']) ?></span>
                </span>
              </td>
              <td class="px-5 py-4 text-white font-medium text-xs max-w-[240px] truncate"><?= htmlspecialchars($t['subject']) ?></td>
              <td class="px-5 py-4">
                <span class="inline-flex items-center gap-1 text-[10px] px-2.5 py-1 rounded-full font-bold <?= $badgeClass ?>">
                  <?php if ($status === 'open'): ?>
                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 animate-pulse"></span>
                  <?php endif; ?>
                  <?= $statusLabel ?>
                </span>
              </td>
              <td class="px-5 py-4 text-xs text-gray-400 font-mono"><?= date('d/m/Y H:i', strtotime($t['updated_at'])) ?></td>
              <td class="px-5 py-4 text-right">
                <a href="<?= APP_BASE ?>/admin/tickets/<?= $t['id'] ?>" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold border border-[#1a2332] text-purple-400 hover:bg-purple-500/10 hover:border-purple-500/30 transition-all">
                  Répondre
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Mobile Cards -->
      <div class="block md:hidden divide-y divide-[#1a2332]">
        <?php foreach ($tickets as $t):
          $status = strtolower($t['status']);
          $statusLabel = match($status) {
              'open'     => 'En attente',
              'answered' => 'Répondu',
              'closed'   => 'Fermé',
              default    => $t['status']
          };
          $badgeClass = match($status) {
              'open'     => 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20',
              'answered' => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
              'closed'   => 'bg-gray-500/10 text-gray-400 border border-gray-500/20',
              default    => 'bg-zinc-800 text-gray-400'
          };
        ?>
        <div class="p-4 flex flex-col gap-2.5 <?= $status === 'open' ? 'bg-yellow-500/[0.02]' : '' ?>">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-black text-black" style="background:linear-gradient(135deg,#00d4ff,#0066ff)">
                <?= strtoupper(substr($t['username'], 0, 1)) ?>
              </span>
              <div>
                <p class="text-xs font-bold text-white"><?= htmlspecialchars($t['username']) ?></p>
                <p class="text-[10px] text-gray-500 font-mono">Ticket #<?= $t['id'] ?></p>
              </div>
            </div>
            <span class="inline-flex items-center gap-1 text-[10px] px-2.5 py-1 rounded-full font-bold <?= $badgeClass ?>">
              <?php if ($status === 'open'): ?>
                <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 animate-pulse"></span>
              <?php endif; ?>
              <?= $statusLabel ?>
            </span>
          </div>

          <p class="text-xs font-semibold text-gray-200 leading-snug"><?= htmlspecialchars($t['subject']) ?></p>

          <div class="flex items-center justify-between pt-1 border-t border-white/[0.02]">
            <span class="text-[10px] text-gray-500"><?= date('d/m/Y H:i', strtotime($t['updated_at'])) ?></span>
            <a href="<?= APP_BASE ?>/admin/tickets/<?= $t['id'] ?>" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-purple-500/10 border border-purple-500/20 text-purple-400 active:scale-95 transition-all">
              Ouvrir
            </a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

    <?php endif; ?>
  </div>
</div>
