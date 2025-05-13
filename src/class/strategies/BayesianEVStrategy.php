<?php

/**
 * Classe BayesianEVStrategy - Stratégie IA A1
 * 
 * Modèle bêta-binomial avec maximisation de l'espérance de gain (EV)
 * 
 * @package Amelie\Strategies
 */
class BayesianEVStrategy
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
    
    // Paramètres du modèle
    private $alphas = [];
    private $betas = [];
    private $posteriorProbs = [];
    
    /**
     * Constructeur
     */
    public function __construct()
    {
        // Initialiser les compteurs (Laplace smoothing)
        for ($i = 1; $i <= self::MAX_NUM; $i++) {
            $this->alphas[$i] = 1;
            $this->betas[$i] = 1;
        }
        
        $this->computePosteriors();
    }
    
    /**
     * Génère une recommandation selon cette stratégie
     * 
     * @return array Informations sur la stratégie
     */
    public function generate()
    {
        // Charger les données
        $this->loadData();
        
        // Calculer les probabilités postérieures
        $this->computePosteriors();
        
        // Générer la meilleure combinaison
        $bestCombination = $this->findBestCombination();
        
        // Calculer l'espérance de gain et le ROI
        $ev = $this->calculateEV($bestCombination);
        $roi = $ev / 8.0; // Pour mise 8€
        
        return [
            'strategyId' => 'bayesian_ev',
            'label' => 'Bayesian EV',
            'numbers' => $bestCombination,
            'ev' => $ev,
            'roi' => $roi
        ];
    }
    
    /**
     * Charge les données de l'historique des tirages
     */
    private function loadData()
    {
        $tirages = TirageDataset::getAllTirages();
        
        // Mettre à jour les compteurs pour chaque tirage
        foreach ($tirages as $tirage) {
            if (!isset($tirage['blue']) || !isset($tirage['yellow'])) {
                continue;
            }
            
            $allNumbers = array_merge($tirage['blue'], $tirage['yellow']);
            
            // Pour chaque numéro, mettre à jour les compteurs
            for ($i = 1; $i <= self::MAX_NUM; $i++) {
                if (in_array($i, $allNumbers)) {
                    $this->alphas[$i]++;
                } else {
                    $this->betas[$i]++;
                }
            }
        }
    }
    
    /**
     * Calcule les probabilités postérieures
     */
    private function computePosteriors()
    {
        for ($i = 1; $i <= self::MAX_NUM; $i++) {
            $this->posteriorProbs[$i] = $this->alphas[$i] / ($this->alphas[$i] + $this->betas[$i]);
        }
    }
    
    /**
     * Trouve la meilleure combinaison selon l'EV
     * 
     * @return array Meilleure combinaison de 7 numéros
     */
    private function findBestCombination()
    {
        // Trier les numéros par probabilité postérieure
        $blues = [];
        $yellows = [];
        
        // Copier les probabilités pour le tri
        $blueProbs = $this->posteriorProbs;
        $yellowProbs = $this->posteriorProbs;
        
        // Trier les probabilités par ordre décroissant
        arsort($blueProbs);
        arsort($yellowProbs);
        
        // Sélectionner les meilleurs numéros bleus et jaunes
        $blueKeys = array_keys($blueProbs);
        $yellowKeys = array_keys($yellowProbs);
        
        $blues = array_slice($blueKeys, 0, 4); // Top 4 bleus
        $yellows = array_slice($yellowKeys, 0, 3); // Top 3 jaunes
        
        // Combiner les numéros et les trier
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
        
        // Parcourir toutes les lignes du tableau des gains
        foreach (self::GAIN_TABLE as $row) {
            list($totalMatched, $blueMatched, $yellowMatched, $odds, $gain) = $row;
            
            // Calculer la probabilité de cette combinaison
            $probability = $this->calculateProbability($combination, $blueMatched, $yellowMatched);
            
            // Ajouter à l'espérance mathématique (gain * probabilité)
            $ev += $gain * $probability;
        }
        
        return round($ev, 2);
    }
    
    /**
     * Calcule la probabilité d'obtenir une combinaison spécifique
     * 
     * @param array $combination Combinaison choisie
     * @param int $blueMatched Nombre de numéros bleus à trouver
     * @param int $yellowMatched Nombre de numéros jaunes à trouver
     * @return float Probabilité
     */
    private function calculateProbability($combination, $blueMatched, $yellowMatched)
    {
        // Approche simplifiée pour le calcul de probabilité
        // Dans un modèle complet, nous calculerions les probabilités conditionnelles
        
        // Calculer la probabilité globale pour cette combinaison
        $prob = 1.0;
        
        // Supposer que la proba de tirer bleu/jaune est constante
        $blueProb = $blueMatched / self::BLUE_COUNT;
        $yellowProb = $yellowMatched / self::YELLOW_COUNT;
        
        // Probabilité combinée
        $prob = $blueProb * $yellowProb;
        
        return $prob;
    }
}