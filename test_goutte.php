<?php
// Test d'utilisation de Goutte dans un contexte web simple

// Inclure directement l'autoloader de Composer
require_once __DIR__ . '/vendor/autoload.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test de Goutte</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Test de disponibilité et d'utilisation de Goutte</h1>
    
    <?php
    try {
        // Vérifier si Goutte est disponible
        $isGoutteAvailable = class_exists('Goutte\Client');
        echo "<h2>Disponibilité de Goutte\\Client</h2>";
        echo "<p>Classe Goutte\\Client: <strong>" . ($isGoutteAvailable ? "DISPONIBLE" : "NON DISPONIBLE") . "</strong></p>";
        
        // Ne continuer que si Goutte est disponible
        if ($isGoutteAvailable) {
            // Créer une instance et tester une requête
            echo "<h2>Test d'utilisation</h2>";
            echo "<pre>";
            
            $client = new \Goutte\Client();
            echo "Instance de Goutte\\Client créée avec succès!\n";
            
            $crawler = $client->request('GET', 'https://example.com');
            echo "Requête HTTP effectuée avec succès!\n";
            echo "Titre de la page: " . $crawler->filter('title')->text() . "\n";
            
            echo "</pre>";
        }
    } catch (Exception $e) {
        echo "<h2>Erreur</h2>";
        echo "<pre>";
        echo "ERREUR: " . $e->getMessage() . "\n";
        echo "Type d'exception: " . get_class($e) . "\n";
        echo "Fichier: " . $e->getFile() . "\n";
        echo "Ligne: " . $e->getLine() . "\n";
        echo "</pre>";
    }
    ?>
    
    <h2>Configuration PHP</h2>
    <pre><?php echo phpinfo(INFO_MODULES); ?></pre>
</body>
</html>