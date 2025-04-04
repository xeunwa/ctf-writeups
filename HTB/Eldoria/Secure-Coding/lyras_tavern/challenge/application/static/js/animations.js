document.addEventListener('DOMContentLoaded', () => {
    // Medieval-themed animation for journal entries: fade, slide, and a slight rotation
    const entries = document.querySelectorAll('.journal-entry');
    entries.forEach((entry, index) => {
        // Initial state: slightly lower, rotated, and hidden
        entry.style.opacity = '0';
        entry.style.transform = 'translateY(20px) rotate(-5deg)';
        
        setTimeout(() => {
            // Transition properties with a more "old parchment" feel using a custom easing curve
            entry.style.transition = 'all 0.7s cubic-bezier(0.25, 1.25, 0.5, 1)';
            entry.style.opacity = '1';
            entry.style.transform = 'translateY(0) rotate(0deg)';
        }, index * 300);
    });

    // Medieval hover effects for navigation links: a slight scale and rotation effect
    const navLinks = document.querySelectorAll('nav a');
    navLinks.forEach(link => {
        link.addEventListener('mouseenter', () => {
            link.style.transition = 'transform 0.3s ease-out';
            link.style.transform = 'scale(1.1) rotate(-3deg)';
        });
        
        link.addEventListener('mouseleave', () => {
            link.style.transition = 'transform 0.3s ease-out';
            link.style.transform = 'scale(1) rotate(0deg)';
        });
    });
});
