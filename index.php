<?php
/**
 * Amelie - Version simplifiée
 * Application pour analyser les résultats du jeu Amigo et générer des recommandations
 */

// Inclure le fichier de démarrage
include 'src/startup.php';

// Inclure les classes nécessaires
require_once 'src/class/TirageDataFetcher.php';
require_once 'src/class/TirageStrategies.php';

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
            <div class="alert alert-danger"><?php echo $login_error; ?></div>
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
    
    // Récupérer les données directement (sans cache)
    $dataFetcher = new TirageDataFetcher();
    $recentData = $dataFetcher->getRecentTirages();
    $historicalData = $dataFetcher->getHistoricalTirages(1000); // Limiter à 1000 tirages
    $historicalCount = isset($historicalData['numbers']) ? count($historicalData['numbers']) : 0;
    $recentCount = isset($recentData['numbers']) ? count($recentData['numbers']) : 0;
    
    // Générer les stratégies basées sur les données récupérées
    $strategiesCalculator = new TirageStrategies($historicalData, $recentData);
    $strategies = $strategiesCalculator->getStrategies();

    // Préparer le top 3 safe (accueil + jour)
    $allStrategies = $strategies;
    if (isset($dailyStrategies)) {
        $allStrategies = array_merge($allStrategies, $dailyStrategies);
    }

    // Ajout des valeurs par défaut si manquantes
    foreach ($allStrategies as &$strat) {
        if (!isset($strat['roi'])) $strat['roi'] = 0;
        if (!isset($strat['ev'])) $strat['ev'] = 0;
    }
    unset($strat);
    usort($allStrategies, function($a, $b) {
        if ($a['roi'] == $b['roi']) return $b['ev'] <=> $a['ev'];
        if ($a['roi'] > 0 && $b['roi'] <= 0) return -1;
        if ($a['roi'] <= 0 && $b['roi'] > 0) return 1;
        return $b['roi'] <=> $a['roi'];
    });
    $top3Safe = array_slice($allStrategies, 0, 3);

    // Inclure l'en-tête
    include 'assets/header.php';
    ?>
    
    <link rel="stylesheet" href="assets/data-badge.css">
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-dice fa-lg me-2 text-info"></i> Amélie
            </a>
            <span class="data-badge-info ms-2">
                <i class="fas fa-database"></i>
                <span title="Nombre de tirages historiques analysés"><?php echo $historicalCount; ?> historiques</span>
                <span style="font-size:0.85em;color:#888;margin:0 0.5em;">|</span>
                <span title="Tirages récents"><?php echo $recentCount; ?> récents</span>
            </span>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#strategyTabs"><i class="fas fa-lightbulb me-1"></i>Stratégies</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#stats"><i class="fas fa-chart-bar me-1"></i>Statistiques</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#gainsTabs"><i class="fas fa-coins me-1"></i>Gains</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?logout=1"><i class="fas fa-sign-out-alt me-1"></i>Déconnexion</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <h1 class="text-center mb-4">Amélie - Analyse Amigo</h1>
        
        <div class="alert alert-info">
            <strong>Règles du jeu:</strong> Choisir 7 numéros parmi la combinaison de 12 numéros (7 bleus et 5 jaunes) tirés au sort.
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
        
        <!-- Derniers résultats -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3>Derniers résultats</h3>
            </div>
            <div class="card-body">
                <?php if (isset($recentData['numSortis']) && !empty($recentData['numSortis'])): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Numéros bleus</h4>
                            <div class="d-flex flex-wrap">
                                <?php foreach ($recentData['numSortis']['blue'] as $num): ?>
                                    <div class="number-badge primary"><?php echo $num; ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h4>Numéros jaunes</h4>
                            <div class="d-flex flex-wrap">
                                <?php foreach ($recentData['numSortis']['yellow'] as $num): ?>
                                    <div class="number-badge warning"><?php echo $num; ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <p class="mt-3">Source: <?php echo isset($recentData['dataSource']) ? $recentData['dataSource'] : 'N/A'; ?></p>
                    <p>Dernière mise à jour: <?php echo isset($recentData['lastUpdated']) ? $recentData['lastUpdated'] : 'N/A'; ?></p>
                <?php else: ?>
                    <p>Aucun résultat récent disponible.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Stratégies optimisées -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h3>Stratégies optimisées</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($strategies)): ?>
                    <!-- Interface à onglets pour les stratégies -->
                    <ul class="nav nav-tabs amelie-tabs mb-3" id="strategyTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="top-strategies-tab" data-bs-toggle="tab" data-bs-target="#top-strategies" type="button" role="tab" aria-controls="top-strategies" aria-selected="true">
                                <i class="fas fa-star me-1"></i> Top 3
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="all-strategies-tab" data-bs-toggle="tab" data-bs-target="#all-strategies" type="button" role="tab" aria-controls="all-strategies" aria-selected="false">
                                <i class="fas fa-list me-1"></i> Toutes les stratégies
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="strategyTabsContent">
                        <!-- Onglet Top 3 Stratégies -->
                        <div class="tab-pane fade show active" id="top-strategies" role="tabpanel" aria-labelledby="top-strategies-tab">
                            <div class="row">
                                <?php foreach (array_slice($strategies, 0, 3) as $index => $strategy): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100 border-<?php echo $strategy['class']; ?> shadow">
                                            <div class="card-header bg-<?php echo $strategy['class']; ?> text-white py-2">
                                                <h5 class="mb-0"><?php echo $strategy['name']; ?> 
                                                    <span class="badge bg-light text-dark float-end">
                                                        <?php echo $strategy['rating']; ?>/10
                                                    </span>
                                                </h5>
                                            </div>
                                            <div class="card-body p-3">
                                                <div class="d-flex flex-wrap mb-2 justify-content-center">
                                                    <?php foreach ($strategy['numbers'] as $num): ?>
                                                        <div class="number-badge <?php echo $strategy['class']; ?> m-1 small"><?php echo $num; ?></div>
                                                    <?php endforeach; ?>
                                                </div>
                                                
                                                <!-- Explication de la sélection -->
                                                <div class="small text-muted mb-2"><?php echo $strategy['description']; ?></div>
                                                
                                                <div class="accordion accordion-flush" id="accordionStrat<?php echo $index; ?>">
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header">
                                                            <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>">
                                                                <small><i class="fas fa-lightbulb text-<?php echo $strategy['class']; ?> me-2"></i> Pourquoi ces numéros ?</small>
                                                            </button>
                                                        </h2>
                                                        <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse" data-bs-parent="#accordionStrat<?php echo $index; ?>">
                                                            <div class="accordion-body p-2 small">
                                                                <?php
                                                                    $explanations = [
                                                                        'Numéros Fréquents' => "Ces numéros sont ceux qui apparaissent le plus souvent dans l'historique des tirages. Leur fréquence élevée d'apparition leur donne statistiquement plus de chances de sortir à nouveau.",
                                                                        
                                                                        'Numéros Rares' => "Ces numéros sont ceux qui sont sortis le moins souvent jusqu'à présent. Selon la théorie de la régression vers la moyenne, ils sont statistiquement 'dus' pour apparaître plus fréquemment dans les prochains tirages.",
                                                                        
                                                                        'Stratégie Bleue Maximum' => "Ces 7 numéros apparaissent le plus souvent en position bleue dans les tirages historiques. Cette stratégie vise le jackpot maximal (100 000€ pour une mise de 8€) qui correspond à 7 bleus, 0 jaunes.",
                                                                        
                                                                        'Équilibre Optimal (4B-3J)' => "Cette sélection combine les 4 numéros qui sortent le plus souvent en position bleue et les 3 numéros qui sortent le plus souvent en position jaune. C'est la combinaison avec le meilleur rapport probabilité/gain (1/3 383 pour 400€).",
                                                                        
                                                                        'Stratégie 5B-2J' => "Cette sélection comprend les 5 numéros qui apparaissent le plus souvent en position bleue et les 2 numéros qui apparaissent le plus souvent en position jaune, visant un équilibre entre probabilité (1/5 638) et gain (480€).",
                                                                        
                                                                        'ROI Maximal' => "Ces numéros ont été sélectionnés selon une formule d'optimisation qui équilibre leur probabilité d'apparition en position bleue (60%), jaune (20%) et globale (20%) pour maximiser le retour sur investissement.",
                                                                        
                                                                        'Numéros Chauds' => "Ces numéros sont ceux qui sont sortis le plus fréquemment dans les 200 tirages les plus récents. Ils représentent les tendances actuelles et exploitent le possible 'momentum' de certains numéros.",
                                                                        
                                                                        'Cyclique/Tendances' => "Ces numéros sont ceux qui sont les plus 'mûrs' pour apparaître selon leur cycle d'apparition habituel. Chacun d'eux a dépassé son intervalle moyen entre apparitions.",
                                                                        
                                                                        'Clusters' => "Ces numéros ont été identifiés comme ayant les plus fortes corrélations avec d'autres numéros dans les tirages historiques. Ils font partie de 'groupes' qui tendent à apparaître ensemble.",
                                                                        
                                                                        'Mix Probabilisé' => "Ces numéros ont été sélectionnés selon un calcul d'espérance mathématique qui prend en compte leur contribution à toutes les combinaisons gagnantes possibles du tableau des gains."
                                                                    ];
                                                                    
                                                                    echo isset($explanations[$strategy['name']]) ? $explanations[$strategy['name']] : "Ces numéros ont été sélectionnés selon la méthode d'analyse " . $strategy['method'] . ".";
                                                                ?>
                                                                <div class="mt-2 border-top pt-2">
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
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Onglet Toutes les stratégies -->
                        <div class="tab-pane fade" id="all-strategies" role="tabpanel" aria-labelledby="all-strategies-tab">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Stratégie</th>
                                            <th>Numéros</th>
                                            <th>Note</th>
                                            <th>Description</th>
                                            <th>Mise</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($strategies as $index => $strategy): ?>
                                            <tr>
                                                <td>
                                                    <strong class="text-<?php echo $strategy['class']; ?>">
                                                        <?php echo $strategy['name']; ?>
                                                    </strong>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        <?php foreach ($strategy['numbers'] as $num): ?>
                                                            <div class="number-badge <?php echo $strategy['class']; ?> small" style="width: 1.8rem; height: 1.8rem; font-size: 0.8rem;"><?php echo $num; ?></div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $strategy['class']; ?>"><?php echo $strategy['rating']; ?></span>
                                                </td>
                                                <td>
                                                    <small><?php echo $strategy['description']; ?></small>
                                                </td>
                                                <td><?php echo $strategy['optimalBet']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p>Aucune stratégie disponible.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Statistiques -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h3>Statistiques</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($historicalData) && isset($historicalData['frequency'])): ?>
                    <h4>Fréquence d'apparition des numéros</h4>
                    <div class="chart-container" style="position: relative; height:400px;">
                        <canvas id="frequencyChart"></canvas>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const ctx = document.getElementById('frequencyChart').getContext('2d');
                            const frequencyData = <?php echo json_encode($historicalData['frequency']); ?>;
                            
                            // Préparer les données pour le graphique
                            const labels = Object.keys(frequencyData).map(Number).filter(n => n >= 1 && n <= 28);
                            const data = labels.map(num => frequencyData[num] || 0);
                            
                            // Créer le graphique
                            const chart = new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: 'Fréquence d\'apparition',
                                        data: data,
                                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                                        borderColor: 'rgba(54, 162, 235, 1)',
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        y: {
                                            beginAtZero: true
                                        }
                                    }
                                }
                            });
                        });
                    </script>
                <?php else: ?>
                    <p>Aucune statistique disponible.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Information sur les gains -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h3>Tableau des gains</h3>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs amelie-tabs mb-3" id="gainsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="simple-tab" data-bs-toggle="tab" data-bs-target="#simple" type="button" role="tab" aria-controls="simple" aria-selected="true">
                            <i class="fas fa-coins me-1"></i> Simplifié
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="detailed-tab" data-bs-toggle="tab" data-bs-target="#detailed" type="button" role="tab" aria-controls="detailed" aria-selected="false">
                            <i class="fas fa-table me-1"></i> Détaillé
                        </button>
                    </li>
                </ul>
                <div class="tab-content" id="gainsTabsContent">
                    <!-- Tableau simplifié -->
                    <div class="tab-pane fade show active" id="simple" role="tabpanel" aria-labelledby="simple-tab">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Numéros trouvés</th>
                                    <th>Gain (mise 8€)</th>
                                    <th>Probabilité</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>7 numéros (7B-0J)</td>
                                    <td>100 000€</td>
                                    <td>1 sur 1 184 040</td>
                                </tr>
                                <tr>
                                    <td>7 numéros (6B-1J)</td>
                                    <td>2 000€</td>
                                    <td>1 sur 33 830</td>
                                </tr>
                                <tr>
                                    <td>7 numéros (5B-2J)</td>
                                    <td>480€</td>
                                    <td>1 sur 5 638</td>
                                </tr>
                                <tr>
                                    <td>7 numéros (4B-3J)</td>
                                    <td>400€</td>
                                    <td>1 sur 3 383</td>
                                </tr>
                                <tr>
                                    <td>6 numéros (6B-0J)</td>
                                    <td>1 000€</td>
                                    <td>1 sur 10 572</td>
                                </tr>
                                <tr>
                                    <td>6 numéros (5B-1J)</td>
                                    <td>220€</td>
                                    <td>1 sur 705</td>
                                </tr>
                                <tr>
                                    <td>6 numéros (4B-2J)</td>
                                    <td>80€</td>
                                    <td>1 sur 211</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Tableau détaillé -->
                    <div class="tab-pane fade" id="detailed" role="tabpanel" aria-labelledby="detailed-tab">
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>Numéros trouvés</th>
                                        <th>Bleus</th>
                                        <th>Jaunes</th>
                                        <th>Probabilité</th>
                                        <th>Gain (2€)</th>
                                        <th>Gain (4€)</th>
                                        <th>Gain (6€)</th>
                                        <th>Gain (8€)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td>7</td><td>7</td><td>0</td><td>1:1 184 040</td><td>25 000€</td><td>50 000€</td><td>75 000€</td><td>100 000€</td></tr>
                                    <tr><td>7</td><td>6</td><td>1</td><td>1:33 830</td><td>500€</td><td>1 000€</td><td>1 500€</td><td>2 000€</td></tr>
                                    <tr><td>7</td><td>5</td><td>2</td><td>1:5 638</td><td>120€</td><td>240€</td><td>360€</td><td>480€</td></tr>
                                    <tr><td>7</td><td>4</td><td>3</td><td>1:3 383</td><td>100€</td><td>200€</td><td>300€</td><td>400€</td></tr>
                                    <tr><td>7</td><td>3</td><td>4</td><td>1:6 766</td><td>80€</td><td>160€</td><td>240€</td><td>320€</td></tr>
                                    <tr><td>7</td><td>2</td><td>5</td><td>1:56 383</td><td>100€</td><td>200€</td><td>300€</td><td>400€</td></tr>
                                    <tr><td>6</td><td>6</td><td>0</td><td>1:10 572</td><td>250€</td><td>500€</td><td>750€</td><td>1 000€</td></tr>
                                    <tr><td>6</td><td>5</td><td>1</td><td>1:705</td><td>55€</td><td>110€</td><td>165€</td><td>220€</td></tr>
                                    <tr><td>6</td><td>4</td><td>2</td><td>1:211</td><td>20€</td><td>40€</td><td>60€</td><td>80€</td></tr>
                                    <tr><td>6</td><td>3</td><td>3</td><td>1:111</td><td>25€</td><td>50€</td><td>75€</td><td>100€</td></tr>
                                    <tr><td>6</td><td>2</td><td>4</td><td>1:705</td><td>15€</td><td>30€</td><td>45€</td><td>60€</td></tr>
                                    <tr><td>6</td><td>1</td><td>5</td><td>1:10 572</td><td>10€</td><td>20€</td><td>30€</td><td>40€</td></tr>
                                    <tr><td>5</td><td>5</td><td>0</td><td>1:470</td><td>50€</td><td>100€</td><td>150€</td><td>200€</td></tr>
                                    <tr><td>5</td><td>4</td><td>1</td><td>1:56</td><td>8€</td><td>16€</td><td>24€</td><td>32€</td></tr>
                                    <tr><td>5</td><td>3</td><td>2</td><td>1:28</td><td>3€</td><td>6€</td><td>9€</td><td>12€</td></tr>
                                </tbody>
                            </table>
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