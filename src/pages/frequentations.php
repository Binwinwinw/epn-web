<?php
/**
 * Gestion des fréquentations
 * Équivalent à FREQUENTATIONS1.vb / FREQUENTATIONS2.vb
 */
$routerPublicUrl = '../../public/index.php';
if (!defined('EPN_APP_ROUTER')) {
    $redirectQuery = $_GET;
    $redirectQuery['page'] = 'frequentations';
    header('Location: ' . $routerPublicUrl . '?' . http_build_query($redirectQuery));
    exit;
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/nav-logout.php';

$assetBase = $assetBase ?? 'assets';
$apiBase = $apiBase ?? '../src/api';
$accueilSiteUrl = $accueilSiteUrl ?? 'index.php?page=accueil-site';
$frequentationsUrl = $frequentationsUrl ?? 'index.php?page=frequentations';

$site = isset($_GET['site']) ? strtoupper($_GET['site']) : 'BAC';
if (!in_array($site, ['BAC', 'MAC'])) {
    $site = 'BAC';
}

$tableFrequentation = $site === 'BAC' ? 'frequentation1' : 'frequentation2';
$siteConfig = [
    'BAC' => ['nom' => 'EPN SITE BAC', 'couleur' => '#2563eb', 'couleurFond' => '#dbeafe'],
    'MAC' => ['nom' => 'EPN SITE MAC', 'couleur' => '#0891b2', 'couleurFond' => '#cffafe']
];
$config = $siteConfig[$site];

// Recherche
$recherche = isset($_GET['recherche']) ? $_GET['recherche'] : '';
$frequentations = [];

if (!empty($recherche)) {
    $stmt = $pdo->prepare("
        SELECT * FROM $tableFrequentation 
        WHERE DATE_FREQUENTATION LIKE ? OR NOMS_PRENOMS LIKE ?
        ORDER BY DATE_FREQUENTATION DESC, HEURE_ENTREE DESC
        LIMIT 100
    ");
    $searchTerm = "%$recherche%";
    $stmt->execute([$searchTerm, $searchTerm]);
    $frequentations = $stmt->fetchAll();
} else {
    $dateAujourdhui = date('d/m/Y');
    $stmt = $pdo->prepare("
        SELECT * FROM $tableFrequentation 
        WHERE DATE_FREQUENTATION LIKE ?
        ORDER BY HEURE_ENTREE DESC
        LIMIT 100
    ");
    $stmt->execute(["%$dateAujourdhui%"]);
    $frequentations = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fréquentations - <?php echo $config['nom']; ?></title>
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
            <h1>Fréquentations - <?php echo $config['nom']; ?></h1>
            <div class="date-display">
                <span id="dateJour"><?php echo h(formatDateLongFr(date('Y-m-d'))); ?></span>
                <span id="heureJour" style="margin-left: 20px;"><?php echo date('H:i:s'); ?></span>
            </div>
        </header>

        <div class="content-page">
            <div class="search-bar">
                <input type="text" id="recherche" placeholder="Rechercher par nom ou date" 
                       value="<?php echo h($recherche); ?>" 
                       onkeyup="if(event.key==='Enter') rechercher()">
                <button onclick="rechercher()" class="btn-search">Rechercher</button>
                <button onclick="document.getElementById('recherche').value=''; rechercher();" class="btn-clear">Effacer</button>
                <button onclick="document.getElementById('recherche').value='<?php echo date('d/m/Y'); ?>'; rechercher();" class="btn-today">Aujourd'hui</button>
            </div>

            <div class="action-bar">
                <button onclick="nouvelleFrequentation()" class="btn-primary" style="background-color: <?php echo $config['couleur']; ?>;">
                    Nouvelle fréquentation
                </button>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Usager</th>
                            <th>Poste</th>
                            <th>Utilisation</th>
                            <th>Référent</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($frequentations)): ?>
                        <tr>
                            <td colspan="7" class="no-data">Aucune fréquentation trouvée</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($frequentations as $freq): ?>
                        <tr>
                            <td><?php echo h($freq['DATE_FREQUENTATION']); ?></td>
                            <td><?php echo h($freq['HEURE_ENTREE']); ?> - <?php echo h($freq['HEURE_SORTIE']); ?></td>
                            <td><strong><?php echo h($freq['CIVILITE'] . ' ' . $freq['NOMS_PRENOMS']); ?></strong></td>
                            <td><?php echo h($freq['POSTES']); ?></td>
                            <td><?php echo h($freq['UTILISATIONS']); ?></td>
                            <td><?php echo h($freq['REFERENT']); ?></td>
                            <td>
                                <button onclick="modifierFrequentation(<?php echo $freq['NUM_FREQUENTATION']; ?>)" 
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

    <!-- Modal nouvelle fréquentation -->
    <div id="modalFrequentation" class="modal" style="display: none;">
        <div class="modal-content modal-large">
            <span class="close" onclick="fermerModal()">&times;</span>
            <h2>Nouvelle fréquentation</h2>
            <form id="formFrequentation" onsubmit="sauvegarderFrequentation(event)">
                <input type="hidden" id="num_frequentation" name="num_frequentation">
                <input type="hidden" id="site" name="site" value="<?php echo $site; ?>">
                <input type="hidden" name="csrf_token" value="<?= h(getCSRFToken()); ?>">
                
                <div class="form-group">
                    <label>Date de fréquentation *</label>
                    <input type="text" id="date_frequentation" name="date_frequentation" 
                           value="<?php echo date('d/m/Y'); ?>" required>
                </div>

                <div class="form-group">
                    <label>Espace *</label>
                    <input type="text" id="espace_frequente" name="espace_frequente" 
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

                <div class="form-group">
                    <label>Type d'utilisation *</label>
                    <div class="input-with-button">
                        <input type="text" id="utilisations" name="utilisations" required readonly>
                        <button type="button" onclick="selectionnerUtilisations()" class="btn-select">Sélectionner</button>
                    </div>
                    <input type="hidden" id="tests" name="tests">
                </div>

                <div class="form-group">
                    <label>Poste informatique *</label>
                    <div class="input-with-button">
                        <input type="text" id="postes" name="postes" required readonly>
                        <button type="button" onclick="selectionnerPoste()" class="btn-select">Sélectionner</button>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Heure d'entrée *</label>
                        <input type="time" id="heure_entree" name="heure_entree" 
                               value="<?php echo date('H:i'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Heure de sortie</label>
                        <input type="time" id="heure_sortie" name="heure_sortie">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Casque</label>
                        <select id="casque" name="casque">
                            <option value="">--</option>
                            <option value="Oui">Oui</option>
                            <option value="Non">Non</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Webcam</label>
                        <select id="webcam" name="webcam">
                            <option value="">--</option>
                            <option value="Oui">Oui</option>
                            <option value="Non">Non</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nombre de copies</label>
                        <input type="number" id="nombre_copies" name="nombre_copies" min="0" value="0">
                    </div>
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

    <!-- Modals pour sélections -->
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

    <div id="modalUtilisations" class="modal" style="display: none;">
        <div class="modal-content modal-large">
            <span class="close" onclick="fermerModalUtilisations()">&times;</span>
            <h2>Sélectionner les types d'utilisation</h2>
            <div id="grilleUtilisations" class="grille-utilisations"></div>
            <div class="form-actions">
                <button onclick="validerUtilisations()" class="btn-primary">Valider</button>
                <button onclick="fermerModalUtilisations()" class="btn-secondary">Annuler</button>
            </div>
        </div>
    </div>

    <div id="modalPoste" class="modal" style="display: none;">
        <div class="modal-content modal-medium">
            <span class="close" onclick="fermerModalPoste()">&times;</span>
            <h2>Sélectionner un poste</h2>
            <div id="grillePostes" class="grille-postes"></div>
        </div>
    </div>

    <div id="modalAgent" class="modal" style="display: none;">
        <div class="modal-content modal-small">
            <span class="close" onclick="fermerModalAgent()">&times;</span>
            <h2>Sélectionner un agent</h2>
            <div id="listeAgents" class="liste-agents"></div>
        </div>
    </div>

    <script src="<?= h($assetBase) ?>/js/frequentations.js"></script>
    <script>
        function rechercher() {
            const recherche = document.getElementById('recherche').value;
            window.location.href = '<?= h($frequentationsUrl) ?>&site=<?php echo $site; ?>&recherche=' + encodeURIComponent(recherche);
        }

        function nouvelleFrequentation() {
            document.getElementById('formFrequentation').reset();
            document.getElementById('num_frequentation').value = '';
            document.getElementById('date_frequentation').value = '<?php echo date('d/m/Y'); ?>';
            document.getElementById('espace_frequente').value = 'EPN site <?php echo $site; ?>';
            document.getElementById('heure_entree').value = '<?php echo date('H:i'); ?>';
            document.getElementById('nombre_copies').value = '0';
            document.getElementById('modalFrequentation').style.display = 'block';
        }

        function modifierFrequentation(id) {
            fetch(`<?= h($apiBase) ?>/frequentation.php?site=<?php echo $site; ?>&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const freq = data.frequentation;
                        document.getElementById('num_frequentation').value = freq.NUM_FREQUENTATION;
                        document.getElementById('date_frequentation').value = freq.DATE_FREQUENTATION || '';
                        document.getElementById('espace_frequente').value = freq.ESPACE_FREQUENTE || '';
                        document.getElementById('referent').value = freq.REFERENT || '';
                        document.getElementById('noms_prenoms').value = freq.NOMS_PRENOMS || '';
                        document.getElementById('civilite').value = freq.CIVILITE || '';
                        document.getElementById('code_ville').value = freq.CODE_VILLE || '';
                        document.getElementById('postes').value = freq.POSTES || '';
                        document.getElementById('utilisations').value = freq.UTILISATIONS || '';
                        document.getElementById('heure_entree').value = freq.HEURE_ENTREE || '';
                        document.getElementById('heure_sortie').value = freq.HEURE_SORTIE || '';
                        document.getElementById('casque').value = freq.CASQUE || '';
                        document.getElementById('webcam').value = freq.WEBCAM || '';
                        document.getElementById('nombre_copies').value = freq.NOMBRE_COPIES || 0;
                        document.getElementById('observations').value = freq.OBSERVATIONS || '';
                        document.getElementById('modalFrequentation').style.display = 'block';
                    }
                });
        }

        function sauvegarderFrequentation(event) {
            event.preventDefault();
            const formData = new FormData(document.getElementById('formFrequentation'));

            fetch('<?= h($apiBase) ?>/frequentation.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Fréquentation enregistrée avec succès !');
                    window.location.reload();
                } else {
                    alert('Erreur : ' + data.message);
                }
            });
        }

        function fermerModal() {
            document.getElementById('modalFrequentation').style.display = 'none';
        }

        setInterval(() => {
            document.getElementById('heureJour').textContent = new Date().toLocaleTimeString('fr-FR');
        }, 1000);

    </script>
</body>
</html>

