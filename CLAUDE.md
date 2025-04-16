# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commandes essentielles

- Exécuter l'application: `php index.php`
- Installer les dépendances: `composer install`
- Vérifier la syntaxe PHP: `php -l fichier.php`

## Style de code

- **Conventions de nommage**: CamelCase pour les classes, camelCase pour les méthodes/variables
- **Indentation**: 4 espaces
- **Documentation**: Docblocks PHPDoc pour classes et méthodes
- **Encodage**: UTF-8
- **Framework**: Projet PHP natif, pas de framework supplémentaire

## Règles fonctionnelles

- Application d'analyse de tirages de jeu "Amigo"
- Récupération des données depuis les sources officielles via Goutte\Client
- Règles du jeu: choisir 7 numéros parmi la combinaison de 12 numéros (7 bleus + 5 jaunes)
- Approche simplifiée: pas de cache ni de cron job, récupérer les données en direct