<?php

/**
 * Classe pour récupérer les données de tirages
 */
class TirageDataFetcher {
    private $client;
    
    /**
     * Constructeur
     */
    public function __construct() {
        // Utiliser Goutte\Client pour récupérer les données
        if (class_exists('Goutte\Client')) {
            $this->client = new \Goutte\Client();
        } else {
            error_log("AVERTISSEMENT: Goutte\Client n'est pas disponible. Exécutez 'composer require fabpot/goutte' pour utiliser des données réelles.");
            $this->client = null;
        }
    }
    
    /**
     * Récupère les tirages récents (dernière journée)
     * 
     * @return array Données de tirages récents
     */
    public function getRecentTirages() {
        
        // Récupérer depuis le site officiel
        $numSortis = [];
        $numSortisB = [];
        
        try {
            // Ajouter un timeout pour éviter les blocages
            set_time_limit(30); // 30 secondes maximum pour récupérer les données
            
            // Vérifier que le client HTTP est disponible
            if (!$this->client) {
                throw new Exception("ERREUR CRITIQUE: Goutte\Client n'est pas disponible. Exécutez 'composer require fabpot/goutte' pour l'installer.");
            }
            
            // Mode production avec client web - TOUJOURS utiliser des données réelles selon CLAUDE.md
            error_log("Récupération des données officielles depuis tirage-gagnant.com/amigo/...");
            $crawler = $this->client->request('GET', 'https://tirage-gagnant.com/amigo/');
            error_log("Page récupérée, analyse du contenu...");
            
            // Initialiser les tableaux pour les numéros bleus et jaunes
            $blueNumbers = [];
            $yellowNumbers = [];
            
            // Récupérer tous les numéros avec gestion d'erreurs
            $allNumbers = [];
            try {
                // Récupérer les numéros du dernier tirage avec le sélecteur principal
                $allNumbers = [];
                $crawler->filter('.num, .chance')->each(function ($node) use (&$allNumbers) {
                    $text = $node->text();
                    if (is_numeric($text) && (int)$text >= 1 && (int)$text <= 28) {
                        $allNumbers[] = (int)$text;
                    }
                });
                
                // Vérifier que nous avons suffisamment de numéros
                if (count($allNumbers) < TirageStrategies::TIRAGE_SIZE) {
                    // Essayer un autre sélecteur
                    $tempNumbers = [];
                    $crawler->filter('span[class*="num"]')->each(function ($node) use (&$tempNumbers) {
                        $text = $node->text();
                        if (is_numeric($text) && (int)$text >= 1 && (int)$text <= 28) {
                            $tempNumbers[] = (int)$text;
                        }
                    });
                    
                    // Si le second sélecteur trouve des numéros, les utiliser
                    if (count($tempNumbers) >= TirageStrategies::TIRAGE_SIZE) {
                        $allNumbers = array_slice($tempNumbers, 0, TirageStrategies::TIRAGE_SIZE);
                        error_log("Récupération réussie avec le sélecteur secondaire: " . count($allNumbers) . " numéros");
                    } else {
                        // Si nous n'avons toujours pas assez de numéros, c'est une erreur critique
                        error_log("ERREUR : Impossible de récupérer suffisamment de numéros de tirage officiels");
                        throw new Exception("Impossible de récupérer les numéros officiels du tirage. Veuillez vérifier la connexion au site tirage-gagnant.com.");
                    }
                } else {
                    error_log("Récupération réussie : " . count($allNumbers) . " numéros officiels trouvés");
                }
            } catch (\Exception $e) {
                // En cas d'erreur, lancer une exception
                error_log("Erreur lors de l'extraction des numéros: " . $e->getMessage());
                throw new Exception("Impossible d'extraire les numéros du tirage. Données réelles non disponibles.");
            }
            
            // S'assurer d'avoir suffisamment de numéros
            if (count($allNumbers) < TirageStrategies::TIRAGE_SIZE) {
                throw new Exception("Données incomplètes : nombre insuffisant de numéros dans le tirage récent.");
            }
            
            // Les 7 premiers sont bleus, les 5 suivants sont jaunes
            $blueNumbers = array_slice($allNumbers, 0, TirageStrategies::BLUE_COUNT);
            $yellowNumbers = array_slice($allNumbers, TirageStrategies::BLUE_COUNT, TirageStrategies::YELLOW_COUNT);
            
            // Format structuré pour meilleure compatibilité
            $numSortis = [
                'blue' => $blueNumbers,
                'yellow' => $yellowNumbers
            ];
            
            // Format plat pour compatibilité avec ancien code
            $numSortisB = array_merge($blueNumbers, $yellowNumbers);
            
            // Calculer les statistiques
            // Assurer que les variables sont dans le format attendu
            $numSortisForFreq = is_array($numSortis) ? $numSortis : [];
            $numSortisBForFreq = is_array($numSortisB) ? $numSortisB : [];
            
            $grille = $this->calculateFrequency($numSortisForFreq);
            $grilleB = $this->calculateFrequency($numSortisBForFreq);
            
            $data = [
                'numSortis' => $numSortis,
                'numSortisB' => $numSortisB,
                'grille' => $grille,
                'grilleB' => $grilleB,
                'fetchTime' => time(),
                'dataSource' => 'tirage-gagnant.com',
                'lastUpdated' => date('d/m/Y H:i:s', time()),
                'isAuthentic' => true
            ];
            
            // Données prêtes à être retournées
            
            return $data;
        } catch (Exception $e) {
            // En cas d'erreur, retourner un tableau vide avec message d'erreur
            return [
                'numSortis' => [],
                'numSortisB' => [],
                'grille' => [],
                'grilleB' => [],
                'error' => $e->getMessage(),
                'isAuthentic' => false,
                'notice' => 'IMPORTANT: Impossible de récupérer les données réelles. Les tirages optimisés sont basés sur des simulations et peuvent être inexacts.'
            ];
        }
    }
    
    /**
     * Récupère les données historiques (par défaut 1000 tirages)
     * 
     * @param int $limit Nombre de tirages à récupérer
     * @return array Données historiques
     */
    public function getHistoricalTirages($limit = 1000) {
        
        // Récupérer directement depuis la source officielle
        try {
            // Ajouter un timeout pour éviter les blocages
            set_time_limit(30); // 30 secondes maximum pour récupérer les données
            
            // Vérifier que le client HTTP est disponible
            if (!$this->client) {
                throw new Exception("ERREUR CRITIQUE: Goutte\Client n'est pas disponible. Exécutez 'composer require fabpot/goutte' pour l'installer.");
            }
            
            // Mode production avec client web
            // Pour les données historiques, on utilise reducmiz.com qui permet d'avoir plus de tirages
            error_log("Récupération des données historiques depuis reducmiz.com...");
            
            // La source principale pour les données historiques est reducmiz.com
            $source = 'https://www.reducmiz.com/resultat_fdj.php?jeu=amigo&nb=all';
            $sourceSite = $source;
            
            error_log("Récupération des données historiques depuis $source...");
            
            try {
                $crawler = $this->client->request('GET', $source);
                
                // Récupérer les données avec le sélecteur spécifique à reducmiz.com
                $allNumbers = [];
                $crawler->filter('.bs-docs-section font')->each(function ($node) use (&$allNumbers) {
                    preg_match_all('/\d+/', $node->text(), $matches);
                    if (!empty($matches[0])) {
                        $allNumbers[] = $matches[0];
                    }
                });
                
                // Vérifier si nous avons récupéré des données
                if (empty($allNumbers)) {
                    error_log("Aucune donnée trouvée sur reducmiz.com, essai de la source de secours.");
                    
                    // Source de secours : tirage-gagnant.com
                    $backupSource = 'https://tirage-gagnant.com/amigo/';
                    error_log("Tentative de récupération depuis $backupSource...");
                    
                    $crawler = $this->client->request('GET', $backupSource);
                    $sourceSite = $backupSource;
                    
                    // Récupérer les historiques disponibles sur tirage-gagnant.com
                    $allNumbers = [];
                    $crawler->filter('.historique-tirages tr, .resultats-tirage').each(function ($node) use (&$allNumbers) {
                        $tirage = [];
                        $node->filter('.num, .chance, td:not(.date)')->each(function ($numNode) use (&$tirage) {
                            $text = trim($numNode->text());
                            if (preg_match('/^\d+$/', $text) && (int)$text >= 1 && (int)$text <= 28) {
                                $tirage[] = (int)$text;
                            }
                        });
                        
                        if (count($tirage) > 0) {
                            $allNumbers[] = $tirage;
                        }
                    });
                    
                    // Si toujours aucune donnée, c'est une erreur critique
                    if (empty($allNumbers)) {
                        error_log("ERREUR : Aucune donnée historique trouvée sur les sources officielles");
                        throw new Exception("Impossible de récupérer les données historiques officielles. Veuillez vérifier votre connexion internet.");
                    }
                }
                
                error_log("Données historiques récupérées avec succès: " . count($allNumbers) . " groupes de numéros");
            } catch (\Exception $e) {
                error_log("Erreur lors de la récupération des données historiques: " . $e->getMessage());
                throw new Exception("Impossible de récupérer les données historiques officielles: " . $e->getMessage());
            }
            
            if (empty($allNumbers)) {
                error_log("Aucune donnée historique trouvée dans toutes les sources");
                throw new Exception("Impossible de récupérer les données historiques. Veuillez vérifier votre connexion internet.");
            }
            
            // Aplatir le tableau et convertir en nombres avec gestion d'erreurs
            $allNumsFlat = [];
            if (is_array($allNumbers)) {
                foreach ($allNumbers as $group) {
                    if (is_array($group)) {
                        foreach ($group as $number) {
                            if (is_numeric($number)) {
                                $num = (int)trim($number);
                                if ($num >= 1 && $num <= 28) {
                                    $allNumsFlat[] = $num;
                                }
                            }
                        }
                    }
                }
            }
            
            // Vérifier si nous avons assez de données
            if (count($allNumsFlat) < TirageStrategies::TIRAGE_SIZE) {
                throw new Exception("Données historiques insuffisantes pour une analyse fiable. Impossible d'accéder aux données réelles.");
            }
            
            // Limiter le nombre de tirages si demandé
            if ($limit > 0 && count($allNumsFlat) > ($limit * TirageStrategies::TIRAGE_SIZE)) {
                $allNumsFlat = array_slice($allNumsFlat, 0, $limit * TirageStrategies::TIRAGE_SIZE);
            }
            
            // Structurer les données en tirages de TIRAGE_SIZE numéros (7 bleus + 5 jaunes)
            $numbers = [];
            $chunks = array_chunk($allNumsFlat, TirageStrategies::TIRAGE_SIZE);
            
            foreach ($chunks as $chunk) {
                // Vérifier que le chunk a la bonne taille
                if (count($chunk) < TirageStrategies::TIRAGE_SIZE) {
                    continue; // Ignorer ce tirage s'il est incomplet
                }
                
                $blue = array_slice($chunk, 0, TirageStrategies::BLUE_COUNT);
                $yellow = array_slice($chunk, TirageStrategies::BLUE_COUNT, TirageStrategies::YELLOW_COUNT);
                
                // Pour les tests, on considère que tous les tirages sont d'aujourd'hui
                $numbers[] = [
                    'blue' => $blue,
                    'yellow' => $yellow,
                    'all' => $chunk,
                    'date' => date('Y-m-d') // Utiliser directement la date au format Y-m-d
                ];
            }
            
            // Calculer les fréquences
            $frequency = $this->calculateFrequency($numbers);
            
            // Extraire les dates pour les filtrer plus facilement
            $dates = [];
            foreach ($numbers as $index => $tirage) {
                $dates[$index] = $tirage['date'];
            }
            
            $data = [
                'numbers' => $numbers,
                'frequency' => $frequency,
                'count' => count($numbers),
                'fetchTime' => time(),
                'dataSource' => 'reducmiz.com',
                'lastUpdated' => date('d/m/Y H:i:s', time()),
                'isAuthentic' => true,
                'dates' => $dates // Ajouter les dates pour le filtrage
            ];
            
            // Données prêtes à être retournées
            
            return $data;
        } catch (Exception $e) {
            // En cas d'erreur, retourner un tableau vide avec message d'erreur
            return [
                'numbers' => [],
                'frequency' => [],
                'count' => 0,
                'error' => $e->getMessage(),
                'isAuthentic' => false,
                'notice' => 'IMPORTANT: Impossible de récupérer les données historiques réelles. Les tirages optimisés sont basés sur des simulations et peuvent être inexacts.'
            ];
        }
    }
    
    /**
     * Récupère encore plus de données historiques depuis plusieurs sources
     * 
     * @return array Données historiques enrichies
     */
    public function getExtendedHistoricalData() {
        
        // Récupérer les données de base
        $baseData = $this->getHistoricalTirages(1000);
        
        // Récupérer d'autres données de sources complémentaires
        $extendedData = $this->fetchComplementarySources();
        
        // Fusionner les données
        $mergedNumbers = array_merge($baseData['numbers'], $extendedData['numbers']);
        
        // Calculer les nouvelles fréquences
        $frequency = $this->calculateFrequency($mergedNumbers);
        
        // Calculer les hot/cold numbers sur différentes périodes
        $hotColdPeriods = [
            'week' => array_slice($mergedNumbers, 0, min(500, count($mergedNumbers))),
            'month' => array_slice($mergedNumbers, 0, min(1000, count($mergedNumbers))),
            'quarter' => array_slice($mergedNumbers, 0, min(1000, count($mergedNumbers))),
            'all' => $mergedNumbers
        ];
        
        $hotColdStats = [];
        foreach ($hotColdPeriods as $period => $nums) {
            $hotColdStats[$period] = $this->calculateFrequency($nums);
        }
        
        // Analyser les tendances
        $trends = $this->analyzeTrends($mergedNumbers);
        
        $data = [
            'numbers' => $mergedNumbers,
            'frequency' => $frequency,
            'count' => count($mergedNumbers),
            'hotColdStats' => $hotColdStats,
            'trends' => $trends,
            'fetchTime' => time(),
            'dataSource' => 'multiple sources',
            'lastUpdated' => date('Y-m-d H:i:s'),
            'isAuthentic' => true,
            'sources' => ['reducmiz.com', 'resultats-loto.com']
        ];
        
        // Retourner directement les données
        return $data;
    }
    
    /**
     * Sources complémentaires pour enrichir les données
     * 
     * @return array Données complémentaires
     */
    private function fetchComplementarySources() {
        $numbers = [];
        
        // Vérifier que le client HTTP est disponible
        if (!$this->client) {
            error_log("ERREUR: Impossible de récupérer des données de sources complémentaires - Goutte\Client n'est pas disponible");
            return ['numbers' => [], 'error' => 'Client HTTP non disponible'];
        }
        
        try {
            // Tenter d'obtenir des données réelles d'autres sources
            $otherSources = [
                'https://www.resultats-loto.com/amigo/resultats'
            ];
            
            foreach ($otherSources as $source) {
                try {
                    $crawler = $this->client->request('GET', $source);
                    $crawler->filter('.tirage-number')->each(function ($node) use (&$numbers) {
                        $num = (int)$node->text();
                        if ($num >= 1 && $num <= 28) {
                            $numbers[] = $num;
                        }
                    });
                } catch (Exception $e) {
                    // Ignorer les erreurs individuelles et continuer
                    error_log("Erreur lors de la récupération depuis $source: " . $e->getMessage());
                    continue;
                }
            }
        } catch (Exception $e) {
            // Si toutes les sources échouent, on retourne un tableau vide
            error_log("Erreur générale lors de la récupération de sources complémentaires: " . $e->getMessage());
            return ['numbers' => [], 'error' => $e->getMessage()];
        }
        
        return [
            'numbers' => $numbers,
            'count' => count($numbers),
            'sources' => ['resultats-loto.com'],
            'isAuthentic' => true
        ];
    }
    
    /**
     * Calcule la fréquence d'apparition des nombres
     * 
     * @param array $numbers Liste de nombres ou tirages structurés
     * @return array Tableau de fréquences
     */
    private function calculateFrequency($numbers) {
        $frequency = [];
        
        // Si $numbers n'est pas un tableau, retourner un tableau vide
        if (!is_array($numbers)) {
            return $frequency;
        }
        
        // Vérifier si les données sont structurées avec blue/yellow
        if (!empty($numbers) && isset(reset($numbers)['blue'])) {
            // Format structuré
            foreach ($numbers as $tirage) {
                // Ajouter les numéros bleus
                if (isset($tirage['blue']) && is_array($tirage['blue'])) {
                    foreach ($tirage['blue'] as $number) {
                        if (is_numeric($number) && isset($frequency[$number])) {
                            $frequency[$number]++;
                        } else if (is_numeric($number)) {
                            $frequency[$number] = 1;
                        }
                    }
                }
                
                // Ajouter les numéros jaunes
                if (isset($tirage['yellow']) && is_array($tirage['yellow'])) {
                    foreach ($tirage['yellow'] as $number) {
                        if (is_numeric($number) && isset($frequency[$number])) {
                            $frequency[$number]++;
                        } else if (is_numeric($number)) {
                            $frequency[$number] = 1;
                        }
                    }
                }
                
                // Si 'all' est disponible mais pas 'blue' ou 'yellow'
                if ((!isset($tirage['blue']) || !isset($tirage['yellow'])) && isset($tirage['all']) && is_array($tirage['all'])) {
                    foreach ($tirage['all'] as $number) {
                        if (is_numeric($number) && isset($frequency[$number])) {
                            $frequency[$number]++;
                        } else if (is_numeric($number)) {
                            $frequency[$number] = 1;
                        }
                    }
                }
            }
        } else {
            // Format plat (ancien format)
            foreach ($numbers as $number) {
                // Vérifier que le nombre est bien numérique
                if (is_numeric($number)) {
                    if (isset($frequency[$number])) {
                        $frequency[$number]++;
                    } else {
                        $frequency[$number] = 1;
                    }
                } elseif (is_array($number)) {
                    // Si c'est un tableau, traiter récursivement
                    $subFreq = $this->calculateFrequency($number);
                    foreach ($subFreq as $key => $count) {
                        if (isset($frequency[$key])) {
                            $frequency[$key] += $count;
                        } else {
                            $frequency[$key] = $count;
                        }
                    }
                }
            }
        }
        
        return $frequency;
    }
    
    /**
     * Analyse les tendances dans les séquences de nombres
     * 
     * @param array $numbers Séquence de nombres
     * @return array Tendances détectées
     */
    private function analyzeTrends($numbers) {
        $trends = [];
        
        // Analyser les séquences de 7 nombres consécutifs
        $sequenceLength = 7;
        $sequences = [];
        for ($i = 0; $i < count($numbers) - $sequenceLength; $i += $sequenceLength) {
            $sequence = array_slice($numbers, $i, $sequenceLength);
            $key = implode('-', $sequence);
            
            if (isset($sequences[$key])) {
                $sequences[$key]++;
            } else {
                $sequences[$key] = 1;
            }
        }
        
        // Trouver les séquences les plus fréquentes
        arsort($sequences);
        $topSequences = array_slice($sequences, 0, 10, true);
        
        // Analyser les paires de nombres qui apparaissent souvent ensemble
        $pairs = [];
        for ($i = 0; $i < count($numbers) - 1; $i++) {
            for ($j = $i + 1; $j < min($i + 10, count($numbers)); $j++) {
                $pair = min($numbers[$i], $numbers[$j]) . '-' . max($numbers[$i], $numbers[$j]);
                
                if (isset($pairs[$pair])) {
                    $pairs[$pair]++;
                } else {
                    $pairs[$pair] = 1;
                }
            }
        }
        
        // Trouver les paires les plus fréquentes
        arsort($pairs);
        $topPairs = array_slice($pairs, 0, 15, true);
        
        $trends = [
            'topSequences' => $topSequences,
            'topPairs' => $topPairs
        ];
        
        return $trends;
    }
}