<?php
/**
 * Test de Connexion EPN Web
 * Page de test interactive pour vérifier l'authentification
 */

// Configuration
$isLocalhost = ($_SERVER['HTTP_HOST'] === 'localhost:8888' || $_SERVER['HTTP_HOST'] === 'localhost');
$apiUrl = ($isLocalhost) ? 'http://localhost:8888/src/api/auth.php' : 'https://' . $_SERVER['HTTP_HOST'] . '/src/api/auth.php';
$basePath = dirname($_SERVER['REQUEST_URI']);

$loginResult = null;
$loginError = null;

// Traiter le formulaire de test si POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        $loginError = '❌ Veuillez remplir tous les champs';
    } else {
        // Simuler une requête vers l'API auth
        $ch = curl_init($apiUrl . '?action=login');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'username' => $username,
            'password' => $password
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            $loginError = "❌ Erreur de connexion: $curlError";
        } elseif ($response) {
            $loginResult = json_decode($response, true);
            if (!$loginResult) {
                $loginError = "❌ Réponse invalide de l'API (HTTP $httpCode)";
            }
        } else {
            $loginError = "❌ Pas de réponse de l'API (HTTP $httpCode)";
        }
    }
}

// Utilisateurs de démo
$demoUsers = [
    ['username' => 'admin', 'password' => 'changeme123', 'role' => 'Admin', 'sites' => 'tous les sites'],
    ['username' => 'referent1', 'password' => 'password123', 'role' => 'Référent', 'sites' => 'site BAC'],
    ['username' => 'referent2', 'password' => 'password123', 'role' => 'Référent', 'sites' => 'site MAC'],
    ['username' => 'agent1', 'password' => 'password123', 'role' => 'Agent', 'sites' => 'lecture seule'],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Login - EPN Web</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 100%;
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .test-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        .test-section h2 {
            font-size: 16px;
            color: #667eea;
            margin-top: 0;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        input, button {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }
        button:hover {
            opacity: 0.9;
        }
        .message {
            padding: 12px;
            border-radius: 5px;
            margin-top: 15px;
            display: none;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            display: block;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            display: block;
        }
        .demo-users {
            background: #e7f3ff;
            padding: 15px;
            border-left: 4px solid #2196F3;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .demo-users h3 {
            margin: 0 0 10px 0;
            color: #1976D2;
            font-size: 14px;
        }
        .demo-users ul {
            margin: 0;
            padding-left: 20px;
            font-size: 13px;
        }
        .demo-users li {
            margin: 5px 0;
            color: #333;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        .result-json {
            background: #1e1e1e;
            color: #00ff00;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
            overflow-x: auto;
            max-height: 300px;
            overflow-y: auto;
        }
        .result-json .key { color: #9cdcfe; }
        .result-json .string { color: #ce9178; }
        .result-json .number { color: #b5cea8; }
        .result-json .bool { color: #569cd6; }
        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 13px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>🔐 Test de Connexion EPN Web</h1>
    
    <?php if ($loginResult): ?>
    <div class="alert alert-success">
        <strong>✅ Connexion réussie!</strong>
        <pre class="result-json"><?php echo htmlspecialchars(json_encode($loginResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?></pre>
    </div>
    <?php elseif ($loginError): ?>
    <div class="alert alert-error">
        <?php echo htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8'); ?>
    </div>
    <?php endif; ?>
    
    <div class="demo-users">
        <h3>📝 Utilisateurs de Démo Disponibles:</h3>
        <ul>
            <?php foreach ($demoUsers as $user): ?>
            <li>
                <code><?php echo htmlspecialchars($user['username']); ?></code> / 
                <code><?php echo htmlspecialchars($user['password']); ?></code> 
                (<?php echo htmlspecialchars($user['role']); ?> - <?php echo htmlspecialchars($user['sites']); ?>)
            </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Test 1: Formulaire standard HTML/PHP -->
    <div class="test-section">
        <h2>Test 1: Formulaire Standard (POST direct)</h2>
        <form method="POST">
            <input type="hidden" name="test_login" value="1">
            <input type="text" name="username" placeholder="Nom d'utilisateur" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : 'admin'; ?>">
            <input type="password" name="password" placeholder="Mot de passe" required value="<?php echo isset($_POST['password']) ? htmlspecialchars($_POST['password']) : 'changeme123'; ?>">
            <button type="submit">✅ Se connecter (Test Direct)</button>
        </form>
    </div>

    <!-- Test 2: Requête AJAX avec curl affichage -->
    <div class="test-section">
        <h2>Test 2: Test API Direct (curl)</h2>
        <p>Exécutez dans votre terminal:</p>
        <pre style="background: #333; color: #0f0; padding: 10px; border-radius: 5px; overflow-x: auto; font-size: 12px;">curl -X POST http://localhost:8888/src/api/auth.php?action=login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"changeme123"}'</pre>
    </div>

    <!-- Test 3: Via navigateur en POST JSON -->
    <div class="test-section">
        <h2>Test 3: Test API JSON (cURL/Postman)</h2>
        <p style="font-size: 13px; color: #666;">
            POST Request:
        </p>
        <pre style="background: #f0f0f0; padding: 10px; border-radius: 5px; font-size: 12px;">
URL: <?php echo htmlspecialchars($apiUrl); ?>
Headers: Content-Type: application/json
Body: {
  "username": "admin",
  "password": "changeme123"
}</pre>
    </div>

    <div class="test-section">
        <h2>✅ Accès à l'Application</h2>
        <p style="margin: 0; font-size: 14px;">
            Une fois connecté, accédez à:
        </p>
        <p style="margin: 10px 0 0 0; font-size: 13px;">
            <a href="http://localhost:8888/public/index.php?page=accueil" style="color: #667eea; text-decoration: none;">
                http://localhost:8888/public/index.php?page=accueil
            </a>
        </p>
    </div>

    <div class="test-section" style="background: #fff3cd; border-left: 4px solid #ffc107;">
        <h3 style="margin-top: 0; color: #856404;">📌 Informations Système</h3>
        <ul style="margin: 10px 0; padding-left: 20px; font-size: 13px;">
            <li>PHP Version: <code><?php echo phpversion(); ?></code></li>
            <li>Host: <code><?php echo htmlspecialchars($_SERVER['HTTP_HOST']); ?></code></li>
            <li>API URL: <code><?php echo htmlspecialchars($apiUrl); ?></code></li>
            <li>cURL Disponible: <code><?php echo extension_loaded('curl') ? '✅ Oui' : '❌ Non'; ?></code></li>
        </ul>
    </div>
</div>

<script>
// Test rapide de la requête POST
document.querySelector('form').addEventListener('submit', async (e) => {
    console.log('Formulaire test de login envoyé');
});
</script>
</body>
</html>
