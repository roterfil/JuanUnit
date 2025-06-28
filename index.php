<?php
$page_title = "Welcome to JuanUnit";
$css_path = "css/style.css"; // THIS IS THE CRITICAL LINE
$js_path = "js/script.js";
include 'includes/header.php'; 
?>

<div class="lp-v2-page-wrapper">
    <!-- Landing Page Navigation v2 -->
    <nav class="lp-v2-nav">
        <div class="lp-v2-container">
            <a href="index.php" class="lp-v2-logo">
                <img src="images/logo.png" alt="JuanUnit Logo">
                <span>JuanUnit</span>
            </a>
            <div class="lp-v2-nav-links">
                <a href="#features">Features</a>
                <a href="#how-it-works">How It Works</a>
                <a href="login.php" class="lp-v2-btn-nav">Get Started</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section v2 -->
    <section class="lp-v2-hero">
        <div class="lp-v2-container lp-v2-hero-content">
            <div class="lp-v2-hero-text">
                <span class="lp-v2-badge">Next-Generation Property Management</span>
                <h1>The Future of Unit Management is Here.</h1>
                <p>Simplify operations, empower tenants, and grow your rental business with one intuitive platform.</p>
                <a href="register.php" class="lp-v2-btn-primary">Create Your Account <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="lp-v2-hero-visual">
                <img src="images/img1.png" alt="3D illustration of a character managing tasks on a laptop">
                 <!-- Floating icons -->
                <i class="fas fa-dollar-sign lp-v2-float-icon" style="top: 20%; left: -10%;"></i>
                <i class="fas fa-key lp-v2-float-icon" style="top: 30%; right: -5%;"></i>
                <i class="fas fa-bell lp-v2-float-icon" style="bottom: 15%; left: 0;"></i>
            </div>
        </div>
    </section>

    <!-- Features Section v2 -->
    <section id="features" class="lp-v2-section">
        <div class="lp-v2-container">
            <div class="lp-v2-section-header">
                <h2>Why is JuanUnit Better?</h2>
                <p>We built a system that works for landlords and tenants alike.</p>
            </div>
            <div class="lp-v2-features-grid">
                <!-- Feature Card 1 -->
                <div class="lp-v2-feature-card">
                    <div class="lp-v2-card-icon" style="background-color: #e8f0fe;">
                        <i class="fas fa-sitemap" style="color: #4285f4;"></i>
                    </div>
                    <h3>Centralized Dashboard</h3>
                    <p>Oversee every unit, payment, and request from one powerful admin panel.</p>
                </div>
                <!-- Feature Card 2 -->
                <div class="lp-v2-feature-card">
                    <div class="lp-v2-card-icon" style="background-color: #e6f7ee;">
                        <i class="fas fa-mobile-alt" style="color: #34a853;"></i>
                    </div>
                    <h3>Tenant Self-Service</h3>
                    <p>Empower tenants to pay rent, upload proofs, and report issues online.</p>
                </div>
                <!-- Feature Card 3 -->
                <div class="lp-v2-feature-card">
                    <div class="lp-v2-card-icon" style="background-color: #fff8e6;">
                        <i class="fas fa-bell" style="color: #fbbc05;"></i>
                    </div>
                    <h3>Automated Notifications</h3>
                    <p>Reduce manual work with automated alerts for due dates and new announcements.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section v2 -->
    <section id="how-it-works" class="lp-v2-section lp-v2-how-it-works">
        <div class="lp-v2-container">
            <div class="lp-v2-section-header">
                <h2>Get Started in Minutes</h2>
                <p>Our simple onboarding process gets you up and running fast.</p>
            </div>
            <div class="lp-v2-timeline">
                <!-- Step 1 -->
                <div class="lp-v2-timeline-item">
                    <div class="lp-v2-timeline-text">
                        <span class="lp-v2-timeline-step">01</span>
                        <h3>Create Your Property</h3>
                        <p>Sign up as an administrator and add your first units. Set rent, descriptions, and upload photos in minutes.</p>
                    </div>
                    <div class="lp-v2-timeline-visual">
                        <img src="images/img2.png" alt="3D illustration of a person building with blocks">
                    </div>
                </div>
                <!-- Step 2 -->
                <div class="lp-v2-timeline-item">
                    <div class="lp-v2-timeline-text">
                        <span class="lp-v2-timeline-step">02</span>
                        <h3>Invite Your Tenants</h3>
                        <p>Tenants can register themselves, or you can assign them to units directly. They get instant access to their personal portal.</p>
                    </div>
                    <div class="lp-v2-timeline-visual">
                         <img src="images/img3.png" alt="3D illustration of a person sending a message">
                    </div>
                </div>
                <!-- Step 3 -->
                <div class="lp-v2-timeline-item">
                    <div class="lp-v2-timeline-text">
                        <span class="lp-v2-timeline-step">03</span>
                        <h3>Automate & Relax</h3>
                        <p>Let JuanUnit handle payment tracking, maintenance tickets, and announcements. Focus on growing your business.</p>
                    </div>
                    <div class="lp-v2-timeline-visual">
                         <img src="images/img4.png" alt="3D illustration of a person relaxing with automated tasks in the background">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section v2 -->
    <section class="lp-v2-section lp-v2-cta">
        <div class="lp-v2-container">
            <div class="lp-v2-cta-content">
                <div class="lp-v2-cta-visual">
                    <img src="https://assets.website-files.com/62e8f5c9dbfdcc62e8d287db/62e8f5cadbfdcc249ad2882c_cta-3d-character.png" alt="3D Paper airplane">
                </div>
                <h2>Ready to Transform Your Rental Management?</h2>
                <p>Join dozens of other property managers who trust JuanUnit.</p>
                <a href="register.php" class="lp-v2-btn-primary">Sign Up For Free</a>
            </div>
        </div>
    </section>

    <!-- Footer v2 -->
    <footer class="lp-v2-footer">
        <div class="lp-v2-container">
            <div class="lp-v2-footer-content">
                <div class="lp-v2-footer-col">
                    <a href="index.php" class="lp-v2-logo">
                        <img src="images/logo.png" alt="JuanUnit Logo">
                        <span>JuanUnit</span>
                    </a>
                    <p>The modern solution for dormitory and apartment management.</p>
                </div>
                <div class="lp-v2-footer-col">
                    <h4>Navigate</h4>
                    <ul>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#how-it-works">How It Works</a></li>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    </ul>
                </div>
                <div class="lp-v2-footer-col">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="lp-v2-footer-bottom">
                <p>© <?php echo date('Y'); ?> JuanUnit. All rights reserved.</p>
            </div>
        </div>
    </footer>
</div>

<?php include 'includes/footer.php'; ?>