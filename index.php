<?php
/**
 * Amelie - Version simplifiée
 * Application pour analyser les résultats du jeu Amigo et générer des recommandations
 */

// Inclure le fichier de démarrage
include 'src/startup.php';

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
    
    // Récupérer les données directement (sans cache)
    $dataFetcher = new TirageDataFetcher();
    $recentData = $dataFetcher->getRecentTirages();
    $historicalData = $dataFetcher->getHistoricalTirages(1000); // Limiter à 1000 tirages
    
    // Générer les stratégies basées sur les données récupérées
    $strategiesCalculator = new TirageStrategies($historicalData, $recentData);
    $strategies = $strategiesCalculator->getStrategies();
    
    ?>
    
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
                    <div class="row">
                        <?php foreach ($strategies as $index => $strategy): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card h-100 border-<?php echo $strategy['class']; ?>">
                                    <div class="card-header bg-<?php echo $strategy['class']; ?> text-white">
                                        <h4><?php echo $strategy['name']; ?> 
                                            <span class="badge bg-light text-dark float-end">
                                                Score: <?php echo $strategy['rating']; ?>/10
                                            </span>
                                        </h4>
                                    </div>
                                    <div class="card-body">
                                        <p><?php echo $strategy['description']; ?></p>
                                        <h5>Numéros recommandés:</h5>
                                        <div class="d-flex flex-wrap">
                                            <?php foreach ($strategy['numbers'] as $num): ?>
                                                <div class="number-badge <?php echo $strategy['class']; ?>"><?php echo $num; ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="mt-3">
                                            <p><strong>Méthode:</strong> <?php echo $strategy['method']; ?></p>
                                            <p><strong>Nombre optimal:</strong> <?php echo $strategy['bestPlayCount']; ?> numéros</p>
                                            <p><strong>Mise recommandée:</strong> <?php echo $strategy['optimalBet']; ?></p>
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
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Numéros trouvés</th>
                            <th>Gain</th>
                            <th>Probabilité</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>7 numéros</td>
                            <td>10 000€</td>
                            <td>0.00001%</td>
                        </tr>
                        <tr>
                            <td>6 numéros</td>
                            <td>1 000€</td>
                            <td>0.0012%</td>
                        </tr>
                        <tr>
                            <td>5 numéros</td>
                            <td>100€</td>
                            <td>0.11%</td>
                        </tr>
                        <tr>
                            <td>4 numéros</td>
                            <td>10€</td>
                            <td>2.53%</td>
                        </tr>
                        <tr>
                            <td>3 numéros</td>
                            <td>2€</td>
                            <td>21.85%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php 
}

// Inclure le pied de page
include 'assets/footer.php';
?>