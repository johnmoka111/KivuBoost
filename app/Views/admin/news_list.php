<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-white mb-1">Actualités</h1>
        <p class="text-sm text-gray-400">Gérez les articles de votre vitrine KivuBoost.</p>
    </div>
    <a href="<?= APP_BASE ?>/admin/actualites/creer" class="btn-primary px-4 py-2 rounded-lg text-sm flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Nouvelle Actualité
    </a>
</div>

<div class="glass-card rounded-2xl overflow-hidden">
    <div class="overflow-x-auto sm:overflow-visible">
        <table class="w-full text-sm text-left table-fixed sm:table-auto">
            <thead class="text-xs uppercase text-gray-500 bg-[#0a0f1a] border-b border-[#1a2332]">
                <tr>
                    <th scope="col" class="px-2 py-3.5 sm:px-6 sm:py-4 w-16 sm:w-auto">Image</th>
                    <th scope="col" class="px-2 py-3.5 sm:px-6 sm:py-4">Titre</th>
                    <th scope="col" class="px-6 py-4 hidden sm:table-cell">Statut</th>
                    <th scope="col" class="px-6 py-4 hidden sm:table-cell">Date</th>
                    <th scope="col" class="px-2 py-3.5 sm:px-6 sm:py-4 text-right w-24 sm:w-auto">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#1a2332]">
                <?php if (empty($articles)): ?>
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                        Aucune actualité trouvée.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($articles as $art): ?>
                    <tr class="hover:bg-white/[0.02] transition-colors">
                        <!-- Image -->
                        <td class="px-2 py-3 sm:px-6 sm:py-4 align-middle">
                            <?php if ($art['image_path']): ?>
                                <img src="<?= APP_BASE ?>/public/<?= htmlspecialchars($art['image_path']) ?>" class="w-10 h-10 sm:w-12 sm:h-12 rounded object-cover border border-[#1a2332]">
                            <?php else: ?>
                                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded bg-[#0a0f1a] border border-[#1a2332] flex items-center justify-center">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Titre + Infos Mobiles -->
                        <td class="px-2 py-3 sm:px-6 sm:py-4 align-middle">
                            <div class="font-bold text-white text-xs sm:text-sm mb-0.5 line-clamp-2"><?= htmlspecialchars($art['title']) ?></div>
                            <div class="text-[10px] sm:text-xs text-gray-500 truncate max-w-[140px] sm:max-w-xs"><?= htmlspecialchars($art['summary']) ?></div>
                            
                            <!-- Statut & Date uniquement sur mobile -->
                            <div class="flex items-center gap-1.5 mt-1 sm:hidden">
                                <?php if ($art['status'] === 'publie'): ?>
                                    <span class="px-1.5 py-0.5 text-[8px] font-bold rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/25">Publié</span>
                                <?php else: ?>
                                    <span class="px-1.5 py-0.5 text-[8px] font-bold rounded bg-amber-500/10 text-amber-400 border border-amber-500/25">Brouillon</span>
                                <?php endif; ?>
                                <span class="text-[9px] text-gray-500 whitespace-nowrap">
                                    <?= date('d/m/Y', strtotime($art['created_at'])) ?>
                                </span>
                            </div>
                        </td>
                        
                        <!-- Statut (Desktop) -->
                        <td class="px-6 py-4 hidden sm:table-cell align-middle">
                            <?php if ($art['status'] === 'publie'): ?>
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Publié</span>
                            <?php else: ?>
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20">Brouillon</span>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Date (Desktop) -->
                        <td class="px-6 py-4 whitespace-nowrap text-gray-400 hidden sm:table-cell align-middle">
                            <?= date('d/m/Y H:i', strtotime($art['created_at'])) ?>
                        </td>
                        
                        <!-- Actions -->
                        <td class="px-2 py-3 sm:px-6 sm:py-4 text-right align-middle">
                            <div class="flex items-center justify-end gap-1 sm:gap-2">
                                <a href="<?= APP_BASE ?>/actualites/<?= htmlspecialchars($art['slug']) ?>" target="_blank" title="Voir" class="p-1 sm:p-1.5 text-gray-400 hover:text-blue-400 transition-colors bg-[#0a0f1a] rounded border border-[#1a2332]">
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="<?= APP_BASE ?>/admin/actualites/edit/<?= $art['id'] ?>" title="Modifier" class="p-1 sm:p-1.5 text-gray-400 hover:text-amber-400 transition-colors bg-[#0a0f1a] rounded border border-[#1a2332]">
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="<?= APP_BASE ?>/admin/actualites/delete/<?= $art['id'] ?>" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette actualité ?');">
                                    <?= \App\Core\Auth::csrfField() ?>
                                    <button type="submit" title="Supprimer" class="p-1 sm:p-1.5 text-gray-400 hover:text-red-400 transition-colors bg-[#0a0f1a] rounded border border-[#1a2332]">
                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
