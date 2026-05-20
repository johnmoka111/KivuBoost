<?php
declare(strict_types=1);
session_start();

// --------------------------------------------------------
// CONFIGURATION BASE DE DONNÉES (PDO Strict)
// --------------------------------------------------------
$dbHost = '127.0.0.1';
$dbName = 'kivuboost';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// --------------------------------------------------------
// SECURITE : VÉRIFICATION ADMIN
// --------------------------------------------------------
// À adapter selon votre système d'authentification autonome
$isAdmin = isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'admin';
// Pour le test autonome, on force l'admin (À SUPPRIMER EN PRODUCTION)
$isAdmin = true;

if (!$isAdmin) {
    header('HTTP/1.1 403 Forbidden');
    die("Accès refusé.");
}

// --------------------------------------------------------
// GESTION DES ACTIONS (POST)
// --------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['recharge_id'] ?? 0);

    if ($id > 0 && in_array($action, ['valider', 'rejeter'], true)) {
        try {
            $pdo->beginTransaction();
            
            // Verrouiller la ligne pour éviter les doubles validations (Pessimistic locking)
            $stmt = $pdo->prepare("SELECT user_id, montant, statut FROM flux_recharges WHERE id = ? FOR UPDATE");
            $stmt->execute([$id]);
            $recharge = $stmt->fetch();

            if ($recharge && $recharge['statut'] === 'en_attente') {
                if ($action === 'valider') {
                    // Mettre à jour le statut
                    $stmtUpd = $pdo->prepare("UPDATE flux_recharges SET statut = 'valide' WHERE id = ?");
                    $stmtUpd->execute([$id]);

                    // Créditer le solde (balance)
                    $stmtWallet = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                    $stmtWallet->execute([$recharge['montant'], $recharge['user_id']]);
                    
                    $message = "Dépôt validé et portefeuille crédité.";
                } else {
                    $stmtUpd = $pdo->prepare("UPDATE flux_recharges SET statut = 'refuse' WHERE id = ?");
                    $stmtUpd->execute([$id]);
                    $message = "Dépôt rejeté.";
                }
            } else {
                $error = "Transaction introuvable ou déjà traitée.";
            }
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Erreur serveur : " . $e->getMessage();
        }
    }
}

// --------------------------------------------------------
// RÉCUPÉRATION DES DONNÉES
// --------------------------------------------------------

// 1. Solde fournisseur mondial (Exemple simulé avec cURL)
// À connecter avec l'API réelle
$fournisseurGlobalSolde = "En attente...";
try {
    $stmtProv = $pdo->query("SELECT api_url, api_key FROM providers WHERE status = 1 LIMIT 1");
    $provider = $stmtProv->fetch();
    if ($provider) {
        $ch = curl_init($provider['api_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'key' => $provider['api_key'],
            'action' => 'balance'
        ]));
        $response = curl_exec($ch);
        curl_close($ch);
        $resData = json_decode((string)$response, true);
        if (isset($resData['balance'])) {
            $fournisseurGlobalSolde = '$' . number_format((float)$resData['balance'], 2);
        }
    }
} catch (Exception $e) {}

// 2. Récupérer les recharges en attente
$stmtAttente = $pdo->query("
    SELECT f.*, u.username, u.email 
    FROM flux_recharges f 
    JOIN users u ON f.user_id = u.id 
    WHERE f.statut = 'en_attente' 
    ORDER BY f.date_soumission ASC
");
$recharges = $stmtAttente->fetchAll();

?>
<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🛠️ Régie Admin - KivuBoost</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: {
          extend: {
            colors: {
              cyber: { bg: '#050811', card: '#0d1117', border: '#1a2332' }
            }
          }
        }
      }
    </script>
</head>
<body class="bg-cyber-bg text-gray-200 font-sans min-h-screen p-6">

    <div class="max-w-6xl mx-auto space-y-6">
        
        <div class="flex items-center justify-between border-b border-cyber-border pb-4">
            <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Régie Admin (KivuBoost)
            </h1>
            <div class="bg-cyber-card border border-cyber-border px-4 py-2 rounded-xl flex flex-col items-end">
                <span class="text-xs text-gray-500 uppercase tracking-wider">Réserve Grossiste SMM</span>
                <span class="text-emerald-400 font-bold font-mono text-lg"><?= htmlspecialchars($fournisseurGlobalSolde) ?></span>
            </div>
        </div>

        <?php if (isset($message)): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-xl text-sm">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl text-sm">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="bg-cyber-card border border-cyber-border rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-cyber-border flex items-center justify-between">
                <h2 class="font-bold text-white text-sm uppercase tracking-wider">Dépôts en attente (<?= count($recharges) ?>)</h2>
            </div>
            
            <?php if (empty($recharges)): ?>
                <div class="p-8 text-center text-gray-500 text-sm">Aucun dépôt en attente.</div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[#0a0f1a]/50 text-xs text-gray-500 uppercase tracking-wider border-b border-cyber-border">
                            <tr>
                                <th class="px-5 py-3">Client</th>
                                <th class="px-5 py-3">Réseau & Jeton SMS</th>
                                <th class="px-5 py-3 text-right">Montant</th>
                                <th class="px-5 py-3 text-center">Date</th>
                                <th class="px-5 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-cyber-border">
                            <?php foreach ($recharges as $r): ?>
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-4">
                                    <div class="font-bold text-white"><?= htmlspecialchars($r['username']) ?></div>
                                    <div class="text-xs text-gray-500"><?= htmlspecialchars($r['email']) ?></div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-block bg-white/5 border border-white/10 px-2 py-0.5 rounded text-xs text-gray-300 font-bold mb-1">
                                        <?= htmlspecialchars($r['operateur']) ?>
                                    </span>
                                    <div class="font-mono text-cyan-400 text-xs select-all"><?= htmlspecialchars($r['jeton_sms']) ?></div>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="font-mono font-bold text-emerald-400 text-lg">$<?= number_format((float)$r['montant'], 2) ?></div>
                                </td>
                                <td class="px-5 py-4 text-center text-xs text-gray-500">
                                    <?= date('d/m/Y H:i', strtotime($r['date_soumission'])) ?>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <form method="POST" class="inline-flex gap-2">
                                        <input type="hidden" name="recharge_id" value="<?= $r['id'] ?>">
                                        
                                        <button type="submit" name="action" value="valider" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-500 text-black hover:bg-emerald-400 transition-colors">
                                            Valider le dépôt
                                        </button>
                                        
                                        <button type="submit" name="action" value="rejeter" onclick="return confirm('Confirmer le rejet ?')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-red-500/10 text-red-400 border border-red-500/30 hover:bg-red-500/20 transition-colors">
                                            Rejeter
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
