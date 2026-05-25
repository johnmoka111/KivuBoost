<?php
use App\Core\Auth;
Auth::requireAdmin();
$basePath = rtrim(APP_BASE, '/');
$h = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
?>

<div class="px-4 lg:px-6 space-y-8 max-w-5xl mx-auto pt-2 pb-16">

  <!-- En-tête -->
  <div class="flex items-center gap-3">
    <div class="p-2.5 rounded-xl" style="background:#0a0f1a;border:1px solid #1a2332;">
      <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
      </svg>
    </div>
    <div>
      <h1 class="text-xl font-bold text-white">Gestion du Support Client</h1>
      <p class="text-xs text-gray-500 mt-0.5">Configurez les réseaux sociaux et gérez les agents de support.</p>
    </div>
  </div>

  <!-- ═══════════════════════════════════════════════════════ -->
  <!-- FORMULAIRE 1 : Réseaux Généraux                        -->
  <!-- ═══════════════════════════════════════════════════════ -->
  <div class="rounded-2xl border p-6 space-y-5" style="background:#0d1117;border-color:#1a2332;">
    <h2 class="text-sm font-bold text-white flex items-center gap-2 border-b border-[#1a2332] pb-3">
      <svg class="w-4 h-4 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
      </svg>
      Liens de Contact Généraux
    </h2>

    <form method="POST" action="<?= $basePath ?>/admin/support/settings">
      <?= Auth::csrfField() ?>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5">
            WhatsApp principal <span class="text-zinc-600">(sans +)</span>
          </label>
          <input type="text" name="main_whatsapp"
                 value="<?= $h($allSettings['main_whatsapp'] ?? '') ?>"
                 placeholder="243999999999"
                 class="input-field w-full px-3 py-2.5 rounded-xl text-sm font-mono">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5">URL Facebook</label>
          <input type="url" name="facebook_url"
                 value="<?= $h($allSettings['facebook_url'] ?? '') ?>"
                 placeholder="https://facebook.com/kivuboost"
                 class="input-field w-full px-3 py-2.5 rounded-xl text-sm">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5">URL Instagram</label>
          <input type="url" name="instagram_url"
                 value="<?= $h($allSettings['instagram_url'] ?? '') ?>"
                 placeholder="https://instagram.com/kivuboost"
                 class="input-field w-full px-3 py-2.5 rounded-xl text-sm">
        </div>
      </div>
      <button type="submit"
              class="mt-4 w-full sm:w-auto px-6 py-2.5 rounded-xl text-sm font-bold transition-all hover:scale-[1.02]"
              style="background:linear-gradient(135deg,#00ff88,#00c466);color:#050811;">
        Enregistrer les liens
      </button>
    </form>
  </div>

  <!-- ═══════════════════════════════════════════════════════ -->
  <!-- FORMULAIRE 2 : Ajouter un Agent                        -->
  <!-- ═══════════════════════════════════════════════════════ -->
  <div class="rounded-2xl border p-6 space-y-5" style="background:#0d1117;border-color:#1a2332;">
    <h2 class="text-sm font-bold text-white flex items-center gap-2 border-b border-[#1a2332] pb-3">
      <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
      </svg>
      Ajouter un Agent de Support
    </h2>

    <form method="POST" action="<?= $basePath ?>/admin/support/agents/add"
          enctype="multipart/form-data">
      <?= Auth::csrfField() ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5">Nom complet</label>
          <input type="text" name="name" required maxlength="100"
                 placeholder="Ex: Jean Muhindo"
                 class="input-field w-full px-3 py-2.5 rounded-xl text-sm">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5">Ville</label>
          <input type="text" name="city" required maxlength="100"
                 placeholder="Ex: Goma, Bukavu, Kinshasa"
                 class="input-field w-full px-3 py-2.5 rounded-xl text-sm">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5">
            WhatsApp <span class="text-zinc-600">(sans +)</span>
          </label>
          <input type="text" name="whatsapp_number" required maxlength="20"
                 placeholder="243999000000"
                 class="input-field w-full px-3 py-2.5 rounded-xl text-sm font-mono">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5">
            Photo <span class="text-zinc-600">(JPG/PNG/WEBP, max 2Mo)</span>
          </label>
          <input type="file" name="photo" accept="image/jpeg,image/png,image/webp,image/gif"
                 class="input-field w-full px-3 py-2 rounded-xl text-sm text-gray-400
                        file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0
                        file:text-xs file:font-semibold file:bg-emerald-500/10 file:text-emerald-400
                        hover:file:bg-emerald-500/20 file:cursor-pointer cursor-pointer">
        </div>
      </div>
      <button type="submit"
              class="mt-4 w-full sm:w-auto px-6 py-2.5 rounded-xl text-sm font-bold
                     border border-emerald-500/40 text-emerald-400 hover:bg-emerald-500/10
                     transition-all hover:scale-[1.02]">
        + Ajouter l'agent
      </button>
    </form>
  </div>

  <!-- ═══════════════════════════════════════════════════════ -->
  <!-- LISTE DES AGENTS                                       -->
  <!-- ═══════════════════════════════════════════════════════ -->
  <div class="rounded-2xl border" style="background:#0d1117;border-color:#1a2332;">
    <div class="flex items-center justify-between px-5 py-4 border-b border-[#1a2332]">
      <h2 class="text-sm font-bold text-white">
        Agents Enregistrés
        <span class="ml-2 text-xs font-normal text-zinc-500">(<?= count($agents) ?>)</span>
      </h2>
      <a href="<?= $basePath ?>/support" target="_blank"
         class="text-xs text-cyan-400 hover:text-cyan-300 flex items-center gap-1 transition-colors">
        Voir la page publique
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
        </svg>
      </a>
    </div>

    <?php if (empty($agents)): ?>
    <div class="flex flex-col items-center justify-center py-16 text-center">
      <svg class="w-10 h-10 text-zinc-700 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
      </svg>
      <p class="text-zinc-500 text-sm">Aucun agent ajouté. Utilisez le formulaire ci-dessus.</p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-xs text-gray-600 uppercase tracking-wider border-b border-[#1a2332]">
            <th class="px-5 py-3 font-semibold">Agent</th>
            <th class="px-5 py-3 font-semibold hidden sm:table-cell">Ville</th>
            <th class="px-5 py-3 font-semibold hidden md:table-cell">WhatsApp</th>
            <th class="px-5 py-3 font-semibold">Statut</th>
            <th class="px-5 py-3 font-semibold text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#1a2332]">
          <?php foreach ($agents as $ag): ?>
          <?php
            $agName = htmlspecialchars($ag['name'], ENT_QUOTES, 'UTF-8');
            $agCity = htmlspecialchars($ag['city'], ENT_QUOTES, 'UTF-8');
            $agWa   = htmlspecialchars($ag['whatsapp_number'], ENT_QUOTES, 'UTF-8');
            $active = (int)$ag['is_active'];
            $avatarUrl = $ag['photo_path']
              ? $basePath . '/public/' . htmlspecialchars($ag['photo_path'], ENT_QUOTES, 'UTF-8')
              : 'https://ui-avatars.com/api/?background=0d1117&color=00ff88&bold=true&size=64&name=' . urlencode($ag['name']);
          ?>
          <tr class="hover:bg-white/[.02] transition-colors">
            <td class="px-5 py-3.5">
              <div class="flex items-center gap-3">
                <img src="<?= $avatarUrl ?>" alt="<?= $agName ?>"
                     class="w-9 h-9 rounded-full object-cover border border-zinc-800 shrink-0">
                <span class="font-semibold text-white"><?= $agName ?></span>
              </div>
            </td>
            <td class="px-5 py-3.5 text-zinc-400 hidden sm:table-cell"><?= $agCity ?></td>
            <td class="px-5 py-3.5 hidden md:table-cell">
              <a href="https://wa.me/<?= $agWa ?>" target="_blank"
                 class="font-mono text-emerald-400 hover:text-emerald-300 text-xs transition-colors">
                +<?= $agWa ?>
              </a>
            </td>
            <td class="px-5 py-3.5">
              <?php if ($active): ?>
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[11px] font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Actif
              </span>
              <?php else: ?>
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-zinc-800 border border-zinc-700 text-zinc-500 text-[11px] font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-zinc-600"></span> Inactif
              </span>
              <?php endif; ?>
            </td>
            <td class="px-5 py-3.5">
              <div class="flex items-center justify-end gap-2">
                <!-- Toggle actif/inactif -->
                <form method="POST" action="<?= $basePath ?>/admin/support/agents/toggle">
                  <?= Auth::csrfField() ?>
                  <input type="hidden" name="id" value="<?= (int)$ag['id'] ?>">
                  <input type="hidden" name="is_active" value="<?= $active ? 0 : 1 ?>">
                  <button type="submit"
                          class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors
                                 <?= $active
                                   ? 'bg-zinc-800 hover:bg-zinc-700 text-zinc-300'
                                   : 'bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20' ?>">
                    <?= $active ? 'Désactiver' : 'Activer' ?>
                  </button>
                </form>
                <!-- Supprimer -->
                <form method="POST" action="<?= $basePath ?>/admin/support/agents/delete"
                      onsubmit="return confirm('Supprimer cet agent définitivement ?')">
                  <?= Auth::csrfField() ?>
                  <input type="hidden" name="id" value="<?= (int)$ag['id'] ?>">
                  <button type="submit"
                          class="px-3 py-1.5 rounded-lg text-xs font-semibold
                                 bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20
                                 transition-colors">
                    Supprimer
                  </button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
