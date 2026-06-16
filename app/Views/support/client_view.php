<?php
$status = strtolower($ticket['status']);
$statusLabel = match($status) {
    'open'     => 'Ouvert (En attente de réponse)',
    'answered' => 'Répondu par l\'équipe',
    'closed'   => 'Fermé / Résolu',
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
  <!-- Back Button & Header -->
  <div class="flex items-center justify-between gap-4 mb-6">
    <a href="<?= APP_BASE ?>/tickets" class="inline-flex items-center gap-1 text-xs font-semibold text-gray-400 hover:text-white transition-colors">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
      </svg>
      Retour aux tickets
    </a>

    <?php if ($status !== 'closed'): ?>
      <form action="<?= APP_BASE ?>/tickets/<?= $ticket['id'] ?>/close" method="POST" onsubmit="return confirm('Voulez-vous vraiment marquer ce ticket comme résolu/fermé ?');">
        <input type="hidden" name="_csrf" value="<?= App\Core\Auth::csrfToken() ?>">
        <button type="submit" class="px-3.5 py-1.5 rounded-lg text-[10px] uppercase tracking-wider font-extrabold border border-red-500/30 text-red-400 hover:bg-red-500/10 transition-colors">
          Fermer ce ticket
        </button>
      </form>
    <?php endif; ?>
  </div>

  <!-- Ticket Infos Card -->
  <div class="rounded-2xl border p-5 mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4"
       style="background:linear-gradient(145deg,#0d1117,#070a0f);border-color:#1a2332">
    <div>
      <span class="text-[10px] text-gray-500 uppercase tracking-widest font-mono">Ticket #<?= $ticket['id'] ?></span>
      <h1 class="text-lg font-bold text-white mt-0.5 leading-snug"><?= htmlspecialchars($ticket['subject']) ?></h1>
      <p class="text-xs text-gray-400 mt-1">Date d'ouverture : <?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?></p>
    </div>
    
    <div class="shrink-0 flex items-center">
      <span class="inline-flex items-center gap-1.5 text-xs px-3.5 py-1.5 rounded-full font-bold <?= $badgeClass ?>">
        <?php if ($status === 'open'): ?>
          <span class="w-2 h-2 rounded-full bg-yellow-400 animate-pulse"></span>
        <?php elseif ($status === 'answered'): ?>
          <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
        <?php endif; ?>
        <?= $statusLabel ?>
      </span>
    </div>
  </div>

  <!-- Messages Conversation Box -->
  <div class="space-y-6 mb-8">
    <div class="text-[10px] uppercase tracking-widest text-gray-600 font-bold text-center border-b border-[#1a2332] pb-2">
      Début de la discussion
    </div>

    <?php foreach ($messages as $msg):
      $isAdminReply = ($msg['role'] === 'admin');
      $senderName = $isAdminReply ? 'Support KivuBoost' : htmlspecialchars($msg['username']);
      
      // Avatar
      $avatarUrl = 'https://ui-avatars.com/api/?background=' . ($isAdminReply ? '00ff88&color=050811' : '00d4ff&color=050811') . '&bold=true&name=' . urlencode($senderName);
      if (!empty($msg['avatar']) && file_exists(ROOT_PATH . '/public/uploads/avatars/' . $msg['avatar'])) {
          $avatarUrl = APP_BASE . '/public/uploads/avatars/' . $msg['avatar'];
      }
    ?>
      <div class="flex gap-3.5 <?= $isAdminReply ? 'items-start' : 'items-start flex-row-reverse' ?>">
        <!-- Avatar -->
        <img src="<?= $avatarUrl ?>" alt="<?= $senderName ?>" class="w-9 h-9 rounded-full object-cover shrink-0 border"
             style="border-color: <?= $isAdminReply ? '#00ff8850' : '#00d4ff50' ?>">

        <!-- Message Bubble Container -->
        <div class="flex flex-col gap-1 max-w-[80%]">
          <!-- Sender info -->
          <div class="flex items-center gap-2 text-[10px] <?= $isAdminReply ? 'justify-start' : 'justify-end' ?>">
            <span class="font-bold text-white"><?= $senderName ?></span>
            <?php if ($isAdminReply): ?>
              <span class="px-1.5 py-0.5 rounded text-[8px] bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase font-black tracking-wider">Staff</span>
            <?php endif; ?>
            <span class="text-gray-500 font-mono"><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></span>
          </div>

          <!-- Bubble content -->
          <div class="px-4 py-3 rounded-2xl text-xs leading-relaxed text-gray-200 border"
               style="background: <?= $isAdminReply ? 'rgba(16,185,129,0.05)' : 'rgba(0,212,255,0.03)' ?>;
                      border-color: <?= $isAdminReply ? 'rgba(16,185,129,0.2)' : 'rgba(0,212,255,0.15)' ?>;
                      border-top-left-radius: <?= $isAdminReply ? '0' : '1rem' ?>;
                      border-top-right-radius: <?= $isAdminReply ? '1rem' : '0' ?>;">
            <p class="whitespace-pre-line"><?= htmlspecialchars($msg['message']) ?></p>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Repondre Form -->
  <?php if ($status !== 'closed'): ?>
    <div class="rounded-2xl border p-5" style="background:#0d1117;border-color:#1a2332">
      <h3 class="text-xs font-bold text-white uppercase tracking-wider text-gray-400 mb-3">Rédiger une réponse</h3>
      
      <form action="<?= APP_BASE ?>/tickets/<?= $ticket['id'] ?>/reply" method="POST" class="space-y-4">
        <input type="hidden" name="_csrf" value="<?= App\Core\Auth::csrfToken() ?>">
        
        <div>
          <textarea name="message" rows="4" required placeholder="Votre message..."
                    class="w-full px-4 py-3 rounded-xl text-sm text-white input-field"></textarea>
        </div>
        
        <div class="flex justify-end">
          <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold flex items-center gap-1.5 transition-all active:scale-95 shadow-[0_4px_15px_rgba(0,212,255,0.2)]"
                  style="background:linear-gradient(135deg,#00d4ff,#0088ff);color:#050811">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
            Envoyer ma réponse
          </button>
        </div>
      </form>
    </div>
  <?php else: ?>
    <div class="rounded-2xl border p-6 text-center" style="background:#0c0f16;border-color:#ef444420">
      <svg class="w-8 h-8 mx-auto text-red-400 opacity-60 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0-8V5m0 16a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <p class="text-xs text-gray-400 font-semibold">Ce ticket a été marqué comme fermé et résolu.</p>
      <p class="text-[10px] text-gray-600 mt-0.5">Si vous rencontrez à nouveau un problème, merci de créer un nouveau ticket.</p>
    </div>
  <?php endif; ?>
</div>
