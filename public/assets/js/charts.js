/**
 * Charts JavaScript
 * Chart.js configuration and helpers.
 */

(function () {
  "use strict";

  // Chart defaults
  Chart.defaults.font.family =
    "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif";
  Chart.defaults.font.size = 13;
  Chart.defaults.color = "#4A5A6A";

  /**
   * Color palette for charts
   */
  const COLORS = {
    primary: "#0A8E5A",
    primaryLight: "rgba(10, 142, 90, 0.1)",
    primaryMedium: "rgba(10, 142, 90, 0.6)",
    secondary: "#2196F3",
    secondaryLight: "rgba(33, 150, 243, 0.1)",
    accent: "#FF6B35",
    accentLight: "rgba(255, 107, 53, 0.1)",
    purple: "#8B5CF6",
    pink: "#EC4899",
    colors: [
      "#FF6384",
      "#36A2EB",
      "#FFCE56",
      "#4BC0C0",
      "#9966FF",
      "#FF9F40",
      "#C9CBCF",
    ],
  };

  /**
   * Create a bar chart
   */
  function createBarChart(canvasId, data, options = {}) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    const defaultOptions = {
      responsive: true,
      plugins: {
        legend: {
          display: false,
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function (value) {
              return value + " kg";
            },
          },
        },
      },
    };

    return new Chart(ctx, {
      type: "bar",
      data: data,
      options: { ...defaultOptions, ...options },
    });
  }

  /**
   * Create a line chart
   */
  function createLineChart(canvasId, data, options = {}) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    const defaultOptions = {
      responsive: true,
      plugins: {
        legend: {
          display: false,
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function (value) {
              return value + " kg";
            },
          },
        },
      },
    };

    return new Chart(ctx, {
      type: "line",
      data: data,
      options: { ...defaultOptions, ...options },
    });
  }

  /**
   * Create a pie/doughnut chart
   */
  function createDoughnutChart(canvasId, data, options = {}) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    const defaultOptions = {
      responsive: true,
      plugins: {
        legend: {
          position: "bottom",
          labels: {
            padding: 16,
            usePointStyle: true,
          },
        },
      },
    };

    return new Chart(ctx, {
      type: "doughnut",
      data: data,
      options: { ...defaultOptions, ...options },
    });
  }

  /**
   * Create a horizontal bar chart
   */
  function createHorizontalBarChart(canvasId, data, options = {}) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    const defaultOptions = {
      responsive: true,
      indexAxis: "y",
      plugins: {
        legend: {
          display: false,
        },
      },
      scales: {
        x: {
          beginAtZero: true,
          ticks: {
            callback: function (value) {
              return value + " kg";
            },
          },
        },
      },
    };

    return new Chart(ctx, {
      type: "bar",
      data: data,
      options: { ...defaultOptions, ...options },
    });
  }

  /**
   * Create a mixed chart (bar + line)
   */
  function createMixedChart(canvasId, data, options = {}) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    const defaultOptions = {
      responsive: true,
      plugins: {
        legend: {
          position: "top",
          labels: {
            usePointStyle: true,
            padding: 16,
          },
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          position: "left",
          ticks: {
            callback: function (value) {
              return value + " kg";
            },
          },
        },
      },
    };

    return new Chart(ctx, {
      type: "bar",
      data: data,
      options: { ...defaultOptions, ...options },
    });
  }

  // Export chart functions
  window.Charts = {
    COLORS,
    createBarChart,
    createLineChart,
    createDoughnutChart,
    createHorizontalBarChart,
    createMixedChart,
  };
})();
