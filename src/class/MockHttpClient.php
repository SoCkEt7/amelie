<?php

/**
 * Client HTTP simulé pour l'exécution en CLI
 * Permet d'initialiser le cache sans dépendances web
 */
class MockHttpClient {
    public function request($method, $url) {
        return new MockCrawler($url);
    }
}

/**
 * Simule le comportement d'un Crawler Goutte pour l'exécution en CLI
 */
class MockCrawler {
    private $url;
    
    public function __construct($url) {
        $this->url = $url;
    }
    
    public function filter($selector) {
        return $this;
    }
    
    public function each($callback) {
        // Simuler des résultats en fonction de l'URL
        if (strpos($this->url, 'amigo') !== false) {
            // Générer des nombres aléatoires pour simuler les tirages
            for ($i = 0; $i < 28; $i++) {
                $node = new MockNode(mt_rand(1, 28));
                call_user_func($callback, $node, $i);
            }
        }
    }
    
    public function text() {
        return mt_rand(1, 28);
    }
    
    public function html() {
        return '<span>' . mt_rand(1, 28) . '</span>';
    }
}

/**
 * Simule un nœud DOM pour l'exécution en CLI
 */
class MockNode {
    private $text;
    
    public function __construct($text) {
        $this->text = $text;
    }
    
    public function text() {
        return $this->text;
    }
    
    public function html() {
        return '<span>' . $this->text . '</span>';
    }
}