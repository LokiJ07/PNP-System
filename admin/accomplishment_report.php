<?php
// =====================================================
// FILE: admin/accomplishment_report.php
// PURPOSE: Generate and display accomplishment reports (APPROVED only)
// =====================================================

session_start();
require_once '../config/db_connect.php';
requireAdmin();

// Get filter parameters
$from_date = $_GET['from_date'] ?? date('Y-m-01'); // First day of current month
$to_date = $_GET['to_date'] ?? date('Y-m-d'); // Today
$officer_id = isset($_GET['officer_id']) ? (int)$_GET['officer_id'] : 0;

// Get all officers for dropdown
$officers = $conn->query("
    SELECT user_id, rank, first_name, last_name 
    FROM users 
    WHERE role = 'user' 
    ORDER BY last_name, first_name
");

// Build date condition - ONLY APPROVED REPORTS
$date_condition = " AND DATE(submitted_at) BETWEEN '$from_date' AND '$to_date' AND status = 'approved'";
$officer_condition = $officer_id ? " AND user_id = $officer_id" : "";

// Get summary statistics (APPROVED only)
$stats = [];

// Total reports (APPROVED only)
$result = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM patrol_activities WHERE 1=1 $date_condition $officer_condition) as patrols,
        (SELECT COUNT(*) FROM checkpoint_activities WHERE 1=1 $date_condition $officer_condition) as checkpoints,
        (SELECT COUNT(*) FROM oplan_activities WHERE 1=1 $date_condition $officer_condition) as oplans
");
$stats = $result->fetch_assoc();
$stats['total'] = $stats['patrols'] + $stats['checkpoints'] + $stats['oplans'];

// Personnel statistics (APPROVED only)
$result = $conn->query("
    SELECT 
        (SELECT COALESCE(SUM(personnel_count), 0) FROM patrol_activities WHERE 1=1 $date_condition $officer_condition) as patrol_personnel,
        (SELECT COALESCE(SUM(border_personnel + mobile_personnel), 0) FROM checkpoint_activities WHERE 1=1 $date_condition $officer_condition) as checkpoint_personnel,
        (SELECT COALESCE(SUM(personnel_count), 0) FROM oplan_activities WHERE 1=1 $date_condition $officer_condition) as oplan_personnel
");
$personnel = $result->fetch_assoc();
$stats['total_personnel'] = $personnel['patrol_personnel'] + $personnel['checkpoint_personnel'] + $personnel['oplan_personnel'];

// Accomplishment totals (APPROVED only)
$result = $conn->query("
    SELECT 
        (SELECT COALESCE(SUM(arrested_accomplishment), 0) FROM checkpoint_activities WHERE 1=1 $date_condition $officer_condition) as checkpoint_arrests,
        (SELECT COALESCE(SUM(arrests_made), 0) FROM oplan_activities WHERE 1=1 $date_condition $officer_condition) as oplan_arrests,
        (SELECT COALESCE(SUM(firearms_seized), 0) FROM oplan_activities WHERE 1=1 $date_condition $officer_condition) as firearms,
        (SELECT COALESCE(SUM(contraband_kg), 0) FROM oplan_activities WHERE 1=1 $date_condition $officer_condition) as contraband,
        (SELECT COALESCE(SUM(tct_ovr_accomplishment), 0) FROM checkpoint_activities WHERE 1=1 $date_condition $officer_condition) as tct_ovr
");
$accomplishments = $result->fetch_assoc();
$stats['total_arrests'] = $accomplishments['checkpoint_arrests'] + $accomplishments['oplan_arrests'];
$stats['firearms'] = $accomplishments['firearms'];
$stats['contraband'] = $accomplishments['contraband'];
$stats['tct_ovr'] = $accomplishments['tct_ovr'];

// Get patrol breakdown by type (APPROVED only)
$patrol_breakdown = $conn->query("
    SELECT 
        patrol_type,
        COUNT(*) as count,
        COALESCE(SUM(personnel_count), 0) as personnel
    FROM patrol_activities 
    WHERE 1=1 $date_condition $officer_condition
    GROUP BY patrol_type
");

// Get officer performance (APPROVED only)
$officer_performance = null;
if (!$officer_id) {
    $officer_performance = $conn->query("
        SELECT 
            u.user_id,
            u.rank,
            u.first_name,
            u.last_name,
            (SELECT COUNT(*) FROM patrol_activities WHERE user_id = u.user_id AND DATE(submitted_at) BETWEEN '$from_date' AND '$to_date' AND status = 'approved') as patrols,
            (SELECT COUNT(*) FROM checkpoint_activities WHERE user_id = u.user_id AND DATE(submitted_at) BETWEEN '$from_date' AND '$to_date' AND status = 'approved') as checkpoints,
            (SELECT COUNT(*) FROM oplan_activities WHERE user_id = u.user_id AND DATE(submitted_at) BETWEEN '$from_date' AND '$to_date' AND status = 'approved') as oplans,
            ((SELECT COUNT(*) FROM patrol_activities WHERE user_id = u.user_id AND DATE(submitted_at) BETWEEN '$from_date' AND '$to_date' AND status = 'approved') +
             (SELECT COUNT(*) FROM checkpoint_activities WHERE user_id = u.user_id AND DATE(submitted_at) BETWEEN '$from_date' AND '$to_date' AND status = 'approved') +
             (SELECT COUNT(*) FROM oplan_activities WHERE user_id = u.user_id AND DATE(submitted_at) BETWEEN '$from_date' AND '$to_date' AND status = 'approved')) as total
        FROM users u
        WHERE u.role = 'user'
        HAVING total > 0
        ORDER BY total DESC
        LIMIT 10
    ");
}

// Get selected officer name
$selected_officer_name = 'All Officers';
if ($officer_id) {
    $stmt = $conn->prepare("SELECT rank, first_name, last_name FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $officer_id);
    $stmt->execute();
    $officer = $stmt->get_result()->fetch_assoc();
    if ($officer) {
        $selected_officer_name = $officer['rank'] . ' ' . $officer['first_name'] . ' ' . $officer['last_name'];
    }
    $stmt->close();
}
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
        .dropdown-content { display: none; }
        .dropdown.active .dropdown-content { display: block; }
        .rotate-180 { transform: rotate(180deg); }
        
        /* PRINT STYLES - Optimized for printing from Republic */
        @media print {
            /* Hide everything except the report content */
            .no-print, 
            .sidebar, 
            .flex-1 > .bg-white:first-of-type,
            .flex-1 > .bg-white:nth-of-type(2),
            button,
            .dropdown,
            .no-print * {
                display: none !important;
            }
            
            /* Reset body and html for printing */
            html, body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
                height: auto !important;
                overflow: visible !important;
                display: block !important;
            }
            
            /* Main container adjustments */
            .flex, .bg-\[#0a3d62\] {
                display: block !important;
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            
            /* Show only the print area */
            .print-area {
                display: block !important;
                background: white !important;
                padding: 20px !important;
                margin: 0 auto !important;
                max-width: 100% !important;
                box-shadow: none !important;
            }
            
            /* Ensure the Republic header is at the very top */
            .print-area .text-center:first-of-type {
                margin-top: 0 !important;
                padding-top: 0 !important;
            }
            
            /* Table print optimization */
            table {
                page-break-inside: avoid;
            }
            
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            
            thead {
                display: table-header-group;
            }
            
            tfoot {
                display: table-footer-group;
            }
            
            /* Page break controls */
            .page-break-before {
                page-break-before: always;
            }
            
            .page-break-after {
                page-break-after: always;
            }
            
            /* Ensure text is black for printing */
            .text-\[#08324f\] {
                color: black !important;
            }
            
            /* Border colors for printing */
            .border-\[\#08324f\] {
                border-color: black !important;
            }
            
            /* Background colors become light gray for printing */
            .bg-blue-50, .bg-green-50, .bg-red-50, .bg-yellow-50 {
                background-color: #f5f5f5 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        
        /* Screen styles remain the same */
        .signature-line {
            border-top: 1px solid #000;
            width: 200px;
            margin: 5px auto 0;
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

        <!-- Admin Info -->
        <div class="bg-[#1e4a6a] p-3 rounded-lg mb-4 text-center no-print">
            <p class="text-sm text-yellow-400 font-medium"><?php echo $_SESSION['full_name'] ?? 'Admin'; ?></p>
            <p class="text-xs text-gray-300 mt-1"><?php echo $_SESSION['email'] ?? 'admin@pnp.gov.ph'; ?></p>
        </div>

        <ul class="space-y-1 no-print">
            <li class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer">
                <a href="admin_dashboard.php" class="text-white no-underline block">
                    <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                </a>
            </li>
            <li class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer">
                <a href="checkpoint.php" class="text-white no-underline block">
                    <i class="fas fa-map-marker-alt mr-3"></i> Checkpoint
                </a>
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
                <a href="admin_users.php" class="text-white no-underline block">
                    <i class="fas fa-users mr-3"></i> Users
                </a>
            </li>
            <li class="p-3 rounded bg-[#0a3d62] border-l-4 border-yellow-400">
                <a href="accomplishment_report.php" class="text-white no-underline block">
                    <i class="fas fa-file-alt mr-3"></i> Accomplishment Report
                </a>
            </li>
            <li class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer mt-5 pt-4 border-t border-[#1a4b6d]">
                <a href="../logout.php" class="text-white no-underline block">
                    <i class="fas fa-sign-out-alt mr-3"></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-8 bg-[#eef2f6] overflow-y-auto h-screen">
        
        <!-- Header (Hidden when printing) -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6 border-l-4 border-yellow-400 flex justify-between items-center no-print">
            <div>
                <h2 class="text-2xl font-bold text-[#08324f]">Accomplishment Report</h2>
                <p class="text-gray-600 mt-1">Generate and view accomplishment reports</p>
            </div>
            <button onclick="window.print()" class="bg-[#1f6fb2] text-white px-6 py-2 rounded-lg hover:bg-[#0a3d62] transition flex items-center gap-2">
                <i class="fas fa-print"></i> Print Report
            </button>
        </div>

        <!-- Filter Form (Hidden when printing) -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6 no-print">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                    <input type="date" name="from_date" value="<?php echo $from_date; ?>" 
                           class="w-full p-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                    <input type="date" name="to_date" value="<?php echo $to_date; ?>" 
                           class="w-full p-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Officer</label>
                    <select name="officer_id" class="w-full p-2 border border-gray-300 rounded-lg">
                        <option value="0">All Officers</option>
                        <?php while ($officer = $officers->fetch_assoc()): ?>
                        <option value="<?php echo $officer['user_id']; ?>" <?php echo $officer_id == $officer['user_id'] ? 'selected' : ''; ?>>
                            <?php echo $officer['rank'] . ' ' . $officer['first_name'] . ' ' . $officer['last_name']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-[#1f6fb2] text-white px-4 py-2 rounded-lg hover:bg-[#0a3d62] transition">
                        <i class="fas fa-search mr-2"></i> Generate Report
                    </button>
                </div>
            </form>
        </div>

        <!-- Report Content - This is what will print, starting with Republic header -->
        <div class="print-area bg-white p-8 rounded-lg shadow-md">
            <!-- REPUBLIC HEADER - This will be at the very top when printing -->
            <div class="text-center mb-8 border-b pb-4">
                <div class="flex justify-center items-center gap-4 mb-2">
                    <img src="../image/pnplogo.png" class="w-16 h-16" alt="PNP Logo">
                    <div>
                        <h1 class="text-2xl font-bold text-[#08324f]">REPUBLIC OF THE PHILIPPINES</h1>
                        <h2 class="text-xl">NATIONAL POLICE COMMISSION</h2>
                        <h3 class="text-lg font-semibold">PHILIPPINE NATIONAL POLICE</h3>
                    </div>
                </div>
                <div class="border-t-2 border-b-2 border-[#08324f] py-2 mt-2">
                    <p class="font-bold">MANOLO FORTICH MUNICIPAL POLICE STATION</p>
                    <p class="text-sm">Poblacion, Manolo Fortich, Bukidnon</p>
                </div>
            </div>

            <!-- Report Title -->
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold uppercase underline">CONSOLIDATED ACCOMPLISHMENT REPORT</h2>
                <p class="text-lg mt-2">
                    For the Period: <?php echo date('F d, Y', strtotime($from_date)); ?> to <?php echo date('F d, Y', strtotime($to_date)); ?>
                </p>
                <?php if ($officer_id): ?>
                <p class="text-md mt-1">Officer: <?php echo $selected_officer_name; ?></p>
                <?php endif; ?>
            </div>

            <!-- Executive Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
                    <p class="text-sm text-gray-600">Total Reports</p>
                    <p class="text-3xl font-bold text-[#08324f]"><?php echo $stats['total']; ?></p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500">
                    <p class="text-sm text-gray-600">Personnel Deployed</p>
                    <p class="text-3xl font-bold text-[#08324f]"><?php echo $stats['total_personnel']; ?></p>
                </div>
                <div class="bg-red-50 p-4 rounded-lg border-l-4 border-red-500">
                    <p class="text-sm text-gray-600">Total Arrests</p>
                    <p class="text-3xl font-bold text-[#08324f]"><?php echo $stats['total_arrests']; ?></p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg border-l-4 border-yellow-500">
                    <p class="text-sm text-gray-600">TCT/OVR Accomplishments</p>
                    <p class="text-3xl font-bold text-[#08324f]"><?php echo $stats['tct_ovr']; ?></p>
                </div>
            </div>

            <!-- Activity Breakdown -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-[#08324f] mb-4">I. ACTIVITY BREAKDOWN</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Patrols -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-semibold text-blue-600 mb-3">Patrol Operations</h4>
                        <p class="text-3xl font-bold text-[#08324f] mb-2"><?php echo $stats['patrols']; ?></p>
                        <div class="space-y-2">
                            <?php 
                            $patrol_breakdown->data_seek(0);
                            while ($p = $patrol_breakdown->fetch_assoc()): 
                            ?>
                            <div class="flex justify-between text-sm">
                                <span><?php echo $p['patrol_type']; ?>:</span>
                                <span class="font-semibold"><?php echo $p['count']; ?> (<?php echo $p['personnel']; ?> personnel)</span>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <!-- Checkpoints -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-semibold text-red-600 mb-3">Checkpoint Operations</h4>
                        <p class="text-3xl font-bold text-[#08324f] mb-2"><?php echo $stats['checkpoints']; ?></p>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span>TCT/OVR:</span>
                                <span class="font-semibold"><?php echo $stats['tct_ovr']; ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Arrests:</span>
                                <span class="font-semibold"><?php echo $accomplishments['checkpoint_arrests']; ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Oplan -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-semibold text-green-600 mb-3">Oplan Operations</h4>
                        <p class="text-3xl font-bold text-[#08324f] mb-2"><?php echo $stats['oplans']; ?></p>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span>Firearms Seized:</span>
                                <span class="font-semibold"><?php echo $stats['firearms']; ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Contraband (kg):</span>
                                <span class="font-semibold"><?php echo number_format($stats['contraband'], 2); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Arrests:</span>
                                <span class="font-semibold"><?php echo $accomplishments['oplan_arrests']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Officer Performance (if no specific officer selected) -->
            <?php if (!$officer_id && $officer_performance && $officer_performance->num_rows > 0): ?>
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-[#08324f] mb-4">II. OFFICER PERFORMANCE</h3>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-[#08324f] text-white">
                                <th class="p-2 text-left">Rank</th>
                                <th class="p-2 text-left">Name</th>
                                <th class="p-2 text-center">Patrols</th>
                                <th class="p-2 text-center">Checkpoints</th>
                                <th class="p-2 text-center">Oplans</th>
                                <th class="p-2 text-center">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($o = $officer_performance->fetch_assoc()): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-2"><?php echo $o['rank']; ?></td>
                                <td class="p-2"><?php echo $o['first_name'] . ' ' . $o['last_name']; ?></td>
                                <td class="p-2 text-center"><?php echo $o['patrols']; ?></td>
                                <td class="p-2 text-center"><?php echo $o['checkpoints']; ?></td>
                                <td class="p-2 text-center"><?php echo $o['oplans']; ?></td>
                                <td class="p-2 text-center font-bold"><?php echo $o['total']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Narrative Report -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-[#08324f] mb-4">III. NARRATIVE REPORT</h3>
                <div class="border p-4 rounded-lg bg-gray-50">
                    <p class="mb-2">
                        During the reporting period from <strong><?php echo date('F d, Y', strtotime($from_date)); ?></strong> to 
                        <strong><?php echo date('F d, Y', strtotime($to_date)); ?></strong>, a total of 
                        <strong><?php echo $stats['total']; ?> operations</strong> were conducted involving 
                        <strong><?php echo $stats['total_personnel']; ?> personnel</strong>.
                    </p>
                    <p class="mb-2">
                        Patrol operations totaled <strong><?php echo $stats['patrols']; ?></strong> including 
                        <?php 
                        $patrol_breakdown->data_seek(0);
                        $patrol_details = [];
                        while ($p = $patrol_breakdown->fetch_assoc()) {
                            $patrol_details[] = $p['count'] . ' ' . strtolower($p['patrol_type']);
                        }
                        echo implode(', ', $patrol_details); 
                        ?>.
                    </p>
                    <p class="mb-2">
                        Checkpoint operations recorded <strong><?php echo $accomplishments['tct_ovr']; ?> TCT/OVR accomplishments</strong> 
                        and <strong><?php echo $accomplishments['checkpoint_arrests']; ?> arrests</strong>.
                    </p>
                    <p>
                        Oplan operations resulted in <strong><?php echo $stats['firearms']; ?> firearms seized</strong>, 
                        <strong><?php echo number_format($stats['contraband'], 2); ?> kg of contraband</strong>, and 
                        <strong><?php echo $accomplishments['oplan_arrests']; ?> arrests</strong>.
                    </p>
                </div>
            </div>

            <!-- Signatories -->
            <div class="grid grid-cols-2 gap-8 mt-10">
                <div class="text-center">
                    <p class="font-bold">Prepared by:</p>
                    <div class="mt-8">
                        <p class="font-semibold"><?php echo $_SESSION['full_name'] ?? 'Admin Officer'; ?></p>
                        <p class="text-sm">Admin Officer</p>
                    </div>
                    <div class="signature-line"></div>
                    <p class="text-xs mt-1">Signature Over Printed Name</p>
                </div>
                <div class="text-center">
                    <p class="font-bold">Noted by:</p>
                    <div class="mt-8">
                        <p class="font-semibold">PMAJ. MARIA SANTOS</p>
                        <p class="text-sm">Chief of Police</p>
                        <p class="text-xs">Manolo Fortich MPS</p>
                    </div>
                    <div class="signature-line"></div>
                    <p class="text-xs mt-1">Signature Over Printed Name</p>
                </div>
            </div>

            <!-- Date -->
            <div class="text-right mt-4">
                <p class="text-sm"><span class="font-semibold">Date:</span> <?php echo date('F d, Y'); ?></p>
            </div>
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
    </script>
</body>
</html>
<?php $conn->close(); ?>