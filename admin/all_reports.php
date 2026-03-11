<?php
// =====================================================
// FILE: admin/all_reports.php
// PURPOSE: Display all reports with daily/monthly/yearly views
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
$status = $_GET['status'] ?? 'all';
$barangay_id = isset($_GET['barangay_id']) ? (int)$_GET['barangay_id'] : 0;
$officer_id = isset($_GET['officer_id']) ? (int)$_GET['officer_id'] : 0;
$search = $_GET['search'] ?? '';

// Get all barangays for filter
$barangays = $conn->query("SELECT barangay_id, barangay_name FROM barangays ORDER BY barangay_name");

// Get all officers for filter
$officers = $conn->query("SELECT user_id, rank, first_name, last_name FROM users WHERE role = 'user' ORDER BY last_name, first_name");

// ===== DAILY VIEW =====
if ($view == 'daily') {
    // Get reports for selected date
    $date_condition = "DATE(submitted_at) = '$selected_date'";
    
    // Get summary for the day
    $summary_query = "
        SELECT 
            COUNT(*) as total_reports,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
            
            SUM(CASE WHEN report_type = 'patrol' THEN 1 ELSE 0 END) as patrols,
            SUM(CASE WHEN report_type = 'checkpoint' THEN 1 ELSE 0 END) as checkpoints,
            SUM(CASE WHEN report_type = 'oplan' THEN 1 ELSE 0 END) as oplans,
            
            (SELECT COALESCE(SUM(arrested_accomplishment), 0) FROM checkpoint_activities WHERE DATE(submitted_at) = '$selected_date') +
            (SELECT COALESCE(SUM(arrests_made), 0) FROM oplan_activities WHERE DATE(submitted_at) = '$selected_date') as total_arrests,
            
            (SELECT COALESCE(SUM(firearms_seized), 0) FROM oplan_activities WHERE DATE(submitted_at) = '$selected_date') as firearms,
            (SELECT COALESCE(SUM(contraband_kg), 0) FROM oplan_activities WHERE DATE(submitted_at) = '$selected_date') as contraband
        FROM (
            SELECT 'patrol' as report_type, status, submitted_at FROM patrol_activities
            UNION ALL
            SELECT 'checkpoint' as report_type, status, submitted_at FROM checkpoint_activities
            UNION ALL
            SELECT 'oplan' as report_type, status, submitted_at FROM oplan_activities
        ) as all_reports
        WHERE DATE(submitted_at) = '$selected_date'
    ";
    
    $summary = $conn->query($summary_query)->fetch_assoc();
}

// ===== MONTHLY VIEW =====
if ($view == 'monthly') {
    $year = substr($selected_month, 0, 4);
    $month = substr($selected_month, 5, 2);
    
    // Get monthly summary
    $monthly_summary_query = "
        SELECT 
            COUNT(*) as total_reports,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
            
            SUM(CASE WHEN report_type = 'patrol' THEN 1 ELSE 0 END) as patrols,
            SUM(CASE WHEN report_type = 'checkpoint' THEN 1 ELSE 0 END) as checkpoints,
            SUM(CASE WHEN report_type = 'oplan' THEN 1 ELSE 0 END) as oplans,
            
            (SELECT COALESCE(SUM(arrested_accomplishment), 0) FROM checkpoint_activities WHERE YEAR(submitted_at) = $year AND MONTH(submitted_at) = $month) +
            (SELECT COALESCE(SUM(arrests_made), 0) FROM oplan_activities WHERE YEAR(submitted_at) = $year AND MONTH(submitted_at) = $month) as total_arrests,
            
            (SELECT COALESCE(SUM(firearms_seized), 0) FROM oplan_activities WHERE YEAR(submitted_at) = $year AND MONTH(submitted_at) = $month) as firearms,
            (SELECT COALESCE(SUM(contraband_kg), 0) FROM oplan_activities WHERE YEAR(submitted_at) = $year AND MONTH(submitted_at) = $month) as contraband
        FROM (
            SELECT 'patrol' as report_type, status, submitted_at FROM patrol_activities
            UNION ALL
            SELECT 'checkpoint' as report_type, status, submitted_at FROM checkpoint_activities
            UNION ALL
            SELECT 'oplan' as report_type, status, submitted_at FROM oplan_activities
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
            'approved' => 0,
            'pending' => 0,
            'rejected' => 0
        ];
        
        $current = strtotime('+1 day', $current);
    }

    // Get actual report data for the month
    $daily_query = "
        SELECT 
            DATE(submitted_at) as report_date,
            COUNT(*) as total_reports,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
            
            SUM(CASE WHEN report_type = 'patrol' THEN 1 ELSE 0 END) as patrols,
            SUM(CASE WHEN report_type = 'checkpoint' THEN 1 ELSE 0 END) as checkpoints,
            SUM(CASE WHEN report_type = 'oplan' THEN 1 ELSE 0 END) as oplans
        FROM (
            SELECT 'patrol' as report_type, status, submitted_at FROM patrol_activities
            UNION ALL
            SELECT 'checkpoint' as report_type, status, submitted_at FROM checkpoint_activities
            UNION ALL
            SELECT 'oplan' as report_type, status, submitted_at FROM oplan_activities
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
            $days_in_month[$date]['approved'] = $row['approved'];
            $days_in_month[$date]['pending'] = $row['pending'];
            $days_in_month[$date]['rejected'] = $row['rejected'];
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
        'oplans' => array_sum(array_column($days_in_month, 'oplans')),
        'approved' => array_sum(array_column($days_in_month, 'approved')),
        'pending' => array_sum(array_column($days_in_month, 'pending')),
        'rejected' => array_sum(array_column($days_in_month, 'rejected'))
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
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
            
            SUM(CASE WHEN report_type = 'patrol' THEN 1 ELSE 0 END) as patrols,
            SUM(CASE WHEN report_type = 'checkpoint' THEN 1 ELSE 0 END) as checkpoints,
            SUM(CASE WHEN report_type = 'oplan' THEN 1 ELSE 0 END) as oplans
        FROM (
            SELECT 'patrol' as report_type, status, submitted_at FROM patrol_activities
            UNION ALL
            SELECT 'checkpoint' as report_type, status, submitted_at FROM checkpoint_activities
            UNION ALL
            SELECT 'oplan' as report_type, status, submitted_at FROM oplan_activities
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
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
            
            SUM(CASE WHEN report_type = 'patrol' THEN 1 ELSE 0 END) as patrols,
            SUM(CASE WHEN report_type = 'checkpoint' THEN 1 ELSE 0 END) as checkpoints,
            SUM(CASE WHEN report_type = 'oplan' THEN 1 ELSE 0 END) as oplans,
            
            (SELECT COALESCE(SUM(arrested_accomplishment), 0) FROM checkpoint_activities WHERE YEAR(submitted_at) = $selected_year) +
            (SELECT COALESCE(SUM(arrests_made), 0) FROM oplan_activities WHERE YEAR(submitted_at) = $selected_year) as total_arrests,
            
            (SELECT COALESCE(SUM(firearms_seized), 0) FROM oplan_activities WHERE YEAR(submitted_at) = $selected_year) as firearms,
            (SELECT COALESCE(SUM(contraband_kg), 0) FROM oplan_activities WHERE YEAR(submitted_at) = $selected_year) as contraband
        FROM (
            SELECT 'patrol' as report_type, status, submitted_at FROM patrol_activities
            UNION ALL
            SELECT 'checkpoint' as report_type, status, submitted_at FROM checkpoint_activities
            UNION ALL
            SELECT 'oplan' as report_type, status, submitted_at FROM oplan_activities
        ) as all_reports
        WHERE YEAR(submitted_at) = $selected_year
    ";
    
    $yearly_summary = $conn->query($yearly_summary_query)->fetch_assoc();
}

// Get available years for dropdown
$years_query = "
    SELECT DISTINCT YEAR(submitted_at) as year FROM patrol_activities
    UNION
    SELECT DISTINCT YEAR(submitted_at) as year FROM checkpoint_activities
    UNION
    SELECT DISTINCT YEAR(submitted_at) as year FROM oplan_activities
    ORDER BY year DESC
";
$years = $conn->query($years_query);
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
        .dropdown-content { display: none; }
        .dropdown.active .dropdown-content { display: block; }
        .rotate-180 { transform: rotate(180deg); transition: transform 0.3s; }
        
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
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.25rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border-left: 4px solid #08324f;
            transition: all 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .table-container {
            overflow-x: auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #08324f;
            color: white;
            padding: 14px 10px;
            font-weight: 600;
            font-size: 0.9rem;
            text-align: left;
            white-space: nowrap;
        }
        
        td {
            padding: 12px 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        tr:hover {
            background: #f7fafc;
        }
        
        .badge {
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .calendar-day {
            transition: all 0.2s;
            min-height: 100px;
        }
        .calendar-day:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        @media print {
            .no-print, .sidebar, .view-tabs, .filter-section, button, .dropdown {
                display: none !important;
            }
            body { background: white; }
            .print-area { display: block !important; }
        }
    </style>
</head>
<body class="flex bg-[#0a3d62]">

    <!-- Sidebar -->
    <div class="w-[240px] h-screen bg-[#08324f] text-white p-5 sticky top-0 overflow-y-auto no-print">
        <div class="flex items-center gap-3 mb-6 pb-3 border-b border-[#1a4b6d]">
            <img src="../image/pnplogo.png" class="w-8 h-8 object-contain" alt="PNP Logo">
            <h2 class="text-xl font-semibold">PNP Admin</h2>
        </div>

        <div class="bg-[#1e4a6a] p-3 rounded-lg mb-4 text-center">
            <p class="text-sm text-yellow-400 font-medium"><?php echo $_SESSION['full_name'] ?? 'Admin'; ?></p>
            <p class="text-xs text-gray-300 mt-1"><?php echo $_SESSION['email'] ?? 'admin@pnp.gov.ph'; ?></p>
        </div>

        <ul class="space-y-1">
            <li class="p-3 rounded hover:bg-[#0a3d62]"><a href="admin_dashboard.php" class="text-white no-underline block"><i class="fas fa-tachometer-alt mr-3"></i>Dashboard</a></li>
            <li class="p-3 rounded hover:bg-[#0a3d62]"><a href="checkpoint.php" class="text-white no-underline block"><i class="fas fa-map-marker-alt mr-3"></i>Checkpoint</a></li>
            <li class="dropdown">
                <div class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer flex items-center justify-between" onclick="toggleDropdown(this)">
                    <span><i class="fas fa-walking mr-3"></i>Patrol</span>
                    <i class="fas fa-chevron-down text-xs transition-transform"></i>
                </div>
                <ul class="pl-8 mt-1 space-y-1 dropdown-content">
                    <li class="py-2 px-3 text-sm hover:bg-[#0a3d62] rounded"><a href="footpatrol.php" class="text-white no-underline block">Foot Patrol</a></li>
                    <li class="py-2 px-3 text-sm hover:bg-[#0a3d62] rounded"><a href="mobilepatrol.php" class="text-white no-underline block">Mobile Patrol</a></li>
                    <li class="py-2 px-3 text-sm hover:bg-[#0a3d62] rounded"><a href="motorpatrol.php" class="text-white no-underline block">Motor Patrol</a></li>
                </ul>
            </li>
            <li class="dropdown">
                <div class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer flex items-center justify-between" onclick="toggleDropdown(this)">
                    <span><i class="fas fa-shield-alt mr-3"></i>Oplan</span>
                    <i class="fas fa-chevron-down text-xs transition-transform"></i>
                </div>
                <ul class="pl-8 mt-1 space-y-1 dropdown-content">
                    <li class="py-2 px-3 text-sm hover:bg-[#0a3d62] rounded"><a href="oplanbakal.php" class="text-white no-underline block">Oplan Bakal</a></li>
                    <li class="py-2 px-3 text-sm hover:bg-[#0a3d62] rounded"><a href="oplansita.php" class="text-white no-underline block">Oplan Sita</a></li>
                </ul>
            </li>
            <li class="p-3 rounded hover:bg-[#0a3d62]"><a href="admin_users.php" class="text-white no-underline block"><i class="fas fa-users mr-3"></i>Users</a></li>
            <li class="p-3 rounded hover:bg-[#0a3d62]"><a href="accomplishment_report.php" class="text-white no-underline block"><i class="fas fa-file-alt mr-3"></i>Accomplishment Report</a></li>
            <li class="p-3 rounded bg-[#0a3d62] border-l-4 border-yellow-400"><a href="all_reports.php" class="text-white no-underline block"><i class="fas fa-list mr-3"></i>All Reports</a></li>
            <li class="p-3 rounded hover:bg-[#0a3d62] mt-5 pt-4 border-t border-[#1a4b6d]"><a href="../logout.php" class="text-white no-underline block"><i class="fas fa-sign-out-alt mr-3"></i>Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-6 bg-[#eef2f6] overflow-y-auto h-screen">
        
        <!-- Header -->
        <div class="bg-white p-4 rounded-lg shadow-md mb-4 border-l-4 border-yellow-400 flex justify-between items-center no-print">
            <div>
                <h2 class="text-2xl font-bold text-[#08324f]">📋 All Reports</h2>
                <p class="text-gray-600">View reports by day, month, or year</p>
            </div>
            <div class="flex gap-2">
                <button onclick="exportToCSV('reports-table', 'reports-<?php echo date('Y-m-d'); ?>.csv')" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                    <i class="fas fa-file-csv"></i> Export CSV
                </button>
                <button onclick="printReport()" class="bg-[#1f6fb2] text-white px-4 py-2 rounded-lg hover:bg-[#0a3d62] transition flex items-center gap-2">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>

        <!-- View Tabs -->
        <div class="flex gap-1 mb-4 no-print">
            <a href="?view=daily&date=<?php echo $selected_date; ?>" class="view-tab <?php echo $view == 'daily' ? 'active' : 'inactive'; ?>">
                <i class="fas fa-calendar-day mr-2"></i>Daily View
            </a>
            <a href="?view=monthly&month=<?php echo $selected_month; ?>" class="view-tab <?php echo $view == 'monthly' ? 'active' : 'inactive'; ?>">
                <i class="fas fa-calendar-alt mr-2"></i>Monthly View
            </a>
            <a href="?view=yearly&year=<?php echo $selected_year; ?>" class="view-tab <?php echo $view == 'yearly' ? 'active' : 'inactive'; ?>">
                <i class="fas fa-calendar mr-2"></i>Yearly View
            </a>
        </div>

        <!-- Date Selector with Navigation -->
        <div class="bg-white p-4 rounded-lg shadow-md mb-4 no-print">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="view" value="<?php echo $view; ?>">
                
                <?php if ($view == 'daily'): ?>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Date</label>
                    <div class="flex gap-2">
                        <button type="button" onclick="changeDate(-1, '<?php echo $selected_date; ?>', 'daily')" class="px-3 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <input type="date" name="date" value="<?php echo $selected_date; ?>" class="flex-1 p-2 border border-gray-300 rounded-lg">
                        <button type="button" onclick="changeDate(1, '<?php echo $selected_date; ?>', 'daily')" class="px-3 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                
                <?php elseif ($view == 'monthly'): ?>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Month</label>
                    <div class="flex gap-2">
                        <button type="button" onclick="changeDate(-1, '<?php echo $selected_month; ?>-01', 'monthly')" class="px-3 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <input type="month" name="month" value="<?php echo $selected_month; ?>" class="flex-1 p-2 border border-gray-300 rounded-lg">
                        <button type="button" onclick="changeDate(1, '<?php echo $selected_month; ?>-01', 'monthly')" class="px-3 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                
                <?php elseif ($view == 'yearly'): ?>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Year</label>
                    <div class="flex gap-2">
                        <button type="button" onclick="changeDate(-1, '<?php echo $selected_year; ?>-01-01', 'yearly')" class="px-3 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <select name="year" class="flex-1 p-2 border border-gray-300 rounded-lg">
                            <?php 
                            $years->data_seek(0);
                            while ($year = $years->fetch_assoc()): 
                            ?>
                            <option value="<?php echo $year['year']; ?>" <?php echo $selected_year == $year['year'] ? 'selected' : ''; ?>>
                                <?php echo $year['year']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                        <button type="button" onclick="changeDate(1, '<?php echo $selected_year; ?>-01-01', 'yearly')" class="px-3 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <?php endif; ?>
                
                <div>
                    <button type="submit" class="bg-[#1f6fb2] text-white px-6 py-2 rounded-lg hover:bg-[#0a3d62] transition">
                        <i class="fas fa-search mr-2"></i> Go
                    </button>
                </div>
            </form>
        </div>

        <!-- ===== DAILY VIEW ===== -->
        <?php if ($view == 'daily'): ?>
        
        <!-- Daily Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            <div class="stat-card" data-tooltip="Selected date">
                <p class="text-sm text-gray-500">📅 Date</p>
                <p class="text-xl font-bold"><?php echo date('F d, Y', strtotime($selected_date)); ?></p>
                <p class="text-sm text-gray-500"><?php echo date('l', strtotime($selected_date)); ?></p>
            </div>
            <div class="stat-card" data-tooltip="Total reports submitted">
                <p class="text-sm text-gray-500">📊 Total Reports</p>
                <p class="text-2xl font-bold"><?php echo $summary['total_reports'] ?? 0; ?></p>
            </div>
            <div class="stat-card" data-tooltip="Total arrests made">
                <p class="text-sm text-gray-500">🚔 Arrests</p>
                <p class="text-2xl font-bold"><?php echo $summary['total_arrests'] ?? 0; ?></p>
            </div>
            <div class="stat-card" data-tooltip="Firearms seized / Contraband">
                <p class="text-sm text-gray-500">🔫 Firearms / Contraband</p>
                <p class="text-lg font-bold"><?php echo $summary['firearms'] ?? 0; ?> / <?php echo number_format($summary['contraband'] ?? 0, 2); ?> kg</p>
            </div>
        </div>

        <!-- Status Breakdown -->
        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500">
                <p class="text-xs text-gray-600">✅ Approved</p>
                <p class="text-2xl font-bold text-green-700"><?php echo $summary['approved'] ?? 0; ?></p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg border-l-4 border-yellow-500">
                <p class="text-xs text-gray-600">⏳ Pending</p>
                <p class="text-2xl font-bold text-yellow-700"><?php echo $summary['pending'] ?? 0; ?></p>
            </div>
            <div class="bg-red-50 p-4 rounded-lg border-l-4 border-red-500">
                <p class="text-xs text-gray-600">❌ Rejected</p>
                <p class="text-2xl font-bold text-red-700"><?php echo $summary['rejected'] ?? 0; ?></p>
            </div>
        </div>

        <!-- Type Breakdown -->
        <div class="grid grid-cols-3 gap-3 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg">
                <p class="text-xs text-gray-600">🚶 Patrols</p>
                <p class="text-2xl font-bold text-blue-700"><?php echo $summary['patrols'] ?? 0; ?></p>
            </div>
            <div class="bg-red-50 p-4 rounded-lg">
                <p class="text-xs text-gray-600">🚧 Checkpoints</p>
                <p class="text-2xl font-bold text-red-700"><?php echo $summary['checkpoints'] ?? 0; ?></p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <p class="text-xs text-gray-600">🛡️ Oplans</p>
                <p class="text-2xl font-bold text-green-700"><?php echo $summary['oplans'] ?? 0; ?></p>
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
                status,
                user_id,
                barangay_id,
                accomplishment_description
            FROM patrol_activities
            WHERE DATE(submitted_at) = '$selected_date'
            
            UNION ALL
            
            SELECT 
                'checkpoint' as report_type,
                checkpoint_id as id,
                'Checkpoint' as subtype,
                specific_location,
                submitted_at,
                status,
                user_id,
                barangay_id,
                accomplishment_description
            FROM checkpoint_activities
            WHERE DATE(submitted_at) = '$selected_date'
            
            UNION ALL
            
            SELECT 
                'oplan' as report_type,
                oplan_id as id,
                oplan_type as subtype,
                specific_location,
                submitted_at,
                status,
                user_id,
                barangay_id,
                accomplishment_description
            FROM oplan_activities
            WHERE DATE(submitted_at) = '$selected_date'
            
            ORDER BY submitted_at DESC
        ";
        
        $detailed_reports = $conn->query($detailed_query);
        ?>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-4 bg-gray-100 border-b font-semibold flex justify-between items-center">
                <span><i class="fas fa-list mr-2 text-[#08324f]"></i> Detailed Reports for <?php echo date('F d, Y', strtotime($selected_date)); ?></span>
                <div class="flex gap-2">
                    <input type="text" id="dailySearch" placeholder="Search reports..." class="px-3 py-1 border rounded-lg text-sm" onkeyup="filterTable('dailySearch', 'daily-table')">
                </div>
            </div>
            <div class="table-container">
                <table id="daily-table">
                    <thead>
                        <tr>
                            <th onclick="sortTable(0, 'daily-table')" class="cursor-pointer hover:bg-[#1e4a6a]">Time <i class="fas fa-sort ml-1 text-xs"></i></th>
                            <th onclick="sortTable(1, 'daily-table')" class="cursor-pointer hover:bg-[#1e4a6a]">Type <i class="fas fa-sort ml-1 text-xs"></i></th>
                            <th onclick="sortTable(2, 'daily-table')" class="cursor-pointer hover:bg-[#1e4a6a]">Officer <i class="fas fa-sort ml-1 text-xs"></i></th>
                            <th onclick="sortTable(3, 'daily-table')" class="cursor-pointer hover:bg-[#1e4a6a]">Barangay <i class="fas fa-sort ml-1 text-xs"></i></th>
                            <th>Location</th>
                            <th onclick="sortTable(5, 'daily-table')" class="cursor-pointer hover:bg-[#1e4a6a]">Status <i class="fas fa-sort ml-1 text-xs"></i></th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($detailed_reports->num_rows == 0): ?>
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-500">
                                <i class="fas fa-folder-open text-4xl mb-3"></i>
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
                            <td><?php echo date('h:i A', strtotime($report['submitted_at'])); ?></td>
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
                                <span class="badge 
                                    <?php echo $report['status'] == 'approved' ? 'bg-green-100 text-green-800' : ($report['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                    <?php echo ucfirst($report['status']); ?>
                                </span>
                            </td>
                            <td>
                                <button onclick="toggleDetails(<?php echo $report['id']; ?>)" class="text-blue-600 hover:text-blue-800">
                                    <i id="icon-<?php echo $report['id']; ?>" class="fas fa-chevron-down"></i>
                                </button>
                            </td>
                        </tr>
                        <tr id="details-<?php echo $report['id']; ?>" class="hidden bg-gray-50">
                            <td colspan="7" class="p-4">
                                <div class="text-sm">
                                    <p class="font-semibold mb-2">📝 Accomplishment Description:</p>
                                    <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($report['accomplishment_description'])); ?></p>
                                </div>
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
            <div class="stat-card">
                <p class="text-sm text-gray-500">📅 Month</p>
                <p class="text-xl font-bold"><?php echo date('F Y', strtotime($selected_month . '-01')); ?></p>
            </div>
            <div class="stat-card">
                <p class="text-sm text-gray-500">📊 Total Reports</p>
                <p class="text-2xl font-bold"><?php echo $month_totals['reports']; ?></p>
            </div>
            <div class="stat-card">
                <p class="text-sm text-gray-500">🚔 Arrests</p>
                <p class="text-2xl font-bold"><?php echo $monthly_summary['total_arrests'] ?? 0; ?></p>
            </div>
            <div class="stat-card">
                <p class="text-sm text-gray-500">🔫 Firearms / Contraband</p>
                <p class="text-lg font-bold"><?php echo $monthly_summary['firearms'] ?? 0; ?> / <?php echo number_format($monthly_summary['contraband'] ?? 0, 2); ?> kg</p>
            </div>
        </div>

        <!-- Monthly Status Summary -->
        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500">
                <p class="text-xs text-gray-600">✅ Approved</p>
                <p class="text-2xl font-bold text-green-700"><?php echo $month_totals['approved']; ?></p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg border-l-4 border-yellow-500">
                <p class="text-xs text-gray-600">⏳ Pending</p>
                <p class="text-2xl font-bold text-yellow-700"><?php echo $month_totals['pending']; ?></p>
            </div>
            <div class="bg-red-50 p-4 rounded-lg border-l-4 border-red-500">
                <p class="text-xs text-gray-600">❌ Rejected</p>
                <p class="text-2xl font-bold text-red-700"><?php echo $month_totals['rejected']; ?></p>
            </div>
        </div>

        <!-- Monthly Type Summary -->
        <div class="grid grid-cols-3 gap-3 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg">
                <p class="text-xs text-gray-600">🚶 Patrols</p>
                <p class="text-2xl font-bold text-blue-700"><?php echo $month_totals['patrols']; ?></p>
            </div>
            <div class="bg-red-50 p-4 rounded-lg">
                <p class="text-xs text-gray-600">🚧 Checkpoints</p>
                <p class="text-2xl font-bold text-red-700"><?php echo $month_totals['checkpoints']; ?></p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <p class="text-xs text-gray-600">🛡️ Oplans</p>
                <p class="text-2xl font-bold text-green-700"><?php echo $month_totals['oplans']; ?></p>
            </div>
        </div>

        <!-- CALENDAR STYLE WEEKLY TABLE -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="p-4 bg-gray-100 border-b font-semibold flex justify-between items-center">
                <span><i class="fas fa-calendar-alt mr-2 text-[#08324f]"></i>Weekly Calendar View for <?php echo date('F Y', strtotime($selected_month . '-01')); ?></span>
                <span class="text-sm text-gray-600">Click on a day to view details</span>
            </div>
            
            <div class="p-6">
                <?php foreach ($weeks as $week_index => $week): ?>
                <div class="mb-8 last:mb-0">
                    <!-- Week Header -->
                    <div class="bg-[#08324f] text-white px-4 py-2 rounded-t-lg flex justify-between items-center">
                        <span class="font-semibold"><i class="fas fa-calendar-week mr-2"></i>Week <?php echo $week_index + 1; ?></span>
                        <span class="text-sm opacity-90">
                            <?php 
                            $first_day = reset($week);
                            $last_day = end($week);
                            echo date('M d', strtotime($first_day['date'])) . ' - ' . date('M d, Y', strtotime($last_day['date']));
                            ?>
                        </span>
                    </div>
                    
                    <!-- Days of Week Header -->
                    <div class="grid grid-cols-7 gap-2 mt-3 mb-2">
                        <?php foreach ($day_order as $day): ?>
                        <div class="text-center text-sm font-semibold <?php echo $day == 'Sunday' ? 'text-red-600' : 'text-gray-700'; ?>">
                            <?php echo substr($day, 0, 3); ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Week Grid -->
                    <div class="grid grid-cols-7 gap-2">
                        <?php foreach ($day_order as $day): ?>
                            <?php if (isset($week[$day])): 
                                $day_data = $week[$day];
                                $has_reports = $day_data['reports'] > 0;
                            ?>
                            <a href="?view=daily&date=<?php echo $day_data['date']; ?>" 
                               class="block p-3 border-2 rounded-lg transition-all <?php echo $has_reports ? 'hover:border-blue-400 hover:shadow-lg cursor-pointer bg-white' : 'bg-gray-50 cursor-default border-gray-200'; ?>"
                               <?php if ($has_reports): ?>data-tooltip="Click to view details"<?php endif; ?>>
                                <div class="text-center">
                                    <div class="text-lg font-bold <?php echo $day == 'Sunday' ? 'text-red-600' : 'text-gray-800'; ?>">
                                        <?php echo $day_data['day_number']; ?>
                                    </div>
                                    <?php if ($has_reports): ?>
                                    <div class="mt-2 space-y-1">
                                        <div class="text-sm font-semibold text-blue-600">
                                            <?php echo $day_data['reports']; ?> reports
                                        </div>
                                        <div class="flex justify-center gap-2">
                                            <?php if ($day_data['approved'] > 0): ?>
                                            <span class="w-3 h-3 bg-green-500 rounded-full" title="Approved: <?php echo $day_data['approved']; ?>"></span>
                                            <?php endif; ?>
                                            <?php if ($day_data['pending'] > 0): ?>
                                            <span class="w-3 h-3 bg-yellow-500 rounded-full" title="Pending: <?php echo $day_data['pending']; ?>"></span>
                                            <?php endif; ?>
                                            <?php if ($day_data['rejected'] > 0): ?>
                                            <span class="w-3 h-3 bg-red-500 rounded-full" title="Rejected: <?php echo $day_data['rejected']; ?>"></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-xs text-gray-500 flex justify-center gap-2">
                                            <span class="text-blue-600" title="Patrols">🚶<?php echo $day_data['patrols']; ?></span>
                                            <span class="text-red-600" title="Checkpoints">🚧<?php echo $day_data['checkpoints']; ?></span>
                                            <span class="text-green-600" title="Oplans">🛡️<?php echo $day_data['oplans']; ?></span>
                                        </div>
                                    </div>
                                    <?php else: ?>
                                    <div class="text-xs text-gray-400 mt-3">No reports</div>
                                    <?php endif; ?>
                                </div>
                            </a>
                            <?php else: ?>
                            <div class="p-3 border-2 border-gray-100 rounded-lg bg-gray-50 opacity-40">
                                <div class="text-center text-gray-400 text-lg">-</div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Detailed Daily Breakdown Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-4 bg-gray-100 border-b font-semibold flex justify-between items-center">
                <span><i class="fas fa-list mr-2 text-[#08324f]"></i> Detailed Daily Breakdown</span>
                <input type="text" id="monthlySearch" placeholder="Search days..." class="px-3 py-1 border rounded-lg text-sm" onkeyup="filterTable('monthlySearch', 'monthly-table')">
            </div>
            <div class="table-container">
                <table id="monthly-table">
                    <thead>
                        <tr>
                            <th onclick="sortTable(0, 'monthly-table')" class="cursor-pointer hover:bg-[#1e4a6a]">Date <i class="fas fa-sort ml-1 text-xs"></i></th>
                            <th onclick="sortTable(1, 'monthly-table')" class="cursor-pointer hover:bg-[#1e4a6a]">Day <i class="fas fa-sort ml-1 text-xs"></i></th>
                            <th onclick="sortTable(2, 'monthly-table')" class="cursor-pointer hover:bg-[#1e4a6a]">Total <i class="fas fa-sort ml-1 text-xs"></i></th>
                            <th onclick="sortTable(3, 'monthly-table')" class="cursor-pointer hover:bg-[#1e4a6a]">Patrols <i class="fas fa-sort ml-1 text-xs"></i></th>
                            <th onclick="sortTable(4, 'monthly-table')" class="cursor-pointer hover:bg-[#1e4a6a]">Checkpoints <i class="fas fa-sort ml-1 text-xs"></i></th>
                            <th onclick="sortTable(5, 'monthly-table')" class="cursor-pointer hover:bg-[#1e4a6a]">Oplans <i class="fas fa-sort ml-1 text-xs"></i></th>
                            <th onclick="sortTable(6, 'monthly-table')" class="cursor-pointer hover:bg-[#1e4a6a]">Approved <i class="fas fa-sort ml-1 text-xs"></i></th>
                            <th onclick="sortTable(7, 'monthly-table')" class="cursor-pointer hover:bg-[#1e4a6a]">Pending <i class="fas fa-sort ml-1 text-xs"></i></th>
                            <th onclick="sortTable(8, 'monthly-table')" class="cursor-pointer hover:bg-[#1e4a6a]">Rejected <i class="fas fa-sort ml-1 text-xs"></i></th>
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
                        <tr data-date="<?php echo $date; ?>">
                            <td><?php echo date('M d, Y', strtotime($date)); ?></td>
                            <td class="<?php echo $day['day_of_week'] == 'Sunday' ? 'text-red-600 font-semibold' : ''; ?>">
                                <?php echo $day['day_of_week']; ?>
                            </td>
                            <td class="font-bold"><?php echo $day['reports']; ?></td>
                            <td><?php echo $day['patrols']; ?></td>
                            <td><?php echo $day['checkpoints']; ?></td>
                            <td><?php echo $day['oplans']; ?></td>
                            <td class="text-green-600 font-semibold"><?php echo $day['approved']; ?></td>
                            <td class="text-yellow-600 font-semibold"><?php echo $day['pending']; ?></td>
                            <td class="text-red-600 font-semibold"><?php echo $day['rejected']; ?></td>
                            <td>
                                <a href="?view=daily&date=<?php echo $date; ?>" class="text-blue-600 hover:text-blue-800" data-tooltip="View daily details">
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
                            <td colspan="10" class="p-8 text-center text-gray-500">
                                <i class="fas fa-calendar-times text-4xl mb-3"></i>
                                <p>No reports for this month</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="bg-gray-100 font-semibold">
                        <tr>
                            <td colspan="2" class="text-right">TOTAL:</td>
                            <td><?php echo $month_totals['reports']; ?></td>
                            <td><?php echo $month_totals['patrols']; ?></td>
                            <td><?php echo $month_totals['checkpoints']; ?></td>
                            <td><?php echo $month_totals['oplans']; ?></td>
                            <td class="text-green-600"><?php echo $month_totals['approved']; ?></td>
                            <td class="text-yellow-600"><?php echo $month_totals['pending']; ?></td>
                            <td class="text-red-600"><?php echo $month_totals['rejected']; ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- ===== YEARLY VIEW ===== -->
        <?php elseif ($view == 'yearly'): ?>

        <!-- Yearly Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            <div class="stat-card">
                <p class="text-sm text-gray-500">📅 Year</p>
                <p class="text-xl font-bold"><?php echo $selected_year; ?></p>
            </div>
            <div class="stat-card">
                <p class="text-sm text-gray-500">📊 Total Reports</p>
                <p class="text-2xl font-bold"><?php echo $yearly_summary['total_reports'] ?? 0; ?></p>
            </div>
            <div class="stat-card">
                <p class="text-sm text-gray-500">🚔 Arrests</p>
                <p class="text-2xl font-bold"><?php echo $yearly_summary['total_arrests'] ?? 0; ?></p>
            </div>
            <div class="stat-card">
                <p class="text-sm text-gray-500">🔫 Firearms / Contraband</p>
                <p class="text-lg font-bold"><?php echo $yearly_summary['firearms'] ?? 0; ?> / <?php echo number_format($yearly_summary['contraband'] ?? 0, 2); ?> kg</p>
            </div>
        </div>

        <!-- Yearly Status Summary -->
        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500">
                <p class="text-xs text-gray-600">✅ Approved</p>
                <p class="text-2xl font-bold text-green-700"><?php echo $yearly_summary['approved'] ?? 0; ?></p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg border-l-4 border-yellow-500">
                <p class="text-xs text-gray-600">⏳ Pending</p>
                <p class="text-2xl font-bold text-yellow-700"><?php echo $yearly_summary['pending'] ?? 0; ?></p>
            </div>
            <div class="bg-red-50 p-4 rounded-lg border-l-4 border-red-500">
                <p class="text-xs text-gray-600">❌ Rejected</p>
                <p class="text-2xl font-bold text-red-700"><?php echo $yearly_summary['rejected'] ?? 0; ?></p>
            </div>
        </div>

        <!-- Yearly Type Summary -->
        <div class="grid grid-cols-3 gap-3 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg">
                <p class="text-xs text-gray-600">🚶 Patrols</p>
                <p class="text-2xl font-bold text-blue-700"><?php echo $yearly_summary['patrols'] ?? 0; ?></p>
            </div>
            <div class="bg-red-50 p-4 rounded-lg">
                <p class="text-xs text-gray-600">🚧 Checkpoints</p>
                <p class="text-2xl font-bold text-red-700"><?php echo $yearly_summary['checkpoints'] ?? 0; ?></p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <p class="text-xs text-gray-600">🛡️ Oplans</p>
                <p class="text-2xl font-bold text-green-700"><?php echo $yearly_summary['oplans'] ?? 0; ?></p>
            </div>
        </div>

        <!-- Monthly Breakdown Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-4 bg-gray-100 border-b font-semibold flex justify-between items-center">
                <span><i class="fas fa-calendar-alt mr-2 text-[#08324f]"></i> Monthly Breakdown for <?php echo $selected_year; ?></span>
                <input type="text" id="yearlySearch" placeholder="Search months..." class="px-3 py-1 border rounded-lg text-sm" onkeyup="filterTable('yearlySearch', 'yearly-table')">
            </div>
            <div class="table-container">
                <table id="yearly-table">
                    <thead>
                        <tr>
                            <th onclick="sortTable(0, 'yearly-table')" class="cursor-pointer hover:bg-[#1e4a6a]">Month <i class="fas fa-sort ml-1 text-xs"></i></th>
                            <th onclick="sortTable(1, 'yearly-table')" class="cursor-pointer hover:bg-[#1e4a6a]">Total <i class="fas fa-sort ml-1 text-xs"></i></th>
                            <th onclick="sortTable(2, 'yearly-table')" class="cursor-pointer hover:bg-[#1e4a6a]">Patrols <i class="fas fa-sort ml-1 text-xs"></i></th>
                            <th onclick="sortTable(3, 'yearly-table')" class="cursor-pointer hover:bg-[#1e4a6a]">Checkpoints <i class="fas fa-sort ml-1 text-xs"></i></th>
                            <th onclick="sortTable(4, 'yearly-table')" class="cursor-pointer hover:bg-[#1e4a6a]">Oplans <i class="fas fa-sort ml-1 text-xs"></i></th>
                            <th onclick="sortTable(5, 'yearly-table')" class="cursor-pointer hover:bg-[#1e4a6a]">Approved <i class="fas fa-sort ml-1 text-xs"></i></th>
                            <th onclick="sortTable(6, 'yearly-table')" class="cursor-pointer hover:bg-[#1e4a6a]">Pending <i class="fas fa-sort ml-1 text-xs"></i></th>
                            <th onclick="sortTable(7, 'yearly-table')" class="cursor-pointer hover:bg-[#1e4a6a]">Rejected <i class="fas fa-sort ml-1 text-xs"></i></th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($monthly_reports->num_rows == 0): 
                        ?>
                        <tr>
                            <td colspan="9" class="p-8 text-center text-gray-500">
                                <i class="fas fa-calendar-times text-4xl mb-3"></i>
                                <p>No reports for this year</p>
                            </td>
                        </tr>
                        <?php 
                        else: 
                            while ($month = $monthly_reports->fetch_assoc()): 
                                $month_num = str_pad($month['report_month'], 2, '0', STR_PAD_LEFT);
                        ?>
                        <tr>
                            <td class="font-semibold"><?php echo $month['month_name']; ?></td>
                            <td class="font-bold"><?php echo $month['total_reports']; ?></td>
                            <td><?php echo $month['patrols']; ?></td>
                            <td><?php echo $month['checkpoints']; ?></td>
                            <td><?php echo $month['oplans']; ?></td>
                            <td class="text-green-600 font-semibold"><?php echo $month['approved']; ?></td>
                            <td class="text-yellow-600 font-semibold"><?php echo $month['pending']; ?></td>
                            <td class="text-red-600 font-semibold"><?php echo $month['rejected']; ?></td>
                            <td>
                                <a href="?view=monthly&month=<?php echo $selected_year . '-' . $month_num; ?>" class="text-blue-600 hover:text-blue-800" data-tooltip="View month details">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        endif; 
                        ?>
                    </tbody>
                    <tfoot class="bg-gray-100 font-semibold">
                        <tr>
                            <td class="text-right">TOTAL:</td>
                            <td><?php echo $yearly_summary['total_reports'] ?? 0; ?></td>
                            <td><?php echo $yearly_summary['patrols'] ?? 0; ?></td>
                            <td><?php echo $yearly_summary['checkpoints'] ?? 0; ?></td>
                            <td><?php echo $yearly_summary['oplans'] ?? 0; ?></td>
                            <td class="text-green-600"><?php echo $yearly_summary['approved'] ?? 0; ?></td>
                            <td class="text-yellow-600"><?php echo $yearly_summary['pending'] ?? 0; ?></td>
                            <td class="text-red-600"><?php echo $yearly_summary['rejected'] ?? 0; ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <?php endif; ?>

        <!-- Footer Note -->
        <div class="mt-4 text-center text-sm text-gray-500 no-print">
            <i class="fas fa-info-circle mr-1"></i> Click on any day or month to see detailed reports. Hover over icons for more info.
        </div>
    </div>

    <!-- Link to external JavaScript -->
    <script src="js/all_reports.js"></script>
</body>
</html>
<?php $conn->close(); ?>