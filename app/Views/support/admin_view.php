<?php
$status = strtolower($ticket['status']);
$statusLabel = match($status) {
    'open'     => 'En attente de réponse',
    'answered' => 'Répondu',
    'closed'   => 'Fermé',
    default    => $ticket['status']
};
$badgeClass = match($status) {
    'open'     => 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20',
    'answered' => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
    'closed'   => 'bg-gray-500/10 text-gray-400 border border-gray-500/20',
    default    => 'bg-zinc-800 text-gray-400'
};
?>
<div class="max-w-4xl mx-auto">

  <!-- Nav + Header -->
  <div class="flex items-center justify-between gap-4 mb-6">
    <a href="<?= APP_BASE ?>/admin/tickets" class="inline-flex items-center gap-1 text-xs font-semibold text-gray-400 hover:text-white transition-colors">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
      </svg>
      Retour aux tickets
    </a>

    <?php if ($status !== 'closed'): ?>
      <form action="<?= APP_BASE ?>/admin/tickets/<?= $ticket['id'] ?>/close" method="POST"
            onsubmit="return confirm('Fermer ce ticket comme résolu ?');">
        <input type="hidden" name="_csrf" value="<?= App\Core\Auth::csrfToken() ?>">
        <button type="submit" class="px-3.5 py-1.5 rounded-lg text-[10px] uppercase tracking-wider font-extrabold border border-red-500/30 text-red-400 hover:bg-red-500/10 transition-colors">
          Marquer comme Fermé
        </button>
      </form>
    <?php endif; ?>
  </div>

  <!-- Ticket Info Card -->
  <div class="rounded-2xl border p-5 mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4"
       style="background:linear-gradient(145deg,#0d1117,#070a0f);border-color:#1a2332">
    <div>
      <span class="text-[10px] text-gray-500 uppercase tracking-widest font-mono">Ticket #<?= $ticket['id'] ?> — Client : <strong class="text-cyan-400"><?= htmlspecialchars($ticket['username']) ?></strong></span>
      <h1 class="text-lg font-bold text-white mt-0.5 leading-snug"><?= htmlspecialchars($ticket['subject']) ?></h1>
      <p class="text-xs text-gray-400 mt-1">Ouvert le : <?= date('d/m/Y à H:i', strtotime($ticket['created_at'])) ?></p>
    </div>
    <div class="shrink-0">
      <span class="inline-flex items-center gap-1.5 text-xs px-3.5 py-1.5 rounded-full font-bold <?= $badgeClass ?>">
        <?php if ($status === 'open'): ?>
          <span class="w-2 h-2 rounded-full bg-yellow-400 animate-pulse"></span>
        <?php endif; ?>
        <?= $statusLabel ?>
      </span>
    </div>
  </div>

  <!-- Messages -->
  <div class="space-y-6 mb-8">
    <div class="text-[10px] uppercase tracking-widest text-gray-600 font-bold text-center border-b border-[#1a2332] pb-2">
      Fil de discussion
    </div>

    <?php foreach ($messages as $msg):
      $isAdmin = ($msg['role'] === 'admin');
      $senderName = $isAdmin ? 'Support KivuBoost (Admin)' : htmlspecialchars($msg['username']);
      $avatarUrl = 'https://ui-avatars.com/api/?background=' . ($isAdmin ? '00ff88&color=050811' : '00d4ff&color=050811') . '&bold=true&name=' . urlencode($senderName);
      if (!empty($msg['avatar']) && file_exists(ROOT_PATH . '/public/uploads/avatars/' . $msg['avatar'])) {
          $avatarUrl = APP_BASE . '/public/uploads/avatars/' . $msg['avatar'];
      }
    ?>
      <div class="flex gap-3.5 <?= $isAdmin ? 'items-start flex-row-reverse' : 'items-start' ?>">
        <img src="<?= $avatarUrl ?>" alt="<?= $senderName ?>" class="w-9 h-9 rounded-full object-cover shrink-0 border"
             style="border-color: <?= $isAdmin ? '#00ff8850' : '#00d4ff50' ?>">

        <div class="flex flex-col gap-1 max-w-[80%]">
          <div class="flex items-center gap-2 text-[10px] <?= $isAdmin ? 'justify-end' : 'justify-start' ?>">
            <span class="font-bold text-white"><?= $senderName ?></span>
            <?php if ($isAdmin): ?>
              <span class="px-1.5 py-0.5 rounded text-[8px] bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase font-black tracking-wider">Admin</span>
            <?php endif; ?>
            <span class="text-gray-500 font-mono"><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></span>
          </div>

          <div class="px-4 py-3 rounded-2xl text-xs leading-relaxed text-gray-200 border"
               style="background: <?= $isAdmin ? 'rgba(16,185,129,0.05)' : 'rgba(0,212,255,0.03)' ?>;
                      border-color: <?= $isAdmin ? 'rgba(16,185,129,0.2)' : 'rgba(0,212,255,0.15)' ?>;
                      border-top-right-radius: <?= $isAdmin ? '0' : '1rem' ?>;
                      border-top-left-radius: <?= $isAdmin ? '1rem' : '0' ?>;">
            <p class="whitespace-pre-line"><?= htmlspecialchars($msg['message']) ?></p>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Reply Form (Admin) -->
  <?php if ($status !== 'closed'): ?>
    <div class="rounded-2xl border p-5" style="background:#0d1117;border-color:#1a2332">
      <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
        <span class="w-1.5 h-4 rounded bg-emerald-400 inline-block"></span>
        Répondre en tant qu'administrateur
      </h3>

      <form action="<?= APP_BASE ?>/admin/tickets/<?= $ticket['id'] ?>/reply" method="POST" class="space-y-4">
        <input type="hidden" name="_csrf" value="<?= App\Core\Auth::csrfToken() ?>">

        <div>
          <textarea name="message" rows="5" required placeholder="Rédigez votre réponse ici. Soyez précis et professionnel..."
                    class="w-full px-4 py-3 rounded-xl text-sm text-white input-field"></textarea>
        </div>

        <div class="flex justify-end">
          <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold flex items-center gap-1.5 transition-all active:scale-95 shadow-[0_4px_15px_rgba(0,255,136,0.25)]"
                  style="background:linear-gradient(135deg,#00ff88,#00c466);color:#050811">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
            Envoyer la réponse
          </button>
        </div>
      </form>
    </div>
  <?php else: ?>
    <div class="rounded-2xl border p-6 text-center" style="background:#0c0f16;border-color:#1a2332">
      <p class="text-xs text-gray-500 font-semibold">Ce ticket est marqué comme fermé.</p>
    </div>
  <?php endif; ?>
</div>
