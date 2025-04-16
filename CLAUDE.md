# Directives pour Claude - Amélie (version simplifiée)

## Utilisation des données réelles

**IMPORTANT: Toujours utiliser les données réelles en production**

1. **Sources officielles pour les données:**
   - Utiliser `Goutte\Client` pour accéder directement aux sites sources:
     - tirage-gagnant.com (tirages récents)
     - reducmiz.com (données historiques)
     - resultats-loto.com (source secondaire)
   - Assurer que les dépendances sont installées: `composer require fabpot/goutte`
   - Vérifier que le sélecteur `.num, .chance` est utilisé pour tirage-gagnant.com

2. **Vérification d'authenticité:**
   - Vérifier l'indicateur `isAuthentic` dans les données
   - Afficher clairement la source et la date de mise à jour des données
   - SIGNALER immédiatement si les données ne sont pas disponibles

3. **Architecture simplifiée:**
   - **AUCUN système de cache** - toutes les données sont récupérées en direct
   - **PAS de cron job** - les données sont toujours fraîches à chaque requête
   - Approche minimaliste sans fichiers temporaires

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

## Règles du jeu Amigo

- Choisir 7 numéros parmi la combinaison de 12 numéros tirés (7 bleus + 5 jaunes)
- Stratégies optimisées basées sur l'analyse des positions bleues/jaunes
- Interface compacte avec système d'onglets pour afficher toutes les stratégies