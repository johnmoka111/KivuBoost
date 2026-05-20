<?php
use App\Core\Auth;
use App\Core\Currency;
$pageTitle = 'Recharger mon compte';
?>

<div class="max-w-2xl mx-auto px-1 sm:px-0">

  <!-- Header -->
  <div class="mb-6 text-center sm:text-left">
    <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Recharger mon compte</h1>
    <p class="text-gray-500 text-xs sm:text-sm mt-1">Déposez des fonds instantanément pour activer vos commandes</p>
  </div>

  <!-- Solde actuel (avec switch USD/CDF) -->
  <div class="rounded-2xl p-4 sm:p-5 mb-6 border" style="background:#0d1117;border-color:rgba(0,255,136,0.2);box-shadow:0 0 25px rgba(0,255,136,0.06)">
    <div class="flex items-center justify-between">
      <div>
        <div class="text-[10px] sm:text-xs text-gray-500 uppercase tracking-widest mb-1 font-semibold">Votre Solde Actuel</div>
        <div class="text-2xl sm:text-3xl font-extrabold" style="color:#00ff88"><?= Currency::format((float)$user['balance']) ?></div>
        <a href="<?= APP_BASE ?>/currency/switch" class="inline-flex items-center gap-1 mt-1.5 text-[10px] text-[#00d4ff] hover:underline font-semibold">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
          Afficher en <?= Currency::getActive() === 'USD' ? 'CDF' : 'USD' ?>
        </a>
      </div>
      <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl flex items-center justify-center shrink-0" style="background:rgba(0,255,136,0.08)">
        <svg class="w-6 h-6 sm:w-7 sm:h-7" style="color:#00ff88" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
        </svg>
      </div>
    </div>
  </div>

  <!-- ===== ÉTAPE 1 : Instructions paiement ===== -->
  <div class="rounded-2xl p-4 sm:p-5 mb-5 border" style="background:#0d1117;border-color:#1a2332">
    <div class="flex items-center gap-2.5 mb-4">
      <div class="w-6.5 h-6.5 rounded-full flex items-center justify-center text-xs font-bold text-black shrink-0" style="background:#00ff88; width:26px; height:26px">1</div>
      <h2 class="font-bold text-white text-sm sm:text-base">Envoyez votre dépôt Mobile Money</h2>
    </div>

    <!-- Grid responsive avec copier-coller en 1-clic -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <!-- M-Pesa (Vodacom) -->
      <div class="flex items-center justify-between gap-3 px-4 py-3 rounded-xl border transition-all hover:border-[#e30613]/40" style="background:#0a0f1a;border-color:#1a2332">
        <div class="flex items-center gap-3">
          <svg viewBox="0 0 100 100" class="w-10 h-10 shrink-0 rounded-xl bg-white p-1.5 shadow-md" xmlns="http://www.w3.org/2000/svg">
            <rect x="36" y="20" width="28" height="60" rx="6" fill="none" stroke="#e30613" stroke-width="6"/>
            <line x1="45" y1="28" x2="55" y2="28" stroke="#e30613" stroke-width="3" stroke-linecap="round"/>
            <circle cx="50" cy="70" r="3" fill="#e30613"/>
            <path d="M15 50 C 30 35, 45 65, 85 50" fill="none" stroke="#22c55e" stroke-width="6" stroke-linecap="round"/>
          </svg>
          <div>
            <div class="text-[9px] text-gray-500 uppercase tracking-wider font-semibold">M-Pesa (Vodacom)</div>
            <div class="text-xs sm:text-sm font-bold text-white font-mono"><?= htmlspecialchars($settings['mpesa_number']) ?></div>
          </div>
        </div>
        <button onclick="copyToClipboard('<?= htmlspecialchars($settings['mpesa_number']) ?>', this)" class="text-xs text-gray-500 hover:text-[#00ff88] transition-all p-1.5 hover:bg-white/5 rounded-lg shrink-0" title="Copier le numéro">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10"/></svg>
        </button>
      </div>

      <!-- Airtel Money -->
      <div class="flex items-center justify-between gap-3 px-4 py-3 rounded-xl border transition-all hover:border-[#e30613]/40" style="background:#0a0f1a;border-color:#1a2332">
        <div class="flex items-center gap-3">
          <svg viewBox="0 0 100 100" class="w-10 h-10 shrink-0 rounded-xl shadow-md" xmlns="http://www.w3.org/2000/svg">
            <rect width="100" height="100" rx="20" fill="#e30613"/>
            <g transform="translate(10, 10) scale(0.8)">
              <path d="M64.2 38.3C62.1 31 56 25.5 48.2 25.5C37.5 25.5 29.5 34.5 29.5 45.2C29.5 56 37.5 65 48.2 65C54.8 65 60.5 61 62.8 54.8C63.5 52.8 62.5 50.8 60.5 50.1C58.5 49.4 56.5 50.4 55.8 52.4C54.3 56.5 50.5 59.2 46.2 59.2C39.5 59.2 34.5 53.5 34.5 46.8C34.5 40.1 39.5 34.4 46.2 34.4C51.2 34.4 55.2 37.8 56.4 42.6C57 44.9 59.3 46.3 61.6 45.7C63.9 45.1 64.9 42.8 64.2 38.3Z" fill="white"/>
              <circle cx="50" cy="50" r="6" fill="white"/>
            </g>
          </svg>
          <div>
            <div class="text-[9px] text-gray-500 uppercase tracking-wider font-semibold">Airtel Money</div>
            <div class="text-xs sm:text-sm font-bold text-white font-mono"><?= htmlspecialchars($settings['airtel_number']) ?></div>
          </div>
        </div>
        <button onclick="copyToClipboard('<?= htmlspecialchars($settings['airtel_number']) ?>', this)" class="text-xs text-gray-500 hover:text-[#00ff88] transition-all p-1.5 hover:bg-white/5 rounded-lg shrink-0" title="Copier le numéro">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10"/></svg>
        </button>
      </div>

      <!-- Orange Money -->
      <?php if (!empty($settings['orange_number'])): ?>
      <div class="flex items-center justify-between gap-3 px-4 py-3 rounded-xl border transition-all hover:border-[#ff6600]/40" style="background:#0a0f1a;border-color:#1a2332">
        <div class="flex items-center gap-3">
          <svg viewBox="0 0 100 100" class="w-10 h-10 shrink-0 rounded-xl bg-white p-1.5 shadow-md" xmlns="http://www.w3.org/2000/svg">
            <g transform="translate(5, 5) scale(0.9)">
              <path d="M22 54 L44 32 L34 22 L72 22 L72 60 L62 50 L40 72 Z" fill="#000000" />
              <path d="M78 46 L56 68 L66 78 L28 78 L28 40 L38 50 L60 28 Z" fill="#ff6600" />
            </g>
          </svg>
          <div>
            <div class="text-[9px] text-gray-500 uppercase tracking-wider font-semibold">Orange Money</div>
            <div class="text-xs sm:text-sm font-bold text-white font-mono"><?= htmlspecialchars($settings['orange_number']) ?></div>
          </div>
        </div>
        <button onclick="copyToClipboard('<?= htmlspecialchars($settings['orange_number']) ?>', this)" class="text-xs text-gray-500 hover:text-[#00ff88] transition-all p-1.5 hover:bg-white/5 rounded-lg shrink-0" title="Copier le numéro">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10"/></svg>
        </button>
      </div>
      <?php endif; ?>
    </div>

    <!-- Paiement en ligne (masqué si non configuré) -->
    <?php if ($settings['pawapay_enabled'] === '1' || $settings['visapay_enabled'] === '1'): ?>
    <div class="mt-4 pt-4 border-t" style="border-color:#1a2332">
      <div class="text-xs text-gray-500 uppercase tracking-wider mb-3">Paiement en ligne</div>
      <div class="flex flex-wrap gap-2">
        <?php if ($settings['pawapay_enabled'] === '1'): ?>
        <button class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium border transition-all hover:border-emerald-500/40"
                style="background:#0a0f1a;border-color:#1a2332;color:#e2e8f0">
          <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
          Payer via PawaPay
        </button>
        <?php endif; ?>
        <?php if ($settings['visapay_enabled'] === '1'): ?>
        <button class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium border transition-all hover:border-blue-500/40"
                style="background:#0a0f1a;border-color:#1a2332;color:#e2e8f0">
          <svg class="w-4 h-4 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
          Payer par Carte Visa
        </button>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- ===== ÉTAPE 2 : Formulaire déclaration ===== -->
  <div class="rounded-2xl p-4 sm:p-5 mb-6 border" style="background:#0d1117;border-color:#1a2332">
    <div class="flex items-center gap-2.5 mb-4">
      <div class="w-6.5 h-6.5 rounded-full flex items-center justify-center text-xs font-bold text-black shrink-0" style="background:#00d4ff; width:26px; height:26px">2</div>
      <h2 class="font-bold text-white text-sm sm:text-base">Déclarez votre transaction</h2>
    </div>
    <p class="text-xs text-gray-500 mb-4">Après avoir effectué le dépôt, remplissez ce formulaire. Un administrateur validera votre crédit sous peu.</p>

    <form method="POST" action="<?= APP_BASE ?>/recharge/submit" class="space-y-4">
      <?= Auth::csrfField() ?>

      <!-- Réseau -->
      <div>
        <label class="block text-xs font-medium text-gray-400 mb-1.5" for="network">Réseau utilisé</label>
        <select name="network" id="network" required
                class="w-full px-4 py-3 rounded-xl text-sm"
                style="background:#0a0f1a;border:1px solid #1a2332;color:#e2e8f0">
          <option value="">— Choisir le réseau —</option>
          <option value="M-Pesa">M-Pesa (Vodacom)</option>
          <option value="Airtel Money">Airtel Money</option>
          <option value="Orange Money">Orange Money</option>
        </select>
      </div>

      <!-- RG-3.1 : Sélecteur de la devise envoyée -->
      <div>
        <label class="block text-xs font-medium text-gray-400 mb-1.5">Devise que vous avez envoyée</label>
        <div class="grid grid-cols-2 gap-3">
          <label id="lbl-usd" class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer transition-all border-[#00ff88]/40 bg-[#00ff88]/5" onclick="selectCurrency('USD')">
            <input type="radio" name="currency" value="USD" id="radio-usd" checked class="hidden">
            <div class="w-8 h-8 rounded-full flex items-center justify-center font-black text-sm" style="background:rgba(0,255,136,0.15);color:#00ff88">$</div>
            <div>
              <div class="text-xs font-bold text-white">USD</div>
              <div class="text-[10px] text-gray-500">Dollar Américain</div>
            </div>
          </label>
          <label id="lbl-cdf" class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer transition-all border-[#1a2332]" onclick="selectCurrency('CDF')">
            <input type="radio" name="currency" value="CDF" id="radio-cdf" class="hidden">
            <div class="w-8 h-8 rounded-full flex items-center justify-center font-black text-sm" style="background:rgba(0,212,255,0.1);color:#00d4ff">Fc</div>
            <div>
              <div class="text-xs font-bold text-white">CDF</div>
              <div class="text-[10px] text-gray-500">Franc Congolais</div>
            </div>
          </label>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Montant -->
        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5" for="amount" id="amount-label">Montant déposé (en USD $)</label>
          <div class="relative">
            <span id="currency-symbol" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-bold">$</span>
            <input type="number" name="amount" id="amount" required min="1" step="0.01"
                   placeholder="5.00"
                   class="w-full pl-8 pr-4 py-3 rounded-xl text-sm"
                   style="background:#0a0f1a;border:1px solid #1a2332;color:#e2e8f0;transition:border-color .2s"
                   onfocus="this.style.borderColor='rgba(0,255,136,0.5)'"
                   onblur="this.style.borderColor='#1a2332'"
                   oninput="updateConversionInfo()">
          </div>
          <!-- Info de conversion dynamique -->
          <div class="text-[11px] text-gray-500 mt-1.5" id="conversion-hint">
            Équivalent CDF : <strong id="cdf-equivalent" style="color:#00d4ff">0 Fc</strong>
          </div>
        </div>

        <!-- Référence transaction -->
        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5" for="transaction_id">Référence de transaction (SMS)</label>
          <input type="text" name="transaction_id" id="transaction_id" required
                 placeholder="ex: MP240519XXXXX"
                 class="w-full px-4 py-3 rounded-xl text-sm font-mono"
                 style="background:#0a0f1a;border:1px solid #1a2332;color:#e2e8f0;transition:border-color .2s"
                 onfocus="this.style.borderColor='rgba(0,255,136,0.5)'"
                 onblur="this.style.borderColor='#1a2332'">
        </div>
      </div>

      <button type="submit"
              class="w-full py-3.5 rounded-xl text-sm font-bold transition-all hover:brightness-110 active:scale-[0.99]"
              style="background:linear-gradient(135deg,#00ff88,#00c466);color:#050811;box-shadow:0 4px 20px rgba(0,255,136,0.25)">
        Soumettre ma demande de recharge
      </button>
    </form>
  </div>

  <!-- ===== Historique des recharges ===== -->
  <?php if (!empty($recharges)): ?>
  <div class="rounded-2xl border" style="background:#0d1117;border-color:#1a2332">
    <div class="px-4 py-3.5 border-b" style="border-color:#1a2332">
      <h2 class="font-bold text-white text-sm">Historique de mes recharges</h2>
    </div>
    <div class="divide-y divide-[#1a2332]" style="divide-color:#1a2332">
      <?php foreach ($recharges as $r): ?>
      <?php
      $bc = match($r['status']) {
          'Approved' => 'badge-completed',
          'Rejected' => 'badge-canceled',
          default    => 'badge-pending',
      };
      ?>
      <div class="px-4 py-3.5 flex items-center justify-between gap-3 text-xs sm:text-sm">
        <div>
          <div class="text-sm font-bold text-white"><?= Currency::format((float)$r['amount']) ?></div>
          <div class="text-[10px] text-gray-500 mt-0.5">
            <span class="font-semibold text-gray-400"><?= htmlspecialchars($r['network']) ?></span> · <?= htmlspecialchars($r['transaction_id']) ?>
          </div>
          <div class="text-[10px] text-gray-600 mt-0.5"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></div>
        </div>
        <span class="<?= $bc ?> text-[10px] px-2.5 py-1 rounded-full font-medium shrink-0">
          <?= htmlspecialchars($r['status']) ?>
        </span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</div>

<!-- ===== JS : Cliquer pour copier & Estimations CDF ===== -->
<script>
const exchangeRate = <?= (float)\App\Models\Setting::get('usd_rate_cdf', '2850') ?>;
let activeCurrency = 'USD';

function selectCurrency(currency) {
  activeCurrency = currency;
  const lblUsd = document.getElementById('lbl-usd');
  const lblCdf = document.getElementById('lbl-cdf');
  const symbol = document.getElementById('currency-symbol');
  const label  = document.getElementById('amount-label');
  const hint   = document.getElementById('conversion-hint');

  if (currency === 'CDF') {
    lblCdf.style.borderColor = 'rgba(0,212,255,0.4)';
    lblCdf.style.background  = 'rgba(0,212,255,0.05)';
    lblUsd.style.borderColor = '#1a2332';
    lblUsd.style.background  = 'transparent';
    symbol.textContent = 'Fc';
    label.textContent  = 'Montant d\'envoi (en CDF Fc)';
    document.getElementById('radio-cdf').checked = true;
    hint.innerHTML = 'Equivalent USD : <strong id="cdf-equivalent" style="color:#00ff88">$0.00</strong>';
  } else {
    lblUsd.style.borderColor = 'rgba(0,255,136,0.4)';
    lblUsd.style.background  = 'rgba(0,255,136,0.05)';
    lblCdf.style.borderColor = '#1a2332';
    lblCdf.style.background  = 'transparent';
    symbol.textContent = '$';
    label.textContent  = 'Montant d\'envoi (en USD $)';
    document.getElementById('radio-usd').checked = true;
    hint.innerHTML = '\u00c9quivalent CDF : <strong id="cdf-equivalent" style="color:#00d4ff">0 Fc</strong>';
  }
  updateConversionInfo();
}

function updateConversionInfo() {
  const amt = parseFloat(document.getElementById('amount').value) || 0;
  const el  = document.getElementById('cdf-equivalent');
  if (!el) return;
  if (activeCurrency === 'CDF') {
    el.textContent = '$' + (amt / exchangeRate).toFixed(2);
  } else {
    el.textContent = Math.round(amt * exchangeRate).toLocaleString('fr-FR') + ' Fc';
  }
}

function copyToClipboard(text, button) {
  navigator.clipboard.writeText(text).then(() => {
    const originalContent = button.innerHTML;
    button.innerHTML = '<span class="text-[9px] text-[#00ff88] font-mono font-bold tracking-tight">Copié !</span>';
    setTimeout(() => { button.innerHTML = originalContent; }, 1500);
  }).catch(() => { alert('Numéro : ' + text); });
}
// legacy compatibility
function updateCdfEquivalent() { updateConversionInfo(); }
</script>
