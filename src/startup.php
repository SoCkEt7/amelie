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

// Configurer Chart.js pour les graphiques
echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';

// Conserver le système d'authentification
$password = "nirvana"; // Mot de passe pour l'authentification
$h = sha1($password.'migo'); // Hash pour authentification via URL (legacy)