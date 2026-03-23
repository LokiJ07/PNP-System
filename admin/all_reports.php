<?php
// =====================================================
// FILE: admin/all_reports.php
// PURPOSE: Display all reports with daily/monthly/yearly views
// UPDATED: PNP color scheme, navigation arrows, always show layout
// =====================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Fix the path for InfinityFree - use absolute path
$root_path = dirname(__DIR__);
require_once $root_path . '/config/db_connect.php';

// Get view type
$view = isset($_GET['view']) ? $_GET['view'] : 'daily';
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$selected_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Navigation dates
$prev_date = date('Y-m-d', strtotime($selected_date . ' -1 day'));
$next_date = date('Y-m-d', strtotime($selected_date . ' +1 day'));
$prev_month = date('Y-m', strtotime($selected_month . '-01 -1 month'));
$next_month = date('Y-m', strtotime($selected_month . '-01 +1 month'));
$prev_year = $selected_year - 1;
$next_year = $selected_year + 1;

// Admin info
$admin_name = $_SESSION['full_name'] ?? 'Admin';
$admin_email = $_SESSION['email'] ?? 'admin@pnp.gov.ph';

// Get available years (for dropdown)
$years = [];
try {
    $years_patrol = $conn->query("SELECT DISTINCT YEAR(patrol_date) as yr FROM patrol_activities WHERE status = 'approved'");
    if ($years_patrol && $years_patrol->num_rows > 0) {
        while ($row = $years_patrol->fetch_assoc()) {
            if (!in_array($row['yr'], $years)) $years[] = $row['yr'];
        }
    }
} catch (Exception $e) {}
try {
    $years_checkpoint = $conn->query("SELECT DISTINCT YEAR(checkpoint_date) as yr FROM checkpoint_activities WHERE status = 'approved'");
    if ($years_checkpoint && $years_checkpoint->num_rows > 0) {
        while ($row = $years_checkpoint->fetch_assoc()) {
            if (!in_array($row['yr'], $years)) $years[] = $row['yr'];
        }
    }
} catch (Exception $e) {}
try {
    $years_oplan = $conn->query("SELECT DISTINCT YEAR(oplan_date) as yr FROM oplan_activities WHERE status = 'approved'");
    if ($years_oplan && $years_oplan->num_rows > 0) {
        while ($row = $years_oplan->fetch_assoc()) {
            if (!in_array($row['yr'], $years)) $years[] = $row['yr'];
        }
    }
} catch (Exception $e) {}
rsort($years);
if (empty($years)) $years[] = date('Y');

// Initialize variables
$summary = null;
$detailed_reports = [];
$monthly_summary = null;
$weeks = [];
$yearly_summary = null;
$monthly_reports = [];

// ===== DAILY VIEW =====
if ($view == 'daily') {
    $patrols_count = 0; $checkpoints_count = 0; $oplans_count = 0;
    $total_arrests = 0; $firearms = 0; $contraband = 0;
    
    $sql = "SELECT COUNT(*) as total FROM patrol_activities WHERE patrol_date = '$selected_date' AND status = 'approved'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) $patrols_count = $result->fetch_assoc()['total'];
    
    $sql = "SELECT COUNT(*) as total FROM checkpoint_activities WHERE checkpoint_date = '$selected_date' AND status = 'approved'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) $checkpoints_count = $result->fetch_assoc()['total'];
    
    $sql = "SELECT COUNT(*) as total FROM oplan_activities WHERE oplan_date = '$selected_date' AND status = 'approved'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) $oplans_count = $result->fetch_assoc()['total'];
    
    $sql = "SELECT COALESCE(SUM(arrested_accomplishment), 0) as total FROM checkpoint_activities WHERE checkpoint_date = '$selected_date' AND status = 'approved'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) $total_arrests += $result->fetch_assoc()['total'];
    
    $sql = "SELECT COALESCE(SUM(arrests_made), 0) as total FROM oplan_activities WHERE oplan_date = '$selected_date' AND status = 'approved'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) $total_arrests += $result->fetch_assoc()['total'];
    
    $sql = "SELECT COALESCE(SUM(firearms_seized), 0) as total FROM oplan_activities WHERE oplan_date = '$selected_date' AND status = 'approved'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) $firearms = $result->fetch_assoc()['total'];
    
    $sql = "SELECT COALESCE(SUM(contraband_kg), 0) as total FROM oplan_activities WHERE oplan_date = '$selected_date' AND status = 'approved'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) $contraband = $result->fetch_assoc()['total'];
    
    $summary = [
        'total_reports' => $patrols_count + $checkpoints_count + $oplans_count,
        'patrols' => $patrols_count,
        'checkpoints' => $checkpoints_count,
        'oplans' => $oplans_count,
        'total_arrests' => $total_arrests,
        'firearms' => $firearms,
        'contraband' => $contraband
    ];
    
    // Detailed reports
    $detailed_reports = [];
    $sql = "SELECT patrol_id as id, patrol_type as subtype, specific_location, patrol_date as activity_date, patrol_time as activity_time, user_id, barangay_id, 'patrol' as report_type FROM patrol_activities WHERE patrol_date = '$selected_date' AND status = 'approved'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) while ($row = $result->fetch_assoc()) $detailed_reports[] = $row;
    
    $sql = "SELECT checkpoint_id as id, 'Checkpoint' as subtype, specific_location, checkpoint_date as activity_date, checkpoint_time as activity_time, user_id, barangay_id, 'checkpoint' as report_type FROM checkpoint_activities WHERE checkpoint_date = '$selected_date' AND status = 'approved'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) while ($row = $result->fetch_assoc()) $detailed_reports[] = $row;
    
    $sql = "SELECT oplan_id as id, oplan_type as subtype, specific_location, oplan_date as activity_date, oplan_time as activity_time, user_id, barangay_id, 'oplan' as report_type FROM oplan_activities WHERE oplan_date = '$selected_date' AND status = 'approved'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) while ($row = $result->fetch_assoc()) $detailed_reports[] = $row;
    
    if (!empty($detailed_reports)) {
        usort($detailed_reports, function($a, $b) {
            return strtotime($b['activity_time']) - strtotime($a['activity_time']);
        });
    }
}

// ===== MONTHLY VIEW =====
if ($view == 'monthly') {
    $year = substr($selected_month, 0, 4);
    $month = substr($selected_month, 5, 2);
    
    $patrols_month = 0; $checkpoints_month = 0; $oplans_month = 0; $arrests_month = 0;
    
    $sql = "SELECT COUNT(*) as total FROM patrol_activities WHERE YEAR(patrol_date) = $year AND MONTH(patrol_date) = $month AND status = 'approved'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) $patrols_month = $result->fetch_assoc()['total'];
    
    $sql = "SELECT COUNT(*) as total FROM checkpoint_activities WHERE YEAR(checkpoint_date) = $year AND MONTH(checkpoint_date) = $month AND status = 'approved'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) $checkpoints_month = $result->fetch_assoc()['total'];
    
    $sql = "SELECT COUNT(*) as total FROM oplan_activities WHERE YEAR(oplan_date) = $year AND MONTH(oplan_date) = $month AND status = 'approved'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) $oplans_month = $result->fetch_assoc()['total'];
    
    $sql = "SELECT COALESCE(SUM(arrested_accomplishment), 0) as total FROM checkpoint_activities WHERE YEAR(checkpoint_date) = $year AND MONTH(checkpoint_date) = $month AND status = 'approved'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) $arrests_month += $result->fetch_assoc()['total'];
    
    $sql = "SELECT COALESCE(SUM(arrests_made), 0) as total FROM oplan_activities WHERE YEAR(oplan_date) = $year AND MONTH(oplan_date) = $month AND status = 'approved'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) $arrests_month += $result->fetch_assoc()['total'];
    
    $monthly_summary = [
        'total_reports' => $patrols_month + $checkpoints_month + $oplans_month,
        'patrols' => $patrols_month,
        'checkpoints' => $checkpoints_month,
        'oplans' => $oplans_month,
        'total_arrests' => $arrests_month
    ];
    
    // Build calendar days
    $first_day = date('Y-m-01', strtotime($selected_month . '-01'));
    $last_day = date('Y-m-t', strtotime($selected_month . '-01'));
    $days_in_month = [];
    $current = strtotime($first_day);
    $last = strtotime($last_day);
    while ($current <= $last) {
        $date = date('Y-m-d', $current);
        $days_in_month[$date] = [
            'date' => $date,
            'day_number' => date('j', $current),
            'day_of_week' => date('l', $current),
            'reports' => 0,
            'patrols' => 0,
            'checkpoints' => 0,
            'oplans' => 0
        ];
        $current = strtotime('+1 day', $current);
    }
    
    // Populate with actual data
    $sql = "SELECT patrol_date as rdate, COUNT(*) as cnt FROM patrol_activities WHERE YEAR(patrol_date) = $year AND MONTH(patrol_date) = $month AND status = 'approved' GROUP BY patrol_date";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if (isset($days_in_month[$row['rdate']])) {
                $days_in_month[$row['rdate']]['reports'] += $row['cnt'];
                $days_in_month[$row['rdate']]['patrols'] = $row['cnt'];
            }
        }
    }
    
    $sql = "SELECT checkpoint_date as rdate, COUNT(*) as cnt FROM checkpoint_activities WHERE YEAR(checkpoint_date) = $year AND MONTH(checkpoint_date) = $month AND status = 'approved' GROUP BY checkpoint_date";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if (isset($days_in_month[$row['rdate']])) {
                $days_in_month[$row['rdate']]['reports'] += $row['cnt'];
                $days_in_month[$row['rdate']]['checkpoints'] = $row['cnt'];
            }
        }
    }
    
    $sql = "SELECT oplan_date as rdate, COUNT(*) as cnt FROM oplan_activities WHERE YEAR(oplan_date) = $year AND MONTH(oplan_date) = $month AND status = 'approved' GROUP BY oplan_date";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if (isset($days_in_month[$row['rdate']])) {
                $days_in_month[$row['rdate']]['reports'] += $row['cnt'];
                $days_in_month[$row['rdate']]['oplans'] = $row['cnt'];
            }
        }
    }
    
    // Group into weeks
    $weeks = [];
    $week_number = 0;
    $current_week = [];
    $day_order = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    foreach ($days_in_month as $date => $day_data) {
        $day_of_week = $day_data['day_of_week'];
        if ($day_of_week == 'Sunday' && !empty($current_week)) {
            $weeks[$week_number++] = $current_week;
            $current_week = [];
        }
        $current_week[$day_of_week] = $day_data;
    }
    if (!empty($current_week)) $weeks[$week_number] = $current_week;
}

// ===== YEARLY VIEW =====
if ($view == 'yearly') {
    $patrols_year = 0; $checkpoints_year = 0; $oplans_year = 0;
    
    $sql = "SELECT COUNT(*) as total FROM patrol_activities WHERE YEAR(patrol_date) = $selected_year AND status = 'approved'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) $patrols_year = $result->fetch_assoc()['total'];
    
    $sql = "SELECT COUNT(*) as total FROM checkpoint_activities WHERE YEAR(checkpoint_date) = $selected_year AND status = 'approved'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) $checkpoints_year = $result->fetch_assoc()['total'];
    
    $sql = "SELECT COUNT(*) as total FROM oplan_activities WHERE YEAR(oplan_date) = $selected_year AND status = 'approved'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) $oplans_year = $result->fetch_assoc()['total'];
    
    $yearly_summary = [
        'total_reports' => $patrols_year + $checkpoints_year + $oplans_year,
        'patrols' => $patrols_year,
        'checkpoints' => $checkpoints_year,
        'oplans' => $oplans_year
    ];
    
    // Monthly breakdown (all 12 months)
    $monthly_reports = [];
    for ($m = 1; $m <= 12; $m++) {
        $month_name = date('F', mktime(0, 0, 0, $m, 1));
        $patrol_month = 0; $checkpoint_month = 0; $oplan_month = 0;
        
        $sql = "SELECT COUNT(*) as cnt FROM patrol_activities WHERE YEAR(patrol_date) = $selected_year AND MONTH(patrol_date) = $m AND status = 'approved'";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) $patrol_month = $result->fetch_assoc()['cnt'];
        
        $sql = "SELECT COUNT(*) as cnt FROM checkpoint_activities WHERE YEAR(checkpoint_date) = $selected_year AND MONTH(checkpoint_date) = $m AND status = 'approved'";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) $checkpoint_month = $result->fetch_assoc()['cnt'];
        
        $sql = "SELECT COUNT(*) as cnt FROM oplan_activities WHERE YEAR(oplan_date) = $selected_year AND MONTH(oplan_date) = $m AND status = 'approved'";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) $oplan_month = $result->fetch_assoc()['cnt'];
        
        $monthly_reports[] = [
            'report_month' => $m,
            'month_name' => $month_name,
            'total_reports' => $patrol_month + $checkpoint_month + $oplan_month,
            'patrols' => $patrol_month,
            'checkpoints' => $checkpoint_month,
            'oplans' => $oplan_month
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../image/pnplogo.png">
    <title>PNP | All Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --pnp-navy: #003366;
            --pnp-gold: #FFD700;
            --pnp-light-navy: #1a4d8c;
        }
        body { background: #eef2f6; }
        .sidebar {
            background: linear-gradient(135deg, var(--pnp-navy), #002244);
        }
        .view-tab.active {
            background: white;
            color: var(--pnp-navy);
            border-bottom: 3px solid var(--pnp-gold);
        }
        .view-tab.inactive {
            background: #e2e8f0;
            color: #4a5568;
        }
        .view-tab.inactive:hover {
            background: #cbd5e0;
        }
        .btn-nav {
            background: var(--pnp-navy);
            color: white;
            transition: all 0.2s;
        }
        .btn-nav:hover {
            background: var(--pnp-light-navy);
            transform: translateY(-1px);
        }
        .stat-card {
            transition: all 0.2s ease;
            border-left-width: 4px;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        th {
            background: var(--pnp-navy);
            color: white;
        }
        .badge-patrol { background: #dbeafe; color: #1e40af; }
        .badge-checkpoint { background: #fee2e2; color: #b91c1c; }
        .badge-oplan { background: #dcfce7; color: #166534; }
        .filter-card {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        @media print {
            .no-print, .sidebar, .view-tabs, button, .dropdown { display: none !important; }
            body { background: white; }
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
            .sidebar-mobile.open { left: 0; }
        }
        .dropdown-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        .dropdown.active .dropdown-content {
            max-height: 300px;
        }
        .rotate-180 { transform: rotate(180deg); }
    </style>
</head>
<body class="flex flex-col md:flex-row">

    <!-- Mobile Menu Button -->
    <button id="mobileMenuBtn" class="md:hidden fixed top-4 left-4 z-50 bg-[#003366] text-white p-3 rounded-lg shadow-lg no-print">
        <i class="fas fa-bars text-xl"></i>
    </button>
    <div id="menuOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden no-print" onclick="closeMobileMenu()"></div>

    <!-- Sidebar (PNP Navy) -->
    <div id="sidebar" class="w-full md:w-[260px] sidebar text-white h-screen overflow-y-auto sidebar-mobile fixed top-0 left-[-100%] md:left-0 md:sticky z-50 transition-all duration-300 ease-in-out no-print shadow-xl">
        <button id="closeSidebar" class="md:hidden absolute top-4 right-4 text-white text-xl">
            <i class="fas fa-times"></i>
        </button>
        <div class="flex items-center gap-3 p-5 border-b border-[#FFD700] sticky top-0 sidebar z-10">
            <img src="../image/pnplogo.png" class="w-10 h-10 object-contain" alt="PNP Logo">
            <div>
                <h2 class="text-lg font-semibold leading-tight">PNP Operation</h2>
                <p class="text-xs text-[#FFD700]">Admin Panel</p>
            </div>
        </div>
        <div class="bg-[#1a4d8c] mx-4 my-4 p-4 rounded-lg text-center shadow-lg border border-[#FFD700]">
            <div class="w-16 h-16 bg-[#FFD700] rounded-full mx-auto mb-3 flex items-center justify-center text-[#003366] text-2xl font-bold">
                <?php echo substr($admin_name, 0, 1); ?>
            </div>
            <p class="font-medium text-[#FFD700]"><?php echo $admin_name; ?></p>
            <p class="text-xs text-gray-300 mt-1 break-all"><?php echo $admin_email; ?></p>
        </div>
        <ul class="space-y-1 px-3 pb-5">
            <li><a href="admin_dashboard.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1a4d8c] transition"><i class="fas fa-tachometer-alt w-5"></i> Dashboard</a></li>
            <li><a href="checkpoint.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1a4d8c] transition"><i class="fas fa-map-marker-alt w-5"></i> Checkpoint</a></li>
            <li class="dropdown">
                <div class="flex items-center justify-between p-3 rounded-lg hover:bg-[#1a4d8c] cursor-pointer transition" onclick="toggleDropdown(this)">
                    <div class="flex items-center gap-3"><i class="fas fa-walking w-5"></i> Patrol</div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300"></i>
                </div>
                <ul class="dropdown-content pl-4 ml-4 space-y-1 border-l border-[#1a4d8c]">
                    <li><a href="footpatrol.php" class="block p-2 text-sm hover:bg-[#1a4d8c] rounded-lg transition">Foot Patrol</a></li>
                    <li><a href="mobilepatrol.php" class="block p-2 text-sm hover:bg-[#1a4d8c] rounded-lg transition">Mobile Patrol</a></li>
                    <li><a href="motorpatrol.php" class="block p-2 text-sm hover:bg-[#1a4d8c] rounded-lg transition">Motor Patrol</a></li>
                </ul>
            </li>
            <li class="dropdown">
                <div class="flex items-center justify-between p-3 rounded-lg hover:bg-[#1a4d8c] cursor-pointer transition" onclick="toggleDropdown(this)">
                    <div class="flex items-center gap-3"><i class="fas fa-shield-alt w-5"></i> Oplan</div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300"></i>
                </div>
                <ul class="dropdown-content pl-4 ml-4 space-y-1 border-l border-[#1a4d8c]">
                    <li><a href="oplanbakal.php" class="block p-2 text-sm hover:bg-[#1a4d8c] rounded-lg transition">Oplan Bakal</a></li>
                    <li><a href="oplansita.php" class="block p-2 text-sm hover:bg-[#1a4d8c] rounded-lg transition">Oplan Sita</a></li>
                </ul>
            </li>
            <li><a href="admin_users.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1a4d8c] transition"><i class="fas fa-users w-5"></i> Users</a></li>
            <li><a href="accomplishment_report.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1a4d8c] transition"><i class="fas fa-file-alt w-5"></i> Accomplishment Report</a></li>
            <li class="bg-[#1a4d8c] rounded-lg"><a href="all_reports.php" class="flex items-center gap-3 p-3"><i class="fas fa-folder-open w-5 text-[#FFD700]"></i> All Reports</a></li>
            <li><a href="activity_logs.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1a4d8c] transition"><i class="fas fa-history w-5"></i> Activity Logs</a></li>
            <li class="my-4 border-t border-[#1a4d8c]"></li>
            <li><a href="../logout.php" class="flex items-center gap-3 p-3 rounded-lg bg-red-600 hover:bg-red-700 transition"><i class="fas fa-sign-out-alt w-5"></i> Logout</a></li>
            <li class="mt-6 text-center text-xs text-gray-400">
                <p>PNP Manolo Fortich v2.0</p>
                <p class="mt-1">© 2026 All Rights Reserved</p>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-4 md:p-6 overflow-y-auto min-h-screen">
        
        <!-- Header -->
        <div class="bg-white p-4 md:p-6 rounded-lg shadow-md mb-4 border-l-4 border-[#FFD700] flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 no-print">
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-[#003366] flex items-center gap-2">
                    <i class="fas fa-folder-open text-[#FFD700]"></i>
                    All Reports
                </h2>
                <p class="text-sm text-gray-600 mt-1">Filtered by Activity Date (patrol_date, checkpoint_date, oplan_date)</p>
            </div>
            <div class="bg-green-100 text-green-800 px-4 py-2 rounded-full text-sm font-semibold">
                <i class="fas fa-check-circle mr-1"></i> Auto-Approved
            </div>
        </div>

        <!-- View Tabs -->
        <div class="flex gap-1 mb-4 no-print">
            <a href="?view=daily&date=<?php echo $selected_date; ?>" class="view-tab <?php echo $view == 'daily' ? 'active' : 'inactive'; ?> px-4 py-2 rounded-t-lg font-medium">
                <i class="fas fa-calendar-day mr-2"></i>Daily
            </a>
            <a href="?view=monthly&month=<?php echo $selected_month; ?>" class="view-tab <?php echo $view == 'monthly' ? 'active' : 'inactive'; ?> px-4 py-2 rounded-t-lg font-medium">
                <i class="fas fa-calendar-alt mr-2"></i>Monthly
            </a>
            <a href="?view=yearly&year=<?php echo $selected_year; ?>" class="view-tab <?php echo $view == 'yearly' ? 'active' : 'inactive'; ?> px-4 py-2 rounded-t-lg font-medium">
                <i class="fas fa-calendar mr-2"></i>Yearly
            </a>
        </div>

        <!-- Date Selector with Navigation Arrows -->
        <div class="filter-card p-4 mb-4 no-print">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="view" value="<?php echo $view; ?>">
                <?php if ($view == 'daily'): ?>
                <div class="flex items-center gap-2">
                    <a href="?view=daily&date=<?php echo $prev_date; ?>" class="btn-nav px-3 py-2 rounded-lg text-sm"><i class="fas fa-chevron-left"></i></a>
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Select Activity Date</label>
                        <input type="date" name="date" value="<?php echo $selected_date; ?>" class="w-full p-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#FFD700]">
                    </div>
                    <a href="?view=daily&date=<?php echo $next_date; ?>" class="btn-nav px-3 py-2 rounded-lg text-sm"><i class="fas fa-chevron-right"></i></a>
                    <a href="?view=daily&date=<?php echo date('Y-m-d'); ?>" class="btn-nav px-3 py-2 rounded-lg text-sm"><i class="fas fa-calendar-day"></i> Today</a>
                </div>
                <?php elseif ($view == 'monthly'): ?>
                <div class="flex items-center gap-2">
                    <a href="?view=monthly&month=<?php echo $prev_month; ?>" class="btn-nav px-3 py-2 rounded-lg text-sm"><i class="fas fa-chevron-left"></i></a>
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Select Activity Month</label>
                        <input type="month" name="month" value="<?php echo $selected_month; ?>" class="w-full p-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <a href="?view=monthly&month=<?php echo $next_month; ?>" class="btn-nav px-3 py-2 rounded-lg text-sm"><i class="fas fa-chevron-right"></i></a>
                    <a href="?view=monthly&month=<?php echo date('Y-m'); ?>" class="btn-nav px-3 py-2 rounded-lg text-sm"><i class="fas fa-calendar-alt"></i> Current</a>
                </div>
                <?php elseif ($view == 'yearly'): ?>
                <div class="flex items-center gap-2">
                    <a href="?view=yearly&year=<?php echo $prev_year; ?>" class="btn-nav px-3 py-2 rounded-lg text-sm"><i class="fas fa-chevron-left"></i></a>
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Select Activity Year</label>
                        <select name="year" class="w-full p-2 border border-gray-300 rounded-lg text-sm">
                            <?php foreach ($years as $year): ?>
                            <option value="<?php echo $year; ?>" <?php echo $selected_year == $year ? 'selected' : ''; ?>><?php echo $year; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <a href="?view=yearly&year=<?php echo $next_year; ?>" class="btn-nav px-3 py-2 rounded-lg text-sm"><i class="fas fa-chevron-right"></i></a>
                    <a href="?view=yearly&year=<?php echo date('Y'); ?>" class="btn-nav px-3 py-2 rounded-lg text-sm"><i class="fas fa-calendar"></i> Current</a>
                </div>
                <?php endif; ?>
                <div>
                    <button type="submit" class="px-4 py-2 bg-[#003366] text-white rounded-lg hover:bg-[#002244] transition text-sm">
                        <i class="fas fa-search mr-1"></i> Go
                    </button>
                </div>
            </form>
        </div>

        <!-- ===== DAILY VIEW (always shown) ===== -->
        <?php if ($view == 'daily'): ?>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-[#003366]">
                <p class="text-xs text-gray-500">📅 Activity Date</p>
                <p class="text-lg font-bold text-[#003366]"><?php echo date('F d, Y', strtotime($selected_date)); ?></p>
                <p class="text-xs text-gray-500"><?php echo date('l', strtotime($selected_date)); ?></p>
            </div>
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-green-500">
                <p class="text-xs text-gray-500">📊 Total Reports</p>
                <p class="text-2xl font-bold text-[#003366]"><?php echo $summary['total_reports']; ?></p>
            </div>
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-red-500">
                <p class="text-xs text-gray-500">🚔 Arrests</p>
                <p class="text-2xl font-bold text-[#003366]"><?php echo $summary['total_arrests']; ?></p>
            </div>
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-purple-500">
                <p class="text-xs text-gray-500">🔫 Firearms / Contraband</p>
                <p class="text-lg font-bold text-[#003366]"><?php echo $summary['firearms']; ?> / <?php echo number_format($summary['contraband'], 2); ?> kg</p>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
                <p class="text-xs text-gray-600">🚶 Patrols</p>
                <p class="text-2xl font-bold text-blue-700"><?php echo $summary['patrols']; ?></p>
            </div>
            <div class="bg-red-50 p-4 rounded-lg border-l-4 border-red-500">
                <p class="text-xs text-gray-600">🚧 Checkpoints</p>
                <p class="text-2xl font-bold text-red-700"><?php echo $summary['checkpoints']; ?></p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500">
                <p class="text-xs text-gray-600">🛡️ Oplans</p>
                <p class="text-2xl font-bold text-green-700"><?php echo $summary['oplans']; ?></p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-4 bg-gray-100 border-b font-semibold text-[#003366]">
                <span><i class="fas fa-list mr-2"></i> Detailed Reports for <?php echo date('F d, Y', strtotime($selected_date)); ?></span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr><th>Time</th><th>Type</th><th>Officer</th><th>Barangay</th><th>Location</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($detailed_reports)): ?>
                        <tr><td colspan="6" class="p-8 text-center text-gray-500">No reports for this day</td></tr>
                        <?php else: foreach ($detailed_reports as $report): 
                            $officer_stmt = $conn->prepare("SELECT rank, first_name, last_name FROM users WHERE user_id = ?");
                            $officer_stmt->bind_param("i", $report['user_id']);
                            $officer_stmt->execute();
                            $officer = $officer_stmt->get_result()->fetch_assoc();
                            $officer_name = $officer ? $officer['rank'] . ' ' . $officer['first_name'] . ' ' . $officer['last_name'] : 'Unknown';
                            $barangay_stmt = $conn->prepare("SELECT barangay_name FROM barangays WHERE barangay_id = ?");
                            $barangay_stmt->bind_param("i", $report['barangay_id']);
                            $barangay_stmt->execute();
                            $barangay = $barangay_stmt->get_result()->fetch_assoc();
                            $barangay_name = $barangay ? $barangay['barangay_name'] : 'Unknown';
                        ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-2 whitespace-nowrap"><?php echo date('h:i A', strtotime($report['activity_time'])); ?></td>
                            <td><span class="badge <?php echo $report['report_type'] == 'patrol' ? 'badge-patrol' : ($report['report_type'] == 'checkpoint' ? 'badge-checkpoint' : 'badge-oplan'); ?>"><i class="fas <?php echo $report['report_type'] == 'patrol' ? 'fa-walking' : ($report['report_type'] == 'checkpoint' ? 'fa-map-marker-alt' : 'fa-shield-alt'); ?> mr-1"></i> <?php echo $report['subtype']; ?></span></td>
                            <td><?php echo $officer_name; ?></td>
                            <td><?php echo $barangay_name; ?></td>
                            <td class="max-w-xs truncate"><?php echo htmlspecialchars($report['specific_location']); ?></td>
                            <td><a href="view_report.php?type=<?php echo $report['report_type']; ?>&id=<?php echo $report['id']; ?>" class="bg-[#003366] text-white px-3 py-1 rounded text-xs hover:bg-[#002244] transition inline-flex items-center gap-1"><i class="fas fa-eye"></i> View</a></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ===== MONTHLY VIEW ===== -->
        <?php elseif ($view == 'monthly'): ?>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-[#003366]">
                <p class="text-xs text-gray-500">📅 Activity Month</p>
                <p class="text-lg font-bold text-[#003366]"><?php echo date('F Y', strtotime($selected_month . '-01')); ?></p>
            </div>
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-green-500">
                <p class="text-xs text-gray-500">📊 Total Reports</p>
                <p class="text-2xl font-bold text-[#003366]"><?php echo $monthly_summary['total_reports']; ?></p>
            </div>
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-red-500">
                <p class="text-xs text-gray-500">🚔 Arrests</p>
                <p class="text-2xl font-bold text-[#003366]"><?php echo $monthly_summary['total_arrests']; ?></p>
            </div>
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-purple-500">
                <p class="text-xs text-gray-500">📈 Daily Average</p>
                <p class="text-lg font-bold text-[#003366]"><?php echo round($monthly_summary['total_reports'] / date('t', strtotime($selected_month . '-01')), 1); ?></p>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500"><p class="text-xs text-gray-600">🚶 Patrols</p><p class="text-2xl font-bold text-blue-700"><?php echo $monthly_summary['patrols']; ?></p></div>
            <div class="bg-red-50 p-4 rounded-lg border-l-4 border-red-500"><p class="text-xs text-gray-600">🚧 Checkpoints</p><p class="text-2xl font-bold text-red-700"><?php echo $monthly_summary['checkpoints']; ?></p></div>
            <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500"><p class="text-xs text-gray-600">🛡️ Oplans</p><p class="text-2xl font-bold text-green-700"><?php echo $monthly_summary['oplans']; ?></p></div>
        </div>
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="p-4 bg-gray-100 border-b font-semibold text-[#003366]"><i class="fas fa-calendar-alt mr-2"></i> Calendar View (Activity Dates)</div>
            <div class="p-4">
                <?php foreach ($weeks as $week_index => $week): ?>
                <div class="mb-4">
                    <div class="bg-[#003366] text-white px-3 py-1 rounded-t-lg text-sm">Week <?php echo $week_index + 1; ?></div>
                    <div class="grid grid-cols-7 gap-1 mt-1 mb-1">
                        <?php foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day): ?><div class="text-center text-xs font-semibold text-gray-700"><?php echo $day; ?></div><?php endforeach; ?>
                    </div>
                    <div class="grid grid-cols-7 gap-1">
                        <?php foreach (['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $day): ?>
                            <?php if (isset($week[$day])): 
                                $day_data = $week[$day];
                                $has_reports = $day_data['reports'] > 0;
                            ?>
                            <a href="?view=daily&date=<?php echo $day_data['date']; ?>" class="block p-2 border rounded-lg text-center <?php echo $has_reports ? 'bg-white hover:bg-blue-50 hover:border-[#FFD700]' : 'bg-gray-50'; ?>">
                                <div class="text-sm font-bold"><?php echo $day_data['day_number']; ?></div>
                                <?php if ($has_reports): ?><div class="text-xs font-semibold text-[#003366]"><?php echo $day_data['reports']; ?> reports</div><?php else: ?><div class="text-xs text-gray-400">-</div><?php endif; ?>
                            </a>
                            <?php else: ?>
                            <div class="p-2 border rounded-lg bg-gray-50 text-gray-400 text-center">-</div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ===== YEARLY VIEW ===== -->
        <?php elseif ($view == 'yearly'): ?>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-[#003366]"><p class="text-xs text-gray-500">📅 Activity Year</p><p class="text-lg font-bold text-[#003366]"><?php echo $selected_year; ?></p></div>
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-green-500"><p class="text-xs text-gray-500">📊 Total Reports</p><p class="text-2xl font-bold text-[#003366]"><?php echo $yearly_summary['total_reports']; ?></p></div>
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-red-500"><p class="text-xs text-gray-500">📈 Monthly Average</p><p class="text-lg font-bold text-[#003366]"><?php echo round($yearly_summary['total_reports'] / 12, 1); ?></p></div>
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-purple-500">
                <p class="text-xs text-gray-500">🚔 Busiest Month</p>
                <p class="text-lg font-bold text-[#003366]"><?php $max = 0; $max_month = ''; foreach ($monthly_reports as $m) if ($m['total_reports'] > $max) { $max = $m['total_reports']; $max_month = substr($m['month_name'],0,3); } echo $max > 0 ? $max_month . ' (' . $max . ')' : 'N/A'; ?></p>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500"><p class="text-xs text-gray-600">🚶 Patrols</p><p class="text-2xl font-bold text-blue-700"><?php echo $yearly_summary['patrols']; ?></p></div>
            <div class="bg-red-50 p-4 rounded-lg border-l-4 border-red-500"><p class="text-xs text-gray-600">🚧 Checkpoints</p><p class="text-2xl font-bold text-red-700"><?php echo $yearly_summary['checkpoints']; ?></p></div>
            <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500"><p class="text-xs text-gray-600">🛡️ Oplans</p><p class="text-2xl font-bold text-green-700"><?php echo $yearly_summary['oplans']; ?></p></div>
        </div>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-4 bg-gray-100 border-b font-semibold text-[#003366]"><i class="fas fa-calendar-alt mr-2"></i> Monthly Breakdown (Activity Dates)</div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead><tr><th>Month</th><th class="text-center">Total</th><th class="text-center">Patrols</th><th class="text-center">Checkpoints</th><th class="text-center">Oplans</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach ($monthly_reports as $month): $month_num = str_pad($month['report_month'],2,'0',STR_PAD_LEFT); ?>
                        <tr class="border-b hover:bg-gray-50"><td class="py-2 px-2 font-semibold"><?php echo $month['month_name']; ?></td><td class="text-center font-bold"><?php echo $month['total_reports']; ?></td><td class="text-center"><?php echo $month['patrols']; ?></td><td class="text-center"><?php echo $month['checkpoints']; ?></td><td class="text-center"><?php echo $month['oplans']; ?></td><td><a href="?view=monthly&month=<?php echo $selected_year . '-' . $month_num; ?>" class="text-[#003366] hover:text-[#002244] text-sm"><i class="fas fa-eye"></i> View</a></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <div class="mt-4 text-xs text-gray-500 text-center no-print">
            <i class="fas fa-check-circle text-green-500 mr-1"></i> Reports filtered by Activity Date (patrol_date, checkpoint_date, oplan_date)
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const menuBtn = document.getElementById('mobileMenuBtn');
        const closeBtn = document.getElementById('closeSidebar');
        const overlay = document.getElementById('menuOverlay');
        function openMobileMenu() { sidebar.classList.add('open'); overlay.classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
        function closeMobileMenu() { sidebar.classList.remove('open'); overlay.classList.add('hidden'); document.body.style.overflow = ''; }
        if (menuBtn) menuBtn.addEventListener('click', openMobileMenu);
        if (closeBtn) closeBtn.addEventListener('click', closeMobileMenu);
        if (overlay) overlay.addEventListener('click', closeMobileMenu);
        window.addEventListener('resize', function() { if (window.innerWidth >= 768) closeMobileMenu(); });
        function toggleDropdown(element) {
            const parent = element.closest('.dropdown');
            parent.classList.toggle('active');
            const arrow = element.querySelector('.fa-chevron-down');
            if (arrow) arrow.classList.toggle('rotate-180');
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>