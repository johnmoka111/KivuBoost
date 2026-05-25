<?php
use App\Core\Auth;
Auth::requireAdmin();
$basePath = rtrim(APP_BASE, '/');
$flash = \App\Core\Controller::getFlash();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rédiger une actualité — KivuBoost Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        *{font-family:'Inter',sans-serif}
        body{background:#000;color:#fff}
        .nav-blur{backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px)}
        .field-label{display:block;font-size:.8rem;font-weight:600;color:#a1a1aa;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.5rem}
        .field-input{width:100%;background:#18181b;border:1px solid #3f3f46;border-radius:.75rem;padding:.75rem 1rem;color:#fff;font-size:.95rem;outline:none;transition:border-color .2s}
        .field-input:focus{border-color:#10b981;box-shadow:0 0 0 3px rgba(16,185,129,.1)}
        .field-input::placeholder{color:#52525b}
        /* Quill overrides */
        #quill-toolbar{background:#18181b;border:1px solid #3f3f46;border-bottom:1px solid #27272a;border-radius:.75rem .75rem 0 0}
        #quill-toolbar .ql-stroke{stroke:#a1a1aa}
        #quill-toolbar .ql-fill{fill:#a1a1aa}
        #quill-toolbar .ql-picker-label{color:#a1a1aa}
        #quill-toolbar button:hover .ql-stroke,#quill-toolbar .ql-active .ql-stroke{stroke:#10b981!important}
        #quill-toolbar button:hover .ql-fill,#quill-toolbar .ql-active .ql-fill{fill:#10b981!important}
        #quill-toolbar .ql-picker-options{background:#27272a;border:1px solid #3f3f46}
        .ql-editor{background:#0a0a0a;color:#e4e4e7;font-size:1rem;line-height:1.8;min-height:320px;border:1px solid #3f3f46;border-top:none;border-radius:0 0 .75rem .75rem}
        .ql-editor.ql-blank::before{color:#52525b}
        .ql-editor a{color:#10b981}
        .ql-editor blockquote{border-left:3px solid #10b981;color:#71717a}
        .drop-zone{background:#0a0a0a;border:2px dashed #3f3f46;border-radius:.75rem;transition:border-color .25s,background .25s;cursor:pointer}
        .drop-zone:hover,.drop-zone.drag-over{border-color:#10b981;background:rgba(16,185,129,.04)}
        .status-pill{display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 1rem;border-radius:999px;cursor:pointer;border:1px solid #3f3f46;font-size:.85rem;font-weight:500;transition:all .2s}
        .status-pill.active-publie{border-color:#10b981;background:rgba(16,185,129,.12);color:#10b981}
        .status-pill.active-brouillon{border-color:#f59e0b;background:rgba(245,158,11,.1);color:#f59e0b}
        .btn-submit{background:linear-gradient(135deg,#10b981,#059669);color:#000;font-weight:700;border-radius:.875rem;padding:.875rem 2rem;font-size:.95rem;transition:opacity .2s,transform .15s;width:100%;cursor:pointer}
        .btn-submit:hover{opacity:.9;transform:translateY(-1px)}
        .btn-submit:active{transform:translateY(0)}
    </style>
</head>
<body class="min-h-screen">

<!-- NAVBAR -->
<nav class="fixed top-0 left-0 right-0 z-50 nav-blur bg-black/85 border-b border-zinc-800">
    <div class="max-w-5xl mx-auto px-4 h-16 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="<?= $basePath ?>/admin" class="flex items-center gap-2">
                <img src="<?= $basePath ?>/assets/logo.jpeg" alt="KivuBoost"
                     class="w-9 h-9 rounded-full object-cover border border-zinc-700">
                <span class="font-bold text-base text-white tracking-tight">KivuBoost</span>
            </a>
            <span class="text-zinc-600 text-sm hidden sm:inline">/</span>
            <span class="text-zinc-400 text-sm hidden sm:inline font-medium">Rédiger une actualité</span>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= $basePath ?>/actualites"
               target="_blank"
               class="hidden sm:inline-flex items-center gap-1.5 text-zinc-400 hover:text-emerald-400 text-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                Voir le site
            </a>
            <a href="<?= $basePath ?>/admin"
               class="px-3 py-1.5 text-sm text-zinc-300 border border-zinc-700 rounded-lg hover:border-zinc-500 transition-colors">
                ← Admin
            </a>
        </div>
    </div>
</nav>

<main class="pt-20 pb-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6">

        <!-- Flash -->
        <?php if ($flash): ?>
            <div class="mb-6 px-4 py-3 rounded-xl text-sm font-medium
                <?= $flash['type'] === 'success'
                    ? 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-400'
                    : 'bg-red-500/10 border border-red-500/30 text-red-400' ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <!-- Page header -->
        <div class="mb-8">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white mb-1">Nouvelle actualité</h1>
            <p class="text-zinc-500 text-sm">Rédigez et publiez un article sur la vitrine KivuBoost.</p>
        </div>

        <!-- FORM -->
        <form id="news-form"
              action="<?= $basePath ?>/admin/actualites/publier"
              method="POST"
              enctype="multipart/form-data"
              class="space-y-6"
              novalidate>

            <?= \App\Core\Auth::csrfField() ?>

            <!-- Titre -->
            <div>
                <label class="field-label" for="title">Titre de l'article *</label>
                <input id="title" type="text" name="title" required
                       class="field-input" placeholder="Ex: KivuBoost lance le support multi-devises...">
            </div>

            <!-- Résumé -->
            <div>
                <label class="field-label" for="summary">Résumé (affiché dans la grille) *</label>
                <textarea id="summary" name="summary" rows="3" required
                          class="field-input resize-none"
                          placeholder="Courte description accrocheuse pour la carte d'aperçu..."></textarea>
            </div>

            <!-- Photo de couverture -->
            <div>
                <label class="field-label">Photo de couverture (JPG / PNG / WEBP — max 2 Mo)</label>
                <div id="drop-zone"
                     class="drop-zone p-6 text-center"
                     onclick="document.getElementById('cover_image').click()">
                    <div id="drop-placeholder">
                        <svg class="w-10 h-10 text-zinc-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-zinc-400 text-sm font-medium">Cliquez ou déposez une image ici</p>
                        <p class="text-zinc-600 text-xs mt-1">JPG, PNG, WEBP • Maximum 2 Mo</p>
                    </div>
                    <div id="drop-preview" class="hidden">
                        <img id="preview-img" src="" alt="Aperçu" class="max-h-48 mx-auto rounded-xl object-cover">
                        <p id="preview-name" class="text-zinc-400 text-sm mt-2"></p>
                    </div>
                </div>
                <input id="cover_image" type="file" name="cover_image"
                       accept=".jpg,.jpeg,.png,.webp" class="hidden">
            </div>

            <!-- Éditeur Quill -->
            <div>
                <label class="field-label">Contenu complet de l'article *</label>
                <div id="quill-toolbar">
                    <span class="ql-formats">
                        <select class="ql-header">
                            <option value="1">Titre 1</option>
                            <option value="2">Titre 2</option>
                            <option value="3">Titre 3</option>
                            <option selected>Normal</option>
                        </select>
                    </span>
                    <span class="ql-formats">
                        <button class="ql-bold"></button>
                        <button class="ql-italic"></button>
                        <button class="ql-underline"></button>
                        <button class="ql-strike"></button>
                    </span>
                    <span class="ql-formats">
                        <button class="ql-blockquote"></button>
                        <button class="ql-code-block"></button>
                    </span>
                    <span class="ql-formats">
                        <button class="ql-list" value="ordered"></button>
                        <button class="ql-list" value="bullet"></button>
                    </span>
                    <span class="ql-formats">
                        <button class="ql-link"></button>
                        <button class="ql-image"></button>
                    </span>
                    <span class="ql-formats">
                        <button class="ql-clean"></button>
                    </span>
                </div>
                <div id="editor">
                    <p><br></p>
                </div>
                <input type="hidden" name="content" id="hidden_content">
            </div>

            <!-- Statut -->
            <div>
                <label class="field-label">Statut de publication</label>
                <div class="flex gap-3">
                    <label id="pill-publie" class="status-pill active-publie" for="status_publie">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd"/>
                        </svg>
                        Publier maintenant
                        <input type="radio" id="status_publie" name="status" value="publie" checked class="sr-only">
                    </label>
                    <label id="pill-brouillon" class="status-pill" for="status_brouillon">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.536-6.536a2 2 0 012.828 2.828L11.828 15.828a2 2 0 01-1.414.586H9v-1.414a2 2 0 01.586-1.414z"/>
                        </svg>
                        Sauvegarder en brouillon
                        <input type="radio" id="status_brouillon" name="status" value="brouillon" class="sr-only">
                    </label>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit" id="submit-btn" class="btn-submit">
                <span id="btn-label">Publier l'article →</span>
            </button>

        </form>
    </div>
</main>

<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
(function () {
    // --- Quill init ---
    const quill = new Quill('#editor', {
        modules: { toolbar: '#quill-toolbar' },
        theme: 'snow',
        placeholder: 'Rédigez votre article ici…',
    });

    // --- CSRF + content injection on submit ---
    const form = document.getElementById('news-form');
    const hiddenContent = document.getElementById('hidden_content');
    const btnLabel = document.getElementById('btn-label');

    form.addEventListener('submit', function (e) {
        const content = quill.root.innerHTML.trim();
        if (content === '<p><br></p>' || content === '') {
            e.preventDefault();
            alert('Le contenu de l\'article est obligatoire.');
            return;
        }
        hiddenContent.value = content;
        btnLabel.textContent = 'Publication en cours…';
    });

    // --- Image preview ---
    const fileInput = document.getElementById('cover_image');
    const dropZone  = document.getElementById('drop-zone');
    const placeholder = document.getElementById('drop-placeholder');
    const preview   = document.getElementById('drop-preview');
    const previewImg= document.getElementById('preview-img');
    const previewName=document.getElementById('preview-name');

    function showPreview(file) {
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(ev) {
            previewImg.src = ev.target.result;
            previewName.textContent = file.name + ' (' + (file.size / 1024).toFixed(0) + ' Ko)';
            placeholder.classList.add('hidden');
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }

    fileInput.addEventListener('change', function () {
        if (this.files[0]) showPreview(this.files[0]);
    });

    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('drag-over');
    });
    dropZone.addEventListener('dragleave', function() {
        this.classList.remove('drag-over');
    });
    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('drag-over');
        const file = e.dataTransfer.files[0];
        if (file) {
            const dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            showPreview(file);
        }
    });

    // --- Status pills ---
    const pillPublie   = document.getElementById('pill-publie');
    const pillBrouillon= document.getElementById('pill-brouillon');
    const radios = document.querySelectorAll('input[name="status"]');

    radios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            pillPublie.className = 'status-pill' + (this.value === 'publie' ? ' active-publie' : '');
            pillBrouillon.className = 'status-pill' + (this.value === 'brouillon' ? ' active-brouillon' : '');
            document.getElementById('btn-label').textContent =
                this.value === 'publie' ? 'Publier l\'article →' : 'Enregistrer le brouillon →';
        });
    });
})();
</script>

</body>
</html>
