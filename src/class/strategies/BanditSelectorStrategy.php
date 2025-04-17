<?php

/**
 * Classe BanditSelectorStrategy - Stratégie IA A4
 * 
 * Algorithme de bandit multi-bras avec politique epsilon-greedy
 * Sélectionne parmi les autres stratégies celle qui semble la plus prometteuse
 * 
 * @package Amelie\Strategies
 */
class BanditSelectorStrategy
{
    // Paramètres de configuration
    const MAX_NUM = 28;
    const BLUE_COUNT = 7;
    const YELLOW_COUNT = 5;
    const PLAYER_PICK = 7;
    
    // Paramètre epsilon pour l'exploration (10%)
    const EPSILON = 0.1;
    
    // Stratégies disponibles
    private $strategies = [];
    
    // Données cumulées pour chaque stratégie
    private $strategyStats = [];
    
    /**
     * Constructeur
     */
    public function __construct()
    {
        // Initialiser les stratégies disponibles
        $this->strategies = [
            'bayesian_ev' => new BayesianEVStrategy(),
            'markov_roi' => new MarkovROIStrategy(),
            'ml_predict' => new MLPredictStrategy(),
            'legacy_4b3j' => null // Sera simulé
        ];
        
        // Initialiser les statistiques
        foreach (array_keys($this->strategies) as $stratId) {
            $this->strategyStats[$stratId] = [
                'rewards' => [], // Historique des récompenses
                'avgReward' => 0, // Récompense moyenne
                'count' => 0     // Nombre de sélections
            ];
        }
    }
    
    /**
     * Génère une recommandation selon cette stratégie
     * 
     * @return array Informations sur la stratégie
     */
    public function generate()
    {
        // Simuler l'évaluation des stratégies sur les données historiques
        $this->evaluateStrategies();
        
        // Sélectionner la stratégie selon la politique epsilon-greedy
        $selectedStrategy = $this->selectStrategy();
        
        // Obtenir la combinaison de la stratégie sélectionnée
        $combination = $this->getStrategyOutput($selectedStrategy);
        
        // Calculer l'espérance et le ROI en fonction de la stratégie choisie
        $stats = $this->strategyStats[$selectedStrategy];
        $ev = max(1, $stats['avgReward']) * 8.0; // Convertir ROI en EV
        $roi = $ev / 8.0;
        
        return [
            'strategyId' => 'bandit_selector',
            'label' => 'Bandit Selector ε-Greedy',
            'numbers' => $combination,
            'ev' => $ev,
            'roi' => $roi,
            'selectedStrategy' => $selectedStrategy
        ];
    }
    
    /**
     * Évalue les performances des stratégies sur les données historiques
     */
    private function evaluateStrategies()
    {
        $tirages = TirageDataset::getLastTirages(50); // Utiliser les 50 derniers tirages
        
        // Pour chaque stratégie, simuler son utilisation sur ces tirages
        foreach (array_keys($this->strategies) as $stratId) {
            $totalReward = 0;
            $count = 0;
            
            // Simuler l'application de la stratégie sur chaque tirage
            foreach ($tirages as $index => $tirage) {
                if ($index < count($tirages) - 1) { // Éviter le dernier tirage (pas de résultat connu)
                    $nextTirage = $tirages[$index + 1];
                    
                    // Obtenir la combinaison suggérée par cette stratégie
                    $combination = $this->simulateStrategy($stratId, array_slice($tirages, 0, $index + 1));
                    
                    // Calculer le gain pour cette combinaison
                    $reward = $this->calculateReward($combination, $nextTirage);
                    
                    // Mettre à jour les statistiques
                    $this->strategyStats[$stratId]['rewards'][] = $reward;
                    $totalReward += $reward;
                    $count++;
                }
            }
            
            // Calculer la récompense moyenne
            if ($count > 0) {
                $this->strategyStats[$stratId]['avgReward'] = $totalReward / $count;
            }
            $this->strategyStats[$stratId]['count'] = $count;
        }
    }
    
    /**
     * Sélectionne une stratégie selon la politique epsilon-greedy
     * 
     * @return string Identifiant de la stratégie sélectionnée
     */
    private function selectStrategy()
    {
        // Avec probabilité epsilon, explorer (sélection aléatoire)
        if (mt_rand() / mt_getrandmax() < self::EPSILON) {
            $strategies = array_keys($this->strategies);
            return $strategies[array_rand($strategies)];
        }
        
        // Sinon, exploiter (sélectionner la meilleure stratégie)
        $bestStrategy = null;
        $bestReward = -INF;
        
        foreach ($this->strategyStats as $stratId => $stats) {
            if ($stats['count'] > 0 && $stats['avgReward'] > $bestReward) {
                $bestReward = $stats['avgReward'];
                $bestStrategy = $stratId;
            }
        }
        
        // Si aucune stratégie n'a été évaluée, en choisir une au hasard
        if ($bestStrategy === null) {
            $strategies = array_keys($this->strategies);
            return $strategies[array_rand($strategies)];
        }
        
        return $bestStrategy;
    }
    
    /**
     * Simule l'exécution d'une stratégie sur un ensemble de tirages
     * 
     * @param string $stratId Identifiant de la stratégie
     * @param array $historicalTirages Tirages historiques
     * @return array Combinaison suggérée par la stratégie
     */
    private function simulateStrategy($stratId, $historicalTirages)
    {
        // Pour les stratégies réelles, utiliser l'instance
        if (isset($this->strategies[$stratId]) && $this->strategies[$stratId] !== null) {
            $result = $this->strategies[$stratId]->generate();
            return $result['numbers'];
        }
        
        // Pour la stratégie 'legacy_4b3j', simuler une sélection 4 bleus + 3 jaunes
        if ($stratId === 'legacy_4b3j') {
            // Sélectionner les numéros les plus fréquents en position bleue/jaune
            $blueFreq = array_fill(1, self::MAX_NUM, 0);
            $yellowFreq = array_fill(1, self::MAX_NUM, 0);
            
            foreach ($historicalTirages as $tirage) {
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
            
            // Trier par fréquence
            arsort($blueFreq);
            arsort($yellowFreq);
            
            // Sélectionner les 4 meilleurs bleus et 3 meilleurs jaunes
            $blues = array_slice(array_keys($blueFreq), 0, 4);
            $yellows = array_slice(array_keys($yellowFreq), 0, 3);
            
            $combination = array_merge($blues, $yellows);
            sort($combination);
            
            return $combination;
        }
        
        // Par défaut, retourner une combinaison aléatoire
        $numbers = range(1, self::MAX_NUM);
        shuffle($numbers);
        return array_slice($numbers, 0, self::PLAYER_PICK);
    }
    
    /**
     * Calcule la récompense pour une combinaison sur un tirage réel
     * 
     * @param array $combination Combinaison suggérée
     * @param array $tirage Tirage réel
     * @return float Récompense (gain normalisé par la mise)
     */
    private function calculateReward($combination, $tirage)
    {
        // Extraire les numéros du tirage
        $drawnNumbers = [];
        if (isset($tirage['blue'])) {
            $drawnNumbers = array_merge($drawnNumbers, $tirage['blue']);
        }
        if (isset($tirage['yellow'])) {
            $drawnNumbers = array_merge($drawnNumbers, $tirage['yellow']);
        }
        
        // Compter les correspondances
        $matchCount = count(array_intersect($combination, $drawnNumbers));
        
        // Calculer la récompense (simulée)
        switch ($matchCount) {
            case 7: return 100.0; // Jackpot (simplifié)
            case 6: return 10.0;
            case 5: return 2.0;
            case 4: return 1.0;
            default: return 0.0;
        }
    }
    
    /**
     * Obtient la combinaison suggérée par une stratégie
     * 
     * @param string $stratId Identifiant de la stratégie
     * @return array Combinaison suggérée
     */
    private function getStrategyOutput($stratId)
    {
        // Générer la sortie de la stratégie sélectionnée
        if (isset($this->strategies[$stratId]) && $this->strategies[$stratId] !== null) {
            $result = $this->strategies[$stratId]->generate();
            return $result['numbers'];
        }
        
        // Pour la stratégie legacy, construire une combinaison 4B-3J
        if ($stratId === 'legacy_4b3j') {
            return $this->simulateStrategy($stratId, TirageDataset::getLastTirages(50));
        }
        
        // Fallback: générer une combinaison aléatoire
        $numbers = range(1, self::MAX_NUM);
        shuffle($numbers);
        return array_slice($numbers, 0, self::PLAYER_PICK);
    }
}