<?php
/**
 * Gestion du matériel (Postes informatiques)
 * Liste des postes avec possibilité d'ajout
 */
$routerPublicUrl = '../../public/index.php';
if (!defined('EPN_APP_ROUTER')) {
    $redirectQuery = $_GET;
    $redirectQuery['page'] = 'materiel';
    header('Location: ' . $routerPublicUrl . '?' . http_build_query($redirectQuery));
    exit;
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/nav-logout.php';

$assetBase = $assetBase ?? 'assets';
$apiBase = $apiBase ?? '../src/api';
$accueilSiteUrl = $accueilSiteUrl ?? 'index.php?page=accueil-site';

$site = isset($_GET['site']) ? strtoupper($_GET['site']) : 'BAC';
if (!in_array($site, ['BAC', 'MAC'])) {
    $site = 'BAC';
}

$siteConfig = [
    'BAC' => [
        'nom' => 'EPN SITE BAC',
        'couleur' => '#2563eb',
        'couleurFond' => '#dbeafe',
        'couleurTexte' => '#1e40af'
    ],
    'MAC' => [
        'nom' => 'EPN SITE MAC',
        'couleur' => '#0891b2',
        'couleurFond' => '#cffafe',
        'couleurTexte' => '#0e7490'
    ]
];
$config = $siteConfig[$site];

// Récupérer tous les postes du site (toutes dates confondues)
$stmt = $pdo->prepare("
    SELECT * FROM postes 
    WHERE SITE = ?
    ORDER BY DATE_UTILISATION DESC, NUMERO_POSTE
");
$stmt->execute([$site]);
$postes = normalizePostesRecords($stmt->fetchAll());

// Récupérer le numéro de poste maximum pour suggérer le suivant
$stmt = $pdo->prepare("
    SELECT MAX(NUMERO_POSTE) as max_num FROM postes WHERE SITE = ?
");
$stmt->execute([$site]);
$result = $stmt->fetch();
$prochainNumero = ($result['max_num'] ?? 0) + 1;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matériel - <?php echo $config['nom']; ?></title>
    <link rel="stylesheet" href="<?= h($assetBase) ?>/css/style.css">
    <link rel="stylesheet" href="<?= h($assetBase) ?>/css/uiverse-modern.css">
    <style>
        :root {
            --site-color: <?php echo $config['couleur']; ?>;
            --site-bg: <?php echo $config['couleurFond']; ?>;
            --site-text: <?php echo $config['couleurTexte']; ?>;
        }

        .materiel-container {
            padding: 24px 20px 40px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .materiel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 16px;
            padding: 16px 20px;
            border-radius: 10px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        }

        .materiel-header h2 {
            margin: 0;
            color: #0f172a;
            font-size: clamp(1.15rem, 2vw, 1.45rem);
        }

        .btn-ajouter,
        .btn-submit,
        .btn-cancel {
            border: none;
            padding: 9px 16px;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.15s ease;
        }

        .btn-ajouter,
        .btn-submit {
            background: var(--site-color);
            color: white;
        }

        .btn-ajouter:hover,
        .btn-submit:hover,
        .btn-cancel:hover {
            opacity: 0.88;
        }

        .btn-cancel {
            background: #e2e8f0;
            color: #1e293b;
        }

        .postes-table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
            border-radius: 0;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
            border: 1px solid #e2e8f0;
        }

        .postes-table thead {
            background: var(--site-color);
            color: white;
        }

        .postes-table th,
        .postes-table td {
            padding: 13px 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .postes-table th {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .postes-table tbody tr:hover {
            background-color: rgba(37, 99, 235, 0.04);
        }

        .statut-badge {
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

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(4px);
            overflow: auto;
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 24px;
            border-radius: 10px;
            width: 92%;
            max-width: 560px;
            box-shadow: 0 4px 24px rgba(15, 23, 42, 0.16);
            border: 1px solid #e2e8f0;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        .modal-header h2 {
            margin: 0;
            color: #0f172a;
        }

        .close {
            color: #64748b;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 20px;
        }

        .close:hover {
            color: #0f172a;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 700;
            color: #334155;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 9px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 0.94rem;
            box-sizing: border-box;
            background: #fff;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--site-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 24px;
        }

        .empty-state {
            text-align: center;
            padding: 32px 24px;
            color: #475569;
            border-radius: 8px;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
        }

        @media (max-width: 768px) {
            .materiel-header {
                flex-direction: column;
                align-items: stretch;
            }

            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container-site" data-site="<?php echo $site; ?>">
        <?php renderRetourTop(h($accueilSiteUrl) . '&site=' . $site); ?>
        <header class="header-site" style="background-color: <?php echo $config['couleur']; ?>;">
            <h1>Matériel - <?php echo $config['nom']; ?></h1>
        </header>

        <div class="materiel-container">
            <div class="materiel-header">
                <h2>Postes informatiques du site</h2>
                <button class="btn-ajouter" onclick="ouvrirModalAjout()">
                    Ajouter un poste
                </button>
            </div>

            <?php if (empty($postes)): ?>
                <div class="empty-state">
                    <p>Aucun poste enregistré pour ce site.</p>
                    <button class="btn-ajouter" onclick="ouvrirModalAjout()" style="margin-top: 20px;">
                        Créer le premier poste
                    </button>
                </div>
            <?php else: ?>
                <table class="postes-table">
                    <thead>
                        <tr>
                            <th>N° Poste</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Usager</th>
                            <th>Utilisation</th>
                            <th>Heure début</th>
                            <th>Heure fin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($postes as $poste): ?>
                        <tr>
                            <td><strong><?php echo h($poste['NUMERO_POSTE']); ?></strong></td>
                            <td><?php echo formatDate($poste['DATE_UTILISATION']); ?></td>
                            <td>
                                <span class="statut-badge statut-<?php echo strtolower(str_replace(' ', '-', $poste['STATUT'])); ?>">
                                    <?php echo h($poste['STATUT']); ?>
                                </span>
                            </td>
                            <td><?php echo !empty($poste['NOMS_PRENOMS']) ? h($poste['NOMS_PRENOMS']) : '-'; ?></td>
                            <td><?php echo !empty($poste['UTILISATIONS']) ? h($poste['UTILISATIONS']) : '-'; ?></td>
                            <td><?php echo !empty($poste['HEURE_DEBUT']) ? substr($poste['HEURE_DEBUT'], 0, 5) : '-'; ?></td>
                            <td><?php echo !empty($poste['HEURE_FIN']) ? substr($poste['HEURE_FIN'], 0, 5) : '-'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <?php renderLogoutFooter(h($accueilSiteUrl) . '&site=' . $site, $apiBase); ?>
    </div>

    <!-- Modal d'ajout de poste -->
    <div id="modalAjout" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Ajouter un nouveau poste</h2>
                <span class="close" onclick="fermerModalAjout()">&times;</span>
            </div>
            <form id="formAjoutPoste" onsubmit="ajouterPoste(event)">
                <div class="form-group">
                    <label for="numero_poste">Numéro du poste *</label>
                    <input type="number" id="numero_poste" name="numero_poste" 
                           min="1" required value="<?php echo $prochainNumero; ?>">
                </div>
                
                <div class="form-group">
                    <label for="date_utilisation">Date d'utilisation *</label>
                    <input type="date" id="date_utilisation" name="date_utilisation" 
                           value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="statut">Statut *</label>
                    <select id="statut" name="statut" required>
                        <option value="Libre" selected>Libre</option>
                        <option value="En cours">En cours</option>
                        <option value="Terminé">Terminé</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="noms_prenoms">Usager</label>
                    <input type="text" id="noms_prenoms" name="noms_prenoms" 
                           placeholder="Nom et prénom de l'usager">
                </div>
                
                <div class="form-group">
                    <label for="utilisations">Type d'utilisation</label>
                    <input type="text" id="utilisations" name="utilisations" 
                           placeholder="Ex: France Travail, CAF, etc.">
                </div>
                
                <div class="form-group">
                    <label for="heure_debut">Heure de début</label>
                    <input type="time" id="heure_debut" name="heure_debut">
                </div>
                
                <div class="form-group">
                    <label for="heure_fin">Heure de fin</label>
                    <input type="time" id="heure_fin" name="heure_fin">
                </div>
                
                <input type="hidden" name="site" value="<?php echo $site; ?>">
                <input type="hidden" name="csrf_token" value="<?= h(getCSRFToken()); ?>">
                
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="fermerModalAjout()">Annuler</button>
                    <button type="submit" class="btn-submit">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Ouvrir le modal
        function ouvrirModalAjout() {
            document.getElementById('modalAjout').style.display = 'block';
        }
        
        // Fermer le modal
        function fermerModalAjout() {
            document.getElementById('modalAjout').style.display = 'none';
            document.getElementById('formAjoutPoste').reset();
        }
        
        // Fermer le modal en cliquant en dehors
        window.onclick = function(event) {
            const modal = document.getElementById('modalAjout');
            if (event.target === modal) {
                fermerModalAjout();
            }
        }
        
        // Ajouter un poste
        function ajouterPoste(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const data = {
                action: 'create',
                site: formData.get('site'),
                numero_poste: parseInt(formData.get('numero_poste')),
                date_utilisation: formData.get('date_utilisation'),
                statut: formData.get('statut'),
                noms_prenoms: formData.get('noms_prenoms') || null,
                utilisations: formData.get('utilisations') || null,
                heure_debut: formData.get('heure_debut') || null,
                heure_fin: formData.get('heure_fin') || null
            };
            
            const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
            fetch('<?= h($apiBase) ?>/postes.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    ...(csrfToken ? { 'X-CSRF-Token': csrfToken } : {})
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Poste ajouté avec succès !');
                    fermerModalAjout();
                    // Recharger la page pour afficher le nouveau poste
                    window.location.reload();
                } else {
                    alert('Erreur : ' + (result.message || 'Impossible d\'ajouter le poste'));
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'ajout du poste');
            });
        }

    </script>
</body>
</html>

