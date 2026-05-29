<?php
include 'dbconfig.php';
$activeCount = $pdo->query("SELECT COUNT(*) FROM logs WHERE sign_out_time IS NULL")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhoIn — Contact Tracing</title>
    <meta name="description" content="Department of Computer Engineering Contact Tracing System. Sign in, sign out, and track who's currently inside.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Navigation -->
    <nav class="nav-bar" id="navbar">
        <a href="index.php" class="nav-logo">WHOIN</a>
        <ul class="nav-links" id="navLinks">
            <li><a href="#home">Home</a></li>
            <li><a href="#signin">Sign In</a></li>
            <li><a href="#active">Active</a></li>
            <li><a href="register.php">Register</a></li>
            <li><a href="admin.php" class="nav-cta">Admin</a></li>
        </ul>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-bg-orbs">
            <div class="hero-orb hero-orb-1"></div>
            <div class="hero-orb hero-orb-2"></div>
            <div class="hero-orb hero-orb-3"></div>
        </div>

        <h1 class="hero-headline animate-on-scroll">
            WHO'S <br>
            <span class="gradient-text">IN</span>?
        </h1>

        <svg class="hero-dotted-line animate-on-scroll animate-delay-1" viewBox="0 0 600 1">
            <line x1="0" y1="0.5" x2="600" y2="0.5" stroke-dasharray="6 6" stroke="currentColor" stroke-width="1" />
        </svg>

        <p class="hero-subtitle animate-on-scroll animate-delay-2">
            Department of Computer Engineering — Contact Tracing
        </p>
    </section>

    <!-- Sign In / Sign Out + Active Sessions -->
    <div class="section" id="signin">
        <div class="action-grid">

            <!-- Sign In Card -->
            <div class="glass-card glass-card-elevated glass-card-glow animate-on-scroll">
                <div class="card-header-bar">
                    <div class="card-header-icon indigo"><i class="fas fa-sign-in-alt"></i></div>
                    <div>
                        <div class="card-header-title">Sign In / Sign Out</div>
                        <div class="card-header-subtitle">Enter your ID to get started</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label-dark" for="search_id">ID Number (USC students/faculty/staff)</label>
                    <div class="input-group-dark">
                        <input type="text" id="search_id" class="input-dark" placeholder="Enter ID Number">
                        <button class="btn-gradient" onclick="retrieveUser()">Retrieve</button>
                    </div>
                </div>

                <div id="user_info"></div>
            </div>

            <!-- Currently Inside Card -->
            <div class="glass-card glass-card-elevated animate-on-scroll animate-delay-1" id="active">
                <div class="card-header-bar">
                    <div class="card-header-icon emerald"><i class="fas fa-users"></i></div>
                    <div>
                        <div class="card-header-title">Currently Inside</div>
                        <div class="card-header-subtitle">Live presence tracking</div>
                    </div>
                </div>

                <div class="stats-counter">
                    <span class="stats-number" id="active_count"><?php echo htmlspecialchars($activeCount); ?></span>
                    <span class="stats-label">People Inside</span>
                </div>

                <div id="active_sessions">
                    <?php include 'active_sessions.php'; ?>
                </div>
            </div>

        </div>

        <!-- Quick Links -->
        <div style="text-align: center; margin-top: 3rem;" class="animate-on-scroll animate-delay-3">
            <a href="register.php" class="btn-outline-dark">New User? Register Here</a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="site-footer">
        <h2 class="footer-headline">WHOIN</h2>
        <div class="footer-links">
            <a href="index.php">Home</a>
            <a href="register.php">Register</a>
            <a href="admin.php">Admin</a>
        </div>
        <p class="footer-copy">
            &copy; <?php echo date('Y'); ?> WhoIn — Department of Computer Engineering
        </p>
    </footer>

    <script src="js/toast.js"></script>
    <script src="js/navigation.js"></script>
    <script src="js/animations.js"></script>
    <script src="js/api.js"></script>
    <script src="js/app.js"></script>
</body>
</html>