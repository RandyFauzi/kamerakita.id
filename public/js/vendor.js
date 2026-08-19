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
})();
