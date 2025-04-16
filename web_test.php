<?php
// Test de connexion web avec Goutte

// Inclure l'autoloader de Composer
require_once __DIR__ . '/vendor/autoload.php';

echo "Test de connexion avec Goutte\Client...\n";

try {
    // Essayer de créer une instance
    $client = new \Goutte\Client();
    echo "Instance de Goutte\Client créée avec succès!\n";
    
    // Essayer de se connecter à un site web
    echo "Tentative de connexion à un site web...\n";
    $crawler = $client->request('GET', 'https://example.com');
    
    // Vérifier si la connexion a réussi
    $statusCode = $client->getResponse()->getStatusCode();
    echo "Code de statut: $statusCode\n";
    
    // Extraire le titre de la page
    $title = $crawler->filter('title')->text();
    echo "Titre de la page: $title\n";
    
    echo "Connexion réussie!\n";
} catch (Exception $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
}