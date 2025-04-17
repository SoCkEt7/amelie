<?php

/**
 * Classe TirageStrategies - Version simplifiée
 * 
 * Implémente différentes stratégies optimisées pour le jeu Amigo
 * basées sur l'analyse statistique des données historiques et le tableau des gains.
 * 
 * @package Amelie
 */
class TirageStrategies {
    // Données historiques et récentes
    private $historicalData;
    private $recentData;
    
    // Stockage des stratégies calculées
    private $strategies = [];
    
    // Constantes de configuration
    const MAX_NUM = 28;                // Nombre maximum (1-28)
    const TIRAGE_SIZE = 12;            // Taille d'un tirage (7 bleus + 5 jaunes)
    const BLUE_COUNT = 7;              // Nombre de numéros bleus par tirage
    const YELLOW_COUNT = 5;            // Nombre de numéros jaunes par tirage
    const PLAYER_PICK = 7;             // Nombre de numéros à choisir par le joueur
    
    // Tableau des gains et probabilités
    private $gainTable = [
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
        [6, 1, 5, 10571.79, 40]
        // Les autres combinaisons ont des gains plus faibles
    ];
    
    /**
     * Constructeur
     * 
     * @param array $historicalData Données historiques des tirages
     * @param array $recentData Données récentes des tirages
     */
    public function __construct($historicalData, $recentData) {
        $this->historicalData = $historicalData;
        $this->recentData = $recentData;
        
        // Générer toutes les stratégies disponibles
        $this->generateAllStrategies();
    }
    
    /**
     * Récupère les stratégies calculées
     * 
     * @return array Liste des stratégies
     */
    public function getStrategies() {
        return $this->strategies;
    }
    
    /**
     * Génère toutes les stratégies disponibles
     */
    private function generateAllStrategies() {
        // Extraction des données de base
        $frequency = isset($this->historicalData['frequency']) ? $this->historicalData['frequency'] : [];
        $numbers = isset($this->historicalData['numbers']) ? $this->historicalData['numbers'] : [];
        
        // Calculer les fréquences spécifiques bleues et jaunes
        $blueFrequency = $this->calculateBlueFrequency($numbers);
        $yellowFrequency = $this->calculateYellowFrequency($numbers);
        
        // Stratégies basées sur les patterns simples
        $this->strategies[] = $this->generateMostFrequentStrategy($frequency);
        $this->strategies[] = $this->generateLeastFrequentStrategy($frequency);
        
        // Stratégies basées sur les positions et le tableau des gains
        $this->strategies[] = $this->generateBlueMaxStrategy($blueFrequency);
        $this->strategies[] = $this->generateOptimalBalanceStrategy($blueFrequency, $yellowFrequency);
        $this->strategies[] = $this->generate5B2JStrategy($blueFrequency, $yellowFrequency);
        $this->strategies[] = $this->generateROIMaxStrategy($blueFrequency, $yellowFrequency, $frequency);
        
        // Stratégies basées sur l'analyse temporelle
        $this->strategies[] = $this->generateHotNumbersStrategy($numbers);
        $this->strategies[] = $this->generateCyclicStrategy($numbers);
        
        // Stratégies avancées
        $this->strategies[] = $this->generateClustersStrategy($numbers);
        $this->strategies[] = $this->generateMixedProbabilityStrategy($blueFrequency, $yellowFrequency, $frequency);
        
        // Ajouter la meilleure stratégie IA v2
        $this->strategies[] = $this->generateIAv2Strategy();
        
        // Trier les stratégies par note (rating) décroissante
        usort($this->strategies, function($a, $b) {
            return $b['rating'] <=> $a['rating'];
        });
    }
    
    /**
     * Stratégie IA v2 - utilise le meilleur choix de l'AIStrategyManager
     * 
     * @return array Stratégie basée sur l'IA
     */
    private function generateIAv2Strategy() {
        // Utiliser AIStrategyManager pour obtenir la meilleure stratégie
        $bestPick = AIStrategyManager::bestPick();
        
        return [
            'name' => 'IA v2',
            'description' => 'Combinaison optimale sélectionnée par intelligence artificielle (' . $bestPick['label'] . ')',
            'numbers' => $bestPick['numbers'],
            'rating' => 9.2, // Note élevée pour cette stratégie avancée
            'class' => 'info',
            'method' => 'Intelligence artificielle',
            'bestPlayCount' => self::PLAYER_PICK,
            'optimalBet' => '8€'
        ];
    }
    
    /**
     * 1. Stratégie des numéros les plus fréquents
     * Sélectionne les numéros qui apparaissent le plus souvent dans les tirages
     */
    private function generateMostFrequentStrategy($frequency) {
        // Trier par fréquence décroissante
        arsort($frequency);
        
        // Sélectionner les 7 numéros les plus fréquents (PLAYER_PICK)
        $selectedNumbers = array_slice(array_keys($frequency), 0, self::PLAYER_PICK);
        sort($selectedNumbers);
        
        return [
            'name' => 'Numéros Fréquents',
            'description' => 'Sélectionne les numéros qui apparaissent le plus souvent dans les tirages historiques',
            'numbers' => $selectedNumbers,
            'rating' => 8.1,
            'class' => 'primary',
            'method' => 'Analyse de fréquence globale',
            'bestPlayCount' => self::PLAYER_PICK,
            'optimalBet' => '4€'
        ];
    }
    
    /**
     * 2. Stratégie des numéros les moins fréquents
     * Sélectionne les numéros qui apparaissent le moins souvent (théorie de la compensation)
     */
    private function generateLeastFrequentStrategy($frequency) {
        // Initialiser une fréquence pour tous les numéros
        $completeFrequency = [];
        for ($i = 1; $i <= self::MAX_NUM; $i++) {
            $completeFrequency[$i] = isset($frequency[$i]) ? $frequency[$i] : 0;
        }
        
        // Trier par fréquence croissante
        asort($completeFrequency);
        
        // Sélectionner les 7 numéros les moins fréquents (PLAYER_PICK)
        $selectedNumbers = array_slice(array_keys($completeFrequency), 0, self::PLAYER_PICK);
        sort($selectedNumbers);
        
        return [
            'name' => 'Numéros Rares',
            'description' => 'Sélectionne les numéros qui apparaissent rarement (théorie de la compensation)',
            'numbers' => $selectedNumbers,
            'rating' => 7.8,
            'class' => 'danger',
            'method' => 'Analyse inverse de fréquence',
            'bestPlayCount' => self::PLAYER_PICK,
            'optimalBet' => '4€'
        ];
    }
    
    /**
     * 3. Stratégie Bleue Maximum
     * Vise à obtenir 7 numéros bleus pour le jackpot maximal
     */
    private function generateBlueMaxStrategy($blueFrequency) {
        // Trier par fréquence bleue décroissante
        arsort($blueFrequency);
        
        // Sélectionner les 7 numéros (PLAYER_PICK) apparaissant le plus souvent comme bleus
        $selectedNumbers = array_slice(array_keys($blueFrequency), 0, self::PLAYER_PICK);
        sort($selectedNumbers);
        
        return [
            'name' => 'Stratégie Bleue Maximum',
            'description' => 'Vise le jackpot maximal (7 bleus, 0 jaunes -> 100 000€ pour 8€)',
            'numbers' => $selectedNumbers,
            'rating' => 8.5,
            'class' => 'info',
            'method' => 'Analyse de position bleue',
            'bestPlayCount' => self::PLAYER_PICK,
            'optimalBet' => '8€'
        ];
    }
    
    /**
     * 4. Stratégie d'Équilibre Optimal (4B-3J)
     * Vise la combinaison 4 bleus, 3 jaunes pour le meilleur rapport probabilité/gain
     */
    private function generateOptimalBalanceStrategy($blueFrequency, $yellowFrequency) {
        // Trier par fréquence
        arsort($blueFrequency);
        arsort($yellowFrequency);
        
        // Sélectionner les 4 meilleurs numéros bleus et les 3 meilleurs numéros jaunes
        $blueSelected = array_slice(array_keys($blueFrequency), 0, 4);
        $yellowSelected = array_slice(array_keys($yellowFrequency), 0, 3);
        
        // Combiner et trier
        $selectedNumbers = array_merge($blueSelected, $yellowSelected);
        sort($selectedNumbers);
        
        return [
            'name' => 'Équilibre Optimal (4B-3J)',
            'description' => 'Vise la combinaison avec le meilleur rapport probabilité/gain',
            'numbers' => $selectedNumbers,
            'rating' => 9.1,
            'class' => 'success',
            'method' => 'Analyse de position spécifique',
            'bestPlayCount' => self::PLAYER_PICK,
            'optimalBet' => '8€'
        ];
    }
    
    /**
     * 5. Stratégie 5B-2J
     * Vise spécifiquement la combinaison 5 bleus, 2 jaunes
     */
    private function generate5B2JStrategy($blueFrequency, $yellowFrequency) {
        // Trier par fréquence
        arsort($blueFrequency);
        arsort($yellowFrequency);
        
        // Sélectionner les 5 meilleurs numéros bleus et les 2 meilleurs numéros jaunes
        $blueSelected = array_slice(array_keys($blueFrequency), 0, 5);
        $yellowSelected = array_slice(array_keys($yellowFrequency), 0, 2);
        
        // Combiner et trier
        $selectedNumbers = array_merge($blueSelected, $yellowSelected);
        sort($selectedNumbers);
        
        return [
            'name' => 'Stratégie 5B-2J',
            'description' => 'Bon équilibre entre probabilité (1/5638) et gain (480€ pour 8€)',
            'numbers' => $selectedNumbers,
            'rating' => 8.8,
            'class' => 'warning',
            'method' => 'Analyse de position spécifique',
            'bestPlayCount' => self::PLAYER_PICK,
            'optimalBet' => '8€'
        ];
    }
    
    /**
     * 6. Stratégie ROI Maximal
     * Optimisation pointue du retour sur investissement
     */
    private function generateROIMaxStrategy($blueFrequency, $yellowFrequency, $frequency) {
        // Calculer un score ROI pour chaque numéro
        $roiScores = [];
        
        // Normaliser les fréquences
        $totalBlue = array_sum($blueFrequency);
        $totalYellow = array_sum($yellowFrequency);
        $totalFreq = array_sum($frequency);
        
        for ($i = 1; $i <= self::MAX_NUM; $i++) {
            $blueProb = ($totalBlue > 0) ? ($blueFrequency[$i] / $totalBlue) : 0;
            $yellowProb = ($totalYellow > 0) ? ($yellowFrequency[$i] / $totalYellow) : 0;
            $globalFreq = ($totalFreq > 0) ? (isset($frequency[$i]) ? $frequency[$i] / $totalFreq : 0) : 0;
            
            // Formule de ROI: combinaison pondérée des probabilités selon les gains potentiels
            // Plus de poids pour les positions bleues (gains plus élevés)
            $roiScores[$i] = ($blueProb * 0.6) + ($yellowProb * 0.2) + ($globalFreq * 0.2);
        }
        
        // Trier par score ROI décroissant
        arsort($roiScores);
        
        // Sélectionner les 7 meilleurs numéros ROI
        $selectedNumbers = array_slice(array_keys($roiScores), 0, self::PLAYER_PICK);
        sort($selectedNumbers);
        
        return [
            'name' => 'ROI Maximal',
            'description' => 'Optimisation pointue du retour sur investissement',
            'numbers' => $selectedNumbers,
            'rating' => 8.9,
            'class' => 'primary',
            'method' => 'Formule complexe intégrant fréquence, position et gains',
            'bestPlayCount' => self::PLAYER_PICK,
            'optimalBet' => '8€'
        ];
    }
    
    /**
     * 7. Stratégie des numéros chauds
     * Sélectionne les numéros récemment sortis souvent
     */
    private function generateHotNumbersStrategy($numbers) {
        // Prendre les 200 derniers tirages pour déterminer les numéros chauds
        $recentNumbers = array_slice($numbers, 0, min(200, count($numbers)));
        
        // Calculer la fréquence dans cet échantillon récent
        $hotFrequency = [];
        foreach ($recentNumbers as $tirage) {
            $allNumbers = [];
            
            // Extraire tous les numéros du tirage selon la structure
            if (isset($tirage['blue']) && is_array($tirage['blue'])) {
                $allNumbers = array_merge($allNumbers, $tirage['blue']);
            }
            if (isset($tirage['yellow']) && is_array($tirage['yellow'])) {
                $allNumbers = array_merge($allNumbers, $tirage['yellow']);
            }
            if (empty($allNumbers) && isset($tirage['all']) && is_array($tirage['all'])) {
                $allNumbers = $tirage['all'];
            }
            
            // Compter les fréquences
            foreach ($allNumbers as $num) {
                if ($num >= 1 && $num <= self::MAX_NUM) {
                    if (isset($hotFrequency[$num])) {
                        $hotFrequency[$num]++;
                    } else {
                        $hotFrequency[$num] = 1;
                    }
                }
            }
        }
        
        // Trier par fréquence décroissante
        arsort($hotFrequency);
        
        // Sélectionner les 7 numéros les plus "chauds" (PLAYER_PICK)
        $selectedNumbers = array_slice(array_keys($hotFrequency), 0, self::PLAYER_PICK);
        sort($selectedNumbers);
        
        return [
            'name' => 'Numéros Chauds',
            'description' => 'Sélectionne les numéros qui sont sortis fréquemment dans les tirages récents',
            'numbers' => $selectedNumbers,
            'rating' => 8.3,
            'class' => 'danger',
            'method' => 'Analyse des 200 derniers tirages',
            'bestPlayCount' => self::PLAYER_PICK,
            'optimalBet' => '6€'
        ];
    }
    
    /**
     * 8. Stratégie Cyclique/Tendances
     * Détecte les cycles et tendances dans les tirages
     */
    private function generateCyclicStrategy($numbers) {
        // Calculer la maturité pour chaque numéro
        $maturityScores = [];
        
        for ($i = 1; $i <= self::MAX_NUM; $i++) {
            $appearances = $this->getNumberAppearances($i, $numbers);
            $intervals = $this->calculateIntervals($appearances);
            
            if (empty($intervals)) {
                $maturityScores[$i] = 0;
                continue;
            }
            
            // Calculer l'intervalle moyen
            $avgInterval = array_sum($intervals) / count($intervals);
            
            // Calculer le temps depuis la dernière apparition
            $lastAppearance = end($appearances);
            $timeSinceLast = count($numbers) - $lastAppearance;
            
            // La maturité est le ratio du temps écoulé par rapport à l'intervalle moyen
            $maturity = $avgInterval > 0 ? $timeSinceLast / $avgInterval : 0;
            
            // Score final: plus le numéro est "mûr", plus son score est élevé
            $maturityScores[$i] = $maturity;
        }
        
        // Trier par score de maturité décroissant
        arsort($maturityScores);
        
        // Sélectionner les 7 numéros les plus "mûrs"
        $selectedNumbers = array_slice(array_keys($maturityScores), 0, self::PLAYER_PICK);
        sort($selectedNumbers);
        
        return [
            'name' => 'Cyclique/Tendances',
            'description' => 'Détecte les cycles et tendances dans les tirages',
            'numbers' => $selectedNumbers,
            'rating' => 8.2,
            'class' => 'secondary',
            'method' => 'Analyse des intervalles d\'apparition',
            'bestPlayCount' => self::PLAYER_PICK,
            'optimalBet' => '6€'
        ];
    }
    
    /**
     * 9. Stratégie des Clusters
     * Identifie les groupes de numéros apparaissant souvent ensemble
     */
    private function generateClustersStrategy($numbers) {
        // Construire une matrice de corrélation entre les numéros
        $correlationMatrix = $this->buildCorrelationMatrix($numbers);
        
        // Calculer un score de "force de cluster" pour chaque numéro
        $clusterScores = [];
        
        for ($i = 1; $i <= self::MAX_NUM; $i++) {
            $clusterScores[$i] = 0;
            
            // Somme des corrélations avec tous les autres numéros
            for ($j = 1; $j <= self::MAX_NUM; $j++) {
                if ($i != $j) {
                    $clusterScores[$i] += isset($correlationMatrix[$i][$j]) ? $correlationMatrix[$i][$j] : 0;
                }
            }
        }
        
        // Trier par score de cluster
        arsort($clusterScores);
        
        // Sélectionner les 7 numéros avec les meilleurs scores de cluster
        $selectedNumbers = array_slice(array_keys($clusterScores), 0, self::PLAYER_PICK);
        sort($selectedNumbers);
        
        return [
            'name' => 'Clusters',
            'description' => 'Identifie les groupes de numéros apparaissant souvent ensemble',
            'numbers' => $selectedNumbers,
            'rating' => 8.0,
            'class' => 'info',
            'method' => 'Analyse de corrélation entre numéros',
            'bestPlayCount' => self::PLAYER_PICK,
            'optimalBet' => '6€'
        ];
    }
    
    /**
     * 10. Stratégie Mix Probabilisé
     * Pondère les numéros selon leur espérance mathématique de gain
     */
    private function generateMixedProbabilityStrategy($blueFrequency, $yellowFrequency, $frequency) {
        // Calcul d'espérance mathématique pour chaque numéro
        $expectedValues = [];
        
        // Normaliser les fréquences
        $totalBlue = array_sum($blueFrequency);
        $totalYellow = array_sum($yellowFrequency);
        
        for ($i = 1; $i <= self::MAX_NUM; $i++) {
            $blueProb = ($totalBlue > 0) ? ($blueFrequency[$i] / $totalBlue) : 0;
            $yellowProb = ($totalYellow > 0) ? ($yellowFrequency[$i] / $totalYellow) : 0;
            
            // Calculer l'espérance mathématique basée sur le tableau des gains
            $expectedValue = 0;
            
            foreach ($this->gainTable as $gainRow) {
                list($totalMatched, $blueMatched, $yellowMatched, $odds, $gain) = $gainRow;
                
                // Probabilité simplifiée de faire partie de cette combinaison gagnante
                $probContribution = ($blueProb * $blueMatched / self::BLUE_COUNT) + 
                                   ($yellowProb * $yellowMatched / self::YELLOW_COUNT);
                
                // Ajouter à l'espérance mathématique
                $expectedValue += ($probContribution * $gain) / $odds;
            }
            
            $expectedValues[$i] = $expectedValue;
        }
        
        // Trier par espérance mathématique décroissante
        arsort($expectedValues);
        
        // Sélectionner les 7 numéros avec la meilleure espérance mathématique
        $selectedNumbers = array_slice(array_keys($expectedValues), 0, self::PLAYER_PICK);
        sort($selectedNumbers);
        
        return [
            'name' => 'Mix Probabilisé',
            'description' => 'Pondère les numéros selon leur espérance mathématique de gain',
            'numbers' => $selectedNumbers,
            'rating' => 8.6,
            'class' => 'success',
            'method' => 'Calcul précis des probabilités et des gains espérés',
            'bestPlayCount' => self::PLAYER_PICK,
            'optimalBet' => '8€'
        ];
    }
    
    /**
     * Calcule la fréquence d'apparition d'un numéro en position "bleue"
     */
    private function calculateBlueFrequency($numbers) {
        $blueFrequency = array_fill(1, self::MAX_NUM, 0);
        
        // Vérifier si les données sont structurées avec blue/yellow ou simplement un tableau plat
        $isStructured = false;
        if (!empty($numbers) && is_array($numbers) && isset(reset($numbers)['blue'])) {
            $isStructured = true;
        }
        
        if ($isStructured) {
            // Structure avec blue/yellow
            foreach ($numbers as $tirage) {
                if (!isset($tirage['blue']) || !is_array($tirage['blue'])) {
                    continue;
                }
                
                // Incrémenter la fréquence des numéros bleus
                foreach ($tirage['blue'] as $num) {
                    if ($num >= 1 && $num <= self::MAX_NUM) {
                        $blueFrequency[$num]++;
                    }
                }
            }
        } else {
            // Tableau plat - utiliser les 7 premiers numéros (hypothèse basée sur les règles du jeu)
            $chunks = array_chunk($numbers, self::TIRAGE_SIZE);
            foreach ($chunks as $tirage) {
                // Considérer les 7 premiers comme bleus
                for ($i = 0; $i < min(self::BLUE_COUNT, count($tirage)); $i++) {
                    $num = $tirage[$i];
                    if ($num >= 1 && $num <= self::MAX_NUM) {
                        $blueFrequency[$num]++;
                    }
                }
            }
        }
        
        return $blueFrequency;
    }
    
    /**
     * Calcule la fréquence d'apparition d'un numéro en position "jaune"
     */
    private function calculateYellowFrequency($numbers) {
        $yellowFrequency = array_fill(1, self::MAX_NUM, 0);
        
        // Vérifier si les données sont structurées avec blue/yellow ou simplement un tableau plat
        $isStructured = false;
        if (!empty($numbers) && is_array($numbers) && isset(reset($numbers)['yellow'])) {
            $isStructured = true;
        }
        
        if ($isStructured) {
            // Structure avec blue/yellow
            foreach ($numbers as $tirage) {
                if (!isset($tirage['yellow']) || !is_array($tirage['yellow'])) {
                    continue;
                }
                
                // Incrémenter la fréquence des numéros jaunes
                foreach ($tirage['yellow'] as $num) {
                    if ($num >= 1 && $num <= self::MAX_NUM) {
                        $yellowFrequency[$num]++;
                    }
                }
            }
        } else {
            // Tableau plat - utiliser les 5 derniers numéros (hypothèse basée sur les règles du jeu)
            $chunks = array_chunk($numbers, self::TIRAGE_SIZE);
            foreach ($chunks as $tirage) {
                // Considérer les 5 derniers comme jaunes
                $count = count($tirage);
                for ($i = self::BLUE_COUNT; $i < min($count, self::TIRAGE_SIZE); $i++) {
                    $num = $tirage[$i];
                    if ($num >= 1 && $num <= self::MAX_NUM) {
                        $yellowFrequency[$num]++;
                    }
                }
            }
        }
        
        return $yellowFrequency;
    }
    
    /**
     * Obtient les indices de tirage où un numéro est apparu
     */
    private function getNumberAppearances($number, $numbers) {
        $appearances = [];
        
        // Vérifier si les données sont structurées avec blue/yellow ou simplement un tableau plat
        $isStructured = false;
        if (!empty($numbers) && is_array($numbers) && isset(reset($numbers)['blue'])) {
            $isStructured = true;
        }
        
        if ($isStructured) {
            // Structure avec blue/yellow
            foreach ($numbers as $index => $tirage) {
                $allNumbers = array_merge(
                    isset($tirage['blue']) ? $tirage['blue'] : [],
                    isset($tirage['yellow']) ? $tirage['yellow'] : []
                );
                
                if (in_array($number, $allNumbers)) {
                    $appearances[] = $index;
                }
            }
        } else {
            // Tableau plat - chercher le numéro dans tous les tirages
            $chunks = array_chunk($numbers, self::TIRAGE_SIZE);
            foreach ($chunks as $index => $tirage) {
                if (in_array($number, $tirage)) {
                    $appearances[] = $index;
                }
            }
        }
        
        return $appearances;
    }
    
    /**
     * Calcule les intervalles entre les apparitions successives
     */
    private function calculateIntervals($appearances) {
        $intervals = [];
        $count = count($appearances);
        
        for ($i = 1; $i < $count; $i++) {
            $intervals[] = $appearances[$i] - $appearances[$i - 1];
        }
        
        return $intervals;
    }
    
    /**
     * Construit une matrice de corrélation entre tous les numéros
     */
    private function buildCorrelationMatrix($numbers) {
        $matrix = [];
        
        // Initialiser la matrice
        for ($i = 1; $i <= self::MAX_NUM; $i++) {
            for ($j = 1; $j <= self::MAX_NUM; $j++) {
                $matrix[$i][$j] = 0;
            }
        }
        
        // Vérifier si les données sont structurées avec blue/yellow ou simplement un tableau plat
        $isStructured = false;
        if (!empty($numbers) && is_array($numbers) && isset(reset($numbers)['blue'])) {
            $isStructured = true;
        }
        
        if ($isStructured) {
            // Structure avec blue/yellow
            foreach ($numbers as $tirage) {
                $allNumbers = array_merge(
                    isset($tirage['blue']) ? $tirage['blue'] : [],
                    isset($tirage['yellow']) ? $tirage['yellow'] : []
                );
                
                // Pour chaque paire de numéros dans ce tirage
                for ($i = 0; $i < count($allNumbers); $i++) {
                    for ($j = $i + 1; $j < count($allNumbers); $j++) {
                        $num1 = $allNumbers[$i];
                        $num2 = $allNumbers[$j];
                        
                        // Incrémenter la corrélation dans les deux sens
                        if ($num1 >= 1 && $num1 <= self::MAX_NUM && $num2 >= 1 && $num2 <= self::MAX_NUM) {
                            $matrix[$num1][$num2]++;
                            $matrix[$num2][$num1]++;
                        }
                    }
                }
            }
        } else {
            // Tableau plat - traiter par groupes de 12 (taille d'un tirage)
            $chunks = array_chunk($numbers, self::TIRAGE_SIZE);
            foreach ($chunks as $tirage) {
                // Pour chaque paire de numéros dans ce tirage
                for ($i = 0; $i < count($tirage); $i++) {
                    for ($j = $i + 1; $j < count($tirage); $j++) {
                        $num1 = $tirage[$i];
                        $num2 = $tirage[$j];
                        
                        // Incrémenter la corrélation dans les deux sens
                        if ($num1 >= 1 && $num1 <= self::MAX_NUM && $num2 >= 1 && $num2 <= self::MAX_NUM) {
                            $matrix[$num1][$num2]++;
                            $matrix[$num2][$num1]++;
                        }
                    }
                }
            }
        }
        
        // Normaliser la matrice
        $maxCorrelation = 1;
        for ($i = 1; $i <= self::MAX_NUM; $i++) {
            for ($j = 1; $j <= self::MAX_NUM; $j++) {
                if ($matrix[$i][$j] > $maxCorrelation) {
                    $maxCorrelation = $matrix[$i][$j];
                }
            }
        }
        
        if ($maxCorrelation > 0) {
            for ($i = 1; $i <= self::MAX_NUM; $i++) {
                for ($j = 1; $j <= self::MAX_NUM; $j++) {
                    $matrix[$i][$j] /= $maxCorrelation;
                }
            }
        }
        
        return $matrix;
    }
}