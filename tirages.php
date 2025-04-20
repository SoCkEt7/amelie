<?php
/**
 * Page de test affichant les 2000 derniers tirages Amigo
 * Permet de vérifier la récupération des données réelles
 */

// Inclure le fichier de démarrage et les classes nécessaires
include 'src/startup.php';
require_once 'src/class/TirageDataFetcher.php';
require_once 'src/class/TirageStrategies.php';

// Inclure l'en-tête
include 'assets/header.php';

// Gestion de la connexion comme sur la page principale
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
    // Utilisateur connecté - Afficher la liste des tirages
    
    // Activer le rapport d'erreurs pour le débogage
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // Nombre de tirages à afficher
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 2000;
    if ($limit <= 0) $limit = 2000;
    if ($limit > 5000) $limit = 5000; // Maximum raisonnable
    
    // Source de données
    $source = isset($_GET['source']) ? $_GET['source'] : 'all';
    
    // Récupérer les données
    $dataFetcher = new TirageDataFetcher();
    
    // Afficher un message de chargement
    echo '<div class="container mt-4">';
    echo '<h1 class="text-center mb-4">Historique des tirages Amigo</h1>';
    echo '<div class="alert alert-info">Chargement des données en cours... Cela peut prendre quelques instants.</div>';
    
    // Vider le buffer pour montrer le message de chargement
    ob_flush();
    flush();
    
    // Récupérer les données selon la source choisie
    $start_time = microtime(true);
    
    if ($source === 'recent') {
        $recentData = $dataFetcher->getRecentTirages();
        $historicalData = []; 
    } else if ($source === 'extended') {
        $historicalData = $dataFetcher->getExtendedHistoricalData();
    } else {
        // Source par défaut = historique standard
        $historicalData = $dataFetcher->getHistoricalTirages($limit);
    }
    
    $end_time = microtime(true);
    $loading_time = round($end_time - $start_time, 2);
    
    // Sauvegarder les données dans un fichier JSON
    $tiragesData = [];
    $dataType = "";
    
    if ($source === 'recent' && isset($recentData['numSortis'])) {
        $tiragesData = $recentData;
        $dataType = "recent";
    } elseif (isset($historicalData['numbers']) && !empty($historicalData['numbers'])) {
        $tiragesData = $historicalData;
        $dataType = $source === 'extended' ? "extended" : "historical";
    }
    
    if (!empty($tiragesData)) {
        $date = date('Y-m-d');
        $hour = date('H');
        $filename = "tirages/{$date}_{$hour}_{$dataType}.json";
        $dir = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($filename, json_encode($tiragesData, JSON_PRETTY_PRINT));
    }
    
    ?>
    
    <div class="container mt-4">
        <h1 class="text-center mb-4">Historique des tirages Amigo</h1>
        
        <!-- Informations sur la requête -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3>Informations</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="alert alert-info">
                            <strong>Temps de chargement:</strong> <?php echo $loading_time; ?> secondes
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="alert alert-info">
                            <strong>Source:</strong> <?php echo isset($historicalData['dataSource']) ? $historicalData['dataSource'] : (isset($recentData['dataSource']) ? $recentData['dataSource'] : 'N/A'); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="alert alert-info">
                            <strong>Authenticité:</strong> 
                            <?php 
                                $isAuthentic = isset($historicalData['isAuthentic']) ? $historicalData['isAuthentic'] : 
                                              (isset($recentData['isAuthentic']) ? $recentData['isAuthentic'] : false);
                                echo $isAuthentic ? 'Données réelles' : 'Données simulées';
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-12">
                        <form action="" method="get" class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="limit">Nombre de tirages:</label>
                                    <select name="limit" id="limit" class="form-control">
                                        <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10 tirages</option>
                                        <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50 tirages</option>
                                        <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100 tirages</option>
                                        <option value="500" <?php echo $limit == 500 ? 'selected' : ''; ?>>500 tirages</option>
                                        <option value="1000" <?php echo $limit == 1000 ? 'selected' : ''; ?>>1000 tirages</option>
                                        <option value="2000" <?php echo $limit == 2000 ? 'selected' : ''; ?>>2000 tirages</option>
                                        <option value="5000" <?php echo $limit == 5000 ? 'selected' : ''; ?>>5000 tirages</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="source">Source:</label>
                                    <select name="source" id="source" class="form-control">
                                        <option value="all" <?php echo $source == 'all' ? 'selected' : ''; ?>>Historique standard</option>
                                        <option value="extended" <?php echo $source == 'extended' ? 'selected' : ''; ?>>Historique étendu</option>
                                        <option value="recent" <?php echo $source == 'recent' ? 'selected' : ''; ?>>Résultats récents</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label><br>
                                    <button type="submit" class="btn btn-primary">Actualiser</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Derniers tirages -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h3>Historique des tirages</h3>
            </div>
            <div class="card-body p-0">
                <?php if ($source === 'recent' && isset($recentData['numSortis'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Numéros Bleus</th>
                                    <th>Numéros Jaunes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php echo isset($recentData['lastUpdated']) ? $recentData['lastUpdated'] : 'N/A'; ?></td>
                                    <td>
                                        <?php if (isset($recentData['numSortis']['blue']) && is_array($recentData['numSortis']['blue'])): ?>
                                            <?php foreach ($recentData['numSortis']['blue'] as $num): ?>
                                                <span class="badge bg-primary"><?php echo $num; ?></span>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (isset($recentData['numSortis']['yellow']) && is_array($recentData['numSortis']['yellow'])): ?>
                                            <?php foreach ($recentData['numSortis']['yellow'] as $num): ?>
                                                <span class="badge bg-warning text-dark"><?php echo $num; ?></span>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php elseif (isset($historicalData['numbers']) && !empty($historicalData['numbers'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Numéros Bleus</th>
                                    <th>Numéros Jaunes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $count = count($historicalData['numbers']);
                                for ($i = 0; $i < min($count, $limit); $i++): 
                                    $tirage = $historicalData['numbers'][$i];
                                ?>
                                    <tr>
                                        <td><?php echo ($i + 1); ?></td>
                                        <td>
                                            <?php if (isset($tirage['blue']) && is_array($tirage['blue'])): ?>
                                                <?php foreach ($tirage['blue'] as $num): ?>
                                                    <span class="badge bg-primary"><?php echo $num; ?></span>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (isset($tirage['yellow']) && is_array($tirage['yellow'])): ?>
                                                <?php foreach ($tirage['yellow'] as $num): ?>
                                                    <span class="badge bg-warning text-dark"><?php echo $num; ?></span>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning m-3">
                        <strong>Aucun tirage disponible.</strong> Vérifiez votre connexion ou essayez une autre source.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Statistiques rapides -->
        <?php if (isset($historicalData['frequency']) && !empty($historicalData['frequency'])): ?>
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h3>Statistiques</h3>
            </div>
            <div class="card-body">
                <h4>Top 10 des numéros les plus fréquents</h4>
                <div class="row">
                    <?php 
                    // Trier par fréquence
                    $frequency = $historicalData['frequency'];
                    arsort($frequency);
                    $topNumbers = array_slice($frequency, 0, 10, true);
                    
                    foreach ($topNumbers as $number => $count): 
                    ?>
                        <div class="col-md-2 col-4 mb-3">
                            <div class="card text-center">
                                <div class="card-body p-2">
                                    <h5 class="card-title mb-0">
                                        <span class="badge bg-primary" style="font-size: 1.2rem;"><?php echo $number; ?></span>
                                    </h5>
                                    <p class="card-text"><?php echo $count; ?> fois</p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
<?php 
}

// Inclure le pied de page
include 'assets/footer.php';
?>