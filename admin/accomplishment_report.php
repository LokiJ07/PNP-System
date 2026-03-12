<?php
// =====================================================
// FILE: admin/accomplishment_report.php
// PURPOSE: Detailed accomplishment report with all fields
// IMPROVED: Added disposition, better UI, mobile responsive
// =====================================================

session_start();
require_once '../config/db_connect.php';
requireAdmin();

// Get filter parameters
$from_date = $_GET['from_date'] ?? date('Y-m-01');
$to_date = $_GET['to_date'] ?? date('Y-m-d');

// Build date condition - ONLY APPROVED REPORTS
$date_condition = " AND DATE(submitted_at) BETWEEN '$from_date' AND '$to_date' AND status = 'approved'";

// Get summary statistics
$stats = [];

// Total reports count
$result = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM patrol_activities WHERE 1=1 $date_condition) as patrols,
        (SELECT COUNT(*) FROM checkpoint_activities WHERE 1=1 $date_condition) as checkpoints,
        (SELECT COUNT(*) FROM oplan_activities WHERE 1=1 $date_condition) as oplans
");
$row = $result->fetch_assoc();
$stats['patrols'] = $row['patrols'];
$stats['checkpoints'] = $row['checkpoints'];
$stats['oplans'] = $row['oplans'];
$stats['total_operations'] = $row['patrols'] + $row['checkpoints'] + $row['oplans'];

// Personnel deployed
$result = $conn->query("
    SELECT 
        (SELECT COALESCE(SUM(personnel_count), 0) FROM patrol_activities WHERE 1=1 $date_condition) as patrol_personnel,
        (SELECT COALESCE(SUM(COALESCE(border_personnel, 0) + COALESCE(mobile_personnel, 0)), 0) FROM checkpoint_activities WHERE 1=1 $date_condition) as checkpoint_personnel,
        (SELECT COALESCE(SUM(personnel_count), 0) FROM oplan_activities WHERE 1=1 $date_condition) as oplan_personnel
");
$personnel = $result->fetch_assoc();
$stats['total_personnel'] = $personnel['patrol_personnel'] + $personnel['checkpoint_personnel'] + $personnel['oplan_personnel'];

// Arrests and accomplishments
$result = $conn->query("
    SELECT 
        (SELECT COALESCE(SUM(arrested_accomplishment), 0) FROM checkpoint_activities WHERE 1=1 $date_condition) as checkpoint_arrests,
        (SELECT COALESCE(SUM(arrests_made), 0) FROM oplan_activities WHERE 1=1 $date_condition) as oplan_arrests,
        (SELECT COALESCE(SUM(firearms_seized), 0) FROM oplan_activities WHERE 1=1 $date_condition) as firearms,
        (SELECT COALESCE(SUM(contraband_kg), 0) FROM oplan_activities WHERE 1=1 $date_condition) as contraband,
        (SELECT COALESCE(SUM(tct_ovr_accomplishment), 0) FROM checkpoint_activities WHERE 1=1 $date_condition) as tct_ovr
");
$accomplishments = $result->fetch_assoc();
$stats['total_arrests'] = $accomplishments['checkpoint_arrests'] + $accomplishments['oplan_arrests'];
$stats['firearms'] = $accomplishments['firearms'];
$stats['contraband'] = $accomplishments['contraband'];
$stats['tct_ovr'] = $accomplishments['tct_ovr'];

// Oplan type counts
$result = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM oplan_activities WHERE 1=1 $date_condition AND oplan_type = 'Oplan Bakal') as oplan_bakal,
        (SELECT COUNT(*) FROM oplan_activities WHERE 1=1 $date_condition AND oplan_type = 'Oplan Sita') as oplan_sita
");
$oplan_counts = $result->fetch_assoc();
$stats['oplan_bakal'] = $oplan_counts['oplan_bakal'] ?? 0;
$stats['oplan_sita'] = $oplan_counts['oplan_sita'] ?? 0;

// ===== DISPOSITION TOTALS (NEW) =====
$result = $conn->query("
    SELECT 
        (SELECT COALESCE(SUM(fixed_count), 0) FROM checkpoint_activities WHERE 1=1 $date_condition) +
        (SELECT COALESCE(SUM(fixed_count), 0) FROM oplan_activities WHERE 1=1 $date_condition) as total_fixed,
        
        (SELECT COALESCE(SUM(fined_count), 0) FROM checkpoint_activities WHERE 1=1 $date_condition) +
        (SELECT COALESCE(SUM(fined_count), 0) FROM oplan_activities WHERE 1=1 $date_condition) as total_fined,
        
        (SELECT COALESCE(SUM(warned_count), 0) FROM checkpoint_activities WHERE 1=1 $date_condition) +
        (SELECT COALESCE(SUM(warned_count), 0) FROM oplan_activities WHERE 1=1 $date_condition) as total_warned,
        
        (SELECT COALESCE(SUM(charged_count), 0) FROM checkpoint_activities WHERE 1=1 $date_condition) +
        (SELECT COALESCE(SUM(charged_count), 0) FROM oplan_activities WHERE 1=1 $date_condition) as total_charged,
        
        (SELECT COALESCE(SUM(community_service), 0) FROM checkpoint_activities WHERE 1=1 $date_condition) +
        (SELECT COALESCE(SUM(community_service), 0) FROM oplan_activities WHERE 1=1 $date_condition) as total_community
");
$disposition = $result->fetch_assoc();

// ===== DETAILED PATROL BREAKDOWN =====
$patrol_details = [];
$patrol_query = $conn->query("
    SELECT 
        patrol_type,
        COUNT(*) as count,
        COALESCE(SUM(personnel_count), 0) as total_personnel
    FROM patrol_activities 
    WHERE 1=1 $date_condition
    GROUP BY patrol_type
");
while ($p = $patrol_query->fetch_assoc()) {
    $patrol_details[$p['patrol_type']] = [
        'count' => $p['count'],
        'personnel' => $p['total_personnel']
    ];
}

// ===== DETAILED CHECKPOINT BREAKDOWN =====
$checkpoint_query = $conn->query("
    SELECT 
        COUNT(*) as total_checkpoints,
        COALESCE(SUM(border_control_ops), 0) as border_ops,
        COALESCE(SUM(border_personnel), 0) as border_personnel,
        COALESCE(SUM(mobile_checkpoint_ops), 0) as mobile_ops,
        COALESCE(SUM(mobile_personnel), 0) as mobile_personnel,
        COALESCE(SUM(tct_ovr_accomplishment), 0) as tct_ovr,
        COALESCE(SUM(arrested_accomplishment), 0) as arrests,
        COALESCE(SUM(drinking_violations), 0) as drinking,
        COALESCE(SUM(smoking_violations), 0) as smoking,
        COALESCE(SUM(halfnaked_violations), 0) as halfnaked,
        COALESCE(SUM(curfew_violations), 0) as curfew,
        COALESCE(SUM(vandalism_violations), 0) as vandalism,
        COALESCE(SUM(other_violations), 0) as other_violations
    FROM checkpoint_activities 
    WHERE 1=1 $date_condition
");
$checkpoint_details = $checkpoint_query->fetch_assoc();

// ===== DETAILED OPLAN BREAKDOWN =====
$oplan_details = [];
$oplan_query = $conn->query("
    SELECT 
        oplan_type,
        COUNT(*) as count,
        COALESCE(SUM(arrests_made), 0) as arrests,
        COALESCE(SUM(firearms_seized), 0) as firearms,
        COALESCE(SUM(contraband_kg), 0) as contraband,
        COALESCE(SUM(personnel_count), 0) as personnel,
        COALESCE(SUM(house_visitations), 0) as house_visits,
        COALESCE(SUM(kontra_boga), 0) as kontra_boga,
        COALESCE(SUM(anti_vaping), 0) as anti_vaping,
        COALESCE(SUM(drinking_violations), 0) as drinking,
        COALESCE(SUM(smoking_violations), 0) as smoking,
        COALESCE(SUM(halfnaked_violations), 0) as halfnaked,
        COALESCE(SUM(curfew_violations), 0) as curfew,
        COALESCE(SUM(vandalism_violations), 0) as vandalism,
        COALESCE(SUM(other_violations), 0) as other_violations
    FROM oplan_activities 
    WHERE 1=1 $date_condition
    GROUP BY oplan_type
");
while ($o = $oplan_query->fetch_assoc()) {
    $oplan_details[$o['oplan_type']] = $o;
}

// ===== VIOLATIONS BREAKDOWN =====
$violations_query = $conn->query("
    SELECT 
        (SELECT COALESCE(SUM(drinking_violations), 0) FROM patrol_activities WHERE 1=1 $date_condition) +
        (SELECT COALESCE(SUM(drinking_violations), 0) FROM checkpoint_activities WHERE 1=1 $date_condition) +
        (SELECT COALESCE(SUM(drinking_violations), 0) FROM oplan_activities WHERE 1=1 $date_condition) as drinking,
        
        (SELECT COALESCE(SUM(smoking_violations), 0) FROM patrol_activities WHERE 1=1 $date_condition) +
        (SELECT COALESCE(SUM(smoking_violations), 0) FROM checkpoint_activities WHERE 1=1 $date_condition) +
        (SELECT COALESCE(SUM(smoking_violations), 0) FROM oplan_activities WHERE 1=1 $date_condition) as smoking,
        
        (SELECT COALESCE(SUM(halfnaked_violations), 0) FROM patrol_activities WHERE 1=1 $date_condition) +
        (SELECT COALESCE(SUM(halfnaked_violations), 0) FROM checkpoint_activities WHERE 1=1 $date_condition) +
        (SELECT COALESCE(SUM(halfnaked_violations), 0) FROM oplan_activities WHERE 1=1 $date_condition) as halfnaked,
        
        (SELECT COALESCE(SUM(curfew_violations), 0) FROM patrol_activities WHERE 1=1 $date_condition) +
        (SELECT COALESCE(SUM(curfew_violations), 0) FROM checkpoint_activities WHERE 1=1 $date_condition) +
        (SELECT COALESCE(SUM(curfew_violations), 0) FROM oplan_activities WHERE 1=1 $date_condition) as curfew,
        
        (SELECT COALESCE(SUM(vandalism_violations), 0) FROM patrol_activities WHERE 1=1 $date_condition) +
        (SELECT COALESCE(SUM(vandalism_violations), 0) FROM checkpoint_activities WHERE 1=1 $date_condition) +
        (SELECT COALESCE(SUM(vandalism_violations), 0) FROM oplan_activities WHERE 1=1 $date_condition) as vandalism,
        
        (SELECT COALESCE(SUM(other_violations), 0) FROM patrol_activities WHERE 1=1 $date_condition) +
        (SELECT COALESCE(SUM(other_violations), 0) FROM checkpoint_activities WHERE 1=1 $date_condition) +
        (SELECT COALESCE(SUM(other_violations), 0) FROM oplan_activities WHERE 1=1 $date_condition) as other
");
$violations = $violations_query->fetch_assoc();
$stats['total_violations'] = array_sum($violations);

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
    <title>PNP | Accomplishment Report</title>
    
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
        
        /* PRINT STYLES */
        @media print {
            .no-print, 
            .sidebar, 
            .flex-1 > .bg-white:first-of-type,
            .flex-1 > .bg-white:nth-of-type(2),
            button,
            .dropdown,
            .no-print * {
                display: none !important;
            }
            
            html, body {
                background: white !important;
                margin: 0 !important;
                padding: 10px !important;
                font-size: 11pt;
            }
            
            .print-area {
                display: block !important;
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
            }
            
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 10px 0;
                font-size: 10pt;
            }
            
            th {
                background: #08324f !important;
                color: white !important;
                padding: 6px;
                text-align: left;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            td {
                padding: 4px 6px;
                border-bottom: 1px solid #ddd;
            }
            
            .summary-card {
                background: #f8f9fa !important;
                padding: 8px;
                border-left: 4px solid #08324f;
                margin-bottom: 8px;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        
        /* Card styles */
        .stat-card {
            transition: all 0.2s ease;
            border-left-width: 4px;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #08324f;
            border-bottom: 2px solid #ffc107;
            padding-bottom: 4px;
            margin-bottom: 12px;
        }
        
        .value-highlight {
            font-weight: 700;
            color: #08324f;
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
            <li class="bg-[#1e4a6a] rounded-lg"><a href="accomplishment_report.php" class="flex items-center gap-3 p-3"><i class="fas fa-file-alt w-5 text-yellow-400"></i> Accomplishment Report</a></li>
            <li><a href="all_reports.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1e4a6a] transition"><i class="fas fa-folder-open w-5"></i> All Reports</a></li>
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
        
        <!-- Header with Print Button -->
        <div class="bg-white p-4 md:p-6 rounded-lg shadow-md mb-4 border-l-4 border-yellow-400 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 no-print">
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-[#08324f] flex items-center gap-2">
                    <i class="fas fa-file-alt text-yellow-500"></i>
                    Accomplishment Report
                </h2>
                <p class="text-sm text-gray-600 mt-1">All reports are automatically approved</p>
            </div>
            <button onclick="window.print()" class="bg-[#1f6fb2] text-white px-4 py-2 rounded-lg hover:bg-[#0a3d62] transition flex items-center gap-2">
                <i class="fas fa-print"></i> Print Report
            </button>
        </div>

        <!-- Date Filter -->
        <div class="bg-white p-4 rounded-lg shadow-md mb-4 no-print">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                    <input type="date" name="from_date" value="<?php echo $from_date; ?>" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2]">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                    <input type="date" name="to_date" value="<?php echo $to_date; ?>" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2]">
                </div>
                <div>
                    <button type="submit" class="bg-[#1f6fb2] text-white px-6 py-2 rounded-lg hover:bg-[#0a3d62] transition flex items-center gap-2">
                        <i class="fas fa-search"></i> Generate
                    </button>
                </div>
            </form>
        </div>

        <!-- REPORT CONTENT -->
        <div class="print-area bg-white p-4 md:p-6 rounded-lg shadow-md">
            
            <!-- REPUBLIC HEADER -->
            <div class="text-center mb-6 border-b pb-4">
                <div class="flex justify-center items-center gap-3 mb-2">
                    <img src="../image/pnplogo.png" class="w-12 h-12" alt="PNP Logo">
                    <div>
                        <h1 class="text-lg font-bold">REPUBLIC OF THE PHILIPPINES</h1>
                        <h2 class="text-base">PHILIPPINE NATIONAL POLICE</h2>
                    </div>
                </div>
                <div class="border-t-2 border-b-2 border-[#08324f] py-1">
                    <p class="font-bold">MANOLO FORTICH MUNICIPAL POLICE STATION</p>
                    <p class="text-xs">Poblacion, Manolo Fortich, Bukidnon</p>
                </div>
            </div>

            <!-- Report Title -->
            <div class="text-center mb-6">
                <h2 class="text-lg font-bold uppercase">ACCOMPLISHMENT REPORT</h2>
                <p class="text-base"><?php echo date('F d, Y', strtotime($from_date)); ?> - <?php echo date('F d, Y', strtotime($to_date)); ?></p>
            </div>

            <!-- SUMMARY CARDS -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                <div class="stat-card bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
                    <p class="text-xs text-gray-600">Total Operations</p>
                    <p class="text-2xl font-bold text-[#08324f]"><?php echo $stats['total_operations']; ?></p>
                </div>
                <div class="stat-card bg-green-50 p-4 rounded-lg border-l-4 border-green-500">
                    <p class="text-xs text-gray-600">Personnel</p>
                    <p class="text-2xl font-bold text-[#08324f]"><?php echo $stats['total_personnel']; ?></p>
                </div>
                <div class="stat-card bg-red-50 p-4 rounded-lg border-l-4 border-red-500">
                    <p class="text-xs text-gray-600">Arrests</p>
                    <p class="text-2xl font-bold text-[#08324f]"><?php echo $stats['total_arrests']; ?></p>
                </div>
                <div class="stat-card bg-yellow-50 p-4 rounded-lg border-l-4 border-yellow-500">
                    <p class="text-xs text-gray-600">Violations</p>
                    <p class="text-2xl font-bold text-[#08324f]"><?php echo $stats['total_violations']; ?></p>
                </div>
            </div>

            <!-- DISPOSITION SUMMARY (NEW) -->
            <div class="mb-6">
                <h3 class="section-title">⚖️ DISPOSITION SUMMARY</h3>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                    <div class="bg-gray-50 p-3 rounded-lg text-center">
                        <p class="text-xs text-gray-500">Fixed</p>
                        <p class="text-2xl font-bold text-[#08324f]"><?php echo $disposition['total_fixed'] ?? 0; ?></p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg text-center">
                        <p class="text-xs text-gray-500">Fined</p>
                        <p class="text-2xl font-bold text-[#08324f]"><?php echo $disposition['total_fined'] ?? 0; ?></p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg text-center">
                        <p class="text-xs text-gray-500">Warned</p>
                        <p class="text-2xl font-bold text-[#08324f]"><?php echo $disposition['total_warned'] ?? 0; ?></p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg text-center">
                        <p class="text-xs text-gray-500">Charged</p>
                        <p class="text-2xl font-bold text-[#08324f]"><?php echo $disposition['total_charged'] ?? 0; ?></p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg text-center">
                        <p class="text-xs text-gray-500">Community</p>
                        <p class="text-2xl font-bold text-[#08324f]"><?php echo $disposition['total_community'] ?? 0; ?></p>
                    </div>
                </div>
            </div>

            <!-- PATROL OPERATIONS -->
            <div class="mb-6">
                <h3 class="section-title">🚶 PATROL OPERATIONS</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
                    <!-- Foot Patrol -->
                    <div class="border rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-walking text-blue-600 text-xl"></i>
                            <h4 class="font-semibold">Foot Patrol</h4>
                        </div>
                        <p class="text-2xl font-bold text-[#08324f]"><?php echo $patrol_details['Foot Patrol']['count'] ?? 0; ?></p>
                        <p class="text-sm text-gray-600">Personnel: <?php echo $patrol_details['Foot Patrol']['personnel'] ?? 0; ?></p>
                    </div>
                    
                    <!-- Mobile Patrol -->
                    <div class="border rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-car text-blue-600 text-xl"></i>
                            <h4 class="font-semibold">Mobile Patrol</h4>
                        </div>
                        <p class="text-2xl font-bold text-[#08324f]"><?php echo $patrol_details['Mobile Patrol']['count'] ?? 0; ?></p>
                        <p class="text-sm text-gray-600">Personnel: <?php echo $patrol_details['Mobile Patrol']['personnel'] ?? 0; ?></p>
                    </div>
                    
                    <!-- Motor Patrol -->
                    <div class="border rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-motorcycle text-blue-600 text-xl"></i>
                            <h4 class="font-semibold">Motor Patrol</h4>
                        </div>
                        <p class="text-2xl font-bold text-[#08324f]"><?php echo $patrol_details['Motorcycle Patrol']['count'] ?? 0; ?></p>
                        <p class="text-sm text-gray-600">Personnel: <?php echo $patrol_details['Motorcycle Patrol']['personnel'] ?? 0; ?></p>
                    </div>
                </div>
            </div>

            <!-- CHECKPOINT OPERATIONS -->
            <div class="mb-6">
                <h3 class="section-title">🚧 CHECKPOINT OPERATIONS</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-3">
                    <div class="border rounded-lg p-3">
                        <p class="text-xs text-gray-500">Total Checkpoints</p>
                        <p class="text-2xl font-bold text-[#08324f]"><?php echo $checkpoint_details['total_checkpoints'] ?? 0; ?></p>
                    </div>
                    <div class="border rounded-lg p-3">
                        <p class="text-xs text-gray-500">Border Ops</p>
                        <p class="text-xl font-bold text-[#08324f]"><?php echo $checkpoint_details['border_ops'] ?? 0; ?></p>
                        <p class="text-xs">Personnel: <?php echo $checkpoint_details['border_personnel'] ?? 0; ?></p>
                    </div>
                    <div class="border rounded-lg p-3">
                        <p class="text-xs text-gray-500">Mobile Ops</p>
                        <p class="text-xl font-bold text-[#08324f]"><?php echo $checkpoint_details['mobile_ops'] ?? 0; ?></p>
                        <p class="text-xs">Personnel: <?php echo $checkpoint_details['mobile_personnel'] ?? 0; ?></p>
                    </div>
                    <div class="border rounded-lg p-3">
                        <p class="text-xs text-gray-500">TCT/OVR</p>
                        <p class="text-xl font-bold text-[#08324f]"><?php echo $checkpoint_details['tct_ovr'] ?? 0; ?></p>
                    </div>
                </div>
                <div class="bg-red-50 p-3 rounded-lg flex justify-between">
                    <span class="font-semibold">Arrests Made:</span>
                    <span class="font-bold text-lg text-red-600"><?php echo $checkpoint_details['arrests'] ?? 0; ?></span>
                </div>
            </div>

            <!-- OPLAN OPERATIONS -->
            <div class="mb-6">
                <h3 class="section-title">🛡️ OPLAN OPERATIONS</h3>
                
                <!-- Oplan Bakal -->
                <div class="mb-4">
                    <h4 class="font-semibold text-[#08324f] bg-gray-100 p-2 rounded mb-2">OPLAN BAKAL</h4>
                    <?php if (isset($oplan_details['Oplan Bakal'])): ?>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="border rounded-lg p-3">
                            <p class="text-xs text-gray-500">Operations</p>
                            <p class="text-xl font-bold text-[#08324f]"><?php echo $oplan_details['Oplan Bakal']['count']; ?></p>
                        </div>
                        <div class="border rounded-lg p-3">
                            <p class="text-xs text-gray-500">Firearms</p>
                            <p class="text-xl font-bold text-red-600"><?php echo $oplan_details['Oplan Bakal']['firearms']; ?></p>
                        </div>
                        <div class="border rounded-lg p-3">
                            <p class="text-xs text-gray-500">Arrests</p>
                            <p class="text-xl font-bold text-[#08324f]"><?php echo $oplan_details['Oplan Bakal']['arrests']; ?></p>
                        </div>
                        <div class="border rounded-lg p-3">
                            <p class="text-xs text-gray-500">Personnel</p>
                            <p class="text-xl font-bold text-[#08324f]"><?php echo $oplan_details['Oplan Bakal']['personnel']; ?></p>
                        </div>
                    </div>
                    <?php else: ?>
                    <p class="text-gray-500 text-sm">No Oplan Bakal operations</p>
                    <?php endif; ?>
                </div>
                
                <!-- Oplan Sita -->
                <div>
                    <h4 class="font-semibold text-[#08324f] bg-gray-100 p-2 rounded mb-2">OPLAN SITA</h4>
                    <?php if (isset($oplan_details['Oplan Sita'])): ?>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="border rounded-lg p-3">
                            <p class="text-xs text-gray-500">Operations</p>
                            <p class="text-xl font-bold text-[#08324f]"><?php echo $oplan_details['Oplan Sita']['count']; ?></p>
                        </div>
                        <div class="border rounded-lg p-3">
                            <p class="text-xs text-gray-500">Contraband</p>
                            <p class="text-xl font-bold text-orange-600"><?php echo number_format($oplan_details['Oplan Sita']['contraband'], 2); ?> kg</p>
                        </div>
                        <div class="border rounded-lg p-3">
                            <p class="text-xs text-gray-500">Kontra Boga</p>
                            <p class="text-xl font-bold text-[#08324f]"><?php echo $oplan_details['Oplan Sita']['kontra_boga']; ?></p>
                        </div>
                        <div class="border rounded-lg p-3">
                            <p class="text-xs text-gray-500">Anti-Vaping</p>
                            <p class="text-xl font-bold text-[#08324f]"><?php echo $oplan_details['Oplan Sita']['anti_vaping']; ?></p>
                        </div>
                        <div class="border rounded-lg p-3">
                            <p class="text-xs text-gray-500">House Visits</p>
                            <p class="text-xl font-bold text-[#08324f]"><?php echo $oplan_details['Oplan Sita']['house_visits']; ?></p>
                        </div>
                        <div class="border rounded-lg p-3">
                            <p class="text-xs text-gray-500">Arrests</p>
                            <p class="text-xl font-bold text-[#08324f]"><?php echo $oplan_details['Oplan Sita']['arrests']; ?></p>
                        </div>
                        <div class="border rounded-lg p-3">
                            <p class="text-xs text-gray-500">Personnel</p>
                            <p class="text-xl font-bold text-[#08324f]"><?php echo $oplan_details['Oplan Sita']['personnel']; ?></p>
                        </div>
                    </div>
                    <?php else: ?>
                    <p class="text-gray-500 text-sm">No Oplan Sita operations</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- VIOLATIONS -->
            <div class="mb-6">
                <h3 class="section-title">⚠️ ORDINANCE VIOLATIONS</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <div class="border rounded-lg p-3">
                        <p class="text-xs text-gray-500">Drinking in Public</p>
                        <p class="text-xl font-bold text-[#08324f]"><?php echo $violations['drinking'] ?? 0; ?></p>
                    </div>
                    <div class="border rounded-lg p-3">
                        <p class="text-xs text-gray-500">Smoking Ban</p>
                        <p class="text-xl font-bold text-[#08324f]"><?php echo $violations['smoking'] ?? 0; ?></p>
                    </div>
                    <div class="border rounded-lg p-3">
                        <p class="text-xs text-gray-500">Half-Naked</p>
                        <p class="text-xl font-bold text-[#08324f]"><?php echo $violations['halfnaked'] ?? 0; ?></p>
                    </div>
                    <div class="border rounded-lg p-3">
                        <p class="text-xs text-gray-500">Curfew</p>
                        <p class="text-xl font-bold text-[#08324f]"><?php echo $violations['curfew'] ?? 0; ?></p>
                    </div>
                    <div class="border rounded-lg p-3">
                        <p class="text-xs text-gray-500">Vandalism</p>
                        <p class="text-xl font-bold text-[#08324f]"><?php echo $violations['vandalism'] ?? 0; ?></p>
                    </div>
                    <div class="border rounded-lg p-3">
                        <p class="text-xs text-gray-500">Other</p>
                        <p class="text-xl font-bold text-[#08324f]"><?php echo $violations['other'] ?? 0; ?></p>
                    </div>
                </div>
            </div>

            <!-- NARRATIVE REPORT -->
            <div class="mb-6">
                <h3 class="section-title">📝 NARRATIVE REPORT</h3>
                <div class="bg-gray-50 p-4 rounded-lg text-sm leading-relaxed">
                    <p class="mb-2">
                        <span class="font-semibold">Period Covered:</span> <?php echo date('F d, Y', strtotime($from_date)); ?> to <?php echo date('F d, Y', strtotime($to_date)); ?>
                    </p>
                    <p class="mb-2">
                        <span class="font-semibold">Operations Conducted:</span> A total of <strong><?php echo $stats['total_operations']; ?> operations</strong> were conducted, comprising 
                        <strong><?php echo $stats['patrols']; ?> patrols</strong>, 
                        <strong><?php echo $stats['checkpoints']; ?> checkpoints</strong>, and 
                        <strong><?php echo $stats['oplans']; ?> oplan operations</strong>.
                    </p>
                    <p class="mb-2">
                        <span class="font-semibold">Personnel Deployed:</span> A total of <strong><?php echo $stats['total_personnel']; ?> personnel</strong> were deployed.
                    </p>
                    <p class="mb-2">
                        <span class="font-semibold">Accomplishments:</span> The operations resulted in <strong><?php echo $stats['total_arrests']; ?> arrests</strong>, 
                        <strong><?php echo $stats['firearms']; ?> firearms seized</strong>, 
                        <strong><?php echo number_format($stats['contraband'], 2); ?> kg contraband</strong>, and 
                        <strong><?php echo $stats['tct_ovr']; ?> TCT/OVR accomplishments</strong>.
                    </p>
                    <p class="mb-2">
                        <span class="font-semibold">Disposition:</span> Cases resulted in 
                        <strong><?php echo $disposition['total_fixed'] ?? 0; ?> fixed</strong>, 
                        <strong><?php echo $disposition['total_fined'] ?? 0; ?> fined</strong>, 
                        <strong><?php echo $disposition['total_warned'] ?? 0; ?> warned</strong>, 
                        <strong><?php echo $disposition['total_charged'] ?? 0; ?> charged</strong>, and 
                        <strong><?php echo $disposition['total_community'] ?? 0; ?> community service</strong>.
                    </p>
                    <p>
                        <span class="font-semibold">Violations:</span> A total of <strong><?php echo $stats['total_violations']; ?> ordinance violations</strong> were recorded.
                    </p>
                </div>
            </div>

            <!-- SIGNATORIES -->
            <div class="grid grid-cols-2 gap-8 mt-8">
                <div class="text-center">
                    <p class="font-bold">Prepared by:</p>
                    <div class="mt-8">
                        <p class="font-semibold"><?php echo $_SESSION['full_name'] ?? 'Admin Officer'; ?></p>
                        <p class="text-sm">Admin Officer</p>
                    </div>
                    <div class="border-t border-black w-40 mx-auto mt-2"></div>
                    <p class="text-xs mt-1">Signature Over Printed Name</p>
                </div>
                <div class="text-center">
                    <p class="font-bold">Noted by:</p>
                    <div class="mt-8">
                        <p class="font-semibold">PLTCOL. ROGIE C ORTENTIO</p>
                        <p class="text-sm">Chief of Police</p>
                    </div>
                    <div class="border-t border-black w-40 mx-auto mt-2"></div>
                    <p class="text-xs mt-1">Signature Over Printed Name</p>
                </div>
            </div>

            <!-- Date -->
            <div class="text-right mt-4 text-sm">
                <p>Date: <?php echo date('F d, Y'); ?></p>
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