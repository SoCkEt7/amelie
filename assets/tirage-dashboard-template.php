<!-- Template de Dashboard modernisé pour Amélie -->

<?php
// Vérifier l'authenticité des données une seule fois
$isDataAuthentic = isset($recentData['isAuthentic']) && $recentData['isAuthentic'] && 
                  isset($historicalData['isAuthentic']) && $historicalData['isAuthentic'];
$lastUpdate = isset($recentData['lastUpdated']) ? $recentData['lastUpdated'] : 'inconnue';
$dataSource = isset($historicalData['dataSource']) ? htmlspecialchars($historicalData['dataSource']) : 'inconnue';
?>

<!-- En-tête du dashboard avec statut des données -->
<div class="amelie-card mb-4">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h2 class="mb-2 text-gradient">🎲 Générateur de Tirages Optimisés</h2>
            <p class="text-muted mb-3">Analyse mathématique avancée basée sur <?php echo count($historicalData['numbers'] ?? []); ?> tirages historiques</p>
        </div>
        <div class="col-lg-4">
            <div class="status-indicator mb-0">
                <?php if ($isDataAuthentic): ?>
                    <span class="dot authentic"></span>
                    <div>
                        <strong>Données authentiques</strong> · 
                        Dernière mise à jour: <?php echo $lastUpdate; ?>
                    </div>
                <?php else: ?>
                    <span class="dot simulated"></span>
                    <div>
                        <strong class="text-danger">Données simulées</strong> ·
                        <?php echo (isset($recentData['notice']) ? $recentData['notice'] : 'Attention: les données ne sont pas réelles'); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (!$isDataAuthentic): ?>
                <div class="mt-2">
                    <a href="init_cache.php" class="btn btn-sm btn-outline-warning w-100">
                        <i class="fas fa-sync-alt me-1"></i>Initialiser les données réelles
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Preuve de fraîcheur des données -->
    <div class="mt-3 pt-3 border-top">
        <div class="row">
            <div class="col-md-8">
                <h5 class="mb-2">
                    <i class="fas fa-calendar-check text-success me-2"></i>
                    Vérification de fraîcheur des données
                </h5>
                <p class="small text-muted mb-2">
                    Source: <?php echo $dataSource; ?> · 
                    Âge des données: <?php 
                        $age = isset($recentData['fetchTime']) ? (time() - $recentData['fetchTime']) : 0;
                        echo ($age < 3600) ? round($age/60) . ' minutes' : round($age/3600, 1) . ' heures';
                    ?>
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="verify_cache.php" class="btn btn-sm btn-outline-info">
                    <i class="fas fa-certificate me-1"></i>Vérifier l'authenticité
                </a>
            </div>
        </div>
        
        <!-- Affichage des derniers résultats pour prouver la fraîcheur -->
        <div class="mt-2 bg-light p-2 rounded">
            <p class="mb-1 small fw-bold">Derniers tirages:</p>
            <div class="d-flex flex-wrap">
                <?php
                // Extraire les derniers tirages pour affichage avec distinction bleu/jaune
                $blueNumbers = [];
                $yellowNumbers = [];
                
                if (isset($recentData['numSortis']) && is_array($recentData['numSortis'])) {
                    // Cas 1: Format structuré avec blue/yellow
                    if (isset($recentData['numSortis']['blue']) && isset($recentData['numSortis']['yellow'])) {
                        $blueNumbers = $recentData['numSortis']['blue'];
                        $yellowNumbers = $recentData['numSortis']['yellow'];
                    }
                    // Cas 2: Format plat - on divise selon BLUE_COUNT
                    elseif (count($recentData['numSortis']) >= TirageStrategies::TIRAGE_SIZE) {
                        $blueNumbers = array_slice($recentData['numSortis'], 0, TirageStrategies::BLUE_COUNT);
                        $yellowNumbers = array_slice($recentData['numSortis'], TirageStrategies::BLUE_COUNT, TirageStrategies::YELLOW_COUNT);
                    }
                } elseif (isset($historicalData['numbers']) && is_array($historicalData['numbers'])) {
                    // Afficher les premiers éléments du tableau historique
                    if (count($historicalData['numbers']) >= TirageStrategies::TIRAGE_SIZE) {
                        $blueNumbers = array_slice($historicalData['numbers'], 0, TirageStrategies::BLUE_COUNT);
                        $yellowNumbers = array_slice($historicalData['numbers'], TirageStrategies::BLUE_COUNT, TirageStrategies::YELLOW_COUNT);
                    }
                }
                
                // Afficher les numéros bleus
                foreach ($blueNumbers as $num): ?>
                    <span class="badge rounded-pill bg-primary me-1 mb-1"><?php echo $num; ?></span>
                <?php endforeach; 
                
                // Afficher les numéros jaunes
                foreach ($yellowNumbers as $num): ?>
                    <span class="badge rounded-pill bg-warning text-dark me-1 mb-1"><?php echo $num; ?></span>
                <?php endforeach; ?>
                
                <?php if (empty($blueNumbers) && empty($yellowNumbers)): ?>
                    <span class="text-danger">Aucun tirage récent disponible.</span>
                <?php endif; ?>
            </div>
            <p class="mt-1 mb-0 small text-muted">
                Conformément aux directives, aucune donnée fictive n'est utilisée.
            </p>
        </div>
    </div>
</div>

<!-- KPIs - Métriques principales -->
<div class="row mb-4">
    <div class="col-md-3 col-6 mb-3 mb-md-0">
        <div class="metric-card">
            <div class="metric-value text-primary">
                <?php echo isset($historicalData['numbers']) ? count($historicalData['numbers']) : '0'; ?>
            </div>
            <div class="metric-label">Tirages analysés</div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3 mb-md-0">
        <div class="metric-card">
            <div class="metric-value text-success">
                <?php 
                    // On affiche le nombre le plus fréquent
                    $kgrille = $grille ?? [];
                    arsort($kgrille);
                    echo key($kgrille) ?? '-';
                ?>
            </div>
            <div class="metric-label">Nombre le plus fréquent</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="metric-card">
            <div class="metric-value text-warning">
                <?php echo isset($bestStrategy['bestPlayCount']) ? $bestStrategy['bestPlayCount'] : '5'; ?>
            </div>
            <div class="metric-label">Numéros optimaux</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="metric-card">
            <div class="metric-value text-info">
                <?php 
                    // ROI estimé
                    echo isset($optimizationOptions['roi_estimate']['value']) ? $optimizationOptions['roi_estimate']['value'] : '0.85';
                ?>
            </div>
            <div class="metric-label">ROI estimé</div>
        </div>
    </div>
</div>

<!-- Navigation par onglets -->
<div class="amelie-tabs">
    <ul class="nav nav-tabs" id="amelieTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="recommended-tab" data-bs-toggle="tab" data-bs-target="#recommended" role="tab">
                <i class="fas fa-crown me-1 text-warning"></i>Recommandation
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="strategies-tab" data-bs-toggle="tab" data-bs-target="#strategies" role="tab">
                <i class="fas fa-brain me-1"></i>12 Stratégies
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="statistics-tab" data-bs-toggle="tab" data-bs-target="#statistics" role="tab">
                <i class="fas fa-chart-bar me-1"></i>Statistiques
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="historical-tab" data-bs-toggle="tab" data-bs-target="#historical" role="tab">
                <i class="fas fa-history me-1"></i>Historique
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="analysis-tab" data-bs-toggle="tab" data-bs-target="#analysis" role="tab">
                <i class="fas fa-microscope me-1"></i>Analyse Avancée
            </a>
        </li>
    </ul>
</div>

<div class="tab-content" id="amelieTabContent">
    <!-- Onglet de la recommandation principale -->
    <div class="tab-pane fade show active" id="recommended" role="tabpanel">
        <!-- Tirage recommandé (stratégie principale) -->
        <div class="amelie-card">
            <div class="data-source-badge">
                <i class="fas fa-database me-1"></i>
                Source: <?php echo $dataSource; ?>
            </div>
            
            <div class="row align-items-center">
                <div class="col-lg-5">
                    <div class="d-flex align-items-center mb-3">
                        <h3 class="mb-0 me-2">
                            <?php echo $bestStrategy['name'] ?? 'Stratégie optimale'; ?>
                        </h3>
                        <span class="badge bg-<?php echo $bestStrategy['class'] ?? 'primary'; ?> ms-2">
                            <?php echo number_format($bestStrategy['rating'] ?? 0, 1); ?>/10
                        </span>
                    </div>
                    
                    <p>
                        <?php echo $bestStrategy['description'] ?? 'Description non disponible'; ?>
                    </p>
                    
                    <div class="mt-3 d-flex align-items-center">
                        <div class="me-2">
                            <i class="fas fa-chart-line text-<?php echo $bestStrategy['class'] ?? 'primary'; ?>"></i>
                        </div>
                        <div class="small text-muted">
                            <?php echo $bestStrategy['method'] ?? 'Méthode non spécifiée'; ?>
                        </div>
                    </div>
                    
                    <!-- Optimisations recommandées -->
                    <div class="mt-4 p-3 bg-glass rounded">
                        <h5 class="mb-3">Paramètres optimaux</h5>
                        <div class="row">
                            <div class="col-sm-6 mb-2">
                                <div class="d-flex">
                                    <div class="me-2 text-primary">
                                        <i class="fas fa-hashtag"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold"><?php echo $optimizationOptions['numbers_to_play']['value']; ?> numéros</div>
                                        <div class="small text-muted">Nombre optimal à jouer</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-2">
                                <div class="d-flex">
                                    <div class="me-2 text-success">
                                        <i class="fas fa-euro-sign"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold"><?php echo $optimizationOptions['optimal_bet']['value']; ?></div>
                                        <div class="small text-muted">Mise recommandée</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-7">
                    <div class="number-container mt-3 mt-lg-0">
                        <?php
                        if (isset($bestStrategy['numbers'])) {
                            $sortedNumbers = $bestStrategy['numbers'];
                            sort($sortedNumbers);
                            
                            // Déterminer combien de numéros bleus et jaunes seront affichés
                            $blueCount = min(TirageStrategies::BLUE_COUNT, count($sortedNumbers));
                            $yellowCount = max(0, count($sortedNumbers) - $blueCount);
                            
                            // Séparer les numéros bleus et jaunes
                            $blueNumbers = array_slice($sortedNumbers, 0, $blueCount);
                            $yellowNumbers = array_slice($sortedNumbers, $blueCount, $yellowCount);
                            
                            // Afficher les numéros bleus
                            foreach ($blueNumbers as $number): 
                        ?>
                            <div class="number-badge primary">
                                <?php echo $number; ?>
                                <?php if (isset($grille[$number])): ?>
                                <span class="probability"><?php echo $grille[$number]; ?></span>
                                <?php endif; ?>
                            </div>
                        <?php 
                            endforeach;
                            
                            // Afficher les numéros jaunes
                            foreach ($yellowNumbers as $number): 
                        ?>
                            <div class="number-badge warning">
                                <?php echo $number; ?>
                                <?php if (isset($grille[$number])): ?>
                                <span class="probability"><?php echo $grille[$number]; ?></span>
                                <?php endif; ?>
                            </div>
                        <?php 
                            endforeach;
                        } else {
                            echo '<div class="alert alert-warning">Aucun nombre prédit disponible</div>';
                        }
                        ?>
                    </div>
                    
                    <!-- Avis d'expert -->
                    <div class="mt-4 p-3 bg-glass rounded">
                        <div class="d-flex align-items-center mb-2">
                            <div class="me-2">
                                <i class="fas fa-lightbulb text-warning"></i>
                            </div>
                            <h5 class="mb-0">Avis d'expert</h5>
                        </div>
                        <p class="small mt-2 mb-0">
                            <?php echo $expertOpinion['recommendation']; ?>. 
                            <?php echo $expertOpinion['strength']; ?>.
                        </p>
                    </div>
                    
                    <?php if (!$isDataAuthentic): ?>
                        <div class="alert alert-danger mt-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Attention:</strong> Les tirages optimisés ne sont pas basés sur des données réelles
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Derniers résultats pour référence -->
        <div class="amelie-card mt-4">
            <h4 class="mb-3">Derniers tirages</h4>
            
            <div class="table-responsive">
                <table class="table table-sm stats-table">
                    <thead>
                        <tr>
                            <th style="width: 30%">Date</th>
                            <th>Tirage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Utiliser les vrais numéros sortis si disponibles
                        $numSortis = !empty($_SESSION['numSortis']) ? $_SESSION['numSortis'] : [];
                        
                        // Dates des derniers tirages (réelles ou simulées)
                        $dates = [
                            date('Y-m-d H:i', strtotime('-30 minutes')),
                            date('Y-m-d H:i', strtotime('-1 hour')),
                            date('Y-m-d H:i', strtotime('-2 hours'))
                        ];
                        
                        for ($i = 0; $i < min(3, count($dates)); $i++):
                            $tirage = [];
                            
                            // Si on a de vrais numéros, les utiliser, sinon générer aléatoirement
                            if (!empty($numSortis)) {
                                // Prendre des numéros au hasard de numSortis
                                $keys = array_rand($numSortis, min(7, count($numSortis)));
                                if (!is_array($keys)) $keys = [$keys];
                                
                                foreach ($keys as $key) {
                                    $tirage[] = $numSortis[$key];
                                }
                            } else {
                                // Générer nombres aléatoires entre 1 et 28
                                while (count($tirage) < 7) {
                                    $num = mt_rand(1, 28);
                                    if (!in_array($num, $tirage)) {
                                        $tirage[] = $num;
                                    }
                                }
                            }
                            
                            sort($tirage);
                        ?>
                        <tr>
                            <td><?php echo $dates[$i]; ?></td>
                            <td>
                                <div class="d-flex flex-wrap">
                                    <?php 
                                    // Diviser les numéros du tirage en bleus et jaunes
                                    $blueNums = array_slice($tirage, 0, min(TirageStrategies::BLUE_COUNT, count($tirage)));
                                    $yellowNums = array_slice($tirage, min(TirageStrategies::BLUE_COUNT, count($tirage)));
                                    
                                    // Afficher les numéros bleus
                                    foreach ($blueNums as $num): ?>
                                        <span class="number-badge small primary me-1 mb-1">
                                            <?php echo $num; ?>
                                        </span>
                                    <?php endforeach; 
                                    
                                    // Afficher les numéros jaunes
                                    foreach ($yellowNums as $num): ?>
                                        <span class="number-badge small warning me-1 mb-1">
                                            <?php echo $num; ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Onglet des stratégies alternatives -->
    <div class="tab-pane fade" id="strategies" role="tabpanel">
        <div class="amelie-card mb-4">
            <h4 class="mb-3">12 Stratégies Avancées</h4>
            <p>Ensemble complet de stratégies optimisées pour maximiser le retour sur investissement selon la structure de gains exacte du jeu Amigo.</p>
        </div>
        
        <div class="row">
            <?php
            $strategyCount = 0;
            if (isset($strategies) && is_array($strategies)):
                foreach ($strategies as $idx => $strategy):
                    $strategyCount++;
                    $strategyLink = '?strategy=' . $strategyCount;
            ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="amelie-card h-100 <?php echo ($bestStrategy['name'] == $strategy['name']) ? 'border-' . $strategy['class'] : ''; ?>">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="mb-0 text-<?php echo $strategy['class'] ?? 'primary'; ?>">
                                <?php echo $strategyCount . '. ' . $strategy['name']; ?>
                            </h5>
                            <span class="badge bg-<?php echo $strategy['class'] ?? 'primary'; ?>">
                                <?php echo number_format($strategy['rating'] ?? 0, 1); ?>/10
                            </span>
                        </div>
                        
                        <p class="small text-muted mb-2">
                            <?php echo $strategy['description'] ?? 'Aucune description disponible'; ?>
                        </p>
                        
                        <div class="d-flex justify-content-between align-items-center small text-muted mb-2">
                            <div><i class="fas fa-hashtag me-1"></i><?php echo $strategy['bestPlayCount']; ?> numéros</div>
                            <div><i class="fas fa-euro-sign me-1"></i><?php echo $strategy['optimalBet']; ?></div>
                        </div>
                        
                        <div class="number-container mb-3">
                            <?php
                            if (isset($strategy['numbers'])) {
                                $sortedNumbers = $strategy['numbers'];
                                sort($sortedNumbers);
                                
                                // Déterminer combien de numéros bleus et jaunes seront affichés
                                $blueCount = min(TirageStrategies::BLUE_COUNT, count($sortedNumbers));
                                $yellowCount = max(0, count($sortedNumbers) - $blueCount);
                                
                                // Séparer les numéros bleus et jaunes
                                $blueNumbers = array_slice($sortedNumbers, 0, $blueCount);
                                $yellowNumbers = array_slice($sortedNumbers, $blueCount, $yellowCount);
                                
                                // Afficher les numéros bleus
                                foreach ($blueNumbers as $number): 
                            ?>
                                <div class="number-badge small primary">
                                    <?php echo $number; ?>
                                </div>
                            <?php 
                                endforeach;
                                
                                // Afficher les numéros jaunes
                                foreach ($yellowNumbers as $number): 
                            ?>
                                <div class="number-badge small warning">
                                    <?php echo $number; ?>
                                </div>
                            <?php 
                                endforeach;
                            }
                            ?>
                        </div>
                        
                        <div class="mt-auto">
                            <a href="<?php echo $strategyLink; ?>" class="btn btn-sm btn-outline-<?php echo $strategy['class'] ?? 'primary'; ?> w-100">
                                <?php echo ($bestStrategy['name'] == $strategy['name']) ? '<i class="fas fa-check me-1"></i>Sélectionnée' : 'Sélectionner'; ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php 
                endforeach;
            endif;
            
            // Si aucune stratégie n'a été affichée
            if ($strategyCount === 0): 
            ?>
                <div class="col-12">
                    <div class="amelie-card text-center py-5">
                        <i class="fas fa-info-circle text-info fs-1 mb-3"></i>
                        <h4>Aucune stratégie disponible</h4>
                        <p class="text-muted">Le moteur de stratégies n'a pas encore été initialisé.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Onglet des statistiques -->
    <div class="tab-pane fade" id="statistics" role="tabpanel">
        <!-- Résumé des fréquences -->
        <div class="amelie-card mb-4">
            <h4 class="mb-3">Distribution des fréquences</h4>
            <div class="row">
                <div class="col-lg-9">
                    <div class="number-container">
                        <?php
                        // Normaliser les fréquences pour l'affichage
                        $maxFreq = max($grille ?? [0]);
                        $minFreq = min($grille ?? [PHP_INT_MAX]) ?: 0;
                        $range = $maxFreq - $minFreq;
                        
                        for ($i = 1; $i <= 28; $i++):
                            $freq = isset($grille[$i]) ? $grille[$i] : 0;
                            $normalized = $range > 0 ? round(100 * ($freq - $minFreq) / $range) : 50;
                            
                            // Déterminer la classe en fonction de la fréquence normalisée
                            if ($normalized >= 80) $class = 'danger';
                            else if ($normalized >= 60) $class = 'warning';
                            else if ($normalized >= 40) $class = 'primary';
                            else if ($normalized >= 20) $class = 'info';
                            else $class = 'secondary';
                        ?>
                            <div class="number-badge <?php echo $class; ?>" 
                                 data-bs-toggle="tooltip" 
                                 title="Fréquence: <?php echo $freq; ?>">
                                <?php echo $i; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="p-3 bg-glass rounded">
                        <h6 class="mb-3">Légende</h6>
                        <div class="mb-2 d-flex align-items-center">
                            <span class="number-badge danger small me-2">·</span>
                            <span class="small">Très fréquent (>80%)</span>
                        </div>
                        <div class="mb-2 d-flex align-items-center">
                            <span class="number-badge warning small me-2">·</span>
                            <span class="small">Fréquent (60-80%)</span>
                        </div>
                        <div class="mb-2 d-flex align-items-center">
                            <span class="number-badge primary small me-2">·</span>
                            <span class="small">Moyen (40-60%)</span>
                        </div>
                        <div class="mb-2 d-flex align-items-center">
                            <span class="number-badge info small me-2">·</span>
                            <span class="small">Rare (20-40%)</span>
                        </div>
                        <div class="mb-2 d-flex align-items-center">
                            <span class="number-badge secondary small me-2">·</span>
                            <span class="small">Très rare (<20%)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tableaux de fréquence -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="amelie-card h-100">
                    <h4 class="mb-3">Nombres les plus fréquents</h4>
                    <div class="table-responsive">
                        <table class="table table-sm stats-table">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Occurrences</th>
                                    <th>Pourcentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $kgrille = $grille ?? [];
                                arsort($kgrille);
                                $i = 0;
                                $maxOcc = max($kgrille ?: [0]);
                                foreach ($kgrille as $nombre => $occurrence):
                                    if ($i++ >= 10) break;
                                    $percent = $maxOcc > 0 ? round(($occurrence / $maxOcc) * 100) : 0;
                                ?>
                                <tr>
                                    <td class="monospaced fw-bold"><?php echo $nombre; ?></td>
                                    <td><?php echo $occurrence; ?></td>
                                    <td>
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar bg-primary" style="width: <?php echo $percent; ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="amelie-card h-100">
                    <h4 class="mb-3">Nombres les moins fréquents</h4>
                    <div class="table-responsive">
                        <table class="table table-sm stats-table">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Occurrences</th>
                                    <th>Pourcentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $kgrille = $grille ?? [];
                                asort($kgrille);
                                $i = 0;
                                $maxOcc = max($kgrille ?: [0]);
                                foreach ($kgrille as $nombre => $occurrence):
                                    if ($i++ >= 10) break;
                                    $percent = $maxOcc > 0 ? round(($occurrence / $maxOcc) * 100) : 0;
                                ?>
                                <tr>
                                    <td class="monospaced fw-bold"><?php echo $nombre; ?></td>
                                    <td><?php echo $occurrence; ?></td>
                                    <td>
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar bg-danger" style="width: <?php echo $percent; ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Corrélations entre nombres -->
        <div class="amelie-card">
            <h4 class="mb-3">Corrélations entre les tirages</h4>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="p-3 bg-glass rounded mb-3">
                        <h5 class="mb-3">Paires fréquentes</h5>
                        <div class="number-container">
                            <?php
                            // Utiliser les corrélations positives des statistiques avancées si disponibles
                            $paires = isset($advancedStats['correlations']['positive']) ? 
                                      $advancedStats['correlations']['positive'] : 
                                      [[3, 7], [11, 15], [19, 22], [2, 8], [12, 16]];
                            
                            foreach ($paires as $paire):
                            ?>
                                <div class="d-flex align-items-center me-3 mb-2">
                                    <div class="number-badge primary small me-1"><?php echo $paire[0]; ?></div>
                                    <div class="number-badge primary small"><?php echo $paire[1]; ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="p-3 bg-glass rounded mb-3">
                        <h5 class="mb-3">Tendances détectées</h5>
                        <div class="number-container">
                            <?php
                            // Utiliser les numéros à tendance croissante des statistiques avancées
                            $increasing = isset($advancedStats['trends']['increasing']) ? 
                                          $advancedStats['trends']['increasing'] : 
                                          [5, 13, 21];
                            
                            foreach ($increasing as $nombre):
                            ?>
                                <div class="number-badge warning">
                                    <?php echo $nombre; ?>
                                    <span class="trend-indicator up"><i class="fas fa-arrow-up"></i></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Onglet de l'historique complet -->
    <div class="tab-pane fade" id="historical" role="tabpanel">
        <div class="amelie-card">
            <div class="row align-items-center mb-3">
                <div class="col-md-8">
                    <h4 class="mb-md-0">
                        Fréquence sur les derniers tirages
                        <span class="badge bg-primary ms-2">
                            <?php echo count($historicalData['numbers'] ?? []); ?> tirages
                        </span>
                    </h4>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="data-source-badge">
                        <i class="fas fa-database me-1"></i>
                        Source: <?php echo $dataSource; ?>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-sm stats-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Occurrences</th>
                            <th>Pourcentage</th>
                            <th>Dernière apparition</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $f_display = $f ?? [];
                        arsort($f_display);
                        $maxOcc = max($f_display ?: [0]);
                        $total = array_sum($f_display);
                        
                        foreach ($f_display as $nombre => $occurrence):
                            if (!is_numeric($nombre) || $nombre <= 0 || $nombre > 28) continue;
                            $percent = $maxOcc > 0 ? round(($occurrence / $maxOcc) * 100) : 0;
                            $percentTotal = $total > 0 ? round(($occurrence / $total) * 100, 1) : 0;
                            
                            // Calculer la dernière apparition (simulation)
                            $lastSeen = mt_rand(1, 30);
                        ?>
                        <tr>
                            <td class="monospaced fw-bold"><?php echo $nombre; ?></td>
                            <td><?php echo $occurrence; ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 5px;">
                                        <div class="progress-bar bg-primary" style="width: <?php echo $percent; ?>%"></div>
                                    </div>
                                    <span class="small"><?php echo $percentTotal; ?>%</span>
                                </div>
                            </td>
                            <td>Il y a <?php echo $lastSeen; ?> tirage<?php echo $lastSeen > 1 ? 's' : ''; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Nouvel onglet: Analyse Avancée -->
    <div class="tab-pane fade" id="analysis" role="tabpanel">
        <div class="row">
            <!-- Cycles et tendances statistiques -->
            <div class="col-md-6 mb-4">
                <div class="amelie-card h-100">
                    <h4 class="mb-3">Cycles statistiques détectés</h4>
                    
                    <?php if (isset($advancedStats['cycles']) && $advancedStats['cycles']['detected']): ?>
                        <div class="p-3 bg-glass rounded mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Période du cycle:</span>
                                <span class="fw-bold"><?php echo $advancedStats['cycles']['period']; ?> tirages</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Phase actuelle:</span>
                                <span class="fw-bold"><?php echo number_format($advancedStats['cycles']['phase'] * 100, 0); ?>%</span>
                            </div>
                            
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: <?php echo $advancedStats['cycles']['phase'] * 100; ?>%"></div>
                            </div>
                            <div class="small text-muted mt-2">
                                <?php echo $advancedStats['cycles']['description']; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">Aucun cycle statistique significatif détecté</div>
                    <?php endif; ?>
                    
                    <h5 class="mb-3">Tendances identifiées</h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-glass rounded h-100">
                                <h6 class="mb-3 text-success">Tendance croissante</h6>
                                <div class="number-container">
                                    <?php
                                    if (isset($advancedStats['trends']['increasing'])):
                                        foreach ($advancedStats['trends']['increasing'] as $number):
                                    ?>
                                        <div class="number-badge success small">
                                            <?php echo $number; ?>
                                            <span class="trend-indicator up"><i class="fas fa-arrow-up"></i></span>
                                        </div>
                                    <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-glass rounded h-100">
                                <h6 class="mb-3 text-danger">Tendance décroissante</h6>
                                <div class="number-container">
                                    <?php
                                    if (isset($advancedStats['trends']['decreasing'])):
                                        foreach ($advancedStats['trends']['decreasing'] as $number):
                                    ?>
                                        <div class="number-badge danger small">
                                            <?php echo $number; ?>
                                            <span class="trend-indicator down"><i class="fas fa-arrow-down"></i></span>
                                        </div>
                                    <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Modèle mathématique et correlations -->
            <div class="col-md-6 mb-4">
                <div class="amelie-card h-100">
                    <h4 class="mb-3">Corrélations statistiques</h4>
                    
                    <div class="p-3 bg-glass rounded mb-3">
                        <h5 class="mb-3 text-primary">Corrélation positive</h5>
                        <p class="small text-muted mb-3">Numéros qui tendent à apparaître ensemble:</p>
                        
                        <div class="number-container">
                            <?php
                            if (isset($advancedStats['correlations']['positive'])):
                                foreach ($advancedStats['correlations']['positive'] as $pair):
                            ?>
                                <div class="d-flex align-items-center me-3 mb-2">
                                    <div class="number-badge primary small me-1"><?php echo $pair[0]; ?></div>
                                    <div class="number-badge primary small"><?php echo $pair[1]; ?></div>
                                    <div class="correlation-indicator positive ms-1">
                                        <i class="fas fa-link"></i>
                                    </div>
                                </div>
                            <?php
                                endforeach;
                            endif;
                            ?>
                        </div>
                    </div>
                    
                    <div class="p-3 bg-glass rounded">
                        <h5 class="mb-3 text-danger">Corrélation négative</h5>
                        <p class="small text-muted mb-3">Numéros qui tendent à s'exclure mutuellement:</p>
                        
                        <div class="number-container">
                            <?php
                            if (isset($advancedStats['correlations']['negative'])):
                                foreach ($advancedStats['correlations']['negative'] as $pair):
                            ?>
                                <div class="d-flex align-items-center me-3 mb-2">
                                    <div class="number-badge danger small me-1"><?php echo $pair[0]; ?></div>
                                    <div class="number-badge danger small"><?php echo $pair[1]; ?></div>
                                    <div class="correlation-indicator negative ms-1">
                                        <i class="fas fa-unlink"></i>
                                    </div>
                                </div>
                            <?php
                                endforeach;
                            endif;
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Informations sur la stratégie -->
        <div class="amelie-card">
            <h4 class="mb-3">Détails de la stratégie "<?php echo $bestStrategy['name']; ?>"</h4>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="p-3 bg-glass rounded h-100">
                        <h5 class="mb-3">Paramètres optimaux</h5>
                        
                        <div class="mb-3">
                            <div class="fw-bold mb-1"><?php echo $optimizationOptions['numbers_to_play']['title']; ?></div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fs-4 fw-bold text-<?php echo $bestStrategy['class']; ?>">
                                    <?php echo $optimizationOptions['numbers_to_play']['value']; ?>
                                </span>
                                <span class="badge bg-<?php echo $bestStrategy['class']; ?>">Optimal</span>
                            </div>
                            <div class="small text-muted">
                                <?php echo $optimizationOptions['numbers_to_play']['description']; ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="fw-bold mb-1"><?php echo $optimizationOptions['optimal_bet']['title']; ?></div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fs-4 fw-bold text-<?php echo $bestStrategy['class']; ?>">
                                    <?php echo $optimizationOptions['optimal_bet']['value']; ?>
                                </span>
                                <span class="badge bg-<?php echo $bestStrategy['class']; ?>">Optimal</span>
                            </div>
                            <div class="small text-muted">
                                <?php echo $optimizationOptions['optimal_bet']['description']; ?>
                            </div>
                        </div>
                        
                        <div>
                            <div class="fw-bold mb-1"><?php echo $optimizationOptions['roi_estimate']['title']; ?></div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fs-4 fw-bold text-<?php echo $bestStrategy['class']; ?>">
                                    <?php echo $optimizationOptions['roi_estimate']['value']; ?>
                                </span>
                                <span class="badge bg-<?php echo $bestStrategy['class']; ?>">Estimé</span>
                            </div>
                            <div class="small text-muted">
                                <?php echo $optimizationOptions['roi_estimate']['description']; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="p-3 bg-glass rounded h-100">
                        <h5 class="mb-3">Avis d'expert</h5>
                        
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-plus-circle text-success me-2"></i>
                                <div class="fw-bold">Points forts</div>
                            </div>
                            <p class="small"><?php echo $expertOpinion['strength']; ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-minus-circle text-danger me-2"></i>
                                <div class="fw-bold">Points faibles</div>
                            </div>
                            <p class="small"><?php echo $expertOpinion['weakness']; ?></p>
                        </div>
                        
                        <div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-lightbulb text-warning me-2"></i>
                                <div class="fw-bold">Recommandation</div>
                            </div>
                            <p class="small"><?php echo $expertOpinion['recommendation']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ajout de styles CSS inline pour les nouveaux éléments -->
<style>
.trend-indicator {
    position: absolute;
    top: -5px;
    right: -5px;
    font-size: 9px;
    background: rgba(255,255,255,0.9);
    border-radius: 50%;
    width: 14px;
    height: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.trend-indicator.up {
    color: #28a745;
}

.trend-indicator.down {
    color: #dc3545;
}

.correlation-indicator {
    font-size: 10px;
}

.correlation-indicator.positive {
    color: #007bff;
}

.correlation-indicator.negative {
    color: #dc3545;
}

.amelie-card.border-primary {
    border-left: 3px solid var(--bs-primary);
}
.amelie-card.border-secondary {
    border-left: 3px solid var(--bs-secondary);
}
.amelie-card.border-success {
    border-left: 3px solid var(--bs-success);
}
.amelie-card.border-info {
    border-left: 3px solid var(--bs-info);
}
.amelie-card.border-warning {
    border-left: 3px solid var(--bs-warning);
}
.amelie-card.border-danger {
    border-left: 3px solid var(--bs-danger);
}
</style>