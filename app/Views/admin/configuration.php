<?php
use App\Core\Auth;
Auth::requireAdmin();

$pageTitle = 'Configuration Globale';
?>

<div class="px-4 lg:px-6 space-y-6 max-w-5xl mx-auto pt-2">
  <div class="flex items-center gap-3 mb-6">
    <div class="p-2.5 rounded-xl" style="background:#0a0f1a;border:1px solid #1a2332;">
      <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 016 0z"/></svg>
    </div>
    <div>
      <h1 class="text-xl font-bold text-white">Paramètres Généraux</h1>
      <p class="text-xs text-gray-500 mt-0.5">Configuration de la plateforme et intégrations de paiements.</p>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <!-- Formulaire 1 : Configuration Générale & Mobile Money Manuel -->
    <div class="md:col-span-1 rounded-2xl border p-5 shadow-lg flex flex-col justify-between" style="background:#0d1117;border-color:#1a2332">
      <form method="POST" action="<?= APP_BASE ?>/admin/settings/update" class="space-y-4 h-full flex flex-col justify-between">
        <?= Auth::csrfField() ?>
        
        <div class="space-y-4">
          <h3 class="font-bold text-white text-sm border-b pb-2 border-[#1a2332] flex items-center gap-1.5">
            <svg class="w-4 h-4 text-[#00ff88] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
            Paramètres Généraux
          </h3>

          <div>
            <label class="block text-xs font-medium text-gray-400 mb-1.5">Nom de la Plateforme</label>
            <input type="text" name="site_name" value="<?= htmlspecialchars($allSettings['site_name'] ?? 'KivuBoost') ?>" class="input-field w-full px-3 py-2 rounded-xl text-sm">
          </div>

          <input type="hidden" name="markup_percentage" value="0">

          <div>
            <label class="block text-xs font-medium text-gray-400 mb-1.5">Taux de change (1 USD = X CDF)</label>
            <input type="number" name="usd_rate_cdf" required value="<?= htmlspecialchars($allSettings['usd_rate_cdf'] ?? '2800') ?>" class="input-field w-full px-3 py-2 rounded-xl text-sm font-mono">
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-400 mb-1.5">Taux de change (1 USD = X XOF - BkaPay)</label>
            <input type="number" name="usd_rate_xof" required value="<?= htmlspecialchars($allSettings['usd_rate_xof'] ?? '600') ?>" class="input-field w-full px-3 py-2 rounded-xl text-sm font-mono">
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-400 mb-1.5">Taux de change (1 USD = X XAF - BkaPay)</label>
            <input type="number" name="usd_rate_xaf" required value="<?= htmlspecialchars($allSettings['usd_rate_xaf'] ?? '600') ?>" class="input-field w-full px-3 py-2 rounded-xl text-sm font-mono">
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-400 mb-1.5">Taux de change (1 USD = X GNF - BkaPay)</label>
            <input type="number" name="usd_rate_gnf" required value="<?= htmlspecialchars($allSettings['usd_rate_gnf'] ?? '8600') ?>" class="input-field w-full px-3 py-2 rounded-xl text-sm font-mono">
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-400 mb-1.5">Numéro M-Pesa (Vodacom)</label>
            <input type="text" name="mpesa_number" value="<?= htmlspecialchars($allSettings['mpesa_number'] ?? '+243999999999') ?>" class="input-field w-full px-3 py-2 rounded-xl text-sm font-mono">
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-400 mb-1.5">Numéro Airtel Money</label>
            <input type="text" name="airtel_number" value="<?= htmlspecialchars($allSettings['airtel_number'] ?? '+243888888888') ?>" class="input-field w-full px-3 py-2 rounded-xl text-sm font-mono">
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-400 mb-1.5">Numéro Orange Money</label>
            <input type="text" name="orange_number" value="<?= htmlspecialchars($allSettings['orange_number'] ?? '') ?>" class="input-field w-full px-3 py-2 rounded-xl text-sm font-mono">
          </div>
        </div>

        <button type="submit"
                class="w-full py-2.5 rounded-xl text-xs font-bold shadow-md mt-4"
                style="background:linear-gradient(135deg,#00ff88,#00c466);color:#050811;box-shadow:0 4px 15px rgba(0,255,136,0.2)">
          Sauvegarder Général
        </button>
      </form>
    </div>

    <!-- Formulaire 2 : Configuration des Passerelles de Paiement en Ligne (Automatique) -->
    <div class="md:col-span-2 rounded-2xl border p-5 shadow-lg" style="background:#0d1117;border-color:#1a2332">
      <form method="POST" action="<?= APP_BASE ?>/admin/gateways/update" class="space-y-6">
        <?= Auth::csrfField() ?>

        <h3 class="font-bold text-white text-sm border-b pb-2 border-[#1a2332] flex items-center gap-1.5">
          <svg class="w-4 h-4 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
          Configuration des Passerelles Automatiques
        </h3>

        <div class="grid grid-cols-1 gap-4">
          <?php foreach ($gateways as $gw): ?>
          <div class="p-4 rounded-xl border border-[#1a2332] bg-[#0a0f1a] space-y-3">
            <div class="flex items-center justify-between border-b border-[#1a2332] pb-2">
              <div class="flex items-center gap-2">
                <span class="font-bold text-white text-sm"><?= htmlspecialchars($gw['name']) ?></span>
                <span class="text-[9.5px] px-2 py-0.5 rounded-full font-mono font-bold <?= $gw['is_active'] ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-gray-800 text-gray-400' ?>">
                  <?= $gw['is_active'] ? 'ACTIF' : 'INACTIF' ?>
                </span>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="gateways[<?= $gw['identifier'] ?>][is_active]" value="1" <?= $gw['is_active'] ? 'checked' : '' ?> class="sr-only peer">
                <div class="w-8 h-4.5 bg-gray-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-cyan-500"></div>
              </label>
            </div>

            <input type="hidden" name="gateways[<?= $gw['identifier'] ?>][name]" value="<?= htmlspecialchars($gw['name']) ?>">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-[10px] font-medium text-gray-400 mb-1">Clé Publique</label>
                <input type="text" name="gateways[<?= $gw['identifier'] ?>][public_key]" value="<?= htmlspecialchars($gw['public_key'] ?? '') ?>" placeholder="pk_live_..." class="input-field w-full px-2.5 py-1.5 rounded-lg text-xs font-mono">
              </div>

              <div>
                <label class="block text-[10px] font-medium text-gray-400 mb-1">Clé Privée / API Key</label>
                <input type="password" name="gateways[<?= $gw['identifier'] ?>][private_key]" value="<?= htmlspecialchars($gw['private_key'] ?? '') ?>" placeholder="sk_payin_live_..." class="input-field w-full px-2.5 py-1.5 rounded-lg text-xs font-mono">
              </div>

              <div>
                <label class="block text-[10px] font-medium text-gray-400 mb-1">Secret Webhook (HMAC Signature)</label>
                <input type="password" name="gateways[<?= $gw['identifier'] ?>][signature_secret]" value="<?= htmlspecialchars($gw['signature_secret'] ?? '') ?>" placeholder="Signature secret..." class="input-field w-full px-2.5 py-1.5 rounded-lg text-xs font-mono">
              </div>

              <div>
                <label class="block text-[10px] font-medium text-gray-400 mb-1">URL API Base / Session Endpoint</label>
                <input type="text" name="gateways[<?= $gw['identifier'] ?>][api_url]" value="<?= htmlspecialchars($gw['api_url'] ?? '') ?>" placeholder="https://..." class="input-field w-full px-2.5 py-1.5 rounded-lg text-xs font-mono">
              </div>
            </div>

            <div class="pt-1 text-[9px] text-gray-500 font-mono flex flex-wrap justify-between items-center gap-1">
              <span>Webhook : <span class="text-cyan-400 select-all"><?= APP_URL ?>/api/v1/payments/webhook/<?= $gw['identifier'] ?></span></span>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <button type="submit"
                class="w-full py-2.5 rounded-xl text-xs font-bold shadow-md"
                style="background:linear-gradient(135deg,#00d4ff,#0088ff);color:#050811;box-shadow:0 4px 15px rgba(0,212,255,0.2)">
          Sauvegarder les Passerelles Automatiques
        </button>
      </form>
    </div>

  </div>
</div>
