# Guide d'installation et configuration d'Amélie

## Prérequis
- PHP 7.4 ou supérieur
- Composer
- Serveur Web (Apache/Nginx)

## Étapes d'installation

1. **Cloner le dépôt**
   ```
   git clone https://github.com/votre-repo/amelie.git
   cd amelie
   ```

2. **Installer les dépendances**
   ```
   composer install
   ```

3. **Initialiser le cache**
   Pour des performances optimales, initialisez le cache avant la première utilisation :
   ```
   php init_cache.php
   ```
   Ce script génère un cache initial avec les 1000 derniers résultats de tirages.

4. **Configurer la mise à jour automatique (crontab)**
   Pour maintenir vos données à jour, ajoutez ce job cron pour actualiser les données 2 fois par jour :
   ```
   # Ajouter cette ligne à votre crontab (crontab -e)
   0 */12 * * * php /chemin/vers/amelie/cron_update_data.php
   ```

## Structure du cache

Les données mises en cache sont stockées dans le dossier `/src/cache/` :

1. **Données récentes** (tirages récents)
   - Fichier: `593f9b65c3e7b3ff26cc367c89280ee4.json`
   - Contient les derniers tirages et statistiques du jour
   - Durée de vie: 3 jours

2. **Données historiques** (1000 derniers tirages)
   - Fichier: `579c01831528c59081f7c212346f5f2c.json`
   - Contient les 1000 derniers tirages avec leurs statistiques
   - Durée de vie: 3 jours

3. **Données historiques étendues**
   - Fichier: `1ceec53b24fb9ef5c66d78064a726ee6.json`
   - Combinaison enrichie des différentes sources de données
   - Durée de vie: 3 jours

## Vérification de l'authenticité des données

Amélie intègre désormais un système de vérification d'authenticité des données :

- Indicateur visuel montrant si les données utilisées sont réelles ou simulées
- Affichage de la source des données et de la date de dernière mise à jour
- Système de score de fiabilité pour chaque stratégie

Pour obtenir les données les plus fiables, il est recommandé d'exécuter l'application dans un environnement web avec connexion internet pour permettre la récupération des données réelles depuis les sites sources.

## Troubleshooting

- **Problème de performance au chargement initial** : Vérifiez que vous avez bien exécuté `php init_cache.php` pour pré-remplir le cache.
- **Données non à jour** : Vérifiez que le cron job est correctement configuré et s'exécute bien.
- **Affichage d'un avertissement sur les données simulées** : C'est normal lors de l'exécution en mode CLI. En production web, les vraies données seront récupérées.