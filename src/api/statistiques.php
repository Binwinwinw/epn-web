<?php
/**
 * API pour les statistiques unifiées, les requêtes dynamiques et l'export CSV.
 * 
 * Sécurité:
 * - Authentification requise
 * - Validation stricte des données
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/security-headers.php';

header('Content-Type: application/json');

// Ajouter les headers de sécurité
setSecurityHeaders();

// Vérifier l'authentification
if (!isUserAuthenticated()) {
    if (DEBUG_MODE) {
        simulateAuthForDevelopment();
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentification requise']);
        exit;
    }
}

$action = $_GET['action'] ?? 'dashboard';

function sendJsonResponse(array $payload, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function lowerText(string $value): string {
    return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
}

function upperText(string $value): string {
    return function_exists('mb_strtoupper') ? mb_strtoupper($value, 'UTF-8') : strtoupper($value);
}

function cleanLabel(?string $value, string $fallback = 'Non renseigné'): string {
    $value = trim((string) $value);
    return $value !== '' ? $value : $fallback;
}

function parseStoredDate(?string $value): ?DateTimeImmutable {
    $value = trim((string) $value);
    if ($value === '') {
        return null;
    }

    $candidates = [$value];

    if (preg_match('/\d{2}\/\d{2}\/\d{4}/', $value, $matches)) {
        $candidates[] = $matches[0];
    }
    if (preg_match('/\d{4}-\d{2}-\d{2}/', $value, $matches)) {
        $candidates[] = $matches[0];
    }

    foreach ($candidates as $candidate) {
        foreach (['d/m/Y', 'Y-m-d', 'd-m-Y', 'd/m/y', 'Y/m/d'] as $format) {
            $date = DateTimeImmutable::createFromFormat('!' . $format, $candidate);
            if ($date instanceof DateTimeImmutable) {
                return $date;
            }
        }
    }

    $timestamp = strtotime($value);
    if ($timestamp !== false) {
        return (new DateTimeImmutable('@' . $timestamp))->setTimezone(new DateTimeZone(date_default_timezone_get()));
    }

    return null;
}

function normalizeSiteValue(?string $site): string {
    $site = upperText(trim((string) $site));
    return in_array($site, ['GLOBAL', 'BAC', 'MAC'], true) ? $site : 'GLOBAL';
}

function detectSiteFromText(?string $text): string {
    $text = upperText((string) $text);
    if (str_contains($text, 'BAC')) {
        return 'BAC';
    }
    if (str_contains($text, 'MAC')) {
        return 'MAC';
    }
    return 'GLOBAL';
}

function normalizeGender(?string $civilite): string {
    $value = lowerText(trim((string) $civilite));
    if ($value === '') {
        return 'Non renseigné';
    }
    if (str_contains($value, 'mme') || str_contains($value, 'mlle') || str_contains($value, 'madame')) {
        return 'Femme';
    }
    if ($value === 'm' || $value === 'm.' || str_contains($value, 'monsieur')) {
        return 'Homme';
    }
    return ucfirst($value);
}

function ageGroupFromBirthDate(?string $birthDate): string {
    $date = parseStoredDate($birthDate);
    if (!$date) {
        return 'Non renseigné';
    }

    $age = (new DateTimeImmutable('today'))->diff($date)->y;

    if ($age < 18) {
        return 'Moins de 18 ans';
    }
    if ($age <= 25) {
        return '18 à 25 ans';
    }
    if ($age <= 40) {
        return '26 à 40 ans';
    }
    if ($age <= 60) {
        return '41 à 60 ans';
    }

    return '60 ans et plus';
}

function splitListValues(?string $value): array {
    $value = trim((string) $value);
    if ($value === '') {
        return ['Non renseigné'];
    }

    $parts = preg_split('/\s*(?:,|;|\||\/|\+)\s*/u', $value);
    $values = [];

    foreach ($parts as $part) {
        $part = trim((string) $part);
        if ($part !== '') {
            $values[] = $part;
        }
    }

    return $values ?: ['Non renseigné'];
}

function normalizeNameKey(?string $value): string {
    $value = preg_replace('/\s+/', ' ', trim((string) $value));
    return $value !== '' ? upperText($value) : '';
}

function buildInscriptionLookup(PDO $pdo): array {
    $lookup = [];
    $stmt = $pdo->query('SELECT NOMS_PRENOMS, DATE_NAISSANCE, CODE_VILLE, PAYS, PROFIL, SITUATION, CIVILITE FROM inscription');

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $key = normalizeNameKey($row['NOMS_PRENOMS'] ?? '');
        if ($key === '') {
            continue;
        }

        if (!isset($lookup[$key])) {
            $lookup[$key] = $row;
            continue;
        }

        if (!empty($row['DATE_NAISSANCE']) || !empty($row['CODE_VILLE']) || !empty($row['PAYS'])) {
            $lookup[$key] = array_merge($lookup[$key], $row);
        }
    }

    return $lookup;
}

function fetchAnalyticsRows(PDO $pdo, string $module, string $site): array {
    $params = [];

    if ($module === 'inscriptions') {
        $sql = "
            SELECT
                CASE
                    WHEN UPPER(ESPACE) LIKE '%BAC%' THEN 'BAC'
                    WHEN UPPER(ESPACE) LIKE '%MAC%' THEN 'MAC'
                    ELSE 'GLOBAL'
                END AS SITE,
                DATE_INSCRIPTION AS DATE_EVENT,
                DATE_NAISSANCE,
                CIVILITE,
                NOMS_PRENOMS,
                CODE_VILLE,
                PAYS,
                REFERENT,
                PROFIL,
                SITUATION,
                ESPACE
            FROM vue_inscriptions
        ";

        if ($site !== 'GLOBAL') {
            $sql .= ' WHERE UPPER(ESPACE) LIKE ?';
            $params[] = '%' . $site . '%';
        }
    } elseif ($module === 'ateliers') {
        $sql = '
            SELECT
                SITE,
                DATE_ATELIER AS DATE_EVENT,
                CIVILITE,
                NOMS_PRENOMS,
                CODE_VILLE,
                REFERENT,
                FORMATEUR,
                ATELIER_CHOISI,
                PERIODE_ATELIER,
                STATUT
            FROM vue_ateliers
        ';

        if ($site !== 'GLOBAL') {
            $sql .= ' WHERE SITE = ?';
            $params[] = $site;
        }
    } else {
        $sql = '
            SELECT
                SITE,
                DATE_FREQUENTATION AS DATE_EVENT,
                CIVILITE,
                NOMS_PRENOMS,
                CODE_VILLE,
                REFERENT,
                UTILISATIONS,
                POSTES
            FROM vue_frequentations
        ';

        if ($site !== 'GLOBAL') {
            $sql .= ' WHERE SITE = ?';
            $params[] = $site;
        }
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function keepTopGroups(array $groups, int $limit = 10): array {
    if (count($groups) <= $limit) {
        return $groups;
    }

    $trimmed = [];
    $others = 0;
    $index = 0;

    foreach ($groups as $label => $value) {
        if ($index < ($limit - 1)) {
            $trimmed[$label] = $value;
        } else {
            $others += $value;
        }
        $index++;
    }

    if ($others > 0) {
        $trimmed['Autres'] = $others;
    }

    return $trimmed;
}

function getQueryTitle(string $module, string $query): string {
    $titles = [
        'sexe' => 'Répartition femmes / hommes',
        'tranche_age' => 'Répartition par tranche d\'âge',
        'provenance' => 'Origine et provenance des publics',
        'utilisation' => 'Types d\'utilisation les plus fréquents',
        'referent' => 'Répartition par référent',
        'profil' => 'Répartition par profil',
        'situation' => 'Répartition par situation',
        'atelier' => 'Ateliers les plus sollicités',
        'site' => 'Répartition par site',
        'evolution' => 'Évolution journalière',
    ];

    return $titles[$query] ?? ('Analyse ' . $module);
}

function getChartType(string $query): string {
    if ($query === 'evolution') {
        return 'line';
    }

    if (in_array($query, ['sexe', 'site'], true)) {
        return 'doughnut';
    }

    return 'bar';
}

function getModuleLabel(string $module): string {
    $labels = [
        'frequentations' => 'Fréquentations',
        'inscriptions' => 'Inscriptions',
        'ateliers' => 'Ateliers',
    ];

    return $labels[$module] ?? ucfirst($module);
}

function recordExportHistory(PDO $pdo, string $site, string $module, array $filters): void {
    try {
        $stmt = $pdo->prepare('
            INSERT INTO exports_historique (TYPE_EXPORT, SITE, PERIODE_DEBUT, PERIODE_FIN, FORMAT_EXPORT, FILTRES_JSON)
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            'csv',
            $site,
            $filters['date_debut'],
            $filters['date_fin'],
            $module,
            json_encode($filters, JSON_UNESCAPED_UNICODE),
        ]);
    } catch (Throwable $e) {
        // Historique optionnel : ne pas bloquer l'export si la table est absente.
    }
}

function runAnalytics(PDO $pdo, string $module, string $query, string $site, ?string $dateDebut, ?string $dateFin): array {
    $lookup = buildInscriptionLookup($pdo);
    $rows = fetchAnalyticsRows($pdo, $module, $site);
    $startDate = parseStoredDate($dateDebut) ?: new DateTimeImmutable('-24 months');
    $endDate = parseStoredDate($dateFin) ?: new DateTimeImmutable('today');

    if ($startDate > $endDate) {
        [$startDate, $endDate] = [$endDate, $startDate];
    }

    $groups = [];
    $totalRecords = 0;

    foreach ($rows as $row) {
        $eventDate = parseStoredDate($row['DATE_EVENT'] ?? '');
        if (!$eventDate || $eventDate < $startDate || $eventDate > $endDate) {
            continue;
        }

        $key = normalizeNameKey($row['NOMS_PRENOMS'] ?? '');
        if ($key !== '' && isset($lookup[$key])) {
            $row = array_merge($lookup[$key], $row);
        }

        $labels = [];
        switch ($query) {
            case 'sexe':
                $labels[] = normalizeGender($row['CIVILITE'] ?? '');
                break;

            case 'tranche_age':
                $labels[] = ageGroupFromBirthDate($row['DATE_NAISSANCE'] ?? '');
                break;

            case 'provenance':
                $codeVille = trim((string) ($row['CODE_VILLE'] ?? ''));
                $pays = trim((string) ($row['PAYS'] ?? ''));
                $labels[] = $codeVille !== '' ? $codeVille : ($pays !== '' ? $pays : 'Non renseigné');
                break;

            case 'utilisation':
                $labels = splitListValues($row['UTILISATIONS'] ?? '');
                break;

            case 'profil':
                $labels[] = cleanLabel($row['PROFIL'] ?? '');
                break;

            case 'situation':
                $labels[] = cleanLabel($row['SITUATION'] ?? '');
                break;

            case 'atelier':
                $labels = splitListValues($row['ATELIER_CHOISI'] ?? '');
                break;

            case 'site':
                $labels[] = cleanLabel($row['SITE'] ?? detectSiteFromText($row['ESPACE'] ?? ''), 'GLOBAL');
                break;

            case 'evolution':
                $labels[] = $eventDate->format('d/m/Y');
                break;

            case 'referent':
            default:
                $labels[] = cleanLabel($row['FORMATEUR'] ?? ($row['REFERENT'] ?? ''));
                break;
        }

        $totalRecords++;

        foreach ($labels as $label) {
            $label = cleanLabel($label);
            $groups[$label] = ($groups[$label] ?? 0) + 1;
        }
    }

    if ($query === 'evolution') {
        uksort($groups, static function ($left, $right) {
            $leftDate = parseStoredDate($left) ?: new DateTimeImmutable('today');
            $rightDate = parseStoredDate($right) ?: new DateTimeImmutable('today');
            return $leftDate <=> $rightDate;
        });
    } else {
        arsort($groups);
        $groups = keepTopGroups($groups, 10);
    }

    $totalGroupedValues = array_sum($groups);
    $table = [];

    foreach ($groups as $label => $value) {
        $table[] = [
            'label' => $label,
            'value' => $value,
            'percentage' => $totalGroupedValues > 0 ? round(($value / $totalGroupedValues) * 100, 1) : 0,
        ];
    }

    $insights = [];
    if ($totalRecords === 0) {
        $insights[] = 'Aucune donnée trouvée pour les filtres sélectionnés.';
        $insights[] = 'Essayez d\'élargir la période ou de changer le module.';
    } else {
        $insights[] = $totalRecords . ' enregistrement(s) analysé(s) sur la période.';
        if (isset($table[0])) {
            $insights[] = 'Catégorie dominante : ' . $table[0]['label'] . ' avec ' . $table[0]['value'] . ' occurrence(s).';
        }
        if ($query === 'evolution') {
            $insights[] = 'Le graphique montre la dynamique journalière du module sélectionné.';
        } else {
            $insights[] = 'La requête peut être exportée immédiatement en CSV.';
        }
    }

    return [
        'success' => true,
        'message' => $totalRecords > 0 ? 'Analyse mise à jour en direct.' : 'Aucune donnée disponible pour ces critères.',
        'filters' => [
            'site' => $site,
            'module' => $module,
            'query' => $query,
            'date_debut' => $startDate->format('Y-m-d'),
            'date_fin' => $endDate->format('Y-m-d'),
        ],
        'summary' => [
            'total_records' => $totalRecords,
            'group_total' => $totalGroupedValues,
            'top_label' => $table[0]['label'] ?? 'Aucun résultat',
            'period_label' => $startDate->format('d/m/Y') . ' → ' . $endDate->format('d/m/Y'),
            'updated_at' => date('H:i:s'),
        ],
        'chart' => [
            'title' => getQueryTitle($module, $query),
            'subtitle' => 'Module : ' . getModuleLabel($module) . ' • Site : ' . $site,
            'type' => getChartType($query),
            'labels' => array_column($table, 'label'),
            'values' => array_column($table, 'value'),
        ],
        'table' => $table,
        'insights' => $insights,
    ];
}

try {
    if ($action === 'query' || $action === 'export') {
        $allowedQueries = [
            'frequentations' => ['sexe', 'tranche_age', 'provenance', 'utilisation', 'referent', 'site', 'evolution'],
            'inscriptions' => ['sexe', 'tranche_age', 'provenance', 'profil', 'situation', 'referent', 'site', 'evolution'],
            'ateliers' => ['sexe', 'tranche_age', 'provenance', 'atelier', 'referent', 'site', 'evolution'],
        ];

        $module = $_GET['module'] ?? 'frequentations';
        if (!isset($allowedQueries[$module])) {
            $module = 'frequentations';
        }

        $query = $_GET['query'] ?? $allowedQueries[$module][0];
        if (!in_array($query, $allowedQueries[$module], true)) {
            $query = $allowedQueries[$module][0];
        }

        $site = normalizeSiteValue($_GET['site'] ?? 'GLOBAL');
        $analytics = runAnalytics(
            $pdo,
            $module,
            $query,
            $site,
            $_GET['date_debut'] ?? null,
            $_GET['date_fin'] ?? null
        );

        if ($action === 'export') {
            $filename = 'statistiques-' . $module . '-' . strtolower($site) . '-' . date('Ymd-His') . '.csv';
            recordExportHistory($pdo, $site, $module, $analytics['filters']);

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');
            fwrite($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($output, ['Module', ucfirst($module)], ';');
            fputcsv($output, ['Site', $site], ';');
            fputcsv($output, ['Requête', getQueryTitle($module, $query)], ';');
            fputcsv($output, ['Période', $analytics['summary']['period_label']], ';');
            fputcsv($output, [], ';');
            fputcsv($output, ['Libellé', 'Volume', 'Part (%)'], ';');

            foreach ($analytics['table'] as $row) {
                fputcsv($output, [$row['label'], $row['value'], $row['percentage']], ';');
            }

            fclose($output);
            exit;
        }

        sendJsonResponse($analytics);
    }

    $dateAujourdhui = date('Y-m-d');
    $dateAujourdhuiFormat = date('d/m/Y');

    $stmt = $pdo->prepare("
        SELECT 
            SITE,
            COUNT(*) as total,
            SUM(CASE WHEN STATUT = 'Libre' THEN 1 ELSE 0 END) as libre,
            SUM(CASE WHEN STATUT = 'En cours' THEN 1 ELSE 0 END) as en_cours,
            SUM(CASE WHEN STATUT = 'Terminé' THEN 1 ELSE 0 END) as termine
        FROM postes 
        WHERE DATE_UTILISATION = ?
        GROUP BY SITE
    ");
    $stmt->execute([$dateAujourdhui]);
    $statsPostes = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $statsPostes[$row['SITE']] = $row;
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM frequentation1 WHERE DATE_FREQUENTATION LIKE ?');
    $stmt->execute(["%$dateAujourdhuiFormat%"]);
    $frequentationAujourdhuiBAC = (int) (($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0));

    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM frequentation2 WHERE DATE_FREQUENTATION LIKE ?');
    $stmt->execute(["%$dateAujourdhuiFormat%"]);
    $frequentationAujourdhuiMAC = (int) (($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0));

    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM inscription WHERE DATE_INSCRIPTION LIKE ?');
    $stmt->execute(["%$dateAujourdhuiFormat%"]);
    $inscriptionsAujourdhui = (int) (($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0));

    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM ateliers1 WHERE DATE_ATELIER LIKE ?');
    $stmt->execute(["%$dateAujourdhuiFormat%"]);
    $ateliersAujourdhuiBAC = (int) (($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0));

    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM ateliers2 WHERE DATE_ATELIER LIKE ?');
    $stmt->execute(["%$dateAujourdhuiFormat%"]);
    $ateliersAujourdhuiMAC = (int) (($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0));

    sendJsonResponse([
        'success' => true,
        'date' => $dateAujourdhui,
        'postes' => [
            'BAC' => $statsPostes['BAC'] ?? ['total' => 0, 'libre' => 0, 'en_cours' => 0, 'termine' => 0],
            'MAC' => $statsPostes['MAC'] ?? ['total' => 0, 'libre' => 0, 'en_cours' => 0, 'termine' => 0],
        ],
        'frequentations' => [
            'BAC' => $frequentationAujourdhuiBAC,
            'MAC' => $frequentationAujourdhuiMAC,
            'total' => $frequentationAujourdhuiBAC + $frequentationAujourdhuiMAC,
        ],
        'inscriptions' => [
            'total' => $inscriptionsAujourdhui,
        ],
        'ateliers' => [
            'BAC' => $ateliersAujourdhuiBAC,
            'MAC' => $ateliersAujourdhuiMAC,
            'total' => $ateliersAujourdhuiBAC + $ateliersAujourdhuiMAC,
        ],
    ]);
} catch (Throwable $e) {
    sendJsonResponse([
        'success' => false,
        'message' => $e->getMessage(),
    ], 500);
}

