/**
 * Premium Scroll Animation Library
 * Reusable animation functions for any project
 */
(function() {
    const initAnimations = () => {
        // Check if user prefers reduced motion
        const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
        if (reducedMotion) {
            document.querySelectorAll('.animate-fade-up, .animate-fade-down, .animate-fade-left, .animate-fade-right, .animate-zoom-in, .animate-zoom-out, .animate-flip-up').forEach(el => {
                el.classList.add('is-visible');
            });
            return;
        }

        const observerOptions = {
            root: null,
            rootMargin: "0px 0px -10% 0px", 
            threshold: 0.15 
        };

        const observerCallback = (entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    if (!entry.target.hasAttribute('data-animate-repeat')) {
                        observer.unobserve(entry.target);
                    }
                } else {
                    if (entry.target.hasAttribute('data-animate-repeat')) {
                        entry.target.classList.remove('is-visible');
                    }
                }
            });
        };

        const observer = new IntersectionObserver(observerCallback, observerOptions);

        const animationClasses = [
            '.animate-fade-up', '.animate-fade-down', '.animate-fade-left', 
            '.animate-fade-right', '.animate-zoom-in', '.animate-zoom-out', '.animate-flip-up'
        ];

        const animatedElements = document.querySelectorAll(animationClasses.join(', '));
        animatedElements.forEach((el) => {
            if (el.parentElement && !el.style.transitionDelay) {
                const siblings = Array.from(el.parentElement.children).filter(child => 
                    animationClasses.some(cls => child.classList.contains(cls.replace('.', '')))
                );
                
                if (siblings.length > 1) {
                    const siblingIndex = siblings.indexOf(el);
                    el.style.transitionDelay = `${siblingIndex * 150}ms`;
                }
            }
            observer.observe(el);
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAnimations);
    } else {
        initAnimations();
    }
})();
