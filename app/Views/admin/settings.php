<?php
use App\Core\Auth;
Auth::requireAdmin();

$pageTitle = 'Paramètres des Marges Fournisseurs';
?>

<div class="max-w-3xl mx-auto">
  <div class="mb-6">
    <h1 class="text-xl font-bold text-white flex items-center gap-2">
      <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg> 
      Paramètres des Marges Fournisseurs
    </h1>
    <p class="text-gray-500 text-sm mt-1">Définissez le pourcentage de profit applicable pour chaque grossiste API.</p>
  </div>

  <div class="rounded-2xl p-6 border" style="background:#0d1117;border-color:#1a2332;box-shadow:0 10px 30px rgba(0,0,0,0.2)">
    <form method="POST" action="<?= APP_BASE ?>/admin/settings/update-margins" class="space-y-6">
      <?= Auth::csrfField() ?>

      <div class="space-y-4">
        <?php foreach ($providers as $provider): ?>
          <div class="p-4 rounded-xl border flex flex-col sm:flex-row sm:items-center justify-between gap-4" style="background:#0a0f1a;border-color:#1a2332;">
            <div>
              <h3 class="text-sm font-bold text-white"><?= htmlspecialchars($provider['name']) ?></h3>
              <p class="text-xs text-gray-500 mt-0.5">URL API : <?= htmlspecialchars(parse_url($provider['api_url'], PHP_URL_HOST)) ?></p>
            </div>
            <div class="w-full sm:w-48">
              <label class="block text-xs font-medium text-gray-400 mb-1.5" for="markup_<?= $provider['id'] ?>">Marge Client (% markup)</label>
              <div class="relative">
                <input type="number" 
                       name="margins[<?= $provider['id'] ?>]" 
                       id="markup_<?= $provider['id'] ?>" 
                       value="<?= htmlspecialchars((string)$provider['markup_percentage']) ?>" 
                       min="0" max="1000" required
                       class="w-full pl-4 pr-8 py-2 rounded-lg text-sm font-bold text-white"
                       style="background:#050811;border:1px solid #1a2332;transition:border-color .2s"
                       onfocus="this.style.borderColor='rgba(0,255,136,0.5)'"
                       onblur="this.style.borderColor='#1a2332'">
                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-bold">%</span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
        
        <?php if (empty($providers)): ?>
          <div class="text-sm text-gray-500 italic text-center py-4">Aucun fournisseur API actif trouvé.</div>
        <?php endif; ?>
      </div>

      <button type="submit"
              class="w-full py-3.5 rounded-xl text-sm font-bold transition-all mt-4"
              style="background:linear-gradient(135deg,#00ff88,#00c466);color:#050811;box-shadow:0 4px 15px rgba(0,255,136,0.2)">
        Enregistrer les Marges
      </button>
    </form>
  </div>
</div>
