/**
 * Premium Scroll Animation Library
 * Reusable animation functions for any project
 */
document.addEventListener("DOMContentLoaded", () => {
    // Check if user prefers reduced motion
    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    if (reducedMotion) return;

    // The observer options
    const observerOptions = {
        root: null,
        rootMargin: "0px 0px -10% 0px", // Trigger slightly before it comes into view
        threshold: 0.15 // Trigger when 15% of the element is visible
    };

    // The observer callback
    const observerCallback = (entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Add the visible class to trigger CSS transitions
                entry.target.classList.add('is-visible');
                
                // If it shouldn't repeat, unobserve it
                if (!entry.target.hasAttribute('data-animate-repeat')) {
                    observer.unobserve(entry.target);
                }
            } else {
                // If it repeats, remove the class when out of view
                if (entry.target.hasAttribute('data-animate-repeat')) {
                    entry.target.classList.remove('is-visible');
                }
            }
        });
    };

    // Initialize Intersection Observer
    const observer = new IntersectionObserver(observerCallback, observerOptions);

    // Reusable animation classes
    const animationClasses = [
        '.animate-fade-up',
        '.animate-fade-down',
        '.animate-fade-left',
        '.animate-fade-right',
        '.animate-zoom-in',
        '.animate-zoom-out',
        '.animate-flip-up'
    ];

    // Find all elements and observe them
    const animatedElements = document.querySelectorAll(animationClasses.join(', '));
    animatedElements.forEach((el, index) => {
        // Automatically add staggering if they share the same parent and don't have explicit delay
        if (el.parentElement && !el.style.transitionDelay) {
            const siblings = Array.from(el.parentElement.children).filter(child => 
                animationClasses.some(cls => child.classList.contains(cls.replace('.', '')))
            );
            
            if (siblings.length > 1) {
                const siblingIndex = siblings.indexOf(el);
                // Stagger by 150ms per item
                el.style.transitionDelay = `${siblingIndex * 150}ms`;
            }
        }

        observer.observe(el);
    });
});
