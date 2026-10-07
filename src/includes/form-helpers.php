<?php
/**
 * Helpers pour formulaires sécurisés avec CSRF tokens
 */

/**
 * Afficher un formulaire avec CSRF token intégré
 * 
 * @param string $action URL d'action
 * @param string $method POST|GET
 * @param string $class Classes CSS optionnelles
 * @param string $content Contenu HTML du formulaire
 * @return string HTML du formulaire
 */
function secureFormStart($action, $method = 'POST', $class = '', $id = '') {
    $token = getCSRFToken();
    $idAttr = $id ? " id=\"$id\"" : '';
    $classAttr = $class ? " class=\"$class\"" : '';
    
    return '
    <form action="' . htmlspecialchars($action) . '" method="' . htmlspecialchars($method) . '"' . $classAttr . $idAttr . ' novalidate>
        <input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">
    ';
}

/**
 * Fermer un formulaire
 */
function secureFormEnd() {
    return '</form>';
}

/**
 * Champ texte sécurisé avec validation
 */
function formInput($name, $label, $value = '', $required = false, $type = 'text', $pattern = '', $maxlength = '') {
    $requiredAttr = $required ? ' required' : '';
    $patternAttr = $pattern ? " pattern=\"$pattern\"" : '';
    $maxAttr = $maxlength ? " maxlength=\"$maxlength\"" : '';
    $id = htmlspecialchars($name);
    $value = htmlspecialchars($value);
    
    return '
    <div class="form-group mb-3">
        <label for="' . $id . '" class="form-label">' . htmlspecialchars($label) . '</label>
        <input 
            type="' . htmlspecialchars($type) . '" 
            class="form-control" 
            id="' . $id . '" 
            name="' . $id . '" 
            value="' . $value . '"' . $requiredAttr . $patternAttr . $maxAttr . '>
        <small class="form-text text-muted"></small>
    </div>
    ';
}

/**
 * Champ date sécurisé
 */
function formDate($name, $label, $value = '', $required = false) {
    $requiredAttr = $required ? ' required' : '';
    $id = htmlspecialchars($name);
    $value = htmlspecialchars($value);
    
    return '
    <div class="form-group mb-3">
        <label for="' . $id . '" class="form-label">' . htmlspecialchars($label) . '</label>
        <input 
            type="date" 
            class="form-control" 
            id="' . $id . '" 
            name="' . $id . '" 
            value="' . $value . '"' . $requiredAttr . '>
    </div>
    ';
}

/**
 * Champ email sécurisé
 */
function formEmail($name, $label, $value = '', $required = false) {
    return formInput($name, $label, $value, $required, 'email', '', 255);
}

/**
 * Champ time sécurisé
 */
function formTime($name, $label, $value = '', $required = false) {
    $requiredAttr = $required ? ' required' : '';
    $id = htmlspecialchars($name);
    $value = htmlspecialchars($value);
    
    return '
    <div class="form-group mb-3">
        <label for="' . $id . '" class="form-label">' . htmlspecialchars($label) . '</label>
        <input 
            type="time" 
            class="form-control" 
            id="' . $id . '" 
            name="' . $id . '" 
            value="' . $value . '"' . $requiredAttr . '>
    </div>
    ';
}

/**
 * Champ sélect sécurisé
 */
function formSelect($name, $label, $options, $selected = '', $required = false) {
    $requiredAttr = $required ? ' required' : '';
    $id = htmlspecialchars($name);
    $html = '
    <div class="form-group mb-3">
        <label for="' . $id . '" class="form-label">' . htmlspecialchars($label) . '</label>
        <select class="form-control" id="' . $id . '" name="' . $id . '"' . $requiredAttr . '>
            <option value="">Sélectionner...</option>
    ';
    
    foreach ($options as $key => $value) {
        $selectedAttr = ($key === $selected) ? ' selected' : '';
        $html .= '<option value="' . htmlspecialchars($key) . '"' . $selectedAttr . '>' . htmlspecialchars($value) . '</option>';
    }
    
    $html .= '
        </select>
    </div>
    ';
    
    return $html;
}

/**
 * Champ textarea sécurisé
 */
function formTextarea($name, $label, $value = '', $required = false, $rows = 4) {
    $requiredAttr = $required ? ' required' : '';
    $id = htmlspecialchars($name);
    $value = htmlspecialchars($value);
    
    return '
    <div class="form-group mb-3">
        <label for="' . $id . '" class="form-label">' . htmlspecialchars($label) . '</label>
        <textarea 
            class="form-control" 
            id="' . $id . '" 
            name="' . $id . '" 
            rows="' . (int)$rows . '"' . $requiredAttr . '>' . $value . '</textarea>
    </div>
    ';
}

/**
 * Bouton de soumission
 */
function formSubmit($label = 'Envoyer', $class = 'btn-primary') {
    return '
    <div class="form-group">
        <button type="submit" class="btn ' . htmlspecialchars($class) . '">' . htmlspecialchars($label) . '</button>
    </div>
    ';
}

/**
 * Card layout
 */
function card($title, $content, $footer = '') {
    $footerHtml = $footer ? '<div class="card-footer bg-light">' . $footer . '</div>' : '';
    
    return '
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">' . htmlspecialchars($title) . '</h5>
        </div>
        <div class="card-body">
            ' . $content . '
        </div>
        ' . $footerHtml . '
    </div>
    ';
}

/**
 * Alert HTML
 */
function alert($message, $type = 'info') {
    $classes = [
        'info' => 'alert-info',
        'success' => 'alert-success',
        'warning' => 'alert-warning',
        'danger' => 'alert-danger'
    ];
    $class = $classes[$type] ?? 'alert-info';
    
    return '
    <div class="alert ' . $class . ' alert-dismissible fade show" role="alert">
        ' . htmlspecialchars($message) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    ';
}

?>
