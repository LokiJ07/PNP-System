<?php
// =====================================================
// FILE: user/my_reports.php
// PURPOSE: Display all user reports with filtering options
// =====================================================
session_start();
require_once '../config/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user information
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    session_destroy();
    header('Location: ../index.php');
    exit();
}

// Get filter parameters
$filter_type = $_GET['type'] ?? 'all';
$filter_status = $_GET['status'] ?? 'all';
$filter_date_from = $_GET['date_from'] ?? '';
$filter_date_to = $_GET['date_to'] ?? '';

// Build the query to get all user activities
$activities = [];

// Get patrol activities
$patrol_query = "
    SELECT 
        'patrol' as activity_type,
        patrol_id as id,
        patrol_type as subtype,
        specific_location,
        patrol_date as activity_date,
        patrol_time as activity_time,
        status,
        admin_remarks as remarks,
        submitted_at,
        personnel_count,
        vehicle_number,
        accomplishment_description
    FROM patrol_activities 
    WHERE user_id = ?
";
$params = [$user_id];
$types = "i";

if ($filter_type !== 'all' && $filter_type !== 'patrol') {
    $patrol_query .= " AND 1=0";
}

if ($filter_status !== 'all') {
    $patrol_query .= " AND status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

if (!empty($filter_date_from)) {
    $patrol_query .= " AND patrol_date >= ?";
    $params[] = $filter_date_from;
    $types .= "s";
}

if (!empty($filter_date_to)) {
    $patrol_query .= " AND patrol_date <= ?";
    $params[] = $filter_date_to;
    $types .= "s";
}

$patrol_query .= " ORDER BY submitted_at DESC";

$stmt = $conn->prepare($patrol_query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$patrols = $stmt->get_result();
while ($row = $patrols->fetch_assoc()) {
    $activities[] = $row;
}

// Get checkpoint activities
$checkpoint_query = "
    SELECT 
        'checkpoint' as activity_type,
        checkpoint_id as id,
        'Checkpoint Operation' as subtype,
        specific_location,
        checkpoint_date as activity_date,
        checkpoint_time as activity_time,
        status,
        admin_remarks as remarks,
        submitted_at,
        border_control_ops,
        border_personnel,
        mobile_checkpoint_ops,
        mobile_personnel,
        overlapping_ops,
        tct_ovr_accomplishment,
        arrested_accomplishment,
        accomplishment_description
    FROM checkpoint_activities 
    WHERE user_id = ?
";
$params = [$user_id];
$types = "i";

if ($filter_type !== 'all' && $filter_type !== 'checkpoint') {
    $checkpoint_query .= " AND 1=0";
}

if ($filter_status !== 'all') {
    $checkpoint_query .= " AND status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

if (!empty($filter_date_from)) {
    $checkpoint_query .= " AND checkpoint_date >= ?";
    $params[] = $filter_date_from;
    $types .= "s";
}

if (!empty($filter_date_to)) {
    $checkpoint_query .= " AND checkpoint_date <= ?";
    $params[] = $filter_date_to;
    $types .= "s";
}

$checkpoint_query .= " ORDER BY submitted_at DESC";

$stmt = $conn->prepare($checkpoint_query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$checkpoints = $stmt->get_result();
while ($row = $checkpoints->fetch_assoc()) {
    $activities[] = $row;
}

// Get oplan activities
$oplan_query = "
    SELECT 
        'oplan' as activity_type,
        oplan_id as id,
        oplan_type as subtype,
        specific_location,
        oplan_date as activity_date,
        oplan_time as activity_time,
        status,
        admin_remarks as remarks,
        submitted_at,
        personnel_count,
        operations_count,
        arrests_made,
        firearms_seized,
        contraband_kg,
        accomplishment_description
    FROM oplan_activities 
    WHERE user_id = ?
";
$params = [$user_id];
$types = "i";

if ($filter_type !== 'all' && $filter_type !== 'oplan') {
    $oplan_query .= " AND 1=0";
}

if ($filter_status !== 'all') {
    $oplan_query .= " AND status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

if (!empty($filter_date_from)) {
    $oplan_query .= " AND oplan_date >= ?";
    $params[] = $filter_date_from;
    $types .= "s";
}

if (!empty($filter_date_to)) {
    $oplan_query .= " AND oplan_date <= ?";
    $params[] = $filter_date_to;
    $types .= "s";
}

$oplan_query .= " ORDER BY submitted_at DESC";

$stmt = $conn->prepare($oplan_query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$oplans = $stmt->get_result();
while ($row = $oplans->fetch_assoc()) {
    $activities[] = $row;
}

// Sort all activities by submitted_at (most recent first)
usort($activities, function($a, $b) {
    return strtotime($b['submitted_at']) - strtotime($a['submitted_at']);
});

// Get statistics for summary cards
$stats = [];

$result = $conn->query("SELECT COUNT(*) as total FROM patrol_activities WHERE user_id = $user_id");
$stats['patrols'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM checkpoint_activities WHERE user_id = $user_id");
$stats['checkpoints'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM oplan_activities WHERE user_id = $user_id");
$stats['oplans'] = $result->fetch_assoc()['total'];

$result = $conn->query("
    SELECT 
        COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved,
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
        COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected
    FROM (
        SELECT status FROM patrol_activities WHERE user_id = $user_id
        UNION ALL
        SELECT status FROM checkpoint_activities WHERE user_id = $user_id
        UNION ALL
        SELECT status FROM oplan_activities WHERE user_id = $user_id
    ) as all_activities
");
$status_counts = $result->fetch_assoc();

// Get photos for activities
$photo_counts = [];
$photo_query = $conn->query("
    SELECT activity_type, activity_id, COUNT(*) as photo_count
    FROM activity_photos
    WHERE activity_type IN ('patrol', 'checkpoint', 'oplan')
    GROUP BY activity_type, activity_id
");
while ($row = $photo_query->fetch_assoc()) {
    $photo_counts[$row['activity_type']][$row['activity_id']] = $row['photo_count'];
}

// Default profile picture
if (!empty($user['profile_pic']) && file_exists('../' . $user['profile_pic'])) {
    $profile_pic = '../' . $user['profile_pic'];
    $profile_pic_version = '?v=' . filemtime('../' . $user['profile_pic']);
} else {
    $profile_pic = 'https://ui-avatars.com/api/?name=' . urlencode($user['first_name'] . '+' . $user['last_name']) . '&size=100&background=1f6fb2&color=fff';
    $profile_pic_version = '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <link rel="icon" type="image/png" href="../image/pnplogo.png">
    <title>PNP | My Reports</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media (max-width: 640px) {
            .sidebar-mobile {
                position: fixed;
                left: -100%;
                transition: left 0.3s ease;
                z-index: 50;
                width: 80%;
                max-width: 280px;
            }
            .sidebar-mobile.open {
                left: 0;
            }
        }
        button, .clickable {
            min-height: 44px;
            min-width: 44px;
        }
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        .animate-slideIn {
            animation: slideIn 0.3s ease forwards;
        }
        
        /* Status badges */
        .status-badge {
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .status-approved {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-rejected {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        /* Report card hover effect */
        .report-card {
            transition: all 0.2s ease;
        }
        .report-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        /* Photo gallery modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.9);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            max-width: 90%;
            max-height: 90%;
        }
        .modal-img {
            max-width: 100%;
            max-height: 80vh;
            object-fit: contain;
        }
        .photo-thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            transition: opacity 0.2s;
            border: 2px solid #e5e7eb;
        }
        .photo-thumbnail:hover {
            opacity: 0.8;
            border-color: #1f6fb2;
        }
        .no-photos {
            width: 60px;
            height: 60px;
            background-color: #f3f4f6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 0.75rem;
            text-align: center;
            border: 2px dashed #d1d5db;
        }
    </style>
</head>
<body class="flex flex-col md:flex-row bg-[#0a3d62] min-h-screen">

    <!-- Photo Gallery Modal -->
    <div id="photoModal" class="modal" onclick="closePhotoModal()">
        <div class="modal-content relative">
            <button onclick="closePhotoModal()" class="absolute top-4 right-4 text-white text-2xl z-10 bg-black bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-70">
                <i class="fas fa-times"></i>
            </button>
            <img id="modalImage" class="modal-img" src="" alt="Activity Photo">
            <div class="absolute bottom-4 left-0 right-0 text-center text-white text-sm bg-black bg-opacity-50 py-2 px-4 mx-auto w-fit rounded-full" id="modalCaption"></div>
        </div>
    </div>

    <!-- Mobile Menu Button -->
    <button id="mobileMenuBtn" class="md:hidden fixed top-4 left-4 z-50 bg-[#08324f] text-white p-3 rounded-lg shadow-lg">
        <i class="fas fa-bars text-xl"></i>
    </button>

    <!-- Mobile Menu Overlay -->
    <div id="menuOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden" onclick="closeMobileMenu()"></div>

    <!-- Sidebar -->
    <div id="sidebar" class="w-full md:w-[240px] bg-[#08324f] text-white p-4 md:p-5 md:sticky md:top-0 md:h-screen overflow-y-auto sidebar-mobile fixed top-0 left-[-100%] h-screen z-50 transition-all duration-300 ease-in-out">
        <button id="closeSidebar" class="md:hidden absolute top-4 right-4 text-white text-xl">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="flex items-center gap-3 mb-6 pb-3 border-b border-[#1a4b6d] mt-12 md:mt-0">
            <img src="../image/pnplogo.png" class="w-8 h-8 md:w-10 md:h-10 object-contain" alt="PNP Logo">
            <h2 class="text-lg md:text-xl font-semibold">PNP User</h2>
        </div>

        <!-- User Profile Section -->
        <div class="bg-gradient-to-b from-[#0a3d62] to-[#08324f] p-5 rounded-xl mb-6 text-center border border-[#1a4b6d] shadow-lg">
            <div class="relative mx-auto w-24 h-24 mb-3">
                <img src="<?php echo $profile_pic . $profile_pic_version; ?>" class="w-full h-full rounded-full object-cover border-3 border-yellow-400 shadow-lg" alt="Profile" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($user['first_name'].'+'.$user['last_name']); ?>&size=100&background=1f6fb2&color=fff'">
                <div class="absolute bottom-1 right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white"></div>
            </div>
            
            <h3 class="text-lg font-bold text-yellow-400"><?php echo $user['rank'] . ' ' . $user['first_name'] . ' ' . $user['last_name']; ?></h3>
            <p class="text-xs text-gray-300 mb-2">Badge: <?php echo $user['badge_number']; ?></p>
        </div>

        <!-- Simple Menu -->
        <ul class="space-y-2">
            <li class="p-3 rounded-lg hover:bg-[#1f6fb2] transition">
                <a href="user_dashboard.php" class="text-white no-underline block text-sm md:text-base font-medium">
                    <i class="fas fa-tachometer-alt mr-3 w-5"></i> Dashboard
                </a>
            </li>
            
            <li class="p-3 rounded-lg bg-[#0a3d62] border-l-4 border-yellow-400 hover:bg-[#1f6fb2] transition">
                <a href="my_reports.php" class="text-white no-underline block text-sm md:text-base font-medium">
                    <i class="fas fa-file-alt mr-3 w-5 text-yellow-400"></i> My Reports
                </a>
            </li>
            
            <li class="p-3 rounded-lg hover:bg-[#1f6fb2] transition">
                <a href="settings.php" class="text-white no-underline block text-sm md:text-base font-medium">
                    <i class="fas fa-cog mr-3 w-5"></i> Settings
                </a>
            </li>
            
            <li class="my-4 border-t border-[#1a4b6d]"></li>
            
            <li class="p-3 rounded-lg bg-red-600 hover:bg-red-700 transition cursor-pointer">
                <a href="../logout.php" class="text-white no-underline block text-sm md:text-base font-medium">
                    <i class="fas fa-sign-out-alt mr-3 w-5"></i> Logout
                </a>
            </li>
            
            <li class="mt-6 text-center text-xs text-gray-400">
                <p>PNP Manolo Fortich v2.0</p>
                <p class="mt-1">© 2026 All Rights Reserved</p>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-3 md:p-6 lg:p-8 bg-[#eef2f6] overflow-y-auto min-h-screen">
        
        <!-- Header -->
        <div class="bg-white p-3 md:p-4 rounded-lg shadow-sm mb-4 md:mb-6 ml-10 md:ml-0">
            <h2 class="text-xl md:text-2xl font-bold text-[#08324f]">My Reports</h2>
            <p class="text-xs md:text-sm text-gray-600 mt-1">View and track all your submitted activity reports</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
            <div class="bg-white p-3 rounded-lg shadow-sm border-l-4 border-blue-500">
                <p class="text-xs text-gray-500">Total Reports</p>
                <p class="text-xl font-bold text-[#08324f]"><?php echo count($activities); ?></p>
            </div>
            <div class="bg-white p-3 rounded-lg shadow-sm border-l-4 border-green-500">
                <p class="text-xs text-gray-500">Approved</p>
                <p class="text-xl font-bold text-green-600"><?php echo $status_counts['approved'] ?? 0; ?></p>
            </div>
            <div class="bg-white p-3 rounded-lg shadow-sm border-l-4 border-yellow-500">
                <p class="text-xs text-gray-500">Pending</p>
                <p class="text-xl font-bold text-yellow-600"><?php echo $status_counts['pending'] ?? 0; ?></p>
            </div>
            <div class="bg-white p-3 rounded-lg shadow-sm border-l-4 border-red-500">
                <p class="text-xs text-gray-500">Rejected</p>
                <p class="text-xl font-bold text-red-600"><?php echo $status_counts['rejected'] ?? 0; ?></p>
            </div>
            <div class="bg-white p-3 rounded-lg shadow-sm border-l-4 border-purple-500 hidden md:block">
                <p class="text-xs text-gray-500">Patrols</p>
                <p class="text-xl font-bold text-purple-600"><?php echo $stats['patrols']; ?></p>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
            <h3 class="text-sm font-semibold text-[#08324f] mb-3 flex items-center">
                <i class="fas fa-filter mr-2 text-yellow-500"></i> Filter Reports
            </h3>
            
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Activity Type</label>
                    <select name="type" class="w-full p-2 text-sm border border-gray-300 rounded-lg">
                        <option value="all" <?php echo $filter_type == 'all' ? 'selected' : ''; ?>>All Types</option>
                        <option value="patrol" <?php echo $filter_type == 'patrol' ? 'selected' : ''; ?>>Patrols</option>
                        <option value="checkpoint" <?php echo $filter_type == 'checkpoint' ? 'selected' : ''; ?>>Checkpoints</option>
                        <option value="oplan" <?php echo $filter_type == 'oplan' ? 'selected' : ''; ?>>Oplans</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Status</label>
                    <select name="status" class="w-full p-2 text-sm border border-gray-300 rounded-lg">
                        <option value="all" <?php echo $filter_status == 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="pending" <?php echo $filter_status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo $filter_status == 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $filter_status == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Date From</label>
                    <input type="date" name="date_from" value="<?php echo $filter_date_from; ?>" class="w-full p-2 text-sm border border-gray-300 rounded-lg">
                </div>
                
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Date To</label>
                    <input type="date" name="date_to" value="<?php echo $filter_date_to; ?>" class="w-full p-2 text-sm border border-gray-300 rounded-lg">
                </div>
                
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-[#1f6fb2] text-white px-3 py-2 rounded-lg hover:bg-[#0a3d62] transition text-sm">
                        <i class="fas fa-search mr-1"></i> Apply
                    </button>
                    <a href="my_reports.php" class="flex-1 bg-gray-300 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-400 transition text-sm text-center">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Reports List -->
        <div class="space-y-4">
            <?php if (empty($activities)): ?>
            <div class="bg-white p-8 rounded-lg shadow-sm text-center">
                <i class="fas fa-file-alt text-5xl text-gray-300 mb-3"></i>
                <p class="text-gray-500">No reports found</p>
                <p class="text-sm text-gray-400 mt-2">Start by submitting your first activity report</p>
                <a href="user_dashboard.php" class="inline-block mt-4 bg-[#1f6fb2] text-white px-4 py-2 rounded-lg hover:bg-[#0a3d62] transition">
                    <i class="fas fa-plus mr-2"></i> New Report
                </a>
            </div>
            <?php else: ?>
                <?php foreach ($activities as $activity): 
                    // Get photo count for this activity
                    $photo_count = $photo_counts[$activity['activity_type']][$activity['id']] ?? 0;
                    
                    // Determine border color based on type
                    $borderColor = '';
                    if ($activity['activity_type'] == 'patrol') {
                        $borderColor = 'border-blue-500';
                    } elseif ($activity['activity_type'] == 'checkpoint') {
                        $borderColor = 'border-red-500';
                    } elseif ($activity['activity_type'] == 'oplan') {
                        $borderColor = 'border-green-500';
                    }
                    
                    // Format subtype for display
                    $displayType = '';
                    if ($activity['subtype'] == 'Foot Patrol') {
                        $displayType = 'Foot Patrol';
                    } elseif ($activity['subtype'] == 'Mobile Patrol') {
                        $displayType = 'Mobile Patrol';
                    } elseif ($activity['subtype'] == 'Motorcycle Patrol') {
                        $displayType = 'Motor Patrol';
                    } elseif ($activity['subtype'] == 'Oplan Bakal') {
                        $displayType = 'Oplan Bakal';
                    } elseif ($activity['subtype'] == 'Oplan Sita') {
                        $displayType = 'Oplan Sita';
                    } elseif ($activity['subtype'] == 'Checkpoint Operation') {
                        $displayType = 'Checkpoint Operation';
                    } else {
                        $displayType = $activity['subtype'];
                    }
                ?>
                <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 <?php echo $borderColor; ?> report-card">
                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row justify-between items-start gap-2 mb-3">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-[#08324f]"><?php echo $displayType; ?></span>
                            <span class="text-xs text-gray-500">#<?php echo $activity['id']; ?></span>
                            <?php if ($photo_count > 0): ?>
                            <span class="text-xs bg-purple-100 text-purple-600 px-2 py-0.5 rounded-full">
                                <i class="fas fa-camera mr-1"></i> <?php echo $photo_count; ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center gap-2">
                            <?php if ($activity['status'] == 'approved'): ?>
                                <span class="status-badge status-approved">
                                    <i class="fas fa-check-circle"></i> Approved
                                </span>
                            <?php elseif ($activity['status'] == 'pending'): ?>
                                <span class="status-badge status-pending">
                                    <i class="fas fa-clock"></i> Pending
                                </span>
                            <?php elseif ($activity['status'] == 'rejected'): ?>
                                <span class="status-badge status-rejected">
                                    <i class="fas fa-times-circle"></i> Rejected
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Location and Date -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-3 text-sm">
                        <div class="flex items-start gap-2">
                            <i class="fas fa-map-marker-alt text-gray-400 mt-1 w-4"></i>
                            <span class="text-gray-600"><?php echo htmlspecialchars($activity['specific_location']); ?></span>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="far fa-calendar-alt text-gray-400 mt-1 w-4"></i>
                            <span class="text-gray-600">
                                <?php echo date('F d, Y', strtotime($activity['activity_date'])); ?> 
                                at <?php echo date('h:i A', strtotime($activity['activity_time'])); ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Accomplishment Description -->
                    <div class="mb-3">
                        <p class="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg">
                            <?php echo nl2br(htmlspecialchars($activity['accomplishment_description'])); ?>
                        </p>
                    </div>
                    
                    <!-- Details Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-3 text-xs">
                        <!-- Personnel Count -->
                        <div class="bg-gray-50 p-2 rounded">
                            <span class="text-gray-500">Personnel:</span>
                            <span class="font-medium ml-1">
                                <?php 
                                if ($activity['activity_type'] == 'checkpoint') {
                                    echo ($activity['border_personnel'] ?? 0) + ($activity['mobile_personnel'] ?? 0);
                                } else {
                                    echo $activity['personnel_count'] ?? 1;
                                }
                                ?>
                            </span>
                        </div>
                        
                        <!-- Vehicle Number (for patrols) -->
                        <?php if (!empty($activity['vehicle_number'])): ?>
                        <div class="bg-gray-50 p-2 rounded">
                            <span class="text-gray-500">Vehicle:</span>
                            <span class="font-medium ml-1"><?php echo $activity['vehicle_number']; ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <!-- CHECKPOINT FIELDS - All 7 fields -->
                        <?php if ($activity['activity_type'] == 'checkpoint'): ?>
                            <div class="bg-gray-50 p-2 rounded">
                                <span class="text-gray-500">Border Ops:</span>
                                <span class="font-medium ml-1"><?php echo $activity['border_control_ops'] ?? 0; ?></span>
                            </div>
                            <div class="bg-gray-50 p-2 rounded">
                                <span class="text-gray-500">Border Personnel:</span>
                                <span class="font-medium ml-1"><?php echo $activity['border_personnel'] ?? 0; ?></span>
                            </div>
                            <div class="bg-gray-50 p-2 rounded">
                                <span class="text-gray-500">Mobile Ops:</span>
                                <span class="font-medium ml-1"><?php echo $activity['mobile_checkpoint_ops'] ?? 0; ?></span>
                            </div>
                            <div class="bg-gray-50 p-2 rounded">
                                <span class="text-gray-500">Mobile Personnel:</span>
                                <span class="font-medium ml-1"><?php echo $activity['mobile_personnel'] ?? 0; ?></span>
                            </div>
                            <div class="bg-gray-50 p-2 rounded">
                                <span class="text-gray-500">Overlapping Ops:</span>
                                <span class="font-medium ml-1"><?php echo $activity['overlapping_ops'] ?? 0; ?></span>
                            </div>
                            <div class="bg-gray-50 p-2 rounded">
                                <span class="text-gray-500">TCT/OVR:</span>
                                <span class="font-medium ml-1"><?php echo $activity['tct_ovr_accomplishment'] ?? 0; ?></span>
                            </div>
                            <div class="bg-gray-50 p-2 rounded">
                                <span class="text-gray-500">Arrests:</span>
                                <span class="font-medium ml-1"><?php echo $activity['arrested_accomplishment'] ?? 0; ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <!-- OPLAN FIELDS -->
                        <?php if ($activity['activity_type'] == 'oplan'): ?>
                            <div class="bg-gray-50 p-2 rounded">
                                <span class="text-gray-500">Operations:</span>
                                <span class="font-medium ml-1"><?php echo $activity['operations_count'] ?? 1; ?></span>
                            </div>
                            <div class="bg-gray-50 p-2 rounded">
                                <span class="text-gray-500">Arrests:</span>
                                <span class="font-medium ml-1"><?php echo $activity['arrests_made'] ?? 0; ?></span>
                            </div>
                            <?php if (!empty($activity['firearms_seized'])): ?>
                            <div class="bg-gray-50 p-2 rounded">
                                <span class="text-gray-500">Firearms:</span>
                                <span class="font-medium ml-1"><?php echo $activity['firearms_seized']; ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($activity['contraband_kg'])): ?>
                            <div class="bg-gray-50 p-2 rounded">
                                <span class="text-gray-500">Contraband:</span>
                                <span class="font-medium ml-1"><?php echo $activity['contraband_kg']; ?> kg</span>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <!-- Submission Date (common for all) -->
                        <div class="bg-gray-50 p-2 rounded">
                            <span class="text-gray-500">Submitted:</span>
                            <span class="font-medium ml-1"><?php echo date('M d, Y', strtotime($activity['submitted_at'])); ?></span>
                        </div>
                    </div>
                    
                    <!-- Photos Section -->
                    <?php if ($photo_count > 0): ?>
                    <div class="mt-3 border-t pt-3">
                        <p class="text-xs font-medium text-gray-600 mb-2">
                            <i class="fas fa-images mr-1"></i> Photos (<?php echo $photo_count; ?>)
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <?php
                            // Get actual photos for this activity
                            $photo_stmt = $conn->prepare("
                                SELECT photo_path FROM activity_photos 
                                WHERE activity_type = ? AND activity_id = ?
                                LIMIT 3
                            ");
                            $photo_stmt->bind_param("si", $activity['activity_type'], $activity['id']);
                            $photo_stmt->execute();
                            $photo_result = $photo_stmt->get_result();
                            
                            while ($photo = $photo_result->fetch_assoc()):
                            ?>
                            <img src="../<?php echo $photo['photo_path']; ?>" 
                                 class="photo-thumbnail" 
                                 onclick="openPhotoModal('../<?php echo $photo['photo_path']; ?>', '<?php echo $displayType; ?> - Photo')"
                                 alt="Activity Photo">
                            <?php 
                            endwhile;
                            $photo_stmt->close();
                            
                            if ($photo_count > 3):
                            ?>
                            <div class="no-photos" onclick="alert('View all <?php echo $photo_count; ?> photos')">
                                <span>+<?php echo $photo_count - 3; ?> more</span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Remarks (if rejected) -->
                    <?php if ($activity['status'] == 'rejected' && !empty($activity['remarks'])): ?>
                    <div class="mt-3 bg-red-50 border-l-4 border-red-500 p-3 rounded">
                        <p class="text-xs font-medium text-red-800 mb-1">Admin Remarks:</p>
                        <p class="text-sm text-red-700"><?php echo htmlspecialchars($activity['remarks']); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
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
        window.addEventListener('resize', function() { if (window.innerWidth >= 768) closeMobileMenu(); });

        // Photo Modal Functions
        const photoModal = document.getElementById('photoModal');
        const modalImage = document.getElementById('modalImage');
        const modalCaption = document.getElementById('modalCaption');

        function openPhotoModal(imageSrc, caption) {
            modalImage.src = imageSrc;
            modalCaption.textContent = caption || '';
            photoModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closePhotoModal() {
            photoModal.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && photoModal.classList.contains('active')) {
                closePhotoModal();
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>