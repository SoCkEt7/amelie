<?php
// Debug de Goutte dans l'environnement web

// Inclure l'autoloader de Composer directement
require_once __DIR__ . '/vendor/autoload.php';

echo "<pre>";
echo "Test de chargement de Goutte\Client...\n";

// Vérifier si la classe existe
if (class_exists('Goutte\Client')) {
    echo "SUCCÈS : La classe Goutte\Client existe!\n";
    
    try {
        // Essayer de créer une instance
        $client = new \Goutte\Client();
        echo "SUCCÈS : Instance de Goutte\Client créée avec succès!\n";
        
        // Tester une connexion
        $crawler = $client->request('GET', 'https://example.com');
        $statusCode = $client->getResponse()->getStatusCode();
        echo "Connexion HTTP réussie! Statut: $statusCode\n";
    } catch (Exception $e) {
        echo "ERREUR lors de la création de l'instance : " . $e->getMessage() . "\n";
    }
} else {
    echo "ERREUR : La classe Goutte\Client n'existe pas!\n";
    
    // Lister les namespaces disponibles
    echo "Namespaces disponibles dans l'autoloader:\n";
    $loader = require __DIR__ . '/vendor/composer/autoload_namespaces.php';
    foreach ($loader as $namespace => $path) {
        echo " - $namespace\n";
    }
    
    $loader = require __DIR__ . '/vendor/composer/autoload_psr4.php';
    foreach ($loader as $namespace => $path) {
        echo " - $namespace\n";
    }
    
    // Vérifier le fichier
    $file = __DIR__ . '/vendor/fabpot/goutte/Goutte/Client.php';
    echo "Vérification du fichier: $file\n";
    echo "Existe: " . (file_exists($file) ? "OUI" : "NON") . "\n";
}

echo "</pre>";