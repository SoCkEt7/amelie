<?php
/**
 * Classe de gestion du cache des données de tirages
 */
class DataCache {
    private $cachePath;
    private $cacheLifetime; // en secondes
    
    /**
     * Constructeur
     * 
     * @param string $cachePath Chemin du dossier de cache
     * @param int $cacheLifetime Durée de vie du cache en secondes (par défaut 3 jours pour les données historiques)
     */
    public function __construct($cachePath = null, $cacheLifetime = 259200) {
        $this->cachePath = $cachePath ?: dirname(__DIR__) . '/cache';
        $this->cacheLifetime = $cacheLifetime;
        
        // Créer le dossier de cache s'il n'existe pas
        if (!file_exists($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }
    
    /**
     * Récupère des données du cache (désactivé - retourne toujours null)
     * 
     * @param string $key Clé de cache
     * @param bool $ignoreExpiry Ignorer l'expiration du cache
     * @return mixed|null Données ou null si non trouvées/expirées
     */
    public function get($key, $ignoreExpiry = false) {
        // Cache désactivé - toujours retourner null
        return null;
    }
    
    /**
     * Stocke des données dans le cache (désactivé - ne fait rien)
     * 
     * @param string $key Clé de cache
     * @param mixed $data Données à stocker
     * @return bool Toujours true
     */
    public function set($key, $data) {
        // Cache désactivé - ne rien faire
        return true;
    }
    
    /**
     * Supprime une entrée du cache (désactivé - ne fait rien)
     * 
     * @param string $key Clé de cache
     * @return bool Toujours true
     */
    public function delete($key) {
        // Cache désactivé - ne rien faire
        return true;
    }
    
    /**
     * Retourne le chemin complet du fichier de cache
     * 
     * @param string $key Clé de cache
     * @return string Chemin du fichier
     */
    private function getCacheFilename($key) {
        return $this->cachePath . '/' . md5($key) . '.json';
    }
    
    /**
     * Vérifie si une entrée existe dans le cache (désactivé - retourne toujours false)
     * 
     * @param string $key Clé de cache
     * @param bool $ignoreExpiry Ignorer l'expiration pour vérifier l'existence
     * @return bool Toujours false
     */
    public function has($key, $ignoreExpiry = false) {
        // Cache désactivé - toujours retourner false
        return false;
    }
    
    /**
     * Nettoie les entrées expirées du cache (désactivé - ne fait rien)
     * 
     * @return int Toujours 0
     */
    public function cleanup() {
        // Cache désactivé - ne rien faire
        return 0;
    }
}