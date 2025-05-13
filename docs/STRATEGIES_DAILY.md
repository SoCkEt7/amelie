# Stratégies Journalières pour Amigo

Ce document décrit les stratégies journalières utilisées pour optimiser les gains au jeu Amigo en se basant uniquement sur les tirages du jour courant.

## Contexte

- Chaque jour, environ **250 tirages** sont effectués
- Possible **reset de la RNG** quotidien, créant des patterns propres à chaque journée
- Les stratégies journalières s'adaptent rapidement aux tendances du jour

## Importance des Stratégies Journalières

Les stratégies journalières offrent plusieurs avantages par rapport aux stratégies basées sur l'historique complet:

1. **Réactivité aux tendances quotidiennes**: Détection rapide des numéros "chauds" du jour
2. **Adaptation au reset RNG**: Si le générateur aléatoire est réinitialisé chaque jour, ces stratégies capturent mieux les patterns
3. **Données plus pertinentes**: Les tirages du jour représentent l'état actuel du système

## Stratégies Journalières Disponibles

### 1. Numéros Fréquents du Jour
- **Description**: Sélectionne les numéros qui apparaissent le plus souvent dans les tirages du jour
- **Méthode**: Analyse de fréquence sur les tirages du jour uniquement
- **Avantage**: Capture rapidement les tendances quotidiennes

### 2. Position Optimale du Jour
- **Description**: Analyse les positions (bleue/jaune) les plus fréquentes pour chaque numéro dans la journée
- **Méthode**: Sélection basée sur le ratio d'apparition en position bleue vs jaune
- **Avantage**: S'adapte aux biais potentiels du RNG pour la journée

### 3. Séquences du Jour
- **Description**: Identifie les séquences de numéros apparues plusieurs fois dans la journée
- **Méthode**: Analyse des sous-ensembles de numéros tirés ensemble
- **Avantage**: Détecte les patterns de co-occurrence propres au jour

### 4. ROI Intra-journalier
- **Description**: Optimisation du retour sur investissement basée sur les gains dans la journée
- **Méthode**: Calcul de l'espérance de gain pour chaque numéro sur les tirages du jour
- **Avantage**: Maximise les gains potentiels sur les tendances du jour

### 5. Stratégie Adaptative
- **Description**: S'ajuste automatiquement en fonction de la progression de la journée
- **Méthode**: Pondération variable entre les numéros chauds et froids selon l'heure
- **Avantage**: Prend en compte le cycle quotidien du RNG

## Particularités des RNG quotidiens

Si le générateur de nombres aléatoires est réinitialisé chaque jour:

1. **Début de journée**: Distribution potentiellement plus prévisible juste après le reset
2. **Milieu de journée**: Établissement de tendances stables sur certains numéros
3. **Fin de journée**: Possible convergence vers une distribution plus équilibrée

Ces caractéristiques sont spécifiquement exploitées par les stratégies journalières pour maximiser les gains.

## Mise en œuvre

Les stratégies journalières sont implémentées dans la classe `TirageDailyStrategies.php` et accessibles via la page `daily.php`, qui récupère et analyse uniquement les tirages du jour en cours.