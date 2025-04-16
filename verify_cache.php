<?php
/**
 * Script de vérification d'authenticité du cache
 * Vérifie que les données utilisées sont réelles et à jour
 */

// Inclure les classes nécessaires
require_once __DIR__ . '/src/class/DataCache.php';
require_once __DIR__ . '/src/class/TirageVerifier.php';

// Définir les variables globales
$password = "nirvana"; // Valeur de src/startup.php
$h = sha1($password.'migo'); // Hash pour l'authentification

// Vérifier si le script est exécuté en ligne de commande
$isCli = php_sapi_name() === 'cli';
$outputFormat = $isCli ? "CLI" : "HTML";

// Fonction pour formater la sortie
function output($message, $type = 'info') {
    global $outputFormat;
    
    if ($outputFormat === "CLI") {
        $prefix = match($type) {
            'success' => "\033[32m[SUCCÈS]\033[0m ",
            'error' => "\033[31m[ERREUR]\033[0m ",
            'warning' => "\033[33m[AVERT.]\033[0m ",
            default => "\033[34m[INFO]\033[0m "
        };
        echo $prefix . $message . PHP_EOL;
    } else {
        $class = match($type) {
            'success' => "alert-success",
            'error' => "alert-danger",
            'warning' => "alert-warning",
            default => "alert-info"
        };
        echo "<div class='alert $class'>" . htmlspecialchars($message) . "</div>";
    }
}

// En-tête HTML si nécessaire
if ($outputFormat === "HTML") {
    echo "<!DOCTYPE html>
    <html lang='fr'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Vérification du cache - Amélie</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    </head>
    <body class='p-3'>
    <h1>Vérification d'authenticité du cache</h1>";
}

// Initialiser le cache
$cache = new DataCache();

// Fichiers de cache à vérifier
$cacheFiles = [
    'recent_tirages' => 'Données des tirages récents',
    'historical_tirages_1000' => 'Données des 1000 derniers tirages',
    'extended_historical_data' => 'Données historiques étendues'
];

$allAuthentic = true;
$hasIssues = false;

foreach ($cacheFiles as $cacheKey => $description) {
    // Vérifier si le cache existe
    if (!$cache->has($cacheKey)) {
        output("Le cache '$cacheKey' ($description) n'existe pas ou a expiré.", 'error');
        $hasIssues = true;
        continue;
    }
    
    // Récupérer les données du cache
    $cacheData = $cache->get($cacheKey);
    
    // Vérifier l'authenticité des données
    $authInfo = TirageVerifier::verifyData($cacheData);
    
    if (!$authInfo['isAuthentic']) {
        output("⚠️ $description : NON AUTHENTIQUE - " . $authInfo['reason'], 'error');
        $allAuthentic = false;
        $hasIssues = true;
    } else {
        $source = isset($authInfo['source']) ? $authInfo['source'] : 'Non spécifiée';
        $lastUpdated = isset($cacheData['lastUpdated']) ? $cacheData['lastUpdated'] : (
            isset($cacheData['fetchTime']) ? date('Y-m-d H:i:s', $cacheData['fetchTime']) : 'Inconnue'
        );
        $freshness = time() - ($cacheData['fetchTime'] ?? 0);
        $freshnessHuman = $freshness < 3600 ? round($freshness / 60) . " minutes" : round($freshness / 3600, 1) . " heures";
        
        // Extraire un échantillon des dernières données
        $sampleDisplay = '';
        if (isset($authInfo['sampleData']) && !empty($authInfo['sampleData'])) {
            $sampleNums = array_slice($authInfo['sampleData'], 0, 5);
            $sampleDisplay = ' - Derniers numéros: ' . implode(', ', $sampleNums);
        }
        
        if ($freshness > 86400) {
            output("⚠️ $description : Authentique mais données anciennes ($freshnessHuman) - Source: $source, Dernière mise à jour: $lastUpdated$sampleDisplay", 'warning');
            $hasIssues = true;
        } else {
            output("✅ $description : Authentique - Source: $source, Dernière mise à jour: $lastUpdated ($freshnessHuman)$sampleDisplay", 'success');
        }
    }
}

// Conclusion
if ($allAuthentic && !$hasIssues) {
    output("Toutes les données sont authentiques et à jour.", 'success');
} elseif ($allAuthentic) {
    output("Les données sont authentiques mais certaines peuvent être obsolètes.", 'warning');
} else {
    output("ATTENTION : Certaines données ne sont pas authentiques. Les recommandations peuvent être incorrectes.", 'error');
    if ($isCli) {
        output("Exécutez 'php cron_update_data.php' pour mettre à jour les données avec des sources réelles.", 'info');
    }
}

// Pied de page HTML si nécessaire
if ($outputFormat === "HTML") {
    echo "<div class='mt-4'>
    <a href='index.php' class='btn btn-primary'>Retour à l'accueil</a>
    <a href='cron_update_data.php' class='btn btn-success ms-2'>Mettre à jour les données</a>
    </div>
    </body></html>";
}