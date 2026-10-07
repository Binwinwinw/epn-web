<?php
/**
 * Gestion des ateliers
 * Équivalent à LATELIER1.vb / LATELIER2.vb
 */
$routerPublicUrl = '../../public/index.php';
if (!defined('EPN_APP_ROUTER')) {
    $redirectQuery = $_GET;
    $redirectQuery['page'] = 'ateliers';
    header('Location: ' . $routerPublicUrl . '?' . http_build_query($redirectQuery));
    exit;
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/nav-logout.php';

$assetBase = $assetBase ?? 'assets';
$apiBase = $apiBase ?? '../src/api';
$accueilSiteUrl = $accueilSiteUrl ?? 'index.php?page=accueil-site';
$ateliersUrl = $ateliersUrl ?? 'index.php?page=ateliers';

$site = isset($_GET['site']) ? strtoupper($_GET['site']) : 'BAC';
if (!in_array($site, ['BAC', 'MAC'])) {
    $site = 'BAC';
}

$tableAteliers = $site === 'BAC' ? 'ateliers1' : 'ateliers2';
$siteConfig = [
    'BAC' => ['nom' => 'EPN SITE BAC', 'couleur' => '#2563eb', 'couleurFond' => '#dbeafe'],
    'MAC' => ['nom' => 'EPN SITE MAC', 'couleur' => '#0891b2', 'couleurFond' => '#cffafe']
];
$config = $siteConfig[$site];

// Recherche
$recherche = isset($_GET['recherche']) ? $_GET['recherche'] : '';
$recherchePeriode = isset($_GET['periode']) ? $_GET['periode'] : '';
$ateliers = [];

if (!empty($recherche)) {
    $stmt = $pdo->prepare("
        SELECT * FROM $tableAteliers 
        WHERE DATE_ATELIER LIKE ? OR NOMS_PRENOMS LIKE ?
        ORDER BY DATE_ATELIER DESC
        LIMIT 100
    ");
    $searchTerm = "%$recherche%";
    $stmt->execute([$searchTerm, $searchTerm]);
    $ateliers = $stmt->fetchAll();
} elseif (!empty($recherchePeriode)) {
    $stmt = $pdo->prepare("
        SELECT * FROM $tableAteliers 
        WHERE PERIODE_ATELIER LIKE ?
        ORDER BY DATE_ATELIER DESC
        LIMIT 100
    ");
    $stmt->execute(["%$recherchePeriode%"]);
    $ateliers = $stmt->fetchAll();
} else {
    $dateAujourdhui = date('d/m/Y');
    $stmt = $pdo->prepare("
        SELECT * FROM $tableAteliers 
        WHERE DATE_ATELIER LIKE ?
        ORDER BY DATE_ATELIER DESC
        LIMIT 100
    ");
    $stmt->execute(["%$dateAujourdhui%"]);
    $ateliers = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ateliers - <?php echo $config['nom']; ?></title>
    <link rel="stylesheet" href="<?= h($assetBase) ?>/css/style.css">
    <link rel="stylesheet" href="<?= h($assetBase) ?>/css/uiverse-modern.css">
    <style>
        :root {
            --site-color: <?php echo $config['couleur']; ?>;
            --site-bg: <?php echo $config['couleurFond']; ?>;
        }
    </style>
</head>
<body>
    <div class="container-site">
        <?php renderRetourTop(h($accueilSiteUrl) . '&site=' . $site); ?>
        <header class="header-site" style="background-color: <?php echo $config['couleur']; ?>;">
            <h1>Ateliers - <?php echo $config['nom']; ?></h1>
            <div class="date-display">
                <span id="dateJour"><?php echo h(formatDateLongFr(date('Y-m-d'))); ?></span>
            </div>
        </header>

        <div class="content-page">
            <div class="search-bar">
                <input type="text" id="recherche" placeholder="Rechercher par date ou nom" 
                       value="<?php echo h($recherche); ?>" 
                       onkeyup="if(event.key==='Enter') rechercher()">
                <input type="text" id="periode" placeholder="Rechercher par période (ex: Jan 2024)" 
                       value="<?php echo h($recherchePeriode); ?>" 
                       onkeyup="if(event.key==='Enter') rechercherPeriode()">
                <button onclick="rechercher()" class="btn-search">Rechercher</button>
                <button onclick="rechercherPeriode()" class="btn-search">Par période</button>
                <button onclick="document.getElementById('recherche').value=''; document.getElementById('periode').value=''; rechercher();" class="btn-clear">Effacer</button>
                <button onclick="document.getElementById('recherche').value='<?php echo date('d/m/Y'); ?>'; rechercher();" class="btn-today">Aujourd'hui</button>
            </div>

            <div class="action-bar">
                <button onclick="nouvelAtelier()" class="btn-primary" style="background-color: <?php echo $config['couleur']; ?>;">
                    Nouvel atelier
                </button>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Période</th>
                            <th>Usager</th>
                            <th>Atelier</th>
                            <th>Formateur</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ateliers)): ?>
                        <tr>
                            <td colspan="7" class="no-data">Aucun atelier trouvé</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($ateliers as $atelier): ?>
                        <tr>
                            <td><?php echo h($atelier['DATE_ATELIER']); ?></td>
                            <td><?php echo h($atelier['PERIODE_ATELIER']); ?></td>
                            <td><strong><?php echo h($atelier['CIVILITE'] . ' ' . $atelier['NOMS_PRENOMS']); ?></strong></td>
                            <td><?php echo h($atelier['ATELIER_CHOISI']); ?></td>
                            <td><?php echo h($atelier['FORMATEUR']); ?></td>
                            <td>
                                <span class="statut-badge statut-<?php echo strtolower($atelier['STATUT'] ?? 'public'); ?>">
                                    <?php echo h($atelier['STATUT'] ?? 'Public'); ?>
                                </span>
                            </td>
                            <td>
                                <button onclick="modifierAtelier(<?php echo $atelier['NUM_ATELIER']; ?>)" 
                                        class="btn-small">Modifier</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php renderLogoutFooter(h($accueilSiteUrl) . '&site=' . $site, $apiBase); ?>
    </div>

    <!-- Modal atelier -->
    <div id="modalAtelier" class="modal" style="display: none;">
        <div class="modal-content modal-large">
            <span class="close" onclick="fermerModal()">&times;</span>
            <h2>Nouvel atelier</h2>
            <form id="formAtelier" onsubmit="sauvegarderAtelier(event)">
                <input type="hidden" id="num_atelier" name="num_atelier">
                <input type="hidden" id="site" name="site" value="<?php echo $site; ?>">
                <input type="hidden" name="csrf_token" value="<?= h(getCSRFToken()); ?>">
                
                <div class="form-group">
                    <label>Date de l'atelier *</label>
                    <input type="text" id="date_atelier" name="date_atelier" 
                           value="<?php echo date('d/m/Y'); ?>" required>
                </div>

                <div class="form-group">
                    <label>Période</label>
                    <div class="input-with-button">
                        <input type="text" id="periode_atelier" name="periode_atelier" placeholder="Ex: Jan 2024">
                        <button type="button" onclick="genererPeriode()" class="btn-select">Générer</button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Espace *</label>
                    <input type="text" id="espace_atelier" name="espace_atelier" 
                           value="EPN site <?php echo $site; ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Référent (Agent) *</label>
                    <div class="input-with-button">
                        <input type="text" id="referent" name="referent" required readonly>
                        <button type="button" onclick="selectionnerAgent()" class="btn-select">Sélectionner</button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Usager *</label>
                    <div class="input-with-button">
                        <input type="text" id="noms_prenoms" name="noms_prenoms" required readonly>
                        <input type="hidden" id="civilite" name="civilite">
                        <input type="hidden" id="code_ville" name="code_ville">
                        <button type="button" onclick="rechercherUsager()" class="btn-select">Rechercher</button>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Téléphone portable</label>
                        <input type="text" id="telephone_portable" name="telephone_portable">
                    </div>
                    <div class="form-group">
                        <label>Téléphone fixe</label>
                        <input type="text" id="telephone_fixe" name="telephone_fixe">
                    </div>
                </div>

                <div class="form-group">
                    <label>Atelier choisi *</label>
                    <div class="input-with-button">
                        <input type="text" id="atelier_choisi" name="atelier_choisi" required readonly>
                        <button type="button" onclick="selectionnerAtelier()" class="btn-select">Sélectionner</button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Formateur</label>
                    <div class="input-with-button">
                        <input type="text" id="formateur" name="formateur" readonly>
                        <button type="button" onclick="selectionnerFormateur()" class="btn-select">Sélectionner</button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Situation de formation</label>
                    <select id="situation_formation" name="situation_formation">
                        <option value="">--</option>
                        <option value="En cours">En cours</option>
                        <option value="Terminé">Terminé</option>
                        <option value="Abandonné">Abandonné</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Statut *</label>
                    <select id="statut" name="statut" required>
                        <option value="Public">Public</option>
                        <option value="Mairie">Mairie</option>
                        <option value="Association">Association</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Observations</label>
                    <textarea id="observations" name="observations" rows="3"></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary" style="background-color: <?php echo $config['couleur']; ?>;">
                        Enregistrer
                    </button>
                    <button type="button" onclick="fermerModal()" class="btn-secondary">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modals -->
    <div id="modalUsager" class="modal" style="display: none;">
        <div class="modal-content modal-medium">
            <span class="close" onclick="fermerModalUsager()">&times;</span>
            <h2>Rechercher un usager</h2>
            <div class="search-bar">
                <input type="text" id="rechercheUsager" placeholder="Nom ou prénom" 
                       onkeyup="rechercherUsagers()">
            </div>
            <div id="listeUsagers" class="liste-usagers"></div>
        </div>
    </div>

    <div id="modalAtelierChoix" class="modal" style="display: none;">
        <div class="modal-content modal-medium">
            <span class="close" onclick="fermerModalAtelierChoix()">&times;</span>
            <h2>Sélectionner un atelier</h2>
            <div id="listeAteliers" class="liste-ateliers"></div>
        </div>
    </div>

    <div id="modalAgent" class="modal" style="display: none;">
        <div class="modal-content modal-small">
            <span class="close" onclick="fermerModalAgent()">&times;</span>
            <h2>Sélectionner un agent</h2>
            <div id="listeAgents" class="liste-agents"></div>
        </div>
    </div>

    <script>
        function rechercher() {
            const recherche = document.getElementById('recherche').value;
            window.location.href = '<?= h($ateliersUrl) ?>&site=<?php echo $site; ?>&recherche=' + encodeURIComponent(recherche);
        }

        function rechercherPeriode() {
            const periode = document.getElementById('periode').value;
            window.location.href = '<?= h($ateliersUrl) ?>&site=<?php echo $site; ?>&periode=' + encodeURIComponent(periode);
        }

        function genererPeriode() {
            const date = document.getElementById('date_atelier').value;
            if (date) {
                // Extraire le mois et l'année (format: JJ/MM/AAAA)
                const parts = date.split('/');
                if (parts.length === 3) {
                    const mois = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
                    const moisNum = parseInt(parts[1]) - 1;
                    document.getElementById('periode_atelier').value = mois[moisNum] + ' ' + parts[2];
                }
            }
        }

        function nouvelAtelier() {
            document.getElementById('formAtelier').reset();
            document.getElementById('num_atelier').value = '';
            document.getElementById('date_atelier').value = '<?php echo date('d/m/Y'); ?>';
            document.getElementById('espace_atelier').value = 'EPN site <?php echo $site; ?>';
            document.getElementById('statut').value = 'Public';
            document.getElementById('modalAtelier').style.display = 'block';
        }

        function modifierAtelier(id) {
            fetch(`<?= h($apiBase) ?>/atelier.php?site=<?php echo $site; ?>&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const at = data.atelier;
                        document.getElementById('num_atelier').value = at.NUM_ATELIER;
                        document.getElementById('date_atelier').value = at.DATE_ATELIER || '';
                        document.getElementById('periode_atelier').value = at.PERIODE_ATELIER || '';
                        document.getElementById('espace_atelier').value = at.ESPACE_ATELIER || '';
                        document.getElementById('referent').value = at.REFERENT || '';
                        document.getElementById('noms_prenoms').value = at.NOMS_PRENOMS || '';
                        document.getElementById('civilite').value = at.CIVILITE || '';
                        document.getElementById('code_ville').value = at.CODE_VILLE || '';
                        document.getElementById('telephone_portable').value = at.TELEPHONES_PORTABLE || '';
                        document.getElementById('telephone_fixe').value = at.TELEPHONES_FIXE || '';
                        document.getElementById('atelier_choisi').value = at.ATELIER_CHOISI || '';
                        document.getElementById('formateur').value = at.FORMATEUR || '';
                        document.getElementById('situation_formation').value = at.SITUATION_FORMATION || '';
                        document.getElementById('statut').value = at.STATUT || 'Public';
                        document.getElementById('observations').value = at.OBSERVATIONS || '';
                        document.getElementById('modalAtelier').style.display = 'block';
                    }
                });
        }

        function sauvegarderAtelier(event) {
            event.preventDefault();
            const formData = new FormData(document.getElementById('formAtelier'));

            fetch('<?= h($apiBase) ?>/atelier.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Atelier enregistré avec succès !');
                    window.location.reload();
                } else {
                    alert('Erreur : ' + data.message);
                }
            });
        }

        function rechercherUsager() {
            document.getElementById('modalUsager').style.display = 'block';
            rechercherUsagers();
        }

        function rechercherUsagers() {
            const recherche = document.getElementById('rechercheUsager').value;
            fetch(`<?= h($apiBase) ?>/usagers.php?recherche=${encodeURIComponent(recherche)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = '<ul class="usager-list">';
                        if (data.usagers.length === 0) {
                            html += '<li class="no-data">Aucun usager trouvé</li>';
                        } else {
                            data.usagers.forEach(usager => {
                                html += `<li onclick="choisirUsager(${usager.NUM_INSCRIPTION}, '${usager.CIVILITE || ''}', '${usager.NOMS_PRENOMS}', '${usager.CODE_VILLE || ''}', '${usager.TELEPHONES_PORTABLE || ''}', '${usager.TELEPHONES_FIXE || ''}')">
                                    <strong>${usager.CIVILITE || ''} ${usager.NOMS_PRENOMS}</strong><br>
                                    <small>${usager.ADRESSE || ''} ${usager.CODE_VILLE || ''}</small>
                                </li>`;
                            });
                        }
                        html += '</ul>';
                        document.getElementById('listeUsagers').innerHTML = html;
                    }
                });
        }

        function choisirUsager(id, civilite, nom, codeVille, telPortable, telFixe) {
            document.getElementById('noms_prenoms').value = nom;
            document.getElementById('civilite').value = civilite;
            document.getElementById('code_ville').value = codeVille;
            document.getElementById('telephone_portable').value = telPortable;
            document.getElementById('telephone_fixe').value = telFixe;
            fermerModalUsager();
        }

        function fermerModalUsager() {
            document.getElementById('modalUsager').style.display = 'none';
        }

        function selectionnerAtelier() {
            const ateliers = [
                'MS Word', 'MS Excel', 'MS PowerPoint', 'MS Publisher', 'MS Access',
                'Internet et domaines', 'Réseau', 'Graphisme/Paint', 'Musique', 'Vidéo',
                'Cours', 'Leçon', 'Enfant/Enseignement', 'Autre'
            ];
            let html = '<ul class="atelier-list">';
            ateliers.forEach(atelier => {
                html += `<li onclick="choisirAtelier('${atelier}')">${atelier}</li>`;
            });
            html += '</ul>';
            document.getElementById('listeAteliers').innerHTML = html;
            document.getElementById('modalAtelierChoix').style.display = 'block';
        }

        function choisirAtelier(nom) {
            document.getElementById('atelier_choisi').value = nom;
            fermerModalAtelierChoix();
        }

        function fermerModalAtelierChoix() {
            document.getElementById('modalAtelierChoix').style.display = 'none';
        }

        function selectionnerAgent() {
            fetch('<?= h($apiBase) ?>/agents.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = '<ul class="agent-list">';
                        data.agents.forEach(agent => {
                            html += `<li onclick="choisirAgent('${agent.NOM_AGENT}')">${agent.NOM_AGENT}</li>`;
                        });
                        html += '</ul>';
                        document.getElementById('listeAgents').innerHTML = html;
                        document.getElementById('modalAgent').style.display = 'block';
                    }
                });
        }

        function selectionnerFormateur() {
            selectionnerAgent(); // Utilise la même liste que les agents
        }

        function choisirAgent(nom) {
            if (document.getElementById('formateur').readOnly) {
                document.getElementById('formateur').value = nom;
            } else {
                document.getElementById('referent').value = nom;
            }
            fermerModalAgent();
        }

        function fermerModalAgent() {
            document.getElementById('modalAgent').style.display = 'none';
        }

        function fermerModal() {
            document.getElementById('modalAtelier').style.display = 'none';
        }

    </script>
</body>
</html>

