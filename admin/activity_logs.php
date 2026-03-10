<?php
// =====================================================
// FILE: admin/activity_logs.php
// PURPOSE: Display all system activity logs for admin
// SIMPLIFIED: Table only, more user-friendly
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

// Build query conditions
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
    $conditions[] = "(l.details LIKE ? OR l.action LIKE ? OR l.table_name LIKE ? OR l.ip_address LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ssss";
}

$where_clause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM activity_logs l $where_clause";
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

// Get summary counts for badges
$today_count = $conn->query("SELECT COUNT(*) as count FROM activity_logs WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'];
$pending_actions = $conn->query("SELECT COUNT(*) as count FROM activity_logs WHERE action LIKE '%PENDING%' OR action LIKE '%SUBMIT%'")->fetch_assoc()['count'];
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
        .dropdown-content { display: none; }
        .dropdown.active .dropdown-content { display: block; }
        .rotate-180 { transform: rotate(180deg); }
        
        /* Custom styles for better table readability */
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
    </style>
</head>
<body class="flex bg-[#0a3d62]">

    <!-- Sidebar (same as before) -->
    <div class="w-[240px] h-screen bg-[#08324f] text-white p-5 sticky top-0 overflow-y-auto">
        <div class="flex items-center gap-3 mb-6 pb-3 border-b border-[#1a4b6d]">
            <img src="../image/pnplogo.png" class="w-8 h-8 object-contain" alt="PNP Logo">
            <h2 class="text-xl font-semibold">PNP Admin</h2>
        </div>

        <!-- Admin Info -->
        <div class="bg-[#1e4a6a] p-3 rounded-lg mb-4 text-center">
            <p class="text-sm text-yellow-400 font-medium"><?php echo $_SESSION['full_name'] ?? 'Admin'; ?></p>
            <p class="text-xs text-gray-300 mt-1"><?php echo $_SESSION['email'] ?? 'admin@pnp.gov.ph'; ?></p>
        </div>

        <ul class="space-y-1">
            <li class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer">
                <a href="admin_dashboard.php" class="text-white no-underline block"><i class="fas fa-tachometer-alt mr-3"></i> Dashboard</a>
            </li>
            <li class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer">
                <a href="checkpoint.php" class="text-white no-underline block"><i class="fas fa-map-marker-alt mr-3"></i> Checkpoint</a>
            </li>
            <li class="dropdown">
                <div class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer flex items-center justify-between" onclick="toggleDropdown(this)">
                    <span><i class="fas fa-walking mr-3"></i> Patrol</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300"></i>
                </div>
                <ul class="pl-8 mt-1 space-y-1 dropdown-content">
                    <li class="py-2 px-3 text-sm hover:bg-[#0a3d62] rounded"><a href="footpatrol.php" class="text-white no-underline block">Foot Patrol</a></li>
                    <li class="py-2 px-3 text-sm hover:bg-[#0a3d62] rounded"><a href="mobilepatrol.php" class="text-white no-underline block">Mobile Patrol</a></li>
                    <li class="py-2 px-3 text-sm hover:bg-[#0a3d62] rounded"><a href="motorpatrol.php" class="text-white no-underline block">Motorcycle Patrol</a></li>
                </ul>
            </li>
            <li class="dropdown">
                <div class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer flex items-center justify-between" onclick="toggleDropdown(this)">
                    <span><i class="fas fa-shield-alt mr-3"></i> Oplan Bakal / Sita</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300"></i>
                </div>
                <ul class="pl-8 mt-1 space-y-1 dropdown-content">
                    <li class="py-2 px-3 text-sm hover:bg-[#0a3d62] rounded"><a href="oplanbakal.php" class="text-white no-underline block">Oplan Bakal</a></li>
                    <li class="py-2 px-3 text-sm hover:bg-[#0a3d62] rounded"><a href="oplansita.php" class="text-white no-underline block">Oplan Sita</a></li>
                </ul>
            </li>
            <li class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer">
                <a href="admin_users.php" class="text-white no-underline block"><i class="fas fa-users mr-3"></i> Users</a>
            </li>
            <li class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer">
                <a href="accomplishment_report.php" class="text-white no-underline block"><i class="fas fa-file-alt mr-3"></i> Accomplishment Report</a>
            </li>
            <li class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer">
                <a href="all_reports.php" class="text-white no-underline block"><i class="fas fa-list mr-3"></i> All Reports</a>
            </li>
            <li class="p-3 rounded bg-[#0a3d62] border-l-4 border-yellow-400">
                <a href="activity_logs.php" class="text-white no-underline block"><i class="fas fa-history mr-3"></i> Activity Logs</a>
            </li>
            <li class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer mt-5 pt-4 border-t border-[#1a4b6d]">
                <a href="../logout.php" class="text-white no-underline block"><i class="fas fa-sign-out-alt mr-3"></i> Logout</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-6 bg-[#eef2f6] overflow-y-auto h-screen">
        
        <!-- Simple Header with Stats -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="text-2xl font-bold text-[#08324f]">📋 Activity Logs</h2>
                <p class="text-sm text-gray-600">Track all system activities</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <span class="stat-badge">
                    <i class="fas fa-calendar-day text-blue-500"></i>
                    <span>Today: <strong><?php echo $today_count; ?></strong></span>
                </span>
                <span class="stat-badge">
                    <i class="fas fa-clock text-yellow-500"></i>
                    <span>Pending: <strong><?php echo $pending_actions; ?></strong></span>
                </span>
                <span class="stat-badge">
                    <i class="fas fa-database text-green-500"></i>
                    <span>Total: <strong><?php echo number_format($total_logs); ?></strong></span>
                </span>
            </div>
        </div>

        <!-- Simple Filter Bar -->
        <div class="filter-card">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <!-- Quick Filters -->
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">🔍 Search</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search details, action, table..." 
                           class="w-full p-2 border border-gray-300 rounded-lg text-sm">
                </div>
                
                <div class="w-[150px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">👤 User</label>
                    <select name="user_id" class="w-full p-2 border border-gray-300 rounded-lg text-sm">
                        <option value="0">All Users</option>
                        <?php while ($user = $users->fetch_assoc()): ?>
                        <option value="<?php echo $user['user_id']; ?>" <?php echo $user_id == $user['user_id'] ? 'selected' : ''; ?>>
                            <?php echo $user['rank'] . ' ' . $user['first_name'] . ' ' . $user['last_name']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="w-[130px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">⚡ Action</label>
                    <select name="action" class="w-full p-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">All</option>
                        <?php while ($a = $actions->fetch_assoc()): ?>
                        <option value="<?php echo $a['action']; ?>" <?php echo $action == $a['action'] ? 'selected' : ''; ?>>
                            <?php echo $a['action']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="w-[120px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">📁 Table</label>
                    <select name="table" class="w-full p-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">All</option>
                        <?php while ($t = $tables->fetch_assoc()): ?>
                        <option value="<?php echo $t['table_name']; ?>" <?php echo $table == $t['table_name'] ? 'selected' : ''; ?>>
                            <?php echo $t['table_name']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="w-[130px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">📅 From</label>
                    <input type="date" name="from_date" value="<?php echo $from_date; ?>" class="w-full p-2 border border-gray-300 rounded-lg text-sm">
                </div>

                <div class="w-[130px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">📅 To</label>
                    <input type="date" name="to_date" value="<?php echo $to_date; ?>" class="w-full p-2 border border-gray-300 rounded-lg text-sm">
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-[#1f6fb2] text-white rounded-lg hover:bg-[#0a3d62] transition text-sm">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    <a href="activity_logs.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Results Info Bar -->
        <div class="flex justify-between items-center mb-3 text-sm text-gray-600">
            <div>
                <i class="fas fa-list mr-2"></i>
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

        <!-- Logs Table - Clean and Readable -->
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
                            $action_color = 'bg-gray-100 text-gray-700';
                            if (strpos($log['action'], 'LOGIN') !== false) $action_color = 'bg-green-100 text-green-700';
                            elseif (strpos($log['action'], 'LOGOUT') !== false) $action_color = 'bg-gray-100 text-gray-700';
                            elseif (strpos($log['action'], 'SUBMIT') !== false) $action_color = 'bg-blue-100 text-blue-700';
                            elseif (strpos($log['action'], 'APPROVE') !== false) $action_color = 'bg-emerald-100 text-emerald-700';
                            elseif (strpos($log['action'], 'REJECT') !== false) $action_color = 'bg-red-100 text-red-700';
                            elseif (strpos($log['action'], 'UPDATE') !== false) $action_color = 'bg-yellow-100 text-yellow-700';
                            elseif (strpos($log['action'], 'DELETE') !== false) $action_color = 'bg-red-100 text-red-700';
                            
                            // Format time nicely
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
                                <span class="action-badge <?php echo $action_color; ?>">
                                    <?php echo $log['action']; ?>
                                </span>
                            </td>
                            <td class="p-3">
                                <span class="text-xs bg-gray-100 px-2 py-1 rounded font-mono">
                                    <?php echo $log['table_name']; ?>
                                </span>
                            </td>
                            <td class="p-3 text-center text-sm"><?php echo $log['record_id'] ?: '—'; ?></td>
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

            <!-- Simple Pagination -->
            <?php if ($total_logs > $limit): ?>
            <div class="p-4 bg-gray-50 border-t border-gray-200 flex flex-wrap items-center justify-between gap-3">
                <div class="text-sm text-gray-600">
                    Page <?php echo $page; ?> of <?php echo ceil($total_logs / $limit); ?>
                </div>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                       class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 text-sm flex items-center gap-1">
                        <i class="fas fa-chevron-left text-xs"></i> Previous
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($page < ceil($total_logs / $limit)): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                       class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 text-sm flex items-center gap-1">
                        Next <i class="fas fa-chevron-right text-xs"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Quick Tips -->
        <div class="mt-4 text-xs text-gray-500 flex items-center gap-4">
            <span><i class="fas fa-circle text-green-500 mr-1"></i> Today's logs</span>
            <span><i class="fas fa-square text-blue-100 mr-1"></i> Submissions</span>
            <span><i class="fas fa-square text-green-100 mr-1"></i> Approvals</span>
            <span><i class="fas fa-square text-yellow-100 mr-1"></i> Updates</span>
            <span><i class="fas fa-square text-red-100 mr-1"></i> Rejects/Deletes</span>
        </div>
    </div>

    <script>
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