<?php
/**
 * Page statistiques, requêtes et export CSV
 */
$routerPublicUrl = '../../public/index.php';
if (!defined('EPN_APP_ROUTER')) {
    $redirectQuery = $_GET;
    $redirectQuery['page'] = 'statistiques';
    header('Location: ' . $routerPublicUrl . '?' . http_build_query($redirectQuery));
    exit;
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/nav-logout.php';

$assetBase = $assetBase ?? 'assets';
$apiBase = $apiBase ?? '../src/api';
$homeAppUrl = $homeAppUrl ?? 'index.php?page=accueil';
$accueilSiteUrl = $accueilSiteUrl ?? 'index.php?page=accueil-site';

$site = strtoupper($_GET['site'] ?? 'GLOBAL');
if (!in_array($site, ['GLOBAL', 'BAC', 'MAC'], true)) {
    $site = 'GLOBAL';
}

$siteConfig = [
    'GLOBAL' => ['nom' => 'Tous les sites', 'couleur' => '#0f172a', 'couleurFond' => '#e2e8f0', 'couleurTexte' => '#0f172a'],
    'BAC' => ['nom' => 'EPN SITE BAC', 'couleur' => '#2563eb', 'couleurFond' => '#dbeafe', 'couleurTexte' => '#1d4ed8'],
    'MAC' => ['nom' => 'EPN SITE MAC', 'couleur' => '#0891b2', 'couleurFond' => '#cffafe', 'couleurTexte' => '#0f766e'],
];

$configStats = $siteConfig[$site];
$defaultStart = date('Y-m-d', strtotime('-24 months'));
$defaultEnd = date('Y-m-d');
$backUrl = $site === 'GLOBAL' ? $homeAppUrl : $accueilSiteUrl . '&site=' . urlencode($site);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques et exports - <?php echo h(SITE_NAME); ?></title>
    <link rel="stylesheet" href="<?= h($assetBase) ?>/css/style.css">
    <link rel="stylesheet" href="<?= h($assetBase) ?>/css/uiverse-modern.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --site-color: <?php echo $configStats['couleur']; ?>;
            --site-bg: <?php echo $configStats['couleurFond']; ?>;
            --site-text: <?php echo $configStats['couleurTexte']; ?>;
        }

        .stats-shell {
            max-width: 1400px;
            margin: 0 auto;
            padding: 24px 20px 40px;
        }

        .stats-panel,
        .summary-card,
        .chart-card,
        .insights-card,
        .results-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        }

        .stats-panel {
            padding: 22px;
            margin-bottom: 22px;
        }

        .stats-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 18px;
        }

        .stats-panel-header h2 {
            margin: 4px 0 0;
            color: #0f172a;
            font-size: 1.3rem;
        }

        .stats-panel-header p {
            color: #64748b;
            margin: 0;
        }

        .stats-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .stats-actions button {
            border: none;
            border-radius: 6px;
            padding: 9px 14px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.15s ease;
        }

        .stats-actions button:hover {
            opacity: 0.88;
        }

        .btn-analytics-primary {
            background: var(--site-color);
            color: #fff;
        }

        .btn-analytics-secondary {
            background: #eef2ff;
            color: #1e3a8a;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
        }

        .filter-field label {
            display: block;
            margin-bottom: 6px;
            font-weight: 700;
            color: #334155;
        }

        .filter-field select,
        .filter-field input {
            width: 100%;
            padding: 9px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 0.94rem;
            background: #fff;
        }

        .filter-field select:focus,
        .filter-field input:focus {
            outline: none;
            border-color: var(--site-color);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
        }

        .query-status {
            margin-top: 12px;
            color: #475569;
            font-size: 0.95rem;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 22px;
        }

        .summary-card {
            padding: 18px;
        }

        .summary-card h3 {
            margin: 0 0 10px;
            font-size: 0.8rem;
            color: #64748b;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .summary-card .value {
            font-size: 2rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.03em;
        }

        .summary-card p {
            margin-top: 8px;
            color: #475569;
        }

        .analytics-grid {
            display: grid;
            grid-template-columns: 1.7fr 1fr;
            gap: 18px;
            margin-bottom: 22px;
        }

        .chart-card,
        .insights-card,
        .results-card {
            padding: 20px;
        }

        .card-head {
            margin-bottom: 14px;
        }

        .card-head h3 {
            margin: 0 0 6px;
            color: #0f172a;
            font-size: 1.15rem;
        }

        .card-head p {
            margin: 0;
            color: #64748b;
        }

        .chart-wrap {
            position: relative;
            min-height: 320px;
        }

        .insights-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .insights-list li {
            padding: 10px 12px;
            border-radius: 6px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #334155;
        }

        .results-card .table-container {
            margin-top: 0;
        }

        .results-meta {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 14px;
            color: #475569;
        }

        .empty-placeholder {
            text-align: center;
            padding: 24px;
            border-radius: 8px;
            background: #f8fafc;
            color: #64748b;
            border: 1px dashed #cbd5e1;
        }

        @media (max-width: 980px) {
            .analytics-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .stats-panel-header {
                flex-direction: column;
            }

            .stats-actions {
                width: 100%;
            }

            .stats-actions button {
                flex: 1 1 auto;
            }
        }
    </style>
</head>
<body>
    <div class="container-site" data-site="<?php echo $site; ?>">
        <?php renderRetourTop(h($backUrl)); ?>
        <header class="header-site" style="background-color: <?php echo $configStats['couleur']; ?>;">
            <h1>Statistiques, requêtes et export</h1>
            <div class="date-display">
                <span><?php echo h($configStats['nom']); ?></span>
                <span><?php echo h(formatDateLongFr(date('Y-m-d'))); ?></span>
            </div>
        </header>

        <div class="stats-shell">
            <section class="stats-panel">
                <div class="stats-panel-header">
                    <div>
                        <p>Sélecteur de requête dynamique</p>
                        <h2>Interrogez la base en direct sur les fréquentations, les profils et les provenances</h2>
                    </div>
                    <div class="stats-actions">
                        <button type="button" id="btnActualiser" class="btn-analytics-primary">Actualiser</button>
                        <button type="button" id="btnExporter" class="btn-analytics-secondary">Exporter CSV</button>
                    </div>
                </div>

                <div class="filters-grid">
                    <div class="filter-field">
                        <label for="siteFilter">Site</label>
                        <select id="siteFilter">
                            <option value="GLOBAL" <?php echo $site === 'GLOBAL' ? 'selected' : ''; ?>>Tous les sites</option>
                            <option value="BAC" <?php echo $site === 'BAC' ? 'selected' : ''; ?>>BAC</option>
                            <option value="MAC" <?php echo $site === 'MAC' ? 'selected' : ''; ?>>MAC</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label for="moduleFilter">Module</label>
                        <select id="moduleFilter">
                            <option value="frequentations">Fréquentations</option>
                            <option value="inscriptions">Inscriptions</option>
                            <option value="ateliers">Ateliers</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label for="queryFilter">Requête</label>
                        <select id="queryFilter"></select>
                    </div>
                    <div class="filter-field">
                        <label for="dateDebut">Date de début</label>
                        <input type="date" id="dateDebut" value="<?php echo $defaultStart; ?>">
                    </div>
                    <div class="filter-field">
                        <label for="dateFin">Date de fin</label>
                        <input type="date" id="dateFin" value="<?php echo $defaultEnd; ?>">
                    </div>
                </div>

                <p id="queryStatus" class="query-status" aria-live="polite">Chargement des statistiques…</p>
            </section>

            <section class="summary-grid">
                <article class="summary-card">
                    <h3>Enregistrements</h3>
                    <div class="value" id="summaryTotal">0</div>
                    <p>Données analysées</p>
                </article>
                <article class="summary-card">
                    <h3>Point dominant</h3>
                    <div class="value" id="summaryTop">-</div>
                    <p>Catégorie principale</p>
                </article>
                <article class="summary-card">
                    <h3>Période</h3>
                    <div class="value" id="summaryPeriod">-</div>
                    <p>Filtre actif</p>
                </article>
                <article class="summary-card">
                    <h3>Mise à jour</h3>
                    <div class="value" id="summaryTime">-</div>
                    <p>Dernier rafraîchissement</p>
                </article>
            </section>

            <section class="analytics-grid">
                <article class="chart-card">
                    <div class="card-head">
                        <h3 id="chartTitle">Analyse en cours</h3>
                        <p id="chartSubtitle">Le graphique évolue selon le sélecteur de requête.</p>
                    </div>
                    <div class="chart-wrap">
                        <canvas id="statsChart"></canvas>
                        <div id="chartEmptyState" class="empty-placeholder" style="display: none; margin-top: 12px;">Aucune donnée disponible pour les filtres sélectionnés.</div>
                    </div>
                </article>

                <article class="insights-card">
                    <div class="card-head">
                        <h3>Lecture rapide</h3>
                        <p>Repères immédiats pour interpréter les résultats.</p>
                    </div>
                    <ul id="insightsList" class="insights-list">
                        <li>Chargement des points clés…</li>
                    </ul>
                </article>
            </section>

            <section class="results-card">
                <div class="results-meta">
                    <strong>Résultats de la requête</strong>
                    <span id="resultsCount">0 ligne</span>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Libellé</th>
                                <th>Volume</th>
                                <th>Part</th>
                            </tr>
                        </thead>
                        <tbody id="resultsBody">
                            <tr>
                                <td colspan="3" class="no-data">Chargement…</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <?php renderLogoutFooter(h($backUrl), $apiBase); ?>
    </div>

    <script>
        const siteFilter = document.getElementById('siteFilter');
        const moduleFilter = document.getElementById('moduleFilter');
        const queryFilter = document.getElementById('queryFilter');
        const dateDebut = document.getElementById('dateDebut');
        const dateFin = document.getElementById('dateFin');
        const queryStatus = document.getElementById('queryStatus');
        const resultsBody = document.getElementById('resultsBody');
        const resultsCount = document.getElementById('resultsCount');
        const insightsList = document.getElementById('insightsList');
        const summaryTotal = document.getElementById('summaryTotal');
        const summaryTop = document.getElementById('summaryTop');
        const summaryPeriod = document.getElementById('summaryPeriod');
        const summaryTime = document.getElementById('summaryTime');
        const chartTitle = document.getElementById('chartTitle');
        const chartSubtitle = document.getElementById('chartSubtitle');
        const chartEmptyState = document.getElementById('chartEmptyState');

        const queryOptions = {
            frequentations: {
                sexe: 'Homme / Femme',
                tranche_age: 'Tranche d\'âge',
                provenance: 'Provenance',
                utilisation: 'Type d\'utilisation',
                referent: 'Référent',
                site: 'Répartition par site',
                evolution: 'Évolution journalière'
            },
            inscriptions: {
                sexe: 'Homme / Femme',
                tranche_age: 'Tranche d\'âge',
                provenance: 'Provenance',
                profil: 'Profil',
                situation: 'Situation',
                referent: 'Référent',
                site: 'Répartition par site',
                evolution: 'Évolution journalière'
            },
            ateliers: {
                sexe: 'Homme / Femme',
                tranche_age: 'Tranche d\'âge',
                provenance: 'Provenance',
                atelier: 'Atelier choisi',
                referent: 'Référent / formateur',
                site: 'Répartition par site',
                evolution: 'Évolution journalière'
            }
        };

        let chartInstance = null;

        function updateQueryOptions() {
            const module = moduleFilter.value;
            const options = queryOptions[module] || {};
            const currentValue = queryFilter.value;

            queryFilter.innerHTML = '';
            Object.entries(options).forEach(([value, label]) => {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = label;
                queryFilter.appendChild(option);
            });

            if (options[currentValue]) {
                queryFilter.value = currentValue;
            }
        }

        function buildParams(action) {
            const params = new URLSearchParams({
                action,
                site: siteFilter.value,
                module: moduleFilter.value,
                query: queryFilter.value,
                date_debut: dateDebut.value,
                date_fin: dateFin.value
            });
            return params.toString();
        }

        function renderSummary(data) {
            const totalRecords = data.summary?.total_records ?? 0;
            const topItem = data.table && data.table.length ? data.table[0].label : '-';
            const topDisplay = topItem.length > 14 ? topItem.slice(0, 14) + '…' : topItem;

            summaryTotal.textContent = totalRecords;
            summaryTop.textContent = topDisplay;
            summaryPeriod.textContent = data.summary?.period_label ?? '-';
            summaryTime.textContent = data.summary?.updated_at ?? '-';
        }

        function renderInsights(data) {
            const insights = Array.isArray(data.insights) && data.insights.length
                ? data.insights
                : ['Aucun indicateur disponible pour la sélection courante.'];

            insightsList.innerHTML = insights.map(text => `<li>${text}</li>`).join('');
        }

        function renderTable(rows) {
            if (!rows || !rows.length) {
                resultsBody.innerHTML = '<tr><td colspan="3" class="no-data">Aucune donnée trouvée pour cette requête.</td></tr>';
                resultsCount.textContent = '0 ligne';
                return;
            }

            resultsBody.innerHTML = rows.map(row => `
                <tr>
                    <td>${row.label}</td>
                    <td><strong>${row.value}</strong></td>
                    <td>${row.percentage} %</td>
                </tr>
            `).join('');

            resultsCount.textContent = `${rows.length} ligne${rows.length > 1 ? 's' : ''}`;
        }

        function renderChart(data) {
            const canvas = document.getElementById('statsChart');
            const labels = data.chart?.labels || [];
            const values = data.chart?.values || [];

            if (chartInstance) {
                chartInstance.destroy();
            }

            if (!labels.length || typeof Chart === 'undefined') {
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                chartEmptyState.style.display = 'block';
                return;
            }

            chartEmptyState.style.display = 'none';

            const palette = ['#2563eb', '#0891b2', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#14b8a6', '#64748b', '#0ea5e9', '#22c55e'];
            const chartType = data.chart?.type || 'bar';

            chartInstance = new Chart(canvas, {
                type: chartType,
                data: {
                    labels,
                    datasets: [{
                        label: data.chart?.title || 'Analyse',
                        data: values,
                        backgroundColor: chartType === 'line'
                            ? 'rgba(37, 99, 235, 0.18)'
                            : labels.map((_, index) => palette[index % palette.length]),
                        borderColor: chartType === 'line'
                            ? '#2563eb'
                            : labels.map((_, index) => palette[index % palette.length]),
                        borderWidth: 2,
                        fill: chartType === 'line',
                        tension: 0.28
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: chartType !== 'bar',
                            position: 'bottom'
                        }
                    },
                    scales: chartType === 'doughnut' ? {} : {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }

        function loadAnalytics() {
            queryStatus.textContent = 'Chargement en cours…';
            chartTitle.textContent = 'Analyse en cours';
            chartSubtitle.textContent = 'Mise à jour des indicateurs…';

            fetch('<?= h($apiBase) ?>/statistiques.php?' + buildParams('query'))
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Impossible de charger les données.');
                    }

                    chartTitle.textContent = data.chart?.title || 'Analyse';
                    chartSubtitle.textContent = data.chart?.subtitle || 'Résultats mis à jour automatiquement.';
                    queryStatus.textContent = data.message || 'Analyse chargée.';

                    renderSummary(data);
                    renderInsights(data);
                    renderTable(data.table || []);
                    renderChart(data);
                })
                .catch(error => {
                    queryStatus.textContent = error.message || 'Erreur de chargement.';
                    resultsBody.innerHTML = '<tr><td colspan="3" class="no-data">Impossible de récupérer les statistiques.</td></tr>';
                    insightsList.innerHTML = '<li>Vérifiez la connexion ou les filtres sélectionnés.</li>';
                });
        }

        document.getElementById('btnActualiser').addEventListener('click', loadAnalytics);
        document.getElementById('btnExporter').addEventListener('click', () => {
            window.open('<?= h($apiBase) ?>/statistiques.php?' + buildParams('export'), '_blank');
        });

        [siteFilter, moduleFilter, queryFilter, dateDebut, dateFin].forEach(element => {
            element.addEventListener('change', () => {
                if (element === moduleFilter) {
                    updateQueryOptions();
                }
                loadAnalytics();
            });
        });

        updateQueryOptions();
        loadAnalytics();

    </script>
</body>
</html>
