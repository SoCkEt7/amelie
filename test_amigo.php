<?php
/**
 * Script de test pour la récupération des données Amigo
 * Ce script teste la récupération des tirages depuis différentes sources
 */

// Inclure les dépendances nécessaires
require 'vendor/autoload.php';
// DataCache a été supprimé
require_once 'src/class/TirageDataFetcher.php';
require_once 'src/class/TirageStrategies.php';

echo "=== Test de récupération des résultats Amigo ===\n\n";

// Configuration des logs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Activer la journalisation vers un fichier
$logFile = 'var/log/test_amigo.log';
if (!file_exists(dirname($logFile))) {
    mkdir(dirname($logFile), 0777, true);
}
ini_set('error_log', $logFile);
echo "Logs écrits dans $logFile\n\n";

// Fonction d'aide pour tester une URL
function testUrl($url) {
    echo "Test de l'URL: $url\n";
    try {
        $client = new \Goutte\Client();
        $crawler = $client->request('GET', $url);
        echo "Connexion réussie!\n";
        
        // Afficher un aperçu du contenu
        $html = $crawler->html();
        $preview = substr($html, 0, 500) . "...";
        echo "Aperçu du contenu:\n" . $preview . "\n\n";
        
        return $crawler;
    } catch (Exception $e) {
        echo "ERREUR: " . $e->getMessage() . "\n\n";
        return null;
    }
}

// Fonction pour rechercher des numéros avec différents sélecteurs
function findNumbersWithSelectors($crawler) {
    if (!$crawler) return;
    
    $selectors = [
        '.num, .chance',
        '.tirage-number',
        '.boule',
        '.ball',
        'span[class*="num"]',
        'span[class*="ball"]',
        'div[class*="num"]',
        'td:not(:has(*))',
        'li:not(:has(*))'
    ];
    
    echo "Recherche de numéros avec différents sélecteurs CSS:\n";
    foreach ($selectors as $selector) {
        $numbers = [];
        try {
            $crawler->filter($selector)->each(function ($node) use (&$numbers) {
                $text = trim($node->text());
                if (preg_match('/^(\d{1,2})$/', $text) && $text >= 1 && $text <= 28) {
                    $numbers[] = (int)$text;
                }
            });
            
            echo "  Sélecteur '$selector': " . count($numbers) . " numéros trouvés\n";
            if (count($numbers) > 0) {
                echo "    Numéros: " . implode(', ', array_slice($numbers, 0, 20)) . (count($numbers) > 20 ? "..." : "") . "\n";
            }
        } catch (Exception $e) {
            echo "  Erreur avec sélecteur '$selector': " . $e->getMessage() . "\n";
        }
    }
    echo "\n";
}

// Test récursif pour trouver des numéros
function findNumbersRecursively($crawler) {
    if (!$crawler) return;
    
    echo "Recherche récursive de numéros dans le DOM...\n";
    $numbers = [];
    
    try {
        $crawler->filter('*')->each(function ($node) use (&$numbers) {
            $text = trim($node->text());
            if (preg_match('/^(\d{1,2})$/', $text) && $text >= 1 && $text <= 28) {
                $numbers[] = (int)$text;
            }
        });
        
        echo "Trouvé " . count($numbers) . " numéros potentiels dans le DOM\n";
        if (count($numbers) > 0) {
            echo "Numéros: " . implode(', ', array_slice($numbers, 0, 40)) . (count($numbers) > 40 ? "..." : "") . "\n";
        }
    } catch (Exception $e) {
        echo "Erreur lors de la recherche récursive: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// Tester les sources individuelles
$sources = [
    'https://tirage-gagnant.com/amigo/',
    'https://www.reducmiz.com/resultat_fdj.php?jeu=amigo&nb=all',
    'https://www.resultats-loto.com/amigo/resultats',
    'https://tirage-loto.net/resultats-amigo.htm',
    'https://www.loto-tirage.com/resultats-amigo/'
];

foreach ($sources as $source) {
    echo "=== Test de la source: " . $source . " ===\n";
    $crawler = testUrl($source);
    findNumbersWithSelectors($crawler);
    findNumbersRecursively($crawler);
}

// Tester le TirageDataFetcher
echo "=== Test du TirageDataFetcher ===\n";
try {
    $fetcher = new TirageDataFetcher();
    
    echo "Récupération des données récentes...\n";
    $recentData = $fetcher->getRecentTirages();
    
    echo "Données récentes récupérées:\n";
    if (isset($recentData['error'])) {
        echo "ERREUR: " . $recentData['error'] . "\n";
    } else {
        echo "Source: " . (isset($recentData['dataSource']) ? $recentData['dataSource'] : 'Non spécifiée') . "\n";
        echo "Date de mise à jour: " . (isset($recentData['lastUpdated']) ? $recentData['lastUpdated'] : 'Non spécifiée') . "\n";
        echo "Authentique: " . (isset($recentData['isAuthentic']) && $recentData['isAuthentic'] ? 'Oui' : 'Non') . "\n";
        
        if (isset($recentData['numSortis'])) {
            if (isset($recentData['numSortis']['blue'])) {
                echo "Numéros bleus: " . implode(', ', $recentData['numSortis']['blue']) . "\n";
            }
            if (isset($recentData['numSortis']['yellow'])) {
                echo "Numéros jaunes: " . implode(', ', $recentData['numSortis']['yellow']) . "\n";
            }
        } else {
            echo "Aucun numéro sorti n'a été trouvé.\n";
        }
    }
    
    echo "\nRécupération des données historiques...\n";
    $historicalData = $fetcher->getHistoricalTirages(50); // Limiter à 50 tirages pour le test
    
    echo "Données historiques récupérées:\n";
    if (isset($historicalData['error'])) {
        echo "ERREUR: " . $historicalData['error'] . "\n";
    } else {
        echo "Source: " . (isset($historicalData['dataSource']) ? $historicalData['dataSource'] : 'Non spécifiée') . "\n";
        echo "Date de mise à jour: " . (isset($historicalData['lastUpdated']) ? $historicalData['lastUpdated'] : 'Non spécifiée') . "\n";
        echo "Authentique: " . (isset($historicalData['isAuthentic']) && $historicalData['isAuthentic'] ? 'Oui' : 'Non') . "\n";
        echo "Nombre de tirages: " . (isset($historicalData['count']) ? $historicalData['count'] : 0) . "\n";
        
        if (isset($historicalData['frequency']) && !empty($historicalData['frequency'])) {
            echo "Fréquences des numéros:\n";
            arsort($historicalData['frequency']);
            $i = 0;
            foreach ($historicalData['frequency'] as $number => $freq) {
                echo "  $number: $freq fois\n";
                $i++;
                if ($i >= 10) break; // Limiter à 10 numéros pour l'affichage
            }
        }
    }
    
} catch (Exception $e) {
    echo "ERREUR CRITIQUE: " . $e->getMessage() . "\n";
}

echo "\n=== Test terminé ===\n";
?>