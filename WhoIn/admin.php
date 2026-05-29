<?php
session_start();

// Database connection at the VERY TOP
$host = 'localhost';
$dbname = 'whoin';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

if (isset($_GET['stats']) && isset($_SESSION['admin_logged_in'])) {
    header('Content-Type: application/json');
    try {
        $stats = [
            'total_users' => (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'currently_inside' => (int) $pdo->query("SELECT COUNT(*) FROM logs WHERE sign_out_time IS NULL")->fetchColumn(),
            'todays_visits' => (int) $pdo->query("SELECT COUNT(*) FROM logs l INNER JOIN users u ON l.user_id = u.user_id WHERE DATE(l.sign_in_time) = CURDATE()")->fetchColumn(),
            'total_logs' => (int) $pdo->query("SELECT COUNT(*) FROM logs")->fetchColumn()
        ];
        echo json_encode($stats);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit();
}

// Hardcoded admin credentials
$valid_username = 'admin';
$valid_password = 'admin123';

// Check if admin is logging in
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    if ($_POST['username'] == $valid_username && $_POST['password'] == $valid_password) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $error = "Invalid credentials!";
    }
}

// Check if admin is logged out
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit();
}

// If not logged in, show login form
if (!isset($_SESSION['admin_logged_in'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>WhoIn — Admin Login</title>
        <meta name="description" content="Admin login for the WhoIn contact tracing system.">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <!-- Background Orbs -->
        <div class="hero-bg-orbs" style="position: fixed; inset: 0; z-index: 0;">
            <div class="hero-orb hero-orb-1"></div>
            <div class="hero-orb hero-orb-2"></div>
        </div>

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

        <div class="admin-login-page">
            <div class="glass-card glass-card-elevated admin-login-card" style="position: relative; z-index: 1;">
                <div class="card-header-icon" style="display: flex; align-items: center; justify-content: center;"><i class="fas fa-lock"></i></div>
                <div class="card-header-title">Admin Login</div>
                <div class="card-header-subtitle">Department of Computer Engineering</div>

                <?php if(isset($error)): ?>
                    <div class="admin-alert"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label class="form-label-dark" for="admin_username">Username</label>
                        <input type="text" name="username" id="admin_username" class="input-dark" required placeholder="Enter username">
                    </div>
                    <div class="form-group">
                        <label class="form-label-dark" for="admin_password">Password</label>
                        <input type="password" name="password" id="admin_password" class="input-dark" required placeholder="Enter password">
                    </div>
                    <button type="submit" name="login" class="btn-gradient" style="width: 100%; padding: 14px;">Login →</button>
                </form>

                <div class="admin-creds">Default: admin / admin123</div>

                <div style="text-align: center; margin-top: 1.5rem;">
                    <a href="index.php" class="btn-outline-dark">← Back to Home</a>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const toggle = document.getElementById('navToggle');
                const links = document.getElementById('navLinks');
                if (toggle && links) {
                    toggle.addEventListener('click', () => {
                        links.classList.toggle('open');
                    });
                }
            });
        </script>
    </body>
    </html>
    <?php
    exit();
}

// Process search if submitted
$search_results = [];
$search_type = '';
$search_term = '';

if (isset($_GET['search'])) {
    $search_term = isset($_GET['search_term']) ? '%' . $_GET['search_term'] . '%' : '%%';
    $search_type = isset($_GET['search_type']) ? $_GET['search_type'] : 'name';
    
    try {
        switch($search_type) {
            case 'name':
                $sql = "SELECT u.*, 
                        CASE WHEN l.sign_out_time IS NULL AND l.sign_in_time IS NOT NULL THEN 'Inside' ELSE 'Left' END as status,
                        MAX(l.sign_in_time) as last_sign_in
                        FROM users u 
                        LEFT JOIN logs l ON u.user_id = l.user_id 
                        WHERE u.first_name LIKE ? OR u.last_name LIKE ? 
                        GROUP BY u.user_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$search_term, $search_term]);
                $search_results = $stmt->fetchAll();
                break;
            case 'id_number':
                $sql = "SELECT u.*, 
                        CASE WHEN l.sign_out_time IS NULL AND l.sign_in_time IS NOT NULL THEN 'Inside' ELSE 'Left' END as status,
                        MAX(l.sign_in_time) as last_sign_in
                        FROM users u 
                        LEFT JOIN logs l ON u.user_id = l.user_id 
                        WHERE u.id_number LIKE ? 
                        GROUP BY u.user_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$search_term]);
                $search_results = $stmt->fetchAll();
                break;
            case 'city':
                $sql = "SELECT u.*, 
                        CASE WHEN l.sign_out_time IS NULL AND l.sign_in_time IS NOT NULL THEN 'Inside' ELSE 'Left' END as status,
                        MAX(l.sign_in_time) as last_sign_in
                        FROM users u 
                        LEFT JOIN logs l ON u.user_id = l.user_id 
                        WHERE u.city LIKE ? 
                        GROUP BY u.user_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$search_term]);
                $search_results = $stmt->fetchAll();
                break;
            case 'barangay':
                $sql = "SELECT u.*, 
                        CASE WHEN l.sign_out_time IS NULL AND l.sign_in_time IS NOT NULL THEN 'Inside' ELSE 'Left' END as status,
                        MAX(l.sign_in_time) as last_sign_in
                        FROM users u 
                        LEFT JOIN logs l ON u.user_id = l.user_id 
                        WHERE u.barangay LIKE ? 
                        GROUP BY u.user_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$search_term]);
                $search_results = $stmt->fetchAll();
                break;
            case 'province':
                $sql = "SELECT u.*, 
                        CASE WHEN l.sign_out_time IS NULL AND l.sign_in_time IS NOT NULL THEN 'Inside' ELSE 'Left' END as status,
                        MAX(l.sign_in_time) as last_sign_in
                        FROM users u 
                        LEFT JOIN logs l ON u.user_id = l.user_id 
                        WHERE u.province LIKE ? 
                        GROUP BY u.user_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$search_term]);
                $search_results = $stmt->fetchAll();
                break;
        }
    } catch(PDOException $e) {
        $search_error = "Search error: " . $e->getMessage();
    }
}

// Get date filter for logs
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : '7';
$date_condition = "";
if ($date_filter == 'today') {
    $date_condition = "WHERE DATE(l.sign_in_time) = CURDATE()";
} elseif ($date_filter == '7') {
    $date_condition = "WHERE l.sign_in_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
} elseif ($date_filter == '30') {
    $date_condition = "WHERE l.sign_in_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
}

try {
    $logs_sql = "SELECT u.first_name, u.last_name, u.id_number, l.sign_in_time, l.sign_out_time 
                 FROM logs l 
                 JOIN users u ON l.user_id = u.user_id 
                 $date_condition
                 ORDER BY l.sign_in_time DESC 
                 LIMIT 100";
    $logs_stmt = $pdo->query($logs_sql);
} catch(PDOException $e) {
    $logs_error = "Error loading logs: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhoIn - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Background Orbs -->
    <div class="hero-bg-orbs" style="position: fixed; inset: 0; z-index: 0;">
        <div class="hero-orb hero-orb-1"></div>
        <div class="hero-orb hero-orb-2"></div>
        <div class="hero-orb hero-orb-3" style="opacity: 0.05;"></div>
    </div>

    <div class="container-fluid" style="position: relative; z-index: 1;">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar-glass">
                <h4><i class="fas fa-shield-alt"></i> WhoIn Admin</h4>
                <nav class="nav flex-column">
                    <a class="nav-link" href="admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a class="nav-link" href="#search"><i class="fas fa-search"></i> Search Users</a>
                    <a class="nav-link" href="#logs"><i class="fas fa-history"></i> Activity Logs</a>
                    <a class="nav-link" href="index.php"><i class="fas fa-home"></i> Back to Portal</a>
                    <a class="nav-link text-danger" href="?logout=1"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 content" style="padding: var(--space-2xl);">
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <h2 style="font-weight: 800; text-transform: uppercase; letter-spacing: -0.01em;">
                        <i class="fas fa-chart-line" style="color: var(--color-cyan); margin-right: 10px;"></i>
                        Admin Dashboard
                    </h2>
                    <div style="font-size: var(--font-size-sm); color: var(--text-muted); font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">
                        Welcome, Administrator
                    </div>
                </div>
                
                <!-- Stats Row -->
                <div class="row mb-5">
                    <div class="col-md-3">
                        <div class="stat-card-glass">
                            <h5>Total Users</h5>
                            <h3><?php echo $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(); ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card-glass cyan">
                            <h5>Currently Inside</h5>
                            <h3 id="currentlyInsideCount"><?php echo $pdo->query("SELECT COUNT(*) FROM logs WHERE sign_out_time IS NULL")->fetchColumn(); ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card-glass amber">
                            <h5>Today's Visits</h5>
                            <h3 id="todayVisitsCount"><?php echo $pdo->query("SELECT COUNT(*) FROM logs l INNER JOIN users u ON l.user_id = u.user_id WHERE DATE(l.sign_in_time) = CURDATE()")->fetchColumn(); ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card-glass">
                            <h5>Total Logs</h5>
                            <h3><?php echo $pdo->query("SELECT COUNT(*) FROM logs")->fetchColumn(); ?></h3>
                        </div>
                    </div>
                </div>
                
                <!-- Search Section -->
                <div class="glass-card mb-5" id="search" style="padding: var(--space-xl);">
                    <div class="card-header-bar" style="margin-bottom: var(--space-lg);">
                        <div class="card-header-icon indigo" style="width: 36px; height: 36px; font-size: 1rem;"><i class="fas fa-search"></i></div>
                        <div>
                            <div class="card-header-title" style="font-size: var(--font-size-lg);">Search Users</div>
                        </div>
                    </div>
                    
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <select name="search_type" class="select-dark">
                                <option value="name" <?php echo $search_type == 'name' ? 'selected' : ''; ?>>By Name</option>
                                <option value="id_number" <?php echo $search_type == 'id_number' ? 'selected' : ''; ?>>By ID Number</option>
                                <option value="city" <?php echo $search_type == 'city' ? 'selected' : ''; ?>>By City</option>
                                <option value="barangay" <?php echo $search_type == 'barangay' ? 'selected' : ''; ?>>By Barangay</option>
                                <option value="province" <?php echo $search_type == 'province' ? 'selected' : ''; ?>>By Province</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="search_term" class="input-dark" placeholder="Enter search term..." value="<?php echo isset($_GET['search_term']) ? htmlspecialchars(str_replace('%', '', $_GET['search_term'])) : ''; ?>">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" name="search" class="btn-gradient w-100" style="padding: 11px 20px; font-size: var(--font-size-sm);">🔍 Search</button>
                        </div>
                    </form>
                    
                    <?php if (!empty($search_results)): ?>
                    <div class="mt-5">
                        <h6 style="font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; font-size: var(--font-size-sm); margin-bottom: var(--space-md); color: var(--text-secondary);">
                            Search Results (<?php echo count($search_results); ?> found)
                        </h6>
                        <div class="table-glass-container">
                            <table class="table-glass">
                                <thead>
                                    <tr>
                                        <th>ID Number</th>
                                        <th>Full Name</th>
                                        <th>Address</th>
                                        <th>Contact</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($search_results as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['id_number'] ?? 'Guest'); ?></td>
                                        <td style="font-weight: 600; color: var(--text-primary);"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['barangay'] . ', ' . $user['city'] . ', ' . $user['province']); ?></td>
                                        <td><?php echo htmlspecialchars($user['contact_number']); ?></td>
                                        <td>
                                            <?php if ($user['status'] == 'Inside'): ?>
                                                <span class="badge-glass-inside">
                                                    <span class="pulse-dot"></span>Inside
                                                </span>
                                            <?php else: ?>
                                                <span class="badge-glass-left">Left</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php elseif(isset($_GET['search'])): ?>
                    <div class="alert-dark warning" style="margin-top: var(--space-lg);">No results found.</div>
                    <?php endif; ?>
                </div>
                
                <!-- Logs Section -->
                <div class="glass-card" id="logs" style="padding: var(--space-xl);">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="margin-bottom: var(--space-lg);">
                        <div class="card-header-bar" style="margin-bottom: 0;">
                            <div class="card-header-icon indigo" style="width: 36px; height: 36px; font-size: 1rem;"><i class="fas fa-history"></i></div>
                            <div>
                                <div class="card-header-title" style="font-size: var(--font-size-lg);">Activity Logs</div>
                            </div>
                        </div>
                        <form method="GET" class="d-flex gap-2">
                            <select name="date_filter" class="select-dark" style="width: auto;">
                                <option value="today" <?php echo $date_filter == 'today' ? 'selected' : ''; ?>>Today</option>
                                <option value="7" <?php echo $date_filter == '7' ? 'selected' : ''; ?>>Last 7 Days</option>
                                <option value="30" <?php echo $date_filter == '30' ? 'selected' : ''; ?>>Last 30 Days</option>
                                <option value="all" <?php echo $date_filter == 'all' ? 'selected' : ''; ?>>All Time</option>
                            </select>
                            <button type="submit" class="btn-outline-dark" style="padding: 10px 20px;">Filter</button>
                        </form>
                    </div>
                    
                    <div>
                        <?php if(isset($logs_error)): ?>
                            <div class="alert-dark warning"><?php echo $logs_error; ?></div>
                        <?php else: ?>
                        <div class="table-glass-container">
                            <table class="table-glass">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>ID Number</th>
                                        <th>Sign In Time</th>
                                        <th>Sign Out Time</th>
                                        <th>Duration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $has_logs = false;
                                    while($log = $logs_stmt->fetch(PDO::FETCH_ASSOC)): 
                                        $has_logs = true;
                                        $sign_in = new DateTime($log['sign_in_time']);
                                        $sign_out = $log['sign_out_time'] ? new DateTime($log['sign_out_time']) : null;
                                        $duration = $sign_out ? $sign_in->diff($sign_out)->format('%h hrs %i min') : 'Still inside';
                                    ?>
                                    <tr>
                                        <td style="font-weight: 600; color: var(--text-primary);"><?php echo htmlspecialchars($log['first_name'] . ' ' . $log['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($log['id_number'] ?? 'Guest'); ?></td>
                                        <td><?php echo date('M d, Y - h:i A', strtotime($log['sign_in_time'])); ?></td>
                                        <td>
                                            <?php if ($log['sign_out_time']): ?>
                                                <?php echo date('M d, Y - h:i A', strtotime($log['sign_out_time'])); ?>
                                            <?php else: ?>
                                                <span class="badge-glass-inside">
                                                    <span class="pulse-dot"></span>Inside
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="font-weight: 500;"><?php echo $duration; ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                    <?php if(!$has_logs): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted" style="padding: var(--space-2xl) !important;">No logs found</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const todayCountEl = document.getElementById('todayVisitsCount');
            const insideCountEl = document.getElementById('currentlyInsideCount');

            const updateStats = () => {
                fetch('admin.php?stats=1')
                    .then(response => response.json())
                    .then(data => {
                        if (data.todays_visits !== undefined && todayCountEl) {
                            todayCountEl.textContent = data.todays_visits;
                        }
                        if (data.currently_inside !== undefined && insideCountEl) {
                            insideCountEl.textContent = data.currently_inside;
                        }
                    })
                    .catch(() => {
                        // Polling errors are ignored
                    });
            };

            updateStats();
            setInterval(updateStats, 10000);
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>