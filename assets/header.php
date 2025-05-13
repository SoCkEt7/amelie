<?php
include('src/startup.php'); ?>
<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Amélie - Générateur de Tirages Optimisés</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
          integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/app.css">
    <link rel="icon" href="./assets/images/favicon.png" type="image/x-icon">
</head>
<body data-bs-theme="dark">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php if (isset($cacheWarning)): ?>
<div class="alert alert-warning m-0 border-0 rounded-0">
    <div class="container d-flex align-items-center">
        <i class="fas fa-exclamation-triangle me-2"></i> 
        <div><?php echo $cacheWarning; ?></div>
    </div>
</div>
<?php endif; ?>

<header class="app-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <a href="index.php" class="text-decoration-none">
                <div class="d-flex align-items-center">
                    <div class="me-3 d-flex align-items-center justify-content-center bg-primary bg-opacity-25 rounded-circle" style="width: 42px; height: 42px;">
                        <i class="fas fa-dice fs-3 text-primary"></i>
                    </div>
                    <div>
                        <h1 class="mb-0 fs-4">Amélie</h1>
                        <div class="d-none d-md-block text-muted small">Générateur de tirages optimisés</div>
                    </div>
                </div>
            </a>
            
            <?php if (isset($_SESSION['connected'])): ?>
            <div class="d-flex align-items-center">
                <div class="d-none d-md-flex me-3">
                    <a href="index.php" class="btn btn-sm btn-outline-primary me-1">
                        <i class="fas fa-home me-1"></i>Accueil
                    </a>
                    <a href="daily.php" class="btn btn-sm btn-outline-danger me-1">
                        <i class="fas fa-calendar-day me-1"></i>Stratégies du jour
                    </a>
                    <a href="ai.php" class="btn btn-sm btn-outline-info me-1">
                        <i class="fas fa-robot me-1"></i>Stratégies IA
                    </a>
                    <a href="tirages.php" class="btn btn-sm btn-outline-success me-1">
                        <i class="fas fa-history me-1"></i>Historique
                    </a>
                    <a href="keno.php" class="btn btn-sm btn-outline-warning me-1">
                        <i class="fas fa-dice me-1"></i>Keno
                    </a>
                    <button class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="modal" data-bs-target="#safeModal" title="Top 3 Safe">
                        <i class="fas fa-shield-alt"></i>
                    </button>
                </div>
                <div class="d-md-none">
                    <button class="btn btn-outline-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                <a href="?logout" class="btn btn-sm btn-outline-danger ms-2">
                    <i class="fas fa-sign-out-alt me-1"></i>Déconnexion
                </a>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['connected'])): ?>
            <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="mobileMenuLabel">Menu</h5>
                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <div class="d-grid gap-3">
                        <a href="index.php" class="btn btn-outline-primary">
                            <i class="fas fa-home me-2"></i>Accueil
                        </a>
                        <a href="daily.php" class="btn btn-outline-danger">
                            <i class="fas fa-calendar-day me-2"></i>Stratégies du jour
                        </a>
                        <a href="ai.php" class="btn btn-outline-info">
                            <i class="fas fa-robot me-2"></i>Stratégies IA - toutes les données
                        </a>
                        <a href="tirages.php" class="btn btn-outline-success">
                            <i class="fas fa-history me-2"></i>Historique
                        </a>
                        <a href="keno.php" class="btn btn-outline-warning">
                            <i class="fas fa-dice me-2"></i>Keno
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

</header>

<!-- Modale Safe Global -->
<div class="modal fade" id="safeModal" tabindex="-1" aria-labelledby="safeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title w-100 text-center" id="safeModalLabel">
          <i class="fas fa-shield-alt me-2 text-success"></i>Top 5 stratégies « safe » (3 Historiques + 2 Journalières)
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <?php if (isset($top5Safe) && !empty($top5Safe)): ?>
          <div class="row justify-content-center">
            <div class="alert alert-info text-center mb-3">
              <i class="fas fa-info-circle me-2"></i>Combinaison de nos meilleures stratégies historiques et journalières
            </div>
            <?php foreach ($top5Safe as $strategy): ?>
              <div class="col-md-4 mb-3">
                <div class="card h-100 shadow border-<?php echo $strategy['class'] ?? 'primary'; ?>">
                  <div class="card-header bg-<?php echo $strategy['class'] ?? 'primary'; ?> text-white text-center">
                    <strong><?php echo htmlspecialchars($strategy['name'] ?? $strategy['label']); ?></strong>
                    <span class="ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo htmlspecialchars($strategy['description'] ?? $strategy['method'] ?? ''); ?>">
                      <i class="fas fa-info-circle"></i>
                    </span>
                  </div>
                  <div class="card-body text-center">
                    <div class="mb-2">
                      <span class="badge bg-dark me-1">Source : <b><?php echo htmlspecialchars($strategy['method'] ?? ''); ?></b></span>
                    </div>
                    <div class="mb-2">
                      <span class="badge bg-success me-1">EV : <b><?php echo (isset($strategy['ev']) && $strategy['ev'] > 0) ? number_format($strategy['ev'], 2) : 'Non calculé'; ?> €</b></span>
                      <span class="badge bg-primary">ROI : <b><?php echo (isset($strategy['roi']) && $strategy['roi'] > 0) ? number_format($strategy['roi'], 3) : 'Non calculé'; ?></b></span>
                    </div>
                    <div class="mb-2">
                      <?php
                      // Séparation bleu/jaune : par défaut 7 bleus, 0/5 jaunes
                      $bleus = array_slice($strategy['numbers'], 0, 7);
                      $jaunes = array_slice($strategy['numbers'], 7);
                      foreach ($bleus as $num) {
                        echo '<span class="badge bg-primary me-1">'.$num.'</span>';
                      }
                      foreach ($jaunes as $num) {
                        echo '<span class="badge bg-warning text-dark me-1">'.$num.'</span>';
                      }
                      ?>
                    </div>
                    <div class="mt-2">
                      <span class="badge bg-<?php echo $strategy['source'] === 'Journalier' ? 'warning' : 'light'; ?> text-dark">Tirage : <b><?php echo htmlspecialchars($strategy['source']); ?></b></span>
                      <span class="badge bg-dark">Score : <b><?php echo number_format($strategy['rating'], 1); ?>/10</b></span>
                    </div>
                    <div class="mt-2 small text-muted">
                      <?php
                        // Affichage masse de données analysées (si dispo)
                        if (isset($strategy['tiragesAnalyzed'])) {
                          echo '<span class="badge bg-info text-dark">'.htmlspecialchars($strategy['tiragesAnalyzed']).' tirages analysés</span>';
                        } elseif (isset($strategy['tirages']) && is_array($strategy['tirages'])) {
                          echo '<span class="badge bg-info text-dark">'.count($strategy['tirages']).' tirages analysés</span>';
                        } elseif (isset($strategy['dataCount'])) {
                          echo '<span class="badge bg-info text-dark">'.htmlspecialchars($strategy['dataCount']).' tirages analysés</span>';
                        } else {
                          echo '<span class="badge bg-info text-dark">Masse de données inconnue</span>';
                        }
                      ?>
                    </div>
                  </div>
                  <div class="card-footer small text-muted text-center">
                    <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo htmlspecialchars($strategy['description'] ?? $strategy['method'] ?? ''); ?>">
                      <?php echo htmlspecialchars($strategy['description'] ?? $strategy['method'] ?? ''); ?>
                    </span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <script>var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
          tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
          });</script>
        <?php else: ?>
          <div class="text-muted text-center">Aucune donnée disponible.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="content-wrapper">
<main class="container p-2 p-md-3">