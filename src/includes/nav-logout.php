<?php
/**
 * Composant de navigation / déconnexion partagé.
 *
 * Usage — footer standard (Retour + Déconnexion) :
 *   <?php renderLogoutFooter($backUrl, $apiBase); ?>
 *
 * Usage — bouton menu isolé pour accueil-site (intégré dans .menu-site) :
 *   <?php renderLogoutMenuBtn(); ?>
 *
 * Usage — script JS seul (lorsque le bouton est dans le HTML parent) :
 *   <?php renderLogoutScript($apiBase); ?>
 *
 * Paramètres :
 *   $backUrl  string  URL du bouton ← Retour (peut être null pour masquer le bouton)
 *   $apiBase  string  Chemin vers src/api (ex : '../src/api')
 */

/**
 * Barre de navigation haut de page : bouton ← Retour à gauche + Déconnexion à droite.
 * $backUrl  : URL de destination du bouton Retour
 * $label    : texte du bouton Retour (défaut « ← Retour »)
 * $apiBase  : chemin vers src/api
 */
function renderRetourTop(string $backUrl, string $label = '← Retour', string $apiBase = '../src/api'): void
{
    $backUrl = htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8');
    $label   = htmlspecialchars($label,   ENT_QUOTES, 'UTF-8');
?>
        <div class="topbar-retour">
            <button class="btn-retour" onclick="window.location.href='<?= $backUrl ?>'">
                <?= $label ?>
            </button>
            <button class="btn-deconnexion" onclick="_epnLogout()">
                Déconnexion
            </button>
        </div>
<?php
    renderLogoutScript($apiBase);
}

/**
 * Footer vide conservé pour compatibilité — ne génère plus de HTML visible.
 * Le bouton Déconnexion est maintenant dans renderRetourTop().
 */
function renderLogoutFooter(string $backUrl, string $apiBase = '../src/api'): void
{
    // Rien à afficher : Déconnexion est dans la topbar.
    // Appelé uniquement si une page n'utilise pas encore renderRetourTop().
    renderLogoutScript($apiBase);
}

/**
 * Bouton menu-site (s'insère dans .menu-site, style btn-menu).
 */
function renderLogoutMenuBtn(): void
{
?>
            <button class="btn-menu btn-deconnexion" onclick="_epnLogout()">
                <span class="icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><polyline points="16 17 21 12 16 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><line x1="21" y1="12" x2="9" y2="12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span>
                <span>Déconnexion</span>
            </button>
<?php
}

/**
 * Bloc <script> contenant uniquement la fonction _epnLogout().
 * Appelé automatiquement par renderLogoutFooter().
 * Appelez-le manuellement si vous utilisez renderLogoutMenuBtn() ou le bouton Tailwind.
 */
function renderLogoutScript(string $apiBase = '../src/api'): void
{
    $apiBase = htmlspecialchars($apiBase, ENT_QUOTES, 'UTF-8');
?>
    <script>
        async function _epnLogout() {
            if (!confirm('Voulez-vous vraiment vous déconnecter ?')) {
                return;
            }
            try {
                await fetch('<?= $apiBase ?>/auth.php?action=logout', {
                    method: 'POST',
                    credentials: 'include'
                });
            } catch (e) {
                // Déconnexion locale même si le réseau échoue
            }
            window.location.href = 'index.php?page=landingpage';
        }
    </script>
<?php
}
