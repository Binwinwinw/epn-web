<?php
/**
 * Page: Ajouter/Modifier une fréquentation
 * Formulaire sécurisé avec CSRF tokens et validation côté client
 */

require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../src/includes/auth.php';
require_once __DIR__ . '/../src/includes/rbac.php';
require_once __DIR__ . '/../src/includes/form-helpers.php';

// Vérifier authentification et permission
if (!isUserAuthenticated()) {
    header('Location: ?page=login');
    exit;
}
requirePermission('frequentation:create');

$frequentation = null;
$isEdit = false;

// Si ID fourni, charger la fréquentation existante
if (isset($_GET['id'])) {
    requirePermission('frequentation:update');
    $isEdit = true;
    $id = (int)$_GET['id'];
    $site = getCurrentSite();
    $table = getTableForSite('frequentation', $site);
    
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE NUM_FREQUENTATION = ?");
    $stmt->execute([$id]);
    $frequentation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$frequentation) {
        echo alert('Fréquentation non trouvée', 'danger');
        $frequentation = null;
        $isEdit = false;
    }
}

$pageTitle = $isEdit ? 'Modifier une fréquentation' : 'Ajouter une fréquentation';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - EPN Web</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 2rem 0; }
        .container { max-width: 900px; }
        .card { border: none; border-radius: 10px; }
        .card-header { border-radius: 10px 10px 0 0; }
        .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25); }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
        .btn-primary:hover { background: linear-gradient(135deg, #764ba2 0%, #667eea 100%); }
        .form-group label { font-weight: 500; color: #333; }
        .required::after { content: " *"; color: red; }
        .success-message { display: none; }
    </style>
</head>
<body>
<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="text-white mb-0"><?= htmlspecialchars($pageTitle) ?></h1>
            <p class="text-white-50">Remplissez le formulaire ci-dessous</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div id="success-alert" class="alert alert-success alert-dismissible fade show success-message" role="alert">
                <strong>Succès !</strong> <span id="success-message"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            
            <div id="error-alert" class="alert alert-danger alert-dismissible fade show" style="display: none;" role="alert">
                <strong>Erreur !</strong> <span id="error-message"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            
            <form id="frequentation-form" class="card shadow-lg p-4">
                <input type="hidden" name="csrf_token" value="<?= h(getCSRFToken()); ?>">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="num_frequentation" value="<?= (int)$frequentation['NUM_FREQUENTATION'] ?>">
                <?php endif; ?>
                
                <h5 class="mb-4">Informations personnelles</h5>
                
                <div class="row">
                    <div class="col-md-6">
                        <?php echo formDate('date_frequentation', 'Date de fréquentation', $frequentation['DATE_FREQUENTATION'] ?? '', true); ?>
                    </div>
                    <div class="col-md-6">
                        <?php echo formSelect('espace_frequente', 'Espace fréquenté', 
                            ['Accueil' => 'Accueil', 'Espace numérique' => 'Espace numérique', 'Espace jeunes' => 'Espace jeunes', 'Espace enfants' => 'Espace enfants'],
                            $frequentation['ESPACE_FREQUENTE'] ?? '', true); ?>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-3">
                        <?php echo formSelect('civilite', 'Civilité', 
                            ['M' => 'M', 'Mme' => 'Mme', 'Mlle' => 'Mlle', 'Dr' => 'Dr'],
                            $frequentation['CIVILITE'] ?? '', true); ?>
                    </div>
                    <div class="col-md-9">
                        <?php echo formInput('noms_prenoms', 'Noms et Prénoms', $frequentation['NOMS_PRENOMS'] ?? '', true, 'text', '', 255); ?>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <?php echo formTime('heure_entree', 'Heure d\'entrée', $frequentation['HEURE_ENTREE'] ?? ''); ?>
                    </div>
                    <div class="col-md-4">
                        <?php echo formTime('heure_sortie', 'Heure de sortie', $frequentation['HEURE_SORTIE'] ?? ''); ?>
                    </div>
                    <div class="col-md-4">
                        <?php echo formInput('nombre_copies', 'Nombre de copies', $frequentation['NOMBRE_COPIES'] ?? 0, false, 'number'); ?>
                    </div>
                </div>
                
                <h5 class="mb-3 mt-4">Équipements utilisés</h5>
                
                <div class="row">
                    <div class="col-md-6">
                        <?php echo formSelect('casque', 'Casque audio', 
                            ['Oui' => 'Oui', 'Non' => 'Non'],
                            $frequentation['CASQUE'] ?? ''); ?>
                    </div>
                    <div class="col-md-6">
                        <?php echo formSelect('webcam', 'Webcam', 
                            ['Oui' => 'Oui', 'Non' => 'Non'],
                            $frequentation['WEBCAM'] ?? ''); ?>
                    </div>
                </div>
                
                <h5 class="mb-3 mt-4">Notes</h5>
                
                <?php echo formTextarea('observations', 'Observations', $frequentation['OBSERVATIONS'] ?? '', false, 4); ?>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <a href="?page=frequentations" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary px-5">
                        <?= $isEdit ? 'Mettre à jour' : 'Créer' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('frequentation-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch('/epn-web/src/api/frequentation.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('success-message').textContent = result.message;
            document.getElementById('success-alert').style.display = 'block';
            setTimeout(() => window.location.href = '?page=frequentations', 2000);
        } else {
            document.getElementById('error-message').textContent = result.message || 'Erreur inconnue';
            document.getElementById('error-alert').style.display = 'block';
        }
    } catch (error) {
        document.getElementById('error-message').textContent = error.message;
        document.getElementById('error-alert').style.display = 'block';
    }
});
</script>
</body>
</html>
