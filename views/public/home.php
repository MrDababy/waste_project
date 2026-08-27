<!-- Hero Section -->
<section class="hero" id="home">
    <div class="container">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="fas fa-leaf"></i>
                Environmental Monitoring Platform
            </div>
            <h1>
                Track Plastic.<br>
                <span class="highlight">Measure Impact.</span><br>
                Build a Cleaner Future.
            </h1>
            <p>
                Monitor plastic waste collection across schools and locations in Tanzania.
                Track progress, analyze data, and make informed decisions for a sustainable environment.
            </p>
            <div class="hero-actions">
                <a href="/dashboard" class="btn btn-primary btn-lg">
                    <i class="fas fa-chart-bar"></i> View Dashboard
                </a>
                <a href="/register" class="btn btn-outline btn-lg">
                    <i class="fas fa-user-plus"></i> Get Started
                </a>
            </div>
            <div class="hero-stats">
                <div class="hero-stat">
                    <h3 id="stat-total"><?= number_format($stats['total_amount'] ?? 0, 0) ?>kg</h3>
                    <p>Total Collected</p>
                </div>
                <div class="hero-stat">
                    <h3><?= $stats['total_locations'] ?? 0 ?></h3>
                    <p>Locations Monitored</p>
                </div>
                <div class="hero-stat">
                    <h3><?= $stats['total_records'] ?? 0 ?></h3>
                    <p>Records Submitted</p>
                </div>
            </div>
        </div>
        <div class="hero-visual">
            <div class="visual-circle">
                <i class="fas fa-recycle"></i>
                <div class="floating-icons">
                    <i class="fas fa-tree"></i>
                    <i class="fas fa-globe-africa"></i>
                    <i class="fas fa-water"></i>
                    <i class="fas fa-sun"></i>
                    <i class="fas fa-leaf"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features" id="features">
    <div class="container">
        <div class="section-header">
            <div class="section-badge">Why Choose Us</div>
            <h2>Comprehensive Plastic Waste Monitoring</h2>
            <p>Our platform provides everything you need to track, analyze, and report on plastic waste collection.</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-database"></i>
                </div>
                <h3>Data Collection</h3>
                <p>Record plastic waste collections with detailed information including type, amount, location, and evidence images.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-check-double"></i>
                </div>
                <h3>Approval Workflow</h3>
                <p>Administrators can review, approve, or reject submissions to ensure data quality and accuracy.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Analytics & Insights</h3>
                <p>Visualize trends with interactive charts showing monthly collections, plastic type distribution, and location comparisons.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <h3>Interactive Map</h3>
                <p>View all monitored locations on an interactive map with real-time statistics for each collection point.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <h3>Reporting</h3>
                <p>Generate comprehensive monthly and yearly reports with export capabilities for stakeholders.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Secure & Encrypted</h3>
                <p>All sensitive data is encrypted using AES-256-GCM, ensuring privacy and data protection.</p>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="section-header">
            <div class="section-badge">Live Statistics</div>
            <h2>Making a Real Impact</h2>
            <p>Real-time data from monitored locations across Dar es Salaam</p>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-weight-hanging"></i>
                </div>
                <div class="stat-number" data-count="<?= $stats['total_amount'] ?? 0 ?>">
                    <?= number_format($stats['total_amount'] ?? 0, 0) ?>
                </div>
                <div class="stat-label">Total Plastic Collected (kg)</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-map-pin"></i>
                </div>
                <div class="stat-number" data-count="<?= $stats['total_locations'] ?? 0 ?>">
                    <?= $stats['total_locations'] ?? 0 ?>
                </div>
                <div class="stat-label">Active Monitoring Locations</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-number" data-count="<?= $stats['total_records'] ?? 0 ?>">
                    <?= $stats['total_records'] ?? 0 ?>
                </div>
                <div class="stat-label">Approved Records</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number" data-count="<?= $stats['total_collectors'] ?? 0 ?>">
                    <?= $stats['total_collectors'] ?? 0 ?>
                </div>
                <div class="stat-label">Active Collectors</div>
            </div>
        </div>
    </div>
</section>

<!-- Recent Collections -->
<?php if (!empty($recentCollections)): ?>
<section class="recent-section">
    <div class="container">
        <div class="section-header">
            <div class="section-badge">Recent Activity</div>
            <h2>Latest Collections</h2>
            <p>Recent approved plastic waste collections from monitored locations</p>
        </div>
        <div class="recent-grid">
            <?php foreach ($recentCollections as $collection): ?>
                <div class="recent-item">
                    <div class="recent-icon">
                        <i class="fas fa-trash-alt"></i>
                    </div>
                    <div class="recent-info">
                        <h4><?= htmlspecialchars($collection['location_name']) ?></h4>
                        <p>
                            <?= htmlspecialchars($collection['plastic_name']) ?> • 
                            <?= date('d M Y', strtotime($collection['collection_date'])) ?>
                        </p>
                    </div>
                    <div class="recent-amount">
                        <?= number_format($collection['amount'], 2) ?> kg
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <h2>Ready to Make a Difference?</h2>
        <p>Join our environmental monitoring initiative. Register today and start tracking plastic waste collections.</p>
        <?php if (!$this->session->isAuthenticated()): ?>
            <a href="/register" class="btn btn-primary btn-lg">
                <i class="fas fa-user-plus"></i> Get Started Now
            </a>
        <?php else: ?>
            <a href="/dashboard" class="btn btn-primary btn-lg">
                <i class="fas fa-chart-bar"></i> Go to Dashboard
            </a>
        <?php endif; ?>
    </div>
</section>

<script src="/assets/js/animations.js"></script>
<script>
// Animate stats on scroll
document.addEventListener('DOMContentLoaded', function() {
    const statNumbers = document.querySelectorAll('.stat-number');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = entry.target;
                const count = parseFloat(target.dataset.count) || 0;
                animateNumber(target, count);
                observer.unobserve(target);
            }
        });
    }, { threshold: 0.5 });
    
    statNumbers.forEach(stat => observer.observe(stat));
});

function animateNumber(element, target) {
    let current = 0;
    const increment = target / 60;
    const duration = 2000;
    const steps = 60;
    const stepTime = duration / steps;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        element.textContent = Math.round(current).toLocaleString();
    }, stepTime);
}
</script>