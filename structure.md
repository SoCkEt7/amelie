# Structure du Projet Amélie

## Fichiers Principaux

- `index.php` : Page principale qui affiche les stratégies basées sur l'historique complet
- `daily.php` : Page qui affiche les stratégies basées uniquement sur les tirages du jour

## Organisation du Code

### Classes Principales

- `TirageDataFetcher.php` : Récupère les données de tirages depuis les sources officielles
  - `getRecentTirages()` : Récupère les tirages récents (dernière journée)
  - `getHistoricalTirages()` : Récupère l'historique des tirages (jusqu'à 1000)
  - `getExtendedHistoricalData()` : Récupère des données historiques enrichies

- `TirageStrategies.php` : Génère les stratégies basées sur l'historique complet
  - Analyse les fréquences, positions, et corrélations des numéros
  - Calcule les scores pour chaque stratégie

- `TirageDailyStrategies.php` : Génère des stratégies basées sur les tirages du jour
  - Se concentre sur les tendances spécifiques de la journée
  - Adapte les stratégies en temps réel

- `TirageVerifier.php` : Vérifie l'authenticité des données
  - Contrôle que les données sont issues de sources officielles
  - Vérifie la fraîcheur des données

### Flux de Données

1. Les données sont récupérées depuis:
   - `reducmiz.com` (source principale pour les tirages historiques)
   - `tirage-gagnant.com` (source de secours ou pour les tirages récents)

2. Structure des tirages:
   - 7 numéros bleus + 5 numéros jaunes = 12 numéros par tirage
   - Chaque tirage est stocké avec sa date et ses statistiques

3. Traitement des données:
   - Les données récentes sont extraites du premier groupe (les plus récentes)
   - Les données historiques sont traitées dans l'ordre pour maintenir la cohérence

## Points Importants

- **Source des données**: Toujours utiliser des données réelles, comme indiqué dans CLAUDE.md
- **Optimisation**: Le code est conçu pour récupérer directement les données sans système de cache
- **Fiabilité**: Mécanismes de secours en cas d'échec d'une source

## Système de Log

- Les logs de débogage sont affichés dans la console Symfony
- Les erreurs importantes sont enregistrées dans `/var/log/`

## Dépendances

- `Goutte\Client`: Client HTTP pour récupérer les données des sites web
  - Installation: `composer require fabpot/goutte`