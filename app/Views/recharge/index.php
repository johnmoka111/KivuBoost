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

  <!-- Solde actuel avec sélecteur de devise multi-pays -->
  <div class="rounded-2xl p-4 sm:p-5 mb-6 border" style="background:#0d1117;border-color:rgba(0,255,136,0.2);box-shadow:0 0 25px rgba(0,255,136,0.06)">
    <div class="flex items-center justify-between">
      <div class="flex-1 min-w-0">
        <div class="text-[10px] sm:text-xs text-gray-500 uppercase tracking-widest mb-1 font-semibold">Votre Solde Actuel</div>
        <div class="text-2xl sm:text-3xl font-extrabold" style="color:#00ff88" id="recharge-balance-display">
          <?= Currency::format((float)$user['balance']) ?>
        </div>
        <!-- Sélecteur de devise compact -->
        <div class="relative mt-2 inline-block" id="recharge-currency-wrapper">
          <button onclick="toggleRechargeCurrency()" 
                  class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-[10px] font-bold uppercase border transition-all hover:bg-white/5"
                  style="background:#0a0f1a;border-color:#1a2332;color:#00d4ff">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            Afficher en <span id="recharge-currency-label"><?= Currency::getActive() ?></span>
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
          </button>
          <!-- Dropdown — Devises des pays supportés uniquement -->
          <div id="recharge-currency-dropdown"
               class="hidden absolute left-0 top-full mt-1.5 z-50 rounded-2xl border shadow-2xl overflow-hidden"
               style="background:#0d1117;border-color:#1a2332;width:260px;max-height:280px;overflow-y:auto">
            <div class="px-3 py-2 border-b" style="border-color:#1a2332">
              <p class="text-[10px] text-gray-500 font-semibold uppercase tracking-wider">Devises des pays supportés</p>
            </div>
            <?php
            // Devises liées uniquement aux pays supportés par l'agrégateur
            $supportedCurrencies = [
              'USD' => ['name'=>'Dollar Américain', 'flag'=>'🇺🇸', 'countries'=>'International'],
              'CDF' => ['name'=>'Franc Congolais',  'flag'=>'🇨🇩', 'countries'=>'R.D. Congo'],
              'XOF' => ['name'=>'Franc CFA UEMOA',  'flag'=>'🌍', 'countries'=>'Côte d\'Ivoire, Sénégal, Mali, BF, Togo, Bénin, Niger'],
              'XAF' => ['name'=>'Franc CFA CEMAC',  'flag'=>'🌍', 'countries'=>'Cameroun, Congo-B, Gabon...'],
              'GNF' => ['name'=>'Franc Guinéen',    'flag'=>'🇬🇳', 'countries'=>'Guinée'],
              'EUR' => ['name'=>'Euro',              'flag'=>'🇪🇺', 'countries'=>'Zone Euro'],
            ];
            $activeCur  = Currency::getActive();
            $balanceUsd = (float)$user['balance'];
            foreach ($supportedCurrencies as $code => $info):
              $rate      = Currency::getRate($code);
              $converted = $balanceUsd * $rate;
              $noDecimal = ['CDF','XAF','XOF','GNF'];
              $d         = in_array($code, $noDecimal) ? 0 : 2;
              $fmtAmt    = number_format($converted, $d, ',', ' ') . ' ' . (Currency::all()[$code]['symbol'] ?? $code);
              $isActive  = $code === $activeCur;
            ?>
            <button onclick="rechargeSelectCurrency('<?= $code ?>')"
                    class="w-full flex items-center justify-between px-4 py-3 text-left transition-colors hover:bg-white/5 <?= $isActive ? 'text-[#00ff88]' : 'text-gray-300' ?>"
                    style="<?= $isActive ? 'background:rgba(0,255,136,0.05)' : '' ?>">
              <div class="flex items-center gap-2.5 min-w-0">
                <span class="text-lg leading-none"><?= $info['flag'] ?></span>
                <div class="min-w-0">
                  <div class="text-xs font-bold truncate <?= $isActive ? 'text-[#00ff88]' : 'text-white' ?>"><?= $code ?> — <?= $info['name'] ?></div>
                  <div class="text-[10px] text-gray-500 truncate"><?= $info['countries'] ?></div>
                </div>
              </div>
              <div class="text-xs font-black ml-2 shrink-0 <?= $isActive ? 'text-[#00ff88]' : 'text-gray-400' ?>"><?= $fmtAmt ?></div>
            </button>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl flex items-center justify-center shrink-0" style="background:rgba(0,255,136,0.08)">
        <svg class="w-6 h-6 sm:w-7 sm:h-7" style="color:#00ff88" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
        </svg>
      </div>
    </div>
  </div>

  <script>
  function toggleRechargeCurrency() {
    document.getElementById('recharge-currency-dropdown').classList.toggle('hidden');
  }
  document.addEventListener('click', function(e) {
    const w = document.getElementById('recharge-currency-wrapper');
    if (w && !w.contains(e.target)) {
      document.getElementById('recharge-currency-dropdown').classList.add('hidden');
    }
  });
  function rechargeSelectCurrency(code) {
    fetch('<?= APP_BASE ?>/currency/switch?to=' + code)
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          document.getElementById('recharge-balance-display').textContent = data.formatted;
          document.getElementById('recharge-currency-label').textContent  = code;
          // Sync sidebar aussi
          const sb = document.getElementById('balance-display');
          if (sb) sb.textContent = data.formatted;
          const sl = document.getElementById('currency-active-label');
          if (sl) sl.textContent = code;
          document.getElementById('recharge-currency-dropdown').classList.add('hidden');
        }
      })
      .catch(() => { window.location.href = '<?= APP_BASE ?>/currency/switch?to=' + code; });
  }
  </script>

  <!-- ===== FORMULAIRE STEPPER INTÉGRÉ ===== -->
  <div class="rounded-2xl border p-4 sm:p-6 mb-6" style="background:#0d1117;border-color:#1a2332;box-shadow:0 4px 20px rgba(0,0,0,0.15)">
    
    <!-- En-tête de progression visuelle (Stepper Indicators) -->
    <div class="flex items-center justify-between mb-8 pb-4 border-b border-[#1a2332]">
      <!-- Step 1 -->
      <div class="flex items-center gap-2 step-indicator cursor-pointer" id="step-ind-1" onclick="goToStep(1)">
        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black bg-[#00ff88] text-black transition-all duration-300" id="step-num-1">1</div>
        <span class="text-xs font-bold text-white hidden sm:inline" id="step-text-1">Montant</span>
      </div>
      <div class="h-[2px] flex-1 mx-2 bg-[#1a2332] transition-all duration-300" id="step-line-1"></div>
      
      <!-- Step 2 -->
      <div class="flex items-center gap-2 step-indicator" id="step-ind-2">
        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black bg-[#1a2332] text-gray-500 transition-all duration-300" id="step-num-2">2</div>
        <span class="text-xs font-bold text-gray-500 hidden sm:inline" id="step-text-2">Pays</span>
      </div>
      <div class="h-[2px] flex-1 mx-2 bg-[#1a2332] transition-all duration-300" id="step-line-2"></div>
      
      <!-- Step 3 -->
      <div class="flex items-center gap-2 step-indicator" id="step-ind-3">
        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black bg-[#1a2332] text-gray-500 transition-all duration-300" id="step-num-3">3</div>
        <span class="text-xs font-bold text-gray-500 hidden sm:inline" id="step-text-3">Réseau</span>
      </div>
      <div class="h-[2px] flex-1 mx-2 bg-[#1a2332] transition-all duration-300" id="step-line-3"></div>
      
      <!-- Step 4 -->
      <div class="flex items-center gap-2 step-indicator" id="step-ind-4">
        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black bg-[#1a2332] text-gray-500 transition-all duration-300" id="step-num-4">4</div>
        <span class="text-xs font-bold text-gray-500 hidden sm:inline" id="step-text-4">Validation</span>
      </div>
    </div>

    <!-- Conteneur Formulaire Principal -->
    <form id="recharge-stepper-form" method="POST" action="<?= APP_BASE ?>/recharge/online/initiate" class="space-y-6">
      <?= Auth::csrfField() ?>
      <input type="hidden" name="gateway" value="bkapay">
      <input type="hidden" name="currency" id="stepper-currency" value="USD">

      <!-- ==================== ÉTAPE 1 : MONTANT ==================== -->
      <div class="step-content space-y-5" id="step-content-1">
        <div>
          <h3 class="text-base font-bold text-white mb-1">Déposez des fonds</h3>
          <p class="text-xs text-gray-500">Choisissez la devise de dépôt et entrez le montant.</p>
        </div>

        <!-- Sélecteur de Devise KivuBoost -->
        <div class="grid grid-cols-2 gap-3.5">
          <button type="button" id="btn-currency-usd" onclick="selectStepperCurrency('USD')" 
                  class="flex items-center gap-3 px-4 py-3 rounded-2xl border text-left transition-all border-[#00ff88]/40 bg-[#00ff88]/5 focus:outline-none">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-sm bg-[#00ff88]/20 text-[#00ff88]">$</div>
            <div>
              <div class="text-xs font-bold text-white">USD</div>
              <div class="text-[10px] text-gray-500">Dollar Américain</div>
            </div>
          </button>
          
          <button type="button" id="btn-currency-cdf" onclick="selectStepperCurrency('CDF')" 
                  class="flex items-center gap-3 px-4 py-3 rounded-2xl border text-left transition-all border-[#1a2332] bg-transparent focus:outline-none">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-sm bg-cyan-500/10 text-cyan-400">Fc</div>
            <div>
              <div class="text-xs font-bold text-white">CDF</div>
              <div class="text-[10px] text-gray-500">Franc Congolais</div>
            </div>
          </button>
        </div>

        <!-- Entrée de Montant -->
        <div>
          <label class="block text-xs font-medium text-gray-400 mb-2" id="stepper-amount-label">Montant (en USD $)</label>
          <div class="relative">
            <span id="stepper-amount-symbol" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-bold">$</span>
            <input type="number" name="amount" id="stepper-amount" required min="0.1" step="0.01"
                   placeholder="10.00"
                   class="w-full pl-9 pr-4 py-3 rounded-2xl text-sm font-semibold focus:outline-none"
                   style="background:#0a0f1a;border:1px solid #1a2332;color:#e2e8f0;"
                   oninput="updateStepperConversion()">
          </div>
          <div class="text-xs text-gray-500 mt-2" id="stepper-conversion-hint">
            Équivalent CDF : <strong id="stepper-cdf-equivalent" style="color:#00d4ff">0 Fc</strong>
          </div>
        </div>

        <div class="flex justify-end pt-2">
          <button type="button" onclick="nextStep(1)" id="btn-next-1" disabled 
                  class="px-6 py-3 rounded-xl text-xs font-bold bg-[#00ff88] text-black hover:brightness-110 active:scale-[0.98] transition-all disabled:opacity-50 disabled:cursor-not-allowed">
            Suivant
          </button>
        </div>
      </div>

      <!-- ==================== ÉTAPE 2 : PAYS ==================== -->
      <div class="step-content space-y-5 hidden" id="step-content-2">
        <div>
          <h3 class="text-base font-bold text-white mb-1">Sélectionnez le Pays</h3>
          <p class="text-xs text-gray-500">Quel est le pays d'émission de votre portefeuille Mobile Money ?</p>
        </div>

        <!-- Liste des Pays (Boutons tactiles) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="stepper-countries-grid">
          <!-- RDC (Featured) -->
          <button type="button" onclick="selectStepperCountry('CD')" id="btn-country-cd" 
                  class="flex items-center gap-3 px-4 py-3.5 rounded-2xl border text-left transition-all border-[#1a2332] bg-[#0a0f1a] hover:border-cyan-500/30 focus:outline-none">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs bg-emerald-500/10 text-emerald-400">CD</div>
            <div>
              <div class="text-xs font-bold text-white">R.D. Congo (RDC)</div>
              <div class="text-[9px] text-gray-500">M-Pesa, Airtel Money, Orange Money</div>
            </div>
          </button>
          
          <!-- Côte d'Ivoire -->
          <button type="button" onclick="selectStepperCountry('CI')" id="btn-country-ci" 
                  class="flex items-center gap-3 px-4 py-3.5 rounded-2xl border text-left transition-all border-[#1a2332] bg-[#0a0f1a] hover:border-cyan-500/30 focus:outline-none">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs bg-cyan-500/10 text-cyan-400">CI</div>
            <div>
              <div class="text-xs font-bold text-white">Côte d'Ivoire</div>
              <div class="text-[9px] text-gray-500">Orange, MTN, Wave, Moov</div>
            </div>
          </button>

          <!-- Cameroun -->
          <button type="button" onclick="selectStepperCountry('CM')" id="btn-country-cm" 
                  class="flex items-center gap-3 px-4 py-3.5 rounded-2xl border text-left transition-all border-[#1a2332] bg-[#0a0f1a] hover:border-cyan-500/30 focus:outline-none">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs bg-cyan-500/10 text-cyan-400">CM</div>
            <div>
              <div class="text-xs font-bold text-white">Cameroun</div>
              <div class="text-[9px] text-gray-500">Orange Money, MTN</div>
            </div>
          </button>

          <!-- Bénin -->
          <button type="button" onclick="selectStepperCountry('BJ')" id="btn-country-bj" 
                  class="flex items-center gap-3 px-4 py-3.5 rounded-2xl border text-left transition-all border-[#1a2332] bg-[#0a0f1a] hover:border-cyan-500/30 focus:outline-none">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs bg-cyan-500/10 text-cyan-400">BJ</div>
            <div>
              <div class="text-xs font-bold text-white">Bénin</div>
              <div class="text-[9px] text-gray-500">MTN, Moov, Celtis</div>
            </div>
          </button>

          <!-- Sénégal -->
          <button type="button" onclick="selectStepperCountry('SN')" id="btn-country-sn" 
                  class="flex items-center gap-3 px-4 py-3.5 rounded-2xl border text-left transition-all border-[#1a2332] bg-[#0a0f1a] hover:border-cyan-500/30 focus:outline-none">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs bg-cyan-500/10 text-cyan-400">SN</div>
            <div>
              <div class="text-xs font-bold text-white">Sénégal</div>
              <div class="text-[9px] text-gray-500">Orange Money, Wave, Free</div>
            </div>
          </button>

          <!-- Togo -->
          <button type="button" onclick="selectStepperCountry('TG')" id="btn-country-tg" 
                  class="flex items-center gap-3 px-4 py-3.5 rounded-2xl border text-left transition-all border-[#1a2332] bg-[#0a0f1a] hover:border-cyan-500/30 focus:outline-none">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs bg-cyan-500/10 text-cyan-400">TG</div>
            <div>
              <div class="text-xs font-bold text-white">Togo</div>
              <div class="text-[9px] text-gray-500">T-Money, Moov</div>
            </div>
          </button>

          <!-- Mali -->
          <button type="button" onclick="selectStepperCountry('ML')" id="btn-country-ml" 
                  class="flex items-center gap-3 px-4 py-3.5 rounded-2xl border text-left transition-all border-[#1a2332] bg-[#0a0f1a] hover:border-cyan-500/30 focus:outline-none">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs bg-cyan-500/10 text-cyan-400">ML</div>
            <div>
              <div class="text-xs font-bold text-white">Mali</div>
              <div class="text-[9px] text-gray-500">Orange, Moov</div>
            </div>
          </button>

          <!-- Burkina Faso -->
          <button type="button" onclick="selectStepperCountry('BF')" id="btn-country-bf" 
                  class="flex items-center gap-3 px-4 py-3.5 rounded-2xl border text-left transition-all border-[#1a2332] bg-[#0a0f1a] hover:border-cyan-500/30 focus:outline-none">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs bg-cyan-500/10 text-cyan-400">BF</div>
            <div>
              <div class="text-xs font-bold text-white">Burkina Faso</div>
              <div class="text-[9px] text-gray-500">Orange, Moov</div>
            </div>
          </button>

          <!-- Niger -->
          <button type="button" onclick="selectStepperCountry('NE')" id="btn-country-ne" 
                  class="flex items-center gap-3 px-4 py-3.5 rounded-2xl border text-left transition-all border-[#1a2332] bg-[#0a0f1a] hover:border-cyan-500/30 focus:outline-none">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs bg-cyan-500/10 text-cyan-400">NE</div>
            <div>
              <div class="text-xs font-bold text-white">Niger</div>
              <div class="text-[9px] text-gray-500">Orange, Airtel</div>
            </div>
          </button>

          <!-- Guinée -->
          <button type="button" onclick="selectStepperCountry('GN')" id="btn-country-gn" 
                  class="flex items-center gap-3 px-4 py-3.5 rounded-2xl border text-left transition-all border-[#1a2332] bg-[#0a0f1a] hover:border-cyan-500/30 focus:outline-none">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs bg-cyan-500/10 text-cyan-400">GN</div>
            <div>
              <div class="text-xs font-bold text-white">Guinée</div>
              <div class="text-[9px] text-gray-500">Orange, MTN</div>
            </div>
          </button>
        </div>
        <input type="hidden" name="bkapay_country" id="stepper-country-input" required>

        <div class="flex justify-between pt-2">
          <button type="button" onclick="prevStep(2)" class="px-5 py-2.5 rounded-xl text-xs font-bold border border-gray-700 hover:bg-white/5 transition-all text-gray-300">
            Retour
          </button>
          <button type="button" onclick="nextStep(2)" id="btn-next-2" disabled 
                  class="px-5 py-2.5 rounded-xl text-xs font-bold bg-[#00ff88] text-black hover:brightness-110 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
            Suivant
          </button>
        </div>
      </div>

      <!-- ==================== ÉTAPE 3 : OPÉRATEUR & TÉLÉPHONE ==================== -->
      <div class="step-content space-y-5 hidden" id="step-content-3">
        <div>
          <h3 class="text-base font-bold text-white mb-1">Réseau & Numéro</h3>
          <p class="text-xs text-gray-500">Sélectionnez votre réseau et entrez votre numéro de téléphone.</p>
        </div>

        <!-- Opérateurs Mobiles -->
        <div>
          <label class="block text-xs font-medium text-gray-400 mb-2.5">Opérateur Mobile</label>
          <div class="grid grid-cols-3 gap-3" id="stepper-operators-container">
            <!-- Injecté dynamiquement en JS en fonction du pays -->
          </div>
          <input type="hidden" name="bkapay_operator" id="stepper-operator-input" required>
        </div>

        <!-- Numéro de téléphone -->
        <div>
          <label class="block text-xs font-medium text-gray-400 mb-2" for="stepper-phone">Numéro Mobile Money (sans indicatif)</label>
          <div class="relative flex items-center">
            <span id="stepper-phone-prefix" class="absolute left-4 text-gray-400 text-sm font-mono font-semibold"></span>
            <input type="tel" name="bkapay_phone" id="stepper-phone" required
                   placeholder="ex: 812345678"
                   class="w-full py-3 rounded-2xl text-sm font-mono font-semibold focus:outline-none"
                   style="background:#0a0f1a;border:1px solid #1a2332;color:#e2e8f0;padding-left:1rem;"
                   oninput="validateStepperPhone()">
          </div>
        </div>

        <div class="flex justify-between pt-2">
          <button type="button" onclick="prevStep(3)" class="px-5 py-2.5 rounded-xl text-xs font-bold border border-gray-700 hover:bg-white/5 transition-all text-gray-300">
            Retour
          </button>
          <button type="button" onclick="nextStep(3)" id="btn-next-3" disabled 
                  class="px-5 py-2.5 rounded-xl text-xs font-bold bg-[#00ff88] text-black hover:brightness-110 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
            Suivant
          </button>
        </div>
      </div>

      <!-- ==================== ÉTAPE 4 : VALIDATION ==================== -->
      <div class="step-content space-y-5 hidden" id="step-content-4">
        <div>
          <h3 class="text-base font-bold text-white mb-1">Confirmez votre rechargement</h3>
          <p class="text-xs text-gray-500">Vérifiez les détails du prélèvement avant de soumettre la demande.</p>
        </div>

        <!-- Ticket Récapitulatif -->
        <div class="rounded-2xl p-4 sm:p-5 space-y-3 border" style="background:#0a0f1a;border-color:#1a2332">
          <div class="flex justify-between text-xs text-gray-400">
            <span>Montant demandé (Solde) :</span>
            <span id="final-original-amount" class="font-bold text-white">—</span>
          </div>
          <div class="flex justify-between text-xs text-gray-400">
            <span>Pays de provenance :</span>
            <span id="final-country" class="font-bold text-white">—</span>
          </div>
          <div class="flex justify-between text-xs text-gray-400">
            <span>Réseau & Téléphone :</span>
            <span id="final-phone" class="font-bold text-white">—</span>
          </div>
          <div class="flex justify-between text-xs text-gray-400">
            <span>Taux de change appliqué :</span>
            <span id="final-rate" class="font-bold text-white">—</span>
          </div>
          <div class="border-t border-[#1a2332] pt-3 flex justify-between text-sm">
            <span class="font-semibold text-white">Montant net débité :</span>
            <span id="final-target-amount" class="font-black text-lg" style="color:#00ff88">—</span>
          </div>
        </div>

        <!-- Alerte Push USSD -->
        <div class="rounded-2xl p-4 bg-cyan-500/5 border border-cyan-500/20 text-xs text-cyan-400 flex gap-3">
          <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
          </svg>
          <div>
            <span class="font-bold block mb-0.5">Autorisation via votre portable</span>
            <span>Une demande USSD interactive va être poussée sur votre téléphone portable. Veuillez saisir votre mot de passe secret Mobile Money pour valider la transaction.</span>
          </div>
        </div>

        <div class="flex justify-between pt-2">
          <button type="button" onclick="prevStep(4)" class="px-5 py-2.5 rounded-xl text-xs font-bold border border-gray-700 hover:bg-white/5 transition-all text-gray-300">
            Retour
          </button>
          <button type="submit" class="px-6 py-3 rounded-xl text-xs font-bold transition-all hover:brightness-110 active:scale-[0.98]"
                  style="background:linear-gradient(135deg,#00ff88,#00c466);color:#050811;box-shadow:0 4px 15px rgba(0,255,136,0.25)">
            Lancer le paiement direct
          </button>
        </div>
      </div>

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

<script>
// Taux d'échange configurés en base de données
const exchangeRate = <?= (float)\App\Models\Setting::get('usd_rate_cdf', '2850') ?>;
const bkapayExchangeRates = {
  CDF: exchangeRate,
  XOF: <?= (float)\App\Models\Setting::get('usd_rate_xof', '600') ?>,
  XAF: <?= (float)\App\Models\Setting::get('usd_rate_xaf', '600') ?>,
  GNF: <?= (float)\App\Models\Setting::get('usd_rate_gnf', '8600') ?>
};

// Données des pays supportés par BkaPay
const countriesData = {
  "CD": {
    name: "R.D. Congo",
    currency: "CDF",
    prefix: "+243",
    get rate() { return bkapayExchangeRates.CDF; },
    operators: [
      { id: "airtel", name: "Airtel Money" },
      { id: "orange", name: "Orange Money" },
      { id: "vodacom", name: "M-Pesa (Vodacom)" }
    ]
  },
  "CI": {
    name: "Côte d'Ivoire",
    currency: "XOF",
    prefix: "+225",
    get rate() { return bkapayExchangeRates.XOF; },
    operators: [
      { id: "orange", name: "Orange Money" },
      { id: "mtn", name: "MTN Mobile" },
      { id: "wave", name: "Wave" },
      { id: "moov", name: "Moov Money" }
    ]
  },
  "CM": {
    name: "Cameroun",
    currency: "XAF",
    prefix: "+237",
    get rate() { return bkapayExchangeRates.XAF; },
    operators: [
      { id: "orange", name: "Orange Money" },
      { id: "mtn", name: "MTN Mobile" }
    ]
  },
  "BJ": {
    name: "Bénin",
    currency: "XOF",
    prefix: "+229",
    get rate() { return bkapayExchangeRates.XOF; },
    operators: [
      { id: "mtn", name: "MTN Mobile" },
      { id: "moov", name: "Moov Money" },
      { id: "celtis", name: "Celtis" }
    ]
  },
  "SN": {
    name: "Sénégal",
    currency: "XOF",
    prefix: "+221",
    get rate() { return bkapayExchangeRates.XOF; },
    operators: [
      { id: "orange", name: "Orange Money" },
      { id: "wave", name: "Wave" },
      { id: "free", name: "Free Money" }
    ]
  },
  "TG": {
    name: "Togo",
    currency: "XOF",
    prefix: "+228",
    get rate() { return bkapayExchangeRates.XOF; },
    operators: [
      { id: "tmoney", name: "T-Money" },
      { id: "moov", name: "Moov Money" }
    ]
  },
  "ML": {
    name: "Mali",
    currency: "XOF",
    prefix: "+223",
    get rate() { return bkapayExchangeRates.XOF; },
    operators: [
      { id: "orange", name: "Orange Money" },
      { id: "moov", name: "Moov Money" }
    ]
  },
  "BF": {
    name: "Burkina Faso",
    currency: "XOF",
    prefix: "+226",
    get rate() { return bkapayExchangeRates.XOF; },
    operators: [
      { id: "orange", name: "Orange Money" },
      { id: "moov", name: "Moov Money" }
    ]
  },
  "NE": {
    name: "Niger",
    currency: "XOF",
    prefix: "+227",
    get rate() { return bkapayExchangeRates.XOF; },
    operators: [
      { id: "orange", name: "Orange Money" },
      { id: "airtel", name: "Airtel Money" }
    ]
  },
  "GN": {
    name: "Guinée",
    currency: "GNF",
    prefix: "+224",
    get rate() { return bkapayExchangeRates.GNF; },
    operators: [
      { id: "orange", name: "Orange Money" },
      { id: "mtn", name: "MTN Mobile" }
    ]
  }
};

let currentStep = 1;
let selectedCurrency = 'USD';
let selectedCountryCode = '';
let selectedOperatorId = '';

// ==================== GESTION DEVISSES ====================
function selectStepperCurrency(currency) {
  selectedCurrency = currency;
  document.getElementById('stepper-currency').value = currency;

  const btnUsd = document.getElementById('btn-currency-usd');
  const btnCdf = document.getElementById('btn-currency-cdf');
  const symbol = document.getElementById('stepper-amount-symbol');
  const label = document.getElementById('stepper-amount-label');
  const hint = document.getElementById('stepper-conversion-hint');

  if (currency === 'CDF') {
    btnCdf.className = "flex items-center gap-3 px-4 py-3 rounded-2xl border text-left transition-all border-cyan-500/40 bg-cyan-500/5 focus:outline-none";
    btnUsd.className = "flex items-center gap-3 px-4 py-3 rounded-2xl border text-left transition-all border-[#1a2332] bg-transparent focus:outline-none";
    symbol.textContent = 'Fc';
    label.textContent = "Montant (en CDF Fc)";
    hint.innerHTML = 'Équivalent USD : <strong id="stepper-cdf-equivalent" style="color:#00ff88">$0.00</strong>';
  } else {
    btnUsd.className = "flex items-center gap-3 px-4 py-3 rounded-2xl border text-left transition-all border-[#00ff88]/40 bg-[#00ff88]/5 focus:outline-none";
    btnCdf.className = "flex items-center gap-3 px-4 py-3 rounded-2xl border text-left transition-all border-[#1a2332] bg-transparent focus:outline-none";
    symbol.textContent = '$';
    label.textContent = "Montant (en USD $)";
    hint.innerHTML = 'Équivalent CDF : <strong id="stepper-cdf-equivalent" style="color:#00d4ff">0 Fc</strong>';
  }
  
  updateStepperConversion();
}

function updateStepperConversion() {
  const amt = parseFloat(document.getElementById('stepper-amount').value) || 0;
  const el = document.getElementById('stepper-cdf-equivalent');
  const btnNext = document.getElementById('btn-next-1');

  if (amt > 0) {
    btnNext.removeAttribute('disabled');
  } else {
    btnNext.setAttribute('disabled', 'true');
  }

  if (!el) return;
  if (selectedCurrency === 'CDF') {
    el.textContent = '$' + (amt / exchangeRate).toFixed(2);
  } else {
    el.textContent = Math.round(amt * exchangeRate).toLocaleString('fr-FR') + ' Fc';
  }
}

// ==================== GESTION DES PAYS ====================
function selectStepperCountry(countryCode) {
  selectedCountryCode = countryCode;
  document.getElementById('stepper-country-input').value = countryCode;

  // Mettre à jour l'état visuel de la grille de boutons pays
  const grid = document.getElementById('stepper-countries-grid');
  grid.querySelectorAll('button').forEach(btn => {
    btn.className = "flex items-center gap-3 px-4 py-3.5 rounded-2xl border text-left transition-all border-[#1a2332] bg-[#0a0f1a] hover:border-cyan-500/30 focus:outline-none";
  });

  const activeBtn = document.getElementById('btn-country-' + countryCode.toLowerCase());
  if (activeBtn) {
    activeBtn.className = "flex items-center gap-3 px-4 py-3.5 rounded-2xl border text-left transition-all border-[#00ff88]/40 bg-[#00ff88]/5 focus:outline-none";
  }

  // Activer le bouton Suivant de l'étape 2
  document.getElementById('btn-next-2').removeAttribute('disabled');

  // Générer les opérateurs pour l'étape suivante
  setupOperatorsStep();
}

// ==================== OPÉRATEURS & TÉLÉPHONE ====================
function setupOperatorsStep() {
  const country = countriesData[selectedCountryCode];
  const container = document.getElementById('stepper-operators-container');
  const prefixSpan = document.getElementById('stepper-phone-prefix');
  const phoneInput = document.getElementById('stepper-phone');

  // Vider les opérateurs existants
  container.innerHTML = '';
  selectedOperatorId = '';
  document.getElementById('stepper-operator-input').value = '';
  document.getElementById('btn-next-3').setAttribute('disabled', 'true');

  // Définir le préfixe
  prefixSpan.textContent = country.prefix;
  const prefixWidth = country.prefix.length * 9 + 16;
  phoneInput.style.paddingLeft = prefixWidth + 'px';

  // Injecter les boutons opérateurs
  country.operators.forEach(op => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.id = 'op-' + op.id;
    btn.className = "flex flex-col items-center justify-center p-3 rounded-2xl border text-center transition-all border-[#1a2332] bg-[#0a0f1a] hover:border-cyan-500/20 focus:outline-none";
    btn.onclick = () => selectStepperOperator(op.id);

    // Nom Réseau
    const nameDiv = document.createElement('div');
    nameDiv.className = "text-xs font-bold text-white mt-1.5";
    nameDiv.textContent = op.name;

    btn.appendChild(nameDiv);
    container.appendChild(btn);
  });
}

function selectStepperOperator(opId) {
  selectedOperatorId = opId;
  document.getElementById('stepper-operator-input').value = opId;

  const container = document.getElementById('stepper-operators-container');
  container.querySelectorAll('button').forEach(btn => {
    btn.className = "flex flex-col items-center justify-center p-3 rounded-2xl border text-center transition-all border-[#1a2332] bg-[#0a0f1a] hover:border-[#00ff88]/20 focus:outline-none";
  });

  const activeBtn = document.getElementById('op-' + opId);
  if (activeBtn) {
    activeBtn.className = "flex flex-col items-center justify-center p-3 rounded-2xl border text-center transition-all border-[#00ff88]/40 bg-[#00ff88]/5 focus:outline-none";
  }

  validateStepperPhone();
}

function validateStepperPhone() {
  const phone = document.getElementById('stepper-phone').value.trim();
  const btnNext = document.getElementById('btn-next-3');

  if (selectedOperatorId && phone.length >= 6) {
    btnNext.removeAttribute('disabled');
  } else {
    btnNext.setAttribute('disabled', 'true');
  }
}

// ==================== GESTION DES ÉTAPES (STEPS) ====================
function goToStep(step) {
  // Empêcher d'aller aux étapes futures sans validation
  if (step === 2 && (!parseFloat(document.getElementById('stepper-amount').value) || parseFloat(document.getElementById('stepper-amount').value) <= 0)) return;
  if (step === 3 && !selectedCountryCode) return;
  if (step === 4 && (!selectedOperatorId || document.getElementById('stepper-phone').value.trim().length < 6)) return;

  currentStep = step;

  // Masquer tous les contenus d'étapes
  document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
  document.getElementById('step-content-' + step).classList.remove('hidden');

  // Mettre à jour la barre d'indicateurs
  for (let i = 1; i <= 4; i++) {
    const numEl = document.getElementById('step-num-' + i);
    const textEl = document.getElementById('step-text-' + i);
    const lineEl = document.getElementById('step-line-' + i);

    if (i < step) {
      // Étape terminée
      numEl.className = "w-8 h-8 rounded-full flex items-center justify-center text-xs font-black bg-[#00ff88] text-black transition-all duration-300";
      textEl.className = "text-xs font-bold text-white hidden sm:inline";
      if (lineEl) lineEl.style.background = "#00ff88";
    } else if (i === step) {
      // Étape active
      numEl.className = "w-8 h-8 rounded-full flex items-center justify-center text-xs font-black bg-cyan-500 text-black transition-all duration-300";
      textEl.className = "text-xs font-bold text-white hidden sm:inline";
      if (lineEl) lineEl.style.background = "#1a2332";
    } else {
      // Étape future
      numEl.className = "w-8 h-8 rounded-full flex items-center justify-center text-xs font-black bg-[#1a2332] text-gray-500 transition-all duration-300";
      textEl.className = "text-xs font-bold text-gray-500 hidden sm:inline";
      if (lineEl) lineEl.style.background = "#1a2332";
    }
  }

  // Si étape 4, préparer le résumé de confirmation
  if (step === 4) {
    setupConfirmationStep();
  }
}

function nextStep(current) {
  goToStep(current + 1);
}

function prevStep(current) {
  goToStep(current - 1);
}

// ==================== PRÉPARER LA VALIDATION FINALE ====================
function setupConfirmationStep() {
  const amount = parseFloat(document.getElementById('stepper-amount').value) || 0;
  const country = countriesData[selectedCountryCode];
  const operatorName = country.operators.find(o => o.id === selectedOperatorId)?.name || selectedOperatorId;
  const rawPhone = document.getElementById('stepper-phone').value.trim();

  // Convertir le montant USD d'abord si c'est du CDF
  let amountUsd = amount;
  if (selectedCurrency === 'CDF') {
    amountUsd = amount / exchangeRate;
  }

  const rate = country.rate;
  const targetAmount = Math.round(amountUsd * rate);

  // Remplir les informations textuelles
  document.getElementById('final-original-amount').textContent = amount.toLocaleString('fr-FR') + ' ' + selectedCurrency;
  document.getElementById('final-country').textContent = country.name;
  document.getElementById('final-phone').textContent = operatorName + ' (' + country.prefix + ' ' + rawPhone + ')';
  document.getElementById('final-rate').textContent = '1 USD = ' + rate + ' ' + country.currency;
  document.getElementById('final-target-amount').textContent = targetAmount.toLocaleString('fr-FR') + ' ' + country.currency;
}

// Intercepter la soumission finale pour formater le numéro avec préfixe pays
document.getElementById('recharge-stepper-form').addEventListener('submit', function(e) {
  const country = countriesData[selectedCountryCode];
  const rawPhone = document.getElementById('stepper-phone').value.trim();
  
  // Nettoyer le numéro (chiffres uniquement et enlever un éventuel 0 initial)
  let cleanPhone = rawPhone.replace(/\D/g, '');
  if (cleanPhone.startsWith('0')) {
    cleanPhone = cleanPhone.substring(1);
  }

  // Assigner la valeur définitive nettoyée avec préfixe
  document.getElementById('stepper-phone').value = country.prefix + cleanPhone;
});
</script>
