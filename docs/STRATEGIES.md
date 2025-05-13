# Stratégies optimisées pour Amigo

Ce document décrit les stratégies et leur logique pour maximiser les gains au jeu Amigo.

## Tableau des gains

| Total_Bons | Nb_Bleus | Nb_Jaunes | Chance_sur | Gain_2€ | Gain_4€ | Gain_6€ | Gain_8€ |
|------------|----------|-----------|------------|---------|---------|---------|---------|
| 7 | 7 | 0 | 1184040 | 25000 | 50000 | 75000 | 100000 |
| 7 | 6 | 1 | 33829.71 | 500 | 1000 | 1500 | 2000 |
| 7 | 5 | 2 | 5638.29 | 120 | 240 | 360 | 480 |
| 7 | 4 | 3 | 3382.97 | 100 | 200 | 300 | 400 |
| 7 | 3 | 4 | 6765.94 | 80 | 160 | 240 | 320 |
| 7 | 2 | 5 | 56382.86 | 100 | 200 | 300 | 400 |
| 6 | 6 | 0 | 10571.79 | 250 | 500 | 750 | 1000 |
| 6 | 5 | 1 | 704.79 | 55 | 110 | 165 | 220 |
| 6 | 4 | 2 | 211.44 | 20 | 40 | 60 | 80 |
| 6 | 3 | 3 | 111.44 | 25 | 50 | 75 | 100 |
| 6 | 2 | 4 | 704.79 | 15 | 30 | 45 | 60 |
| 6 | 1 | 5 | 10571.79 | 10 | 20 | 30 | 40 |
| 5 | 5 | 0 | 469.86 | 50 | 100 | 150 | 200 |
| 5 | 4 | 1 | 56.38 | 8 | 16 | 24 | 32 |
| 5 | 3 | 2 | 28.19 | 3 | 6 | 9 | 12 |
| 5 | 2 | 3 | 46.99 | 3 | 6 | 9 | 12 |
| 5 | 1 | 4 | 281.91 | 3 | 6 | 9 | 12 |
| 4 | 4 | 0 | 9867 | 3 | 6 | 9 | 12 |
| 4 | 3 | 1 | 60.41 | 2 | 4 | 6 | 8 |
| 4 | 2 | 2 | 12.08 | 2 | 4 | 6 | 8 |
| 4 | 1 | 3 | 30.21 | 2 | 4 | 6 | 8 |
| 4 | 0 | 4 | 422.87 | 2 | 4 | 6 | 8 |

## Stratégies principales

### 1. Numéros Fréquents
- **Description**: Sélectionne les numéros qui apparaissent le plus souvent dans les tirages historiques
- **Méthode**: Analyse de fréquence globale
- **Logique**: Plus un numéro est fréquent, plus il a de chances de réapparaître

### 2. Numéros Rares
- **Description**: Sélectionne les numéros qui apparaissent rarement (théorie de la compensation)
- **Méthode**: Analyse inverse de fréquence
- **Logique**: Les numéros rares pourraient être "dus" selon la théorie de la régression vers la moyenne

### 3. Stratégie Bleue Maximum
- **Description**: Maximise les chances d'obtenir des numéros bleus
- **Méthode**: Sélection des numéros les plus fréquents en position bleue
- **Logique**: Vise le jackpot maximal (7 bleus, 0 jaunes -> 100 000€ pour 8€)

### 4. Équilibre Optimal (4B-3J)
- **Description**: Vise la combinaison avec le meilleur rapport probabilité/gain (4 bleus, 3 jaunes)
- **Méthode**: Sélection des meilleurs numéros bleus et jaunes selon leur position historique
- **Logique**: La combinaison 4B-3J a les meilleures chances (1/3383) pour un gain important (400€ pour 8€)

### 5. Mix Probabilisé
- **Description**: Pondère les numéros selon leur espérance mathématique de gain
- **Méthode**: Calcul précis des probabilités et des gains espérés
- **Logique**: Maximise l'espérance mathématique sur le long terme

### 6. Numéros Chauds
- **Description**: Sélectionne les numéros récemment sortis plusieurs fois
- **Méthode**: Analyse des 200 derniers tirages
- **Logique**: Exploite les séries temporelles favorables

## Stratégies avancées

### 7. Stratégie ROI Maximal
- **Description**: Optimisation pointue du retour sur investissement
- **Méthode**: Formule complexe intégrant fréquence, position et gains
- **Logique**: Vise le meilleur rapport entre mise et gain espéré

### 8. Stratégie 5B-2J
- **Description**: Vise spécifiquement la combinaison 5 bleus, 2 jaunes
- **Méthode**: Sélection précise basée sur l'historique des positions
- **Logique**: Bon équilibre entre probabilité (1/5638) et gain (480€ pour 8€)

### 9. Cyclique/Tendances
- **Description**: Détecte les cycles et tendances dans les tirages
- **Méthode**: Analyse des intervalles d'apparition et des corrélations temporelles
- **Logique**: Exploite la non-uniformité temporelle des tirages

### 10. Clusters
- **Description**: Identifie les groupes de numéros apparaissant souvent ensemble
- **Méthode**: Analyse de corrélation entre numéros
- **Logique**: Exploite les affinités entre certains numéros