<?php

/**
 * Classe AIStrategyManager
 * 
 * Gère les différentes stratégies IA pour le tirage Amigo
 * Fournit une façade pour l'accès aux stratégies
 * 
 * @package Amelie\Strategies
 */
class AIStrategyManager
{
    /**
     * Génère toutes les stratégies IA disponibles
     * 
     * @return array Liste des informations de stratégie
     */
    public static function generateAll()
    {
        $strategies = [];
        
        // Instancier et récupérer les résultats de chaque stratégie
        $strategies[] = (new BayesianEVStrategy())->generate();
        $strategies[] = (new MarkovROIStrategy())->generate();
        $strategies[] = (new MLPredictStrategy())->generate();
        $strategies[] = (new BanditSelectorStrategy())->generate();
        $strategies[] = (new ClusterEVStrategy())->generate();
        
        // Trier par espérance de gain décroissante
        usort($strategies, function ($a, $b) {
            return $b['ev'] <=> $a['ev'];
        });
        
        return $strategies;
    }
    
    /**
     * Retourne la meilleure stratégie (celle avec l'EV la plus élevée)
     * 
     * @return array Informations sur la meilleure stratégie
     */
    public static function bestPick()
    {
        $strategies = self::generateAll();
        
        // Si aucune stratégie n'a été générée, retourner une valeur par défaut
        if (empty($strategies)) {
            return [
                'strategyId' => 'ai_default',
                'label' => 'IA par défaut',
                'numbers' => range(1, 7),
                'ev' => 0,
                'roi' => 0
            ];
        }
        
        // Retourner la première stratégie (qui a déjà été triée par EV décroissante)
        return $strategies[0];
    }
}