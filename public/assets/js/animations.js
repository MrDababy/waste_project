/**
 * Animations JavaScript
 * Scroll-triggered animations and counter effects.
 */

(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    initCounterAnimations();
    initParallaxEffects();
  });

  /**
   * Animate number counters on scroll
   */
  function initCounterAnimations() {
    const counters = document.querySelectorAll(".stat-number[data-count]");

    if (!counters.length) return;

    if (!("IntersectionObserver" in window)) {
      counters.forEach((counter) => {
        const target = parseInt(counter.dataset.count) || 0;
        counter.textContent = target.toLocaleString();
      });
      return;
    }

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            const counter = entry.target;
            const target = parseFloat(counter.dataset.count) || 0;
            animateCounter(counter, target);
            observer.unobserve(counter);
          }
        });
      },
      { threshold: 0.5 },
    );

    counters.forEach((counter) => observer.observe(counter));
  }

  /**
   * Animate a single counter
   */
  function animateCounter(element, target) {
    const duration = 2000;
    const steps = 60;
    const increment = target / steps;
    const stepTime = duration / steps;
    let current = 0;

    // Handle zero
    if (target === 0) {
      element.textContent = "0";
      return;
    }

    // Handle large numbers with decimals
    const hasDecimal = target % 1 !== 0;
    const decimals = hasDecimal ? 1 : 0;

    const timer = setInterval(() => {
      current += increment;
      if (current >= target) {
        current = target;
        clearInterval(timer);
      }
      element.textContent = current.toFixed(decimals).toLocaleString();
    }, stepTime);
  }

  /**
   * Parallax effects for hero section
   */
  function initParallaxEffects() {
    const hero = document.querySelector(".hero");
    if (!hero) return;

    window.addEventListener(
      "scroll",
      function () {
        const scrolled = window.pageYOffset;
        const rate = scrolled * 0.3;

        // Parallax for hero visual
        const visual = hero.querySelector(".hero-visual");
        if (visual) {
          visual.style.transform = `translateY(${rate * 0.1}px)`;
        }

        // Parallax for floating icons
        const icons = hero.querySelectorAll(".floating-icons i");
        icons.forEach((icon, index) => {
          const speed = 0.05 + index * 0.02;
          icon.style.transform = `translateY(${scrolled * speed}px) rotate(${scrolled * 0.02}deg)`;
        });
      },
      { passive: true },
    );
  }
})();
