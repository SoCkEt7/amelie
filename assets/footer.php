</main>
</div><!-- /.content-wrapper -->

<footer>
    <div class="container">
        <?php if (isset($_SESSION['connected'])): ?>
            <div class="row align-items-center">
                <div class="col-md-5 text-center text-md-start mb-3 mb-md-0">
                    <div class="d-flex align-items-center justify-content-center justify-content-md-start">
                        <i class="fas fa-dice text-primary me-2"></i>
                        <span>Amélie - Générateur de Tirages Optimisés</span>
                    </div>
                    <?php if (isset($historicalData) && isset($historicalData['lastUpdated'])): ?>
                    <div class="mt-2 small text-muted">
                        <i class="fas fa-sync-alt me-1"></i> Dernière mise à jour: <?php echo $historicalData['lastUpdated']; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="col-md-4 text-center mb-3 mb-md-0">
                    <div class="d-flex justify-content-center">
                        <a href="#" class="btn btn-sm btn-outline-light me-2">
                            <i class="fas fa-question-circle me-1"></i>Aide
                        </a>
                        <a href="index.php" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-sync-alt me-1"></i>Actualiser
                        </a>
                    </div>
                </div>
                
                <div class="col-md-3 text-center text-md-end">
                    <div class="small">
                        Développé par <a href="https://codequantum.io" class="text-decoration-none text-info">Codequantum.io</a>
                    </div>
                    <div class="mt-1 text-muted x-small">
                        Version 2.5.0
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"
        integrity="sha512-uKQ39gEGiyUJl4AI6L+ekBdGKpGw4xJ55+xyJG7YFlJokPNYegn9KwQ3P8A7aFQAUtUsAQHep+d/lrGqrbPIDQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    if (tooltipTriggerList.length > 0) {
        [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    }
    
    // Initialisation des popovers
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    if (popoverTriggerList.length > 0) {
        [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
    }
    
    // Gestion des onglets
    const tabElms = document.querySelectorAll('.amelie-tabs .nav-link');
    tabElms.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const target = this.getAttribute('data-bs-target');
            
            // Enlever la classe active de tous les onglets et contenus
            document.querySelectorAll('.amelie-tabs .nav-link').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('show', 'active'));
            
            // Ajouter la classe active à l'onglet cliqué et son contenu
            this.classList.add('active');
            if (target) {
                document.querySelector(target).classList.add('show', 'active');
            }
        });
    });
    
    // Animation des badges de nombres
    const numberBadges = document.querySelectorAll('.number-badge');
    numberBadges.forEach(badge => {
        badge.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.08)';
            this.style.boxShadow = '0 6px 12px rgba(0, 0, 0, 0.25)';
        });
        
        badge.addEventListener('mouseleave', function() {
            this.style.transform = '';
            this.style.boxShadow = '';
        });
    });
});
</script>

<?php if (isset($debugInfo) && $debugInfo): ?>
<!-- Bouton de débogage flottant -->
<div style="position:fixed; bottom:10px; right:10px; z-index:1000;">
    <a href="?<?php echo (isset($_GET['debug']) ? '' : 'debug=1'); ?>" class="btn btn-sm <?php echo (isset($_GET['debug']) ? 'btn-danger' : 'btn-secondary'); ?>">
        <i class="fas fa-bug"></i>
    </a>
</div>
<?php endif; ?>

</body>
</html>