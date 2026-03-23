<?php
// =====================================================
// FILE: admin/view_report.php
// PURPOSE: Display complete activity report details (VIEW ONLY - Auto Approved)
// IMPROVED: Clean UI, removed status buttons, mobile responsive
// FIXED: Date fields now use proper database column names
// =====================================================

session_start();
require_once '../config/db_connect.php';

// Function to check if user is admin
function requireAdmin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: ../index.php');
        exit();
    }
}

requireAdmin();

$type = $_GET['type'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$type || !$id) {
    $_SESSION['error'] = 'Invalid report';
    header('Location: admin_dashboard.php');
    exit();
}

$report = null;
$user = null;
$photos = [];

// Get report based on type
switch ($type) {
    case 'patrol':
    case 'footpatrol':
    case 'mobilepatrol':
    case 'motorpatrol':
        $query = "
            SELECT p.*, 
                   CONCAT(u.rank, ' ', u.first_name, ' ', u.last_name) as officer_name,
                   u.badge_number, u.email, u.contact_number,
                   b.barangay_name, b.latitude as barangay_lat, b.longitude as barangay_lng,
                   'patrol' as activity_type, p.patrol_type as subtype,
                   p.patrol_date as activity_date, p.patrol_time as activity_time
            FROM patrol_activities p
            JOIN users u ON p.user_id = u.user_id
            JOIN barangays b ON p.barangay_id = b.barangay_id
            WHERE p.patrol_id = ?
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $display_type = 'patrol';
        break;

    case 'checkpoint':
        $query = "
            SELECT c.*, 
                   CONCAT(u.rank, ' ', u.first_name, ' ', u.last_name) as officer_name,
                   u.badge_number, u.email, u.contact_number,
                   b.barangay_name, b.latitude as barangay_lat, b.longitude as barangay_lng,
                   'checkpoint' as activity_type, 'Checkpoint' as subtype,
                   c.checkpoint_date as activity_date, c.checkpoint_time as activity_time
            FROM checkpoint_activities c
            JOIN users u ON c.user_id = u.user_id
            JOIN barangays b ON c.barangay_id = b.barangay_id
            WHERE c.checkpoint_id = ?
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $display_type = 'checkpoint';
        break;

    case 'oplan':
    case 'oplanbakal':
    case 'oplansita':
        $query = "
            SELECT o.*, 
                   CONCAT(u.rank, ' ', u.first_name, ' ', u.last_name) as officer_name,
                   u.badge_number, u.email, u.contact_number,
                   b.barangay_name, b.latitude as barangay_lat, b.longitude as barangay_lng,
                   'oplan' as activity_type, o.oplan_type as subtype,
                   o.oplan_date as activity_date, o.oplan_time as activity_time
            FROM oplan_activities o
            JOIN users u ON o.user_id = u.user_id
            JOIN barangays b ON o.barangay_id = b.barangay_id
            WHERE o.oplan_id = ?
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $display_type = 'oplan';
        break;

    default:
        $_SESSION['error'] = 'Invalid report type';
        header('Location: admin_dashboard.php');
        exit();
}

$stmt->execute();
$report = $stmt->get_result()->fetch_assoc();

if (!$report) {
    $_SESSION['error'] = 'Report not found';
    header('Location: admin_dashboard.php');
    exit();
}

// Get photos for this report
$photo_stmt = $conn->prepare("SELECT * FROM activity_photos WHERE activity_type = ? AND activity_id = ?");
$photo_stmt->bind_param("si", $display_type, $id);
$photo_stmt->execute();
$photos = $photo_stmt->get_result();

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
    <title>PNP | View Report - <?php echo ucfirst($display_type); ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Lightbox -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
    
    <style>
        /* PNP Color Scheme */
        :root {
            --pnp-navy: #003366;
            --pnp-gold: #FFD700;
            --pnp-light-navy: #1a4d8c;
            --pnp-dark-navy: #002244;
        }
        
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
            scrollbar-color: var(--pnp-gold) var(--pnp-navy);
        }
        .sidebar-scroll::-webkit-scrollbar {
            width: 6px;
        }
        .sidebar-scroll::-webkit-scrollbar-track {
            background: var(--pnp-navy);
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background-color: var(--pnp-gold);
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
        
        /* Map */
        #map {
            height: 300px;
            width: 100%;
            border-radius: 0.5rem;
            z-index: 1;
            border: 2px solid var(--pnp-gold);
        }
        
        /* Photo grid */
        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 0.75rem;
        }
        .photo-item {
            aspect-ratio: 1/1;
            object-fit: cover;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid #e5e7eb;
        }
        .photo-item:hover {
            transform: scale(1.05);
            border-color: var(--pnp-gold);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        /* Approved badge */
        .approved-badge {
            background: #10b981;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        /* View-only indicator */
        .view-only {
            background: #6b7280;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        /* Card styles */
        .info-card {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            overflow: hidden;
            transition: all 0.3s;
        }
        .info-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }
        .card-header {
            background: var(--pnp-navy);
            color: white;
            padding: 1rem 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .card-header i {
            color: var(--pnp-gold);
        }
        
        /* Status badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .status-approved {
            background: #d1fae5;
            color: #065f46;
        }
    </style>
</head>
<body class="flex flex-col md:flex-row bg-[#003366] min-h-screen">

    <!-- Mobile Menu Button -->
    <button id="mobileMenuBtn" class="md:hidden fixed top-4 left-4 z-50 bg-[#003366] text-white p-3 rounded-lg shadow-lg">
        <i class="fas fa-bars text-xl"></i>
    </button>

    <!-- Mobile Menu Overlay -->
    <div id="menuOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden" onclick="closeMobileMenu()"></div>

    <!-- Sidebar -->
    <div id="sidebar" class="w-full md:w-[260px] bg-gradient-to-b from-[#003366] to-[#002244] text-white h-screen overflow-y-auto sidebar-scroll sidebar-mobile fixed top-0 left-[-100%] md:left-0 md:sticky z-50 transition-all duration-300 ease-in-out shadow-xl">
        
        <button id="closeSidebar" class="md:hidden absolute top-4 right-4 text-white text-xl">
            <i class="fas fa-times"></i>
        </button>

        <!-- Logo and Title -->
        <div class="flex items-center gap-3 p-5 border-b border-[#FFD700] sticky top-0 bg-[#003366] z-10">
            <img src="../image/pnplogo.png" class="w-10 h-10 object-contain" alt="PNP Logo">
            <div>
                <h2 class="text-lg font-semibold leading-tight">PNP Operation</h2>
                <p class="text-xs text-[#FFD700]">Admin Panel</p>
            </div>
        </div>

        <!-- Admin Info -->
        <div class="bg-[#1a4d8c] mx-4 my-4 p-4 rounded-lg text-center shadow-lg border border-[#FFD700]">
            <div class="w-16 h-16 bg-[#FFD700] rounded-full mx-auto mb-3 flex items-center justify-center text-[#003366] text-2xl font-bold">
                <?php echo substr($admin_name, 0, 1); ?>
            </div>
            <p class="font-medium text-[#FFD700]"><?php echo $admin_name; ?></p>
            <p class="text-xs text-gray-300 mt-1 break-all"><?php echo $admin_email; ?></p>
        </div>

        <!-- Navigation Menu -->
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
            <li><a href="all_reports.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1a4d8c] transition"><i class="fas fa-folder-open w-5"></i> All Reports</a></li>
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
    <div class="flex-1 p-4 md:p-6 lg:p-8 bg-[#eef2f6] overflow-y-auto min-h-screen main-content-mobile">
        
        <!-- Display Session Messages -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-lg shadow-md">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-600 mr-2"></i>
                <span><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-lg shadow-md">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-600 mr-2"></i>
                <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Header with Back Button -->
        <div class="bg-white p-4 md:p-6 rounded-lg shadow-md mb-6 border-l-4 border-[#FFD700] flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="javascript:history.back()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <h2 class="text-xl md:text-2xl font-bold text-[#003366]">
                    <?php 
                    if ($display_type == 'patrol') echo 'Patrol Report';
                    elseif ($display_type == 'checkpoint') echo 'Checkpoint Report';
                    else echo 'Oplan Report';
                    ?>
                </h2>
            </div>
            <div class="flex items-center gap-3">
                <span class="approved-badge">
                    <i class="fas fa-check-circle"></i> APPROVED
                </span>
                <span class="view-only">
                    <i class="fas fa-lock"></i> VIEW ONLY
                </span>
            </div>
        </div>

        <!-- Report Details Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Column - Main Details (2/3 width) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Officer Information Card -->
                <div class="info-card">
                    <div class="card-header">
                        <i class="fas fa-user-shield"></i>
                        <span>Officer Information</span>
                    </div>
                    <div class="p-6">
                        <div class="flex flex-col sm:flex-row gap-6 items-start">
                            <div class="w-20 h-20 bg-gradient-to-br from-[#003366] to-[#1a4d8c] rounded-full flex items-center justify-center text-white text-3xl font-bold border-3 border-[#FFD700] shadow-lg">
                                <?php 
                                $name_parts = explode(' ', $report['officer_name']);
                                $initials = '';
                                foreach ($name_parts as $part) {
                                    if (!empty($part)) $initials .= substr($part, 0, 1);
                                }
                                echo $initials;
                                ?>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-xl font-bold text-[#003366]"><?php echo $report['officer_name']; ?></h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                                    <div>
                                        <p class="text-xs text-gray-500">Badge Number</p>
                                        <p class="font-medium"><?php echo $report['badge_number']; ?></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Email</p>
                                        <p class="font-medium"><?php echo $report['email']; ?></p>
                                    </div>
                                    <?php if (!empty($report['contact_number'])): ?>
                                    <div>
                                        <p class="text-xs text-gray-500">Contact Number</p>
                                        <p class="font-medium"><?php echo $report['contact_number']; ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Details Card -->
                <div class="info-card">
                    <div class="card-header">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Activity Details</span>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Common fields -->
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Activity Type</p>
                                <p class="text-lg font-semibold text-[#003366]"><?php echo $report['subtype']; ?></p>
                            </div>
                            
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Date & Time</p>
                                <p class="text-lg font-semibold text-[#003366]">
                                    <?php 
                                    // Using activity_date and activity_time from the query (which maps to proper database fields)
                                    if (isset($report['activity_date']) && $report['activity_date']) {
                                        echo date('F d, Y', strtotime($report['activity_date']));
                                    } else {
                                        echo 'Not specified';
                                    }
                                    ?><br>
                                    <span class="text-sm text-gray-600">
                                        <?php 
                                        if (isset($report['activity_time']) && $report['activity_time']) {
                                            echo date('h:i A', strtotime($report['activity_time']));
                                        } else {
                                            echo 'Not specified';
                                        }
                                        ?>
                                    </span>
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Barangay</p>
                                <p class="text-lg font-semibold text-[#003366]"><?php echo $report['barangay_name']; ?></p>
                            </div>
                            
                            <div class="md:col-span-2">
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Specific Location</p>
                                <p class="text-base font-medium text-[#003366] bg-gray-50 p-3 rounded-lg">
                                    <?php echo $report['specific_location']; ?>
                                </p>
                            </div>
                            
                            <!-- PATROL SPECIFIC FIELDS -->
                            <?php if ($display_type == 'patrol'): ?>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Personnel Deployed</p>
                                <p class="text-lg font-semibold text-[#003366]"><?php echo $report['personnel_count'] ?? '1'; ?></p>
                            </div>
                            
                            <?php if (!empty($report['vehicle_number'])): ?>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Vehicle/Unit Number</p>
                                <p class="text-lg font-semibold text-[#003366]"><?php echo $report['vehicle_number']; ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Violations for Patrol -->
                            <div class="md:col-span-2">
                                <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Violations Encountered</p>
                                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 bg-gray-50 p-3 rounded-lg">
                                    <div><span class="text-xs">Drinking:</span> <span class="font-bold"><?php echo $report['drinking_violations'] ?? 0; ?></span></div>
                                    <div><span class="text-xs">Smoking:</span> <span class="font-bold"><?php echo $report['smoking_violations'] ?? 0; ?></span></div>
                                    <div><span class="text-xs">Half-Naked:</span> <span class="font-bold"><?php echo $report['halfnaked_violations'] ?? 0; ?></span></div>
                                    <div><span class="text-xs">Curfew:</span> <span class="font-bold"><?php echo $report['curfew_violations'] ?? 0; ?></span></div>
                                    <div><span class="text-xs">Vandalism:</span> <span class="font-bold"><?php echo $report['vandalism_violations'] ?? 0; ?></span></div>
                                    <div><span class="text-xs">Other:</span> <span class="font-bold"><?php echo $report['other_violations'] ?? 0; ?></span></div>
                                </div>
                                <?php if (!empty($report['other_violations_desc'])): ?>
                                <p class="text-sm text-gray-600 mt-2 italic"><?php echo $report['other_violations_desc']; ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <!-- CHECKPOINT SPECIFIC FIELDS -->
                            <?php if ($display_type == 'checkpoint'): ?>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Border Control Ops</p>
                                <p class="text-lg font-semibold text-[#003366]"><?php echo $report['border_control_ops'] ?? '0'; ?></p>
                            </div>
                            
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Border Personnel</p>
                                <p class="text-lg font-semibold text-[#003366]"><?php echo $report['border_personnel'] ?? '0'; ?></p>
                            </div>
                            
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Mobile Checkpoint Ops</p>
                                <p class="text-lg font-semibold text-[#003366]"><?php echo $report['mobile_checkpoint_ops'] ?? '0'; ?></p>
                            </div>
                            
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Mobile Personnel</p>
                                <p class="text-lg font-semibold text-[#003366]"><?php echo $report['mobile_personnel'] ?? '0'; ?></p>
                            </div>
                            
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">TCT/OVR Accomplishments</p>
                                <p class="text-lg font-semibold text-[#003366]"><?php echo $report['tct_ovr_accomplishment'] ?? '0'; ?></p>
                            </div>
                            
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Arrests Made</p>
                                <p class="text-lg font-semibold text-red-600"><?php echo $report['arrested_accomplishment'] ?? '0'; ?></p>
                            </div>
                            
                            <!-- Violations for Checkpoint -->
                            <div class="md:col-span-2 mt-2">
                                <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Violations Encountered</p>
                                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 bg-gray-50 p-3 rounded-lg">
                                    <div><span class="text-xs">Drinking:</span> <span class="font-bold"><?php echo $report['drinking_violations'] ?? 0; ?></span></div>
                                    <div><span class="text-xs">Smoking:</span> <span class="font-bold"><?php echo $report['smoking_violations'] ?? 0; ?></span></div>
                                    <div><span class="text-xs">Half-Naked:</span> <span class="font-bold"><?php echo $report['halfnaked_violations'] ?? 0; ?></span></div>
                                    <div><span class="text-xs">Curfew:</span> <span class="font-bold"><?php echo $report['curfew_violations'] ?? 0; ?></span></div>
                                    <div><span class="text-xs">Vandalism:</span> <span class="font-bold"><?php echo $report['vandalism_violations'] ?? 0; ?></span></div>
                                    <div><span class="text-xs">Other:</span> <span class="font-bold"><?php echo $report['other_violations'] ?? 0; ?></span></div>
                                </div>
                            </div>
                            
                            <!-- Disposition for Checkpoint -->
                            <div class="md:col-span-2 mt-2">
                                <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Disposition</p>
                                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 bg-gray-50 p-3 rounded-lg">
                                    <div><span class="text-xs">Fixed:</span> <span class="font-bold"><?php echo $report['fixed_count'] ?? 0; ?></span></div>
                                    <div><span class="text-xs">Fined:</span> <span class="font-bold"><?php echo $report['fined_count'] ?? 0; ?></span></div>
                                    <div><span class="text-xs">Warned:</span> <span class="font-bold"><?php echo $report['warned_count'] ?? 0; ?></span></div>
                                    <div><span class="text-xs">Charged:</span> <span class="font-bold"><?php echo $report['charged_count'] ?? 0; ?></span></div>
                                    <div><span class="text-xs">Community:</span> <span class="font-bold"><?php echo $report['community_service'] ?? 0; ?></span></div>
                                </div>
                                <?php if (!empty($report['disposition_others'])): ?>
                                <p class="text-sm text-gray-600 mt-2 italic">Others: <?php echo $report['disposition_others']; ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <!-- OPLAN SPECIFIC FIELDS -->
                            <?php if ($display_type == 'oplan'): ?>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Operations Count</p>
                                <p class="text-lg font-semibold text-[#003366]"><?php echo $report['operations_count'] ?? '1'; ?></p>
                            </div>
                            
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Personnel Deployed</p>
                                <p class="text-lg font-semibold text-[#003366]"><?php echo $report['personnel_count'] ?? '1'; ?></p>
                            </div>
                            
                            <?php if ($report['oplan_type'] == 'Oplan Bakal'): ?>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Firearms Seized</p>
                                <p class="text-lg font-semibold text-[#003366]"><?php echo $report['firearms_seized'] ?? '0'; ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Firearms (CRS)</p>
                                <p class="text-lg font-semibold text-[#003366]"><?php echo $report['firearms_crs'] ?? '0'; ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">FAS Deposits</p>
                                <p class="text-lg font-semibold text-[#003366]"><?php echo $report['fas_deposit'] ?? '0'; ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Renewed FAS</p>
                                <p class="text-lg font-semibold text-[#003366]"><?php echo $report['renewed_fas'] ?? '0'; ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($report['oplan_type'] == 'Oplan Sita'): ?>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Contraband Type</p>
                                <p class="text-lg font-semibold text-[#003366]">
                                    <?php 
                                    if (!empty($report['contraband_type'])) {
                                        if ($report['contraband_type'] == 'Other' && !empty($report['contraband_other'])) {
                                            echo $report['contraband_other'];
                                        } else {
                                            echo $report['contraband_type'];
                                        }
                                    } else {
                                        echo 'None';
                                    }
                                    ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Contraband Quantity</p>
                                <p class="text-lg font-semibold text-[#003366]">
                                    <?php 
                                    if (!empty($report['contraband_quantity']) && $report['contraband_quantity'] > 0) {
                                        echo $report['contraband_quantity'] . ' ' . ($report['contraband_unit'] ?? 'units');
                                    } else {
                                        echo '0';
                                    }
                                    ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Contraband Value</p>
                                <p class="text-lg font-semibold text-[#003366]">
                                    <?php 
                                    if (!empty($report['contraband_value']) && $report['contraband_value'] > 0) {
                                        echo '₱' . number_format($report['contraband_value'], 2);
                                    } else {
                                        echo '₱0.00';
                                    }
                                    ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Kontra Boga</p>
                                <p class="text-lg font-semibold text-[#003366]"><?php echo $report['kontra_boga'] ?? '0'; ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Anti-Vaping OPNs</p>
                                <p class="text-lg font-semibold text-[#003366]"><?php echo $report['anti_vaping'] ?? '0'; ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Arrests Made</p>
                                <p class="text-lg font-semibold text-red-600"><?php echo $report['arrests_made'] ?? '0'; ?></p>
                            </div>
                            
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">House Visitations</p>
                                <p class="text-lg font-semibold text-[#003366]"><?php echo $report['house_visitations'] ?? '0'; ?></p>
                            </div>
                            
                            <!-- Violations for Oplan Sita -->
                            <?php if ($report['oplan_type'] == 'Oplan Sita'): ?>
                            <div class="md:col-span-2 mt-2">
                                <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Ordinance Violations</p>
                                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 bg-gray-50 p-3 rounded-lg">
                                    <div><span class="text-xs">Drinking:</span> <span class="font-bold"><?php echo $report['drinking_violations'] ?? 0; ?></span></div>
                                    <div><span class="text-xs">Smoking:</span> <span class="font-bold"><?php echo $report['smoking_violations'] ?? 0; ?></span></div>
                                    <div><span class="text-xs">Half-Naked:</span> <span class="font-bold"><?php echo $report['halfnaked_violations'] ?? 0; ?></span></div>
                                    <div><span class="text-xs">Curfew:</span> <span class="font-bold"><?php echo $report['curfew_violations'] ?? 0; ?></span></div>
                                    <div><span class="text-xs">Vandalism:</span> <span class="font-bold"><?php echo $report['vandalism_violations'] ?? 0; ?></span></div>
                                    <div><span class="text-xs">Other:</span> <span class="font-bold"><?php echo $report['other_violations'] ?? 0; ?></span></div>
                                </div>
                            </div>
                            
                            <!-- Disposition for Oplan Sita -->
                            <div class="md:col-span-2 mt-2">
                                <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Disposition</p>
                                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 bg-gray-50 p-3 rounded-lg">
                                    <div><span class="text-xs">Fixed:</span> <span class="font-bold"><?php echo $report['fixed_count'] ?? 0; ?></span></div>
                                    <div><span class="text-xs">Fined:</span> <span class="font-bold"><?php echo $report['fined_count'] ?? 0; ?></span></div>
                                    <div><span class="text-xs">Warned:</span> <span class="font-bold"><?php echo $report['warned_count'] ?? 0; ?></span></div>
                                    <div><span class="text-xs">Charged:</span> <span class="font-bold"><?php echo $report['charged_count'] ?? 0; ?></span></div>
                                    <div><span class="text-xs">Community:</span> <span class="font-bold"><?php echo $report['community_service'] ?? 0; ?></span></div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Disposition for Oplan Bakal (simplified) -->
                            <?php if ($report['oplan_type'] == 'Oplan Bakal' && ($report['fixed_count'] > 0 || $report['fined_count'] > 0 || $report['warned_count'] > 0 || $report['charged_count'] > 0)): ?>
                            <div class="md:col-span-2 mt-2">
                                <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Disposition</p>
                                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 bg-gray-50 p-3 rounded-lg">
                                    <div><span class="text-xs">Fixed:</span> <span class="font-bold"><?php echo $report['fixed_count'] ?? 0; ?></span></div>
                                    <div><span class="text-xs">Fined:</span> <span class="font-bold"><?php echo $report['fined_count'] ?? 0; ?></span></div>
                                    <div><span class="text-xs">Warned:</span> <span class="font-bold"><?php echo $report['warned_count'] ?? 0; ?></span></div>
                                    <div><span class="text-xs">Charged:</span> <span class="font-bold"><?php echo $report['charged_count'] ?? 0; ?></span></div>
                                    <div><span class="text-xs">Community:</span> <span class="font-bold"><?php echo $report['community_service'] ?? 0; ?></span></div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php endif; ?>
                            
                            <!-- GPS Coordinates -->
                            <?php if ($report['latitude'] && $report['longitude']): ?>
                            <div class="md:col-span-2">
                                <p class="text-xs text-gray-500 uppercase tracking-wider">GPS Coordinates</p>
                                <p class="text-sm font-mono bg-gray-100 p-2 rounded">
                                    <?php echo number_format($report['latitude'], 6); ?>° N, 
                                    <?php echo number_format($report['longitude'], 6); ?>° E
                                    <?php if ($report['gps_accuracy']): ?>
                                    <span class="ml-4 text-gray-500">Accuracy: <?php echo $report['gps_accuracy']; ?>m</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Accomplishment Description Card -->
                <div class="info-card">
                    <div class="card-header">
                        <i class="fas fa-trophy"></i>
                        <span>Accomplishment Description</span>
                    </div>
                    <div class="p-6">
                        <div class="bg-gray-50 p-4 rounded-lg text-gray-700 leading-relaxed">
                            <?php echo nl2br(htmlspecialchars($report['accomplishment_description'])); ?>
                        </div>
                    </div>
                </div>

                <!-- Admin Remarks Card - READ ONLY -->
                <div class="info-card">
                    <div class="card-header">
                        <i class="fas fa-comment"></i>
                        <span>Admin Remarks</span>
                    </div>
                    <div class="p-6">
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($report['admin_remarks'] ?? 'No remarks added.')); ?></p>
                        </div>
                        <div class="mt-4 text-sm text-gray-500 italic flex items-center gap-2">
                            <i class="fas fa-info-circle"></i>
                            <span>This report is auto-approved and cannot be modified.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Map and Photos (1/3 width) -->
            <div class="space-y-6">
                <!-- Location Map Card -->
                <div class="info-card">
                    <div class="card-header">
                        <i class="fas fa-map-marked-alt"></i>
                        <span>Report Location</span>
                    </div>
                    <div class="p-4">
                        <div id="map" class="w-full rounded-lg border-2 border-[#FFD700]"></div>
                        <?php if ($report['latitude'] && $report['longitude']): ?>
                        <div class="mt-3 text-center">
                            <a href="https://www.google.com/maps?q=<?php echo $report['latitude']; ?>,<?php echo $report['longitude']; ?>" 
                               target="_blank" 
                               class="inline-block bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm transition">
                                <i class="fas fa-external-link-alt mr-2"></i> Open in Google Maps
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Photo Evidence Card -->
                <div class="info-card">
                    <div class="card-header flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-images"></i>
                            <span>Photo Evidence</span>
                        </div>
                        <span class="bg-[#FFD700] text-[#003366] px-3 py-1 rounded-full text-xs font-bold">
                            <?php echo $photos->num_rows; ?> Photo<?php echo $photos->num_rows != 1 ? 's' : ''; ?>
                        </span>
                    </div>
                    <div class="p-4">
                        <?php if ($photos->num_rows > 0): ?>
                        <div class="photo-grid">
                            <?php while ($photo = $photos->fetch_assoc()): ?>
                            <a href="../<?php echo $photo['photo_path']; ?>" 
                               data-lightbox="report-photos" 
                               data-title="<?php echo htmlspecialchars($photo['photo_name'] ?? 'Activity Photo'); ?>">
                                <img src="../<?php echo $photo['photo_path']; ?>" 
                                     class="photo-item w-full h-full object-cover" 
                                     alt="Activity Photo">
                            </a>
                            <?php endwhile; ?>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-camera-slash text-4xl mb-2"></i>
                            <p>No photos uploaded</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Report Metadata Card -->
                <div class="info-card">
                    <div class="card-header">
                        <i class="fas fa-info-circle"></i>
                        <span>Submission Details</span>
                    </div>
                    <div class="p-4">
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-600">Report ID:</span>
                                <span class="font-mono font-medium"><?php echo strtoupper($type) . '-' . str_pad($id, 5, '0', STR_PAD_LEFT); ?></span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-600">Submitted:</span>
                                <span><?php echo date('F d, Y h:i A', strtotime($report['submitted_at'])); ?></span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-600">Last Updated:</span>
                                <span><?php echo date('F d, Y h:i A', strtotime($report['updated_at'])); ?></span>
                            </div>
                            <div class="flex justify-between py-2">
                                <span class="text-gray-600">Activity Date:</span>
                                <span class="font-medium text-[#003366]">
                                    <?php 
                                    if (isset($report['activity_date']) && $report['activity_date']) {
                                        echo date('F d, Y', strtotime($report['activity_date']));
                                    } else {
                                        echo 'Not specified';
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-600">Activity Time:</span>
                                <span class="font-medium text-[#003366]">
                                    <?php 
                                    if (isset($report['activity_time']) && $report['activity_time']) {
                                        echo date('h:i A', strtotime($report['activity_time']));
                                    } else {
                                        echo 'Not specified';
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="flex justify-between py-2 text-green-600">
                                <span class="text-gray-600">Status:</span>
                                <span class="font-semibold"><i class="fas fa-check-circle mr-1"></i> APPROVED</span>
                            </div>
                        </div>
                    </div>
                </div>
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

        // Initialize Map
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($report['latitude'] && $report['longitude']): ?>
            const lat = <?php echo $report['latitude']; ?>;
            const lng = <?php echo $report['longitude']; ?>;
            
            const map = L.map('map').setView([lat, lng], 17);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19
            }).addTo(map);
            
            const marker = L.marker([lat, lng]).addTo(map);
            marker.bindPopup(`
                <b><?php echo addslashes($report['subtype']); ?></b><br>
                <?php echo addslashes($report['barangay_name']); ?><br>
                <?php echo date('F d, Y', strtotime($report['activity_date'])); ?>
            `).openPopup();
            
            <?php if ($report['gps_accuracy']): ?>
            L.circle([lat, lng], {
                radius: <?php echo $report['gps_accuracy']; ?>,
                color: '#FFD700',
                fillColor: '#003366',
                fillOpacity: 0.2
            }).addTo(map);
            <?php endif; ?>
            
            <?php else: ?>
            const mapDiv = document.getElementById('map');
            if (mapDiv) {
                mapDiv.innerHTML = '<div class="flex items-center justify-center h-full bg-gray-100 rounded-lg"><p class="text-gray-500">No location data available</p></div>';
            }
            <?php endif; ?>
        });

        // Lightbox options
        if (typeof lightbox !== 'undefined') {
            lightbox.option({
                'resizeDuration': 200,
                'wrapAround': true,
                'albumLabel': 'Photo %1 of %2'
            });
        }
    </script>
</body>
</html>
<?php 
if (isset($stmt)) $stmt->close();
if (isset($photo_stmt)) $photo_stmt->close();
$conn->close(); 
?>