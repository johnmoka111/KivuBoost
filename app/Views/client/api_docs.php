<?php
$pageTitle = 'Connecteur API';
$baseUrl = APP_BASE;
?>

<div class="mb-6">
  <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
    <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg> Connecteur API
  </h1>
  <p class="text-gray-400 text-sm mt-1">Automatisez vos commandes en connectant votre plateforme (Goma, Kinshasa...) à notre panel de Bukavu.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  <!-- Bloc Clé API -->
  <div class="lg:col-span-1 space-y-6">
    <div class="glass-card rounded-2xl p-6 border border-[#1a2332]">
      <h2 class="text-sm font-bold text-white mb-4 uppercase tracking-wider">Vos Identifiants API</h2>
      
      <?php if ($api_key): ?>
        <div class="mb-4">
          <label class="block text-xs font-medium text-gray-500 mb-1.5">Jeton d'accès secret (API Key)</label>
          <div class="flex items-center gap-2 bg-[#050811] border border-[#1a2332] p-2 rounded-lg">
            <code class="text-emerald-400 text-xs break-all flex-1 select-all"><?= htmlspecialchars($api_key) ?></code>
          </div>
        </div>
      <?php else: ?>
        <div class="mb-4 p-4 rounded-lg bg-yellow-500/10 border border-yellow-500/20 text-yellow-500 text-xs">
          Vous n'avez pas encore généré de clé API.
        </div>
      <?php endif; ?>

      <form method="POST" action="<?= $baseUrl ?>/api-docs/generate-key">
        <button type="submit" onclick="return confirm('Attention: Générer une nouvelle clé invalidera l\'ancienne. Continuer ?')" class="btn-primary w-full py-2.5 rounded-xl text-sm flex items-center justify-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
          <?= $api_key ? 'Régénérer le jeton' : 'Générer mon jeton' ?>
        </button>
      </form>
    </div>

    <!-- Exemples de code -->
    <div class="glass-card rounded-2xl p-6 border border-[#1a2332]">
      <h2 class="text-sm font-bold text-white mb-4 uppercase tracking-wider">Exemples d'intégration</h2>
      <div class="space-y-3">
        <a href="<?= $baseUrl ?>/public/examples/php.txt" target="_blank" class="flex items-center justify-between p-3 rounded-lg bg-[#050811] hover:bg-[#1a2332] transition-colors border border-[#1a2332] group">
          <div class="flex items-center gap-3">
            <span class="text-[#777BB4] font-bold">PHP</span>
            <span class="text-xs text-gray-500">cURL basique</span>
          </div>
          <svg class="w-4 h-4 text-gray-500 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </a>
        <a href="<?= $baseUrl ?>/public/examples/python.txt" target="_blank" class="flex items-center justify-between p-3 rounded-lg bg-[#050811] hover:bg-[#1a2332] transition-colors border border-[#1a2332] group">
          <div class="flex items-center gap-3">
            <span class="text-[#3776AB] font-bold">Python</span>
            <span class="text-xs text-gray-500">Requests (Django)</span>
          </div>
          <svg class="w-4 h-4 text-gray-500 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </a>
        <a href="<?= $baseUrl ?>/public/examples/js.txt" target="_blank" class="flex items-center justify-between p-3 rounded-lg bg-[#050811] hover:bg-[#1a2332] transition-colors border border-[#1a2332] group">
          <div class="flex items-center gap-3">
            <span class="text-[#F7DF1E] font-bold">JavaScript</span>
            <span class="text-xs text-gray-500">Axios / Fetch</span>
          </div>
          <svg class="w-4 h-4 text-gray-500 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </a>
      </div>
    </div>
  </div>

  <!-- Documentation -->
  <div class="lg:col-span-2 glass-card rounded-2xl p-6 border border-[#1a2332]">
    <h2 class="text-sm font-bold text-white mb-4 uppercase tracking-wider">Documentation REST API</h2>
    
    <div class="mb-6 p-4 rounded-lg bg-[#050811] border border-[#1a2332]">
      <span class="text-xs text-gray-500 uppercase tracking-wider">Endpoint Unique</span>
      <div class="mt-1 font-mono text-emerald-400 text-sm">
        POST <?= $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $baseUrl ?>/api/v1/index.php
      </div>
    </div>

    <!-- Onglets -->
    <div class="flex border-b border-[#1a2332] mb-4 space-x-4">
      <button onclick="showTab('action-push')" id="btn-push" class="pb-2 text-sm font-bold text-emerald-400 border-b-2 border-emerald-400">Propulser (push)</button>
      <button onclick="showTab('action-check')" id="btn-check" class="pb-2 text-sm font-bold text-gray-500 hover:text-gray-300">Statut (check)</button>
      <button onclick="showTab('action-wallet')" id="btn-wallet" class="pb-2 text-sm font-bold text-gray-500 hover:text-gray-300">Solde (wallet)</button>
      <button onclick="showTab('action-catalog')" id="btn-catalog" class="pb-2 text-sm font-bold text-gray-500 hover:text-gray-300">Tarifs (catalog)</button>
    </div>

    <div id="action-push" class="api-tab">
      <h3 class="text-white font-medium text-sm mb-2">Soumettre une nouvelle commande</h3>
      <div class="bg-[#050811] rounded-lg p-4 font-mono text-xs text-gray-300 space-y-2 border border-[#1a2332]">
        <div><span class="text-purple-400">api_key</span> : <span class="text-gray-500">votre_jeton_secret</span></div>
        <div><span class="text-purple-400">action</span> : <span class="text-emerald-400">"push"</span></div>
        <div><span class="text-purple-400">service</span> : <span class="text-gray-500">ID_du_service</span></div>
        <div><span class="text-purple-400">link</span> : <span class="text-gray-500">url_de_la_publication</span></div>
        <div><span class="text-purple-400">quantity</span> : <span class="text-gray-500">quantité_souhaitée</span></div>
      </div>
      <div class="mt-3 text-xs text-gray-400">Réponse de succès : <code class="text-emerald-400 bg-emerald-500/10 px-1 py-0.5 rounded">{"statut":"succes","id_boost":12345}</code></div>
    </div>

    <div id="action-check" class="api-tab hidden">
      <h3 class="text-white font-medium text-sm mb-2">Vérifier l'état d'un boost</h3>
      <div class="bg-[#050811] rounded-lg p-4 font-mono text-xs text-gray-300 space-y-2 border border-[#1a2332]">
        <div><span class="text-purple-400">api_key</span> : <span class="text-gray-500">votre_jeton_secret</span></div>
        <div><span class="text-purple-400">action</span> : <span class="text-emerald-400">"check"</span></div>
        <div><span class="text-purple-400">order</span> : <span class="text-gray-500">id_boost_recu_precedemment</span></div>
      </div>
    </div>

    <div id="action-wallet" class="api-tab hidden">
      <h3 class="text-white font-medium text-sm mb-2">Vérifier votre solde</h3>
      <div class="bg-[#050811] rounded-lg p-4 font-mono text-xs text-gray-300 space-y-2 border border-[#1a2332]">
        <div><span class="text-purple-400">api_key</span> : <span class="text-gray-500">votre_jeton_secret</span></div>
        <div><span class="text-purple-400">action</span> : <span class="text-emerald-400">"wallet"</span></div>
      </div>
    </div>

    <div id="action-catalog" class="api-tab hidden">
      <h3 class="text-white font-medium text-sm mb-2">Récupérer la liste des services</h3>
      <div class="bg-[#050811] rounded-lg p-4 font-mono text-xs text-gray-300 space-y-2 border border-[#1a2332]">
        <div><span class="text-purple-400">api_key</span> : <span class="text-gray-500">votre_jeton_secret</span></div>
        <div><span class="text-purple-400">action</span> : <span class="text-emerald-400">"catalog"</span></div>
      </div>
    </div>

  </div>
</div>

<script>
function showTab(id) {
  document.querySelectorAll('.api-tab').forEach(el => el.classList.add('hidden'));
  document.getElementById(id).classList.remove('hidden');
  
  const buttons = ['push', 'check', 'wallet', 'catalog'];
  buttons.forEach(btn => {
    document.getElementById('btn-' + btn).className = 'pb-2 text-sm font-bold text-gray-500 hover:text-gray-300 border-b-2 border-transparent transition-colors';
  });
  
  const activeBtn = document.getElementById('btn-' + id.replace('action-', ''));
  activeBtn.className = 'pb-2 text-sm font-bold text-emerald-400 border-b-2 border-emerald-400 transition-colors';
}
</script>
