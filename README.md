# Amélie - Analyse de tirages Amigo

Application PHP pour analyser les tirages du jeu Amigo et générer des stratégies de jeu optimisées.

## Version simplifiée

Cette branche contient une version simplifiée de l'application qui :
- Récupère directement les données depuis les sources officielles
- Ne nécessite pas de cache ni de cron jobs
- Génère des analyses et recommandations en temps réel

## Fonctionnalités

- Affichage des derniers tirages (numéros bleus et jaunes)
- Génération de 12 stratégies différentes pour optimiser les chances de gain
- Visualisation des statistiques de fréquence d'apparition
- Tableau des gains avec probabilités

## Installation

1. Cloner le dépôt
2. Installer les dépendances : `composer install`
3. S'assurer que l'extension php-curl est activée
4. Lancer un serveur PHP : `php -S localhost:8000`

## Utilisation

1. Accéder à l'application via un navigateur
2. Se connecter avec le mot de passe configuré
3. Consulter les derniers tirages et les stratégies recommandées

## Structure du code

- `index.php` : Point d'entrée principal
- `src/class/TirageDataFetcher.php` : Récupération des données depuis les sources
- `src/class/TirageStrategies.php` : Génération des stratégies de jeu
- `assets/` : Fichiers CSS, JS et templates

## Règles du jeu

Le jeu Amigo consiste à choisir 7 numéros parmi une combinaison de 12 numéros (7 bleus et 5 jaunes) tirés au sort.