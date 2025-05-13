# Documentation Amélie - Solution d'optimisation pour Amigo

## Présentation

Cette documentation rassemble les stratégies et informations techniques pour le projet Amélie, qui vise à optimiser les gains au jeu Amigo.

## Documents disponibles

- [Stratégies Historiques](STRATEGIES.md) - Stratégies basées sur l'analyse complète de l'historique des tirages
- [Stratégies IA](STRATEGIES_IA.md) - Stratégies avancées utilisant des algorithmes d'IA
- [Stratégies Journalières](STRATEGIES_DAILY.md) - Stratégies spécifiques aux tirages du jour courant
- [Structure du Projet](structure.md) - Organisation technique du code et des fichiers

## Principes d'implémentation

- **Aucun cache** : toutes les données sont lues et traitées à chaque requête
- **Aucune API externe** : tous les calculs sont effectués localement
- **Données réelles** : utilisation exclusive des données officielles récupérées en temps réel

## Architecture du système

Le système est organisé autour de plusieurs classes PHP:

- `TirageDataFetcher` - Récupération des données depuis les sources officielles
- `TirageDataset` - Accès aux fichiers JSON de l'historique des tirages
- `TirageStrategies` - Implémentation des stratégies historiques
- `TirageDailyStrategies` - Implémentation des stratégies journalières
- `AIStrategyManager` - Gestion des stratégies IA avancées

## Tableau des gains (mise 8€)

| Bons | 🟦 | 🟨 | Chance 1/ | Gain 8€ |
|-----:|---:|---:|----------:|---------:|
| 7 | 7 | 0 | 1 184 040 | 100 000 |
| 7 | 6 | 1 | 33 829,71 | 2 000 |
| 7 | 5 | 2 | 5 638,29 | 480 |
| 7 | 4 | 3 | 3 382,97 | 400 |
| 7 | 3 | 4 | 6 765,94 | 320 |
| 7 | 2 | 5 | 56 382,86 | 400 |
| ... | ... | ... | ... | ... |

Le tableau complet est disponible dans [Stratégies IA](STRATEGIES_IA.md).

## Accès aux interfaces

- `/index.php` - Stratégies historiques
- `/daily.php` - Stratégies journalières
- `/ai.php` - Stratégies IA