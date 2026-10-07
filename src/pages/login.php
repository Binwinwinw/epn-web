<?php
/**
 * Page de login - Accessible sans authentification
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Si déjà connecté, rediriger vers accueil
if (isUserAuthenticated()) {
    header('Location: ' . API_BASE . '?page=accueil');
    exit;
}

// Récupérer le token CSRF
$csrfToken = generateCSRFToken();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - EPN Web</title>
    <link rel="stylesheet" href="/epn-web/public/assets/css/style.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(160deg, #1e3a8a 0%, #2563eb 100%);
            font-family: Inter, 'Segoe UI', system-ui, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 24px rgba(15, 23, 42, 0.18);
            width: 100%;
            max-width: 400px;
            padding: 40px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header h1 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 28px;
        }

        .login-header p {
            color: #999;
            margin: 0;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .form-group input[type="checkbox"] {
            width: auto;
            margin-right: 8px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s ease;
        }

        .submit-btn:hover {
            background: #1d4ed8;
        }

        .submit-btn:active {
            background: #1e40af;
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: none;
            font-size: 14px;
        }

        .success-message {
            background: #efe;
            color: #3c3;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: none;
            font-size: 14px;
        }

        .loading {
            display: none;
            text-align: center;
            color: #999;
            font-size: 14px;
            margin-top: 20px;
        }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #2563eb;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            margin-right: 8px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #999;
        }

        .demo-credentials {
            background: #f5f5f5;
            padding: 12px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }

        .demo-credentials p {
            margin: 0 0 5px 0;
        }

        .demo-credentials code {
            background: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>EPN Web</h1>
            <p>Gestion des EPN</p>
        </div>

        <div class="error-message" id="errorMessage"></div>
        <div class="success-message" id="successMessage"></div>

        <form id="loginForm" onsubmit="handleLogin(event)">
            <div class="form-group">
                <label for="username">Utilisateur</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    required
                    autofocus
                    placeholder="Entrez votre nom d'utilisateur"
                >
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    placeholder="Entrez votre mot de passe"
                >
            </div>

            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember" style="margin: 0; font-weight: 400;">Se souvenir de moi</label>
            </div>

            <button type="submit" class="submit-btn">Connexion</button>

            <div class="loading" id="loading">
                <div class="spinner"></div>
                Connexion en cours...
            </div>
        </form>

        <div class="demo-credentials">
            <p><strong>Accès de démonstration :</strong></p>
            <p>Utilisateur: <code>admin</code></p>
            <p>Mot de passe: <code>changeme123</code></p>
            <p style="color: #c33; margin-top: 8px;">⚠️ Changer le mot de passe après le premier login</p>
        </div>

        <div class="footer">
            <p>EPN Web - Gestion des Espaces Publics Numériques</p>
            <p style="margin-top: 5px;">© 2024-2026</p>
        </div>
    </div>

    <script>
        async function handleLogin(event) {
            event.preventDefault();

            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const remember = document.getElementById('remember').checked;
            const errorDiv = document.getElementById('errorMessage');
            const successDiv = document.getElementById('successMessage');
            const loadingDiv = document.getElementById('loading');

            // Réinitialiser les messages
            errorDiv.style.display = 'none';
            successDiv.style.display = 'none';
            loadingDiv.style.display = 'block';

            try {
                const response = await fetch('<?= API_BASE ?>/src/api/auth.php?action=login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    credentials: 'include',
                    body: JSON.stringify({ username, password })
                });

                const data = await response.json();
                loadingDiv.style.display = 'none';

                if (!response.ok || !data.success) {
                    errorDiv.textContent = data.message || 'Connexion échouée';
                    errorDiv.style.display = 'block';
                    document.getElementById('password').value = '';
                    return;
                }

                // Connexion réussie
                successDiv.textContent = 'Connexion réussie, redirection...';
                successDiv.style.display = 'block';

                if (remember) {
                    localStorage.setItem('epn_username', username);
                }

                // Rediriger vers l'accueil
                setTimeout(() => {
                    window.location.href = '<?= API_BASE ?>?page=accueil';
                }, 500);

            } catch (error) {
                loadingDiv.style.display = 'none';
                errorDiv.textContent = 'Erreur réseau: ' + error.message;
                errorDiv.style.display = 'block';
                console.error('Login error:', error);
            }
        }

        // Pré-remplir le nom d'utilisateur si disponible
        window.addEventListener('DOMContentLoaded', () => {
            const savedUsername = localStorage.getItem('epn_username');
            if (savedUsername) {
                document.getElementById('username').value = savedUsername;
                document.getElementById('remember').checked = true;
                document.getElementById('password').focus();
            }
        });

        // Appuyer sur Entrée pour envoyer
        document.getElementById('password').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                document.getElementById('loginForm').dispatchEvent(new Event('submit'));
            }
        });
    </script>
</body>
</html>
