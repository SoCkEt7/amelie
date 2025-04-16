<?php
/**
 * Amelie - Stratégies journalières
 * Page d'analyse des stratégies basées uniquement sur les tirages du jour
 */

// Inclure le fichier de démarrage
include 'src/startup.php';

// Inclure les classes nécessaires
require_once 'src/class/TirageDataFetcher.php';
require_once 'src/class/TirageStrategies.php';
require_once 'src/class/TirageDailyStrategies.php';

// Inclure l'en-tête
include 'assets/header.php';

// Gestion de la déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Gestion de la connexion
if (!isset($_SESSION['connected'])) {
    if (isset($_POST['connexion']) && isset($_POST['password'])) {
        if (trim($_POST['password']) === $password) {
            $_SESSION['connected'] = true;
        } else {
            $login_error = "Mot de passe incorrect";
        }
    }
}

// Afficher le formulaire de connexion si non connecté
if (!isset($_SESSION['connected'])) { ?>
    <div align="center" class="form-group p-5">
        <h1>Connexion</h1>
        <form action="" method="post" class="w-100" style="max-width: 400px;">
            <?php if (isset($login_error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($login_error); ?>
            </div>
            <?php endif; ?>
            <div class="mb-3">
                <input class="form-control form-control-lg" type="password" name="password" placeholder="Mot de passe" required autofocus>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg" name="connexion">Connexion</button>
            </div>
        </form>
    </div>
<?php } else {
    // Utilisateur connecté - Afficher l'application
    
    // Activer le rapport d'erreurs pour le débogage
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // Récupérer les données directement
    $dataFetcher = new TirageDataFetcher();
    
    // Récupérer les tirages du jour
    $dailyTirages = TirageDailyStrategies::getDailyTirages($dataFetcher);
    
    // Récupérer le tirage le plus récent pour comparaison
    $recentData = $dataFetcher->getRecentTirages();
    
    // Générer les stratégies basées sur les données du jour
    $dailyStrategiesCalculator = new TirageDailyStrategies($dailyTirages);
    $dailyStrategies = $dailyStrategiesCalculator->getStrategies();
    
    ?>
    
    <div class="container mt-4">
        <h1 class="text-center mb-4">Stratégies Journalières</h1>
        
        <div class="alert alert-info">
            <strong>Règles du jeu:</strong> Choisir 7 numéros parmi la combinaison de 12 numéros (7 bleus et 5 jaunes) tirés au sort.
            <br>
            <strong>Note:</strong> Ces stratégies sont basées uniquement sur les tirages d'aujourd'hui et s'adaptent aux tendances du jour.
        </div>
        
        <?php if (!empty($recentData['error'])): ?>
        <div class="alert alert-danger">
            <strong>Erreur:</strong> <?php echo $recentData['error']; ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($recentData['isAuthentic']) && !$recentData['isAuthentic']): ?>
        <div class="alert alert-warning">
            <strong>Attention:</strong> Les données affichées sont simulées et peuvent ne pas refléter les tirages réels.
        </div>
        <?php endif; ?>
        
        <!-- Informations sur les tirages du jour -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3>Tirages du jour</h3>
            </div>
            <div class="card-body">
                <?php if (count($dailyTirages) > 0): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong><?php echo count($dailyTirages); ?> tirages</strong> analysés aujourd'hui.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Dernier tirage</h4>
                            <?php if (isset($recentData['numSortis']) && !empty($recentData['numSortis'])): ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5>Numéros bleus</h5>
                                        <div class="d-flex flex-wrap">
                                            <?php foreach ($recentData['numSortis']['blue'] as $num): ?>
                                                <div class="number-badge primary"><?php echo $num; ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h5>Numéros jaunes</h5>
                                        <div class="d-flex flex-wrap">
                                            <?php foreach ($recentData['numSortis']['yellow'] as $num): ?>
                                                <div class="number-badge warning"><?php echo $num; ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                <p class="mt-3">
                                    Source: <?php echo isset($recentData['dataSource']) ? $recentData['dataSource'] : 'N/A'; ?>
                                    <br>
                                    Dernière mise à jour: <?php echo isset($recentData['lastUpdated']) ? $recentData['lastUpdated'] : 'N/A'; ?>
                                </p>
                            <?php else: ?>
                                <p>Aucun résultat récent disponible.</p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h4>Statistiques du jour</h4>
                            <?php 
                                // Calculer les statistiques sur les tirages du jour
                                $blueFrequency = array_fill(1, 28, 0);
                                $yellowFrequency = array_fill(1, 28, 0);
                                $totalFrequency = array_fill(1, 28, 0);
                                
                                foreach ($dailyTirages as $tirage) {
                                    if (isset($tirage['blue']) && is_array($tirage['blue'])) {
                                        foreach ($tirage['blue'] as $num) {
                                            if ($num >= 1 && $num <= 28) {
                                                $blueFrequency[$num]++;
                                                $totalFrequency[$num]++;
                                            }
                                        }
                                    }
                                    
                                    if (isset($tirage['yellow']) && is_array($tirage['yellow'])) {
                                        foreach ($tirage['yellow'] as $num) {
                                            if ($num >= 1 && $num <= 28) {
                                                $yellowFrequency[$num]++;
                                                $totalFrequency[$num]++;
                                            }
                                        }
                                    }
                                }
                                
                                // Trouver les numéros les plus fréquents
                                arsort($totalFrequency);
                                $topNumbers = array_slice($totalFrequency, 0, 5, true);
                            ?>
                            
                            <h5>Top 5 des numéros aujourd'hui</h5>
                            <div class="row">
                                <?php foreach ($topNumbers as $num => $count): ?>
                                    <div class="col-md-2 col-4 mb-2">
                                        <div class="card">
                                            <div class="card-body p-2 text-center">
                                                <div class="number-badge primary mb-2"><?php echo $num; ?></div>
                                                <span class="badge bg-secondary"><?php echo $count; ?> fois</span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <p class="mt-3">
                                <strong>Analyse basée sur:</strong> <?php echo count($dailyTirages); ?> tirages aujourd'hui
                            </p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Aucun tirage</strong> n'a été détecté aujourd'hui. Impossible de générer des stratégies journalières.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Stratégies journalières -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h3>Stratégies journalières</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($dailyStrategies)): ?>
                    <div class="row">
                        <?php 
                        // Trier les stratégies en deux groupes - standards et innovantes
                        $standardStrategies = array_slice($dailyStrategies, 0, 5);
                        $innovativeStrategies = array_slice($dailyStrategies, 5);
                        
                        // Afficher un titre pour les stratégies standards
                        if (!empty($standardStrategies)): 
                        ?>
                        <div class="col-12 mb-3">
                            <h4>Stratégies Standards</h4>
                        </div>
                        <?php 
                        endif;
                        
                        // Afficher les stratégies standards
                        foreach ($standardStrategies as $index => $strategy): 
                        ?>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 border-<?php echo $strategy['class']; ?> shadow">
                                    <div class="card-header bg-<?php echo $strategy['class']; ?> text-white py-2">
                                        <h5 class="mb-0"><?php echo $strategy['name']; ?> 
                                            <span class="badge  text-dark float-end">
                                                <?php echo $strategy['rating']; ?>/10
                                            </span>
                                        </h5>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="d-flex flex-wrap mb-3 justify-content-center">
                                            <?php foreach ($strategy['numbers'] as $num): ?>
                                                <div class="number-badge <?php echo $strategy['class']; ?> m-1"><?php echo $num; ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                        
                                        <!-- Explication de la sélection -->
                                        <div class="small text-muted mb-2"><?php echo $strategy['description']; ?></div>
                                        
                                        <div class="small  p-2 rounded">
                                            <div class="row g-1">
                                                <div class="col-4"><strong>Méthode:</strong></div>
                                                <div class="col-8"><?php echo $strategy['method']; ?></div>
                                            </div>
                                            <div class="row g-1">
                                                <div class="col-4"><strong>Optimale:</strong></div>
                                                <div class="col-8"><?php echo $strategy['bestPlayCount']; ?> numéros</div>
                                            </div>
                                            <div class="row g-1">
                                                <div class="col-4"><strong>Mise:</strong></div>
                                                <div class="col-8"><?php echo $strategy['optimalBet']; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php 
                        // Afficher un titre pour les stratégies innovantes
                        if (!empty($innovativeStrategies)): 
                        ?>
                        <div class="col-12 mt-4 mb-3">
                            <h4 class="text-primary">Stratégies Innovantes <span class="badge bg-danger">Nouveau!</span></h4>
                            <p class="small text-muted">Ces stratégies avancées analysent les données journalières avec des algorithmes plus sophistiqués.</p>
                        </div>
                        <?php 
                        endif;
                        
                        // Afficher les stratégies innovantes
                        foreach ($innovativeStrategies as $index => $strategy): 
                        ?>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 border-<?php echo $strategy['class']; ?> shadow">
                                    <div class="card-header bg-<?php echo $strategy['class']; ?> text-white py-2">
                                        <h5 class="mb-0"><?php echo $strategy['name']; ?> 
                                            <span class="badge  text-dark float-end">
                                                <?php echo $strategy['rating']; ?>/10
                                            </span>
                                        </h5>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="d-flex flex-wrap mb-3 justify-content-center">
                                            <?php foreach ($strategy['numbers'] as $num): ?>
                                                <div class="number-badge <?php echo $strategy['class']; ?> m-1"><?php echo $num; ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                        
                                        <!-- Explication de la sélection -->
                                        <div class="small text-muted mb-2"><?php echo $strategy['description']; ?></div>
                                        
                                        <div class="small  p-2 rounded">
                                            <div class="row g-1">
                                                <div class="col-4"><strong>Méthode:</strong></div>
                                                <div class="col-8"><?php echo $strategy['method']; ?></div>
                                            </div>
                                            <div class="row g-1">
                                                <div class="col-4"><strong>Optimale:</strong></div>
                                                <div class="col-8"><?php echo $strategy['bestPlayCount']; ?> numéros</div>
                                            </div>
                                            <div class="row g-1">
                                                <div class="col-4"><strong>Mise:</strong></div>
                                                <div class="col-8"><?php echo $strategy['optimalBet']; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>Aucune stratégie disponible.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Explication de l'approche -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h3>À propos des stratégies journalières</h3>
            </div>
            <div class="card-body">
                <p>Les stratégies journalières sont conçues pour s'adapter aux tendances spécifiques qui se développent au cours d'une même journée de tirages Amigo. Contrairement aux stratégies standard qui se basent sur l'historique complet, ces stratégies se concentrent uniquement sur les tirages du jour actuel.</p>
                
                <div class="row">
                    <div class="col-md-6">
                        <h4>Les stratégies standards</h4>
                        <ul class="list-group">
                            <li class="list-group-item">
                                <strong>Écarts Journaliers</strong> - Numéros qui n'ont pas été tirés depuis longtemps aujourd'hui, suivant le principe qu'ils sont "dus".
                            </li>
                            <li class="list-group-item">
                                <strong>Tendance Horaire</strong> - Exploite les variations statistiques selon les périodes de la journée (matin/après-midi/soir).
                            </li>
                            <li class="list-group-item">
                                <strong>Tendances du Jour</strong> - Détecte les numéros "chauds" qui sortent fréquemment aujourd'hui.
                            </li>
                            <li class="list-group-item">
                                <strong>Positions Stables</strong> - Identifie les numéros qui apparaissent de façon constante en position bleue ou jaune.
                            </li>
                            <li class="list-group-item">
                                <strong>Groupes du Jour</strong> - Repère les numéros qui tendent à apparaître ensemble dans les tirages du jour.
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h4>Les nouvelles stratégies innovantes</h4>
                        <ul class="list-group">
                            <li class="list-group-item list-group-item-secondary">
                                <strong>Chaînes Séquentielles</strong> - Analyse les transitions et séquences des numéros dans l'ordre d'apparition pour détecter les patterns réguliers.
                            </li>
                            <li class="list-group-item list-group-item-dark">
                                <strong>Numéros Versatiles</strong> - Identifie les numéros qui changent fréquemment de position (bleu/jaune) pour exploiter cette volatilité.
                            </li>
                            <li class="list-group-item list-group-item-success">
                                <strong>Équilibre Journalier 4B-3J</strong> - Optimise la sélection pour viser le meilleur ratio bleu/jaune (4-3) selon les tendances du jour.
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h4>Points forts</h4>
                        <ul class="list-group">
                            <li class="list-group-item">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Adaptation rapide</strong> - S'ajuste aux tendances du jour même.
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Détection de schémas temporaires</strong> - Exploite les patterns qui peuvent être invisibles sur le long terme.
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Diversité d'approches</strong> - Propose 8 angles d'analyse différents sur les mêmes données.
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-sync text-info me-2"></i>
                                <strong>Actualisation</strong> - Les stratégies évoluent automatiquement au fil de la journée.
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h4>Comment maximiser vos chances</h4>
                        <div class="alert alert-success p-3">
                            <h5><i class="fas fa-trophy me-2"></i> Conseils pour optimiser vos gains</h5>
                            <ol class="mb-0">
                                <li>Privilégiez la stratégie <strong>Équilibre Journalier 4B-3J</strong> qui offre le meilleur rapport probabilité/gain (note 9.0/10)</li>
                                <li>Pour une approche différente, essayez les <strong>Chaînes Séquentielles</strong> (8.8/10) qui exploitent l'ordre d'apparition des numéros</li>
                                <li>Préférez les mises de 6€ ou 8€ pour maximiser votre retour sur investissement</li>
                                <li>Jouez toujours 7 numéros, jamais plus ni moins</li>
                                <li>Les stratégies sont plus fiables en fin de journée, quand plus de tirages ont été analysés</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php 
}

// Inclure le pied de page
include 'assets/footer.php';
?>