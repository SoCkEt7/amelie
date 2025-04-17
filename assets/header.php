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
                <div class="me-3">
                    <a href="index.php" class="btn btn-sm btn-outline-primary me-1">
                        <i class="fas fa-home me-1"></i>Accueil
                    </a>
                    <a href="daily.php" class="btn btn-sm btn-outline-danger me-1">
                        <i class="fas fa-calendar-day me-1"></i>Stratégies du jour
                    </a>
                    <a href="ai.php" class="btn btn-sm btn-outline-info me-1">
                        <i class="fas fa-robot me-1"></i>Stratégies IA - toutes les données
                    </a>
                    <a href="tirages.php" class="btn btn-sm btn-outline-success me-1">
                        <i class="fas fa-history me-1"></i>Historique
                    </a>
                </div>
                <a href="?logout" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-sign-out-alt me-1"></i>Déconnexion
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<div class="content-wrapper">
<main class="container p-2 p-md-3">