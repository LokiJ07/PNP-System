<?php
// =====================================================
// FILE: admin/all_reports.php
// PURPOSE: Display all reports with daily/monthly/yearly views
// IMPROVED: Removed pending/rejected, added dispositions and violations
// =====================================================

session_start();
require_once '../config/db_connect.php';
requireAdmin();

// Get view type (daily, monthly, yearly)
$view = $_GET['view'] ?? 'daily';
$selected_date = $_GET['date'] ?? date('Y-m-d');
$selected_month = $_GET['month'] ?? date('Y-m');
$selected_year = $_GET['year'] ?? date('Y');

// Get filter parameters
$type = $_GET['type'] ?? 'all';
$barangay_id = isset($_GET['barangay_id']) ? (int)$_GET['barangay_id'] : 0;
$officer_id = isset($_GET['officer_id']) ? (int)$_GET['officer_id'] : 0;
$search = $_GET['search'] ?? '';

// Get all barangays for filter
$barangays = $conn->query("SELECT barangay_id, barangay_name FROM barangays ORDER BY barangay_name");

// Get all officers for filter
$officers = $conn->query("SELECT user_id, rank, first_name, last_name FROM users WHERE role = 'user' ORDER BY last_name, first_name");

// ===== DAILY VIEW =====
if ($view == 'daily') {
    // Get reports for selected date - ONLY APPROVED
    $date_condition = "DATE(submitted_at) = '$selected_date' AND status = 'approved'";
    
    // Get summary for the day
    $summary_query = "
        SELECT 
            COUNT(*) as total_reports,
            SUM(CASE WHEN report_type = 'patrol' THEN 1 ELSE 0 END) as patrols,
            SUM(CASE WHEN report_type = 'checkpoint' THEN 1 ELSE 0 END) as checkpoints,
            SUM(CASE WHEN report_type = 'oplan' THEN 1 ELSE 0 END) as oplans,
            
            (SELECT COALESCE(SUM(arrested_accomplishment), 0) FROM checkpoint_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') +
            (SELECT COALESCE(SUM(arrests_made), 0) FROM oplan_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') as total_arrests,
            
            (SELECT COALESCE(SUM(firearms_seized), 0) FROM oplan_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') as firearms,
            (SELECT COALESCE(SUM(contraband_kg), 0) FROM oplan_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') as contraband,
            
            (SELECT COALESCE(SUM(fixed_count), 0) FROM checkpoint_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') +
            (SELECT COALESCE(SUM(fixed_count), 0) FROM oplan_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') as total_fixed,
            
            (SELECT COALESCE(SUM(fined_count), 0) FROM checkpoint_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') +
            (SELECT COALESCE(SUM(fined_count), 0) FROM oplan_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') as total_fined,
            
            (SELECT COALESCE(SUM(warned_count), 0) FROM checkpoint_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') +
            (SELECT COALESCE(SUM(warned_count), 0) FROM oplan_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') as total_warned,
            
            (SELECT COALESCE(SUM(charged_count), 0) FROM checkpoint_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') +
            (SELECT COALESCE(SUM(charged_count), 0) FROM oplan_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') as total_charged,
            
            (SELECT COALESCE(SUM(community_service), 0) FROM checkpoint_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') +
            (SELECT COALESCE(SUM(community_service), 0) FROM oplan_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') as total_community
        FROM (
            SELECT 'patrol' as report_type, submitted_at FROM patrol_activities WHERE status = 'approved'
            UNION ALL
            SELECT 'checkpoint' as report_type, submitted_at FROM checkpoint_activities WHERE status = 'approved'
            UNION ALL
            SELECT 'oplan' as report_type, submitted_at FROM oplan_activities WHERE status = 'approved'
        ) as all_reports
        WHERE DATE(submitted_at) = '$selected_date'
    ";
    
    $summary = $conn->query($summary_query)->fetch_assoc();
    
    // Get violations for the day
    $violations_query = "
        SELECT 
            (SELECT COALESCE(SUM(drinking_violations), 0) FROM patrol_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') +
            (SELECT COALESCE(SUM(drinking_violations), 0) FROM checkpoint_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') +
            (SELECT COALESCE(SUM(drinking_violations), 0) FROM oplan_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') as drinking,
            
            (SELECT COALESCE(SUM(smoking_violations), 0) FROM patrol_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') +
            (SELECT COALESCE(SUM(smoking_violations), 0) FROM checkpoint_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') +
            (SELECT COALESCE(SUM(smoking_violations), 0) FROM oplan_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') as smoking,
            
            (SELECT COALESCE(SUM(halfnaked_violations), 0) FROM patrol_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') +
            (SELECT COALESCE(SUM(halfnaked_violations), 0) FROM checkpoint_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') +
            (SELECT COALESCE(SUM(halfnaked_violations), 0) FROM oplan_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') as halfnaked,
            
            (SELECT COALESCE(SUM(curfew_violations), 0) FROM patrol_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') +
            (SELECT COALESCE(SUM(curfew_violations), 0) FROM checkpoint_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') +
            (SELECT COALESCE(SUM(curfew_violations), 0) FROM oplan_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') as curfew,
            
            (SELECT COALESCE(SUM(vandalism_violations), 0) FROM patrol_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') +
            (SELECT COALESCE(SUM(vandalism_violations), 0) FROM checkpoint_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') +
            (SELECT COALESCE(SUM(vandalism_violations), 0) FROM oplan_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') as vandalism,
            
            (SELECT COALESCE(SUM(other_violations), 0) FROM patrol_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') +
            (SELECT COALESCE(SUM(other_violations), 0) FROM checkpoint_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') +
            (SELECT COALESCE(SUM(other_violations), 0) FROM oplan_activities WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved') as other
    ";
    $violations = $conn->query($violations_query)->fetch_assoc();
}

// ===== MONTHLY VIEW =====
if ($view == 'monthly') {
    $year = substr($selected_month, 0, 4);
    $month = substr($selected_month, 5, 2);
    
    // Get monthly summary
    $monthly_summary_query = "
        SELECT 
            COUNT(*) as total_reports,
            SUM(CASE WHEN report_type = 'patrol' THEN 1 ELSE 0 END) as patrols,
            SUM(CASE WHEN report_type = 'checkpoint' THEN 1 ELSE 0 END) as checkpoints,
            SUM(CASE WHEN report_type = 'oplan' THEN 1 ELSE 0 END) as oplans,
            
            (SELECT COALESCE(SUM(arrested_accomplishment), 0) FROM checkpoint_activities WHERE YEAR(submitted_at) = $year AND MONTH(submitted_at) = $month AND status = 'approved') +
            (SELECT COALESCE(SUM(arrests_made), 0) FROM oplan_activities WHERE YEAR(submitted_at) = $year AND MONTH(submitted_at) = $month AND status = 'approved') as total_arrests,
            
            (SELECT COALESCE(SUM(firearms_seized), 0) FROM oplan_activities WHERE YEAR(submitted_at) = $year AND MONTH(submitted_at) = $month AND status = 'approved') as firearms,
            (SELECT COALESCE(SUM(contraband_kg), 0) FROM oplan_activities WHERE YEAR(submitted_at) = $year AND MONTH(submitted_at) = $month AND status = 'approved') as contraband
        FROM (
            SELECT 'patrol' as report_type, submitted_at FROM patrol_activities WHERE status = 'approved'
            UNION ALL
            SELECT 'checkpoint' as report_type, submitted_at FROM checkpoint_activities WHERE status = 'approved'
            UNION ALL
            SELECT 'oplan' as report_type, submitted_at FROM oplan_activities WHERE status = 'approved'
        ) as all_reports
        WHERE YEAR(submitted_at) = $year AND MONTH(submitted_at) = $month
    ";
    
    $monthly_summary = $conn->query($monthly_summary_query)->fetch_assoc();
    
    // Get the first and last day of the month
    $first_day = date('Y-m-01', strtotime($selected_month . '-01'));
    $last_day = date('Y-m-t', strtotime($selected_month . '-01'));

    // Get all days in the month with their day of week
    $days_in_month = [];
    $current = strtotime($first_day);
    $last = strtotime($last_day);

    while ($current <= $last) {
        $date = date('Y-m-d', $current);
        $day_of_week = date('l', $current);
        $day_number = date('j', $current);
        
        $days_in_month[$date] = [
            'date' => $date,
            'day_number' => $day_number,
            'day_of_week' => $day_of_week,
            'day_short' => date('D', $current),
            'reports' => 0,
            'patrols' => 0,
            'checkpoints' => 0,
            'oplans' => 0,
            'arrests' => 0,
            'firearms' => 0,
            'contraband' => 0
        ];
        
        $current = strtotime('+1 day', $current);
    }

    // Get actual report data for the month
    $daily_query = "
        SELECT 
            DATE(submitted_at) as report_date,
            COUNT(*) as total_reports,
            SUM(CASE WHEN report_type = 'patrol' THEN 1 ELSE 0 END) as patrols,
            SUM(CASE WHEN report_type = 'checkpoint' THEN 1 ELSE 0 END) as checkpoints,
            SUM(CASE WHEN report_type = 'oplan' THEN 1 ELSE 0 END) as oplans
        FROM (
            SELECT 'patrol' as report_type, submitted_at FROM patrol_activities WHERE status = 'approved'
            UNION ALL
            SELECT 'checkpoint' as report_type, submitted_at FROM checkpoint_activities WHERE status = 'approved'
            UNION ALL
            SELECT 'oplan' as report_type, submitted_at FROM oplan_activities WHERE status = 'approved'
        ) as all_reports
        WHERE YEAR(submitted_at) = $year AND MONTH(submitted_at) = $month
        GROUP BY DATE(submitted_at)
    ";

    $daily_results = $conn->query($daily_query);

    // Merge actual data with days array
    while ($row = $daily_results->fetch_assoc()) {
        $date = $row['report_date'];
        if (isset($days_in_month[$date])) {
            $days_in_month[$date]['reports'] = $row['total_reports'];
            $days_in_month[$date]['patrols'] = $row['patrols'];
            $days_in_month[$date]['checkpoints'] = $row['checkpoints'];
            $days_in_month[$date]['oplans'] = $row['oplans'];
        }
    }

    // Group days by week (Sunday to Saturday)
    $weeks = [];
    $week_number = 0;
    $current_week = [];

    foreach ($days_in_month as $date => $day_data) {
        $day_of_week = $day_data['day_of_week'];
        
        // Start new week on Sunday
        if ($day_of_week == 'Sunday' && !empty($current_week)) {
            $weeks[$week_number++] = $current_week;
            $current_week = [];
        }
        
        $current_week[$day_of_week] = $day_data;
    }

    // Add the last week
    if (!empty($current_week)) {
        $weeks[$week_number] = $current_week;
    }

    // Calculate totals for the month
    $month_totals = [
        'reports' => array_sum(array_column($days_in_month, 'reports')),
        'patrols' => array_sum(array_column($days_in_month, 'patrols')),
        'checkpoints' => array_sum(array_column($days_in_month, 'checkpoints')),
        'oplans' => array_sum(array_column($days_in_month, 'oplans'))
    ];

    // Order of days for display
    $day_order = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
}

// ===== YEARLY VIEW =====
if ($view == 'yearly') {
    // Get monthly breakdown for the year
    $monthly_query = "
        SELECT 
            MONTH(submitted_at) as report_month,
            DATE_FORMAT(submitted_at, '%M') as month_name,
            COUNT(*) as total_reports,
            SUM(CASE WHEN report_type = 'patrol' THEN 1 ELSE 0 END) as patrols,
            SUM(CASE WHEN report_type = 'checkpoint' THEN 1 ELSE 0 END) as checkpoints,
            SUM(CASE WHEN report_type = 'oplan' THEN 1 ELSE 0 END) as oplans
        FROM (
            SELECT 'patrol' as report_type, submitted_at FROM patrol_activities WHERE status = 'approved'
            UNION ALL
            SELECT 'checkpoint' as report_type, submitted_at FROM checkpoint_activities WHERE status = 'approved'
            UNION ALL
            SELECT 'oplan' as report_type, submitted_at FROM oplan_activities WHERE status = 'approved'
        ) as all_reports
        WHERE YEAR(submitted_at) = $selected_year
        GROUP BY MONTH(submitted_at)
        ORDER BY report_month
    ";
    
    $monthly_reports = $conn->query($monthly_query);
    
    // Get yearly summary
    $yearly_summary_query = "
        SELECT 
            COUNT(*) as total_reports,
            SUM(CASE WHEN report_type = 'patrol' THEN 1 ELSE 0 END) as patrols,
            SUM(CASE WHEN report_type = 'checkpoint' THEN 1 ELSE 0 END) as checkpoints,
            SUM(CASE WHEN report_type = 'oplan' THEN 1 ELSE 0 END) as oplans,
            
            (SELECT COALESCE(SUM(arrested_accomplishment), 0) FROM checkpoint_activities WHERE YEAR(submitted_at) = $selected_year AND status = 'approved') +
            (SELECT COALESCE(SUM(arrests_made), 0) FROM oplan_activities WHERE YEAR(submitted_at) = $selected_year AND status = 'approved') as total_arrests,
            
            (SELECT COALESCE(SUM(firearms_seized), 0) FROM oplan_activities WHERE YEAR(submitted_at) = $selected_year AND status = 'approved') as firearms,
            (SELECT COALESCE(SUM(contraband_kg), 0) FROM oplan_activities WHERE YEAR(submitted_at) = $selected_year AND status = 'approved') as contraband
        FROM (
            SELECT 'patrol' as report_type, submitted_at FROM patrol_activities WHERE status = 'approved'
            UNION ALL
            SELECT 'checkpoint' as report_type, submitted_at FROM checkpoint_activities WHERE status = 'approved'
            UNION ALL
            SELECT 'oplan' as report_type, submitted_at FROM oplan_activities WHERE status = 'approved'
        ) as all_reports
        WHERE YEAR(submitted_at) = $selected_year
    ";
    
    $yearly_summary = $conn->query($yearly_summary_query)->fetch_assoc();
}

// Get available years for dropdown
$years_query = "
    SELECT DISTINCT YEAR(submitted_at) as year FROM patrol_activities WHERE status = 'approved'
    UNION
    SELECT DISTINCT YEAR(submitted_at) as year FROM checkpoint_activities WHERE status = 'approved'
    UNION
    SELECT DISTINCT YEAR(submitted_at) as year FROM oplan_activities WHERE status = 'approved'
    ORDER BY year DESC
";
$years = $conn->query($years_query);

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
    <title>PNP | All Reports</title>
    
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
        
        /* View tabs */
        .view-tab {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem 0.5rem 0 0;
            font-weight: 500;
            transition: all 0.2s;
            font-size: 1rem;
        }
        .view-tab.active {
            background: white;
            color: #08324f;
            border-bottom: 3px solid #ffc107;
            box-shadow: 0 -2px 8px rgba(0,0,0,0.05);
        }
        .view-tab.inactive {
            background: #e2e8f0;
            color: #4a5568;
            cursor: pointer;
        }
        .view-tab.inactive:hover {
            background: #cbd5e0;
        }
        
        /* Card styles */
        .stat-card {
            transition: all 0.3s ease;
            border-left-width: 4px;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.2);
        }
        
        /* Table styles */
        .table-container {
            overflow-x: auto;
            border-radius: 0.5rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #08324f;
            color: white;
            padding: 0.75rem 0.5rem;
            font-weight: 600;
            font-size: 0.8rem;
            white-space: nowrap;
        }
        td {
            padding: 0.75rem 0.5rem;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.9rem;
        }
        tr:hover {
            background-color: #f9fafb;
        }
        
        /* Badge styles */
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 600;
            white-space: nowrap;
        }
        
        /* Filter card */
        .filter-card {
            background: white;
            border-radius: 0.75rem;
            padding: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 1rem;
        }
        
        /* Calendar day */
        .calendar-day {
            transition: all 0.2s;
            min-height: 100px;
        }
        .calendar-day:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        /* Print styles */
        @media print {
            .no-print, .sidebar, .view-tabs, .filter-section, button, .dropdown {
                display: none !important;
            }
            body { background: white; }
            .print-area { display: block !important; }
        }
    </style>
</head>
<body class="flex flex-col md:flex-row bg-[#08324f] min-h-screen">

    <!-- Mobile Menu Button -->
    <button id="mobileMenuBtn" class="md:hidden fixed top-4 left-4 z-50 bg-[#1e4a6a] text-white p-3 rounded-lg shadow-lg no-print">
        <i class="fas fa-bars text-xl"></i>
    </button>

    <!-- Mobile Menu Overlay -->
    <div id="menuOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden no-print" onclick="closeMobileMenu()"></div>

    <!-- Sidebar -->
    <div id="sidebar" class="w-full md:w-[260px] bg-[#08324f] text-white h-screen overflow-y-auto sidebar-scroll sidebar-mobile fixed top-0 left-[-100%] md:left-0 md:sticky z-50 transition-all duration-300 ease-in-out no-print">
        
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
            <li class="bg-[#1e4a6a] rounded-lg"><a href="all_reports.php" class="flex items-center gap-3 p-3"><i class="fas fa-folder-open w-5 text-yellow-400"></i> All Reports</a></li>
            <li><a href="activity_logs.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1e4a6a] transition"><i class="fas fa-history w-5"></i> Activity Logs</a></li>
            
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
        <div class="bg-white p-4 md:p-6 rounded-lg shadow-md mb-4 border-l-4 border-yellow-400 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 no-print">
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-[#08324f] flex items-center gap-2">
                    <i class="fas fa-folder-open text-yellow-500"></i>
                    All Reports
                </h2>
                <p class="text-sm text-gray-600 mt-1">All reports are automatically approved</p>
            </div>
            <div class="bg-green-100 text-green-800 px-4 py-2 rounded-full text-sm font-semibold">
                <i class="fas fa-check-circle mr-1"></i> Auto-Approved
            </div>
        </div>

        <!-- View Tabs -->
        <div class="flex gap-1 mb-4 no-print">
            <a href="?view=daily&date=<?php echo $selected_date; ?>" class="view-tab <?php echo $view == 'daily' ? 'active' : 'inactive'; ?>">
                <i class="fas fa-calendar-day mr-2"></i>Daily
            </a>
            <a href="?view=monthly&month=<?php echo $selected_month; ?>" class="view-tab <?php echo $view == 'monthly' ? 'active' : 'inactive'; ?>">
                <i class="fas fa-calendar-alt mr-2"></i>Monthly
            </a>
            <a href="?view=yearly&year=<?php echo $selected_year; ?>" class="view-tab <?php echo $view == 'yearly' ? 'active' : 'inactive'; ?>">
                <i class="fas fa-calendar mr-2"></i>Yearly
            </a>
        </div>

        <!-- Date Selector -->
        <div class="filter-card no-print">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="view" value="<?php echo $view; ?>">
                
                <?php if ($view == 'daily'): ?>
                <div class="flex-1 min-w-[250px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Select Date</label>
                    <input type="date" name="date" value="<?php echo $selected_date; ?>" class="w-full p-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#1f6fb2]">
                </div>
                
                <?php elseif ($view == 'monthly'): ?>
                <div class="flex-1 min-w-[250px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Select Month</label>
                    <input type="month" name="month" value="<?php echo $selected_month; ?>" class="w-full p-2 border border-gray-300 rounded-lg text-sm">
                </div>
                
                <?php elseif ($view == 'yearly'): ?>
                <div class="flex-1 min-w-[250px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Select Year</label>
                    <select name="year" class="w-full p-2 border border-gray-300 rounded-lg text-sm">
                        <?php 
                        $years->data_seek(0);
                        while ($year = $years->fetch_assoc()): 
                        ?>
                        <option value="<?php echo $year['year']; ?>" <?php echo $selected_year == $year['year'] ? 'selected' : ''; ?>>
                            <?php echo $year['year']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div>
                    <button type="submit" class="px-4 py-2 bg-[#1f6fb2] text-white rounded-lg hover:bg-[#0a3d62] transition text-sm">
                        <i class="fas fa-search mr-1"></i> Go
                    </button>
                </div>
            </form>
        </div>

        <!-- ===== DAILY VIEW ===== -->
        <?php if ($view == 'daily'): ?>
        
        <!-- Daily Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-blue-500">
                <p class="text-xs text-gray-500">📅 Date</p>
                <p class="text-lg font-bold text-[#08324f]"><?php echo date('F d, Y', strtotime($selected_date)); ?></p>
                <p class="text-xs text-gray-500"><?php echo date('l', strtotime($selected_date)); ?></p>
            </div>
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-green-500">
                <p class="text-xs text-gray-500">📊 Total Reports</p>
                <p class="text-2xl font-bold text-[#08324f]"><?php echo $summary['total_reports'] ?? 0; ?></p>
            </div>
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-red-500">
                <p class="text-xs text-gray-500">🚔 Arrests</p>
                <p class="text-2xl font-bold text-[#08324f]"><?php echo $summary['total_arrests'] ?? 0; ?></p>
            </div>
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-purple-500">
                <p class="text-xs text-gray-500">🔫 Firearms / Contraband</p>
                <p class="text-lg font-bold text-[#08324f]"><?php echo $summary['firearms'] ?? 0; ?> / <?php echo number_format($summary['contraband'] ?? 0, 2); ?> kg</p>
            </div>
        </div>

        <!-- Type Breakdown -->
        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
                <p class="text-xs text-gray-600">🚶 Patrols</p>
                <p class="text-2xl font-bold text-blue-700"><?php echo $summary['patrols'] ?? 0; ?></p>
            </div>
            <div class="bg-red-50 p-4 rounded-lg border-l-4 border-red-500">
                <p class="text-xs text-gray-600">🚧 Checkpoints</p>
                <p class="text-2xl font-bold text-red-700"><?php echo $summary['checkpoints'] ?? 0; ?></p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500">
                <p class="text-xs text-gray-600">🛡️ Oplans</p>
                <p class="text-2xl font-bold text-green-700"><?php echo $summary['oplans'] ?? 0; ?></p>
            </div>
        </div>

        <!-- Disposition Summary -->
        <div class="bg-white p-4 rounded-lg shadow-md mb-4">
            <h3 class="text-sm font-semibold text-[#08324f] mb-3 flex items-center gap-2">
                <i class="fas fa-balance-scale text-yellow-500"></i>
                Disposition Summary
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                <div><span class="text-xs text-gray-500">Fixed:</span> <span class="font-bold"><?php echo $summary['total_fixed'] ?? 0; ?></span></div>
                <div><span class="text-xs text-gray-500">Fined:</span> <span class="font-bold"><?php echo $summary['total_fined'] ?? 0; ?></span></div>
                <div><span class="text-xs text-gray-500">Warned:</span> <span class="font-bold"><?php echo $summary['total_warned'] ?? 0; ?></span></div>
                <div><span class="text-xs text-gray-500">Charged:</span> <span class="font-bold"><?php echo $summary['total_charged'] ?? 0; ?></span></div>
                <div><span class="text-xs text-gray-500">Community:</span> <span class="font-bold"><?php echo $summary['total_community'] ?? 0; ?></span></div>
            </div>
        </div>

        <!-- Violations Summary -->
        <div class="bg-white p-4 rounded-lg shadow-md mb-4">
            <h3 class="text-sm font-semibold text-[#08324f] mb-3 flex items-center gap-2">
                <i class="fas fa-gavel text-yellow-500"></i>
                Ordinance Violations
            </h3>
            <div class="grid grid-cols-3 md:grid-cols-6 gap-3">
                <div><span class="text-xs text-gray-500">Drinking:</span> <span class="font-bold"><?php echo $violations['drinking'] ?? 0; ?></span></div>
                <div><span class="text-xs text-gray-500">Smoking:</span> <span class="font-bold"><?php echo $violations['smoking'] ?? 0; ?></span></div>
                <div><span class="text-xs text-gray-500">Half-Naked:</span> <span class="font-bold"><?php echo $violations['halfnaked'] ?? 0; ?></span></div>
                <div><span class="text-xs text-gray-500">Curfew:</span> <span class="font-bold"><?php echo $violations['curfew'] ?? 0; ?></span></div>
                <div><span class="text-xs text-gray-500">Vandalism:</span> <span class="font-bold"><?php echo $violations['vandalism'] ?? 0; ?></span></div>
                <div><span class="text-xs text-gray-500">Other:</span> <span class="font-bold"><?php echo $violations['other'] ?? 0; ?></span></div>
            </div>
        </div>

        <!-- Detailed Reports Table -->
        <?php
        $detailed_query = "
            SELECT 
                'patrol' as report_type,
                patrol_id as id,
                patrol_type as subtype,
                specific_location,
                submitted_at,
                user_id,
                barangay_id,
                accomplishment_description
            FROM patrol_activities
            WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved'
            
            UNION ALL
            
            SELECT 
                'checkpoint' as report_type,
                checkpoint_id as id,
                'Checkpoint' as subtype,
                specific_location,
                submitted_at,
                user_id,
                barangay_id,
                accomplishment_description
            FROM checkpoint_activities
            WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved'
            
            UNION ALL
            
            SELECT 
                'oplan' as report_type,
                oplan_id as id,
                oplan_type as subtype,
                specific_location,
                submitted_at,
                user_id,
                barangay_id,
                accomplishment_description
            FROM oplan_activities
            WHERE DATE(submitted_at) = '$selected_date' AND status = 'approved'
            
            ORDER BY submitted_at DESC
        ";
        
        $detailed_reports = $conn->query($detailed_query);
        ?>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-4 bg-gray-100 border-b font-semibold">
                <span><i class="fas fa-list mr-2 text-[#08324f]"></i> Detailed Reports for <?php echo date('F d, Y', strtotime($selected_date)); ?></span>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Type</th>
                            <th>Officer</th>
                            <th>Barangay</th>
                            <th>Location</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($detailed_reports->num_rows == 0): ?>
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-500">
                                <i class="fas fa-folder-open text-4xl mb-3 text-gray-300"></i>
                                <p>No reports for this day</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php while ($report = $detailed_reports->fetch_assoc()): 
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
                        <tr>
                            <td class="whitespace-nowrap"><?php echo date('h:i A', strtotime($report['submitted_at'])); ?></td>
                            <td>
                                <span class="badge 
                                    <?php echo $report['report_type'] == 'patrol' ? 'bg-blue-100 text-blue-800' : ($report['report_type'] == 'checkpoint' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'); ?>">
                                    <i class="fas 
                                        <?php echo $report['report_type'] == 'patrol' ? 'fa-walking' : ($report['report_type'] == 'checkpoint' ? 'fa-map-marker-alt' : 'fa-shield-alt'); ?> mr-1">
                                    </i>
                                    <?php echo $report['subtype']; ?>
                                </span>
                            </td>
                            <td><?php echo $officer_name; ?></td>
                            <td><?php echo $barangay_name; ?></td>
                            <td class="max-w-xs truncate" title="<?php echo htmlspecialchars($report['specific_location']); ?>">
                                <?php echo htmlspecialchars($report['specific_location']); ?>
                            </td>
                            <td>
                                <a href="view_report.php?type=<?php echo $report['report_type']; ?>&id=<?php echo $report['id']; ?>" 
                                   class="bg-[#1f6fb2] text-white px-3 py-1 rounded text-xs hover:bg-[#0a3d62] transition inline-flex items-center gap-1">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ===== MONTHLY VIEW ===== -->
        <?php elseif ($view == 'monthly'): ?>

        <!-- Monthly Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-blue-500">
                <p class="text-xs text-gray-500">📅 Month</p>
                <p class="text-lg font-bold text-[#08324f]"><?php echo date('F Y', strtotime($selected_month . '-01')); ?></p>
            </div>
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-green-500">
                <p class="text-xs text-gray-500">📊 Total Reports</p>
                <p class="text-2xl font-bold text-[#08324f]"><?php echo $month_totals['reports']; ?></p>
            </div>
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-red-500">
                <p class="text-xs text-gray-500">🚔 Arrests</p>
                <p class="text-2xl font-bold text-[#08324f]"><?php echo $monthly_summary['total_arrests'] ?? 0; ?></p>
            </div>
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-purple-500">
                <p class="text-xs text-gray-500">🔫 Firearms / Contraband</p>
                <p class="text-lg font-bold text-[#08324f]"><?php echo $monthly_summary['firearms'] ?? 0; ?> / <?php echo number_format($monthly_summary['contraband'] ?? 0, 2); ?> kg</p>
            </div>
        </div>

        <!-- Monthly Type Summary -->
        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
                <p class="text-xs text-gray-600">🚶 Patrols</p>
                <p class="text-2xl font-bold text-blue-700"><?php echo $month_totals['patrols']; ?></p>
            </div>
            <div class="bg-red-50 p-4 rounded-lg border-l-4 border-red-500">
                <p class="text-xs text-gray-600">🚧 Checkpoints</p>
                <p class="text-2xl font-bold text-red-700"><?php echo $month_totals['checkpoints']; ?></p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500">
                <p class="text-xs text-gray-600">🛡️ Oplans</p>
                <p class="text-2xl font-bold text-green-700"><?php echo $month_totals['oplans']; ?></p>
            </div>
        </div>

        <!-- Calendar View -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="p-4 bg-gray-100 border-b font-semibold">
                <span><i class="fas fa-calendar-alt mr-2 text-[#08324f]"></i> Monthly Calendar View</span>
            </div>
            <div class="p-4">
                <?php foreach ($weeks as $week_index => $week): ?>
                <div class="mb-4 last:mb-0">
                    <!-- Week Header -->
                    <div class="bg-[#08324f] text-white px-3 py-1 rounded-t-lg text-sm">
                        Week <?php echo $week_index + 1; ?>
                    </div>
                    
                    <!-- Days of Week Header -->
                    <div class="grid grid-cols-7 gap-1 mt-1 mb-1">
                        <?php foreach ($day_order as $day): ?>
                        <div class="text-center text-xs font-semibold <?php echo $day == 'Sunday' ? 'text-red-600' : 'text-gray-700'; ?>">
                            <?php echo substr($day, 0, 3); ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Week Grid -->
                    <div class="grid grid-cols-7 gap-1">
                        <?php foreach ($day_order as $day): ?>
                            <?php if (isset($week[$day])): 
                                $day_data = $week[$day];
                                $has_reports = $day_data['reports'] > 0;
                            ?>
                            <a href="?view=daily&date=<?php echo $day_data['date']; ?>" 
                               class="block p-2 border rounded-lg text-center <?php echo $has_reports ? 'bg-white hover:bg-blue-50 hover:border-blue-300' : 'bg-gray-50 cursor-default'; ?>">
                                <div class="text-sm font-bold <?php echo $day == 'Sunday' ? 'text-red-600' : 'text-gray-700'; ?>">
                                    <?php echo $day_data['day_number']; ?>
                                </div>
                                <?php if ($has_reports): ?>
                                <div class="text-xs font-semibold text-blue-600">
                                    <?php echo $day_data['reports']; ?> reports
                                </div>
                                <?php endif; ?>
                            </a>
                            <?php else: ?>
                            <div class="p-2 border rounded-lg bg-gray-50 text-gray-400 text-center">
                                -
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Daily Breakdown Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-4 bg-gray-100 border-b font-semibold">
                <span><i class="fas fa-list mr-2 text-[#08324f]"></i> Daily Breakdown</span>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Day</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Patrols</th>
                            <th class="text-center">Checkpoints</th>
                            <th class="text-center">Oplans</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $displayed = false;
                        foreach ($days_in_month as $date => $day): 
                            if ($day['reports'] > 0):
                                $displayed = true;
                        ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($date)); ?></td>
                            <td class="<?php echo $day['day_of_week'] == 'Sunday' ? 'text-red-600' : ''; ?>">
                                <?php echo $day['day_of_week']; ?>
                            </td>
                            <td class="text-center font-bold"><?php echo $day['reports']; ?></td>
                            <td class="text-center"><?php echo $day['patrols']; ?></td>
                            <td class="text-center"><?php echo $day['checkpoints']; ?></td>
                            <td class="text-center"><?php echo $day['oplans']; ?></td>
                            <td>
                                <a href="?view=daily&date=<?php echo $date; ?>" class="text-blue-600 hover:text-blue-800 text-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php 
                            endif;
                        endforeach; 
                        
                        if (!$displayed):
                        ?>
                        <tr>
                            <td colspan="7" class="p-6 text-center text-gray-500">
                                No reports for this month
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ===== YEARLY VIEW ===== -->
        <?php elseif ($view == 'yearly'): ?>

        <!-- Yearly Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-blue-500">
                <p class="text-xs text-gray-500">📅 Year</p>
                <p class="text-lg font-bold text-[#08324f]"><?php echo $selected_year; ?></p>
            </div>
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-green-500">
                <p class="text-xs text-gray-500">📊 Total Reports</p>
                <p class="text-2xl font-bold text-[#08324f]"><?php echo $yearly_summary['total_reports'] ?? 0; ?></p>
            </div>
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-red-500">
                <p class="text-xs text-gray-500">🚔 Arrests</p>
                <p class="text-2xl font-bold text-[#08324f]"><?php echo $yearly_summary['total_arrests'] ?? 0; ?></p>
            </div>
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-purple-500">
                <p class="text-xs text-gray-500">🔫 Firearms / Contraband</p>
                <p class="text-lg font-bold text-[#08324f]"><?php echo $yearly_summary['firearms'] ?? 0; ?> / <?php echo number_format($yearly_summary['contraband'] ?? 0, 2); ?> kg</p>
            </div>
        </div>

        <!-- Yearly Type Summary -->
        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
                <p class="text-xs text-gray-600">🚶 Patrols</p>
                <p class="text-2xl font-bold text-blue-700"><?php echo $yearly_summary['patrols'] ?? 0; ?></p>
            </div>
            <div class="bg-red-50 p-4 rounded-lg border-l-4 border-red-500">
                <p class="text-xs text-gray-600">🚧 Checkpoints</p>
                <p class="text-2xl font-bold text-red-700"><?php echo $yearly_summary['checkpoints'] ?? 0; ?></p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500">
                <p class="text-xs text-gray-600">🛡️ Oplans</p>
                <p class="text-2xl font-bold text-green-700"><?php echo $yearly_summary['oplans'] ?? 0; ?></p>
            </div>
        </div>

        <!-- Monthly Breakdown Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-4 bg-gray-100 border-b font-semibold">
                <span><i class="fas fa-calendar-alt mr-2 text-[#08324f]"></i> Monthly Breakdown</span>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Patrols</th>
                            <th class="text-center">Checkpoints</th>
                            <th class="text-center">Oplans</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($monthly_reports->num_rows == 0): 
                        ?>
                        <tr>
                            <td colspan="6" class="p-6 text-center text-gray-500">
                                No reports for this year
                            </td>
                        </tr>
                        <?php 
                        else: 
                            while ($month = $monthly_reports->fetch_assoc()): 
                                $month_num = str_pad($month['report_month'], 2, '0', STR_PAD_LEFT);
                        ?>
                        <tr>
                            <td class="font-semibold"><?php echo $month['month_name']; ?></td>
                            <td class="text-center font-bold"><?php echo $month['total_reports']; ?></td>
                            <td class="text-center"><?php echo $month['patrols']; ?></td>
                            <td class="text-center"><?php echo $month['checkpoints']; ?></td>
                            <td class="text-center"><?php echo $month['oplans']; ?></td>
                            <td>
                                <a href="?view=monthly&month=<?php echo $selected_year . '-' . $month_num; ?>" class="text-blue-600 hover:text-blue-800 text-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        endif; 
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php endif; ?>

        <!-- Footer Note -->
        <div class="mt-4 text-xs text-gray-500 text-center no-print">
            <i class="fas fa-check-circle text-green-500 mr-1"></i> All reports are auto-approved
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
    </script>
</body>
</html>
<?php $conn->close(); ?>