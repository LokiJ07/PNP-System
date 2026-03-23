<?php
// =====================================================
// FILE: admin/activity_logs.php
// PURPOSE: Display system activity logs and activities summary
// REMOVED: Pending and Rejected stats (auto-approved)
// =====================================================

session_start();
require_once '../config/db_connect.php';
requireAdmin();

// Get filter parameters
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$action = $_GET['action'] ?? '';
$table = $_GET['table'] ?? '';
$from_date = $_GET['from_date'] ?? date('Y-m-d', strtotime('-7 days'));
$to_date = $_GET['to_date'] ?? date('Y-m-d');
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
$search = $_GET['search'] ?? '';

// Get all users for filter dropdown
$users = $conn->query("SELECT user_id, rank, first_name, last_name FROM users ORDER BY last_name, first_name");

// Get distinct actions for filter
$actions = $conn->query("SELECT DISTINCT action FROM activity_logs ORDER BY action");

// Get distinct tables for filter
$tables = $conn->query("SELECT DISTINCT table_name FROM activity_logs ORDER BY table_name");

// =========================================
// ACTIVITIES SUMMARY
// =========================================

// Today's activities count
$today_activities = $conn->query("
    SELECT COUNT(*) as count 
    FROM activity_logs 
    WHERE DATE(created_at) = CURDATE()
")->fetch_assoc()['count'];

// This week's activities
$week_activities = $conn->query("
    SELECT COUNT(*) as count 
    FROM activity_logs 
    WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)
")->fetch_assoc()['count'];

// This month's activities
$month_activities = $conn->query("
    SELECT COUNT(*) as count 
    FROM activity_logs 
    WHERE MONTH(created_at) = MONTH(CURDATE()) 
    AND YEAR(created_at) = YEAR(CURDATE())
")->fetch_assoc()['count'];

// Activities by type (last 7 days)
$activities_by_type = $conn->query("
    SELECT 
        action,
        COUNT(*) as count,
        DATE(created_at) as date
    FROM activity_logs
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY action, DATE(created_at)
    ORDER BY date DESC, count DESC
    LIMIT 15
");

// Top active users (last 30 days)
$top_users = $conn->query("
    SELECT 
        l.user_id,
        CONCAT(u.rank, ' ', u.first_name, ' ', u.last_name) as user_name,
        u.badge_number,
        COUNT(*) as activity_count
    FROM activity_logs l
    JOIN users u ON l.user_id = u.user_id
    WHERE l.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY l.user_id
    ORDER BY activity_count DESC
    LIMIT 5
");

// Build query conditions for main logs
$conditions = [];
$params = [];
$types = "";

if ($user_id > 0) {
    $conditions[] = "l.user_id = ?";
    $params[] = $user_id;
    $types .= "i";
}

if (!empty($action)) {
    $conditions[] = "l.action = ?";
    $params[] = $action;
    $types .= "s";
}

if (!empty($table)) {
    $conditions[] = "l.table_name = ?";
    $params[] = $table;
    $types .= "s";
}

if (!empty($from_date)) {
    $conditions[] = "DATE(l.created_at) >= ?";
    $params[] = $from_date;
    $types .= "s";
}

if (!empty($to_date)) {
    $conditions[] = "DATE(l.created_at) <= ?";
    $params[] = $to_date;
    $types .= "s";
}

if (!empty($search)) {
    $conditions[] = "(l.details LIKE ? OR l.action LIKE ? OR l.table_name LIKE ? OR l.ip_address LIKE ? OR CONCAT(u.rank, ' ', u.first_name, ' ', u.last_name) LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "sssss";
}

$where_clause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM activity_logs l LEFT JOIN users u ON l.user_id = u.user_id $where_clause";
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_logs = $count_stmt->get_result()->fetch_assoc()['total'];

// Get logs with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$query = "
    SELECT l.*, 
           CONCAT(u.rank, ' ', u.first_name, ' ', u.last_name) as user_name,
           u.badge_number
    FROM activity_logs l
    LEFT JOIN users u ON l.user_id = u.user_id
    $where_clause
    ORDER BY l.created_at DESC
    LIMIT ? OFFSET ?
";

$log_params = $params;
$log_params[] = $limit;
$log_params[] = $offset;
$log_types = $types . "ii";

$stmt = $conn->prepare($query);
if (!empty($log_params)) {
    $stmt->bind_param($log_types, ...$log_params);
}
$stmt->execute();
$logs = $stmt->get_result();

// Admin info for sidebar
$admin_name = $_SESSION['full_name'] ?? 'Admin';
$admin_email = $_SESSION['email'] ?? 'admin@pnp.gov.ph';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../image/pnplogo.png">
    <title>PNP | Activity Logs</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* Dropdown styles */
        .dropdown-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        .dropdown.active .dropdown-content {
            max-height: 300px;
            transition: max-height 0.5s ease-in;
        }
        .rotate-180 {
            transform: rotate(180deg);
            transition: transform 0.3s ease;
        }
        
        /* Card hover effects */
        .stat-card {
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.2);
        }
        
        /* Sidebar scrollbar */
        .sidebar-scroll {
            scrollbar-width: thin;
            scrollbar-color: #1e4a6a #08324f;
        }
        .sidebar-scroll::-webkit-scrollbar {
            width: 6px;
        }
        .sidebar-scroll::-webkit-scrollbar-track {
            background: #08324f;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background-color: #1e4a6a;
            border-radius: 20px;
        }
        
        /* Mobile menu */
        @media (max-width: 768px) {
            .sidebar-mobile {
                position: fixed;
                left: -100%;
                transition: left 0.3s ease;
                z-index: 50;
                width: 280px;
                height: 100vh;
            }
            .sidebar-mobile.open {
                left: 0;
            }
            .main-content-mobile {
                width: 100%;
                margin-left: 0;
            }
        }
        
        /* Table styles */
        .log-row {
            transition: all 0.2s ease;
        }
        .log-row:hover {
            background-color: #f0f7ff !important;
            transform: translateX(2px);
        }
        .action-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-align: center;
            min-width: 70px;
        }
        .filter-card {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 1rem;
        }
        .stat-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: white;
            border-radius: 50px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        /* Activity type colors */
        .activity-login { background-color: #e6f7e6; color: #2e7d32; }
        .activity-logout { background-color: #f5f5f5; color: #616161; }
        .activity-submit { background-color: #e3f2fd; color: #1565c0; }
        .activity-approve { background-color: #e8f5e9; color: #2e7d32; }
        .activity-update { background-color: #fff8e1; color: #f57c00; }
        .activity-delete { background-color: #ffebee; color: #c62828; }
        .activity-register { background-color: #f3e5f5; color: #7b1fa2; }
    </style>
</head>
<body class="flex flex-col md:flex-row bg-[#08324f] min-h-screen">

    <!-- Mobile Menu Button -->
    <button id="mobileMenuBtn" class="md:hidden fixed top-4 left-4 z-50 bg-[#1e4a6a] text-white p-3 rounded-lg shadow-lg">
        <i class="fas fa-bars text-xl"></i>
    </button>

    <!-- Mobile Menu Overlay -->
    <div id="menuOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden" onclick="closeMobileMenu()"></div>

    <!-- Sidebar -->
    <div id="sidebar" class="w-full md:w-[260px] bg-[#08324f] text-white h-screen overflow-y-auto sidebar-scroll sidebar-mobile fixed top-0 left-[-100%] md:left-0 md:sticky z-50 transition-all duration-300 ease-in-out">
        
        <button id="closeSidebar" class="md:hidden absolute top-4 right-4 text-white text-xl">
            <i class="fas fa-times"></i>
        </button>

        <!-- Logo and Title -->
        <div class="flex items-center gap-3 p-5 border-b border-[#1e4a6a] sticky top-0 bg-[#08324f] z-10">
            <img src="../image/pnplogo.png" class="w-10 h-10 object-contain" alt="PNP Logo">
            <div>
                <h2 class="text-lg font-semibold leading-tight">PNP Operation</h2>
                <p class="text-xs text-yellow-400">Admin Panel</p>
            </div>
        </div>

        <!-- Admin Info -->
        <div class="bg-[#1e4a6a] mx-4 my-4 p-4 rounded-lg text-center shadow-lg">
            <div class="w-16 h-16 bg-yellow-400 rounded-full mx-auto mb-3 flex items-center justify-center text-[#08324f] text-2xl font-bold">
                <?php echo substr($admin_name, 0, 1); ?>
            </div>
            <p class="font-medium text-yellow-400"><?php echo $admin_name; ?></p>
            <p class="text-xs text-gray-300 mt-1 break-all"><?php echo $admin_email; ?></p>
        </div>

        <!-- Navigation Menu -->
        <ul class="space-y-1 px-3 pb-5">
            <li><a href="admin_dashboard.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1e4a6a] transition"><i class="fas fa-tachometer-alt w-5"></i> Dashboard</a></li>
            <li><a href="checkpoint.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1e4a6a] transition"><i class="fas fa-map-marker-alt w-5"></i> Checkpoint</a></li>
            
            <li class="dropdown">
                <div class="flex items-center justify-between p-3 rounded-lg hover:bg-[#1e4a6a] cursor-pointer transition" onclick="toggleDropdown(this)">
                    <div class="flex items-center gap-3"><i class="fas fa-walking w-5"></i> Patrol</div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300"></i>
                </div>
                <ul class="dropdown-content pl-4 ml-4 space-y-1 border-l border-[#1e4a6a]">
                    <li><a href="footpatrol.php" class="block p-2 text-sm hover:bg-[#1e4a6a] rounded-lg transition">Foot Patrol</a></li>
                    <li><a href="mobilepatrol.php" class="block p-2 text-sm hover:bg-[#1e4a6a] rounded-lg transition">Mobile Patrol</a></li>
                    <li><a href="motorpatrol.php" class="block p-2 text-sm hover:bg-[#1e4a6a] rounded-lg transition">Motor Patrol</a></li>
                </ul>
            </li>
            
            <li class="dropdown">
                <div class="flex items-center justify-between p-3 rounded-lg hover:bg-[#1e4a6a] cursor-pointer transition" onclick="toggleDropdown(this)">
                    <div class="flex items-center gap-3"><i class="fas fa-shield-alt w-5"></i> Oplan</div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300"></i>
                </div>
                <ul class="dropdown-content pl-4 ml-4 space-y-1 border-l border-[#1e4a6a]">
                    <li><a href="oplanbakal.php" class="block p-2 text-sm hover:bg-[#1e4a6a] rounded-lg transition">Oplan Bakal</a></li>
                    <li><a href="oplansita.php" class="block p-2 text-sm hover:bg-[#1e4a6a] rounded-lg transition">Oplan Sita</a></li>
                </ul>
            </li>
            
            <li><a href="admin_users.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1e4a6a] transition"><i class="fas fa-users w-5"></i> Users</a></li>
            <li><a href="accomplishment_report.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1e4a6a] transition"><i class="fas fa-file-alt w-5"></i> Accomplishment Report</a></li>
            <li><a href="all_reports.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1e4a6a] transition"><i class="fas fa-folder-open w-5"></i> All Reports</a></li>
            <li class="bg-[#1e4a6a] rounded-lg"><a href="activity_logs.php" class="flex items-center gap-3 p-3"><i class="fas fa-history w-5 text-yellow-400"></i> Activity Logs</a></li>
            
            <li class="my-4 border-t border-[#1e4a6a]"></li>
            <li><a href="../logout.php" class="flex items-center gap-3 p-3 rounded-lg bg-red-600 hover:bg-red-700 transition"><i class="fas fa-sign-out-alt w-5"></i> Logout</a></li>
            
            <li class="mt-6 text-center text-xs text-gray-400">
                <p>PNP Manolo Fortich v2.0</p>
                <p class="mt-1">© 2026 All Rights Reserved</p>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-4 md:p-6 bg-[#eef2f6] overflow-y-auto min-h-screen main-content-mobile">
        
        <!-- Header -->
        <div class="bg-white p-4 md:p-6 rounded-lg shadow-md mb-6 border-l-4 border-yellow-400 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-[#08324f] flex items-center gap-2">
                    <i class="fas fa-history text-yellow-500"></i>
                    Activity Logs
                </h2>
                <p class="text-sm text-gray-600 mt-1">Track all system activities</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <span class="stat-badge">
                    <i class="fas fa-calendar-day text-blue-500"></i>
                    <span>Today: <strong><?php echo number_format($today_activities); ?></strong></span>
                </span>
                <span class="stat-badge">
                    <i class="fas fa-database text-green-500"></i>
                    <span>Total: <strong><?php echo number_format($total_logs); ?></strong></span>
                </span>
            </div>
        </div>

        <!-- ===== ACTIVITIES SUMMARY SECTION ===== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
            
            <!-- Time Period Stats -->
            <div class="bg-white rounded-lg shadow-md p-5">
                <h3 class="text-sm font-semibold text-[#08324f] mb-4 flex items-center gap-2 border-b pb-2">
                    <i class="fas fa-chart-line text-yellow-500"></i>
                    Activity Summary
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600"><i class="fas fa-sun text-orange-400 mr-2"></i>Today</span>
                        <span class="font-bold text-lg text-[#08324f]"><?php echo number_format($today_activities); ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600"><i class="fas fa-calendar-week text-blue-500 mr-2"></i>This Week</span>
                        <span class="font-bold text-lg text-[#08324f]"><?php echo number_format($week_activities); ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600"><i class="fas fa-calendar-alt text-purple-500 mr-2"></i>This Month</span>
                        <span class="font-bold text-lg text-[#08324f]"><?php echo number_format($month_activities); ?></span>
                    </div>
                </div>
            </div>

            <!-- Top Active Users -->
            <div class="bg-white rounded-lg shadow-md p-5">
                <h3 class="text-sm font-semibold text-[#08324f] mb-4 flex items-center gap-2 border-b pb-2">
                    <i class="fas fa-users text-yellow-500"></i>
                    Most Active Users (30 days)
                </h3>
                <?php if ($top_users->num_rows == 0): ?>
                <p class="text-gray-500 text-sm text-center py-4">No activity in the last 30 days</p>
                <?php else: ?>
                <div class="space-y-3">
                    <?php 
                    $rank = 1;
                    while ($user = $top_users->fetch_assoc()): 
                    ?>
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 rounded-full bg-gradient-to-br from-[#08324f] to-[#1e4a6a] text-white flex items-center justify-center text-xs font-bold">
                            <?php echo $rank++; ?>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium"><?php echo $user['user_name']; ?></p>
                            <p class="text-xs text-gray-400"><?php echo $user['badge_number']; ?></p>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-[#08324f]"><?php echo $user['activity_count']; ?></span>
                            <span class="text-xs text-gray-500 block">actions</span>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Recent Activity Types -->
            <div class="bg-white rounded-lg shadow-md p-5">
                <h3 class="text-sm font-semibold text-[#08324f] mb-4 flex items-center gap-2 border-b pb-2">
                    <i class="fas fa-tasks text-yellow-500"></i>
                    Recent Actions (7 days)
                </h3>
                <?php if ($activities_by_type->num_rows == 0): ?>
                <p class="text-gray-500 text-sm text-center py-4">No activity in the last 7 days</p>
                <?php else: ?>
                <div class="space-y-2 max-h-48 overflow-y-auto pr-2">
                    <?php while ($act = $activities_by_type->fetch_assoc()): 
                        $action_class = '';
                        if (strpos($act['action'], 'LOGIN') !== false) $action_class = 'bg-green-100 text-green-700';
                        elseif (strpos($act['action'], 'SUBMIT') !== false) $action_class = 'bg-blue-100 text-blue-700';
                        elseif (strpos($act['action'], 'APPROVE') !== false) $action_class = 'bg-green-100 text-green-700';
                        elseif (strpos($act['action'], 'UPDATE') !== false) $action_class = 'bg-yellow-100 text-yellow-700';
                        elseif (strpos($act['action'], 'DELETE') !== false) $action_class = 'bg-red-100 text-red-700';
                        elseif (strpos($act['action'], 'REGISTER') !== false) $action_class = 'bg-purple-100 text-purple-700';
                        else $action_class = 'bg-gray-100 text-gray-600';
                    ?>
                    <div class="flex justify-between items-center text-sm p-2 rounded <?php echo $action_class; ?>">
                        <span class="truncate max-w-[150px]"><?php echo $act['action']; ?></span>
                        <div class="flex items-center gap-2">
                            <span class="font-bold"><?php echo $act['count']; ?></span>
                            <span class="text-xs opacity-75"><?php echo date('M d', strtotime($act['date'])); ?></span>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-card">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <!-- Search -->
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">🔍 Search</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search by user, action, details..." 
                           class="w-full p-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#1f6fb2] focus:border-transparent">
                </div>
                
                <!-- User Filter -->
                <div class="w-[180px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">👤 User</label>
                    <select name="user_id" class="w-full p-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#1f6fb2]">
                        <option value="0">All Users</option>
                        <?php 
                        $users->data_seek(0);
                        while ($user = $users->fetch_assoc()): 
                        ?>
                        <option value="<?php echo $user['user_id']; ?>" <?php echo $user_id == $user['user_id'] ? 'selected' : ''; ?>>
                            <?php echo $user['rank'] . ' ' . $user['first_name'] . ' ' . $user['last_name']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Action Filter -->
                <div class="w-[140px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">⚡ Action</label>
                    <select name="action" class="w-full p-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#1f6fb2]">
                        <option value="">All Actions</option>
                        <?php 
                        $actions->data_seek(0);
                        while ($a = $actions->fetch_assoc()): 
                        ?>
                        <option value="<?php echo $a['action']; ?>" <?php echo $action == $a['action'] ? 'selected' : ''; ?>>
                            <?php echo $a['action']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Table Filter -->
                <div class="w-[130px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">📁 Table</label>
                    <select name="table" class="w-full p-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#1f6fb2]">
                        <option value="">All Tables</option>
                        <?php 
                        $tables->data_seek(0);
                        while ($t = $tables->fetch_assoc()): 
                        ?>
                        <option value="<?php echo $t['table_name']; ?>" <?php echo $table == $t['table_name'] ? 'selected' : ''; ?>>
                            <?php echo $t['table_name']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Date Range -->
                <div class="w-[130px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">📅 From</label>
                    <input type="date" name="from_date" value="<?php echo $from_date; ?>" class="w-full p-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#1f6fb2]">
                </div>

                <div class="w-[130px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">📅 To</label>
                    <input type="date" name="to_date" value="<?php echo $to_date; ?>" class="w-full p-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#1f6fb2]">
                </div>

                <!-- Buttons -->
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-[#1f6fb2] text-white rounded-lg hover:bg-[#0a3d62] transition text-sm flex items-center gap-1">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="activity_logs.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm flex items-center gap-1">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Results Info Bar -->
        <div class="flex flex-wrap justify-between items-center mb-3 text-sm text-gray-600">
            <div class="flex items-center gap-2">
                <i class="fas fa-list mr-1"></i>
                Showing <strong><?php echo $logs->num_rows; ?></strong> of <strong><?php echo number_format($total_logs); ?></strong> logs
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs">Show:</span>
                <select onchange="changeLimit(this.value)" class="border border-gray-300 rounded px-2 py-1 text-xs">
                    <option value="25" <?php echo $limit == 25 ? 'selected' : ''; ?>>25 per page</option>
                    <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50 per page</option>
                    <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100 per page</option>
                    <option value="200" <?php echo $limit == 200 ? 'selected' : ''; ?>>200 per page</option>
                </select>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gradient-to-r from-[#08324f] to-[#1a4b6a] text-white text-sm">
                            <th class="p-3 text-left rounded-tl-lg">Time</th>
                            <th class="p-3 text-left">User</th>
                            <th class="p-3 text-left">Action</th>
                            <th class="p-3 text-left">Table</th>
                            <th class="p-3 text-left">ID</th>
                            <th class="p-3 text-left">Details</th>
                            <th class="p-3 text-left rounded-tr-lg">IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($logs->num_rows == 0): ?>
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-500 bg-gray-50">
                                <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                                <p class="text-lg">No activity logs found</p>
                                <p class="text-sm mt-1">Try adjusting your filters</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php while ($log = $logs->fetch_assoc()): 
                            // Determine action color
                            $action_class = 'bg-gray-100 text-gray-700';
                            if (strpos($log['action'], 'LOGIN') !== false) $action_class = 'bg-green-100 text-green-700';
                            elseif (strpos($log['action'], 'LOGOUT') !== false) $action_class = 'bg-gray-100 text-gray-700';
                            elseif (strpos($log['action'], 'SUBMIT') !== false) $action_class = 'bg-blue-100 text-blue-700';
                            elseif (strpos($log['action'], 'APPROVE') !== false) $action_class = 'bg-green-100 text-green-700';
                            elseif (strpos($log['action'], 'UPDATE') !== false) $action_class = 'bg-yellow-100 text-yellow-700';
                            elseif (strpos($log['action'], 'DELETE') !== false) $action_class = 'bg-red-100 text-red-700';
                            elseif (strpos($log['action'], 'REGISTER') !== false) $action_class = 'bg-purple-100 text-purple-700';
                            
                            // Format time
                            $log_time = date('M d, H:i', strtotime($log['created_at']));
                            $is_today = date('Y-m-d', strtotime($log['created_at'])) == date('Y-m-d');
                            $time_display = $is_today ? 'Today, ' . date('H:i', strtotime($log['created_at'])) : $log_time;
                        ?>
                        <tr class="border-b border-gray-100 log-row">
                            <td class="p-3 whitespace-nowrap text-sm">
                                <span class="font-medium"><?php echo $time_display; ?></span>
                                <?php if ($is_today): ?>
                                <span class="ml-1 text-xs text-green-500">●</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3">
                                <?php if ($log['user_name']): ?>
                                    <div class="font-medium text-sm"><?php echo $log['user_name']; ?></div>
                                    <div class="text-xs text-gray-400"><?php echo $log['badge_number']; ?></div>
                                <?php else: ?>
                                    <span class="text-sm text-gray-400 italic">System</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3">
                                <span class="action-badge <?php echo $action_class; ?>">
                                    <?php echo $log['action']; ?>
                                </span>
                            </td>
                            <td class="p-3">
                                <span class="text-xs bg-gray-100 px-2 py-1 rounded font-mono">
                                    <?php echo $log['table_name']; ?>
                                </span>
                            </td>
                            <td class="p-3 text-center text-sm font-mono"><?php echo $log['record_id'] ?: '—'; ?></td>
                            <td class="p-3 max-w-xs">
                                <div class="text-sm truncate" title="<?php echo htmlspecialchars($log['details']); ?>">
                                    <?php echo $log['details'] ? htmlspecialchars($log['details']) : '<span class="text-gray-400">—</span>'; ?>
                                </div>
                            </td>
                            <td class="p-3 font-mono text-xs text-gray-500"><?php echo $log['ip_address'] ?: '—'; ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_logs > $limit): ?>
            <div class="p-4 bg-gray-50 border-t border-gray-200 flex flex-wrap items-center justify-between gap-3">
                <div class="text-sm text-gray-600">
                    Page <?php echo $page; ?> of <?php echo ceil($total_logs / $limit); ?>
                </div>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                       class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 text-sm flex items-center gap-1 transition">
                        <i class="fas fa-chevron-left text-xs"></i> Previous
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($page < ceil($total_logs / $limit)): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                       class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 text-sm flex items-center gap-1 transition">
                        Next <i class="fas fa-chevron-right text-xs"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Legend / Tips -->
        <div class="mt-4 text-xs text-gray-500 flex flex-wrap items-center gap-4">
            <span><i class="fas fa-circle text-green-500 mr-1"></i> Today's logs</span>
            <span><i class="fas fa-circle text-blue-500 mr-1"></i> Submissions</span>
            <span><i class="fas fa-circle text-green-500 mr-1"></i> Approvals</span>
            <span><i class="fas fa-circle text-yellow-500 mr-1"></i> Updates</span>
            <span class="ml-auto">
                <i class="fas fa-info-circle text-gray-400 mr-1"></i> 
                All reports are auto-approved
            </span>
        </div>
    </div>

    <script>
        // Mobile Menu Functions
        const sidebar = document.getElementById('sidebar');
        const menuBtn = document.getElementById('mobileMenuBtn');
        const closeBtn = document.getElementById('closeSidebar');
        const overlay = document.getElementById('menuOverlay');

        function openMobileMenu() {
            sidebar.classList.add('open');
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeMobileMenu() {
            sidebar.classList.remove('open');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }

        if (menuBtn) menuBtn.addEventListener('click', openMobileMenu);
        if (closeBtn) closeBtn.addEventListener('click', closeMobileMenu);
        if (overlay) overlay.addEventListener('click', closeMobileMenu);
        
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) closeMobileMenu();
        });

        // Dropdown Functions
        function toggleDropdown(element) {
            const parent = element.closest('.dropdown');
            parent.classList.toggle('active');
            const arrow = element.querySelector('.fa-chevron-down');
            if (arrow) arrow.classList.toggle('rotate-180');
        }

        document.querySelectorAll('.dropdown > div').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const current = this.closest('.dropdown');
                document.querySelectorAll('.dropdown').forEach(drop => {
                    if (drop !== current) {
                        drop.classList.remove('active');
                        const arrow = drop.querySelector('.fa-chevron-down');
                        if (arrow) arrow.classList.remove('rotate-180');
                    }
                });
            });
        });

        // Change items per page
        function changeLimit(limit) {
            const url = new URL(window.location.href);
            url.searchParams.set('limit', limit);
            url.searchParams.set('page', 1);
            window.location.href = url.toString();
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>