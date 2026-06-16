<?php
$base = rtrim(APP_BASE, '/');
$csrfToken = App\Core\Auth::csrfToken();
?>
<div class="max-w-5xl mx-auto">

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
      <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-2">
        <span class="w-2.5 h-6 rounded inline-block" style="background:#a78bfa"></span>
        Regles de Tarification Automatiques
      </h1>
      <p class="text-xs text-gray-500 mt-1">Definissez des majorations automatiques par categorie ou par fournisseur. Elles s'appliquent a la synchronisation et au recalcul.</p>
    </div>
    <div class="flex gap-2 flex-wrap">
      <!-- Appliquer toutes les regles -->
      <form action="<?= $base ?>/admin/pricing-rules/apply" method="POST">
        <input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
        <button type="submit" onclick="return confirm('Recalculer tous les prix avec les regles actives ?')"
                class="px-4 py-2.5 rounded-xl text-xs font-bold border transition-all active:scale-95"
                style="border-color:rgba(167,139,250,0.3);color:#a78bfa;background:rgba(167,139,250,0.08)">
          Appliquer sur tous les services
        </button>
      </form>
      <button onclick="toggleNewRuleForm()"
              class="px-4 py-2.5 rounded-xl text-xs font-bold text-black transition-all active:scale-95"
              style="background:linear-gradient(135deg,#a78bfa,#7c3aed)">
        + Nouvelle regle
      </button>
    </div>
  </div>

  <!-- Formulaire creation/edition -->
  <div id="rule-form-container" class="hidden mb-8 rounded-2xl border p-5" style="background:#0d1117;border-color:#1a2332">
    <h2 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
      <span class="w-1.5 h-4 rounded inline-block" style="background:#a78bfa"></span>
      <span id="form-title">Nouvelle Regle</span>
    </h2>
    <form id="rule-form" action="<?= $base ?>/admin/pricing-rules/save" method="POST" class="space-y-4">
      <input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
      <input type="hidden" name="id" id="rule-id" value="0">

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] uppercase font-bold tracking-wider text-gray-500 mb-1.5">Nom de la regle</label>
          <input type="text" name="name" id="rule-name" required placeholder="ex: Majoration Instagram"
                 class="w-full px-4 py-2.5 rounded-xl text-sm text-white input-field">
        </div>

        <div>
          <label class="block text-[10px] uppercase font-bold tracking-wider text-gray-500 mb-1.5">Type de regle</label>
          <select name="rule_type" id="rule-type" onchange="onRuleTypeChange(this.value)"
                  class="w-full px-4 py-2.5 rounded-xl text-sm text-white input-field">
            <option value="category">Par categorie de service</option>
            <option value="provider">Par fournisseur SMM</option>
          </select>
        </div>

        <div>
          <label class="block text-[10px] uppercase font-bold tracking-wider text-gray-500 mb-1.5">
            <span id="target-label">Categorie cible (mot-cle)</span>
          </label>
          <input type="text" name="target_value" id="rule-target" required placeholder="ex: Instagram"
                 class="w-full px-4 py-2.5 rounded-xl text-sm text-white input-field" id="target-input">
          <select name="target_value" id="rule-target-provider" class="hidden w-full px-4 py-2.5 rounded-xl text-sm text-white input-field">
            <?php foreach ($providers as $p): ?>
              <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-[10px] uppercase font-bold tracking-wider text-gray-500 mb-1.5">Majoration supplementaire (%)</label>
          <input type="number" name="markup_extra" id="rule-markup" min="0" max="500" step="0.5" value="10" required
                 class="w-full px-4 py-2.5 rounded-xl text-sm text-white input-field">
          <p class="text-[10px] text-gray-600 mt-1">S'ajoute a la marge de base du fournisseur lors de la sync.</p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <input type="checkbox" name="is_active" id="rule-active" checked class="w-4 h-4 rounded">
        <label for="rule-active" class="text-xs text-gray-300 font-semibold">Regle active</label>
      </div>

      <div class="flex justify-end gap-3 pt-2">
        <button type="button" onclick="toggleNewRuleForm()"
                class="px-4 py-2 rounded-xl border border-[#1a2332] text-xs font-semibold text-gray-400 hover:bg-white/5 transition-colors">
          Annuler
        </button>
        <button type="submit"
                class="px-5 py-2.5 rounded-xl text-xs font-bold text-black transition-all active:scale-95"
                style="background:linear-gradient(135deg,#a78bfa,#7c3aed);color:#fff">
          Sauvegarder la regle
        </button>
      </div>
    </form>
  </div>

  <!-- Explication -->
  <div class="mb-6 rounded-xl border p-4 flex gap-3" style="background:rgba(167,139,250,0.04);border-color:rgba(167,139,250,0.2)">
    <svg class="w-4 h-4 text-purple-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <div class="text-[11px] text-gray-400 leading-relaxed">
      <strong class="text-purple-300">Comment ca fonctionne :</strong>
      Lors de chaque synchronisation, le prix de vente = Prix grossiste x (1 + Marge fournisseur% + Majoration regle%). Les regles de categorie utilisent une correspondance partielle (ex: "Instagram" correspond a "Instagram Followers", "Instagram Likes", etc.).
      Plusieurs regles peuvent s'additionner sur un meme service.
    </div>
  </div>

  <!-- Table des regles -->
  <div class="rounded-2xl border" style="background:#0d1117;border-color:#1a2332">
    <div class="px-5 py-4 border-b flex items-center gap-2" style="border-color:#1a2332">
      <svg class="w-4 h-4 shrink-0" style="color:#a78bfa" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
      </svg>
      <h2 class="text-sm font-bold text-white">Regles configurees (<?= count($rules) ?>)</h2>
    </div>

    <?php if (empty($rules)): ?>
      <div class="text-center py-14 text-gray-600">
        <svg class="w-10 h-10 mx-auto mb-3 opacity-30" style="color:#a78bfa" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        <p class="text-sm font-medium text-gray-500">Aucune regle definie. Cliquez sur "Nouvelle regle" pour commencer.</p>
      </div>
    <?php else: ?>

      <!-- Desktop -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr style="border-bottom:1px solid #1a2332" class="text-gray-500 text-[10px] font-semibold uppercase tracking-widest bg-[#0a0f1a]/30">
              <th class="text-left px-5 py-3">Nom</th>
              <th class="text-left px-5 py-3">Type</th>
              <th class="text-left px-5 py-3">Cible</th>
              <th class="text-center px-5 py-3">+Majoration</th>
              <th class="text-center px-5 py-3">Statut</th>
              <th class="text-right px-5 py-3">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#1a2332]">
            <?php foreach ($rules as $rule): ?>
            <tr class="hover:bg-white/[0.01] transition-colors">
              <td class="px-5 py-3.5 font-semibold text-white text-xs"><?= htmlspecialchars($rule['name']) ?></td>
              <td class="px-5 py-3.5">
                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold <?= $rule['rule_type'] === 'category' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : 'bg-orange-500/10 text-orange-400 border border-orange-500/20' ?>">
                  <?= $rule['rule_type'] === 'category' ? 'Categorie' : 'Fournisseur' ?>
                </span>
              </td>
              <td class="px-5 py-3.5 text-xs text-gray-300 font-mono">
                <?php if ($rule['rule_type'] === 'provider'): ?>
                  <?php
                    $pName = 'ID ' . $rule['target_value'];
                    foreach ($providers as $p) {
                        if ($p['id'] == $rule['target_value']) { $pName = $p['name']; break; }
                    }
                  ?>
                  <?= htmlspecialchars($pName) ?>
                <?php else: ?>
                  <?= htmlspecialchars($rule['target_value']) ?>
                <?php endif; ?>
              </td>
              <td class="px-5 py-3.5 text-center">
                <span class="text-sm font-black" style="color:#a78bfa">+<?= number_format((float)$rule['markup_extra'], 1) ?>%</span>
              </td>
              <td class="px-5 py-3.5 text-center">
                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold <?= $rule['is_active'] ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-gray-500/10 text-gray-500 border border-gray-500/20' ?>">
                  <?= $rule['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
              </td>
              <td class="px-5 py-3.5 text-right flex items-center justify-end gap-2">
                <button onclick="editRule(<?= htmlspecialchars(json_encode($rule)) ?>)"
                        class="px-3 py-1.5 rounded-lg text-[10px] font-bold border border-[#1a2332] text-purple-400 hover:bg-purple-500/10 transition-all">
                  Modifier
                </button>
                <form action="<?= $base ?>/admin/pricing-rules/delete" method="POST" onsubmit="return confirm('Supprimer cette regle ?')">
                  <input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
                  <input type="hidden" name="id" value="<?= $rule['id'] ?>">
                  <button type="submit" class="px-3 py-1.5 rounded-lg text-[10px] font-bold border border-red-500/20 text-red-400 hover:bg-red-500/10 transition-all">
                    Supprimer
                  </button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Mobile cards -->
      <div class="block md:hidden divide-y divide-[#1a2332]">
        <?php foreach ($rules as $rule): ?>
        <div class="p-4 space-y-2">
          <div class="flex items-center justify-between">
            <span class="font-bold text-white text-sm"><?= htmlspecialchars($rule['name']) ?></span>
            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold <?= $rule['rule_type'] === 'category' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : 'bg-orange-500/10 text-orange-400 border border-orange-500/20' ?>">
              <?= $rule['rule_type'] === 'category' ? 'Categorie' : 'Fournisseur' ?>
            </span>
          </div>
          <div class="flex items-center gap-3 text-xs text-gray-400">
            <span>Cible : <strong class="text-gray-200"><?= htmlspecialchars($rule['target_value']) ?></strong></span>
            <span>Majoration : <strong style="color:#a78bfa">+<?= number_format((float)$rule['markup_extra'], 1) ?>%</strong></span>
          </div>
          <div class="flex gap-2 pt-1">
            <button onclick="editRule(<?= htmlspecialchars(json_encode($rule)) ?>)"
                    class="flex-1 py-1.5 rounded-lg text-[10px] font-bold border border-[#1a2332] text-purple-400">Modifier</button>
            <form action="<?= $base ?>/admin/pricing-rules/delete" method="POST" class="flex-1" onsubmit="return confirm('Supprimer ?')">
              <input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
              <input type="hidden" name="id" value="<?= $rule['id'] ?>">
              <button type="submit" class="w-full py-1.5 rounded-lg text-[10px] font-bold border border-red-500/20 text-red-400">Supprimer</button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
function toggleNewRuleForm() {
  const c = document.getElementById('rule-form-container');
  c.classList.toggle('hidden');
  if (!c.classList.contains('hidden')) {
    // Reset to new mode
    document.getElementById('rule-id').value = '0';
    document.getElementById('rule-name').value = '';
    document.getElementById('rule-markup').value = '10';
    document.getElementById('rule-active').checked = true;
    document.getElementById('form-title').textContent = 'Nouvelle Regle';
    document.getElementById('rule-form').action = '<?= $base ?>/admin/pricing-rules/save';
    c.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }
}

function onRuleTypeChange(val) {
  const targetInput = document.getElementById('rule-target');
  const targetSelect = document.getElementById('rule-target-provider');
  const label = document.getElementById('target-label');
  if (val === 'provider') {
    targetInput.classList.add('hidden');
    targetInput.name = '';
    targetSelect.classList.remove('hidden');
    targetSelect.name = 'target_value';
    label.textContent = 'Fournisseur cible';
  } else {
    targetInput.classList.remove('hidden');
    targetInput.name = 'target_value';
    targetSelect.classList.add('hidden');
    targetSelect.name = '';
    label.textContent = 'Categorie cible (mot-cle)';
  }
}

function editRule(rule) {
  document.getElementById('rule-id').value = rule.id;
  document.getElementById('rule-name').value = rule.name;
  document.getElementById('rule-markup').value = rule.markup_extra;
  document.getElementById('rule-active').checked = rule.is_active == 1;
  document.getElementById('rule-type').value = rule.rule_type;
  onRuleTypeChange(rule.rule_type);
  if (rule.rule_type === 'category') {
    document.getElementById('rule-target').value = rule.target_value;
  } else {
    document.getElementById('rule-target-provider').value = rule.target_value;
  }
  document.getElementById('form-title').textContent = 'Modifier la regle';
  const c = document.getElementById('rule-form-container');
  c.classList.remove('hidden');
  c.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}
</script>
