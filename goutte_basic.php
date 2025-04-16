<?php
// Test basique pour vérifier si Goutte est correctement chargé

// Inclure l'autoloader de Composer
require __DIR__ . '/vendor/autoload.php';

try {
    // Tenter de créer une instance de Goutte\Client
    $client = new \Goutte\Client();
    echo "Goutte\Client créé avec succès!\n";
    
    // Tenter une requête simple
    $crawler = $client->request('GET', 'https://example.com');
    echo "Requête HTTP effectuée avec succès!\n";
    echo "Titre de la page: " . $crawler->filter('title')->text() . "\n";
} catch (\Exception $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    echo "Type d'exception: " . get_class($e) . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}