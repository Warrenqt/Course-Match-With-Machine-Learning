<?php
require_once '../config.php';
require_once '../functions.php';

// Check admin login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$pdo = getDBConnection();

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Search
$search = trim($_GET['search'] ?? '');

try {
    // Get total count
    if ($search) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE name LIKE ? OR email LIKE ?");
        $stmt->execute(["%$search%", "%$search%"]);
    } else {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    }
    $totalUsers = $stmt->fetch()['total'];
    $totalPages = ceil($totalUsers / $perPage);

    // Get users with assessment status
    $sql = "
        SELECT 
            u.id,
            u.name,
            u.email,
            u.created_at,
            u.updated_at,
            ua.assessment_completed,
            ua.created_at as assessment_date,
            (SELECT COUNT(*) FROM quiz_results WHERE user_id = u.id) as quiz_count
        FROM users u
        LEFT JOIN user_assessments ua ON u.id = ua.user_id
    ";
    
    if ($search) {
        $sql .= " WHERE u.name LIKE ? OR u.email LIKE ?";
    }
    
    $sql .= " ORDER BY u.created_at DESC LIMIT $perPage OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    if ($search) {
        $stmt->execute(["%$search%", "%$search%"]);
    } else {
        $stmt->execute();
    }
    $users = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Admin users error: " . $e->getMessage());
    $users = [];
    $totalUsers = 0;
    $totalPages = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - CourseMatch Admin</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body {
            background: var(--galaxy-white);
        }

        .admin-nav {
            background: var(--space-dark);
            padding: var(--space-4) 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .admin-nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-nav-brand {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            color: var(--pure-white);
            font-family: var(--font-display);
            font-weight: 600;
            font-size: 1.1rem;
        }

        .admin-nav-brand span {
            background: var(--cosmic-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .admin-nav-links {
            display: flex;
            gap: var(--space-5);
            align-items: center;
        }

        .admin-nav-links a {
            color: var(--stardust-400);
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.2s;
        }

        .admin-nav-links a:hover,
        .admin-nav-links a.active {
            color: var(--pure-white);
        }

        .admin-nav-links .btn-logout {
            background: var(--error);
            color: var(--pure-white) !important;
            padding: var(--space-2) var(--space-4);
            border-radius: var(--radius-full);
            font-size: 0.85rem;
        }

        .admin-header {
            background: var(--twilight-gradient);
            padding: var(--space-8) 0;
            color: var(--pure-white);
        }

        .admin-header h1 {
            font-family: var(--font-display);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: var(--space-2);
        }

        .admin-content {
            padding: var(--space-8) 0;
        }

        /* Search Bar */
        .search-bar {
            display: flex;
            gap: var(--space-3);
            margin-bottom: var(--space-6);
        }

        .search-bar input {
            flex: 1;
            max-width: 400px;
        }

        /* Table */
        .users-table {
            background: var(--pure-white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            border: 1px solid var(--stardust-200);
        }

        .users-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .users-table th,
        .users-table td {
            padding: var(--space-4);
            text-align: left;
            border-bottom: 1px solid var(--stardust-100);
        }

        .users-table th {
            background: var(--stardust-100);
            font-family: var(--font-display);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--stardust-600);
        }

        .users-table td {
            font-size: 0.95rem;
            color: var(--space-dark);
        }

        .users-table tr:hover {
            background: var(--stardust-50, #F8FAFC);
        }

        .user-name {
            font-weight: 600;
        }

        .user-email {
            color: var(--stardust-500);
            font-size: 0.9rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: var(--space-1);
            padding: var(--space-1) var(--space-3);
            border-radius: var(--radius-full);
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-badge.completed {
            background: var(--success-light);
            color: var(--success);
        }

        .status-badge.pending {
            background: var(--warning-light);
            color: #B45309;
        }

        .quiz-count {
            font-family: var(--font-mono);
            color: var(--cosmic-purple);
            font-weight: 600;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: var(--space-2);
            margin-top: var(--space-6);
        }

        .pagination a,
        .pagination span {
            padding: var(--space-2) var(--space-4);
            border-radius: var(--radius-md);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .pagination a {
            background: var(--pure-white);
            color: var(--stardust-600);
            border: 1px solid var(--stardust-200);
        }

        .pagination a:hover {
            background: var(--stardust-100);
        }

        .pagination span.current {
            background: var(--cosmic-purple);
            color: var(--pure-white);
        }

        .empty-state {
            text-align: center;
            padding: var(--space-10);
            color: var(--stardust-500);
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: var(--space-4);
        }

        @media (max-width: 768px) {
            .users-table {
                overflow-x: auto;
            }

            .users-table table {
                min-width: 700px;
            }
        }
    </style>
</head>
<body>
    <!-- Admin Navigation -->
    <nav class="admin-nav">
        <div class="container">
            <div class="admin-nav-brand">
                🔐 <span>CourseMatch</span> Admin
            </div>
            <div class="admin-nav-links">
                <a href="dashboard.php">📊 Dashboard</a>
                <a href="users.php" class="active">👥 Users</a>
                <a href="courses.php">📈 Courses</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <header class="admin-header">
        <div class="container">
            <h1>👥 User Management</h1>
            <p>View all registered users and their activity status</p>
        </div>
    </header>

    <!-- Content -->
    <main class="admin-content">
        <div class="container">
            <!-- Search Bar -->
            <form class="search-bar" method="GET">
                <input type="text" name="search" class="form-input" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-stellar">🔍 Search</button>
                <?php if ($search): ?>
                    <a href="users.php" class="btn btn-ghost">Clear</a>
                <?php endif; ?>
            </form>

            <!-- Results Count -->
            <p style="color: var(--stardust-500); margin-bottom: var(--space-4);">
                Showing <?php echo count($users); ?> of <?php echo $totalUsers; ?> users
                <?php if ($search): ?>
                    matching "<?php echo htmlspecialchars($search); ?>"
                <?php endif; ?>
            </p>

            <!-- Users Table -->
            <div class="users-table">
                <?php if (empty($users)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">👤</div>
                        <h3>No users found</h3>
                        <p>Try a different search term</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Registered</th>
                                <th>Last Active</th>
                                <th>Assessment</th>
                                <th>Quizzes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="user-name"><?php echo htmlspecialchars($user['name']); ?></div>
                                    <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php 
                                    $lastActive = $user['updated_at'] ?? $user['created_at'];
                                    echo date('M d, Y', strtotime($lastActive)); 
                                    ?>
                                </td>
                                <td>
                                    <?php if ($user['assessment_completed']): ?>
                                        <span class="status-badge completed">✓ Completed</span>
                                    <?php else: ?>
                                        <span class="status-badge pending">○ Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="quiz-count"><?php echo $user['quiz_count']; ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">← Prev</a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Next →</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>

