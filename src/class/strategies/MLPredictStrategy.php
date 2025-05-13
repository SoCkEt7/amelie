<?php

/**
 * Classe MLPredictStrategy - Stratégie IA A3
 * 
 * Utilise des techniques de régression logistique simplifiée
 * pour prédire les chances d'apparition des numéros
 * 
 * @package Amelie\Strategies
 */
class MLPredictStrategy
{
    // Tableau des gains
    const GAIN_TABLE = [
        // format: [numéros_trouvés, bleus, jaunes, chance_sur, gain_pour_8€]
        [7, 7, 0, 1184040, 100000],
        [7, 6, 1, 33829.71, 2000],
        [7, 5, 2, 5638.29, 480],
        [7, 4, 3, 3382.97, 400],
        [7, 3, 4, 6765.94, 320],
        [7, 2, 5, 56382.86, 400],
        [6, 6, 0, 10571.79, 1000],
        [6, 5, 1, 704.79, 220],
        [6, 4, 2, 211.44, 80],
        [6, 3, 3, 111.44, 100],
        [6, 2, 4, 704.79, 60],
        [6, 1, 5, 10571.79, 40],
        [5, 5, 0, 469.86, 200],
        [5, 4, 1, 56.38, 32],
        [5, 3, 2, 28.19, 12],
        [5, 2, 3, 46.99, 12],
        [5, 1, 4, 281.91, 12],
        [4, 4, 0, 9867, 12],
        [4, 3, 1, 60.41, 8],
        [4, 2, 2, 12.08, 8],
        [4, 1, 3, 30.21, 8],
        [4, 0, 4, 422.87, 8]
    ];
    
    // Paramètres de configuration
    const MAX_NUM = 28;
    const BLUE_COUNT = 7;
    const YELLOW_COUNT = 5;
    const PLAYER_PICK = 7;
    
    // Stockage des features pour chaque numéro
    private $features = [];
    
    // Probabilités prédites
    private $predictions = [];
    
    // Espérances de gain par numéro
    private $evByNumber = [];
    
    /**
     * Constructeur
     */
    public function __construct()
    {
        // Initialiser les structures de données
        for ($i = 1; $i <= self::MAX_NUM; $i++) {
            $this->features[$i] = [
                'lag1' => 0,        // Apparu au dernier tirage (0/1)
                'freq50' => 0,      // Fréquence sur 50 derniers tirages
                'freq250' => 0,     // Fréquence sur 250 derniers tirages
                'gapLast' => PHP_INT_MAX, // Écart depuis dernière apparition
                'ratioBlue' => 0.5  // Proportion d'apparitions en bleu
            ];
            
            $this->predictions[$i] = 0.5; // Initialiser avec probabilité neutre
            $this->evByNumber[$i] = 0;
        }
    }
    
    /**
     * Génère une recommandation selon cette stratégie
     * 
     * @return array Informations sur la stratégie
     */
    public function generate()
    {
        // Extraire les features des données historiques
        $this->extractFeatures();
        
        // Faire les prédictions
        $this->predictProbabilities();
        
        // Calculer les espérances de gain par numéro
        $this->calculateEVByNumber();
        
        // Générer la meilleure combinaison
        $bestCombination = $this->findBestCombination();
        
        // Calculer l'espérance de gain et le ROI
        $ev = $this->calculateEV($bestCombination);
        $roi = $ev / 8.0; // Pour mise 8€
        
        return [
            'strategyId' => 'ml_predict',
            'label' => 'ML Predict',
            'numbers' => $bestCombination,
            'ev' => $ev,
            'roi' => $roi
        ];
    }
    
    /**
     * Extrait les features des données historiques
     */
    private function extractFeatures()
    {
        $tirages = TirageDataset::getAllTirages();
        
        // Nombre total de tirages
        $totalTirages = count($tirages);
        
        // Pour chaque numéro, calculer les différentes features
        for ($num = 1; $num <= self::MAX_NUM; $num++) {
            // Initialiser les compteurs
            $appearances = [];
            $blueAppearances = 0;
            $totalAppearances = 0;
            
            // Parcourir les tirages pour collecter les statistiques
            foreach ($tirages as $index => $tirage) {
                $inBlue = false;
                $inYellow = false;
                
                // Vérifier si le numéro est présent en bleu
                if (isset($tirage['blue']) && in_array($num, $tirage['blue'])) {
                    $inBlue = true;
                    $blueAppearances++;
                    $appearances[] = $index;
                }
                
                // Vérifier si le numéro est présent en jaune
                if (isset($tirage['yellow']) && in_array($num, $tirage['yellow'])) {
                    $inYellow = true;
                    $appearances[] = $index;
                }
                
                // Si présent, incrémenter le compteur d'apparitions totales
                if ($inBlue || $inYellow) {
                    $totalAppearances++;
                }
            }
            
            // Calculer lag1 (présence dans le dernier tirage)
            if (!empty($tirages)) {
                $lastTirage = reset($tirages); // Premier élément (le plus récent)
                $this->features[$num]['lag1'] = 
                    (isset($lastTirage['blue']) && in_array($num, $lastTirage['blue'])) ||
                    (isset($lastTirage['yellow']) && in_array($num, $lastTirage['yellow']))
                    ? 1 : 0;
            }
            
            // Calculer les fréquences
            $this->features[$num]['freq50'] = $this->calculateFrequency($appearances, min(50, $totalTirages));
            $this->features[$num]['freq250'] = $this->calculateFrequency($appearances, min(250, $totalTirages));
            
            // Calculer l'écart depuis la dernière apparition
            if (!empty($appearances)) {
                $this->features[$num]['gapLast'] = min($appearances);
            }
            
            // Calculer le ratio d'apparitions en bleu
            if ($totalAppearances > 0) {
                $this->features[$num]['ratioBlue'] = $blueAppearances / $totalAppearances;
            }
        }
    }
    
    /**
     * Calcule la fréquence d'apparition sur un nombre donné de tirages
     * 
     * @param array $appearances Indices des tirages où le numéro est apparu
     * @param int $count Nombre de tirages à considérer
     * @return float Fréquence d'apparition
     */
    private function calculateFrequency($appearances, $count)
    {
        if ($count <= 0) {
            return 0;
        }
        
        // Compter les apparitions dans les $count premiers tirages
        $recent = array_filter($appearances, function ($idx) use ($count) {
            return $idx < $count;
        });
        
        return count($recent) / $count;
    }
    
    /**
     * Prédit les probabilités d'apparition pour chaque numéro
     */
    private function predictProbabilities()
    {
        // Dans une implémentation réelle, nous utiliserions Rubix ML ici
        // Pour simplifier, on utilise une régression logistique simplifiée
        
        for ($num = 1; $num <= self::MAX_NUM; $num++) {
            // Feature weights (seraient normalement appris par l'algorithme)
            $weights = [
                'lag1' => -0.2,     // Effet négatif si tiré récemment (retour à la moyenne)
                'freq50' => 0.8,    // Effet positif si fréquent récemment
                'freq250' => 0.4,   // Effet positif mais moindre sur historique plus long
                'gapLast' => 0.004, // Petit effet positif plus l'écart est grand
                'ratioBlue' => 0.3  // Effet positif pour les numéros souvent en bleu
            ];
            
            // Calculer le score linéaire
            $score = 0;
            foreach ($weights as $feature => $weight) {
                if ($feature === 'gapLast') {
                    // Normaliser l'écart (valeur inverse car plus l'écart est grand, plus la chance augmente)
                    $normalizedGap = min(1.0, $this->features[$num][$feature] / 100);
                    $score += $weight * $normalizedGap;
                } else {
                    $score += $weight * $this->features[$num][$feature];
                }
            }
            
            // Appliquer la fonction logistique pour obtenir une probabilité entre 0 et 1
            $this->predictions[$num] = 1 / (1 + exp(-$score));
        }
    }
    
    /**
     * Calcule l'espérance de gain pour chaque numéro
     */
    private function calculateEVByNumber()
    {
        // Calculer un gain moyen pour chaque numéro
        $averageGainBlue = $this->calculateAverageGain('blue');
        $averageGainYellow = $this->calculateAverageGain('yellow');
        
        for ($num = 1; $num <= self::MAX_NUM; $num++) {
            // Probabilité que le numéro apparaisse en bleu/jaune
            $blueProb = $this->predictions[$num] * $this->features[$num]['ratioBlue'];
            $yellowProb = $this->predictions[$num] * (1 - $this->features[$num]['ratioBlue']);
            
            // EV = probabilité * gain espéré
            $this->evByNumber[$num] = 
                ($blueProb * $averageGainBlue) +
                ($yellowProb * $averageGainYellow);
        }
    }
    
    /**
     * Calcule un gain moyen pour une position donnée
     * 
     * @param string $position 'blue' ou 'yellow'
     * @return float Gain moyen
     */
    private function calculateAverageGain($position)
    {
        $totalGain = 0;
        $count = 0;
        
        foreach (self::GAIN_TABLE as $row) {
            // Format: [numéros_trouvés, bleus, jaunes, chance_sur, gain_pour_8€]
            $blueCount = $row[1];
            $gain = $row[4];
            
            if ($position === 'blue' && $blueCount > 0) {
                $totalGain += $gain * ($blueCount / 7); // Pondéré par proportion de bleus
                $count++;
            } elseif ($position === 'yellow' && $row[2] > 0) { // jaunes
                $totalGain += $gain * ($row[2] / 5); // Pondéré par proportion de jaunes
                $count++;
            }
        }
        
        return $count > 0 ? $totalGain / $count : 0;
    }
    
    /**
     * Trouve la meilleure combinaison selon les EV calculées
     * 
     * @return array Meilleure combinaison de 7 numéros
     */
    private function findBestCombination()
    {
        // Séparer les numéros par catégorie (bleu/jaune) basé sur le ratio
        $blueScores = [];
        $yellowScores = [];
        
        for ($num = 1; $num <= self::MAX_NUM; $num++) {
            // Favoriser les numéros avec une forte probabilité dans leur position dominante
            if ($this->features[$num]['ratioBlue'] >= 0.5) {
                $blueScores[$num] = $this->evByNumber[$num] * 
                                   ($this->features[$num]['ratioBlue'] + 0.5);
            } else {
                $yellowScores[$num] = $this->evByNumber[$num] * 
                                     ((1 - $this->features[$num]['ratioBlue']) + 0.5);
            }
        }
        
        // Trier par score décroissant
        arsort($blueScores);
        arsort($yellowScores);
        
        // Sélectionner les meilleurs numéros
        $blueKeys = array_keys($blueScores);
        $yellowKeys = array_keys($yellowScores);
        
        $blues = array_slice($blueKeys, 0, 4); // Top 4 bleus
        $yellows = array_slice($yellowKeys, 0, 3); // Top 3 jaunes
        
        // Combiner et trier
        $combination = array_merge($blues, $yellows);
        sort($combination);
        
        return $combination;
    }
    
    /**
     * Calcule l'espérance de gain pour une combinaison
     * 
     * @param array $combination Combinaison de 7 numéros
     * @return float Espérance de gain en euros
     */
    private function calculateEV($combination)
    {
        $ev = 0;
        
        // Somme des EV individuelles (approximation)
        foreach ($combination as $num) {
            $ev += $this->evByNumber[$num];
        }
        
        return round($ev, 2);
    }
}