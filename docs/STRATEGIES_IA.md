# Stratégies Amigo v2 – IA **100 % locales**

> **Important**
> - **Aucun cache** : toutes les données sont lues et traitées à chaque requête.
> - **Aucun test automatisé** n’est demandé.
> - **Aucune API externe** : tout se calcule localement.
> - Les **gains** sont codés comme constantes **dans chaque stratégie** (pas de fichier séparé).

---

## 1. Contexte général
- **Fichiers tirages** : `tirages/YYYY-MM-DD_<index>_historical.json` (un JSON par tirage, 7 bleus + 5 jaunes).
- **Structure projet existant** :
   - `index.php` : stratégies historiques.
   - `daily.php` : stratégies journalières.
   - Classes dans `src/class/` : `TirageDataFetcher`, `TirageStrategies`, `TirageDailyStrategies`, `TirageVerifier`.
- **Objectif** : ajouter un onglet *« Stratégies IA – toutes les données »* qui affiche **cinq stratégies IA** calculées en temps réel sur l’historique complet.

---

## 2. Tableau des gains (mise 8 €)
Les valeurs ci‑dessous devront être définies comme constante **GAIN_TABLE** dans chaque stratégie qui en a besoin.

| Bons | 🟦 | 🟨 | Chance 1/ | Gain 8 € |
|-----:|---:|---:|----------:|---------:|
| 7 | 7 | 0 | 1 184 040 | 100 000 |
| 7 | 6 | 1 | 33 829,71 | 2 000 |
| 7 | 5 | 2 | 5 638,29 | 480 |
| 7 | 4 | 3 | 3 382,97 | 400 |
| 7 | 3 | 4 | 6 765,94 | 320 |
| 7 | 2 | 5 | 56 382,86 | 400 |
| 6 | 6 | 0 | 10 571,79 | 1 000 |
| 6 | 5 | 1 | 704,79 | 220 |
| 6 | 4 | 2 | 211,44 | 80 |
| 6 | 3 | 3 | 111,44 | 100 |
| 6 | 2 | 4 | 704,79 | 60 |
| 6 | 1 | 5 | 10 571,79 | 40 |
| 5 | 5 | 0 | 469,86 | 200 |
| 5 | 4 | 1 | 56,38 | 32 |
| 5 | 3 | 2 | 28,19 | 12 |
| 5 | 2 | 3 | 46,99 | 12 |
| 5 | 1 | 4 | 281,91 | 12 |
| 4 | 4 | 0 | 9 867 | 12 |
| 4 | 3 | 1 | 60,41 | 8 |
| 4 | 2 | 2 | 12,08 | 8 |
| 4 | 1 | 3 | 30,21 | 8 |
| 4 | 0 | 4 | 422,87 | 8 |

---

## 3. Détail des cinq stratégies IA

### A1 : `BayesianEVStrategy` — **Modèle bêta‑binomial / EV maximale**
1. **Principe** : pour chaque nombre _n_ (1‑28) on maintient deux compteurs `(alpha_n, beta_n)`.
   - `alpha_n` = fois où _n_ est sorti.
   - `beta_n` = fois où _n_ n’est pas sorti.
2. **Mise à jour** : après chaque nouveau tirage, les compteurs sont incrémentés immédiatement ; pas de facteur d’oubli (puisqu’il n’y a pas de cache, les valeurs sont recalculées à la volée en balayant l’historique complet).
3. **Probabilité postérieure** : `p_n = alpha_n / (alpha_n + beta_n)`.
4. **Sélection** :
   1. Génère toutes les combinaisons 4🟦‑3🟨 candidates (approche gloutonne : on trie bleus et jaunes par `p_n`).
   2. Calcule l’**espérance de gain (EV)** d’une combinaison via `GAIN_TABLE`.
   3. Renvoie la combinaison EV max.

### A2 : `MarkovROIStrategy` — **Chaîne Markov / ROI maximale**
1. **Transition** : matrice 28×28 `T[i][j]` = probabilité qu’un numéro _j_ apparaisse au tirage *t+1* sachant que _i_ est apparu au tirage *t*.
   - Construite en parcourant tout l’historique ; +1 Laplace pour éviter les zéros.
2. **Score proba** : part du dernier tirage et calcule `score_j = Σ_{i∈lastDraw} T[i][j]`.
3. **ROI** : pour chaque numéro, on approxime `roi_j = (score_j * gainEspéré_j)/8`.
4. **Sélection** :
   - Trie séparément bleus et jaunes par ROI.
   - Choisit 4🟦 + 3🟨 ROI max et renvoie la combinaison.

### A3 : `MLPredictStrategy` — **Régression logistique (Rubix ML)**
1. **Features par numéro** :
   - `lag1` : apparu au dernier tirage ? (0/1)
   - `freq50`, `freq250` : fréquence sur les 50 / 250 derniers tirages.
   - `gapLast` : nb de tirages depuis la dernière apparition.
   - `ratioBlue` : proportion d’apparitions en bleu.
2. **Entraînement** : modèle `LogisticRegression` OneVsRest (Rubix) appris à la volée sur l’historique complet.
3. **Prédiction** : proba `p_n` pour chaque numéro.
4. **EV** : `ev_n = p_n * gainEspéré_n`.
5. **Sélection** : top 4🟦 et 3🟨 par `ev_n`.

### A4 : `BanditSelectorStrategy` — **ε‑greedy 0.1 / Gain cumulé**
1. **Arms** : A1, A2, A3 + stratégie `Legacy4B3J` existante.
2. **Récompense** : gain réalisé (0 s’il n’y a aucun match) / 8.
3. **Choix** :
   - 10 % du temps → exploration (arm aléatoire).
   - 90 % → arm au meilleur gain moyen.
4. **Sortie** : renvoie la combinaison choisie par l’arm sélectionné.

### A5 : `ClusterEVStrategy` — **Communautés de numéros / EV cluster**
1. **Corrélation** : matrice de co‑occurrence entre numéros sur tout l’historique.
2. **Communautés** : algorithme de Louvain (simplifié) pour détecter les clusters denses.
3. **Scoring cluster** :
   - Pour chaque cluster, calcule la proba qu’au moins 4 de ses numéros sortent ensemble.
   - Multiplie par le gain moyen associé selon `GAIN_TABLE`.
4. **Sélection** :
   - Prend le cluster EV max puis choisit 4🟦 + 3🟨 à l’intérieur, triés par fréquence.

---

## 4. Fichiers à créer / mettre à jour
Identiques à la section précédente **mais sans fichier PayoutTable** : chaque stratégie contient sa version immuable de `GAIN_TABLE`.

---

## 5. Intégration UI
- **ai.php** : utilise `AIStrategyManager::generateAll()` pour obtenir, pour chaque stratégie :
   - `label` : nom lisible (ex. « Bayesian EV »)
   - `numbers` : **combinaison recommandée** (tirage suggéré) exactement comme affiché dans `index.php` (ordre officiel, bleus puis jaunes)
   - `ev`, `roi`, `strategyId`, etc.
- Affiche un tableau responsive avec :
   1. Nom de la stratégie.
   2. **Tirage suggéré** (7 numéros présentés dans des `<span>` bleus/jaunes, même style que `index.php`).
   3. EV (€, arrondi 2 décimales).
   4. ROI (≥ 0, arrondi 3 déc.).
- **templates/header.php** : ajoute *« Stratégies IA – toutes les données »* (4ᵉ position).

---

## 6. Prompt pour Claude (complet)

```text
Rôle : Claude, expert PHP 8.3 (PSR‑12) & Rubix ML.

Contexte : Projet Amélie (pas de cache, pas de tests, pas d’API externe).

Mission :
1. Créer / mettre à jour dans src/class/ :
   • TirageDataset.php (parse JSON sans cache)
   • strategies/BayesianEVStrategy.php      # A1 (GAIN_TABLE en constante)
   • strategies/MarkovROIStrategy.php       # A2 (GAIN_TABLE)
   • strategies/MLPredictStrategy.php       # A3 (GAIN_TABLE)
   • strategies/ClusterEVStrategy.php       # A5 (GAIN_TABLE)
   • strategies/BanditSelectorStrategy.php  # A4
   • strategies/AIStrategyManager.php       # expose generateAll(): array<strategyInfo>, bestPick(): array
     - `strategyInfo` contient : id, label, numbers (7 ints), ev, roi
2. Modifier :
   • src/class/TirageStrategies.php : ajouter case "IA v2" → AIStrategyManager::bestPick()
   • templates/header.php : ajouter lien vers ai.php (4ᵉ position)
3. Créer **ai.php** :
   - Appelle AIStrategyManager::generateAll()
   - Affiche un tableau responsive par stratégie avec : Nom, **Tirage suggéré** (même rendu que index.php), EV, ROI.
4. Mettre à jour autoload PSR‑4 si nécessaire et lancer `composer dump-autoload`.
5. Aucun test, aucun cache, pas d’API externe.
6. Ne retourner que le code PHP.
```

---

### 7. TL;DR
- `ai.php` présente pour chaque stratégie la **combinaison recommandée** (tirage suggéré), comme dans `index.php`, plus EV et ROI.
- 5 stratégies IA sans cache/test.
- Prompt final prêt pour Claude.
  Prompt pour Claude (complet)

```text
Rôle : Claude, expert PHP 8.3 (PSR‑12) & Rubix ML.

Contexte : Projet Amélie (pas de cache, pas de tests, pas d’API externe).

Mission :
1. Créer / mettre à jour dans src/class/ :
   • TirageDataset.php (parse tirages JSON en direct)
   • strategies/BayesianEVStrategy.php      # implémente A1 (inclut const GAIN_TABLE)
   • strategies/MarkovROIStrategy.php       # A2 (GAIN_TABLE)
   • strategies/MLPredictStrategy.php       # A3 (GAIN_TABLE)
   • strategies/ClusterEVStrategy.php       # A5 (GAIN_TABLE)
   • strategies/BanditSelectorStrategy.php  # A4
   • strategies/AIStrategyManager.php       # façade → generateAll(), bestPick()
2. Modifier :
   • src/class/TirageStrategies.php : ajouter case "IA v2" → AIStrategyManager::bestPick()
   • templates/header.php : lien vers ai.php (4ᵉ position)
3. Créer **ai.php** : affiche tableau responsive des 5 stratégies IA (numéros, EV, ROI, label).
4. Mettre à jour autoload PSR‑4 si nécessaire et exécuter `composer dump-autoload`.
5. Aucun test, aucun cache. Les gains sont des constantes dans chaque classe.
6. Retourne uniquement le code PHP (aucun texte explicatif).
```

---

### 7. TL;DR
- 5 stratégies IA sans cache ni tests, constants de gains incluses.
- Page `ai.php` + lien menu.
- Prompt final prêt pour Claude pour **génération automatique du code**.

