<?php
// Fichier de démarrage pour Amélie - version simplifiée

// Démarrer la session s'il elle n'est pas déjà démarrée
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Charger les dépendances via Composer
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    die("Erreur: Les dépendances ne sont pas installées. Exécutez 'composer install'.");
}

// Chargement manuel des classes principales
require_once __DIR__ . '/class/TirageVerifier.php';
require_once __DIR__ . '/class/TirageDataFetcher.php';
require_once __DIR__ . '/class/TirageStrategies.php';
require_once __DIR__ . '/class/TirageDailyStrategies.php';
require_once __DIR__ . '/class/TirageDataset.php';

// Chargement de toutes les classes dans le dossier strategies
$strategiesDir = __DIR__ . '/class/strategies/';
if (is_dir($strategiesDir)) {
    // Définir l'ordre de chargement pour respecter les dépendances
    $strategyLoadOrder = [
        'AIStrategyManager.php', // Charger la façade en premier
        'BayesianEVStrategy.php',
        'MarkovROIStrategy.php',
        'MLPredictStrategy.php',
        'ClusterEVStrategy.php',
        'BanditSelectorStrategy.php' // Dépend des autres stratégies, donc charger en dernier
    ];
    
    // Charger d'abord les fichiers dans l'ordre spécifié
    foreach ($strategyLoadOrder as $filename) {
        $filePath = $strategiesDir . $filename;
        if (file_exists($filePath)) {
            require_once $filePath;
        }
    }
    
    // Puis charger tous les autres fichiers qui pourraient exister
    $files = glob($strategiesDir . '*.php');
    foreach ($files as $file) {
        if (!in_array(basename($file), $strategyLoadOrder)) {
            require_once $file;
        }
    }
}

// Configurer Chart.js pour les graphiques
echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';

// Conserver le système d'authentification
$password = "nirvana"; // Mot de passe pour l'authentification
$h = sha1($password.'migo'); // Hash pour authentification via URL (legacy)