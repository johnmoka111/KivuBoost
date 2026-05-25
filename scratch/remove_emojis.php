<?php
/**
 * Script de nettoyage d'emojis dans tout le projet KivuBoost
 * Remplace chaque emoji par son équivalent textuel ou le supprime
 */

$replacements = [
    // Interface admin — messages & toasts
    '✅ ' => '',
    '✅' => '',
    '🔒 ' => '',
    '🔒' => '',
    '❌ ' => '',
    '❌' => '',
    '⚠️ ' => '',
    '⚠️' => '',
    '🗑️ ' => '',
    '🗑️' => '',
    '🔄' => '',
    '💳' => '',
    '📦' => '',
    '📋' => '',
    '📊' => '',
    '📈' => '',
    '📉' => '',
    '💰' => '',
    '💵' => '',
    '💡' => '',
    '🔑' => '',
    '🛒' => '',
    '🌐' => '',
    '🚀' => '',
    '⭐' => '',
    '🎯' => '',
    '🔧' => '',
    '⚙️' => '',
    '🏠' => '',
    '👤' => '',
    '👥' => '',
    '📩' => '',
    '📤' => '',
    '📥' => '',
    '🔔' => '',
    '🔕' => '',
    '✔️' => '',
    '✔' => '',
    '✖️' => '',
    '✖' => '',
    '➕' => '',
    '➖' => '',
    '🔍' => '',
    '🔎' => '',
    '🔗' => '',
    '📌' => '',
    '🏷️' => '',
    '🎁' => '',
    '🕐' => '',
    '🕒' => '',
    '💬' => '',
    '🛡️' => '',
    '🛡' => '',
    '⚡' => '',
    '🔥' => '',
    '👋' => '',
    '🎉' => '',
    '🎊' => '',
    '🏆' => '',
    '👑' => '',
    '💎' => '',
    '🌟' => '',
    '✨' => '',
    '🆕' => '',
    '🔁' => '',
    '↩️' => '',
    '↩' => '',
    '🗂️' => '',
    '📁' => '',
    '📝' => '',
    '🖊️' => '',
    '✏️' => '',
    '🖇️' => '',
    '📎' => '',
    '🖨️' => '',
    '🖥️' => '',
    '💻' => '',
    '📱' => '',
    '⌨️' => '',
    '🖱️' => '',
    // Nettoyage double espace résiduel après suppression
];

$dir = __DIR__ . '/../app';
$rootFiles = [
    __DIR__ . '/../index.php',
    __DIR__ . '/../admin.php',
];

function processFile(string $path, array $replacements): bool {
    $content = file_get_contents($path);
    if ($content === false) return false;
    $newContent = str_replace(array_keys($replacements), array_values($replacements), $content);
    // Nettoyer les doubles espaces introduits par la suppression d'emojis en début de chaîne JS
    $newContent = preg_replace("/'  /", "' ", $newContent);
    $newContent = preg_replace('/`  /', '` ', $newContent);
    if ($newContent !== $content) {
        file_put_contents($path, $newContent);
        return true;
    }
    return false;
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
);

$changed = [];

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') continue;
    if (processFile($file->getPathname(), $replacements)) {
        $changed[] = $file->getPathname();
    }
}

foreach ($rootFiles as $f) {
    if (file_exists($f) && processFile($f, $replacements)) {
        $changed[] = $f;
    }
}

echo "Fichiers modifiés (" . count($changed) . ") :\n";
foreach ($changed as $p) {
    echo "  - $p\n";
}
echo "\nTerminé.\n";
