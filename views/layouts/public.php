<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Plastic Waste Monitoring and Recording System - Track plastic collection and environmental impact">
    <meta name="keywords" content="plastic waste, recycling, environmental monitoring, Tanzania">
    <meta name="author" content="Plastic Waste System">
    <meta name="csrf-token" content="<?= $this->session->getCsrfToken() ?>">
    
    <title><?= $pageTitle ?? 'Plastic Waste Monitoring System' ?></title>
    
    <!-- Favicon -->
    <link rel="icon" href="/assets/images/favicon.ico">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- Leaflet.js -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/public.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-recycle"></i>
                <span>Plastic<span>Watch</span></span>
            </a>
            
            <button class="navbar-toggler" type="button" id="navToggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
            
            <div class="navbar-collapse" id="navMenu">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link <?= $_SERVER['REQUEST_URI'] === '/' ? 'active' : '' ?>" href="/">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false ? 'active' : '' ?>" href="/dashboard">
                            <i class="fas fa-chart-bar"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/map') !== false ? 'active' : '' ?>" href="/map">
                            <i class="fas fa-map-marked-alt"></i> Map
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav ms-auto">
                    <?php if ($this->session->isAuthenticated()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard">
                                <i class="fas fa-user"></i> 
                                <?= $this->session->get('user_data.first_name') ?? 'User' ?>
                            </a>
                        </li>
                        <?php if ($this->session->getUserRole() === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/admin/dashboard">
                                    <i class="fas fa-cog"></i> Admin
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/logout">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/login">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary btn-sm" href="/register">
                                <i class="fas fa-user-plus"></i> Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php $flashMessages = $this->getFlashMessages(); ?>
    <?php if (!empty($flashMessages)): ?>
        <div class="flash-container">
            <?php foreach ($flashMessages as $type => $message): ?>
                <div class="flash-message flash-<?= $type ?>">
                    <i class="fas <?= $type === 'success' ? 'fa-check-circle' : ($type === 'error' ? 'fa-exclamation-circle' : ($type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle')) ?>"></i>
                    <?= htmlspecialchars($message) ?>
                    <button class="flash-close">&times;</button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main>
        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <a href="/" class="footer-logo">
                        <i class="fas fa-recycle"></i>
                        <span>Plastic<span>Watch</span></span>
                    </a>
                    <p>Track plastic waste collection and measure environmental impact across Tanzania.</p>
                    <div class="footer-social">
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="/">Home</a></li>
                        <li><a href="/dashboard">Dashboard</a></li>
                        <li><a href="/map">Map</a></li>
                        <?php if (!$this->session->isAuthenticated()): ?>
                            <li><a href="/register">Register</a></li>
                            <li><a href="/login">Login</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h4>About</h4>
                    <ul>
                        <li><a href="#">Mission</a></li>
                        <li><a href="#">Impact</a></li>
                        <li><a href="#">Partners</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-contact">
                    <h4>Contact</h4>
                    <p><i class="fas fa-map-marker-alt"></i> Dar es Salaam, Tanzania</p>
                    <p><i class="fas fa-envelope"></i> info@plasticwatch.org</p>
                    <p><i class="fas fa-phone"></i> +255 712 345 678</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Plastic Waste Monitoring System. All rights reserved.</p>
                <p>Built with <i class="fas fa-heart"></i> for a cleaner environment</p>
            </div>
        </div>
    </footer>

    <!-- Custom JavaScript -->
    <script src="/assets/js/app.js"></script>
    <script src="/assets/js/public.js"></script>
    <?php if (isset($extraScripts)): ?>
        <?= $extraScripts ?>
    <?php endif; ?>
</body>
</html>