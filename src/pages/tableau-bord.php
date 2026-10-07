<?php
/**
 * Tableau de bord unifié - Vue d'ensemble des deux sites
 */
$routerPublicUrl = '../../public/index.php';
if (!defined('EPN_APP_ROUTER')) {
    $redirectQuery = $_GET;
    $redirectQuery['page'] = 'tableau-bord';
    header('Location: ' . $routerPublicUrl . '?' . http_build_query($redirectQuery));
    exit;
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/nav-logout.php';

$assetBase = $assetBase ?? 'assets';
$apiBase = $apiBase ?? '../src/api';
$homeAppUrl = $homeAppUrl ?? 'index.php?page=accueil';

$dateAujourdhui = date('Y-m-d');

// Récupérer les postes des deux sites pour aujourd'hui
$stmt = $pdo->prepare("
    SELECT * FROM postes 
    WHERE DATE_UTILISATION = ?
    ORDER BY SITE, NUMERO_POSTE
");
$stmt->execute([$dateAujourdhui]);
$postes = normalizePostesRecords($stmt->fetchAll());

// Initialiser les postes s'ils n'existent pas
$postesBAC = array_filter($postes, function($p) { return $p['SITE'] === 'BAC'; });
$postesMAC = array_filter($postes, function($p) { return $p['SITE'] === 'MAC'; });

$needReload = false;

if (empty($postesBAC)) {
    for ($i = 1; $i <= 8; $i++) {
        $stmtInsert = $pdo->prepare("
            INSERT INTO postes (NUMERO_POSTE, SITE, STATUT, DATE_UTILISATION) 
            VALUES (?, 'BAC', 'Libre', ?)
            ON DUPLICATE KEY UPDATE STATUT = 'Libre'
        ");
        $stmtInsert->execute([$i, $dateAujourdhui]);
    }
    $needReload = true;
}

if (empty($postesMAC)) {
    for ($i = 1; $i <= 8; $i++) {
        $stmtInsert = $pdo->prepare("
            INSERT INTO postes (NUMERO_POSTE, SITE, STATUT, DATE_UTILISATION) 
            VALUES (?, 'MAC', 'Libre', ?)
            ON DUPLICATE KEY UPDATE STATUT = 'Libre'
        ");
        $stmtInsert->execute([$i, $dateAujourdhui]);
    }
    $needReload = true;
}

// Recharger les postes si nécessaire
if ($needReload) {
    $stmt = $pdo->prepare("
        SELECT * FROM postes 
        WHERE DATE_UTILISATION = ?
        ORDER BY SITE, NUMERO_POSTE
    ");
    $stmt->execute([$dateAujourdhui]);
    $postes = normalizePostesRecords($stmt->fetchAll());
}

// Compatibilité schéma live/local : certains environnements ont DESCRIPTION au lieu de NOMS_PRENOMS
$userColumn = getPostesUserColumn($pdo);
$userAliasSql = $userColumn ? "$userColumn AS NOMS_PRENOMS" : 'NULL AS NOMS_PRENOMS';

// Récupérer tous les postes (matériel) triés par ordre alphabétique du nom du poste
$stmt = $pdo->prepare("
    SELECT 
        SITE,
        NUMERO_POSTE,
        STATUT,
        $userAliasSql,
        UTILISATIONS,
        DATE_UTILISATION,
        HEURE_DEBUT,
        HEURE_FIN
    FROM postes 
    WHERE DATE_UTILISATION = ?
    ORDER BY SITE, NUMERO_POSTE
");
$stmt->execute([$dateAujourdhui]);
$materielListe = $stmt->fetchAll();

// Créer le nom du poste et trier alphabétiquement
foreach ($materielListe as &$materiel) {
    $materiel['nom_poste'] = ($materiel['SITE'] === 'BAC' ? 'Poste ' : 'Mac ') . $materiel['NUMERO_POSTE'];
}

// Trier alphabétiquement par nom_poste
usort($materielListe, function($a, $b) {
    return strcmp($a['nom_poste'], $b['nom_poste']);
});
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord unifié - Gestion EPN</title>
    <link rel="stylesheet" href="<?= h($assetBase) ?>/css/style.css">
    <link rel="stylesheet" href="<?= h($assetBase) ?>/css/uiverse-modern.css">
    <style>
        .dashboard-container {
            padding: 24px 20px 40px;
            max-width: 1500px;
            margin: 0 auto;
        }

        .dashboard-header {
            position: relative;
            overflow: hidden;
            background: #1e3a8a;
            color: #fff;
            padding: 24px 28px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.14);
        }

        .dashboard-header .date-display {
            display: inline-flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 12px;
            padding: 8px 12px;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 0.9rem;
            font-weight: 600;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 18px;
            margin-bottom: 26px;
        }

        .stat-card {
            position: relative;
            background: #ffffff;
            padding: 18px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
            text-align: left;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            border-radius: 0;
            background: currentColor;
            opacity: 0.7;
        }

        .stat-card h3 {
            margin: 0 0 10px 0;
            color: #64748b;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .stat-card .stat-value {
            font-size: 2.3rem;
            font-weight: 800;
            color: #0f172a;
            margin: 8px 0 10px;
            letter-spacing: -0.03em;
        }

        .stat-card p {
            margin: 0;
            color: #475569;
            font-weight: 600;
        }

        .stat-card.bac {
            color: #2563eb;
        }

        .stat-card.mac {
            color: #0891b2;
        }

        .stat-card.total {
            color: #059669;
        }

        .sites-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 24px;
            margin-bottom: 26px;
        }

        .site-postes,
        .materiel-section {
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        }

        .site-postes-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.22);
        }

        .site-postes-header h2,
        .materiel-section h2 {
            margin: 0;
            font-size: 1.18rem;
            color: #0f172a;
        }

        .site-postes.bac .site-postes-header h2 {
            color: #1d4ed8;
        }

        .site-postes.mac .site-postes-header h2 {
            color: #0f766e;
        }

        .postes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 14px;
        }

        .poste-card {
            padding: 14px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: border-color 0.15s ease;
            background: #ffffff;
        }

        .poste-card:hover {
            border-color: #94a3b8;
        }

        .poste-card.libre {
            background: #f0fdf4;
            border-color: #86efac;
            border-left: 3px solid #22c55e;
        }

        .poste-card.en-cours {
            background: #fffbeb;
            border-color: #fde68a;
            border-left: 3px solid #f59e0b;
        }

        .poste-card.termine {
            background: #fff1f2;
            border-color: #fecdd3;
            border-left: 3px solid #f43f5e;
        }

        .poste-numero {
            font-weight: 800;
            font-size: 1rem;
            margin-bottom: 6px;
            color: #0f172a;
        }

        .poste-info {
            font-size: 0.84rem;
            color: #475569;
            line-height: 1.5;
        }

        .materiel-section {
            margin-bottom: 28px;
        }

        .materiel-section h2 {
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.22);
        }

        .materiel-table {
            width: 100%;
            border-collapse: collapse;
        }

        .materiel-table thead {
            background: #f8fafc;
        }

        .materiel-table th,
        .materiel-table td {
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .materiel-table th {
            color: #475569;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .materiel-table tbody tr:hover {
            background: rgba(37, 99, 235, 0.04);
        }

        .statut-badge,
        .site-badge {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.78rem;
            font-weight: 700;
            display: inline-block;
        }

        .statut-libre {
            background-color: #dcfce7;
            color: #166534;
        }

        .statut-en-cours {
            background-color: #fef3c7;
            color: #92400e;
        }

        .statut-termine {
            background-color: #ffe4e6;
            color: #9f1239;
        }

        .site-badge {
            color: white;
        }

        .site-badge.bac {
            background-color: #2563eb;
        }

        .site-badge.mac {
            background-color: #0891b2;
        }

        @media (max-width: 768px) {
            .dashboard-header {
                padding: 24px 20px;
                text-align: center;
            }

            .dashboard-header .date-display {
                justify-content: center;
            }

            .site-postes-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php renderRetourTop(h($homeAppUrl), '← Retour à l\'accueil'); ?>
        <div class="dashboard-header">
            <h1>Tableau de bord unifié</h1>
            <div class="date-display">
                <span id="dateJour"><?php echo h(formatDateLongFr(date('Y-m-d'))); ?></span>
                <span id="heureJour" style="margin-left: 20px;"><?php echo date('H:i:s'); ?></span>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="stats-grid" id="statsGrid">
            <div class="stat-card bac">
                <h3>Postes BAC</h3>
                <div class="stat-value" id="postesBAC">-</div>
                <p>En cours: <span id="postesBACEnCours">-</span></p>
            </div>
            <div class="stat-card mac">
                <h3>Postes MAC</h3>
                <div class="stat-value" id="postesMAC">-</div>
                <p>En cours: <span id="postesMACEnCours">-</span></p>
            </div>
            <div class="stat-card total">
                <h3>Fréquentations</h3>
                <div class="stat-value" id="frequentationsTotal">-</div>
                <p>Aujourd'hui</p>
            </div>
            <div class="stat-card total">
                <h3>Inscriptions</h3>
                <div class="stat-value" id="inscriptionsTotal">-</div>
                <p>Aujourd'hui</p>
            </div>
            <div class="stat-card total">
                <h3>Ateliers</h3>
                <div class="stat-value" id="ateliersTotal">-</div>
                <p>Aujourd'hui</p>
            </div>
        </div>

        <!-- Postes par site -->
        <div class="sites-section">
            <div class="site-postes bac">
                <div class="site-postes-header">
                    <h2>EPN SITE BAC</h2>
                    <span class="site-badge bac">BAC</span>
                </div>
                <div class="postes-grid" id="postesGridBAC">
                    <?php 
                    $postesBAC = array_filter($postes, function($p) { return $p['SITE'] === 'BAC'; });
                    foreach ($postesBAC as $poste): 
                    ?>
                    <div class="poste-card <?php echo strtolower(str_replace(' ', '-', $poste['STATUT'])); ?>">
                        <div class="poste-numero">Poste <?php echo h($poste['NUMERO_POSTE']); ?></div>
                        <div class="poste-info">
                            <div><strong>Statut:</strong> <?php echo h($poste['STATUT']); ?></div>
                            <?php if (!empty($poste['NOMS_PRENOMS'])): ?>
                            <div><strong>Usager:</strong> <?php echo h($poste['NOMS_PRENOMS']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="site-postes mac">
                <div class="site-postes-header">
                    <h2>EPN SITE MAC</h2>
                    <span class="site-badge mac">MAC</span>
                </div>
                <div class="postes-grid" id="postesGridMAC">
                    <?php 
                    $postesMAC = array_filter($postes, function($p) { return $p['SITE'] === 'MAC'; });
                    foreach ($postesMAC as $poste): 
                    ?>
                    <div class="poste-card <?php echo strtolower(str_replace(' ', '-', $poste['STATUT'])); ?>">
                        <div class="poste-numero">Mac <?php echo h($poste['NUMERO_POSTE']); ?></div>
                        <div class="poste-info">
                            <div><strong>Statut:</strong> <?php echo h($poste['STATUT']); ?></div>
                            <?php if (!empty($poste['NOMS_PRENOMS'])): ?>
                            <div><strong>Usager:</strong> <?php echo h($poste['NOMS_PRENOMS']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Liste du matériel triée alphabétiquement -->
        <div class="materiel-section">
            <h2>Liste du matériel par ordre alphabétique</h2>
            <table class="materiel-table">
                <thead>
                    <tr>
                        <th>Nom du poste</th>
                        <th>Site</th>
                        <th>Statut</th>
                        <th>Usager</th>
                        <th>Utilisation</th>
                        <th>Heure début</th>
                        <th>Heure fin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($materielListe)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: #666; padding: 20px;">
                            Aucun matériel enregistré pour aujourd'hui.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($materielListe as $materiel): ?>
                        <tr>
                            <td><strong><?php echo h($materiel['nom_poste']); ?></strong></td>
                            <td>
                                <span class="site-badge <?php echo strtolower($materiel['SITE']); ?>">
                                    <?php echo h($materiel['SITE']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="statut-badge statut-<?php echo strtolower(str_replace(' ', '-', $materiel['STATUT'])); ?>">
                                    <?php echo h($materiel['STATUT']); ?>
                                </span>
                            </td>
                            <td><?php echo !empty($materiel['NOMS_PRENOMS']) ? h($materiel['NOMS_PRENOMS']) : '-'; ?></td>
                            <td><?php echo !empty($materiel['UTILISATIONS']) ? h($materiel['UTILISATIONS']) : '-'; ?></td>
                            <td><?php echo !empty($materiel['HEURE_DEBUT']) ? substr($materiel['HEURE_DEBUT'], 0, 5) : '-'; ?></td>
                            <td><?php echo !empty($materiel['HEURE_FIN']) ? substr($materiel['HEURE_FIN'], 0, 5) : '-'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php renderLogoutFooter(h($homeAppUrl), $apiBase); ?>
    </div>

    <script>
        // Mise à jour de l'heure en temps réel
        function updateTime() {
            const now = new Date();
            document.getElementById('heureJour').textContent = 
                now.toLocaleTimeString('fr-FR');
        }
        
        setInterval(updateTime, 1000);
        updateTime();
        
        // Charger les statistiques
        function loadStats() {
            fetch('<?= h($apiBase) ?>/statistiques.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Postes BAC
                        document.getElementById('postesBAC').textContent = 
                            data.postes.BAC.total || 0;
                        document.getElementById('postesBACEnCours').textContent = 
                            data.postes.BAC.en_cours || 0;
                        
                        // Postes MAC
                        document.getElementById('postesMAC').textContent = 
                            data.postes.MAC.total || 0;
                        document.getElementById('postesMACEnCours').textContent = 
                            data.postes.MAC.en_cours || 0;
                        
                        // Fréquentations
                        document.getElementById('frequentationsTotal').textContent = 
                            data.frequentations.total || 0;
                        
                        // Inscriptions
                        document.getElementById('inscriptionsTotal').textContent = 
                            data.inscriptions.total || 0;
                        
                        // Ateliers
                        document.getElementById('ateliersTotal').textContent = 
                            data.ateliers.total || 0;
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des statistiques:', error);
                });
        }
        
        // Charger les statistiques au chargement de la page et toutes les 30 secondes
        loadStats();
        setInterval(loadStats, 30000);
        
        // Rafraîchissement automatique des postes toutes les 5 secondes
        setInterval(() => {
            location.reload();
        }, 60000); // Rafraîchir toutes les minutes

    </script>
</body>
</html>
