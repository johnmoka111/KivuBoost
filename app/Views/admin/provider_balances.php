<?php
use App\Core\Auth;
Auth::requireAdmin();

$pageTitle = 'Soldes chez les Fournisseurs Grossistes';
?>

<div class="max-w-6xl mx-auto">
  <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold text-white flex items-center gap-2">
        <svg class="w-6 h-6 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> 
        Soldes chez les Fournisseurs Grossistes
      </h1>
      <p class="text-gray-500 text-xs mt-1">Consultez en temps réel les soldes de vos comptes chez chaque grossiste API SMM.</p>
    </div>
    <div>
      <button onclick="refreshAllBalances()" 
              class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-black hover:opacity-90 transition-all shrink-0"
              style="background:#00ff88">
        <svg id="btn-refresh-all-icon" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.228 9H18.01"/></svg>
        Rafraîchir Tous les Soldes
      </button>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($providers as $p): ?>
      <div id="provider-card-<?= $p['id'] ?>" 
           class="rounded-2xl p-5 border flex flex-col justify-between transition-all duration-300 relative overflow-hidden" 
           style="background:#0d1117;border-color:#1a2332"
           data-provider-id="<?= $p['id'] ?>">
        
        <!-- Background light effect -->
        <div class="absolute top-0 right-0 w-24 h-24 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>

        <div>
          <!-- Header card -->
          <div class="flex items-start justify-between gap-3 mb-4">
            <div>
              <h3 class="font-bold text-white text-base leading-tight"><?= htmlspecialchars($p['name']) ?></h3>
              <p class="text-[11px] text-gray-500 mt-1 font-mono break-all"><?= htmlspecialchars(parse_url($p['api_url'], PHP_URL_HOST)) ?></p>
            </div>
            
            <div class="flex items-center gap-1.5">
              <!-- Status Badge -->
              <?php if ($p['status'] == 1): ?>
                <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Actif</span>
              <?php else: ?>
                <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-red-500/10 text-red-400 border border-red-500/20">Inactif</span>
              <?php endif; ?>

              <!-- Individual Refresh Button -->
              <button onclick="refreshProviderBalance(<?= $p['id'] ?>)" 
                      title="Rafraîchir ce solde"
                      class="w-7 h-7 rounded-lg flex items-center justify-center border border-[#1a2332] text-gray-400 hover:text-white hover:bg-white/5 transition-all">
                <svg id="refresh-icon-<?= $p['id'] ?>" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.228 9H18.01"/></svg>
              </button>
            </div>
          </div>

          <!-- Balance Value -->
          <div class="my-6">
            <div class="text-[10px] text-gray-600 uppercase tracking-wider font-semibold">Solde disponible</div>
            <div id="balance-value-<?= $p['id'] ?>" class="text-3xl font-black font-mono mt-1.5 flex items-center gap-2 text-gray-400">
              <span class="w-5 h-5 rounded-full border-2 border-gray-600/30 border-t-emerald-400 animate-spin"></span>
              <span class="text-sm text-gray-600 font-normal">Connexion...</span>
            </div>
            <div id="warning-badge-<?= $p['id'] ?>" class="hidden mt-2 text-[10px] font-black px-2 py-0.5 rounded border uppercase tracking-wider animate-pulse inline-block"></div>
          </div>
        </div>

        <!-- API Info / Error Block -->
        <div class="mt-4 pt-4 border-t border-[#1a2332]">
          <div id="error-block-<?= $p['id'] ?>" class="hidden bg-red-500/10 border border-red-500/20 rounded-xl p-3 text-xs text-red-400 mt-2 font-mono break-words"></div>
          <div id="success-block-<?= $p['id'] ?>" class="text-xs text-gray-600 flex items-center gap-1.5">
            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            <span>Clé d'API configurée</span>
          </div>
        </div>

      </div>
    <?php endforeach; ?>

    <?php if (empty($providers)): ?>
      <div class="col-span-full rounded-2xl border border-dashed border-[#1a2332] p-12 text-center text-gray-500">
        Aucun fournisseur grossiste trouvé dans la base de données.
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
async function fetchProviderBalance(providerId, forceRefresh = false) {
  const cardEl = document.getElementById(`provider-card-${providerId}`);
  const valEl = document.getElementById(`balance-value-${providerId}`);
  const warnEl = document.getElementById(`warning-badge-${providerId}`);
  const errorEl = document.getElementById(`error-block-${providerId}`);
  const successEl = document.getElementById(`success-block-${providerId}`);
  const iconEl = document.getElementById(`refresh-icon-${providerId}`);

  if (!cardEl || !valEl) return;

  // Set loading state
  cardEl.classList.add('animate-pulse');
  if (iconEl) iconEl.classList.add('animate-spin');
  valEl.innerHTML = '<span class="w-5 h-5 rounded-full border-2 border-emerald-400/30 border-t-emerald-400 animate-spin"></span><span class="text-sm text-gray-500 font-normal">Connexion...</span>';
  valEl.className = "text-3xl font-black font-mono mt-1.5 flex items-center gap-2 text-gray-500";
  warnEl.className = "hidden";
  errorEl.className = "hidden";
  successEl.className = "hidden";

  try {
    let url = `<?= APP_BASE ?>/admin/provider-balance?provider_id=${providerId}`;
    if (forceRefresh) {
      url += '&refresh=1';
    }

    const response = await fetch(url);
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    const data = await response.json();

    cardEl.classList.remove('animate-pulse');
    if (iconEl) iconEl.classList.remove('animate-spin');

    if (data.success && data.balance !== null && data.balance !== undefined) {
      const val = parseFloat(data.balance);
      const formatted = val.toLocaleString('en-US', { style: 'currency', currency: 'USD' });
      valEl.textContent = formatted;

      // Color coding & alert status
      if (val < 10.0) {
        const isCritical = val < 5.0;
        warnEl.textContent = isCritical ? 'Critique' : 'Solde Bas';
        warnEl.className = `inline-block mt-2 text-[9px] font-black px-1.5 py-0.5 rounded border uppercase tracking-wider animate-pulse ${
          isCritical 
            ? 'bg-red-500/20 text-red-400 border-red-500/30' 
            : 'bg-amber-500/20 text-amber-400 border-amber-500/30'
        }`;
        valEl.className = `text-3xl font-black font-mono mt-1.5 flex items-center gap-2 ${isCritical ? 'text-red-400' : 'text-amber-400'}`;
        cardEl.style.borderColor = isCritical ? 'rgba(239,68,68,0.4)' : 'rgba(251,191,36,0.4)';
      } else {
        warnEl.className = 'hidden';
        valEl.className = "text-3xl font-black font-mono mt-1.5 flex items-center gap-2 text-[#00ff88]";
        cardEl.style.borderColor = '#1a2332';
      }
      successEl.className = "text-xs text-gray-600 flex items-center gap-1.5";
    } else {
      valEl.innerHTML = '<span class="text-lg text-red-500 font-bold">API Inaccessible</span>';
      valEl.className = "text-3xl font-black font-mono mt-1.5 flex items-center gap-2 text-red-500";
      errorEl.textContent = data.error || "Une erreur inconnue s'est produite lors de la connexion à l'API.";
      errorEl.className = "bg-red-500/10 border border-red-500/20 rounded-xl p-3 text-xs text-red-400 mt-2 font-mono break-words block";
    }
  } catch (err) {
    cardEl.classList.remove('animate-pulse');
    if (iconEl) iconEl.classList.remove('animate-spin');
    valEl.innerHTML = '<span class="text-lg text-red-500 font-bold">Hors-ligne</span>';
    valEl.className = "text-3xl font-black font-mono mt-1.5 flex items-center gap-2 text-red-500";
    errorEl.textContent = err.message;
    errorEl.className = "bg-red-500/10 border border-red-500/20 rounded-xl p-3 text-xs text-red-400 mt-2 font-mono break-words block";
  }
}

function refreshProviderBalance(providerId) {
  fetchProviderBalance(providerId, true);
}

function refreshAllBalances() {
  const btnIcon = document.getElementById('btn-refresh-all-icon');
  if (btnIcon) btnIcon.classList.add('animate-spin');

  const cards = document.querySelectorAll('[data-provider-id]');
  const promises = Array.from(cards).map(card => {
    const id = card.getAttribute('data-provider-id');
    return fetchProviderBalance(id, true);
  });

  Promise.all(promises).then(() => {
    if (btnIcon) btnIcon.classList.remove('animate-spin');
  });
}

// Initial fetch on load
document.addEventListener('DOMContentLoaded', () => {
  const cards = document.querySelectorAll('[data-provider-id]');
  cards.forEach(card => {
    const id = card.getAttribute('data-provider-id');
    fetchProviderBalance(id, false); // Utiliser le cache au premier chargement pour être ultra rapide
  });
});
</script>
