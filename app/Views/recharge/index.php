<?php
use App\Core\Auth;
use App\Core\Currency;
$pageTitle = 'Recharger mon compte';
?>

<div class="max-w-2xl mx-auto px-1 sm:px-0 pt-4">

  <!-- Header -->
  <div class="mb-6 text-center sm:text-left">
    <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Ajouter des fonds</h1>
    <p class="text-gray-500 text-xs sm:text-sm mt-1">Rechargement Mobile Money automatique via BkaPay</p>
  </div>

  <!-- Solde actuel -->
  <div class="rounded-2xl p-4 sm:p-5 mb-6 border" style="background:#0d1117;border-color:rgba(0,255,136,0.2);box-shadow:0 0 25px rgba(0,255,136,0.06)">
    <div class="flex items-center justify-between">
      <div class="flex-1 min-w-0">
        <div class="text-[10px] sm:text-xs text-gray-500 uppercase tracking-widest mb-1 font-semibold">Votre Solde Actuel</div>
        <div class="text-2xl sm:text-3xl font-extrabold" style="color:#00ff88">
          <?= Currency::format((float)$user['balance']) ?>
        </div>
      </div>
      <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl flex items-center justify-center shrink-0" style="background:rgba(0,255,136,0.08)">
        <svg class="w-6 h-6 sm:w-7 sm:h-7" style="color:#00ff88" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
        </svg>
      </div>
    </div>
  </div>

  <div class="bg-[#0d1117] rounded-3xl border border-[#1a2332] shadow-2xl p-4 sm:p-6 mb-10">
    <!-- Directives -->
    <div class="rounded-xl p-4 bg-cyan-500/5 border border-cyan-500/20 text-xs text-cyan-400 mb-6 flex gap-3">
      <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <div class="leading-relaxed">
        Indiquez le montant à recharger puis validez. Vous serez redirigé vers notre agrégateur sécurisé (BkaPay) où vous pourrez choisir votre pays et saisir votre numéro Mobile Money.
      </div>
    </div>

    <form method="POST" action="<?= APP_BASE ?>/recharge/online/initiate" class="space-y-6">
      <?= Auth::csrfField() ?>
      <input type="hidden" name="gateway" value="bkapay">
      <input type="hidden" name="currency" value="USD">

      <!-- MONTANT -->
      <div>
        <label class="block text-sm font-bold text-gray-400 mb-3">Montant à déposer (en USD $)</label>
        <div class="relative max-w-sm">
          <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-lg font-black">$</span>
          <input type="number" name="amount" id="recharge-page-amount" required min="1" step="0.01"
                 placeholder="10.00" oninput="syncPageAmount()"
                 class="w-full pl-10 pr-4 py-4 rounded-xl text-lg font-black focus:outline-none"
                 style="background:#0a0f1a;border:1px solid #1a2332;color:#00ff88;">
        </div>
        <p class="text-xs text-gray-500 mt-2 font-semibold">
          Équivalent indicatif CDF : <span id="recharge-page-eq" style="color:#00d4ff">0 Fc</span>
        </p>
      </div>

      <!-- SUBMIT -->
      <div class="pt-4">
        <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 py-4 px-8 rounded-xl text-sm font-black shadow-xl hover:brightness-110 active:scale-[0.98] transition-all"
                style="background:linear-gradient(135deg,#00ff88,#00c466);color:#050811;">
          Aller vers le paiement sécurisé
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
const exchangeRate = <?= json_encode((float)\App\Core\Setting::get('usd_rate_cdf', '2850')) ?>;

function syncPageAmount() {
  const amount = parseFloat(document.getElementById('recharge-page-amount').value) || 0;
  const cdfEq = document.getElementById('recharge-page-eq');
  cdfEq.textContent = new Intl.NumberFormat('fr-FR').format(amount * exchangeRate) + ' Fc';
}

function selectPageCountry(code) {
  document.getElementById('recharge-page-country-input').value = code;
  document.querySelectorAll('.page-country-btn').forEach(btn => {
    btn.classList.remove('border-emerald-500/50', 'bg-emerald-500/5');
    btn.classList.add('border-[#1a2332]', 'bg-[#0a0f1a]');
  });
  const activeBtn = document.getElementById('btn-page-country-' + code);
  if (activeBtn) {
    activeBtn.classList.add('border-emerald-500/50', 'bg-emerald-500/5');
    activeBtn.classList.remove('border-[#1a2332]', 'bg-[#0a0f1a]');
  }
}
</script>
