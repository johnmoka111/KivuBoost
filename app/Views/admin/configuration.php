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
      <p class="text-xs text-gray-500 mt-0.5">Configuration de la plateforme et intégrations Mobile Money.</p>
    </div>
  </div>

  <form method="POST" action="<?= APP_BASE ?>/admin/settings/update" class="space-y-6">
    <?= Auth::csrfField() ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

      <!-- Configuration Générale & Mobile Money -->
      <div class="rounded-2xl border p-5 space-y-4 shadow-lg" style="background:#0d1117;border-color:#1a2332">
        <h3 class="font-bold text-white text-sm border-b pb-2 mb-4 border-[#1a2332] flex items-center gap-1.5">
          <svg class="w-4 h-4 text-[#00ff88] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
          Paramètres Généraux & Mobile Money
        </h3>

        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5">Nom de la Plateforme</label>
          <input type="text" name="site_name" value="<?= htmlspecialchars($allSettings['site_name'] ?? 'KivuBoost') ?>" class="input-field w-full px-3 py-2.5 rounded-xl text-sm">
        </div>

        <input type="hidden" name="markup_percentage" value="0">

        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5">Taux de change (1 USD = X CDF)</label>
          <input type="number" name="usd_rate_cdf" required value="<?= htmlspecialchars($allSettings['usd_rate_cdf'] ?? '2800') ?>" class="input-field w-full px-3 py-2.5 rounded-xl text-sm font-mono">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-medium text-gray-400 mb-1.5">Numéro M-Pesa (Vodacom)</label>
            <input type="text" name="mpesa_number" value="<?= htmlspecialchars($allSettings['mpesa_number'] ?? '+243999999999') ?>" class="input-field w-full px-3 py-2.5 rounded-xl text-sm font-mono">
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-400 mb-1.5">Numéro Airtel Money</label>
            <input type="text" name="airtel_number" value="<?= htmlspecialchars($allSettings['airtel_number'] ?? '+243888888888') ?>" class="input-field w-full px-3 py-2.5 rounded-xl text-sm font-mono">
          </div>
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5">Numéro Orange Money</label>
          <input type="text" name="orange_number" value="<?= htmlspecialchars($allSettings['orange_number'] ?? '') ?>" class="input-field w-full px-3 py-2.5 rounded-xl text-sm font-mono">
        </div>
      </div>

      <!-- Intégrations PawaPay / VisaPay -->
      <div class="rounded-2xl border p-5 space-y-4 shadow-lg" style="background:#0d1117;border-color:#1a2332">
        <h3 class="font-bold text-white text-sm border-b pb-2 mb-4 border-[#1a2332] flex items-center gap-1.5">
          <svg class="w-4 h-4 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          Intégrations futures (PawaPay / VisaPay)
        </h3>

        <div class="pt-2">
          <div class="flex items-center justify-between">
            <label class="text-xs font-medium text-gray-300">Activer l'API PawaPay (Mobile Money RDC automatique)</label>
            <input type="checkbox" name="pawapay_enabled" value="1" <?= ($allSettings['pawapay_enabled'] ?? '0') === '1' ? 'checked' : '' ?> class="rounded border-[#1a2332] bg-[#0a0f1a] text-emerald-500 focus:ring-emerald-500/20">
          </div>
          <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
            <input type="text" name="pawapay_api_key" value="<?= htmlspecialchars($allSettings['pawapay_api_key'] ?? '') ?>" placeholder="Clé API PawaPay" class="input-field w-full px-2.5 py-2 rounded-lg text-xs font-mono">
            <input type="password" name="pawapay_secret" value="<?= htmlspecialchars($allSettings['pawapay_secret'] ?? '') ?>" placeholder="Secret PawaPay" class="input-field w-full px-2.5 py-2 rounded-lg text-xs font-mono">
          </div>
        </div>

        <div class="border-t border-[#1a2332] pt-4">
          <div class="flex items-center justify-between">
            <label class="text-xs font-medium text-gray-300">Activer l'API VisaPay (Cartes bancaires)</label>
            <input type="checkbox" name="visapay_enabled" value="1" <?= ($allSettings['visapay_enabled'] ?? '0') === '1' ? 'checked' : '' ?> class="rounded border-[#1a2332] bg-[#0a0f1a] text-emerald-500 focus:ring-emerald-500/20">
          </div>
          <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
            <input type="text" name="visapay_api_key" value="<?= htmlspecialchars($allSettings['visapay_api_key'] ?? '') ?>" placeholder="Clé API VisaPay" class="input-field w-full px-2.5 py-2 rounded-lg text-xs font-mono">
            <input type="password" name="visapay_secret" value="<?= htmlspecialchars($allSettings['visapay_secret'] ?? '') ?>" placeholder="Secret VisaPay" class="input-field w-full px-2.5 py-2 rounded-lg text-xs font-mono">
          </div>
        </div>
      </div>

    </div>

    <button type="submit"
            class="w-full py-3 rounded-xl text-sm font-bold shadow-lg mt-2"
            style="background:linear-gradient(135deg,#00ff88,#00c466);color:#050811;box-shadow:0 4px 15px rgba(0,255,136,0.2)">
      Mettre à jour la configuration globale
    </button>
  </form>
</div>
