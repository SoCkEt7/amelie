/**
 * Système de notation des stratégies avec likes/dislikes
 * Utilise localStorage pour stocker les préférences utilisateur
 */

document.addEventListener('DOMContentLoaded', function() {
    // Clé pour le stockage local
    const RATINGS_STORAGE_KEY = 'amelie_strategy_ratings';
    
    // Récupérer les notations existantes ou initialiser un objet vide
    let strategyRatings = JSON.parse(localStorage.getItem(RATINGS_STORAGE_KEY)) || {};
    
    // Initialiser tous les boutons de notation
    initRatingButtons();
    
    /**
     * Initialise tous les boutons de notation sur la page
     */
    function initRatingButtons() {
        // Trouver toutes les cartes de stratégie sur la page
        const strategyCards = document.querySelectorAll('.card');
        
        strategyCards.forEach(card => {
            const cardHeader = card.querySelector('.card-header');
            if (!cardHeader) return;
            
            // Obtenir le nom de la stratégie (texte du h5 dans le header)
            const strategyName = cardHeader.querySelector('h5')?.textContent.trim().split(' ')[0];
            if (!strategyName) return;
            
            // Vérifier si des boutons de notation existent déjà pour cette carte
            if (card.querySelector('.rating-actions')) return;
            
            // Créer les éléments de notation
            const ratingActions = document.createElement('div');
            ratingActions.className = 'rating-actions';
            
            // Bouton Like
            const likeBtn = document.createElement('button');
            likeBtn.className = 'rating-btn like-btn';
            likeBtn.dataset.strategy = strategyName;
            // Initialiser avec le compteur si disponible
            const likes = strategyRatings[strategyName] ? strategyRatings[strategyName].likes : 0;
            likeBtn.innerHTML = `<i class="fas fa-thumbs-up"></i> Gagné (${likes})`;
            
            // Bouton Dislike
            const dislikeBtn = document.createElement('button');
            dislikeBtn.className = 'rating-btn dislike-btn';
            dislikeBtn.dataset.strategy = strategyName;
            // Initialiser avec le compteur si disponible
            const dislikes = strategyRatings[strategyName] ? strategyRatings[strategyName].dislikes : 0;
            dislikeBtn.innerHTML = `<i class="fas fa-thumbs-down"></i> Perdu (${dislikes})`;
            
            // Ajouter les événements click
            likeBtn.addEventListener('click', function() {
                rateStrategy(strategyName, 'like');
            });
            
            dislikeBtn.addEventListener('click', function() {
                rateStrategy(strategyName, 'dislike');
            });
            
            // Ajouter les boutons au conteneur
            ratingActions.appendChild(likeBtn);
            ratingActions.appendChild(dislikeBtn);
            
            // Créer un élément pour afficher les statistiques
            const ratingStats = document.createElement('div');
            ratingStats.className = 'rating-stats';
            ratingStats.dataset.strategy = strategyName;
            
            // Ajouter les éléments à la carte
            const cardBody = card.querySelector('.card-body');
            if (cardBody) {
                cardBody.appendChild(ratingActions);
                cardBody.appendChild(ratingStats);
                
                // Mettre à jour l'affichage initial
                updateButtonStates(strategyName);
                updateRatingStats(strategyName);
                updateStrategyRatingBadge(strategyName);
            }
        });
    }
    
    /**
     * Note une stratégie (like ou dislike)
     */
    function rateStrategy(strategyName, action) {
        // Initialiser l'entrée si elle n'existe pas
        if (!strategyRatings[strategyName]) {
            strategyRatings[strategyName] = {
                likes: 0,
                dislikes: 0,
                userRating: null
            };
        }
        
        const currentRating = strategyRatings[strategyName];
        
        // Si l'utilisateur a déjà noté et clique sur le même bouton, annuler sa notation
        if (currentRating.userRating === action) {
            // Annuler la notation précédente
            if (action === 'like') {
                currentRating.likes = Math.max(0, currentRating.likes - 1);
            } else {
                currentRating.dislikes = Math.max(0, currentRating.dislikes - 1);
            }
            currentRating.userRating = null;
        } 
        // Si l'utilisateur change d'avis
        else if (currentRating.userRating !== null) {
            // Annuler la notation précédente
            if (currentRating.userRating === 'like') {
                currentRating.likes = Math.max(0, currentRating.likes - 1);
            } else {
                currentRating.dislikes = Math.max(0, currentRating.dislikes - 1);
            }
            
            // Ajouter la nouvelle notation
            if (action === 'like') {
                currentRating.likes++;
            } else {
                currentRating.dislikes++;
            }
            currentRating.userRating = action;
        }
        // Premier vote de l'utilisateur
        else {
            if (action === 'like') {
                currentRating.likes++;
            } else {
                currentRating.dislikes++;
            }
            currentRating.userRating = action;
        }
        
        // Sauvegarder dans le localStorage
        localStorage.setItem(RATINGS_STORAGE_KEY, JSON.stringify(strategyRatings));
        
        // Mettre à jour l'interface
        updateButtonStates(strategyName);
        updateRatingStats(strategyName);
        updateStrategyRatingBadge(strategyName);
    }
    
    /**
     * Met à jour l'état visuel des boutons de notation
     */
    function updateButtonStates(strategyName) {
        const likeBtn = document.querySelector(`.like-btn[data-strategy="${strategyName}"]`);
        const dislikeBtn = document.querySelector(`.dislike-btn[data-strategy="${strategyName}"]`);
        
        if (!likeBtn || !dislikeBtn) return;
        
        // Réinitialiser les classes
        likeBtn.classList.remove('liked');
        dislikeBtn.classList.remove('disliked');
        
        // Ajouter la classe appropriée en fonction de la notation de l'utilisateur
        const rating = strategyRatings[strategyName];
        if (rating && rating.userRating) {
            if (rating.userRating === 'like') {
                likeBtn.classList.add('liked');
            } else {
                dislikeBtn.classList.add('disliked');
            }
        }
        
        // Mettre à jour le texte des boutons pour montrer le nombre de votes
        if (rating) {
            likeBtn.innerHTML = `<i class="fas fa-thumbs-up"></i> Gagné (${rating.likes})`;
            dislikeBtn.innerHTML = `<i class="fas fa-thumbs-down"></i> Perdu (${rating.dislikes})`;
        }
    }
    
    /**
     * Met à jour les statistiques de notation affichées
     */
    function updateRatingStats(strategyName) {
        const statsElement = document.querySelector(`.rating-stats[data-strategy="${strategyName}"]`);
        if (!statsElement) return;
        
        const rating = strategyRatings[strategyName];
        if (!rating) {
            statsElement.textContent = '';
            return;
        }
        
        const total = rating.likes + rating.dislikes;
        if (total === 0) {
            statsElement.textContent = '';
            return;
        }
        
        const likePercentage = Math.round((rating.likes / total) * 100);
        statsElement.textContent = `${rating.likes} 👍 ${rating.dislikes} 👎 (${likePercentage}% de succès)`;
    }
    
    /**
     * Ajoute ou met à jour un badge de notation sur la carte de stratégie
     */
    function updateStrategyRatingBadge(strategyName) {
        // Utiliser querySelector avec méthode plus compatible
        const cards = document.querySelectorAll('.card');
        let card = null;
        
        // Trouver la carte qui contient le nom de la stratégie
        for (let i = 0; i < cards.length; i++) {
            const h5 = cards[i].querySelector('.card-header h5');
            if (h5 && h5.textContent.trim().startsWith(strategyName)) {
                card = cards[i];
                break;
            }
        }
        
        if (!card) return;
        
        // Supprimer un badge existant
        const existingBadge = card.querySelector('.rating-badge');
        if (existingBadge) {
            existingBadge.remove();
        }
        
        const rating = strategyRatings[strategyName];
        if (!rating || rating.userRating === null) return;
        
        // Créer un nouveau badge
        const badge = document.createElement('div');
        badge.className = `rating-badge ${rating.userRating === 'like' ? 'liked' : 'disliked'}`;
        badge.innerHTML = rating.userRating === 'like' ? 
            '<i class="fas fa-thumbs-up"></i> Gagnant' : 
            '<i class="fas fa-thumbs-down"></i> Perdant';
        
        // Ajouter le badge à la carte
        card.style.position = 'relative';
        card.appendChild(badge);
    }
    
    // Initialiser les badges pour toutes les stratégies notées
    function initRatingBadges() {
        for (const strategyName in strategyRatings) {
            if (strategyRatings[strategyName].userRating) {
                updateStrategyRatingBadge(strategyName);
            }
        }
    }
    
    // Appeler l'initialisation des badges
    initRatingBadges();
});