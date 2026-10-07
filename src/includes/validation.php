<?php
/**
 * Utilitaires de validation et sanitization pour EPN Web
 * 
 * Fournit des fonctions de validation stricte pour tous les types de données
 */

/**
 * Valide un email
 */
function validateEmail($email) {
    $email = trim($email);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new ValidationException("Email invalide: $email");
    }
    return $email;
}

/**
 * Valide une date au format YYYY-MM-DD
 */
function validateDate($date) {
    $date = trim($date);
    if (empty($date)) {
        return null;
    }
    
    // Vérifier le format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        throw new ValidationException("Format de date invalide: $date (utilisez YYYY-MM-DD)");
    }
    
    // Vérifier que la date est valide
    $parts = explode('-', $date);
    if (!checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0])) {
        throw new ValidationException("Date invalide: $date");
    }
    
    return $date;
}

/**
 * Valide une heure au format HH:MM ou HH:MM:SS
 */
function validateTime($time) {
    $time = trim($time);
    if (empty($time)) {
        return null;
    }
    
    if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $time)) {
        throw new ValidationException("Format d'heure invalide: $time (utilisez HH:MM ou HH:MM:SS)");
    }
    
    return $time;
}

/**
 * Valide une chaîne contre une liste d'énumérations
 */
function validateEnum($value, $validValues, $fieldName = 'champ') {
    $value = trim($value);
    if (empty($validValues)) {
        throw new ValidationException("Configuration d'énumération vide");
    }
    
    if (!in_array($value, $validValues, true)) {
        throw new ValidationException(
            "$fieldName invalide: '$value'. Valeurs autorisées: " . 
            implode(', ', $validValues)
        );
    }
    
    return $value;
}

/**
 * Valide une chaîne de caractères
 */
function validateString($value, $minLength = 0, $maxLength = 255, $fieldName = 'champ') {
    if (is_null($value)) {
        if ($minLength > 0) {
            throw new ValidationException("$fieldName est requis");
        }
        return '';
    }
    
    $value = trim((string)$value);
    $length = strlen($value);
    
    if ($minLength > 0 && $length < $minLength) {
        throw new ValidationException("$fieldName doit contenir au moins $minLength caractère(s)");
    }
    
    if ($length > $maxLength) {
        throw new ValidationException("$fieldName ne peut pas dépasser $maxLength caractère(s)");
    }
    
    return $value;
}

/**
 * Valide un entier
 */
function validateInteger($value, $min = null, $max = null, $fieldName = 'champ') {
    if (is_null($value) || $value === '') {
        return null;
    }
    
    $intValue = filter_var($value, FILTER_VALIDATE_INT);
    if ($intValue === false) {
        throw new ValidationException("$fieldName doit être un nombre entier");
    }
    
    if ($min !== null && $intValue < $min) {
        throw new ValidationException("$fieldName doit être >= $min");
    }
    
    if ($max !== null && $intValue > $max) {
        throw new ValidationException("$fieldName doit être <= $max");
    }
    
    return $intValue;
}

/**
 * Valide un nombre décimal
 */
function validateFloat($value, $min = null, $max = null, $fieldName = 'champ') {
    if (is_null($value) || $value === '') {
        return null;
    }
    
    $floatValue = filter_var($value, FILTER_VALIDATE_FLOAT);
    if ($floatValue === false) {
        throw new ValidationException("$fieldName doit être un nombre");
    }
    
    if ($min !== null && $floatValue < $min) {
        throw new ValidationException("$fieldName doit être >= $min");
    }
    
    if ($max !== null && $floatValue > $max) {
        throw new ValidationException("$fieldName doit être <= $max");
    }
    
    return $floatValue;
}

/**
 * Valide un booléen
 */
function validateBoolean($value) {
    if (is_bool($value)) {
        return $value;
    }
    
    $value = strtolower(trim((string)$value));
    if (in_array($value, ['1', 'true', 'yes', 'on'], true)) {
        return true;
    }
    if (in_array($value, ['0', 'false', 'no', 'off'], true)) {
        return false;
    }
    
    throw new ValidationException("Valeur booléenne invalide");
}

/**
 * Valide une URL
 */
function validateUrl($url) {
    $url = trim($url);
    if (empty($url)) {
        return '';
    }
    
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        throw new ValidationException("URL invalide: $url");
    }
    
    return $url;
}

/**
 * Valide un numéro de téléphone (format simplifié)
 */
function validatePhoneNumber($phone) {
    $phone = trim($phone);
    if (empty($phone)) {
        return '';
    }
    
    // Supprimer les espaces et tirets
    $phone = preg_replace('/[\s\-\.]/', '', $phone);
    
    // Vérifier qu'il ne reste que des chiffres et un + optionnel
    if (!preg_match('/^\+?[0-9]{7,15}$/', $phone)) {
        throw new ValidationException("Numéro de téléphone invalide: $phone");
    }
    
    return $phone;
}

/**
 * Valide un code postal (France)
 */
function validatePostalCode($code) {
    $code = trim($code);
    if (empty($code)) {
        return '';
    }
    
    if (!preg_match('/^[0-9]{5}$/', $code)) {
        throw new ValidationException("Code postal invalide: $code");
    }
    
    return $code;
}

/**
 * Valide une liste de valeurs séparées par des virgules
 */
function validateCSV($value, $separator = ',') {
    if (empty($value)) {
        return [];
    }
    
    $values = array_map('trim', explode($separator, $value));
    return array_filter($values, fn($v) => !empty($v));
}

/**
 * Exception personnalisée pour les erreurs de validation
 */
class ValidationException extends Exception {
    public function __construct($message = "", $code = 0) {
        parent::__construct($message, $code);
    }
}

?>
