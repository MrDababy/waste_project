/**
 * Public Page JavaScript
 * Interactive elements for public pages.
 */

(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    // Initialize any public-specific functionality
    initScrollAnimations();
  });

  /**
   * Scroll animations for feature cards and stats
   */
  function initScrollAnimations() {
    const elements = document.querySelectorAll(
      ".feature-card, .stat-card, .recent-item",
    );

    if (!("IntersectionObserver" in window)) {
      // Fallback for browsers without IntersectionObserver
      elements.forEach((el) => (el.style.opacity = "1"));
      return;
    }

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("visible");
            observer.unobserve(entry.target);
          }
        });
      },
      {
        threshold: 0.1,
        rootMargin: "50px",
      },
    );

    elements.forEach((el) => {
      el.style.opacity = "0";
      el.style.transform = "translateY(30px)";
      el.style.transition = "opacity 0.6s ease, transform 0.6s ease";
      observer.observe(el);
    });
  }

  /**
   * Add CSS for scroll animations if not already present
   */
  const style = document.createElement("style");
  style.textContent = `
        .feature-card.visible,
        .stat-card.visible,
        .recent-item.visible {
            opacity: 1 !important;
            transform: translateY(0) !important;
        }
    `;
  document.head.appendChild(style);
})();
