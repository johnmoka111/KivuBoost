<?php
use App\Core\Auth;
Auth::requireAdmin();

$pageTitle = "Journal d'Audit Système";
?>

<div class="max-w-6xl mx-auto space-y-6">
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
      <h1 class="text-xl font-bold text-white flex items-center gap-2">
        <svg class="w-5 h-5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> 
        Journal d'Audit Système
      </h1>
      <p class="text-gray-500 text-sm mt-1">Traces d'activité et journal de sécurité des utilisateurs et des administrateurs.</p>
    </div>

    <!-- Barre de recherche -->
    <div class="relative w-full md:w-64">
      <input type="text" 
             id="auditSearch" 
             placeholder="Rechercher une action, un utilisateur..." 
             class="w-full pl-9 pr-4 py-2 rounded-lg text-xs font-medium text-white"
             style="background:#0d1117;border:1px solid #1a2332;transition:border-color .2s"
             onfocus="this.style.borderColor='rgba(245,158,11,0.5)'"
             onblur="this.style.borderColor='#1a2332'"
             onkeyup="filterAuditTable()">
      <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </span>
    </div>
  </div>

  <div class="rounded-2xl border overflow-hidden" style="background:#0d1117;border-color:#1a2332;box-shadow:0 10px 30px rgba(0,0,0,0.2)">
    <div class="overflow-x-auto">
      <table class="w-full border-collapse" id="auditTable">
        <thead>
          <tr style="border-bottom:1px solid #1a2332; background: #0a0f1a">
            <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Date & Heure</th>
            <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Utilisateur</th>
            <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Action</th>
            <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Détails</th>
            <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Adresse IP</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#1a2332]">
          <?php foreach ($logs as $log): ?>
            <?php
              // Badges de couleur dynamiques basés sur l'action
              $actionName = htmlspecialchars($log['action']);
              $badgeClass = "bg-gray-500/10 text-gray-400 border-gray-500/20";
              
              if (strpos($actionName, 'login') !== false || strpos($actionName, 'register') !== false || strpos($actionName, 'approve') !== false) {
                  $badgeClass = "bg-emerald-500/10 text-emerald-400 border-emerald-500/20";
              } elseif (strpos($actionName, 'reject') !== false || strpos($actionName, 'logout') !== false) {
                  $badgeClass = "bg-red-500/10 text-red-400 border-red-500/20";
              } elseif (strpos($actionName, 'place_') !== false || strpos($actionName, 'create_') !== false) {
                  $badgeClass = "bg-sky-500/10 text-sky-400 border-sky-500/20";
              } elseif (strpos($actionName, 'update_') !== false || strpos($actionName, 'adjust_') !== false) {
                  $badgeClass = "bg-amber-500/10 text-amber-400 border-amber-500/20";
              }
            ?>
            <tr class="hover:bg-white/[0.02] transition-colors audit-row">
              <td class="px-5 py-4 text-xs font-mono text-gray-500 whitespace-nowrap">
                <?= htmlspecialchars(date('d/m/Y H:i:s', strtotime($log['created_at']))) ?>
              </td>
              <td class="px-5 py-4 whitespace-nowrap">
                <div class="flex items-center gap-2">
                  <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold text-black uppercase bg-[#00ff88]">
                    <?= strtoupper(substr($log['username'] ?? 'S', 0, 1)) ?>
                  </div>
                  <span class="text-xs font-semibold text-white">
                    <?= htmlspecialchars($log['username'] ?? 'Système / Visiteur') ?>
                  </span>
                </div>
              </td>
              <td class="px-5 py-4 whitespace-nowrap">
                <span class="px-2 py-1 rounded text-[10px] font-bold border <?= $badgeClass ?>">
                  <?= $actionName ?>
                </span>
              </td>
              <td class="px-5 py-4 text-xs text-gray-300 max-w-sm truncate" title="<?= htmlspecialchars($log['details'] ?? '') ?>">
                <?= htmlspecialchars($log['details'] ?? 'Aucun détail') ?>
              </td>
              <td class="px-5 py-4 text-xs font-mono text-gray-500 whitespace-nowrap">
                <?= htmlspecialchars($log['ip_address'] ?? 'N/A') ?>
              </td>
            </tr>
          <?php endforeach; ?>

          <?php if (empty($logs)): ?>
            <tr>
              <td colspan="5" class="px-5 py-8 text-center text-xs text-gray-500 italic">Aucune trace d'audit trouvée.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
function filterAuditTable() {
    const input = document.getElementById("auditSearch");
    const filter = input.value.toLowerCase();
    const rows = document.querySelectorAll(".audit-row");

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        if (text.includes(filter)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}
</script>
