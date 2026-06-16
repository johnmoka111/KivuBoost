<div class="max-w-5xl mx-auto">
  <!-- Header Title -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
      <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-2">
        <span class="w-2.5 h-6 rounded bg-cyan-400 inline-block"></span>
        Tickets d'Assistance
      </h1>
      <p class="text-xs text-gray-500 mt-1">Ouvrez un ticket de support pour discuter directement avec notre équipe.</p>
    </div>
    
    <button onclick="toggleNewTicketForm()" class="px-5 py-2.5 rounded-xl font-bold text-xs flex items-center gap-1.5 transition-all active:scale-95 shadow-[0_4px_20px_rgba(0,212,255,0.25)]"
            style="background:linear-gradient(135deg,#00d4ff,#0088ff);color:#050811">
      <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
      </svg>
      Ouvrir un Ticket
    </button>
  </div>

  <!-- Formulaire d'ouverture de Ticket (Caché par défaut) -->
  <div id="new-ticket-container" class="hidden mb-8 rounded-2xl border transition-all duration-300" style="background:#0d1117;border-color:#1a2332">
    <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color:#1a2332">
      <h2 class="text-sm font-bold text-white flex items-center gap-1.5">
        <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        Nouveau Ticket de Support
      </h2>
      <button onclick="toggleNewTicketForm()" class="text-gray-500 hover:text-gray-300">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    
    <form action="<?= APP_BASE ?>/tickets/create" method="POST" class="p-5 space-y-4">
      <input type="hidden" name="_csrf" value="<?= App\Core\Auth::csrfToken() ?>">
      
      <div>
        <label for="subject" class="block text-[10px] uppercase font-bold tracking-wider text-gray-500 mb-2">Sujet / Motif du ticket</label>
        <input type="text" name="subject" id="subject" required placeholder="ex: Commande YouTube #1405 bloquée"
               class="w-full px-4 py-3 rounded-xl text-sm text-white input-field">
      </div>
      
      <div>
        <label for="message" class="block text-[10px] uppercase font-bold tracking-wider text-gray-500 mb-2">Explication détaillée</label>
        <textarea name="message" id="message" rows="5" required placeholder="Décrivez votre problème avec le maximum de détails pour nous permettre de vous aider rapidement..."
                  class="w-full px-4 py-3 rounded-xl text-sm text-white input-field"></textarea>
      </div>

      <div class="flex justify-end gap-3 pt-2">
        <button type="button" onclick="toggleNewTicketForm()" class="px-4 py-2.5 rounded-xl border border-[#1a2332] text-xs font-semibold text-gray-400 hover:bg-white/5 transition-colors">
          Annuler
        </button>
        <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold transition-all active:scale-95 shadow-[0_4px_15px_rgba(0,255,136,0.2)]"
                style="background:linear-gradient(135deg,#00ff88,#00c466);color:#050811">
          Envoyer le Ticket
        </button>
      </div>
    </form>
  </div>

  <!-- Liste des Tickets -->
  <div class="rounded-2xl border" style="background:#0d1117;border-color:#1a2332">
    <div class="px-5 py-4 border-b" style="border-color:#1a2332">
      <h2 class="text-sm font-bold text-white">Mes Tickets d'assistance</h2>
    </div>

    <?php if (empty($tickets)): ?>
      <div class="text-center py-16 text-gray-600">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-30 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        <p class="text-sm font-medium text-gray-500">Aucun ticket de support ouvert.</p>
        <p class="text-xs text-gray-700 mt-1">Vous pouvez ouvrir un ticket pour poser une question sur vos commandes ou soldes.</p>
      </div>
    <?php else: ?>

      <!-- Desktop view table -->
      <div class="hidden md:block overflow-x-auto w-full">
        <table class="w-full text-sm">
          <thead>
            <tr style="border-bottom:1px solid #1a2332" class="text-gray-500 text-[10px] font-semibold uppercase tracking-widest bg-[#0a0f1a]/30">
              <th class="text-left px-5 py-3">ID</th>
              <th class="text-left px-5 py-3">Sujet</th>
              <th class="text-left px-5 py-3">Statut</th>
              <th class="text-left px-5 py-3">Dernière mise à jour</th>
              <th class="text-right px-5 py-3">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#1a2332]">
            <?php foreach ($tickets as $t):
              $status = strtolower($t['status']);
              $statusLabel = match($status) {
                  'open'     => 'Ouvert (En attente)',
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
            <tr class="hover:bg-white/[0.01] transition-colors">
              <td class="px-5 py-4 font-mono text-xs text-gray-500">#<?= $t['id'] ?></td>
              <td class="px-5 py-4 font-semibold text-white"><?= htmlspecialchars($t['subject']) ?></td>
              <td class="px-5 py-4">
                <span class="inline-flex items-center gap-1 text-[10px] px-2.5 py-1 rounded-full font-semibold <?= $badgeClass ?>">
                  <?php if ($status === 'open'): ?>
                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 animate-pulse"></span>
                  <?php elseif ($status === 'answered'): ?>
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                  <?php endif; ?>
                  <?= $statusLabel ?>
                </span>
              </td>
              <td class="px-5 py-4 text-xs text-gray-400"><?= date('d/m/Y H:i', strtotime($t['updated_at'])) ?></td>
              <td class="px-5 py-4 text-right">
                <a href="<?= APP_BASE ?>/tickets/<?= $t['id'] ?>" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold border border-[#1a2332] text-cyan-400 hover:bg-[#00d4ff]/10 hover:border-[#00d4ff]/30 transition-all">
                  Consulter
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Mobile view cards -->
      <div class="block md:hidden divide-y divide-[#1a2332]">
        <?php foreach ($tickets as $t):
          $status = strtolower($t['status']);
          $statusLabel = match($status) {
              'open'     => 'Ouvert',
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
        <div class="p-4 flex flex-col gap-3">
          <div class="flex items-center justify-between">
            <span class="font-mono text-xs text-gray-500">Ticket #<?= $t['id'] ?></span>
            <span class="inline-flex items-center gap-1 text-[10px] px-2.5 py-1 rounded-full font-semibold <?= $badgeClass ?>">
              <?= $statusLabel ?>
            </span>
          </div>

          <h3 class="text-sm font-bold text-white leading-snug"><?= htmlspecialchars($t['subject']) ?></h3>

          <div class="flex items-center justify-between pt-1 mt-1 border-t border-white/[0.02]">
            <div class="text-[10px] text-gray-500">
              Mise à jour : <?= date('d/m/Y H:i', strtotime($t['updated_at'])) ?>
            </div>
            <a href="<?= APP_BASE ?>/tickets/<?= $t['id'] ?>" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-[#00d4ff]/10 border border-[#00d4ff]/20 text-cyan-400 active:scale-95 transition-all">
              Consulter
            </a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

    <?php endif; ?>
  </div>
</div>

<script>
function toggleNewTicketForm() {
  const container = document.getElementById('new-ticket-container');
  if (container.classList.contains('hidden')) {
    container.classList.remove('hidden');
    container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  } else {
    container.classList.add('hidden');
  }
}
</script>
