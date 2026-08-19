(() => {
  const links = document.querySelectorAll('a[href^="#"]');
  const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

  links.forEach((link) => {
    link.addEventListener("click", (event) => {
      const selector = link.getAttribute("href");
      if (!selector || selector === "#") return;
      const target = document.querySelector(selector);
      if (!target) return;

      event.preventDefault();
      target.scrollIntoView({
        behavior: reducedMotion.matches ? "auto" : "smooth",
        block: "start"
      });
    });
  });

  if (reducedMotion.matches) return;

  const reveal = (elements, options = {}) => {
    const { baseDelay = 0, step = 90, className = "reveal-item" } = options;

    elements.filter(Boolean).forEach((element, index) => {
      className.split(/\s+/).forEach((name) => element.classList.add(name));
      element.style.setProperty("--reveal-delay", `${baseDelay + index * step}ms`);
    });
  };

  reveal([...document.querySelectorAll(".hero-copy > *")], { step: 85 });
  reveal([...document.querySelectorAll(".hero-visual > *")], {
    baseDelay: 140,
    step: 105,
    className: "reveal-fade"
  });

  reveal([document.querySelector(".project-heading > div")], { className: "reveal-item reveal-left" });
  reveal([document.querySelector(".project-heading > p")], { baseDelay: 100, className: "reveal-item reveal-right" });
  reveal([...document.querySelectorAll(".project-card")], { baseDelay: 120, step: 130 });

  reveal([...document.querySelectorAll(".profit-heading > *")], { step: 90 });
  reveal([...document.querySelectorAll(".tier-card")], { baseDelay: 100, step: 140 });

  reveal([document.querySelector(".how-heading > div")], { className: "reveal-item reveal-left" });
  reveal([document.querySelector(".how-button")], { baseDelay: 100, className: "reveal-item reveal-right" });
  reveal([...document.querySelectorAll(".step")], { baseDelay: 100, step: 130 });

  reveal([document.querySelector(".final-cta")]);
  document.body.classList.add("motion-ready");

  const revealElements = [...document.querySelectorAll(".reveal-item, .reveal-fade")];
  if (!("IntersectionObserver" in window)) {
    revealElements.forEach((element) => element.classList.add("is-visible"));
    return;
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      entry.target.classList.add("is-visible");
      observer.unobserve(entry.target);
    });
  }, {
    threshold: 0.14,
    rootMargin: "0px 0px -8%"
  });

  revealElements.forEach((element) => observer.observe(element));
})();
