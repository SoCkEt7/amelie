<?php
/**
 * Script de mise à jour des données (exécution manuelle uniquement)
 * N'est plus exécuté automatiquement - les données se chargent à l'exécution du programme
 */

// Inclure l'autoloader de Composer
require_once __DIR__ . '/vendor/autoload.php';

// Test d'accès à Goutte
echo "Test de disponibilité de Goutte\Client: ";
echo class_exists('Goutte\Client') ? "DISPONIBLE" : "NON DISPONIBLE";
echo "\n";

// Inclure les classes nécessaires
require_once __DIR__ . '/src/class/DataCache.php';
require_once __DIR__ . '/src/class/MockHttpClient.php';
require_once __DIR__ . '/src/class/TirageDataFetcher.php';
require_once __DIR__ . '/src/class/TirageStrategies.php';

// Définir les variables globales nécessaires
$password = "nirvana"; // Valeur de src/startup.php
$h = sha1($password.'migo'); // Hash pour l'authentification

// Journalisation
$logFile = __DIR__ . '/var/log/cron_update.log';
function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    echo $logMessage;
}

logMessage("Début de la mise à jour des données...");

try {
    // Pas de cache - récupération directe des données
    $dataFetcher = new TirageDataFetcher();
    
    // 1. Mise à jour des données récentes
    logMessage("Mise à jour des données récentes...");
    $recentData = $dataFetcher->getRecentTirages();
    logMessage("Données récentes mises à jour avec succès.");
    
    // 2. Mise à jour des données historiques avec des limites progressives
    $limits = [1000, 3000, 5000, 7000]; // Augmentation de la profondeur d'historique
    
    foreach ($limits as $limit) {
        logMessage("Mise à jour des données historiques (limite: $limit)...");
        $historicalData = $dataFetcher->getHistoricalTirages($limit);
        logMessage("Historique (limite: $limit) mis à jour avec " . count($historicalData['numbers']) . " tirages.");
    }
    
    // 3. Mise à jour des données historiques étendues
    logMessage("Mise à jour des données historiques étendues...");
    $extendedData = $dataFetcher->getExtendedHistoricalData();
    logMessage("Données historiques étendues mises à jour avec " . count($extendedData['numbers']) . " tirages.");
    
    // 4. Précharger les stratégies de tirage
    logMessage("Préchargement des stratégies de tirage...");
    $strategiesEngine = new TirageStrategies($extendedData, $recentData);
    $strategies = $strategiesEngine->getStrategies();
    logMessage("Stratégies préchargées : " . count($strategies));
    
    // 5. Plus de nettoyage du cache car il est désactivé
    logMessage("Pas de cache à nettoyer - le cache est désactivé.");
    
    logMessage("Mise à jour des données terminée avec succès.");
} catch (Exception $e) {
    logMessage("ERREUR : " . $e->getMessage());
    exit(1);
}