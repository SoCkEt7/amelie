<?php
/**
 * Script d'initialisation du cache
 * À exécuter une seule fois pour créer le cache initial
 */

// Inclure les classes nécessaires
require_once __DIR__ . '/src/class/DataCache.php';
require_once __DIR__ . '/src/class/MockHttpClient.php';
require_once __DIR__ . '/src/class/TirageDataFetcher.php';
require_once __DIR__ . '/src/class/TirageStrategies.php';

// Définir les variables globales nécessaires
$password = "nirvana"; // Valeur de src/startup.php
$h = sha1($password.'migo'); // Hash pour l'authentification

// Vérifier si le script est exécuté en ligne de commande
if (php_sapi_name() !== 'cli') {
    die("Ce script doit être exécuté en ligne de commande.");
}

echo "Initialisation du cache (1000 derniers résultats)...\n";

try {
    // Initialiser le système de cache
    $cache = new DataCache();
    
    // Nettoyer le cache existant
    $cache->cleanup();
    echo "Cache nettoyé.\n";
    
    // Initialiser le récupérateur de données
    $dataFetcher = new TirageDataFetcher($cache);
    
    // 1. Récupérer les données récentes (tirages du jour)
    echo "Récupération des données récentes...\n";
    $recentData = $dataFetcher->getRecentTirages();
    
    // Vérifier si les données sont authentiques
    if (isset($recentData['isAuthentic']) && $recentData['isAuthentic']) {
        echo "Données récentes AUTHENTIQUES mises en cache.\n";
    } else {
        echo "AVERTISSEMENT: Les données récentes NE SONT PAS AUTHENTIQUES. Les tirages optimisés peuvent être inexacts.\n";
        echo "Assurez-vous que Goutte\\Client est installé: composer require fabpot/goutte\n";
    }
    
    // 2. Récupérer les données historiques (1000 derniers tirages)
    echo "Récupération des 1000 derniers tirages...\n";
    $historicalData = $dataFetcher->getHistoricalTirages(1000);
    
    // Vérifier si les données sont authentiques
    if (isset($historicalData['isAuthentic']) && $historicalData['isAuthentic']) {
        echo "Données historiques AUTHENTIQUES mises en cache: " . count($historicalData['numbers']) . " tirages.\n";
    } else {
        echo "AVERTISSEMENT: Les données historiques NE SONT PAS AUTHENTIQUES. Les tirages optimisés peuvent être inexacts.\n";
        echo "Les données historiques contiennent " . count($historicalData['numbers']) . " tirages (potentiellement simulés).\n";
    }
    
    // 3. Générer les données historiques étendues
    echo "Génération des données historiques étendues...\n";
    $extendedData = $dataFetcher->getExtendedHistoricalData();
    
    // Vérifier si les données sont authentiques
    if (isset($extendedData['isAuthentic']) && $extendedData['isAuthentic']) {
        echo "Données étendues AUTHENTIQUES mises en cache: " . count($extendedData['numbers']) . " tirages.\n";
    } else {
        echo "AVERTISSEMENT: Les données étendues NE SONT PAS AUTHENTIQUES. Les tirages optimisés peuvent être inexacts.\n";
        echo "Les données étendues contiennent " . count($extendedData['numbers']) . " tirages (potentiellement simulés).\n";
    }
    
    // 4. Précharger les stratégies
    echo "Précalcul des stratégies...\n";
    $strategiesEngine = new TirageStrategies($extendedData, $recentData);
    $strategies = $strategiesEngine->getStrategies();
    echo "Stratégies mises en cache: " . count($strategies) . ".\n";
    
    // Vérifier si toutes les données sont authentiques
    $allAuthentic = (isset($recentData['isAuthentic']) && $recentData['isAuthentic']) &&
                  (isset($historicalData['isAuthentic']) && $historicalData['isAuthentic']) &&
                  (isset($extendedData['isAuthentic']) && $extendedData['isAuthentic']);
    
    echo "\n======================================================\n";
    if ($allAuthentic) {
        echo "✅ Initialisation du cache terminée avec succès!\n";
        echo "✅ TOUTES les données sont AUTHENTIQUES.\n";
        echo "✅ Les tirages optimisés sont basés sur des données RÉELLES.\n";
    } else {
        echo "⚠️ Initialisation du cache terminée avec avertissements!\n";
        echo "⚠️ CERTAINES ou TOUTES les données NE SONT PAS AUTHENTIQUES.\n";
        echo "⚠️ Les tirages optimisés peuvent être basés sur des données SIMULÉES.\n";
        echo "⚠️ Pour obtenir des données authentiques, installez Goutte\\Client:\n";
        echo "   composer require fabpot/goutte\n";
    }
    echo "======================================================\n\n";
    
    echo "Pour maintenir le cache à jour, ajoutez la commande suivante au crontab (2 fois par jour):\n";
    echo "0 */12 * * * php " . __DIR__ . "/cron_update_data.php\n";
    
} catch (Exception $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}