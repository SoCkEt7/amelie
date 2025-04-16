# Amélie - Générateur de tirages optimisés

## Installation

1. Cloner le dépôt
2. Installer les dépendances via Composer
   ```
   composer install
   ```
3. Configurer le fichier de démarrage `src/startup.php` avec les paramètres de votre environnement

## Initialisation du cache

Pour optimiser les performances, il est recommandé d'initialiser le cache avant la première utilisation :

```
php init_cache.php
```

Ce script va créer un cache initial avec les 1000 derniers résultats de tirages.

## Maintenance du cache

Pour maintenir les données à jour sans surcharger le site lors des chargements, configurez un cron job pour mettre à jour les données en arrière-plan 2 fois par jour :

```
# Ajouter cette ligne à votre crontab
0 */12 * * * php /chemin/vers/amelie/cron_update_data.php
```

Cette configuration permet :
1. Un chargement initial rapide (moins de 1000 résultats au lieu de 3000+)
2. Une mise à jour régulière des données en arrière-plan
3. Une expérience utilisateur optimisée

## Structure des données

- Les données récentes (`recent_tirages`) sont mises en cache pour une durée de 3 jours
- Les données historiques (`historical_tirages_X`) où X est la limite (ex: 1000) sont mises en cache pour 3 jours
- Les données étendues (`extended_historical_data`) combinent plusieurs sources pour des analyses plus riches