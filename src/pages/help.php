<?php
/**
 * Page d'aide
 * Équivalent à HELP.vb
 */
$routerPublicUrl = '../../public/index.php';
if (!defined('EPN_APP_ROUTER')) {
    $redirectQuery = $_GET;
    $redirectQuery['page'] = 'help';
    header('Location: ' . $routerPublicUrl . '?' . http_build_query($redirectQuery));
    exit;
}

require_once __DIR__ . '/../config/config.php';

$assetBase = $assetBase ?? 'assets';
$homeAppUrl = $homeAppUrl ?? 'index.php?page=accueil';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aide - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?= h($assetBase) ?>/css/style.css">
    <link rel="stylesheet" href="<?= h($assetBase) ?>/css/uiverse-modern.css">
    <style>
        .help-container {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        .help-section {
            margin: 20px 0;
            padding: 20px;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 4px solid #2563eb;
        }
        .help-section h2 {
            color: #2563eb;
            margin-bottom: 12px;
        }
        .help-section ul {
            list-style-type: none;
            padding-left: 0;
        }
        .help-section li {
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .help-section li:last-child {
            border-bottom: none;
        }
        .code-example {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 6px;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
            overflow-x: auto;
        }
    </style>
</head>
<body>
<?php
require_once __DIR__ . '/../includes/nav-logout.php';
renderRetourTop(h($homeAppUrl), '← Retour à l\'accueil');
?>
    <div class="help-container">
        <header style="text-align: center; margin-bottom: 24px;">
            <h1>Aide - Gestion des EPN</h1>
        </header>

        <div class="help-section">
            <h2>Recherche d'usagers</h2>
            <p>Pour rechercher un usager dans le système :</p>
            <ul>
                <li><strong>Par nom :</strong> Tapez le nom complet ou partiel (ex: "Dupont" ou "Dupont Alain")</li>
                <li><strong>Par date :</strong> Tapez une date au format DD/MM/YYYY ou MM/YYYY (ex: "01/01/2024" ou "01/2024")</li>
            </ul>
            <div class="code-example">
                Exemple n°1 : Chercher un usager<br>
                On peut taper : Dupont ou Dupont Alain<br><br>
                Exemple n°2 : Chercher des dates de fréquentation<br>
                On peut taper : 01/01/2024 ou 01/2024
            </div>
        </div>

        <div class="help-section">
            <h2>Utilisation du tableau de bord</h2>
            <ul>
                <li>Le tableau de bord affiche l'état en temps réel des 8 postes informatiques</li>
                <li>Les postes sont mis à jour automatiquement toutes les 5 secondes</li>
                <li>Cliquez sur un poste pour le gérer (attribuer à un usager, libérer, etc.)</li>
                <li>Les couleurs indiquent l'état : <span style="color: #28a745;">Vert = En cours</span>, <span style="color: #dc3545;">Rouge = Terminé</span>, <span style="color: #6c757d;">Gris = Libre</span></li>
            </ul>
        </div>

        <div class="help-section">
            <h2>Enregistrement d'une inscription</h2>
            <ol>
                <li>Sélectionnez le site (BAC ou MAC)</li>
                <li>Cliquez sur "Inscriptions"</li>
                <li>Cliquez sur "Nouvelle inscription"</li>
                <li>Remplissez le formulaire avec les informations de l'usager</li>
                <li>Sélectionnez l'agent référent</li>
                <li>Validez l'inscription</li>
            </ol>
        </div>

        <div class="help-section">
            <h2>Enregistrement d'une fréquentation</h2>
            <ol>
                <li>Sélectionnez le site</li>
                <li>Cliquez sur "Fréquentations"</li>
                <li>Cliquez sur "Nouvelle fréquentation"</li>
                <li>Sélectionnez ou recherchez l'usager</li>
                <li>Choisissez le poste informatique</li>
                <li>Sélectionnez le type d'utilisation (France Travail, CAF, etc.)</li>
                <li>Enregistrez l'heure d'entrée et de sortie</li>
            </ol>
        </div>

        <div class="help-section">
            <h2>Gestion des ateliers</h2>
            <ul>
                <li>Les ateliers permettent d'enregistrer les formations dispensées</li>
                <li>Vous pouvez filtrer par date ou par période</li>
                <li>Chaque atelier peut être associé à un formateur</li>
            </ul>
        </div>

        <div class="help-section">
            <h2>Horaires d'ouverture</h2>
            <ul>
                <li><strong>Lundi, Mardi, Mercredi :</strong> 07:40 - 12:30 (matin) / 14:00 - 16:30 (après-midi)</li>
                <li><strong>Jeudi, Vendredi :</strong> 07:40 - 13:30 (matin uniquement)</li>
                <li><strong>Samedi :</strong> 09:00 - 12:00 (matin uniquement)</li>
                <li><strong>Dimanche :</strong> Fermé</li>
            </ul>
        </div>

        <div class="help-section">
            <h2>Contact</h2>
            <p>Pour toute question ou assistance :</p>
            <ul>
                <li><strong>Email :</strong> t.theotiste@gmail.com</li>
                <li><strong>Site BAC :</strong> Chemin Tantan, 97224 Ducos</li>
                <li><strong>Site MAC :</strong> 34 Rue Zizine et des Etages, 97224 Ducos</li>
            </ul>
        </div>
    </div>
</body>
</html>

