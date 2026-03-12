<?php
// =====================================================
// FILE: admin/admin_dashboard.php
// PURPOSE: Admin dashboard with auto-approved reports
// IMPROVED: Clean UI, mobile responsive, removed pending/rejected
// =====================================================

require_once '../config/db_connect.php';
requireAdmin(); // Function to ensure only admins can access

// Get statistics from database
$stats = [];

// ===== USER STATISTICS =====
$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$stats['total_users'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'user' AND account_status = 'active'");
$stats['active_users'] = $result->fetch_assoc()['total'];

// ===== TOTAL REPORTS (all approved) =====
$result = $conn->query("SELECT COUNT(*) as total FROM patrol_activities WHERE status = 'approved'");
$stats['total_patrols'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM checkpoint_activities WHERE status = 'approved'");
$stats['total_checkpoints'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM oplan_activities WHERE status = 'approved'");
$stats['total_oplans'] = $result->fetch_assoc()['total'];

// ===== PATROL BREAKDOWN =====
$result = $conn->query("SELECT COUNT(*) as total FROM patrol_activities WHERE patrol_type = 'Foot Patrol' AND status = 'approved'");
$stats['foot_patrols'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM patrol_activities WHERE patrol_type = 'Mobile Patrol' AND status = 'approved'");
$stats['mobile_patrols'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM patrol_activities WHERE patrol_type = 'Motorcycle Patrol' AND status = 'approved'");
$stats['motor_patrols'] = $result->fetch_assoc()['total'];

// ===== CHECKPOINT STATISTICS =====
$result = $conn->query("SELECT SUM(border_control_ops) as total FROM checkpoint_activities WHERE status = 'approved'");
$stats['border_ops'] = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("SELECT SUM(mobile_checkpoint_ops) as total FROM checkpoint_activities WHERE status = 'approved'");
$stats['mobile_checkpoint_ops'] = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("SELECT SUM(tct_ovr_accomplishment) as total FROM checkpoint_activities WHERE status = 'approved'");
$stats['tct_ovr'] = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("SELECT SUM(arrested_accomplishment) as total FROM checkpoint_activities WHERE status = 'approved'");
$stats['checkpoint_arrests'] = $result->fetch_assoc()['total'] ?? 0;

// ===== CHECKPOINT DISPOSITION STATISTICS =====
$result = $conn->query("SELECT SUM(fixed_count) as total FROM checkpoint_activities WHERE status = 'approved'");
$stats['checkpoint_fixed'] = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("SELECT SUM(fined_count) as total FROM checkpoint_activities WHERE status = 'approved'");
$stats['checkpoint_fined'] = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("SELECT SUM(warned_count) as total FROM checkpoint_activities WHERE status = 'approved'");
$stats['checkpoint_warned'] = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("SELECT SUM(charged_count) as total FROM checkpoint_activities WHERE status = 'approved'");
$stats['checkpoint_charged'] = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("SELECT SUM(community_service) as total FROM checkpoint_activities WHERE status = 'approved'");
$stats['checkpoint_community'] = $result->fetch_assoc()['total'] ?? 0;

// ===== OPLAN STATISTICS =====
$result = $conn->query("SELECT COUNT(*) as total FROM oplan_activities WHERE oplan_type = 'Oplan Bakal' AND status = 'approved'");
$stats['oplan_bakal'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM oplan_activities WHERE oplan_type = 'Oplan Sita' AND status = 'approved'");
$stats['oplan_sita'] = $result->fetch_assoc()['total'];

// Oplan Bakal specifics
$result = $conn->query("SELECT SUM(firearms_seized) as total FROM oplan_activities WHERE oplan_type = 'Oplan Bakal' AND status = 'approved'");
$stats['firearms_seized'] = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("SELECT SUM(firearms_crs) as total FROM oplan_activities WHERE oplan_type = 'Oplan Bakal' AND status = 'approved'");
$stats['firearms_crs'] = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("SELECT SUM(fas_deposit) as total FROM oplan_activities WHERE oplan_type = 'Oplan Bakal' AND status = 'approved'");
$stats['fas_deposit'] = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("SELECT SUM(renewed_fas) as total FROM oplan_activities WHERE oplan_type = 'Oplan Bakal' AND status = 'approved'");
$stats['renewed_fas'] = $result->fetch_assoc()['total'] ?? 0;

// Oplan Sita specifics
$result = $conn->query("SELECT SUM(contraband_kg) as total FROM oplan_activities WHERE oplan_type = 'Oplan Sita' AND status = 'approved'");
$stats['contraband'] = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("SELECT SUM(kontra_boga) as total FROM oplan_activities WHERE oplan_type = 'Oplan Sita' AND status = 'approved'");
$stats['kontra_boga'] = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("SELECT SUM(anti_vaping) as total FROM oplan_activities WHERE oplan_type = 'Oplan Sita' AND status = 'approved'");
$stats['anti_vaping'] = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("SELECT SUM(house_visitations) as total FROM oplan_activities WHERE oplan_type = 'Oplan Sita' AND status = 'approved'");
$stats['sita_house_visits'] = $result->fetch_assoc()['total'] ?? 0;

// Common oplan fields
$result = $conn->query("SELECT SUM(arrests_made) as total FROM oplan_activities WHERE status = 'approved'");
$stats['oplan_arrests'] = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("SELECT SUM(house_visitations) as total FROM oplan_activities WHERE status = 'approved'");
$stats['oplan_house_visits'] = $result->fetch_assoc()['total'] ?? 0;

// ===== OPLAN DISPOSITION STATISTICS =====
$result = $conn->query("SELECT SUM(fixed_count) as total FROM oplan_activities WHERE status = 'approved'");
$stats['oplan_fixed'] = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("SELECT SUM(fined_count) as total FROM oplan_activities WHERE status = 'approved'");
$stats['oplan_fined'] = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("SELECT SUM(warned_count) as total FROM oplan_activities WHERE status = 'approved'");
$stats['oplan_warned'] = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("SELECT SUM(charged_count) as total FROM oplan_activities WHERE status = 'approved'");
$stats['oplan_charged'] = $result->fetch_assoc()['total'] ?? 0;

// ===== VIOLATIONS STATISTICS (from all tables) =====
$result = $conn->query("
    SELECT 
        (SELECT SUM(drinking_violations) FROM patrol_activities WHERE status = 'approved') +
        (SELECT SUM(drinking_violations) FROM checkpoint_activities WHERE status = 'approved') +
        (SELECT SUM(drinking_violations) FROM oplan_activities WHERE status = 'approved') as total
");
$stats['drinking_violations'] = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("
    SELECT 
        (SELECT SUM(smoking_violations) FROM patrol_activities WHERE status = 'approved') +
        (SELECT SUM(smoking_violations) FROM checkpoint_activities WHERE status = 'approved') +
        (SELECT SUM(smoking_violations) FROM oplan_activities WHERE status = 'approved') as total
");
$stats['smoking_violations'] = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("
    SELECT 
        (SELECT SUM(halfnaked_violations) FROM patrol_activities WHERE status = 'approved') +
        (SELECT SUM(halfnaked_violations) FROM checkpoint_activities WHERE status = 'approved') +
        (SELECT SUM(halfnaked_violations) FROM oplan_activities WHERE status = 'approved') as total
");
$stats['halfnaked_violations'] = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("
    SELECT 
        (SELECT SUM(curfew_violations) FROM patrol_activities WHERE status = 'approved') +
        (SELECT SUM(curfew_violations) FROM checkpoint_activities WHERE status = 'approved') +
        (SELECT SUM(curfew_violations) FROM oplan_activities WHERE status = 'approved') as total
");
$stats['curfew_violations'] = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("
    SELECT 
        (SELECT SUM(vandalism_violations) FROM patrol_activities WHERE status = 'approved') +
        (SELECT SUM(vandalism_violations) FROM checkpoint_activities WHERE status = 'approved') +
        (SELECT SUM(vandalism_violations) FROM oplan_activities WHERE status = 'approved') as total
");
$stats['vandalism_violations'] = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("
    SELECT 
        (SELECT SUM(other_violations) FROM patrol_activities WHERE status = 'approved') +
        (SELECT SUM(other_violations) FROM checkpoint_activities WHERE status = 'approved') +
        (SELECT SUM(other_violations) FROM oplan_activities WHERE status = 'approved') as total
");
$stats['other_violations'] = $result->fetch_assoc()['total'] ?? 0;

// Total violations
$stats['total_violations'] = $stats['drinking_violations'] + $stats['smoking_violations'] + 
                             $stats['halfnaked_violations'] + $stats['curfew_violations'] + 
                             $stats['vandalism_violations'] + $stats['other_violations'];

// ===== TOTAL REPORTS =====
$total_reports = $stats['total_patrols'] + $stats['total_checkpoints'] + $stats['total_oplans'];

// ===== RECENT ACTIVITIES =====
$recent = [];

// Recent patrols
$patrols = $conn->query("
    SELECT p.patrol_id as id, 'patrol' as type, p.patrol_type as subtype, 
           CONCAT(u.rank, ' ', u.first_name, ' ', u.last_name) as officer_name,
           b.barangay_name, p.specific_location, p.patrol_date, p.patrol_time,
           p.submitted_at
    FROM patrol_activities p
    JOIN users u ON p.user_id = u.user_id
    JOIN barangays b ON p.barangay_id = b.barangay_id
    WHERE p.status = 'approved'
    ORDER BY p.submitted_at DESC
    LIMIT 5
");
while ($row = $patrols->fetch_assoc()) {
    $row['status'] = 'approved';
    $recent[] = $row;
}

// Recent checkpoints
$checkpoints = $conn->query("
    SELECT c.checkpoint_id as id, 'checkpoint' as type, 'Checkpoint' as subtype,
           CONCAT(u.rank, ' ', u.first_name, ' ', u.last_name) as officer_name,
           b.barangay_name, c.specific_location, c.checkpoint_date, c.checkpoint_time,
           c.submitted_at
    FROM checkpoint_activities c
    JOIN users u ON c.user_id = u.user_id
    JOIN barangays b ON c.barangay_id = b.barangay_id
    WHERE c.status = 'approved'
    ORDER BY c.submitted_at DESC
    LIMIT 5
");
while ($row = $checkpoints->fetch_assoc()) {
    $row['status'] = 'approved';
    $recent[] = $row;
}

// Recent oplans
$oplans = $conn->query("
    SELECT o.oplan_id as id, 'oplan' as type, o.oplan_type as subtype,
           CONCAT(u.rank, ' ', u.first_name, ' ', u.last_name) as officer_name,
           b.barangay_name, o.specific_location, o.oplan_date, o.oplan_time,
           o.submitted_at
    FROM oplan_activities o
    JOIN users u ON o.user_id = u.user_id
    JOIN barangays b ON o.barangay_id = b.barangay_id
    WHERE o.status = 'approved'
    ORDER BY o.submitted_at DESC
    LIMIT 5
");
while ($row = $oplans->fetch_assoc()) {
    $row['status'] = 'approved';
    $recent[] = $row;
}

// Sort recent activities
usort($recent, function($a, $b) {
    return strtotime($b['submitted_at']) - strtotime($a['submitted_at']);
});
$recent = array_slice($recent, 0, 8);

// ===== TOP PERFORMING OFFICERS =====
$top_officers = $conn->query("
    SELECT u.user_id, u.rank, u.first_name, u.last_name,
           (SELECT COUNT(*) FROM patrol_activities WHERE user_id = u.user_id AND status = 'approved') as patrol_count,
           (SELECT COUNT(*) FROM checkpoint_activities WHERE user_id = u.user_id AND status = 'approved') as checkpoint_count,
           (SELECT COUNT(*) FROM oplan_activities WHERE user_id = u.user_id AND status = 'approved') as oplan_count,
           ((SELECT COUNT(*) FROM patrol_activities WHERE user_id = u.user_id AND status = 'approved') +
            (SELECT COUNT(*) FROM checkpoint_activities WHERE user_id = u.user_id AND status = 'approved') +
            (SELECT COUNT(*) FROM oplan_activities WHERE user_id = u.user_id AND status = 'approved')) as total_activities
    FROM users u
    WHERE u.role = 'user'
    ORDER BY total_activities DESC
    LIMIT 3
");

// Admin info
$admin_name = $_SESSION['full_name'] ?? 'Admin';
$admin_email = $_SESSION['email'] ?? 'admin@pnp.gov.ph';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../image/pnplogo.png">
    <title>PNP | Admin Dashboard</title>
    
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
            border-left-width: 4px;
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
        
        /* Gradient Cards */
        .gradient-patrol {
            background: linear-gradient(135deg, #08324f 0%, #1e4a6a 100%);
        }
        .gradient-checkpoint {
            background: linear-gradient(135deg, #c41e3a 0%, #dc3545 100%);
        }
        
        /* Auto-approve badge */
        .auto-approve-badge {
            background: #10b981;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 600;
        }
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
            <li class="bg-[#1e4a6a] rounded-lg">
                <a href="admin_dashboard.php" class="flex items-center gap-3 p-3">
                    <i class="fas fa-tachometer-alt w-5 text-yellow-400"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="checkpoint.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1e4a6a] transition">
                    <i class="fas fa-map-marker-alt w-5"></i>
                    <span>Checkpoint</span>
                </a>
            </li>
            <li class="dropdown">
                <div class="flex items-center justify-between p-3 rounded-lg hover:bg-[#1e4a6a] cursor-pointer transition" onclick="toggleDropdown(this)">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-walking w-5"></i>
                        <span>Patrol</span>
                    </div>
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
                    <div class="flex items-center gap-3">
                        <i class="fas fa-shield-alt w-5"></i>
                        <span>Oplan</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300"></i>
                </div>
                <ul class="dropdown-content pl-4 ml-4 space-y-1 border-l border-[#1e4a6a]">
                    <li><a href="oplanbakal.php" class="block p-2 text-sm hover:bg-[#1e4a6a] rounded-lg transition">Oplan Bakal</a></li>
                    <li><a href="oplansita.php" class="block p-2 text-sm hover:bg-[#1e4a6a] rounded-lg transition">Oplan Sita</a></li>
                </ul>
            </li>
            <li>
                <a href="admin_users.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1e4a6a] transition">
                    <i class="fas fa-users w-5"></i>
                    <span>Users</span>
                </a>
            </li>
            <li>
                <a href="accomplishment_report.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1e4a6a] transition">
                    <i class="fas fa-file-alt w-5"></i>
                    <span>Accomplishment Report</span>
                </a>
            </li>
            <li>
                <a href="all_reports.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1e4a6a] transition">
                    <i class="fas fa-folder-open w-5"></i>
                    <span>All Reports</span>
                </a>
            </li>
            <li>
                <a href="activity_logs.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1e4a6a] transition">
                    <i class="fas fa-history w-5"></i>
                    <span>Activity Logs</span>
                </a>
            </li>
            
            <li class="my-4 border-t border-[#1e4a6a]"></li>
            <li>
                <a href="../logout.php" class="flex items-center gap-3 p-3 rounded-lg bg-red-600 hover:bg-red-700 transition">
                    <i class="fas fa-sign-out-alt w-5"></i>
                    <span>Logout</span>
                </a>
            </li>
            
            <li class="mt-6 text-center text-xs text-gray-400">
                <p>PNP Manolo Fortich v2.0</p>
                <p class="mt-1">© 2026 All Rights Reserved</p>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-4 md:p-6 lg:p-8 bg-[#eef2f6] overflow-y-auto min-h-screen main-content-mobile">
        
 <!-- Header -->
<div class="bg-white p-4 md:p-6 rounded-lg shadow-md mb-6 border-l-4 border-yellow-400 flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
    <div>
        <h2 class="text-xl md:text-2xl font-bold text-[#08324f] flex items-center gap-2">
            <i class="fas fa-chart-line text-yellow-500"></i>
            Dashboard Overview
        </h2>
        <p class="text-sm text-gray-600 mt-1">Welcome back, <?php echo $admin_name; ?>. All reports are auto-approved.</p>
    </div>
    
    <div class="flex items-center gap-3">
        <!-- Notification Bell -->
        <div class="relative">
            <button id="notificationBell" class="relative p-2 text-gray-600 hover:text-[#08324f] transition" onclick="toggleNotifications()">
                <i class="fas fa-bell text-xl md:text-2xl"></i>
                <span id="notificationBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">0</span>
            </button>
            
            <!-- Notification Dropdown -->
            <div id="notificationDropdown" class="absolute right-0 mt-2 w-80 md:w-96 bg-white rounded-lg shadow-xl border border-gray-200 hidden z-50">
                <div class="p-3 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-800">Notifications</h3>
                    <button onclick="markAllAsRead()" class="text-xs text-blue-600 hover:text-blue-800">Mark all as read</button>
                </div>
                <div id="notificationList" class="max-h-96 overflow-y-auto">
                    <div class="p-4 text-center text-gray-500">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Loading...
                    </div>
                </div>
                <div class="p-2 border-t border-gray-200 text-center">
                    <a href="notifications.php" class="text-xs text-blue-600 hover:text-blue-800">View all notifications</a>
                </div>
            </div>
        </div>
    </div>
</div>

        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 mb-6">
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-[#08324f]">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Total Personnel</p>
                        <p class="text-2xl md:text-3xl font-bold text-[#08324f] mt-1"><?php echo $stats['total_users']; ?></p>
                    </div>
                    <div class="w-10 h-10 bg-[#08324f] bg-opacity-10 rounded-full flex items-center justify-center">
                        <i class="fas fa-users text-[#08324f]"></i>
                    </div>
                </div>
                <p class="text-xs text-green-600 mt-2">
                    <i class="fas fa-user-check mr-1"></i> <?php echo $stats['active_users']; ?> active
                </p>
            </div>
            
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-[#c41e3a]">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Total Operations</p>
                        <p class="text-2xl md:text-3xl font-bold text-[#08324f] mt-1"><?php echo $total_reports; ?></p>
                    </div>
                    <div class="w-10 h-10 bg-[#c41e3a] bg-opacity-10 rounded-full flex items-center justify-center">
                        <i class="fas fa-calendar-check text-[#c41e3a]"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">All reports approved</p>
            </div>
            
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-green-600">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Total Violations</p>
                        <p class="text-2xl md:text-3xl font-bold text-[#08324f] mt-1"><?php echo $stats['total_violations']; ?></p>
                    </div>
                    <div class="w-10 h-10 bg-green-600 bg-opacity-10 rounded-full flex items-center justify-center">
                        <i class="fas fa-gavel text-green-600"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">From all operations</p>
            </div>
            
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-yellow-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Total Arrests</p>
                        <p class="text-2xl md:text-3xl font-bold text-[#08324f] mt-1"><?php echo $stats['checkpoint_arrests'] + $stats['oplan_arrests']; ?></p>
                    </div>
                    <div class="w-10 h-10 bg-yellow-500 bg-opacity-10 rounded-full flex items-center justify-center">
                        <i class="fas fa-handcuffs text-yellow-600"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">Checkpoints + Oplans</p>
            </div>
        </div>

        <!-- VIOLATIONS STATISTICS -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-[#08324f] mb-3 flex items-center gap-2">
                <i class="fas fa-gavel text-yellow-500"></i> Ordinance Violations
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2">
                <div class="bg-white p-3 rounded-lg shadow-sm text-center">
                    <p class="text-xs text-gray-500">Drinking</p>
                    <p class="text-xl font-bold text-[#08324f]"><?php echo $stats['drinking_violations']; ?></p>
                </div>
                <div class="bg-white p-3 rounded-lg shadow-sm text-center">
                    <p class="text-xs text-gray-500">Smoking</p>
                    <p class="text-xl font-bold text-[#08324f]"><?php echo $stats['smoking_violations']; ?></p>
                </div>
                <div class="bg-white p-3 rounded-lg shadow-sm text-center">
                    <p class="text-xs text-gray-500">Half-Naked</p>
                    <p class="text-xl font-bold text-[#08324f]"><?php echo $stats['halfnaked_violations']; ?></p>
                </div>
                <div class="bg-white p-3 rounded-lg shadow-sm text-center">
                    <p class="text-xs text-gray-500">Curfew</p>
                    <p class="text-xl font-bold text-[#08324f]"><?php echo $stats['curfew_violations']; ?></p>
                </div>
                <div class="bg-white p-3 rounded-lg shadow-sm text-center">
                    <p class="text-xs text-gray-500">Vandalism</p>
                    <p class="text-xl font-bold text-[#08324f]"><?php echo $stats['vandalism_violations']; ?></p>
                </div>
                <div class="bg-white p-3 rounded-lg shadow-sm text-center">
                    <p class="text-xs text-gray-500">Other</p>
                    <p class="text-xl font-bold text-[#08324f]"><?php echo $stats['other_violations']; ?></p>
                </div>
            </div>
        </div>

        <!-- Patrol Statistics -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-[#08324f] mb-3 flex items-center gap-2">
                <i class="fas fa-walking text-yellow-500"></i> Patrol Operations
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="stat-card gradient-patrol p-4 rounded-lg shadow-lg text-white">
                    <div class="flex items-center justify-between mb-2">
                        <i class="fas fa-walking text-2xl opacity-80"></i>
                        <span class="text-xs bg-white/20 px-2 py-1 rounded-full"><?php echo $stats['foot_patrols']; ?></span>
                    </div>
                    <p class="text-2xl font-bold"><?php echo $stats['foot_patrols']; ?></p>
                    <p class="text-sm opacity-90">Foot Patrols</p>
                </div>
                
                <div class="stat-card gradient-patrol p-4 rounded-lg shadow-lg text-white">
                    <div class="flex items-center justify-between mb-2">
                        <i class="fas fa-car text-2xl opacity-80"></i>
                        <span class="text-xs bg-white/20 px-2 py-1 rounded-full"><?php echo $stats['mobile_patrols']; ?></span>
                    </div>
                    <p class="text-2xl font-bold"><?php echo $stats['mobile_patrols']; ?></p>
                    <p class="text-sm opacity-90">Mobile Patrols</p>
                </div>
                
                <div class="stat-card gradient-patrol p-4 rounded-lg shadow-lg text-white">
                    <div class="flex items-center justify-between mb-2">
                        <i class="fas fa-motorcycle text-2xl opacity-80"></i>
                        <span class="text-xs bg-white/20 px-2 py-1 rounded-full"><?php echo $stats['motor_patrols']; ?></span>
                    </div>
                    <p class="text-2xl font-bold"><?php echo $stats['motor_patrols']; ?></p>
                    <p class="text-sm opacity-90">Motor Patrols</p>
                </div>
            </div>
        </div>

        <!-- Checkpoint & Oplan Stats -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
            <!-- Checkpoint Stats -->
            <div>
                <h3 class="text-lg font-semibold text-[#08324f] mb-3 flex items-center gap-2">
                    <i class="fas fa-map-marker-alt text-yellow-500"></i> Checkpoint Operations
                </h3>
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div class="stat-card gradient-checkpoint p-4 rounded-lg shadow-lg text-white">
                        <i class="fas fa-border-all text-2xl opacity-80 mb-2 block"></i>
                        <p class="text-2xl font-bold"><?php echo $stats['border_ops']; ?></p>
                        <p class="text-sm opacity-90">Border Control</p>
                    </div>
                    <div class="stat-card gradient-checkpoint p-4 rounded-lg shadow-lg text-white">
                        <i class="fas fa-truck text-2xl opacity-80 mb-2 block"></i>
                        <p class="text-2xl font-bold"><?php echo $stats['mobile_checkpoint_ops']; ?></p>
                        <p class="text-sm opacity-90">Mobile Checkpoint</p>
                    </div>
                    <div class="stat-card gradient-checkpoint p-4 rounded-lg shadow-lg text-white">
                        <i class="fas fa-file-alt text-2xl opacity-80 mb-2 block"></i>
                        <p class="text-2xl font-bold"><?php echo $stats['tct_ovr']; ?></p>
                        <p class="text-sm opacity-90">TCT/OVR</p>
                    </div>
                    <div class="stat-card gradient-checkpoint p-4 rounded-lg shadow-lg text-white">
                        <i class="fas fa-gavel text-2xl opacity-80 mb-2 block"></i>
                        <p class="text-2xl font-bold"><?php echo $stats['checkpoint_arrests']; ?></p>
                        <p class="text-sm opacity-90">Arrests</p>
                    </div>
                </div>
                
                <!-- Checkpoint Disposition -->
                <div class="bg-white p-3 rounded-lg shadow-sm">
                    <h4 class="text-sm font-semibold text-[#08324f] mb-2">Disposition</h4>
                    <div class="grid grid-cols-5 gap-1 text-center">
                        <div>
                            <p class="text-xs text-gray-500">Fixed</p>
                            <p class="font-bold text-[#08324f]"><?php echo $stats['checkpoint_fixed']; ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Fined</p>
                            <p class="font-bold text-[#08324f]"><?php echo $stats['checkpoint_fined']; ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Warned</p>
                            <p class="font-bold text-[#08324f]"><?php echo $stats['checkpoint_warned']; ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Charged</p>
                            <p class="font-bold text-[#08324f]"><?php echo $stats['checkpoint_charged']; ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Community</p>
                            <p class="font-bold text-[#08324f]"><?php echo $stats['checkpoint_community']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Oplan Stats -->
            <div>
                <h3 class="text-lg font-semibold text-[#08324f] mb-3 flex items-center gap-2">
                    <i class="fas fa-shield-alt text-yellow-500"></i> Oplan Operations
                </h3>
                
                <!-- Oplan Bakal -->
                <div class="mb-3">
                    <h4 class="text-sm font-semibold text-[#08324f] mb-2">Oplan Bakal</h4>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="bg-white p-3 rounded-lg shadow-sm">
                            <p class="text-xs text-gray-500">Operations</p>
                            <p class="text-xl font-bold text-[#08324f]"><?php echo $stats['oplan_bakal']; ?></p>
                        </div>
                        <div class="bg-white p-3 rounded-lg shadow-sm">
                            <p class="text-xs text-gray-500">Firearms</p>
                            <p class="text-xl font-bold text-red-600"><?php echo $stats['firearms_seized']; ?></p>
                        </div>
                        <div class="bg-white p-3 rounded-lg shadow-sm">
                            <p class="text-xs text-gray-500">FAS Deposit</p>
                            <p class="text-xl font-bold text-[#08324f]"><?php echo $stats['fas_deposit']; ?></p>
                        </div>
                        <div class="bg-white p-3 rounded-lg shadow-sm">
                            <p class="text-xs text-gray-500">Renewed FAS</p>
                            <p class="text-xl font-bold text-[#08324f]"><?php echo $stats['renewed_fas']; ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Oplan Sita -->
                <div class="mb-3">
                    <h4 class="text-sm font-semibold text-[#08324f] mb-2">Oplan Sita</h4>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="bg-white p-3 rounded-lg shadow-sm">
                            <p class="text-xs text-gray-500">Ops</p>
                            <p class="text-xl font-bold text-[#08324f]"><?php echo $stats['oplan_sita']; ?></p>
                        </div>
                        <div class="bg-white p-3 rounded-lg shadow-sm">
                            <p class="text-xs text-gray-500">Kontra Boga</p>
                            <p class="text-xl font-bold text-[#08324f]"><?php echo $stats['kontra_boga']; ?></p>
                        </div>
                        <div class="bg-white p-3 rounded-lg shadow-sm">
                            <p class="text-xs text-gray-500">Anti-Vape</p>
                            <p class="text-xl font-bold text-[#08324f]"><?php echo $stats['anti_vaping']; ?></p>
                        </div>
                        <div class="bg-white p-3 rounded-lg shadow-sm">
                            <p class="text-xs text-gray-500">Contraband</p>
                            <p class="text-xl font-bold text-orange-600"><?php echo number_format($stats['contraband'], 2); ?> kg</p>
                        </div>
                        <div class="bg-white p-3 rounded-lg shadow-sm">
                            <p class="text-xs text-gray-500">House Visits</p>
                            <p class="text-xl font-bold text-[#08324f]"><?php echo $stats['sita_house_visits']; ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Oplan Arrests & Disposition -->
                <div class="grid grid-cols-2 gap-2">
                    <div class="bg-white p-3 rounded-lg shadow-sm">
                        <p class="text-xs text-gray-500">Total Arrests</p>
                        <p class="text-xl font-bold text-red-600"><?php echo $stats['oplan_arrests']; ?></p>
                    </div>
                    <div class="bg-white p-3 rounded-lg shadow-sm">
                        <p class="text-xs text-gray-500">House Visits</p>
                        <p class="text-xl font-bold text-[#08324f]"><?php echo $stats['oplan_house_visits']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Officers -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-[#08324f] mb-3 flex items-center gap-2">
                <i class="fas fa-crown text-yellow-500"></i> Top Performing Officers
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <?php 
                $counter = 1;
                if ($top_officers->num_rows > 0):
                    while ($officer = $top_officers->fetch_assoc()): 
                ?>
                <div class="bg-white p-4 rounded-lg shadow-md flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-[#08324f] to-[#1e4a6a] rounded-full flex items-center justify-center text-yellow-400 font-bold text-lg">
                        <?php echo $counter++; ?>
                    </div>
                    <div>
                        <p class="font-semibold text-[#08324f]"><?php echo $officer['rank'] . ' ' . $officer['first_name'] . ' ' . $officer['last_name']; ?></p>
                        <p class="text-xs text-gray-500"><?php echo $officer['total_activities']; ?> activities</p>
                    </div>
                </div>
                <?php 
                    endwhile;
                else:
                ?>
                <div class="bg-white p-4 rounded-lg shadow-md text-center text-gray-500 col-span-3">
                    No officers found
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Activities -->
        <div>
            <h3 class="text-lg font-semibold text-[#08324f] mb-3 flex items-center gap-2">
                <i class="fas fa-clock text-yellow-500"></i> Recent Activities
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <?php foreach ($recent as $activity): ?>
                <div class="bg-white p-3 rounded-lg shadow-sm border-l-4 
                    <?php 
                    echo $activity['type'] == 'patrol' ? 'border-blue-500' : 
                        ($activity['type'] == 'checkpoint' ? 'border-red-500' : 'border-green-500'); 
                    ?>">
                    <div class="flex justify-between items-start">
                        <p class="text-xs text-gray-500"><?php echo date('M d, h:i A', strtotime($activity['submitted_at'])); ?></p>
                        <span class="text-xs text-green-600 font-medium">Approved</span>
                    </div>
                    <p class="font-medium text-sm mt-1">
                        <?php 
                        if ($activity['type'] == 'patrol') echo $activity['subtype'];
                        elseif ($activity['type'] == 'checkpoint') echo 'Checkpoint';
                        else echo $activity['subtype'];
                        ?>
                    </p>
                    <p class="text-xs text-gray-600"><?php echo $activity['barangay_name']; ?></p>
                    <p class="text-xs text-gray-500 mt-1 truncate"><?php echo $activity['officer_name']; ?></p>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($recent)): ?>
                <div class="bg-white p-4 rounded-lg shadow-md text-center text-gray-500 col-span-4">
                    No recent activities
                </div>
                <?php endif; ?>
            </div>
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

        document.addEventListener('click', function(event) {
            if (!event.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown').forEach(drop => {
                    drop.classList.remove('active');
                    const arrow = drop.querySelector('.fa-chevron-down');
                    if (arrow) arrow.classList.remove('rotate-180');
                });
            }
        });

// Notification Functions
let notificationCheckInterval;

function checkNotifications() {
    fetch('get_notifications.php?action=get_count')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.count > 0) {
                document.getElementById('notificationBadge').textContent = data.count;
                document.getElementById('notificationBadge').classList.remove('hidden');
            } else {
                document.getElementById('notificationBadge').classList.add('hidden');
            }
        })
        .catch(() => {});
}

function toggleNotifications() {
    const dropdown = document.getElementById('notificationDropdown');
    const isHidden = dropdown.classList.contains('hidden');
    
    if (isHidden) {
        loadNotifications();
        dropdown.classList.remove('hidden');
    } else {
        dropdown.classList.add('hidden');
    }
}

function loadNotifications() {
    const list = document.getElementById('notificationList');
    list.innerHTML = '<div class="p-4 text-center text-gray-500"><i class="fas fa-spinner fa-spin mr-2"></i> Loading...</div>';
    
    fetch('get_notifications.php?action=get_notifications')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.notifications.length > 0) {
                list.innerHTML = '';
                data.notifications.forEach(notif => {
                    const timeAgo = notif.time_ago;
                    let icon = 'fa-bell';
                    let color = 'gray-600';
                    
                    if (notif.message.includes('patrol')) {
                        icon = 'fa-walking';
                        color = 'blue-600';
                    } else if (notif.message.includes('checkpoint')) {
                        icon = 'fa-map-marker-alt';
                        color = 'red-600';
                    } else if (notif.message.includes('oplan')) {
                        icon = 'fa-shield-alt';
                        color = 'green-600';
                    }
                    
                    const bgClass = notif.is_read ? 'bg-gray-50' : 'bg-blue-50';
                    
                    list.innerHTML += `
                        <a href="${notif.report_link}" class="block p-3 ${bgClass} hover:bg-gray-100 border-b border-gray-200 transition">
                            <div class="flex items-start gap-2">
                                <div class="text-${color}">
                                    <i class="fas ${icon}"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs text-gray-800">${notif.message}</p>
                                    <p class="text-xs text-gray-500 mt-1">${timeAgo}</p>
                                </div>
                                ${!notif.is_read ? '<span class="w-2 h-2 bg-blue-600 rounded-full flex-shrink-0"></span>' : ''}
                            </div>
                        </a>
                    `;
                });
            } else {
                list.innerHTML = '<div class="p-6 text-center text-gray-500"><i class="fas fa-bell-slash text-3xl mb-2"></i><p>No notifications</p></div>';
            }
        })
        .catch(() => {
            list.innerHTML = '<div class="p-4 text-center text-red-500">Error loading notifications</div>';
        });
}

function markAllAsRead() {
    fetch('get_notifications.php?action=mark_read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        }
    })
    .then(response => response.json())
    .then(() => {
        document.getElementById('notificationBadge').classList.add('hidden');
        loadNotifications();
    })
    .catch(() => {});
}

// Initialize notifications
document.addEventListener('DOMContentLoaded', function() {
    checkNotifications();
    notificationCheckInterval = setInterval(checkNotifications, 30000);
});

// Close notification dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('notificationDropdown');
    const bell = document.getElementById('notificationBell');
    
    if (bell && dropdown && !bell.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.classList.add('hidden');
    }
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (notificationCheckInterval) {
        clearInterval(notificationCheckInterval);
    }
});
    </script>
</body>
</html>
<?php $conn->close(); ?>