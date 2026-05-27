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
      <p class="text-gray-500 text-sm mt-1">Traces d'activité et journal de sécurité (Affichage des 2500 dernières actions).</p>
    </div>

    <!-- Actions (Recherche & PDF) -->
    <div class="flex flex-col md:flex-row items-center gap-3 w-full md:w-auto">
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
      
      <button onclick="downloadAuditPDF(event)" class="w-full md:w-auto flex items-center justify-center gap-2 bg-[#1a2332] hover:bg-[#253043] text-gray-300 hover:text-white px-4 py-2 rounded-lg text-xs font-semibold transition-colors border border-gray-700 hover:border-gray-500 shadow-sm" title="Exporter en PDF">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        Télécharger PDF
      </button>
    </div>
  </div>

  <div id="pdf-content" class="rounded-2xl border overflow-hidden" style="background:#0d1117;border-color:#1a2332;box-shadow:0 10px 30px rgba(0,0,0,0.2)">
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
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

function downloadAuditPDF(event) {
    const btn = event.currentTarget;
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Génération...';
    btn.disabled = true;

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('landscape'); 

    const generatePdf = (offsetX) => {
        // Textes de l'entête
        doc.setFontSize(22);
        doc.setTextColor(15, 23, 42); 
        doc.setFont("helvetica", "bold");
        doc.text("KivuBoost", offsetX, 20);
        
        doc.setFontSize(11);
        doc.setTextColor(100, 116, 139);
        doc.setFont("helvetica", "normal");
        doc.text("Journal d'Audit Système", offsetX, 26);
        
        doc.setFontSize(9);
        const dateStr = new Date().toLocaleString('fr-FR');
        doc.text("Généré le : " + dateStr, offsetX, 31);

        // Génération du tableau propre avec fond blanc
        doc.autoTable({
            html: '#auditTable',
            startY: 42,
            theme: 'grid',
            styles: {
                fontSize: 8,
                cellPadding: 4,
                textColor: [51, 65, 85],
                lineColor: [226, 232, 240],
                lineWidth: 0.1,
                font: 'helvetica',
                valign: 'middle',
                overflow: 'linebreak'
            },
            columnStyles: {
                0: { cellWidth: 35 }, // Date & Heure
                1: { cellWidth: 45 }, // Utilisateur
                2: { cellWidth: 35 }, // Action
                3: { cellWidth: 'auto' }, // Détails (prend tout l'espace restant)
                4: { cellWidth: 30 }  // IP
            },
            headStyles: {
                fillColor: [15, 23, 42], // Bleu très foncé KivuBoost
                textColor: 255,
                fontStyle: 'bold',
                halign: 'left'
            },
            alternateRowStyles: {
                fillColor: [248, 250, 252] // Ligne paire légèrement grisée
            },
            didParseCell: function(data) {
                // Nettoyer les sauts de lignes liés aux icônes du tableau HTML
                if (data.section === 'body' && Array.isArray(data.cell.text)) {
                    // Nettoie l'avatar de la colonne 1
                    if (data.column.index === 1 && data.cell.text.length > 1) {
                        data.cell.text = [data.cell.text[data.cell.text.length - 1].trim()];
                    } else {
                        data.cell.text = [data.cell.text.join(' ').trim()];
                    }
                }
            }
        });

        doc.save('kivuboost_audit_logs.pdf');
        
        btn.innerHTML = originalHTML;
        btn.disabled = false;
    };

    // Charger le logo KivuBoost puis lancer la création du PDF
    const img = new Image();
    img.crossOrigin = "Anonymous";
    img.src = '<?= defined("APP_BASE") ? APP_BASE : "" ?>/assets/logo.jpeg';
    
    img.onload = function() {
        // (image, format, x, y, width, height)
        doc.addImage(img, 'JPEG', 14, 12, 16, 16);
        generatePdf(34); // Décale le texte de l'entête à droite du logo
    };
    
    img.onerror = function() {
        // Fallback sans logo
        generatePdf(14);
    };
}
</script>
