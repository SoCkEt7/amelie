<?php

/**
 * Classe ClusterEVStrategy - Stratégie IA A5
 * 
 * Détecte des communautés (clusters) de numéros qui apparaissent souvent ensemble
 * et maximise l'espérance de gain (EV) des clusters
 * 
 * @package Amelie\Strategies
 */
class ClusterEVStrategy
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
    
    // Matrice de co-occurrence
    private $coOccurrenceMatrix = [];
    
    // Clusters détectés
    private $clusters = [];
    
    // Scores EV des clusters
    private $clusterEVs = [];
    
    /**
     * Constructeur
     */
    public function __construct()
    {
        // Initialiser la matrice de co-occurrence
        for ($i = 1; $i <= self::MAX_NUM; $i++) {
            $this->coOccurrenceMatrix[$i] = array_fill(1, self::MAX_NUM, 0);
        }
    }
    
    /**
     * Génère une recommandation selon cette stratégie
     * 
     * @return array Informations sur la stratégie
     */
    public function generate()
    {
        // Construire la matrice de co-occurrence
        $this->buildCoOccurrenceMatrix();
        
        // Détecter les clusters
        $this->detectClusters();
        
        // Évaluer les clusters
        $this->evaluateClusters();
        
        // Sélectionner la meilleure combinaison
        $bestCombination = $this->selectBestCombination();
        
        // Calculer l'espérance de gain et le ROI
        $ev = $this->calculateEV($bestCombination);
        $roi = $ev / 8.0; // Pour mise 8€
        
        return [
            'strategyId' => 'cluster_ev',
            'label' => 'Cluster EV',
            'numbers' => $bestCombination,
            'ev' => $ev,
            'roi' => $roi
        ];
    }
    
    /**
     * Construit la matrice de co-occurrence entre les numéros
     */
    private function buildCoOccurrenceMatrix()
    {
        $tirages = TirageDataset::getAllTirages();
        
        foreach ($tirages as $tirage) {
            $allNumbers = [];
            
            // Extraire tous les numéros du tirage
            if (isset($tirage['blue']) && is_array($tirage['blue'])) {
                $allNumbers = array_merge($allNumbers, $tirage['blue']);
            }
            if (isset($tirage['yellow']) && is_array($tirage['yellow'])) {
                $allNumbers = array_merge($allNumbers, $tirage['yellow']);
            }
            
            // Mettre à jour la matrice de co-occurrence
            for ($i = 0; $i < count($allNumbers); $i++) {
                for ($j = $i + 1; $j < count($allNumbers); $j++) {
                    $num1 = $allNumbers[$i];
                    $num2 = $allNumbers[$j];
                    
                    if ($num1 >= 1 && $num1 <= self::MAX_NUM && $num2 >= 1 && $num2 <= self::MAX_NUM) {
                        $this->coOccurrenceMatrix[$num1][$num2]++;
                        $this->coOccurrenceMatrix[$num2][$num1]++;
                    }
                }
            }
        }
        
        // Normaliser la matrice (facultatif)
        $this->normalizeMatrix();
    }
    
    /**
     * Normalise la matrice de co-occurrence
     */
    private function normalizeMatrix()
    {
        $max = 0;
        
        // Trouver la valeur maximale
        for ($i = 1; $i <= self::MAX_NUM; $i++) {
            for ($j = 1; $j <= self::MAX_NUM; $j++) {
                if ($this->coOccurrenceMatrix[$i][$j] > $max) {
                    $max = $this->coOccurrenceMatrix[$i][$j];
                }
            }
        }
        
        // Normaliser si max > 0
        if ($max > 0) {
            for ($i = 1; $i <= self::MAX_NUM; $i++) {
                for ($j = 1; $j <= self::MAX_NUM; $j++) {
                    $this->coOccurrenceMatrix[$i][$j] /= $max;
                }
            }
        }
    }
    
    /**
     * Détecte les clusters de numéros à l'aide d'un algorithme simplifié
     * inspiré de l'algorithme de Louvain
     */
    private function detectClusters()
    {
        // Initialiser: chaque numéro est dans son propre cluster
        $communities = [];
        for ($i = 1; $i <= self::MAX_NUM; $i++) {
            $communities[$i] = [$i];
        }
        
        // Calculer les degrés des nœuds
        $degrees = [];
        $totalEdgeWeight = 0;
        
        for ($i = 1; $i <= self::MAX_NUM; $i++) {
            $degrees[$i] = 0;
            for ($j = 1; $j <= self::MAX_NUM; $j++) {
                $degrees[$i] += $this->coOccurrenceMatrix[$i][$j];
                $totalEdgeWeight += $this->coOccurrenceMatrix[$i][$j];
            }
        }
        
        // Normaliser pour éviter une division par zéro
        $totalEdgeWeight = max(0.0001, $totalEdgeWeight / 2); // Diviser par 2 car on compte chaque arête deux fois
        
        // Effectuer quelques passes d'agglomération
        for ($pass = 0; $pass < 3; $pass++) {
            // Phase 1: Optimisation locale - Déplacer les nœuds entre les communautés
            $improved = false;
            
            for ($node = 1; $node <= self::MAX_NUM; $node++) {
                // Trouver la communauté actuelle du nœud
                $currentCommunity = -1;
                foreach ($communities as $communityId => $nodes) {
                    if (in_array($node, $nodes)) {
                        $currentCommunity = $communityId;
                        break;
                    }
                }
                
                if ($currentCommunity === -1) {
                    continue; // Nœud non trouvé dans une communauté
                }
                
                // Calculer le gain en modularité pour chaque communauté
                $bestGain = 0;
                $bestCommunity = $currentCommunity;
                
                foreach ($communities as $communityId => $nodes) {
                    if ($communityId === $currentCommunity) {
                        continue; // Ignorer la communauté actuelle
                    }
                    
                    // Calculer le poids total des arêtes entre le nœud et la communauté
                    $weightToCommunity = 0;
                    foreach ($nodes as $nodeInCommunity) {
                        $weightToCommunity += $this->coOccurrenceMatrix[$node][$nodeInCommunity];
                    }
                    
                    // Calculer le gain en modularité (version simplifiée)
                    $gain = $weightToCommunity - ($degrees[$node] * array_sum(array_intersect_key($degrees, array_flip($nodes))) / (2 * $totalEdgeWeight));
                    
                    if ($gain > $bestGain) {
                        $bestGain = $gain;
                        $bestCommunity = $communityId;
                    }
                }
                
                // Si une meilleure communauté est trouvée, déplacer le nœud
                if ($bestGain > 0 && $bestCommunity !== $currentCommunity) {
                    // Retirer le nœud de sa communauté actuelle
                    $communities[$currentCommunity] = array_diff($communities[$currentCommunity], [$node]);
                    
                    // Si la communauté est vide, la supprimer
                    if (empty($communities[$currentCommunity])) {
                        unset($communities[$currentCommunity]);
                    }
                    
                    // Ajouter le nœud à sa nouvelle communauté
                    $communities[$bestCommunity][] = $node;
                    
                    $improved = true;
                }
            }
            
            // Si aucune amélioration n'a été faite, arrêter les passes
            if (!$improved) {
                break;
            }
        }
        
        // Stocker les clusters
        $this->clusters = array_values($communities);
    }
    
    /**
     * Évalue les clusters en termes d'espérance de gain (EV)
     */
    private function evaluateClusters()
    {
        $blueFreq = $this->calculatePositionFrequencies('blue');
        $yellowFreq = $this->calculatePositionFrequencies('yellow');
        
        foreach ($this->clusters as $index => $cluster) {
            // Ne considérer que les clusters de taille raisonnable
            if (count($cluster) < 4 || count($cluster) > 9) {
                $this->clusterEVs[$index] = 0;
                continue;
            }
            
            // Calculer la probabilité qu'au moins 4 numéros du cluster sortent ensemble
            $clusterStrength = $this->calculateClusterStrength($cluster);
            
            // Trier les numéros du cluster en bleus et jaunes selon leur fréquence
            $blueNumbers = [];
            $yellowNumbers = [];
            
            foreach ($cluster as $num) {
                if ($blueFreq[$num] > $yellowFreq[$num]) {
                    $blueNumbers[] = $num;
                } else {
                    $yellowNumbers[] = $num;
                }
            }
            
            // Estimer la distribution bleue/jaune la plus probable
            $blueCount = min(count($blueNumbers), self::BLUE_COUNT);
            $yellowCount = min(count($yellowNumbers), self::YELLOW_COUNT);
            
            // Compléter si nécessaire
            if ($blueCount + $yellowCount < self::PLAYER_PICK) {
                if (count($blueNumbers) > $blueCount) {
                    $blueCount = min(count($blueNumbers), self::PLAYER_PICK - $yellowCount);
                } elseif (count($yellowNumbers) > $yellowCount) {
                    $yellowCount = min(count($yellowNumbers), self::PLAYER_PICK - $blueCount);
                }
            }
            
            // Calculer le gain moyen associé à cette distribution
            $avgGain = $this->calculateAverageGain($blueCount, $yellowCount);
            
            // EV = probabilité * gain moyen
            $this->clusterEVs[$index] = $clusterStrength * $avgGain;
        }
    }
    
    /**
     * Calcule la fréquence d'apparition en position bleue ou jaune
     * 
     * @param string $position 'blue' ou 'yellow'
     * @return array Fréquences pour chaque numéro
     */
    private function calculatePositionFrequencies($position)
    {
        $frequencies = array_fill(1, self::MAX_NUM, 0);
        $tirages = TirageDataset::getAllTirages();
        
        foreach ($tirages as $tirage) {
            if (isset($tirage[$position]) && is_array($tirage[$position])) {
                foreach ($tirage[$position] as $num) {
                    if ($num >= 1 && $num <= self::MAX_NUM) {
                        $frequencies[$num]++;
                    }
                }
            }
        }
        
        return $frequencies;
    }
    
    /**
     * Calcule la force d'un cluster (probabilité qu'au moins 4 numéros sortent ensemble)
     * 
     * @param array $cluster Cluster de numéros
     * @return float Force du cluster
     */
    private function calculateClusterStrength($cluster)
    {
        $strength = 0;
        
        // Calculer la somme des co-occurrences entre toutes les paires de numéros du cluster
        $totalCoOccurrences = 0;
        $pairCount = 0;
        
        for ($i = 0; $i < count($cluster); $i++) {
            for ($j = $i + 1; $j < count($cluster); $j++) {
                $key_i = $cluster[$i];
                $key_j = $cluster[$j];
                if (
                    isset($this->coOccurrenceMatrix[$key_i]) &&
                    isset($this->coOccurrenceMatrix[$key_i][$key_j]) &&
                    $key_i !== '' && $key_j !== '' &&
                    $key_i !== null && $key_j !== null
                ) {
                    $totalCoOccurrences += $this->coOccurrenceMatrix[$key_i][$key_j];
                } else {
                    // Sécurise contre les warnings
                    // $this->coOccurrenceMatrix[$key_i][$key_j] absent
                    // On ignore simplement cette paire
                    continue;
                }
                $pairCount++;
            }
        }
        
        // Calculer la force moyenne des liens (co-occurrences)
        $avgStrength = $pairCount > 0 ? $totalCoOccurrences / $pairCount : 0;
        
        // Ajuster pour favoriser les clusters de taille appropriée
        $sizeAdjustment = 1.0;
        $optimalSize = self::PLAYER_PICK + 1; // Taille optimale légèrement supérieure à 7
        $size = count($cluster);
        
        if ($size < self::PLAYER_PICK) {
            $sizeAdjustment = $size / self::PLAYER_PICK;
        } elseif ($size > $optimalSize) {
            $sizeAdjustment = max(0.5, $optimalSize / $size);
        }
        
        $strength = $avgStrength * $sizeAdjustment;
        
        return $strength;
    }
    
    /**
     * Calcule le gain moyen pour une distribution donnée de numéros bleus/jaunes
     * 
     * @param int $blueCount Nombre de numéros bleus
     * @param int $yellowCount Nombre de numéros jaunes
     * @return float Gain moyen
     */
    private function calculateAverageGain($blueCount, $yellowCount)
    {
        $totalGain = 0;
        $matchCount = 0;
        
        foreach (self::GAIN_TABLE as $row) {
            // Format: [numéros_trouvés, bleus, jaunes, chance_sur, gain_pour_8€]
            $totalMatched = $row[0];
            $blueMatched = $row[1];
            $yellowMatched = $row[2];
            $gain = $row[4];
            
            // Si la distribution correspond, ajouter au gain moyen
            if ($totalMatched === self::PLAYER_PICK && 
                $blueMatched <= $blueCount && 
                $yellowMatched <= $yellowCount) {
                $totalGain += $gain;
                $matchCount++;
            }
        }
        
        return $matchCount > 0 ? $totalGain / $matchCount : 0;
    }
    
    /**
     * Sélectionne la meilleure combinaison basée sur les clusters
     * 
     * @return array Meilleure combinaison de 7 numéros
     */
    private function selectBestCombination()
    {
        // Trouver le cluster avec la meilleure EV
        $bestClusterIndex = -1;
        $bestEV = -1;
        
        foreach ($this->clusterEVs as $index => $ev) {
            if ($ev > $bestEV) {
                $bestEV = $ev;
                $bestClusterIndex = $index;
            }
        }
        
        // Si aucun cluster valide n'est trouvé, retourner une combinaison par défaut
        if ($bestClusterIndex === -1 || !isset($this->clusters[$bestClusterIndex])) {
            // Utiliser les numéros les plus fréquents
            $tirages = TirageDataset::getAllTirages();
            $frequencies = array_fill(1, self::MAX_NUM, 0);
            
            foreach ($tirages as $tirage) {
                $allNumbers = [];
                if (isset($tirage['blue'])) {
                    $allNumbers = array_merge($allNumbers, $tirage['blue']);
                }
                if (isset($tirage['yellow'])) {
                    $allNumbers = array_merge($allNumbers, $tirage['yellow']);
                }
                
                foreach ($allNumbers as $num) {
                    if ($num >= 1 && $num <= self::MAX_NUM) {
                        $frequencies[$num]++;
                    }
                }
            }
            
            arsort($frequencies);
            $combination = array_slice(array_keys($frequencies), 0, self::PLAYER_PICK);
            sort($combination);
            
            return $combination;
        }
        
        // Utiliser le meilleur cluster pour générer la combinaison
        $cluster = $this->clusters[$bestClusterIndex];
        
        // Séparer les numéros du cluster en bleus et jaunes selon leur fréquence
        $blueFreq = $this->calculatePositionFrequencies('blue');
        $yellowFreq = $this->calculatePositionFrequencies('yellow');
        
        $blueScores = [];
        $yellowScores = [];
        
        foreach ($cluster as $num) {
            $blueScores[$num] = $blueFreq[$num];
            $yellowScores[$num] = $yellowFreq[$num];
        }
        
        // Trier par score décroissant
        arsort($blueScores);
        arsort($yellowScores);
        
        // Sélectionner les meilleurs bleus et jaunes
        $blues = array_slice(array_keys($blueScores), 0, 4); // Top 4 bleus
        $yellows = array_slice(array_keys($yellowScores), 0, 3); // Top 3 jaunes
        
        // Si pas assez de numéros, compléter depuis l'autre catégorie
        if (count($blues) < 4) {
            $remaining = 4 - count($blues);
            $extraYellows = array_slice(array_keys($yellowScores), 3, $remaining);
            $blues = array_merge($blues, $extraYellows);
        }
        
        if (count($yellows) < 3) {
            $remaining = 3 - count($yellows);
            $extraBlues = array_slice(array_keys($blueScores), 4, $remaining);
            $yellows = array_merge($yellows, $extraBlues);
        }
        
        // Combiner et trier
        $combination = array_merge($blues, $yellows);
        
        // S'assurer qu'il n'y a pas de doublons
        $combination = array_unique($combination);
        
        // Si pas assez de numéros, compléter avec des numéros fréquents
        if (count($combination) < self::PLAYER_PICK) {
            $allFrequencies = array_fill(1, self::MAX_NUM, 0);
            $tirages = TirageDataset::getAllTirages();
            
            foreach ($tirages as $tirage) {
                $allNumbers = [];
                if (isset($tirage['blue'])) {
                    $allNumbers = array_merge($allNumbers, $tirage['blue']);
                }
                if (isset($tirage['yellow'])) {
                    $allNumbers = array_merge($allNumbers, $tirage['yellow']);
                }
                
                foreach ($allNumbers as $num) {
                    if ($num >= 1 && $num <= self::MAX_NUM) {
                        $allFrequencies[$num]++;
                    }
                }
            }
            
            // Supprimer les numéros déjà sélectionnés
            foreach ($combination as $num) {
                unset($allFrequencies[$num]);
            }
            
            arsort($allFrequencies);
            $extraNumbers = array_slice(array_keys($allFrequencies), 0, self::PLAYER_PICK - count($combination));
            $combination = array_merge($combination, $extraNumbers);
        }
        
        // Limiter à 7 numéros et trier
        $combination = array_slice($combination, 0, self::PLAYER_PICK);
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
        // Trouver le cluster qui contient le plus de numéros de la combinaison
        $bestOverlap = 0;
        $bestClusterIndex = -1;
        
        foreach ($this->clusters as $index => $cluster) {
            $overlap = count(array_intersect($combination, $cluster));
            if ($overlap > $bestOverlap) {
                $bestOverlap = $overlap;
                $bestClusterIndex = $index;
            }
        }
        
        // Si un bon cluster est trouvé, utiliser son EV
        if ($bestClusterIndex !== -1 && $bestOverlap >= 4) {
            return round($this->clusterEVs[$bestClusterIndex], 2);
        }
        
        // Sinon, calculer une EV approximative basée sur la distribution bleue/jaune
        $tirages = TirageDataset::getAllTirages();
        $blueFreq = $this->calculatePositionFrequencies('blue');
        $yellowFreq = $this->calculatePositionFrequencies('yellow');
        
        // Estimer le nombre de numéros bleus/jaunes
        $expectedBlue = 0;
        $expectedYellow = 0;
        
        foreach ($combination as $num) {
            if ($blueFreq[$num] > $yellowFreq[$num]) {
                $expectedBlue++;
            } else {
                $expectedYellow++;
            }
        }
        
        // Trouver la ligne correspondante dans le tableau des gains
        $expectedGain = 0;
        foreach (self::GAIN_TABLE as $row) {
            if ($row[0] === self::PLAYER_PICK && 
                $row[1] === $expectedBlue && 
                $row[2] === $expectedYellow) {
                $expectedGain = $row[4];
                break;
            }
        }
        
        // Calculer la probabilité approximative
        $probability = 0.0001; // Valeur arbitraire basse
        
        // EV = probabilité * gain
        return round($probability * $expectedGain, 2);
    }
}