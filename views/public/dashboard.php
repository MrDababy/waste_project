<div class="container" style="padding-top: 100px; padding-bottom: 60px;">
    <div class="section-header">
        <div class="section-badge">Analytics Dashboard</div>
        <h2>Plastic Waste Monitoring Dashboard</h2>
        <p>Live statistics and analytics from all monitored locations</p>
    </div>
    
    <!-- Statistics Cards -->
    <div class="dashboard-stats">
        <div class="dashboard-stat">
            <div class="stat-icon"><i class="fas fa-weight-hanging"></i></div>
            <div class="stat-value"><?= number_format($stats['total_amount'] ?? 0, 2) ?> kg</div>
            <div class="stat-label">Total Plastic Collected</div>
        </div>
        <div class="dashboard-stat" style="border-color: var(--secondary);">
            <div class="stat-icon"><i class="fas fa-calendar-month"></i></div>
            <div class="stat-value"><?= number_format($stats['month_amount'] ?? 0, 2) ?> kg</div>
            <div class="stat-label">This Month's Collection</div>
        </div>
        <div class="dashboard-stat" style="border-color: var(--accent);">
            <div class="stat-icon"><i class="fas fa-calendar-year"></i></div>
            <div class="stat-value"><?= number_format($stats['year_amount'] ?? 0, 2) ?> kg</div>
            <div class="stat-label">This Year's Collection</div>
        </div>
        <div class="dashboard-stat" style="border-color: #8B5CF6;">
            <div class="stat-icon"><i class="fas fa-map-pin"></i></div>
            <div class="stat-value"><?= $stats['total_locations'] ?? 0 ?></div>
            <div class="stat-label">Locations Monitored</div>
        </div>
    </div>
    
    <!-- Charts -->
    <div class="chart-grid">
        <div class="chart-container">
            <h3><i class="fas fa-chart-bar"></i> Monthly Collection</h3>
            <canvas id="monthlyChart"></canvas>
        </div>
        <div class="chart-container">
            <h3><i class="fas fa-chart-pie"></i> Plastic Type Distribution</h3>
            <canvas id="distributionChart"></canvas>
        </div>
    </div>
    
    <div class="chart-grid">
        <div class="chart-container">
            <h3><i class="fas fa-chart-line"></i> Yearly Collection</h3>
            <canvas id="yearlyChart"></canvas>
        </div>
        <div class="chart-container">
            <h3><i class="fas fa-chart-bar"></i> Location Comparison</h3>
            <canvas id="locationChart"></canvas>
        </div>
    </div>
    
    <div class="chart-container">
        <h3><i class="fas fa-rocket"></i> Growth Analysis</h3>
        <canvas id="growthChart"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Chart
    const monthlyData = <?= json_encode($monthlyData) ?>;
    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: monthlyData.labels || [],
            datasets: [{
                label: 'Plastic Collected (kg)',
                data: monthlyData.data || [],
                backgroundColor: 'rgba(10, 142, 90, 0.7)',
                borderColor: '#0A8E5A',
                borderWidth: 2,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value + ' kg';
                        }
                    }
                }
            }
        }
    });
    
    // Distribution Chart
    const distributionData = <?= json_encode($plasticTypeDistribution) ?>;
    new Chart(document.getElementById('distributionChart'), {
        type: 'doughnut',
        data: {
            labels: distributionData.labels || [],
            datasets: [{
                data: distributionData.data || [],
                backgroundColor: distributionData.colors || [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#C9CBCF'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 16,
                        usePointStyle: true
                    }
                }
            }
        }
    });
    
    // Yearly Chart
    const yearlyData = <?= json_encode($yearlyData) ?>;
    new Chart(document.getElementById('yearlyChart'), {
        type: 'line',
        data: {
            labels: yearlyData.labels || [],
            datasets: [{
                label: 'Yearly Collection (kg)',
                data: yearlyData.data || [],
                backgroundColor: 'rgba(33, 150, 243, 0.1)',
                borderColor: '#2196F3',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value + ' kg';
                        }
                    }
                }
            }
        }
    });
    
    // Location Chart
    const locationData = <?= json_encode($locationComparison) ?>;
    new Chart(document.getElementById('locationChart'), {
        type: 'bar',
        data: {
            labels: locationData.labels || [],
            datasets: [{
                label: 'Plastic Collected (kg)',
                data: locationData.data || [],
                backgroundColor: [
                    'rgba(10, 142, 90, 0.7)',
                    'rgba(33, 150, 243, 0.7)',
                    'rgba(255, 107, 53, 0.7)',
                    'rgba(139, 92, 246, 0.7)',
                    'rgba(236, 72, 153, 0.7)'
                ],
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value + ' kg';
                        }
                    }
                }
            }
        }
    });
    
    // Growth Chart
    const growthData = <?= json_encode($growthData) ?>;
    new Chart(document.getElementById('growthChart'), {
        type: 'line',
        data: {
            labels: growthData.labels || [],
            datasets: [
                {
                    label: 'Monthly Collection (kg)',
                    data: growthData.data || [],
                    backgroundColor: 'rgba(10, 142, 90, 0.1)',
                    borderColor: '#0A8E5A',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y'
                },
                {
                    label: 'Growth (%)',
                    data: growthData.growth || [],
                    borderColor: '#FF6B35',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    fill: false,
                    tension: 0.4,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 16
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    position: 'left',
                    ticks: {
                        callback: function(value) {
                            return value + ' kg';
                        }
                    }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    },
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
});
</script>