<?php
include 'dbconfig.php';

$registration_success = false;
$guest_id_code = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_number = trim($_POST['id_number']);
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'] ?: null;
    $last_name = $_POST['last_name'];
    $barangay = $_POST['barangay'];
    $city = $_POST['city'];
    $province = $_POST['province'];
    $contact_number = $_POST['contact_number'];
    $email = $_POST['email'];
    
    if ($id_number === '') {
        $id_number = 'GUEST-' . strtoupper(substr(uniqid(), 0, 8));
        $guest_id_code = $id_number;
    }

    $stmt = $pdo->prepare("INSERT INTO users (id_number, first_name, middle_name, last_name, barangay, city, province, contact_number, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id_number, $first_name, $middle_name, $last_name, $barangay, $city, $province, $contact_number, $email]);
    
    $user_id = $pdo->lastInsertId();
    
    // Auto sign in
    $sign_in_time = date('Y-m-d H:i:s');
    $stmt2 = $pdo->prepare("INSERT INTO logs (user_id, sign_in_time) VALUES (?, ?)");
    $stmt2->execute([$user_id, $sign_in_time]);
    
    $registration_success = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhoIn — Register</title>
    <meta name="description" content="Register as a new user for the WhoIn contact tracing system.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Navigation -->
    <nav class="nav-bar scrolled" id="navbar">
        <a href="index.php" class="nav-logo">WHOIN</a>
        <ul class="nav-links" id="navLinks">
            <li><a href="index.php">Home</a></li>
            <li><a href="register.php">Register</a></li>
            <li><a href="admin.php" class="nav-cta">Admin</a></li>
        </ul>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </nav>

    <!-- Background Orbs -->
    <div class="hero-bg-orbs" style="position: fixed; inset: 0; z-index: 0;">
        <div class="hero-orb hero-orb-1"></div>
        <div class="hero-orb hero-orb-2"></div>
    </div>

    <!-- Register Form -->
    <div class="register-page">
        <div class="glass-card glass-card-elevated register-card" style="position: relative; z-index: 1;">
            <div class="card-header-bar">
                <div class="card-header-icon indigo" style="width: 56px; height: 56px; font-size: 1.2rem; display: flex; align-items: center; justify-content: center;"><i class="fas fa-user-plus"></i></div>
                <div>
                    <div class="card-header-title" style="font-size: 1.5rem;">New User Registration</div>
                    <div class="card-header-subtitle">Join the WhoIn tracking system</div>
                </div>
            </div>

            <?php if ($registration_success): ?>
                <div class="alert-dark success" style="margin-bottom: 1.5rem; padding: 1rem; border-radius: var(--radius-md); background: rgba(16, 185, 129, 0.12); color: var(--text-primary);">
                    <strong>Registration complete.</strong>
                    <?php if ($guest_id_code): ?>
                        Your guest ID is <strong><?php echo htmlspecialchars($guest_id_code); ?></strong>. Use this ID to retrieve your record when signing out.
                    <?php else: ?>
                        You may now sign in with your registered ID number.
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="registerForm">
                <!-- ID Number -->
                <div class="form-group">
                    <label class="form-label-dark" for="id_number">ID Number (Leave blank for guest visitors)</label>
                    <input type="text" name="id_number" id="id_number" class="input-dark" placeholder="e.g. 20230001">
                    <p id="visitorType" class="form-note" style="margin-top: 0.5rem; color: var(--text-secondary);">Visitor type: Guest visitor (temporary guest ID will be generated)</p>
                </div>

                <div class="form-divider"></div>
                <p class="form-section-label">Personal Information</p>

                <!-- Name Fields -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label-dark" for="first_name">First Name</label>
                        <input type="text" name="first_name" id="first_name" class="input-dark" placeholder="First Name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label-dark" for="middle_name">Middle Name</label>
                        <input type="text" name="middle_name" id="middle_name" class="input-dark" placeholder="Middle Name">
                    </div>
                    <div class="form-group">
                        <label class="form-label-dark" for="last_name">Last Name</label>
                        <input type="text" name="last_name" id="last_name" class="input-dark" placeholder="Last Name" required>
                    </div>
                </div>

                <div class="form-divider"></div>
                <p class="form-section-label">Address</p>

                <!-- Address Fields -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label-dark" for="barangay">Barangay</label>
                        <input type="text" name="barangay" id="barangay" class="input-dark" placeholder="Barangay" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label-dark" for="city">City/Town</label>
                        <input type="text" name="city" id="city" class="input-dark" placeholder="City/Town" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label-dark" for="province">Province</label>
                        <input type="text" name="province" id="province" class="input-dark" placeholder="Province" required>
                    </div>
                </div>

                <div class="form-divider"></div>
                <p class="form-section-label">Contact Details</p>

                <!-- Contact Fields -->
                <div class="form-row" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group">
                        <label class="form-label-dark" for="contact_number">Contact Number</label>
                        <input type="text" name="contact_number" id="contact_number" class="input-dark" placeholder="09XX XXX XXXX" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label-dark" for="email">Email</label>
                        <input type="email" name="email" id="email" class="input-dark" placeholder="email@example.com" required>
                    </div>
                </div>

                <div class="form-divider"></div>

                <!-- Submit -->
                <button type="submit" class="btn-gradient" style="width: 100%; padding: 16px; font-size: 1rem;">
                    Register & Sign In →
                </button>

                <div style="text-align: center; margin-top: 1.5rem;">
                    <a href="index.php" class="btn-outline-dark">← Back to Home</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="site-footer" style="position: relative; z-index: 1;">
        <p class="footer-copy">
            &copy; <?php echo date('Y'); ?> WhoIn — Department of Computer Engineering
        </p>
    </footer>

    <script>
        // Mobile nav toggle and visitor type indicator
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.getElementById('navToggle');
            const links = document.getElementById('navLinks');
            const idInput = document.getElementById('id_number');
            const visitorType = document.getElementById('visitorType');

            if (toggle && links) {
                toggle.addEventListener('click', () => {
                    links.classList.toggle('open');
                });
            }

            if (idInput && visitorType) {
                const updateVisitorType = () => {
                    const isGuest = idInput.value.trim() === '';
                    visitorType.textContent = isGuest
                        ? 'Visitor type: Guest visitor (temporary guest ID will be generated)'
                        : 'Visitor type: Registered visitor';
                };

                idInput.addEventListener('input', updateVisitorType);
                updateVisitorType();
            }
        });
    </script>
</body>
</html>