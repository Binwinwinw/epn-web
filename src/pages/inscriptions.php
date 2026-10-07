<?php
/**
 * Gestion des inscriptions
 * Équivalent à INSCRIPTIONS1.vb / INSCRIPTIONS2.vb
 */
$routerPublicUrl = '../../public/index.php';
if (!defined('EPN_APP_ROUTER')) {
    $redirectQuery = $_GET;
    $redirectQuery['page'] = 'inscriptions';
    header('Location: ' . $routerPublicUrl . '?' . http_build_query($redirectQuery));
    exit;
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/nav-logout.php';

$assetBase = $assetBase ?? 'assets';
$apiBase = $apiBase ?? '../src/api';
$accueilSiteUrl = $accueilSiteUrl ?? 'index.php?page=accueil-site';
$inscriptionsUrl = $inscriptionsUrl ?? 'index.php?page=inscriptions';

$site = isset($_GET['site']) ? strtoupper($_GET['site']) : 'BAC';
if (!in_array($site, ['BAC', 'MAC'])) {
    $site = 'BAC';
}

$siteConfig = [
    'BAC' => ['nom' => 'EPN SITE BAC', 'couleur' => '#2563eb', 'couleurFond' => '#dbeafe'],
    'MAC' => ['nom' => 'EPN SITE MAC', 'couleur' => '#0891b2', 'couleurFond' => '#cffafe']
];
$config = $siteConfig[$site];

// Recherche
$recherche = isset($_GET['recherche']) ? $_GET['recherche'] : '';
$inscriptions = [];

if (!empty($recherche)) {
    $stmt = $pdo->prepare("
        SELECT * FROM inscription 
        WHERE DATE_INSCRIPTION LIKE ? OR NOMS_PRENOMS LIKE ?
        ORDER BY DATE_INSCRIPTION DESC, NOMS_PRENOMS
        LIMIT 100
    ");
    $searchTerm = "%$recherche%";
    $stmt->execute([$searchTerm, $searchTerm]);
    $inscriptions = $stmt->fetchAll();
} else {
    // Afficher les inscriptions d'aujourd'hui par défaut
    $dateAujourdhui = date('d/m/Y');
    $stmt = $pdo->prepare("
        SELECT * FROM inscription 
        WHERE DATE_INSCRIPTION LIKE ?
        ORDER BY DATE_INSCRIPTION DESC, NOMS_PRENOMS
        LIMIT 100
    ");
    $stmt->execute(["%$dateAujourdhui%"]);
    $inscriptions = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscriptions - <?php echo $config['nom']; ?></title>
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
            <h1>Inscriptions - <?php echo $config['nom']; ?></h1>
            <div class="date-display">
                <span id="dateJour"><?php echo h(formatDateLongFr(date('Y-m-d'))); ?></span>
                <span id="heureJour" style="margin-left: 20px;"><?php echo date('H:i:s'); ?></span>
            </div>
        </header>

        <div class="content-page">
            <!-- Barre de recherche -->
            <div class="search-bar">
                <input type="text" id="recherche" placeholder="Rechercher par nom ou date (ex: Dupont ou 01/01/2024)" 
                       value="<?php echo h($recherche); ?>" 
                       onkeyup="if(event.key==='Enter') rechercher()">
                <button onclick="rechercher()" class="btn-search">Rechercher</button>
                <button onclick="document.getElementById('recherche').value=''; rechercher();" class="btn-clear">Effacer</button>
                <button onclick="document.getElementById('recherche').value='<?php echo date('d/m/Y'); ?>'; rechercher();" class="btn-today">Aujourd'hui</button>
            </div>

            <!-- Bouton nouvelle inscription -->
            <div class="action-bar">
                <button onclick="nouvelleInscription()" class="btn-primary" style="background-color: <?php echo $config['couleur']; ?>;">
                    Nouvelle inscription
                </button>
            </div>

            <!-- Liste des inscriptions -->
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Nom</th>
                            <th>Espace</th>
                            <th>Référent</th>
                            <th>Téléphone</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($inscriptions)): ?>
                        <tr>
                            <td colspan="7" class="no-data">Aucune inscription trouvée</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($inscriptions as $inscription): ?>
                        <tr>
                            <td><?php echo h($inscription['DATE_INSCRIPTION']); ?></td>
                            <td><strong><?php echo h($inscription['CIVILITE'] . ' ' . $inscription['NOMS_PRENOMS']); ?></strong></td>
                            <td><?php echo h($inscription['ESPACE']); ?></td>
                            <td><?php echo h($inscription['REFERENT']); ?></td>
                            <td><?php echo h($inscription['TELEPHONES_PORTABLE'] ?: $inscription['TELEPHONES_FIXE']); ?></td>
                            <td><?php echo h($inscription['EMAIL']); ?></td>
                            <td>
                                <button onclick="modifierInscription(<?php echo $inscription['NUM_INSCRIPTION']; ?>)" 
                                        class="btn-small">Modifier</button>
                                <button onclick="copierInfos(<?php echo $inscription['NUM_INSCRIPTION']; ?>)" 
                                        class="btn-small">Copier</button>
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

    <!-- Modal nouvelle/modification inscription -->
    <div id="modalInscription" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="fermerModal()">&times;</span>
            <h2 id="modalTitre">Nouvelle inscription</h2>
            <form id="formInscription" onsubmit="sauvegarderInscription(event)">
                <input type="hidden" id="num_inscription" name="num_inscription">
                <input type="hidden" name="csrf_token" value="<?= h(getCSRFToken()); ?>">
                
                <div class="form-group">
                    <label>Date d'inscription *</label>
                    <input type="text" id="date_inscription" name="date_inscription" 
                           value="<?php echo date('d/m/Y'); ?>" required>
                </div>

                <div class="form-group">
                    <label>Espace *</label>
                    <input type="text" id="espace" name="espace" 
                           value="EPN site <?php echo $site; ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Référent (Agent) *</label>
                    <div class="input-with-button">
                        <input type="text" id="referent" name="referent" required readonly>
                        <button type="button" onclick="selectionnerAgent()" class="btn-select">Sélectionner</button>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Civilité</label>
                        <select id="civilite" name="civilite">
                            <option value="">--</option>
                            <option value="M.">M.</option>
                            <option value="Mme">Mme</option>
                            <option value="Mlle">Mlle</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nom et Prénom *</label>
                        <input type="text" id="noms_prenoms" name="noms_prenoms" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Date de naissance</label>
                    <input type="text" id="date_naissance" name="date_naissance" placeholder="JJ/MM/AAAA">
                </div>

                <div class="form-group">
                    <label>Adresse</label>
                    <textarea id="adresse" name="adresse" rows="2"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Code postal</label>
                        <input type="text" id="code_ville" name="code_ville">
                    </div>
                    <div class="form-group">
                        <label>Pays</label>
                        <input type="text" id="pays" name="pays" value="France">
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
                    <label>Email</label>
                    <input type="email" id="email" name="email">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Situation</label>
                        <select id="situation" name="situation">
                            <option value="">--</option>
                            <option value="Actif">Actif</option>
                            <option value="Chômeur">Chômeur</option>
                            <option value="Retraité">Retraité</option>
                            <option value="Étudiant">Étudiant</option>
                            <option value="Autre">Autre</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Profil</label>
                        <select id="profil" name="profil">
                            <option value="">--</option>
                            <option value="Débutant">Débutant</option>
                            <option value="Intermédiaire">Intermédiaire</option>
                            <option value="Avancé">Avancé</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Équipement personnel</label>
                        <select id="equipement_personnel" name="equipement_personnel">
                            <option value="">--</option>
                            <option value="Oui">Oui</option>
                            <option value="Non">Non</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Cadre d'utilisation</label>
                        <select id="cadre_utilisation" name="cadre_utilisation">
                            <option value="">--</option>
                            <option value="Personnel">Personnel</option>
                            <option value="Professionnel">Professionnel</option>
                            <option value="Formation">Formation</option>
                        </select>
                    </div>
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

    <!-- Modal sélection agent -->
    <div id="modalAgent" class="modal" style="display: none;">
        <div class="modal-content modal-small">
            <span class="close" onclick="fermerModalAgent()">&times;</span>
            <h2>Sélectionner un agent</h2>
            <div id="listeAgents" class="liste-agents">
                <!-- Chargé via AJAX -->
            </div>
        </div>
    </div>

    <script>
        function rechercher() {
            const recherche = document.getElementById('recherche').value;
            window.location.href = '<?= h($inscriptionsUrl) ?>&site=<?php echo $site; ?>&recherche=' + encodeURIComponent(recherche);
        }

        function nouvelleInscription() {
            document.getElementById('modalTitre').textContent = 'Nouvelle inscription';
            document.getElementById('formInscription').reset();
            document.getElementById('num_inscription').value = '';
            document.getElementById('date_inscription').value = '<?php echo date('d/m/Y'); ?>';
            document.getElementById('espace').value = 'EPN site <?php echo $site; ?>';
            document.getElementById('pays').value = 'France';
            document.getElementById('modalInscription').style.display = 'block';
        }

        function modifierInscription(id) {
            fetch(`<?= h($apiBase) ?>/inscription.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const ins = data.inscription;
                        document.getElementById('modalTitre').textContent = 'Modifier inscription';
                        document.getElementById('num_inscription').value = ins.NUM_INSCRIPTION;
                        document.getElementById('date_inscription').value = ins.DATE_INSCRIPTION || '';
                        document.getElementById('espace').value = ins.ESPACE || '';
                        document.getElementById('referent').value = ins.REFERENT || '';
                        document.getElementById('civilite').value = ins.CIVILITE || '';
                        document.getElementById('noms_prenoms').value = ins.NOMS_PRENOMS || '';
                        document.getElementById('date_naissance').value = ins.DATE_NAISSANCE || '';
                        document.getElementById('adresse').value = ins.ADRESSE || '';
                        document.getElementById('code_ville').value = ins.CODE_VILLE || '';
                        document.getElementById('pays').value = ins.PAYS || 'France';
                        document.getElementById('telephone_portable').value = ins.TELEPHONES_PORTABLE || '';
                        document.getElementById('telephone_fixe').value = ins.TELEPHONES_FIXE || '';
                        document.getElementById('email').value = ins.EMAIL || '';
                        document.getElementById('situation').value = ins.SITUATION || '';
                        document.getElementById('profil').value = ins.PROFIL || '';
                        document.getElementById('equipement_personnel').value = ins.EQUIPEMENT_PERSONNEL || '';
                        document.getElementById('cadre_utilisation').value = ins.CADRE_UTILISATION || '';
                        document.getElementById('modalInscription').style.display = 'block';
                    }
                });
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

        function choisirAgent(nom) {
            document.getElementById('referent').value = nom;
            fermerModalAgent();
        }

        function fermerModalAgent() {
            document.getElementById('modalAgent').style.display = 'none';
        }

        function fermerModal() {
            document.getElementById('modalInscription').style.display = 'none';
        }

        function sauvegarderInscription(event) {
            event.preventDefault();
            const formData = new FormData(document.getElementById('formInscription'));
            formData.append('site', '<?php echo $site; ?>');

            fetch('<?= h($apiBase) ?>/inscription.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Inscription enregistrée avec succès !');
                    window.location.reload();
                } else {
                    alert('Erreur : ' + data.message);
                }
            });
        }

        function copierInfos(id) {
            fetch(`<?= h($apiBase) ?>/inscription.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const ins = data.inscription;
                        const texte = `${ins.CIVILITE} ${ins.NOMS_PRENOMS}\n${ins.ADRESSE}\n${ins.CODE_VILLE}\n${ins.PAYS}\n${ins.TELEPHONES_FIXE}\n${ins.TELEPHONES_PORTABLE}\n${ins.EMAIL}`;
                        navigator.clipboard.writeText(texte).then(() => {
                            alert('Informations copiées !');
                        });
                    }
                });
        }

        // Mise à jour de l'heure
        setInterval(() => {
            document.getElementById('heureJour').textContent = new Date().toLocaleTimeString('fr-FR');
        }, 1000);

    </script>
</body>
</html>

