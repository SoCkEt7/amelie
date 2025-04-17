<?php
require_once 'src/startup.php';

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['connected'])) {
    header('Location: index.php');
    exit();
}

// Afficher les classes disponibles pour le débogage
echo "<!-- Classes chargées: " . implode(", ", get_declared_classes()) . " -->";

// Vérifier que la classe existe
if (!class_exists('AIStrategyManager')) {
    die("Erreur: La classe AIStrategyManager n'est pas disponible.");
}

try {
    // Récupérer toutes les stratégies IA
    $strategies = AIStrategyManager::generateAll();
} catch (Exception $e) {
    die("Erreur lors de la génération des stratégies IA: " . $e->getMessage());
}

// Inclure le header
$pageTitle = "Stratégies IA - toutes les données";
include('assets/header.php');
?>

<div class="card mb-4">
    <div class="card-header bg-dark">
        <h2 class="mb-0 fs-5">
            <i class="fas fa-robot me-2"></i>
            Stratégies IA - toutes les données
        </h2>
    </div>
    <div class="card-body">
        <p class="small text-muted mb-3">
            Ces stratégies sont calculées en temps réel sur l'ensemble des données historiques, 
            sans utiliser de cache. Chaque stratégie utilise une approche différente pour sélectionner
            les 7 numéros à jouer.
        </p>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Stratégie</th>
                        <th>Recommandation</th>
                        <th>EV (€)</th>
                        <th>ROI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($strategies as $strategy): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($strategy['label']); ?></strong>
                        </td>
                        <td>
                            <?php 
                            // Récupérer les numéros bleus/jaunes du tirage courant pour déterminer la couleur
                            $blueNumbers = [];
                            $yellowNumbers = [];
                            
                            $dataFetcher = new TirageDataFetcher();
                            $recentData = $dataFetcher->getRecentTirages();
                            if (isset($recentData['numSortis']) && !empty($recentData['numSortis'])) {
                                $blueNumbers = isset($recentData['numSortis']['blue']) ? $recentData['numSortis']['blue'] : [];
                                $yellowNumbers = isset($recentData['numSortis']['yellow']) ? $recentData['numSortis']['yellow'] : [];
                            }
                            
                            // S'assurer qu'il n'y a pas de doublons dans la stratégie
                            $uniqueNumbers = array_unique($strategy['numbers']);
                            
                            // Compléter à 7 si nécessaire
                            if (count($uniqueNumbers) < 7) {
                                $i = 1;
                                while (count($uniqueNumbers) < 7 && $i <= 28) {
                                    if (!in_array($i, $uniqueNumbers)) {
                                        $uniqueNumbers[] = $i;
                                    }
                                    $i++;
                                }
                                sort($uniqueNumbers);
                            }
                            
                            // Afficher les numéros avec la couleur appropriée
                            foreach ($uniqueNumbers as $number):
                                $inBlue = in_array($number, $blueNumbers);
                                $inYellow = in_array($number, $yellowNumbers);
                                $bgClass = $inBlue ? "bg-primary" : ($inYellow ? "bg-success" : "bg-secondary");
                            ?>
                            <span class="badge <?php echo $bgClass; ?> me-1"><?php echo $number; ?></span>
                            <?php endforeach; ?>
                        </td>
                        <td><?php echo number_format($strategy['ev'], 2); ?></td>
                        <td><?php echo number_format($strategy['roi'], 3); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer text-muted">
        <small>
            <i class="fas fa-info-circle me-1"></i>
            EV = Espérance de gain (Expected Value) - ROI = Retour sur investissement (Return on Investment)
            <br>
            <span class="badge bg-primary me-1">1</span> = Numéro bleu dans le tirage actuel
            <span class="badge bg-success me-1">2</span> = Numéro jaune dans le tirage actuel
            <span class="badge bg-secondary me-1">3</span> = Numéro non présent dans le tirage actuel
            <br>
            <i class="fas fa-database me-1"></i> Basé sur <?php echo TirageDataset::getStats()['count']; ?> tirages historiques
        </small>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-dark">
        <h3 class="mb-0 fs-5">À propos des stratégies IA</h3>
    </div>
    <div class="card-body">
        <dl class="row small">
            <dt class="col-md-3">Bayesian EV</dt>
            <dd class="col-md-9">Modèle bêta-binomial qui maintient des compteurs alpha/beta pour chaque numéro et calcule la probabilité postérieure pour maximiser l'espérance de gain.</dd>
            
            <dt class="col-md-3">Markov ROI</dt>
            <dd class="col-md-9">Utilise une matrice de transition Markovienne pour déterminer les probabilités de transition entre numéros, optimisée pour le ROI.</dd>
            
            <dt class="col-md-3">ML Predict</dt>
            <dd class="col-md-9">Régression logistique basée sur des features comme la fréquence récente, le temps depuis la dernière apparition et la distribution bleu/jaune.</dd>
            
            <dt class="col-md-3">Bandit Selector</dt>
            <dd class="col-md-9">Algorithme ε-greedy (avec ε=0.1) qui choisit parmi les autres stratégies selon leur performance historique.</dd>
            
            <dt class="col-md-3">Cluster EV</dt>
            <dd class="col-md-9">Détecte des communautés de numéros qui apparaissent souvent ensemble en utilisant une variante de l'algorithme de Louvain et maximise l'EV des clusters.</dd>
        </dl>
    </div>
</div>

<?php include('assets/footer.php'); ?>