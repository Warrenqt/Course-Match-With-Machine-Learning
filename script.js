// Course Recommendation System for SHS Students

// Mobile navigation toggle
const navToggle = document.getElementById('nav-toggle');
const navLinks = document.getElementById('nav-links');
if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => {
        navLinks.classList.toggle('show');
    });
}

// Smooth scrolling for all anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href && href.startsWith('#')) {
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start',
                    inline: 'nearest'
                });
            }
        }
    });
});

// Get Started button interaction (placeholder for future functionality)
document.querySelectorAll('[href="#about"]').forEach(link => {
    if (link.textContent.includes('Get Started')) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            // For now, just show a message about the system
            alert('Machine Learning Course Recommendation System\n\nThis system will analyze your:\n• Subject interests and preferences\n• Academic grades and performance\n• Future career aspirations (coming soon)\n\nTo provide personalized college course recommendations.');
        });
    }
});

// Add some interactive elements for student engagement
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects to feature cards
    const featureCards = document.querySelectorAll('.feature-card');
    featureCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Add click tracking for buttons (for future analytics)
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            console.log('Button clicked:', this.textContent.trim());
        });
    });
});

console.log('Course Recommendation System Ready - AI-powered recommendations for SHS students');
