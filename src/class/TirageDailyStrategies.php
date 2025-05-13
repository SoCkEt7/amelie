<?php

/**
 * Classe TirageDailyStrategies
 * 
 * Implémente différentes stratégies basées uniquement sur les tirages du jour
 * pour le jeu Amigo
 * 
 * @package Amelie
 */
class TirageDailyStrategies {
    // Données des tirages du jour
    private $dailyTirages = [];
    
    // Paramètres de configuration
    const MAX_NUM = 28;                // Nombre maximum (1-28)
    const TIRAGE_SIZE = 12;            // Taille d'un tirage (7 bleus + 5 jaunes)
    const BLUE_COUNT = 7;              // Nombre de numéros bleus par tirage
    const YELLOW_COUNT = 5;            // Nombre de numéros jaunes par tirage
    const PLAYER_PICK = 7;             // Nombre de numéros à choisir par le joueur
    
    // Stockage des stratégies
    private $strategies = [];
    
    /**
     * Constructeur
     * 
     * @param array $dailyTirages Tirages du jour
     */
    public function __construct($dailyTirages = []) {
        $this->dailyTirages = $dailyTirages;
        
        // Générer toutes les stratégies
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
     * Calcule et retourne les tirages du jour uniquement
     * 
     * @param TirageDataFetcher $dataFetcher Instance du récupérateur de données
     * @return array Les tirages du jour
     */
    public static function getDailyTirages($dataFetcher) {
        // Récupérer les données de tirage - pas de limite pour avoir tous les tirages du jour
        $historicalData = $dataFetcher->getHistoricalTirages(500); // Augmenté à 500 pour être sûr d'avoir tous les tirages
        
        // Filtrer pour ne garder que les tirages du jour (aujourd'hui)
        $today = date('Y-m-d');
        $dailyTirages = [];
        
        if (!empty($historicalData['numbers'])) {
            // Si les tirages ont une date, filtrer par date
            if (!empty($historicalData['dates'])) {
                foreach ($historicalData['numbers'] as $index => $tirage) {
                    $tirageDate = isset($historicalData['dates'][$index]) ? 
                                 $historicalData['dates'][$index] : null;
                    
                    // Conserver seulement les tirages d'aujourd'hui
                    if ($tirageDate === $today) {
                        $dailyTirages[] = $tirage;
                    }
                }
            } else {
                // Si pas de dates disponibles, utiliser tous les tirages disponibles
                $dailyTirages = $historicalData['numbers'];
            }
        }
        
        return $dailyTirages;
    }
    
    /**
     * Génère toutes les stratégies journalières
     */
    private function generateAllStrategies() {
        // Vérifier qu'on a assez de données
        if (empty($this->dailyTirages) || count($this->dailyTirages) < 5) {
            // Stratégie par défaut si pas assez de tirages du jour
            $this->strategies[] = [
                'name' => 'Données insuffisantes',
                'description' => 'Pas assez de tirages aujourd\'hui pour générer des stratégies journalières',
                'numbers' => range(1, 7), // Valeurs par défaut
                'rating' => 5.0,
                'class' => 'secondary',
                'method' => 'Valeurs par défaut',
                'bestPlayCount' => self::PLAYER_PICK,
                'optimalBet' => '2€'
            ];
            return;
        }
        
        // Analyser les données
        list($blueFrequency, $yellowFrequency, $allFrequency, $lastOccurrence) = $this->analyzeData();
        $timeBasedPatterns = $this->findTimeBasedPatterns();
        $correlations = $this->calculateCorrelations();
        $sequences = $this->analyzeSequences();
        $positionPatterns = $this->analyzePositionPatterns();
        
        // Générer les stratégies basées sur les analyses
        $this->strategies[] = $this->generateGapStrategy($lastOccurrence);
        $this->strategies[] = $this->generateTimeTransitionStrategy($timeBasedPatterns);
        $this->strategies[] = $this->generateIntradayTrendStrategy($blueFrequency, $yellowFrequency);
        $this->strategies[] = $this->generateStablePositionStrategy($blueFrequency, $yellowFrequency);
        $this->strategies[] = $this->generateDailyClusterStrategy($correlations);
        
        // Nouvelles stratégies innovantes
        $this->strategies[] = $this->generateSequentialPatternStrategy($sequences);
        $this->strategies[] = $this->generatePositionTransitionStrategy($positionPatterns);
        $this->strategies[] = $this->generateBlueYellowBalanceStrategy($blueFrequency, $yellowFrequency, $allFrequency);
        
        // Ajouter les stratégies des numéros les plus et moins sortis
        $this->strategies[] = $this->generateMostFrequentStrategy($allFrequency);
        $this->strategies[] = $this->generateLeastFrequentStrategy($allFrequency);
        
        // Trier les stratégies par note (rating) décroissante
        usort($this->strategies, function($a, $b) {
            return $b['rating'] <=> $a['rating'];
        });
    }
    
    /**
     * Analyse les données des tirages du jour pour extraire des statistiques utiles
     * 
     * @return array Statistiques sur les tirages du jour
     */
    private function analyzeData() {
        // Initialiser les compteurs
        $blueFrequency = array_fill(1, self::MAX_NUM, 0);
        $yellowFrequency = array_fill(1, self::MAX_NUM, 0);
        $allFrequency = array_fill(1, self::MAX_NUM, 0);
        $lastOccurrence = array_fill(1, self::MAX_NUM, -1);
        
        // Parcourir les tirages du jour
        foreach ($this->dailyTirages as $index => $tirage) {
            // Compter les bleus
            if (isset($tirage['blue']) && is_array($tirage['blue'])) {
                foreach ($tirage['blue'] as $num) {
                    if ($num >= 1 && $num <= self::MAX_NUM) {
                        $blueFrequency[$num]++;
                        $allFrequency[$num]++;
                        $lastOccurrence[$num] = $index;
                    }
                }
            }
            
            // Compter les jaunes
            if (isset($tirage['yellow']) && is_array($tirage['yellow'])) {
                foreach ($tirage['yellow'] as $num) {
                    if ($num >= 1 && $num <= self::MAX_NUM) {
                        $yellowFrequency[$num]++;
                        $allFrequency[$num]++;
                        $lastOccurrence[$num] = $index;
                    }
                }
            }
        }
        
        return [$blueFrequency, $yellowFrequency, $allFrequency, $lastOccurrence];
    }
    
    /**
     * Recherche des patterns basés sur l'heure des tirages
     * 
     * @return array Patterns basés sur l'heure
     */
    private function findTimeBasedPatterns() {
        // Structure pour stocker les patterns par créneau horaire
        $hourlyPatterns = [];
        
        // Diviser la journée en 4 créneaux (matin, après-midi, soirée, nuit)
        $timeSlots = [
            'morning' => [6, 12],   // 6h-12h
            'afternoon' => [12, 18], // 12h-18h
            'evening' => [18, 23],   // 18h-23h
            'night' => [0, 6]        // 0h-6h
        ];
        
        // Initialiser les compteurs pour chaque créneau
        foreach ($timeSlots as $slot => $hours) {
            $hourlyPatterns[$slot] = array_fill(1, self::MAX_NUM, 0);
        }
        
        // Déterminer le créneau horaire actuel
        $currentHour = (int)date('G');
        $currentSlot = '';
        foreach ($timeSlots as $slot => $hours) {
            if ($currentHour >= $hours[0] && $currentHour < $hours[1]) {
                $currentSlot = $slot;
                break;
            }
        }
        if ($currentHour >= 23 || $currentHour < 0) {
            $currentSlot = 'night';
        }
        
        // Compter les fréquences par créneau horaire
        // Note: Dans cette version simplifiée, nous supposons que tous les tirages sont d'aujourd'hui
        // et utilisons l'index comme approximation de l'heure
        $tiragesToday = count($this->dailyTirages);
        
        foreach ($this->dailyTirages as $index => $tirage) {
            // Simuler l'heure du tirage basée sur sa position dans la liste
            $estimatedHour = (int)(($index / $tiragesToday) * 24);
            
            // Déterminer le créneau
            $slotForTirage = '';
            foreach ($timeSlots as $slot => $hours) {
                if ($estimatedHour >= $hours[0] && $estimatedHour < $hours[1]) {
                    $slotForTirage = $slot;
                    break;
                }
            }
            if ($estimatedHour >= 23 || $estimatedHour < 0) {
                $slotForTirage = 'night';
            }
            
            // Compter les numéros pour ce créneau
            $allNums = [];
            if (isset($tirage['blue']) && is_array($tirage['blue'])) {
                $allNums = array_merge($allNums, $tirage['blue']);
            }
            if (isset($tirage['yellow']) && is_array($tirage['yellow'])) {
                $allNums = array_merge($allNums, $tirage['yellow']);
            }
            
            foreach ($allNums as $num) {
                if ($num >= 1 && $num <= self::MAX_NUM) {
                    $hourlyPatterns[$slotForTirage][$num]++;
                }
            }
        }
        
        return [
            'patterns' => $hourlyPatterns,
            'currentSlot' => $currentSlot
        ];
    }
    
    /**
     * Calcule les corrélations entre les numéros dans les tirages du jour
     * 
     * @return array Matrice de corrélation
     */
    private function calculateCorrelations() {
        // Initialiser la matrice de corrélation
        $correlations = [];
        for ($i = 1; $i <= self::MAX_NUM; $i++) {
            $correlations[$i] = array_fill(1, self::MAX_NUM, 0);
        }
        
        // Parcourir les tirages
        foreach ($this->dailyTirages as $tirage) {
            $allNums = [];
            
            // Extraire tous les numéros du tirage
            if (isset($tirage['blue']) && is_array($tirage['blue'])) {
                $allNums = array_merge($allNums, $tirage['blue']);
            }
            if (isset($tirage['yellow']) && is_array($tirage['yellow'])) {
                $allNums = array_merge($allNums, $tirage['yellow']);
            }
            
            // Calculer les corrélations (paires de numéros apparaissant ensemble)
            for ($i = 0; $i < count($allNums); $i++) {
                for ($j = $i + 1; $j < count($allNums); $j++) {
                    $num1 = $allNums[$i];
                    $num2 = $allNums[$j];
                    
                    if ($num1 >= 1 && $num1 <= self::MAX_NUM && $num2 >= 1 && $num2 <= self::MAX_NUM) {
                        $correlations[$num1][$num2]++;
                        $correlations[$num2][$num1]++;
                    }
                }
            }
        }
        
        return $correlations;
    }
    
    /**
     * 1. Stratégie des Écarts Journaliers
     * Sélectionne les numéros qui n'ont pas été tirés depuis longtemps aujourd'hui
     * 
     * @param array $lastOccurrence Dernière occurrence de chaque numéro
     * @return array Stratégie basée sur les écarts
     */
    private function generateGapStrategy($lastOccurrence) {
        $numCount = count($this->dailyTirages);
        $gapScores = [];
        
        // Calculer les scores d'écart
        for ($num = 1; $num <= self::MAX_NUM; $num++) {
            if ($lastOccurrence[$num] == -1) {
                // Numéro jamais sorti aujourd'hui, score maximum
                $gapScores[$num] = $numCount + 10;
            } else {
                // Calculer l'écart depuis la dernière occurrence
                $gapScores[$num] = $numCount - $lastOccurrence[$num];
            }
        }
        
        // Trier par écart décroissant
        arsort($gapScores);
        
        // Sélectionner les 7 numéros avec les écarts les plus longs
        $selectedNumbers = array_slice(array_keys($gapScores), 0, self::PLAYER_PICK);
        sort($selectedNumbers);
        
        return [
            'name' => 'Écarts Journaliers',
            'description' => 'Numéros qui n\'ont pas été tirés depuis longtemps aujourd\'hui',
            'numbers' => $selectedNumbers,
            'rating' => 8.5,
            'class' => 'primary',
            'method' => 'Analyse des écarts temporels intraday',
            'bestPlayCount' => self::PLAYER_PICK,
            'optimalBet' => '4€'
        ];
    }
    
    /**
     * 2. Stratégie des Transitions Horaires
     * Sélectionne les numéros selon les tendances du créneau horaire actuel
     * 
     * @param array $timePatterns Patterns basés sur l'heure
     * @return array Stratégie basée sur l'heure
     */
    private function generateTimeTransitionStrategy($timePatterns) {
        $currentSlot = $timePatterns['currentSlot'];
        $slotPatterns = $timePatterns['patterns'][$currentSlot];
        
        // Trier par fréquence dans le créneau horaire actuel
        arsort($slotPatterns);
        
        // Sélectionner les 7 numéros les plus fréquents dans ce créneau
        $selectedNumbers = array_slice(array_keys($slotPatterns), 0, self::PLAYER_PICK);
        sort($selectedNumbers);
        
        // Si le créneau n'a pas assez de données, compléter avec des numéros aléatoires
        if (count(array_filter($slotPatterns)) < self::PLAYER_PICK) {
            $rating = 6.5; // Note plus basse car moins fiable
        } else {
            $rating = 8.2;
        }
        
        return [
            'name' => 'Tendance Horaire',
            'description' => 'Numéros fréquents dans le créneau horaire actuel (' . $currentSlot . ')',
            'numbers' => $selectedNumbers,
            'rating' => $rating,
            'class' => 'info',
            'method' => 'Analyse des variations horaires',
            'bestPlayCount' => self::PLAYER_PICK,
            'optimalBet' => '4€'
        ];
    }
    
    /**
     * 3. Stratégie des Tendances Intraday
     * Sélectionne les numéros qui sont "chauds" aujourd'hui
     * 
     * @param array $blueFrequency Fréquence des numéros bleus
     * @param array $yellowFrequency Fréquence des numéros jaunes
     * @return array Stratégie basée sur les tendances du jour
     */
    private function generateIntradayTrendStrategy($blueFrequency, $yellowFrequency) {
        // Calculer un score combiné (fréquence bleue a plus de poids)
        $trendScores = [];
        
        for ($num = 1; $num <= self::MAX_NUM; $num++) {
            $trendScores[$num] = ($blueFrequency[$num] * 2) + $yellowFrequency[$num];
        }
        
        // Trier par score décroissant
        arsort($trendScores);
        
        // Sélectionner les 7 numéros les plus "chauds"
        $selectedNumbers = array_slice(array_keys($trendScores), 0, self::PLAYER_PICK);
        sort($selectedNumbers);
        
        return [
            'name' => 'Tendances du Jour',
            'description' => 'Numéros les plus fréquents dans les tirages d\'aujourd\'hui',
            'numbers' => $selectedNumbers,
            'rating' => 8.7,
            'class' => 'danger',
            'method' => 'Analyse des numéros "chauds" du jour',
            'bestPlayCount' => self::PLAYER_PICK,
            'optimalBet' => '6€'
        ];
    }
    
    /**
     * 4. Stratégie des Positions Stabilisées
     * Sélectionne les numéros selon leur stabilité en position bleue ou jaune
     * 
     * @param array $blueFrequency Fréquence des numéros bleus
     * @param array $yellowFrequency Fréquence des numéros jaunes
     * @return array Stratégie basée sur la stabilité des positions
     */
    private function generateStablePositionStrategy($blueFrequency, $yellowFrequency) {
        // Calculer le ratio bleu/jaune pour chaque numéro
        $stabilityScores = [];
        
        for ($num = 1; $num <= self::MAX_NUM; $num++) {
            $totalOccurrences = $blueFrequency[$num] + $yellowFrequency[$num];
            
            if ($totalOccurrences > 0) {
                // Calculer la "stabilité" - préférer les numéros avec position claire
                $blueRatio = $blueFrequency[$num] / $totalOccurrences;
                
                // Score de stabilité: proche de 0 ou 1 = stable (toujours bleu ou toujours jaune)
                $stabilityScores[$num] = max($blueRatio, 1 - $blueRatio) * $totalOccurrences;
            } else {
                $stabilityScores[$num] = 0;
            }
        }
        
        // Trier par score de stabilité décroissant
        arsort($stabilityScores);
        
        // Sélectionner les 7 numéros avec les positions les plus stables
        $selectedNumbers = array_slice(array_keys($stabilityScores), 0, self::PLAYER_PICK);
        sort($selectedNumbers);
        
        return [
            'name' => 'Positions Stables',
            'description' => 'Numéros avec des positions bleue/jaune stables aujourd\'hui',
            'numbers' => $selectedNumbers,
            'rating' => 7.9,
            'class' => 'warning',
            'method' => 'Analyse de la stabilité des positions',
            'bestPlayCount' => self::PLAYER_PICK,
            'optimalBet' => '4€'
        ];
    }
    
    /**
     * 5. Stratégie des Groupes Journaliers
     * Sélectionne les numéros qui tendent à apparaître ensemble aujourd'hui
     * 
     * @param array $correlations Matrice de corrélation
     * @return array Stratégie basée sur les clusters de la journée
     */
    private function generateDailyClusterStrategy($correlations) {
        // Calculer un score de "force de groupe" pour chaque numéro
        $clusterScores = [];
        
        for ($num = 1; $num <= self::MAX_NUM; $num++) {
            $clusterScores[$num] = array_sum($correlations[$num]);
        }
        
        // Trier par score décroissant
        arsort($clusterScores);
        
        // Sélectionner les 7 numéros les plus corrélés
        $selectedNumbers = array_slice(array_keys($clusterScores), 0, self::PLAYER_PICK);
        sort($selectedNumbers);
        
        return [
            'name' => 'Groupes du Jour',
            'description' => 'Numéros qui tendent à apparaître ensemble aujourd\'hui',
            'numbers' => $selectedNumbers,
            'rating' => 8.0,
            'class' => 'success',
            'method' => 'Analyse des corrélations intraday',
            'bestPlayCount' => self::PLAYER_PICK,
            'optimalBet' => '4€'
        ];
    }
    
    /**
     * Analyse les séquences ordonnées dans les tirages du jour
     * 
     * @return array Séquences et modèles détectés
     */
    private function analyzeSequences() {
        $sequenceData = [
            'transitions' => array_fill(1, self::MAX_NUM, array_fill(1, self::MAX_NUM, 0)),
            'startFrequency' => array_fill(1, self::MAX_NUM, 0),
            'endFrequency' => array_fill(1, self::MAX_NUM, 0),
            'patterns' => []
        ];
        
        // Analyser chaque tirage pour détecter les modèles séquentiels
        foreach ($this->dailyTirages as $tirage) {
            $allNums = [];
            
            // Extraire tous les numéros du tirage en préservant l'ordre
            if (isset($tirage['blue']) && is_array($tirage['blue'])) {
                $allNums = array_merge($allNums, $tirage['blue']);
            }
            if (isset($tirage['yellow']) && is_array($tirage['yellow'])) {
                $allNums = array_merge($allNums, $tirage['yellow']);
            }
            
            // Analyser les transitions entre numéros successifs
            for ($i = 0; $i < count($allNums) - 1; $i++) {
                $current = $allNums[$i];
                $next = $allNums[$i + 1];
                
                if ($current >= 1 && $current <= self::MAX_NUM && $next >= 1 && $next <= self::MAX_NUM) {
                    $sequenceData['transitions'][$current][$next]++;
                }
            }
            
            // Enregistrer les fréquences de début et de fin
            if (!empty($allNums)) {
                $first = $allNums[0];
                $last = $allNums[count($allNums) - 1];
                
                if ($first >= 1 && $first <= self::MAX_NUM) {
                    $sequenceData['startFrequency'][$first]++;
                }
                
                if ($last >= 1 && $last <= self::MAX_NUM) {
                    $sequenceData['endFrequency'][$last]++;
                }
            }
            
            // Détecter les modèles (comme séquences croissantes/décroissantes ou alternances pair/impair)
            $pattern = $this->detectSequencePattern($allNums);
            if (!empty($pattern)) {
                $sequenceData['patterns'][] = $pattern;
            }
        }
        
        return $sequenceData;
    }
    
    /**
     * Détecte les modèles dans une séquence de numéros
     * 
     * @param array $sequence Séquence de numéros
     * @return array Modèles détectés
     */
    private function detectSequencePattern($sequence) {
        $patterns = [];
        
        // Vérifier si la séquence est suffisamment longue
        if (count($sequence) < 3) {
            return $patterns;
        }
        
        // Vérifier les croissances/décroissances
        $increasing = true;
        $decreasing = true;
        $alternating = true;
        
        for ($i = 1; $i < count($sequence); $i++) {
            if ($sequence[$i] <= $sequence[$i-1]) {
                $increasing = false;
            }
            if ($sequence[$i] >= $sequence[$i-1]) {
                $decreasing = false;
            }
            if ($i > 1) {
                // Vérifier alternance haut/bas
                $prev_diff = $sequence[$i-1] - $sequence[$i-2];
                $curr_diff = $sequence[$i] - $sequence[$i-1];
                
                if (($prev_diff > 0 && $curr_diff > 0) || ($prev_diff < 0 && $curr_diff < 0)) {
                    $alternating = false;
                }
            }
        }
        
        if ($increasing) {
            $patterns['type'] = 'increasing';
        } elseif ($decreasing) {
            $patterns['type'] = 'decreasing';
        } elseif ($alternating) {
            $patterns['type'] = 'alternating';
        }
        
        // Vérifier la parité (pairs/impairs)
        $evens = 0;
        $odds = 0;
        
        foreach ($sequence as $num) {
            if ($num % 2 == 0) {
                $evens++;
            } else {
                $odds++;
            }
        }
        
        $patterns['parity'] = [
            'evens' => $evens,
            'odds' => $odds,
            'ratio' => $evens / max(1, count($sequence))
        ];
        
        return $patterns;
    }
    
    /**
     * Analyse les patterns liés aux positions (bleu/jaune) dans les tirages du jour
     * 
     * @return array Données des patterns de position
     */
    private function analyzePositionPatterns() {
        $positionData = [
            'blueToYellow' => array_fill(1, self::MAX_NUM, 0),  // Numéros qui changent de bleu à jaune
            'yellowToBlue' => array_fill(1, self::MAX_NUM, 0),  // Numéros qui changent de jaune à bleu
            'positionChanges' => array_fill(1, self::MAX_NUM, 0), // Nombre total de changements de position
            'stableBlue' => array_fill(1, self::MAX_NUM, 0),    // Numéros stables en position bleue
            'stableYellow' => array_fill(1, self::MAX_NUM, 0),  // Numéros stables en position jaune
        ];
        
        $previousPositions = [];  // Dernière position connue pour chaque numéro (blue/yellow)
        
        // Analyser chaque tirage pour suivre les changements de position
        foreach ($this->dailyTirages as $tirage) {
            $currentPositions = [];
            
            // Enregistrer les positions bleues
            if (isset($tirage['blue']) && is_array($tirage['blue'])) {
                foreach ($tirage['blue'] as $num) {
                    if ($num >= 1 && $num <= self::MAX_NUM) {
                        $currentPositions[$num] = 'blue';
                        
                        // Analyser les changements par rapport au tirage précédent
                        if (isset($previousPositions[$num])) {
                            if ($previousPositions[$num] == 'yellow') {
                                $positionData['yellowToBlue'][$num]++;
                                $positionData['positionChanges'][$num]++;
                            } else {
                                $positionData['stableBlue'][$num]++;
                            }
                        }
                    }
                }
            }
            
            // Enregistrer les positions jaunes
            if (isset($tirage['yellow']) && is_array($tirage['yellow'])) {
                foreach ($tirage['yellow'] as $num) {
                    if ($num >= 1 && $num <= self::MAX_NUM) {
                        $currentPositions[$num] = 'yellow';
                        
                        // Analyser les changements par rapport au tirage précédent
                        if (isset($previousPositions[$num])) {
                            if ($previousPositions[$num] == 'blue') {
                                $positionData['blueToYellow'][$num]++;
                                $positionData['positionChanges'][$num]++;
                            } else {
                                $positionData['stableYellow'][$num]++;
                            }
                        }
                    }
                }
            }
            
            // Mettre à jour les positions précédentes pour le prochain tirage
            $previousPositions = $currentPositions;
        }
        
        return $positionData;
    }
    
    /**
     * 6. Stratégie des Modèles Séquentiels
     * Analyse les séquences et transitions entre numéros dans l'ordre d'apparition
     * 
     * @param array $sequences Données de séquence analysées
     * @return array Stratégie basée sur les séquences
     */
    private function generateSequentialPatternStrategy($sequences) {
        // Analyser les transitions entre numéros pour trouver des chaînes probables
        $transitionScores = [];
        
        // Calculer un score basé sur les transitions et les positions de début/fin
        for ($num = 1; $num <= self::MAX_NUM; $num++) {
            // Score basé sur la fréquence comme numéro de départ
            $startScore = isset($sequences['startFrequency'][$num]) ? $sequences['startFrequency'][$num] * 3 : 0;
            
            // Score basé sur la force des transitions depuis ce numéro
            $transitionScore = 0;
            for ($next = 1; $next <= self::MAX_NUM; $next++) {
                $transitionScore += isset($sequences['transitions'][$num][$next]) ? 
                                  $sequences['transitions'][$num][$next] : 0;
            }
            
            // Score basé sur les transitions vers ce numéro
            $incomingScore = 0;
            for ($prev = 1; $prev <= self::MAX_NUM; $prev++) {
                $incomingScore += isset($sequences['transitions'][$prev][$num]) ? 
                                 $sequences['transitions'][$prev][$num] : 0;
            }
            
            // Score composite
            $transitionScores[$num] = $startScore + $transitionScore + $incomingScore;
        }
        
        // Trier par score décroissant
        arsort($transitionScores);
        
        // Sélectionner les 7 numéros avec les meilleurs scores de transition
        $selectedNumbers = array_slice(array_keys($transitionScores), 0, self::PLAYER_PICK);
        sort($selectedNumbers);
        
        return [
            'name' => 'Chaînes Séquentielles',
            'description' => 'Numéros formant des séquences fortes dans l\'ordre d\'apparition du tirage',
            'numbers' => $selectedNumbers,
            'rating' => 8.8,
            'class' => 'secondary',
            'method' => 'Analyse des transitions et séquences ordonnées',
            'bestPlayCount' => self::PLAYER_PICK,
            'optimalBet' => '6€'
        ];
    }
    
    /**
     * 7. Stratégie des Transitions de Position
     * Exploite les numéros qui changent régulièrement de position (bleu/jaune)
     * 
     * @param array $positionPatterns Données d'analyse des positions
     * @return array Stratégie basée sur les transitions de position
     */
    private function generatePositionTransitionStrategy($positionPatterns) {
        // Préférer les numéros qui changent fréquemment de position
        $transitionScores = [];
        
        for ($num = 1; $num <= self::MAX_NUM; $num++) {
            // Calculer un score basé sur la fréquence des changements
            $changeScore = isset($positionPatterns['positionChanges'][$num]) ? 
                         $positionPatterns['positionChanges'][$num] * 2 : 0;
                         
            // Ajouter un bonus pour les transitions bleu->jaune (généralement plus rares)
            $blueToYellowBonus = isset($positionPatterns['blueToYellow'][$num]) ? 
                              $positionPatterns['blueToYellow'][$num] * 1.5 : 0;
                              
            // Score composite
            $transitionScores[$num] = $changeScore + $blueToYellowBonus;
        }
        
        // Trier par score décroissant
        arsort($transitionScores);
        
        // Sélectionner les 7 numéros avec les meilleurs scores de transition
        $selectedNumbers = array_slice(array_keys($transitionScores), 0, self::PLAYER_PICK);
        sort($selectedNumbers);
        
        return [
            'name' => 'Numéros Versatiles',
            'description' => 'Numéros qui alternent fréquemment entre positions bleue et jaune',
            'numbers' => $selectedNumbers,
            'rating' => 8.6,
            'class' => 'dark',
            'method' => 'Analyse des transitions de position bleu/jaune',
            'bestPlayCount' => self::PLAYER_PICK,
            'optimalBet' => '6€'
        ];
    }
    
    /**
     * 8. Stratégie d'Équilibre Bleu-Jaune
     * Vise une répartition optimale entre positions bleues et jaunes
     * 
     * @param array $blueFrequency Fréquence des numéros bleus
     * @param array $yellowFrequency Fréquence des numéros jaunes
     * @param array $allFrequency Fréquence totale des numéros
     * @return array Stratégie basée sur l'équilibre des positions
     */
    private function generateBlueYellowBalanceStrategy($blueFrequency, $yellowFrequency, $allFrequency) {
        // Le but est de sélectionner les numéros pour obtenir une combinaison
        // avec un rapport optimal entre bleus et jaunes (4B-3J) pour maximiser les chances de gain
        
        // Normaliser les fréquences pour obtenir des probabilités
        $blueProb = [];
        $yellowProb = [];
        $totalBlue = array_sum($blueFrequency);
        $totalYellow = array_sum($yellowFrequency);
        
        for ($num = 1; $num <= self::MAX_NUM; $num++) {
            $blueProb[$num] = $totalBlue > 0 ? $blueFrequency[$num] / $totalBlue : 0;
            $yellowProb[$num] = $totalYellow > 0 ? $yellowFrequency[$num] / $totalYellow : 0;
        }
        
        // Calculer un score d'équilibre optimal pour chaque numéro
        $balanceScores = [];
        
        for ($num = 1; $num <= self::MAX_NUM; $num++) {
            // La stratégie vise à sélectionner les 4 meilleurs numéros bleus et les 3 meilleurs jaunes
            // Pondérer le score en fonction de la probabilité d'apparition en position bleue/jaune
            $bluePotential = $blueProb[$num] * 4; // Pondération pour les 4 positions bleues
            $yellowPotential = $yellowProb[$num] * 3; // Pondération pour les 3 positions jaunes
            
            // Score final d'équilibre, favorisant la position la plus probable
            $balanceScores[$num] = max($bluePotential, $yellowPotential) * ($allFrequency[$num] + 1);
        }
        
        // Trier par score décroissant
        arsort($balanceScores);
        
        // Sélectionner les 7 numéros avec les meilleurs scores d'équilibre
        $selectedNumbers = array_slice(array_keys($balanceScores), 0, self::PLAYER_PICK);
        sort($selectedNumbers);
        
        return [
            'name' => 'Équilibre Journalier 4B-3J',
            'description' => 'Numéros optimisés pour une combinaison gagnante 4 bleus, 3 jaunes selon les tendances du jour',
            'numbers' => $selectedNumbers,
            'rating' => 9.0,
            'class' => 'success',
            'method' => 'Optimisation de la distribution bleu/jaune pour le jour',
            'bestPlayCount' => self::PLAYER_PICK,
            'optimalBet' => '8€'
        ];
    }
    
    /**
     * 9. Stratégie des Numéros les Plus Fréquents du Jour
     * Sélectionne les numéros qui sont apparus le plus souvent aujourd'hui
     * 
     * @param array $allFrequency Fréquence totale des numéros
     * @return array Stratégie basée sur les numéros les plus fréquents
     */
    private function generateMostFrequentStrategy($allFrequency) {
        // Trier par fréquence décroissante
        $frequencies = $allFrequency;
        arsort($frequencies);
        
        // Sélectionner les 7 numéros les plus fréquents
        $selectedNumbers = array_slice(array_keys($frequencies), 0, self::PLAYER_PICK);
        sort($selectedNumbers);
        
        return [
            'name' => 'Top Fréquents du Jour',
            'description' => 'Les 7 numéros qui sont sortis le plus souvent aujourd\'hui',
            'numbers' => $selectedNumbers,
            'rating' => 8.9,
            'class' => 'info',
            'method' => 'Analyse des fréquences journalières',
            'bestPlayCount' => self::PLAYER_PICK,
            'optimalBet' => '6€'
        ];
    }
    
    /**
     * 10. Stratégie des Numéros les Moins Fréquents du Jour
     * Sélectionne les numéros qui sont apparus le moins souvent aujourd'hui
     * 
     * @param array $allFrequency Fréquence totale des numéros
     * @return array Stratégie basée sur les numéros les moins fréquents
     */
    private function generateLeastFrequentStrategy($allFrequency) {
        // Initialiser un tableau avec tous les numéros possibles
        $allNumbers = range(1, self::MAX_NUM);
        $frequencies = [];
        
        // Préparer le tableau de fréquences (en inversant les valeurs pour trier correctement)
        foreach ($allNumbers as $num) {
            $frequencies[$num] = isset($allFrequency[$num]) ? -$allFrequency[$num] : 0;
        }
        
        // Trier par fréquence croissante (valeur négative décroissante)
        arsort($frequencies);
        
        // Sélectionner les 7 numéros les moins fréquents
        $selectedNumbers = array_slice(array_keys($frequencies), 0, self::PLAYER_PICK);
        sort($selectedNumbers);
        
        return [
            'name' => 'Numéros Rares du Jour',
            'description' => 'Les 7 numéros qui sont sortis le moins souvent aujourd\'hui',
            'numbers' => $selectedNumbers,
            'rating' => 7.8,
            'class' => 'danger',
            'method' => 'Analyse des raretés journalières',
            'bestPlayCount' => self::PLAYER_PICK,
            'optimalBet' => '4€'
        ];
    }
}