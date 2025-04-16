# Directives pour Claude - Amélie

## Utilisation des données réelles

**IMPORTANT: Toujours utiliser les données réelles en production**

1. **Environnement de production:**
   - NE JAMAIS utiliser de données simulées en production
   - TOUJOURS vérifier que les données proviennent de sources authentiques
   - Assurer que toutes les recommandations sont basées sur des tirages réels récents

2. **Récupération des données:**
   - Utiliser `Goutte\Client` pour accéder aux sites sources:
     - tirage-gagnant.com
     - reducmiz.com
     - resultats-loto.com
   - Vérifier que les dépendances sont correctement installées: `composer require fabpot/goutte`
   - S'assurer que les permissions réseau permettent l'accès aux sources

3. **Vérification d'authenticité:**
   - Vérifier l'indicateur `isAuthentic` dans les données
   - Afficher clairement la source et la date de mise à jour des données
   - SIGNALER immédiatement si des données simulées sont utilisées par nécessité

4. **Maintenance du cache:**
   - Exécuter le cron job deux fois par jour
   - `0 */12 * * * php /var/www/amelie/cron_update_data.php`
   - Vérifier que le cache est à jour avant de générer des recommandations

## Commandes essentielles

- Initialiser le cache avec données réelles: `php init_cache.php`
- Mettre à jour manuellement les données: `php cron_update_data.php`
- Vérifier l'authenticité des données du cache: `php verify_cache.php`

## En cas de problème d'accès aux données réelles

1. Signaler clairement le problème à l'utilisateur
2. Afficher l'alerte d'avertissement en haut de page
3. Indiquer quand les dernières données réelles ont été récupérées
4. Recommander l'action correctrice (mise à jour manuelle, vérification réseau)

**JAMAIS présenter des données simulées comme réelles.**