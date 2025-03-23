document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.stars input[type="radio"]');
    const ratingText = document.querySelector('.rating-text');
    
    const ratingDescriptions = {
        5: 'Excellent',
        4: 'Very Good',
        3: 'Good',
        2: 'Fair',
        1: 'Poor'
    };

    stars.forEach(star => {
        star.addEventListener('change', function() {
            const rating = this.value;
            ratingText.textContent = ratingDescriptions[rating];
            ratingText.style.color = '#28a745';
        });
    });
});