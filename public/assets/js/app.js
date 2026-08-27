/**
 * Main Application JavaScript
 * Global functionality and utilities.
 */

(function () {
  "use strict";

  // ================================================================
  // DOM Ready
  // ================================================================
  document.addEventListener("DOMContentLoaded", function () {
    initNavigation();
    initFlashMessages();
    initForms();
  });

  // ================================================================
  // Navigation Toggle (Mobile)
  // ================================================================
  function initNavigation() {
    const toggle = document.getElementById("navToggle");
    const menu = document.getElementById("navMenu");

    if (toggle && menu) {
      toggle.addEventListener("click", function () {
        this.classList.toggle("active");
        menu.classList.toggle("show");
      });
    }

    // Close menu on link click (mobile)
    document.querySelectorAll(".navbar-nav .nav-link").forEach((link) => {
      link.addEventListener("click", function () {
        if (window.innerWidth <= 991) {
          const toggle = document.getElementById("navToggle");
          const menu = document.getElementById("navMenu");
          if (toggle) toggle.classList.remove("active");
          if (menu) menu.classList.remove("show");
        }
      });
    });

    // Navbar scroll effect
    const navbar = document.querySelector(".navbar");
    if (navbar) {
      window.addEventListener("scroll", function () {
        if (window.scrollY > 50) {
          navbar.classList.add("scrolled");
        } else {
          navbar.classList.remove("scrolled");
        }
      });
    }
  }

  // ================================================================
  // Flash Messages
  // ================================================================
  function initFlashMessages() {
    document
      .querySelectorAll(".flash-message .flash-close")
      .forEach((button) => {
        button.addEventListener("click", function () {
          const message = this.closest(".flash-message");
          message.style.opacity = "0";
          message.style.transform = "translateX(100px)";
          setTimeout(() => {
            message.remove();
          }, 300);
        });
      });

    // Auto-dismiss after 5 seconds
    document.querySelectorAll(".flash-message").forEach((message) => {
      setTimeout(() => {
        message.style.opacity = "0";
        message.style.transform = "translateX(100px)";
        setTimeout(() => {
          message.remove();
        }, 300);
      }, 5000);
    });
  }

  // ================================================================
  // Form Handling
  // ================================================================
  function initForms() {
    // Form validation
    document.querySelectorAll("form[data-validate]").forEach((form) => {
      form.addEventListener("submit", function (e) {
        const isValid = validateForm(this);
        if (!isValid) {
          e.preventDefault();
        }
      });
    });
  }

  function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll("[required]");

    inputs.forEach((input) => {
      const error = input.parentElement.querySelector(".error-message");
      if (!input.value.trim()) {
        isValid = false;
        input.classList.add("error");
        if (!error) {
          const msg = document.createElement("span");
          msg.className = "error-message";
          msg.textContent = "This field is required";
          input.parentElement.appendChild(msg);
        }
      } else {
        input.classList.remove("error");
        if (error) error.remove();
      }

      // Email validation
      if (input.type === "email" && input.value.trim()) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(input.value.trim())) {
          isValid = false;
          input.classList.add("error");
          const msg =
            input.parentElement.querySelector(".error-message") ||
            document.createElement("span");
          msg.className = "error-message";
          msg.textContent = "Please enter a valid email address";
          if (!input.parentElement.querySelector(".error-message")) {
            input.parentElement.appendChild(msg);
          }
        }
      }
    });

    // Password confirmation
    const password = form.querySelector('[name="password"]');
    const confirm = form.querySelector('[name="password_confirm"]');
    if (password && confirm && password.value !== confirm.value) {
      isValid = false;
      confirm.classList.add("error");
      const msg =
        confirm.parentElement.querySelector(".error-message") ||
        document.createElement("span");
      msg.className = "error-message";
      msg.textContent = "Passwords do not match";
      if (!confirm.parentElement.querySelector(".error-message")) {
        confirm.parentElement.appendChild(msg);
      }
    }

    return isValid;
  }

  // ================================================================
  // Utils
  // ================================================================
  window.Utils = {
    /**
     * Format a number with commas
     */
    formatNumber: function (num) {
      return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    },

    /**
     * Format a date string
     */
    formatDate: function (dateStr) {
      const date = new Date(dateStr);
      return date.toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
      });
    },

    /**
     * Format a datetime string
     */
    formatDateTime: function (dateStr) {
      const date = new Date(dateStr);
      return date.toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
      });
    },

    /**
     * Debounce a function
     */
    debounce: function (func, wait) {
      let timeout;
      return function executedFunction(...args) {
        const later = () => {
          clearTimeout(timeout);
          func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
      };
    },

    /**
     * Get CSRF token from meta tag
     */
    getCsrfToken: function () {
      const meta = document.querySelector('meta[name="csrf-token"]');
      return meta ? meta.getAttribute("content") : "";
    },

    /**
     * Make an AJAX request
     */
    fetch: async function (url, options = {}) {
      const defaultOptions = {
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          "Content-Type": "application/json",
          "X-CSRF-Token": this.getCsrfToken(),
        },
        credentials: "same-origin",
      };

      const mergedOptions = {
        ...defaultOptions,
        ...options,
        headers: {
          ...defaultOptions.headers,
          ...(options.headers || {}),
        },
      };

      const response = await fetch(url, mergedOptions);
      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || "Request failed");
      }

      return data;
    },
  };
})();
