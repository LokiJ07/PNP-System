<?php
// =====================================================
// FILE: admin/view_user.php
// PURPOSE: Display complete user profile with statistics
// IMPROVED: Removed pending/rejected, added violations/disposition
// =====================================================

session_start();
require_once '../config/db_connect.php';
requireAdmin();

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id === 0) {
    $_SESSION['error'] = 'Invalid user ID';
    header('Location: admin_users.php');
    exit();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $rank = $_POST['rank'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $contact_number = $_POST['contact_number'] ?? '';
    $station = $_POST['station'] ?? '';
    $unit = $_POST['unit'] ?? '';
    $address = $_POST['address'] ?? '';
    $emergency_contact = $_POST['emergency_contact'] ?? '';
    $emergency_number = $_POST['emergency_number'] ?? '';
    
    $update_stmt = $conn->prepare("
        UPDATE users SET 
            rank = ?, first_name = ?, last_name = ?, email = ?,
            contact_number = ?, station = ?, unit = ?, address = ?,
            emergency_contact_name = ?, emergency_contact_number = ?
        WHERE user_id = ?
    ");
    
    $update_stmt->bind_param("ssssssssssi", 
        $rank, $first_name, $last_name, $email,
        $contact_number, $station, $unit, $address,
        $emergency_contact, $emergency_number, $user_id
    );
    
    if ($update_stmt->execute()) {
        $_SESSION['success'] = 'Profile updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update profile';
    }
    $update_stmt->close();
    
    header("Location: view_user.php?id=$user_id");
    exit();
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $new_password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    $pass_stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $pass_stmt->bind_param("si", $hashed_password, $user_id);
    
    if ($pass_stmt->execute()) {
        $_SESSION['success'] = "Password reset successfully. New password: $new_password";
    } else {
        $_SESSION['error'] = 'Failed to reset password';
    }
    $pass_stmt->close();
    
    header("Location: view_user.php?id=$user_id");
    exit();
}

// Get user details
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    $_SESSION['error'] = 'User not found';
    header('Location: admin_users.php');
    exit();
}

// Get user statistics (APPROVED ONLY)
$stats = [];

// Patrol statistics
$result = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN patrol_type = 'Foot Patrol' THEN 1 ELSE 0 END) as foot,
        SUM(CASE WHEN patrol_type = 'Mobile Patrol' THEN 1 ELSE 0 END) as mobile,
        SUM(CASE WHEN patrol_type = 'Motorcycle Patrol' THEN 1 ELSE 0 END) as motor,
        SUM(personnel_count) as total_personnel,
        
        SUM(drinking_violations) as drinking,
        SUM(smoking_violations) as smoking,
        SUM(halfnaked_violations) as halfnaked,
        SUM(curfew_violations) as curfew,
        SUM(vandalism_violations) as vandalism,
        SUM(other_violations) as other
    FROM patrol_activities 
    WHERE user_id = $user_id AND status = 'approved'
");
$patrol_stats = $result->fetch_assoc();
$stats['patrols'] = $patrol_stats['total'] ?? 0;
$stats['foot'] = $patrol_stats['foot'] ?? 0;
$stats['mobile'] = $patrol_stats['mobile'] ?? 0;
$stats['motor'] = $patrol_stats['motor'] ?? 0;
$stats['patrol_personnel'] = $patrol_stats['total_personnel'] ?? 0;

// Patrol violations
$stats['patrol_drinking'] = $patrol_stats['drinking'] ?? 0;
$stats['patrol_smoking'] = $patrol_stats['smoking'] ?? 0;
$stats['patrol_halfnaked'] = $patrol_stats['halfnaked'] ?? 0;
$stats['patrol_curfew'] = $patrol_stats['curfew'] ?? 0;
$stats['patrol_vandalism'] = $patrol_stats['vandalism'] ?? 0;
$stats['patrol_other'] = $patrol_stats['other'] ?? 0;

// Checkpoint statistics
$result = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(border_control_ops) as border_ops,
        SUM(mobile_checkpoint_ops) as mobile_ops,
        SUM(tct_ovr_accomplishment) as tct_ovr,
        SUM(arrested_accomplishment) as arrests,
        SUM(border_personnel + mobile_personnel) as total_personnel,
        
        SUM(fixed_count) as fixed,
        SUM(fined_count) as fined,
        SUM(warned_count) as warned,
        SUM(charged_count) as charged,
        SUM(community_service) as community,
        
        SUM(drinking_violations) as drinking,
        SUM(smoking_violations) as smoking,
        SUM(halfnaked_violations) as halfnaked,
        SUM(curfew_violations) as curfew,
        SUM(vandalism_violations) as vandalism,
        SUM(other_violations) as other
    FROM checkpoint_activities 
    WHERE user_id = $user_id AND status = 'approved'
");
$checkpoint_stats = $result->fetch_assoc();
$stats['checkpoints'] = $checkpoint_stats['total'] ?? 0;
$stats['border_ops'] = $checkpoint_stats['border_ops'] ?? 0;
$stats['checkpoint_mobile'] = $checkpoint_stats['mobile_ops'] ?? 0;
$stats['tct_ovr'] = $checkpoint_stats['tct_ovr'] ?? 0;
$stats['checkpoint_arrests'] = $checkpoint_stats['arrests'] ?? 0;
$stats['checkpoint_personnel'] = $checkpoint_stats['total_personnel'] ?? 0;

// Checkpoint disposition
$stats['checkpoint_fixed'] = $checkpoint_stats['fixed'] ?? 0;
$stats['checkpoint_fined'] = $checkpoint_stats['fined'] ?? 0;
$stats['checkpoint_warned'] = $checkpoint_stats['warned'] ?? 0;
$stats['checkpoint_charged'] = $checkpoint_stats['charged'] ?? 0;
$stats['checkpoint_community'] = $checkpoint_stats['community'] ?? 0;

// Checkpoint violations
$stats['checkpoint_drinking'] = $checkpoint_stats['drinking'] ?? 0;
$stats['checkpoint_smoking'] = $checkpoint_stats['smoking'] ?? 0;
$stats['checkpoint_halfnaked'] = $checkpoint_stats['halfnaked'] ?? 0;
$stats['checkpoint_curfew'] = $checkpoint_stats['curfew'] ?? 0;
$stats['checkpoint_vandalism'] = $checkpoint_stats['vandalism'] ?? 0;
$stats['checkpoint_other'] = $checkpoint_stats['other'] ?? 0;

// Oplan statistics
$result = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN oplan_type = 'Oplan Bakal' THEN 1 ELSE 0 END) as bakal,
        SUM(CASE WHEN oplan_type = 'Oplan Sita' THEN 1 ELSE 0 END) as sita,
        SUM(firearms_seized) as firearms,
        SUM(contraband_kg) as contraband,
        SUM(arrests_made) as arrests,
        SUM(personnel_count) as total_personnel,
        SUM(house_visitations) as house_visits,
        SUM(kontra_boga) as kontra_boga,
        SUM(anti_vaping) as anti_vaping,
        
        SUM(fixed_count) as fixed,
        SUM(fined_count) as fined,
        SUM(warned_count) as warned,
        SUM(charged_count) as charged,
        SUM(community_service) as community,
        
        SUM(drinking_violations) as drinking,
        SUM(smoking_violations) as smoking,
        SUM(halfnaked_violations) as halfnaked,
        SUM(curfew_violations) as curfew,
        SUM(vandalism_violations) as vandalism,
        SUM(other_violations) as other
    FROM oplan_activities 
    WHERE user_id = $user_id AND status = 'approved'
");
$oplan_stats = $result->fetch_assoc();
$stats['oplans'] = $oplan_stats['total'] ?? 0;
$stats['bakal'] = $oplan_stats['bakal'] ?? 0;
$stats['sita'] = $oplan_stats['sita'] ?? 0;
$stats['firearms'] = $oplan_stats['firearms'] ?? 0;
$stats['contraband'] = $oplan_stats['contraband'] ?? 0;
$stats['oplan_arrests'] = $oplan_stats['arrests'] ?? 0;
$stats['oplan_personnel'] = $oplan_stats['total_personnel'] ?? 0;
$stats['house_visits'] = $oplan_stats['house_visits'] ?? 0;
$stats['kontra_boga'] = $oplan_stats['kontra_boga'] ?? 0;
$stats['anti_vaping'] = $oplan_stats['anti_vaping'] ?? 0;

// Oplan disposition
$stats['oplan_fixed'] = $oplan_stats['fixed'] ?? 0;
$stats['oplan_fined'] = $oplan_stats['fined'] ?? 0;
$stats['oplan_warned'] = $oplan_stats['warned'] ?? 0;
$stats['oplan_charged'] = $oplan_stats['charged'] ?? 0;
$stats['oplan_community'] = $oplan_stats['community'] ?? 0;

// Oplan violations
$stats['oplan_drinking'] = $oplan_stats['drinking'] ?? 0;
$stats['oplan_smoking'] = $oplan_stats['smoking'] ?? 0;
$stats['oplan_halfnaked'] = $oplan_stats['halfnaked'] ?? 0;
$stats['oplan_curfew'] = $oplan_stats['curfew'] ?? 0;
$stats['oplan_vandalism'] = $oplan_stats['vandalism'] ?? 0;
$stats['oplan_other'] = $oplan_stats['other'] ?? 0;

// Total activities
$stats['total_activities'] = $stats['patrols'] + $stats['checkpoints'] + $stats['oplans'];
$stats['total_personnel'] = $stats['patrol_personnel'] + $stats['checkpoint_personnel'] + $stats['oplan_personnel'];
$stats['total_arrests'] = $stats['checkpoint_arrests'] + $stats['oplan_arrests'];

// Get recent activities (APPROVED ONLY)
$recent = [];

$patrols = $conn->query("
    SELECT 'patrol' as type, patrol_type as subtype, specific_location, 
           patrol_date as activity_date, patrol_time as activity_time,
           submitted_at
    FROM patrol_activities 
    WHERE user_id = $user_id AND status = 'approved'
    ORDER BY submitted_at DESC
    LIMIT 3
");
while ($row = $patrols->fetch_assoc()) {
    $row['status'] = 'approved';
    $recent[] = $row;
}

$checkpoints = $conn->query("
    SELECT 'checkpoint' as type, 'Checkpoint' as subtype, specific_location,
           checkpoint_date as activity_date, checkpoint_time as activity_time,
           submitted_at
    FROM checkpoint_activities 
    WHERE user_id = $user_id AND status = 'approved'
    ORDER BY submitted_at DESC
    LIMIT 3
");
while ($row = $checkpoints->fetch_assoc()) {
    $row['status'] = 'approved';
    $recent[] = $row;
}

$oplans = $conn->query("
    SELECT 'oplan' as type, oplan_type as subtype, specific_location,
           oplan_date as activity_date, oplan_time as activity_time,
           submitted_at
    FROM oplan_activities 
    WHERE user_id = $user_id AND status = 'approved'
    ORDER BY submitted_at DESC
    LIMIT 3
");
while ($row = $oplans->fetch_assoc()) {
    $row['status'] = 'approved';
    $recent[] = $row;
}

usort($recent, function($a, $b) {
    return strtotime($b['submitted_at']) - strtotime($a['submitted_at']);
});
$recent = array_slice($recent, 0, 5);

// Format dates
$last_login = $user['last_login'] ? date('F d, Y h:i A', strtotime($user['last_login'])) : 'Never logged in';
$member_since = $user['date_hired'] ? date('F d, Y', strtotime($user['date_hired'])) : 'Not specified';

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
    <title>PNP | User Profile</title>
    
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
        
        /* Modal styles */
        .modal {
            transition: opacity 0.3s ease;
        }
        .modal.hidden {
            display: none;
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
        
        /* Form input focus */
        .form-input:focus {
            outline: none;
            border-color: #1f6fb2;
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
            
            <li class="bg-[#1e4a6a] rounded-lg"><a href="admin_users.php" class="flex items-center gap-3 p-3"><i class="fas fa-users w-5 text-yellow-400"></i> Users</a></li>
            <li><a href="accomplishment_report.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1e4a6a] transition"><i class="fas fa-file-alt w-5"></i> Accomplishment Report</a></li>
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
        <div class="bg-white p-4 md:p-6 rounded-lg shadow-md mb-6 border-l-4 border-yellow-400 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="admin_users.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <h2 class="text-xl md:text-2xl font-bold text-[#08324f]">User Profile</h2>
            </div>
            <div class="flex items-center gap-3">
                <span class="auto-approve-badge">
                    <i class="fas fa-check-circle mr-1"></i> Auto-Approved
                </span>
                <span class="bg-blue-100 text-blue-700 px-4 py-2 rounded-full text-sm font-semibold flex items-center gap-2">
                    <i class="fas fa-id-card"></i> <?php echo $user['badge_number']; ?>
                </span>
            </div>
        </div>

        <!-- User Profile Card -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <!-- Profile Header -->
            <div class="bg-gradient-to-r from-[#08324f] to-[#1e4a6a] p-6 md:p-8 text-white">
                <div class="flex flex-col md:flex-row gap-6 items-start md:items-center">
                    <!-- Profile Avatar -->
                    <div class="relative">
                        <div class="w-24 h-24 md:w-28 md:h-28 bg-yellow-400 rounded-full flex items-center justify-center text-[#08324f] text-3xl md:text-4xl font-bold border-4 border-white shadow-lg">
                            <?php echo substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1); ?>
                        </div>
                        <div class="absolute -bottom-2 -right-2 px-3 py-1 rounded-full text-xs font-bold
                            <?php echo $user['account_status'] == 'active' ? 'bg-green-500' : 'bg-red-500'; ?> text-white shadow-lg">
                            <?php echo strtoupper($user['account_status']); ?>
                        </div>
                    </div>
                    
                    <!-- Basic Info -->
                    <div class="flex-1">
                        <h1 class="text-2xl md:text-3xl font-bold"><?php echo $user['rank'] . ' ' . $user['first_name'] . ' ' . $user['last_name']; ?></h1>
                        <p class="text-yellow-400 mt-1 text-base md:text-lg"><?php echo $user['email']; ?></p>
                        <div class="flex flex-wrap gap-3 mt-4">
                            <div class="bg-[#1e4a6a] px-4 py-2 rounded-lg">
                                <p class="text-xs text-gray-300">Station</p>
                                <p class="font-semibold"><?php echo $user['station'] ?? 'Manolo Fortich MPS'; ?></p>
                            </div>
                            <div class="bg-[#1e4a6a] px-4 py-2 rounded-lg">
                                <p class="text-xs text-gray-300">Unit</p>
                                <p class="font-semibold"><?php echo $user['unit'] ?? 'Patrol Unit'; ?></p>
                            </div>
                            <div class="bg-[#1e4a6a] px-4 py-2 rounded-lg">
                                <p class="text-xs text-gray-300">Role</p>
                                <p class="font-semibold capitalize"><?php echo $user['role']; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Last Login -->
                    <div class="text-left md:text-right bg-[#1e4a6a] p-4 rounded-lg w-full md:w-auto">
                        <p class="text-sm text-gray-300"><i class="fas fa-clock mr-1"></i> Last Login</p>
                        <p class="font-semibold"><?php echo $last_login; ?></p>
                        <p class="text-sm text-gray-300 mt-2"><i class="fas fa-calendar-alt mr-1"></i> Member Since</p>
                        <p class="font-semibold"><?php echo $member_since; ?></p>
                    </div>
                </div>
            </div>

            <!-- Profile Details -->
            <div class="p-4 md:p-8">
                <!-- Statistics Summary -->
                <h3 class="text-lg md:text-xl font-semibold text-[#08324f] mb-6 flex items-center gap-2">
                    <i class="fas fa-chart-pie text-yellow-500"></i> Activity Statistics
                </h3>
                
                <!-- Summary Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-6 mb-8">
                    <div class="stat-card bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg shadow-md border-l-4 border-blue-500">
                        <p class="text-xs text-gray-600">Total Activities</p>
                        <p class="text-xl md:text-2xl font-bold text-[#08324f] mt-1"><?php echo $stats['total_activities']; ?></p>
                    </div>
                    
                    <div class="stat-card bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-lg shadow-md border-l-4 border-green-500">
                        <p class="text-xs text-gray-600">Personnel</p>
                        <p class="text-xl md:text-2xl font-bold text-[#08324f] mt-1"><?php echo $stats['total_personnel']; ?></p>
                    </div>
                    
                    <div class="stat-card bg-gradient-to-br from-red-50 to-red-100 p-4 rounded-lg shadow-md border-l-4 border-red-500">
                        <p class="text-xs text-gray-600">Arrests</p>
                        <p class="text-xl md:text-2xl font-bold text-[#08324f] mt-1"><?php echo $stats['total_arrests']; ?></p>
                    </div>
                    
                    <div class="stat-card bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-lg shadow-md border-l-4 border-purple-500">
                        <p class="text-xs text-gray-600">Firearms</p>
                        <p class="text-xl md:text-2xl font-bold text-[#08324f] mt-1"><?php echo $stats['firearms']; ?></p>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="mb-8 p-4 md:p-6 bg-gray-50 rounded-lg border-l-4 border-blue-500">
                    <h4 class="text-base md:text-lg font-semibold text-[#08324f] mb-4 flex items-center gap-2">
                        <i class="fas fa-address-card text-yellow-500"></i> Contact Information
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500">Contact Number</p>
                            <p class="font-semibold"><?php echo $user['contact_number'] ?? 'Not specified'; ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Address</p>
                            <p class="font-semibold"><?php echo $user['address'] ?? 'Not specified'; ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Emergency Contact</p>
                            <p class="font-semibold"><?php echo $user['emergency_contact_name'] ?? 'Not specified'; ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Emergency Number</p>
                            <p class="font-semibold"><?php echo $user['emergency_contact_number'] ?? 'Not specified'; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Activity Breakdown Tabs -->
                <div class="mb-8">
                    <div class="border-b border-gray-200 mb-4">
                        <div class="flex gap-4">
                            <button onclick="showTab('patrol')" id="tab-patrol-btn" class="px-4 py-2 text-sm font-medium border-b-2 border-yellow-500 text-[#08324f]">Patrol</button>
                            <button onclick="showTab('checkpoint')" id="tab-checkpoint-btn" class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-[#08324f]">Checkpoint</button>
                            <button onclick="showTab('oplan')" id="tab-oplan-btn" class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-[#08324f]">Oplan</button>
                        </div>
                    </div>

                    <!-- Patrol Tab -->
                    <div id="tab-patrol" class="tab-content">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h5 class="font-medium text-[#08324f] mb-3">By Type</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span>🚶 Foot Patrol:</span>
                                        <span class="font-bold"><?php echo $stats['foot']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>🚗 Mobile Patrol:</span>
                                        <span class="font-bold"><?php echo $stats['mobile']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>🏍️ Motor Patrol:</span>
                                        <span class="font-bold"><?php echo $stats['motor']; ?></span>
                                    </div>
                                    <div class="flex justify-between border-t pt-2 mt-2">
                                        <span>Personnel:</span>
                                        <span class="font-bold"><?php echo $stats['patrol_personnel']; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h5 class="font-medium text-[#08324f] mb-3">Violations</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span>Drinking:</span>
                                        <span class="font-bold"><?php echo $stats['patrol_drinking']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Smoking:</span>
                                        <span class="font-bold"><?php echo $stats['patrol_smoking']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Half-Naked:</span>
                                        <span class="font-bold"><?php echo $stats['patrol_halfnaked']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Curfew:</span>
                                        <span class="font-bold"><?php echo $stats['patrol_curfew']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Vandalism:</span>
                                        <span class="font-bold"><?php echo $stats['patrol_vandalism']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Other:</span>
                                        <span class="font-bold"><?php echo $stats['patrol_other']; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Checkpoint Tab -->
                    <div id="tab-checkpoint" class="tab-content hidden">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h5 class="font-medium text-[#08324f] mb-3">Operations</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span>Total Checkpoints:</span>
                                        <span class="font-bold"><?php echo $stats['checkpoints']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Border Control:</span>
                                        <span class="font-bold"><?php echo $stats['border_ops']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Mobile Ops:</span>
                                        <span class="font-bold"><?php echo $stats['checkpoint_mobile']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>TCT/OVR:</span>
                                        <span class="font-bold"><?php echo $stats['tct_ovr']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Arrests:</span>
                                        <span class="font-bold"><?php echo $stats['checkpoint_arrests']; ?></span>
                                    </div>
                                    <div class="flex justify-between border-t pt-2 mt-2">
                                        <span>Personnel:</span>
                                        <span class="font-bold"><?php echo $stats['checkpoint_personnel']; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h5 class="font-medium text-[#08324f] mb-3">Disposition & Violations</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span>Fixed:</span>
                                        <span class="font-bold"><?php echo $stats['checkpoint_fixed']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Fined:</span>
                                        <span class="font-bold"><?php echo $stats['checkpoint_fined']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Warned:</span>
                                        <span class="font-bold"><?php echo $stats['checkpoint_warned']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Charged:</span>
                                        <span class="font-bold"><?php echo $stats['checkpoint_charged']; ?></span>
                                    </div>
                                    <div class="flex justify-between border-b pb-2 mb-2">
                                        <span>Community:</span>
                                        <span class="font-bold"><?php echo $stats['checkpoint_community']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Drinking:</span>
                                        <span class="font-bold"><?php echo $stats['checkpoint_drinking']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Smoking:</span>
                                        <span class="font-bold"><?php echo $stats['checkpoint_smoking']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Curfew:</span>
                                        <span class="font-bold"><?php echo $stats['checkpoint_curfew']; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Oplan Tab -->
                    <div id="tab-oplan" class="tab-content hidden">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h5 class="font-medium text-[#08324f] mb-3">Operations</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span>Total Oplans:</span>
                                        <span class="font-bold"><?php echo $stats['oplans']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Oplan Bakal:</span>
                                        <span class="font-bold"><?php echo $stats['bakal']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Oplan Sita:</span>
                                        <span class="font-bold"><?php echo $stats['sita']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Firearms:</span>
                                        <span class="font-bold"><?php echo $stats['firearms']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Contraband:</span>
                                        <span class="font-bold"><?php echo number_format($stats['contraband'], 2); ?> kg</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Kontra Boga:</span>
                                        <span class="font-bold"><?php echo $stats['kontra_boga']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Anti-Vaping:</span>
                                        <span class="font-bold"><?php echo $stats['anti_vaping']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>House Visits:</span>
                                        <span class="font-bold"><?php echo $stats['house_visits']; ?></span>
                                    </div>
                                    <div class="flex justify-between border-t pt-2 mt-2">
                                        <span>Personnel:</span>
                                        <span class="font-bold"><?php echo $stats['oplan_personnel']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Arrests:</span>
                                        <span class="font-bold"><?php echo $stats['oplan_arrests']; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h5 class="font-medium text-[#08324f] mb-3">Disposition & Violations</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span>Fixed:</span>
                                        <span class="font-bold"><?php echo $stats['oplan_fixed']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Fined:</span>
                                        <span class="font-bold"><?php echo $stats['oplan_fined']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Warned:</span>
                                        <span class="font-bold"><?php echo $stats['oplan_warned']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Charged:</span>
                                        <span class="font-bold"><?php echo $stats['oplan_charged']; ?></span>
                                    </div>
                                    <div class="flex justify-between border-b pb-2 mb-2">
                                        <span>Community:</span>
                                        <span class="font-bold"><?php echo $stats['oplan_community']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Drinking:</span>
                                        <span class="font-bold"><?php echo $stats['oplan_drinking']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Smoking:</span>
                                        <span class="font-bold"><?php echo $stats['oplan_smoking']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Curfew:</span>
                                        <span class="font-bold"><?php echo $stats['oplan_curfew']; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="mb-8">
                    <h4 class="text-base md:text-lg font-semibold text-[#08324f] mb-4 flex items-center gap-2">
                        <i class="fas fa-history text-yellow-500"></i> Recent Activities
                    </h4>
                    <div class="space-y-3">
                        <?php if (empty($recent)): ?>
                        <p class="text-gray-500 text-center py-4 bg-gray-50 rounded-lg">No recent activities found</p>
                        <?php else: ?>
                        <?php foreach ($recent as $activity): ?>
                        <div class="bg-gray-50 p-4 rounded-lg border-l-4 
                            <?php 
                            echo $activity['type'] == 'patrol' ? 'border-blue-500' : 
                                ($activity['type'] == 'checkpoint' ? 'border-red-500' : 'border-green-500'); 
                            ?>">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                                <div>
                                    <span class="font-medium flex items-center gap-2">
                                        <?php 
                                        if ($activity['type'] == 'patrol') {
                                            echo '<i class="fas fa-walking text-blue-600"></i> ' . $activity['subtype'];
                                        } elseif ($activity['type'] == 'checkpoint') {
                                            echo '<i class="fas fa-map-marker-alt text-red-600"></i> Checkpoint';
                                        } else {
                                            echo '<i class="fas fa-shield-alt text-green-600"></i> ' . $activity['subtype'];
                                        }
                                        ?>
                                    </span>
                                    <p class="text-sm text-gray-600 mt-1"><?php echo $activity['specific_location']; ?></p>
                                </div>
                                <div class="text-right">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                        Approved
                                    </span>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <?php echo date('M d, Y', strtotime($activity['activity_date'])); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-wrap gap-3 justify-end border-t pt-6">
                    <button onclick="openEditModal()" class="bg-[#1f6fb2] hover:bg-[#0a3d62] text-white px-6 py-2 rounded-lg transition flex items-center gap-2">
                        <i class="fas fa-edit"></i> Edit Profile
                    </button>
                    
                    <button onclick="openResetModal()" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded-lg transition flex items-center gap-2">
                        <i class="fas fa-key"></i> Reset Password
                    </button>
                    
                    <?php if ($user['account_status'] == 'active'): ?>
                    <a href="admin_users.php?action=deactivate&id=<?php echo $user['user_id']; ?>" 
                       class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg transition flex items-center gap-2"
                       onclick="return confirm('Are you sure you want to deactivate this user?')">
                        <i class="fas fa-ban"></i> Deactivate
                    </a>
                    <?php else: ?>
                    <a href="admin_users.php?action=activate&id=<?php echo $user['user_id']; ?>" 
                       class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition flex items-center gap-2"
                       onclick="return confirm('Are you sure you want to activate this user?')">
                        <i class="fas fa-check-circle"></i> Activate
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editProfileModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4 modal">
        <div class="bg-white rounded-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto shadow-2xl" onclick="event.stopPropagation()">
            <div class="bg-[#08324f] text-white p-5 rounded-t-xl flex justify-between items-center sticky top-0">
                <h3 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-edit text-yellow-400 mr-2"></i>
                    Edit User Profile
                </h3>
                <button onclick="closeEditModal()" class="text-white hover:text-gray-300 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form method="POST" class="p-6">
                <input type="hidden" name="update_profile" value="1">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rank</label>
                        <select name="rank" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2]">
                            <option value="PO1" <?php echo $user['rank'] == 'PO1' ? 'selected' : ''; ?>>PO1</option>
                            <option value="PO2" <?php echo $user['rank'] == 'PO2' ? 'selected' : ''; ?>>PO2</option>
                            <option value="PO3" <?php echo $user['rank'] == 'PO3' ? 'selected' : ''; ?>>PO3</option>
                            <option value="SPO1" <?php echo $user['rank'] == 'SPO1' ? 'selected' : ''; ?>>SPO1</option>
                            <option value="SPO2" <?php echo $user['rank'] == 'SPO2' ? 'selected' : ''; ?>>SPO2</option>
                            <option value="SPO3" <?php echo $user['rank'] == 'SPO3' ? 'selected' : ''; ?>>SPO3</option>
                            <option value="SPO4" <?php echo $user['rank'] == 'SPO4' ? 'selected' : ''; ?>>SPO4</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="<?php echo $user['email']; ?>" required class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2]">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                        <input type="text" name="first_name" value="<?php echo $user['first_name']; ?>" required class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2]">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                        <input type="text" name="last_name" value="<?php echo $user['last_name']; ?>" required class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2]">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                        <input type="text" name="contact_number" value="<?php echo $user['contact_number'] ?? ''; ?>" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2]">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Station</label>
                        <input type="text" name="station" value="<?php echo $user['station'] ?? 'Manolo Fortich MPS'; ?>" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2]">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                        <input type="text" name="unit" value="<?php echo $user['unit'] ?? 'Patrol Unit'; ?>" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2]">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <textarea name="address" rows="2" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2]"><?php echo $user['address'] ?? ''; ?></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Emergency Contact Name</label>
                        <input type="text" name="emergency_contact" value="<?php echo $user['emergency_contact_name'] ?? ''; ?>" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2]">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Emergency Contact Number</label>
                        <input type="text" name="emergency_number" value="<?php echo $user['emergency_contact_number'] ?? ''; ?>" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2]">
                    </div>
                </div>
                
                <div class="flex gap-3 justify-end mt-6 border-t pt-4">
                    <button type="button" onclick="closeEditModal()" class="px-6 py-2 border border-gray-300 bg-white rounded-lg hover:bg-gray-100 transition text-sm font-medium">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2 bg-[#1f6fb2] text-white rounded-lg hover:bg-[#0a3d62] transition text-sm font-medium">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div id="resetPasswordModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4 modal">
        <div class="bg-white rounded-xl max-w-md w-full shadow-2xl" onclick="event.stopPropagation()">
            <div class="bg-[#08324f] text-white p-5 rounded-t-xl flex justify-between items-center">
                <h3 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-key text-yellow-400 mr-2"></i>
                    Reset Password
                </h3>
                <button onclick="closeResetModal()" class="text-white hover:text-gray-300 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="p-6">
                <p class="text-gray-600 mb-6">Are you sure you want to reset the password for <strong><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></strong>? A new random password will be generated and displayed.</p>
                
                <form method="POST">
                    <input type="hidden" name="reset_password" value="1">
                    
                    <div class="flex gap-3 justify-end">
                        <button type="button" onclick="closeResetModal()" class="px-6 py-2 border border-gray-300 bg-white rounded-lg hover:bg-gray-100 transition text-sm font-medium">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-medium">
                            Reset Password
                        </button>
                    </div>
                </form>
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

        // Tab Functions
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.add('hidden');
            });
            
            // Remove active class from all buttons
            document.querySelectorAll('[id^="tab-"]').forEach(btn => {
                btn.classList.remove('border-yellow-500', 'text-[#08324f]');
                btn.classList.add('text-gray-500');
            });
            
            // Show selected tab
            document.getElementById(`tab-${tabName}`).classList.remove('hidden');
            
            // Activate button
            const btn = document.getElementById(`tab-${tabName}-btn`);
            btn.classList.remove('text-gray-500');
            btn.classList.add('border-yellow-500', 'text-[#08324f]');
        }

        // Modal Functions
        function openEditModal() {
            document.getElementById('editProfileModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeEditModal() {
            document.getElementById('editProfileModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function openResetModal() {
            document.getElementById('resetPasswordModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeResetModal() {
            document.getElementById('resetPasswordModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Close modals when clicking outside
        document.getElementById('editProfileModal').addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });
        
        document.getElementById('resetPasswordModal').addEventListener('click', function(e) {
            if (e.target === this) closeResetModal();
        });

        // Close modals on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEditModal();
                closeResetModal();
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>