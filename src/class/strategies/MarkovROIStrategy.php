<?php

/**
 * Classe MarkovROIStrategy - Stratégie IA A2
 * 
 * Chaîne de Markov pour déterminer les transitions entre numéros
 * et maximisation du ROI (retour sur investissement)
 * 
 * @package Amelie\Strategies
 */
class MarkovROIStrategy
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
    
    // Matrice de transition
    private $transitionMatrix = [];
    
    // Dernier tirage observé
    private $lastDraw = [];
    
    // Scores ROI pour chaque numéro
    private $roiScores = [];
    
    /**
     * Constructeur
     */
    public function __construct()
    {
        // Initialiser la matrice de transition avec lissage de Laplace
        for ($i = 1; $i <= self::MAX_NUM; $i++) {
            $this->transitionMatrix[$i] = array_fill(1, self::MAX_NUM, 1);
        }
    }
    
    /**
     * Génère une recommandation selon cette stratégie
     * 
     * @return array Informations sur la stratégie
     */
    public function generate()
    {
        // Charger les données et construire la matrice de transition
        $this->buildTransitionMatrix();
        
        // Calculer les scores pour chaque numéro
        $this->calculateScores();
        
        // Générer la meilleure combinaison
        $bestCombination = $this->findBestCombination();
        
        // Calculer l'espérance de gain et le ROI
        $ev = $this->calculateEV($bestCombination);
        $roi = $ev / 8.0; // Pour mise 8€
        
        return [
            'strategyId' => 'markov_roi',
            'label' => 'Markov ROI',
            'numbers' => $bestCombination,
            'ev' => $ev,
            'roi' => $roi
        ];
    }
    
    /**
     * Construit la matrice de transition depuis l'historique des tirages
     */
    private function buildTransitionMatrix()
    {
        $tirages = TirageDataset::getAllTirages();
        
        $prevTirage = null;
        
        foreach ($tirages as $index => $tirage) {
            if (!isset($tirage['all']) && (!isset($tirage['blue']) || !isset($tirage['yellow']))) {
                continue;
            }
            
            $currentNumbers = isset($tirage['all']) ? $tirage['all'] : 
                             array_merge($tirage['blue'], $tirage['yellow']);
            
            // Si ce n'est pas le premier tirage, mettre à jour la matrice de transition
            if ($prevTirage !== null) {
                foreach ($prevTirage as $prevNum) {
                    foreach ($currentNumbers as $currNum) {
                        if ($prevNum >= 1 && $prevNum <= self::MAX_NUM && 
                            $currNum >= 1 && $currNum <= self::MAX_NUM) {
                            $this->transitionMatrix[$prevNum][$currNum]++;
                        }
                    }
                }
            }
            
            $prevTirage = $currentNumbers;
        }
        
        // Stocker le dernier tirage pour les prédictions
        if (!empty($tirages)) {
            $lastTirage = reset($tirages); // Premier élément (le plus récent)
            $this->lastDraw = isset($lastTirage['all']) ? $lastTirage['all'] : 
                             array_merge($lastTirage['blue'], $lastTirage['yellow']);
        } else {
            $this->lastDraw = range(1, 12); // Valeur par défaut si aucun tirage disponible
        }
        
        // Normaliser la matrice de transition
        $this->normalizeTransitionMatrix();
    }
    
    /**
     * Normalise la matrice de transition pour obtenir des probabilités
     */
    private function normalizeTransitionMatrix()
    {
        for ($i = 1; $i <= self::MAX_NUM; $i++) {
            $rowSum = array_sum($this->transitionMatrix[$i]);
            if ($rowSum > 0) {
                for ($j = 1; $j <= self::MAX_NUM; $j++) {
                    $this->transitionMatrix[$i][$j] /= $rowSum;
                }
            }
        }
    }
    
    /**
     * Calcule les scores ROI pour chaque numéro
     */
    private function calculateScores()
    {
        $scores = array_fill(1, self::MAX_NUM, 0);
        
        // Calculer le score de chaque numéro basé sur les transitions
        foreach ($this->lastDraw as $prevNum) {
            if ($prevNum >= 1 && $prevNum <= self::MAX_NUM) {
                for ($j = 1; $j <= self::MAX_NUM; $j++) {
                    $scores[$j] += $this->transitionMatrix[$prevNum][$j];
                }
            }
        }
        
        // Calculer le ROI approximatif pour chaque numéro
        $averageGain = $this->calculateAverageGain();
        
        for ($i = 1; $i <= self::MAX_NUM; $i++) {
            // ROI = (score de probabilité * gain moyen espéré) / 8
            $this->roiScores[$i] = ($scores[$i] * $averageGain) / 8.0;
        }
    }
    
    /**
     * Calcule un gain moyen pour l'approximation du ROI
     * 
     * @return float Gain moyen
     */
    private function calculateAverageGain()
    {
        $totalGain = 0;
        $count = 0;
        
        foreach (self::GAIN_TABLE as $row) {
            $totalGain += $row[4]; // Le gain est en position 4
            $count++;
        }
        
        return $count > 0 ? $totalGain / $count : 0;
    }
    
    /**
     * Trouve la meilleure combinaison selon les scores ROI
     * 
     * @return array Meilleure combinaison de 7 numéros
     */
    private function findBestCombination()
    {
        // Séparer les numéros par catégorie (bleu/jaune) basé sur la fréquence d'apparition
        $tirages = TirageDataset::getAllTirages();
        $blueFreq = array_fill(1, self::MAX_NUM, 0);
        $yellowFreq = array_fill(1, self::MAX_NUM, 0);
        
        foreach ($tirages as $tirage) {
            if (isset($tirage['blue'])) {
                foreach ($tirage['blue'] as $num) {
                    if ($num >= 1 && $num <= self::MAX_NUM) {
                        $blueFreq[$num]++;
                    }
                }
            }
            
            if (isset($tirage['yellow'])) {
                foreach ($tirage['yellow'] as $num) {
                    if ($num >= 1 && $num <= self::MAX_NUM) {
                        $yellowFreq[$num]++;
                    }
                }
            }
        }
        
        // Créer des copies des scores ROI pour le tri
        $blueScores = $this->roiScores;
        $yellowScores = $this->roiScores;
        
        // Pondérer les scores par la fréquence d'apparition en bleu/jaune
        for ($i = 1; $i <= self::MAX_NUM; $i++) {
            $blueScores[$i] *= ($blueFreq[$i] + 1) / ($blueFreq[$i] + $yellowFreq[$i] + 2);
            $yellowScores[$i] *= ($yellowFreq[$i] + 1) / ($blueFreq[$i] + $yellowFreq[$i] + 2);
        }
        
        // Trier par score ROI décroissant
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
        
        // Utiliser les scores calculés pour estimer l'EV
        $totalScore = 0;
        foreach ($combination as $num) {
            $totalScore += isset($this->roiScores[$num]) ? $this->roiScores[$num] : 0;
        }
        
        // Convertir en espérance de gain (estimation)
        $ev = $totalScore * 8.0; // Multiplier par la mise (8€)
        
        return round($ev, 2);
    }
}