<?php
$page_title = "Home";
$css_path = "css/style.css";
$js_path = "js/script.js";
include 'includes/header.php';
?>

<!-- Navigation -->
<nav class="navbar">
    <div class="nav-container">
        <a href="#" class="logo">JuanUnit</a>
        <ul class="nav-links">
            <li><a href="#features">Features</a></li>
            <li><a href="#about">About Us</a></li>
            <li><a href="#contact">Contact</a></li>
        </ul>
        <a href="login.php" class="btn btn-primary">Login / Sign up</a>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <div class="hero-text">
            <h1>Unit Management for EveryJuan</h1>
            <p>Streamline your dormitory management with our comprehensive platform. Handle payments, maintenance requests, and tenant communications all in one place.</p>
            <div class="hero-buttons">
                <a href="register.php" class="btn btn-primary">Get Started</a>
                <a href="#contact" class="btn btn-secondary">Contact Us</a>
            </div>
        </div>
        <div class="hero-visual">
            <!-- Placeholder for hero illustration -->
            <div style="width: 100%; height: 400px; background: rgba(255,255,255,0.1); border-radius: 20px; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px;">
                <i class="fas fa-building" style="font-size: 120px; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="section">
    <div class="container">
        <div class="section-header">
            <h2>Feel The Power Of Technology</h2>
            <p>Experience seamless dormitory management with our innovative features designed for modern living.</p>
        </div>
        <div class="features-grid">
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <h3>Effortless Payments</h3>
                <p>Upload payment proofs instantly and track your payment history with ease. Never miss a due date again.</p>
            </div>
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <h3>Instant Announcements</h3>
                <p>Stay updated with important announcements from management. Get notified about events, maintenance, and more.</p>
            </div>
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-tools"></i>
                </div>
                <h3>Quick Maintenance Reporting</h3>
                <p>Report maintenance issues quickly and track their progress in real-time. Efficient problem resolution guaranteed.</p>
            </div>
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Secure & Reliable</h3>
                <p>Your data is protected with enterprise-grade security. Reliable system with 99.9% uptime guarantee.</p>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section id="about" class="section" style="background: white;">
    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center;">
            <div>
                <h4 style="color: #667eea; font-weight: 600; margin-bottom: 1rem;">ABOUT US</h4>
                <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 2rem; color: #333;">We're Best In Software Development</h2>
                <p style="color: #666; margin-bottom: 2rem; line-height: 1.8;">Scale your software operations through a custom engineering team. Meet the demand of your company's expectations with a high-performing operations team skilled in the top technologies you need.</p>
                <a href="#contact" class="btn btn-primary">Learn About Us</a>
            </div>
            <div style="text-align: center;">
                <!-- Placeholder for about illustration -->
                <div style="width: 100%; height: 300px; background: #f8f9ff; border-radius: 20px; display: flex; align-items: center; justify-content: center; color: #667eea;">
                    <i class="fas fa-users" style="font-size: 80px; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Additional Features Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h4 style="color: #667eea; font-weight: 600; margin-bottom: 1rem;">FEATURES</h4>
            <h2>We Provide Exciting Features</h2>
        </div>
        <div class="features-grid">
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3>Mobile Responsive</h3>
                <p>Access your account from any device. Our platform works seamlessly on desktop, tablet, and mobile devices.</p>
            </div>
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Analytics Dashboard</h3>
                <p>Get insights into payment trends, occupancy rates, and maintenance patterns with comprehensive analytics.</p>
            </div>
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <h3>24/7 Support</h3>
                <p>Our dedicated support team is available round the clock to assist you with any questions or concerns.</p>
            </div>
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-cog"></i>
                </div>
                <h3>Easy Management</h3>
                <p>Intuitive interface makes it easy for administrators to manage units, tenants, and all dormitory operations.</p>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section id="contact" class="section" style="background: white;">
    <div class="container">
        <div class="section-header">
            <h2>Get In Touch</h2>
            <p>Ready to transform your dormitory management? Contact us today to get started.</p>
        </div>
        <div style="max-width: 600px; margin: 0 auto;">
            <form class="form-container">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" class="form-control" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Send Message</button>
            </form>
        </div>
    </div>
</section>

<!-- Footer -->
<footer style="background: #333; color: white; text-align: center; padding: 2rem 0;">
    <div class="container">
        <p>&copy; 2024 JuanUnit. All rights reserved. Built with ❤️ for better dormitory management.</p>
    </div>
</footer>

<?php include 'includes/footer.php'; ?>