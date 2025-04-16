<?php

/**
 * Classe de vérification de l'authenticité des tirages
 * Vérifie que les données utilisées pour générer les recommandations sont authentiques
 */
class TirageVerifier {
    /**
     * Vérifie l'authenticité des données de tirage
     * 
     * @param array $data Données de tirage à vérifier
     * @return array Informations d'authenticité
     */
    public static function verifyData($data) {
        if (!is_array($data)) {
            return [
                'isAuthentic' => false,
                'reason' => 'Format de données invalide',
                'reliability' => 0
            ];
        }
        
        // Extraction d'un échantillon des données pour preuve
        $sampleData = [];
        if (isset($data['numSortis']) && is_array($data['numSortis'])) {
            $sampleData = array_slice($data['numSortis'], 0, min(count($data['numSortis']), 10));
        } elseif (isset($data['numbers']) && is_array($data['numbers'])) {
            $sampleData = array_slice($data['numbers'], 0, min(count($data['numbers']), 10));
        }
        
        // Vérifier que les données contiennent déjà un indicateur d'authenticité
        if (isset($data['isAuthentic'])) {
            // Si c'est une simulation explicite, ajouter des informations
            if ($data['isAuthentic'] === false) {
                return [
                    'isAuthentic' => false,
                    'reason' => isset($data['notice']) ? $data['notice'] : 'Données simulées',
                    'reliability' => 30,
                    'warning' => 'Les recommandations sont basées sur des données simulées',
                    'sampleData' => $sampleData
                ];
            }
            
            // Vérifier la fraîcheur des données
            $freshness = self::checkDataFreshness($data);
            if ($freshness['isFresh']) {
                return [
                    'isAuthentic' => true,
                    'source' => isset($data['dataSource']) ? $data['dataSource'] : 'Inconnue',
                    'lastUpdated' => isset($data['lastUpdated']) ? $data['lastUpdated'] : 'Inconnue',
                    'reliability' => 95,
                    'notice' => 'Données authentiques et à jour',
                    'freshness' => $freshness,
                    'sampleData' => $sampleData,
                    'fetchTimeHuman' => isset($data['fetchTime']) ? date('Y-m-d H:i:s', $data['fetchTime']) : 'Inconnue'
                ];
            } else {
                return [
                    'isAuthentic' => true,
                    'source' => isset($data['dataSource']) ? $data['dataSource'] : 'Inconnue',
                    'lastUpdated' => isset($data['lastUpdated']) ? $data['lastUpdated'] : 'Inconnue',
                    'reliability' => 70,
                    'warning' => 'Données authentiques mais pas récentes',
                    'freshness' => $freshness,
                    'sampleData' => $sampleData,
                    'fetchTimeHuman' => isset($data['fetchTime']) ? date('Y-m-d H:i:s', $data['fetchTime']) : 'Inconnue'
                ];
            }
        }
        
        // Si pas d'indicateur explicite, vérifier la structure
        $structureCheck = self::checkDataStructure($data);
        if (!$structureCheck['isValid']) {
            return [
                'isAuthentic' => false,
                'reason' => $structureCheck['reason'],
                'reliability' => 10,
                'sampleData' => $sampleData
            ];
        }
        
        // Par défaut, supposer que les données sont authentiques mais avec fiabilité moyenne
        return [
            'isAuthentic' => true,
            'source' => 'Non spécifiée',
            'reliability' => 60,
            'notice' => 'Aucun indicateur d\'authenticité, mais la structure est valide',
            'sampleData' => $sampleData
        ];
    }
    
    /**
     * Vérifie la structure des données
     * 
     * @param array $data Données à vérifier
     * @return array Résultat de vérification
     */
    private static function checkDataStructure($data) {
        // Pour les données récentes
        if (isset($data['numSortis']) && isset($data['grille'])) {
            if (!is_array($data['numSortis']) || empty($data['numSortis'])) {
                return [
                    'isValid' => false,
                    'reason' => 'Aucun tirage récent disponible'
                ];
            }
            return ['isValid' => true];
        }
        
        // Pour les données historiques
        if (isset($data['numbers']) && isset($data['frequency'])) {
            if (!is_array($data['numbers']) || empty($data['numbers'])) {
                return [
                    'isValid' => false,
                    'reason' => 'Aucune donnée historique disponible'
                ];
            }
            return ['isValid' => true];
        }
        
        return [
            'isValid' => false,
            'reason' => 'Structure de données non reconnue'
        ];
    }
    
    /**
     * Vérifie la fraîcheur des données
     * 
     * @param array $data Données à vérifier
     * @return array Résultat de vérification de fraîcheur
     */
    private static function checkDataFreshness($data) {
        if (!isset($data['fetchTime'])) {
            return [
                'isFresh' => false,
                'reason' => 'Pas d\'horodatage'
            ];
        }
        
        $now = time();
        $fetchTime = (int)$data['fetchTime'];
        $age = $now - $fetchTime;
        
        // Selon le type de données, les critères de fraîcheur changent
        if (isset($data['numSortis'])) {
            // Pour les données récentes (max 24h)
            return [
                'isFresh' => $age <= 86400,
                'age' => $age,
                'humanReadableAge' => self::formatTimeInterval($age)
            ];
        } else {
            // Pour les données historiques (max 7 jours)
            return [
                'isFresh' => $age <= 604800,
                'age' => $age,
                'humanReadableAge' => self::formatTimeInterval($age)
            ];
        }
    }
    
    /**
     * Formate un intervalle de temps en texte lisible
     * 
     * @param int $seconds Nombre de secondes
     * @return string Intervalle formaté
     */
    public static function formatTimeInterval($seconds) {
        if ($seconds < 60) {
            return "$seconds secondes";
        }
        if ($seconds < 3600) {
            return floor($seconds / 60) . " minutes";
        }
        if ($seconds < 86400) {
            return floor($seconds / 3600) . " heures";
        }
        return floor($seconds / 86400) . " jours";
    }
}