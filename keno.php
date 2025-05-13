<?php
// Inclure les fichiers nécessaires
require_once 'src/startup.php';

// Titre de la page
$pageTitle = "Analyse Keno FDJ";

// Inclure l'en-tête
include 'assets/header.php';
?>

<div class="container mt-4">
    <h1 class="text-center mb-4">Amélie - Analyse Keno FDJ</h1>
    
    <!-- Section Keno -->
    <div class="card mb-4" id="kenoSection">
        <div class="card-header bg-warning text-dark">
            <h3><i class="fas fa-dice me-2"></i>Analyse Keno FDJ</h3>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs amelie-tabs mb-3" id="kenoTabList" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="keno-frequent-tab" data-bs-toggle="tab" data-bs-target="#keno-frequent" type="button" role="tab" aria-controls="keno-frequent" aria-selected="true">
                        <i class="fas fa-fire me-1"></i> Global
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="keno-month-tab" data-bs-toggle="tab" data-bs-target="#keno-month" type="button" role="tab" aria-controls="keno-month" aria-selected="false">
                        <i class="fas fa-calendar-alt me-1"></i> Mois
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="keno-week-tab" data-bs-toggle="tab" data-bs-target="#keno-week" type="button" role="tab" aria-controls="keno-week" aria-selected="false">
                        <i class="fas fa-calendar-week me-1"></i> Semaine
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="keno-logs-tab" data-bs-toggle="tab" data-bs-target="#keno-logs" type="button" role="tab" aria-controls="keno-logs" aria-selected="false">
                        <i class="fas fa-list-alt me-1"></i> Logs
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="kenoTabContent">
                <!-- Onglet Global -->
                <div class="tab-pane fade show active" id="keno-frequent" role="tabpanel" aria-labelledby="keno-frequent-tab">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-warning text-white">
                                    <h5 class="mb-0"><i class="fas fa-fire me-2"></i>9 numéros les plus sortis (global)</h5>
                                </div>
                                <div class="card-body">
                                    <div id="keno-most-table"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="fas fa-snowflake me-2"></i>9 numéros les moins sortis (global)</h5>
                                </div>
                                <div class="card-body">
                                    <div id="keno-least-table"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Onglet Mois -->
                <div class="tab-pane fade" id="keno-month" role="tabpanel" aria-labelledby="keno-month-tab">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-warning text-white">
                                    <h5 class="mb-0"><i class="fas fa-fire me-2"></i>9 numéros les plus sortis (mois)</h5>
                                </div>
                                <div class="card-body">
                                    <div id="keno-month-most-table"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="fas fa-snowflake me-2"></i>9 numéros les moins sortis (mois)</h5>
                                </div>
                                <div class="card-body">
                                    <div id="keno-month-least-table"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Analyse basée sur environ 60 tirages mensuels (2 tirages par jour).
                    </div>
                </div>
                
                <!-- Onglet Semaine -->
                <div class="tab-pane fade" id="keno-week" role="tabpanel" aria-labelledby="keno-week-tab">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-warning text-white">
                                    <h5 class="mb-0"><i class="fas fa-fire me-2"></i>9 numéros les plus sortis (semaine)</h5>
                                </div>
                                <div class="card-body">
                                    <div id="keno-week-most-table"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="fas fa-snowflake me-2"></i>9 numéros les moins sortis (semaine)</h5>
                                </div>
                                <div class="card-body">
                                    <div id="keno-week-least-table"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Analyse basée sur environ 14 tirages hebdomadaires (2 tirages par jour).
                    </div>
                </div>
                
                <!-- Onglet Logs -->
                <div class="tab-pane fade" id="keno-logs" role="tabpanel" aria-labelledby="keno-logs-tab">
                    <div id="keno-logs-content" class="mt-3">
                        <div class="card">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0">Logs de récupération des données</h5>
                            </div>
                            <div class="card-body">
                                <pre id="keno-logs-data" class="bg-light p-3" style="max-height: 300px; overflow-y: auto;">Chargement des logs...</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="keno-loading" class="text-center mt-3">
                <span class="spinner-border spinner-border-sm text-warning" role="status"></span> Chargement des résultats Keno...
            </div>
            <div id="keno-error" class="alert alert-danger mt-3 d-none"></div>
            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle me-2"></i> Ces données sont récupérées depuis <a href="https://www.reducmiz.com/resultat_fdj.php?jeu=keno&nb=all" target="_blank" class="alert-link">reducmiz.com</a> et analysées pour vous aider à choisir vos numéros Keno.
            </div>
            
            <!-- Modal Keno Info -->
            <div class="modal fade" id="kenoInfoModal" tabindex="-1" aria-labelledby="kenoInfoModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title" id="kenoInfoModalLabel"><i class="fas fa-info-circle me-2"></i>Informations détaillées Keno</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card mb-3">
                                        <div class="card-header bg-info text-white">
                                            <h5 class="mb-0">Statistiques d'analyse</h5>
                                        </div>
                                        <div class="card-body">
                                            <p><strong>Nombre total de tirages analysés:</strong> <span id="keno-total-tirages">--</span></p>
                                            <p><strong>Nombre total de numéros analysés:</strong> <span id="keno-total-numbers">--</span></p>
                                            <p><strong>Date d'analyse:</strong> <span id="keno-date-analyse">--</span></p>
                                            <p><strong>Dernière mise à jour du cache:</strong> <span id="keno-cache-date">--</span></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card mb-3">
                                        <div class="card-header bg-success text-white">
                                            <h5 class="mb-0">Comment utiliser ces données</h5>
                                        </div>
                                        <div class="card-body">
                                            <p>Les numéros les <strong>moins sortis</strong> sont statistiquement "dus" pour apparaître dans les prochains tirages selon la théorie de la régression vers la moyenne.</p>
                                            <p>Les numéros les <strong>plus sortis</strong> peuvent être considérés comme "chauds" et continuent parfois leur tendance.</p>
                                            <p>Pour une stratégie équilibrée, considérez une combinaison des deux groupes.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                            <button type="button" class="btn btn-warning" id="refresh-keno-data">Rafraîchir les données</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bouton pour ouvrir la modal -->
            <div class="text-center mt-3">
                <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#kenoInfoModal">
                    <i class="fas fa-info-circle me-2"></i>Informations détaillées
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Script pour charger les données Keno -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Fonction pour afficher les numéros Keno dans un tableau
    function renderKenoTable(numbers, freqs, containerId, color) {
        let html = '<div class="table-responsive"><table class="table table-bordered table-sm text-center align-middle">';
        html += '<thead class="table-' + color + '"><tr><th>Numéro</th><th>Apparitions</th></tr></thead><tbody>';
        
        for (let i = 0; i < numbers.length; i++) {
            const num = numbers[i];
            html += `<tr>
                <td><span class="badge bg-${color} fs-5 p-2">${num}</span></td>
                <td>${freqs[num]}</td>
            </tr>`;
        }
        
        html += '</tbody></table></div>';
        document.getElementById(containerId).innerHTML = html;
    }

    // Fonction pour charger les données
    function loadKenoData(forceRefresh = false) {
        document.getElementById('keno-loading').style.display = 'block';
        document.getElementById('keno-error').classList.add('d-none');
        
        // URL avec paramètre pour forcer le rafraîchissement si nécessaire
        const url = 'src/keno_results.php' + (forceRefresh ? '?refresh=1' : '');
        
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur lors de la récupération des données Keno');
                }
                return response.json();
            })
            .then(data => {
                // Masquer le chargement
                document.getElementById('keno-loading').style.display = 'none';
                
                // Afficher les tableaux
                renderKenoTable(data.most, data.most_freq, 'keno-most-table', 'warning');
                renderKenoTable(data.least, data.least_freq, 'keno-least-table', 'info');
                
                // Remplir les informations de la modal
                if (data.total_tirages) {
                    document.getElementById('keno-total-tirages').textContent = data.total_tirages;
                }
                if (data.total_numbers) {
                    document.getElementById('keno-total-numbers').textContent = data.total_numbers;
                }
                if (data.date_analyse) {
                    document.getElementById('keno-date-analyse').textContent = data.date_analyse;
                }
                if (data.cache_date) {
                    document.getElementById('keno-cache-date').textContent = data.cache_date;
                }
                
                // Charger les logs
                fetch('src/keno_results.php?logs=1')
                    .then(response => response.text())
                    .then(logs => {
                        document.getElementById('keno-logs-data').textContent = logs;
                    })
                    .catch(error => {
                        document.getElementById('keno-logs-data').textContent = "Erreur lors du chargement des logs: " + error.message;
                    });
            })
            .catch(error => {
                // Afficher l'erreur
                document.getElementById('keno-loading').style.display = 'none';
                const errorEl = document.getElementById('keno-error');
                errorEl.classList.remove('d-none');
                errorEl.textContent = 'Erreur: ' + error.message;
            });
    }
    
    // Charger les données au chargement de la page
    loadKenoData();
    
    // Ajouter un événement pour rafraîchir les données
    document.getElementById('refresh-keno-data').addEventListener('click', function() {
        loadKenoData(true);
    });
});
</script>

<?php
// Inclure le pied de page
include 'assets/footer.php';
?>