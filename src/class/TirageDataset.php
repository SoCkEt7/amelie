<?php

/**
 * Classe TirageDataset
 * 
 * Parse les fichiers JSON de tirages sans utiliser de cache
 * Conforme aux directives PSR-12
 * 
 * @package Amelie
 */
class TirageDataset
{
    // Constantes
    const TIRAGE_SIZE = 12;            // Taille d'un tirage (7 bleus + 5 jaunes)
    const BLUE_COUNT = 7;              // Nombre de numéros bleus par tirage
    const YELLOW_COUNT = 5;            // Nombre de numéros jaunes par tirage
    const DIRECTORY = 'tirages/';      // Répertoire des fichiers de tirage

    /**
     * Charge les données de tirage à partir d'un fichier JSON
     * 
     * @param string $filename Nom du fichier à charger (null pour prendre le plus récent)
     * @return array Données de tirage
     */
    public static function loadTirages($filename = null)
    {
        // Si aucun nom de fichier n'est spécifié, prendre le plus récent
        if ($filename === null) {
            $filename = self::findMostRecentFile();
        }
        
        $filePath = self::DIRECTORY . $filename;
        
        // Vérifier que le fichier existe
        if (!file_exists($filePath)) {
            error_log("Erreur : le fichier $filePath n'existe pas");
            return [];
        }
        
        // Lire et décoder le JSON
        $data = file_get_contents($filePath);
        if ($data === false) {
            error_log("Erreur : impossible de lire le fichier $filePath");
            return [];
        }
        
        $tirages = json_decode($data, true);
        if ($tirages === null) {
            error_log("Erreur : le fichier $filePath ne contient pas de JSON valide");
            return [];
        }
        
        return $tirages;
    }
    
    /**
     * Trouve le fichier de tirage le plus récent
     * 
     * @return string|null Nom du fichier le plus récent
     */
    private static function findMostRecentFile()
    {
        $pattern = self::DIRECTORY . '*.json';
        $files = glob($pattern);
        
        if (empty($files)) {
            error_log("Aucun fichier de tirage trouvé");
            return null;
        }
        
        // Trier par date de modification (le plus récent d'abord)
        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        // Retourner seulement le nom du fichier, pas le chemin complet
        return basename($files[0]);
    }
    
    /**
     * Obtient tous les tirages (sans filtre)
     * 
     * @return array Tous les tirages
     */
    public static function getAllTirages()
    {
        $data = self::loadTirages();
        
        // Vérifier si les données sont au format attendu
        if (isset($data['numbers'])) {
            return $data['numbers'];
        }
        
        // Si le format est différent, essayer d'autres formats connus
        if (isset($data) && is_array($data)) {
            // Si c'est déjà un tableau de tirages
            if (isset($data[0]) && (isset($data[0]['blue']) || isset($data[0]['all']))) {
                return $data;
            }
        }
        
        // En dernier recours, essayer de récupérer les données via TirageDataFetcher
        $fetcher = new TirageDataFetcher();
        $historicalData = $fetcher->getHistoricalTirages(500);
        
        if (isset($historicalData['numbers'])) {
            return $historicalData['numbers'];
        }
        
        // Si aucune donnée n'est trouvée, retourner un tableau vide
        return [];
    }
    
    /**
     * Obtient les N derniers tirages
     * 
     * @param int $count Nombre de tirages à récupérer
     * @return array Les N derniers tirages
     */
    public static function getLastTirages($count = 50)
    {
        $allTirages = self::getAllTirages();
        return array_slice($allTirages, 0, min($count, count($allTirages)));
    }
    
    /**
     * Obtient les tirages du jour
     * 
     * @param string $date Date au format Y-m-d (ou null pour aujourd'hui)
     * @return array Tirages du jour spécifié
     */
    public static function getTiragesForDate($date = null)
    {
        // Si aucune date n'est spécifiée, utiliser la date du jour
        if ($date === null) {
            $date = date('Y-m-d');
        }
        
        $allTirages = self::getAllTirages();
        $dayTirages = [];
        
        foreach ($allTirages as $tirage) {
            if (isset($tirage['date']) && $tirage['date'] === $date) {
                $dayTirages[] = $tirage;
            }
        }
        
        return $dayTirages;
    }
    
    /**
     * Obtient les données statistiques de base sur tous les tirages
     * 
     * @return array Statistiques sur les tirages
     */
    public static function getStats()
    {
        $allTirages = self::getAllTirages();
        
        // Initialiser les compteurs
        $blueFrequency = array_fill(1, 28, 0);
        $yellowFrequency = array_fill(1, 28, 0);
        $totalFrequency = array_fill(1, 28, 0);
        
        foreach ($allTirages as $tirage) {
            // Compter les numéros bleus
            if (isset($tirage['blue']) && is_array($tirage['blue'])) {
                foreach ($tirage['blue'] as $num) {
                    if ($num >= 1 && $num <= 28) {
                        $blueFrequency[$num]++;
                        $totalFrequency[$num]++;
                    }
                }
            }
            
            // Compter les numéros jaunes
            if (isset($tirage['yellow']) && is_array($tirage['yellow'])) {
                foreach ($tirage['yellow'] as $num) {
                    if ($num >= 1 && $num <= 28) {
                        $yellowFrequency[$num]++;
                        $totalFrequency[$num]++;
                    }
                }
            }
        }
        
        return [
            'blue' => $blueFrequency,
            'yellow' => $yellowFrequency,
            'total' => $totalFrequency,
            'count' => count($allTirages)
        ];
    }
}