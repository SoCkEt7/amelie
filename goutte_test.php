<?php
// Test de Goutte pour voir s'il est correctement chargé

// Inclure l'autoloader de Composer
require_once __DIR__ . '/vendor/autoload.php';

echo "Test de chargement de Goutte\Client...\n";

// Vérifier si la classe existe
if (class_exists('Goutte\Client')) {
    echo "SUCCÈS : La classe Goutte\Client existe!\n";
    
    try {
        // Essayer de créer une instance
        $client = new \Goutte\Client();
        echo "SUCCÈS : Instance de Goutte\Client créée avec succès!\n";
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
}

// Lister les dépendances installées
echo "\nDépendances installées via Composer:\n";
$packages = json_decode(file_get_contents(__DIR__ . '/vendor/composer/installed.json'), true);
if (isset($packages['packages'])) {
    foreach ($packages['packages'] as $package) {
        echo " - {$package['name']} : {$package['version']}\n";
    }
} else {
    foreach ($packages as $package) {
        echo " - {$package['name']} : {$package['version']}\n";
    }
}