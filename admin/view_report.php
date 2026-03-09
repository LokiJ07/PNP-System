<?php
// =====================================================
// FILE: admin/view_report.php
// PURPOSE: Display complete activity report details (LOCKED after approval)
// FIXED: Properly handles different activity types and shows correct details
// =====================================================

session_start();
require_once '../config/db_connect.php';
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

// Check if report is already approved - if yes, lock it
$is_approved = ($report['status'] === 'approved');
$is_rejected = ($report['status'] === 'rejected');

// Get photos for this report
$photo_stmt = $conn->prepare("SELECT * FROM activity_photos WHERE activity_type = ? AND activity_id = ?");
$photo_stmt->bind_param("si", $display_type, $id);
$photo_stmt->execute();
$photos = $photo_stmt->get_result();

// Handle status update if POST and report is NOT approved
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_approved && !$is_rejected) {
    $new_status = $_POST['status'] ?? '';
    $admin_remarks = $_POST['admin_remarks'] ?? '';
    
    if ($new_status) {
        $table = '';
        $id_field = '';
        
        switch ($display_type) {
            case 'patrol':
                $table = 'patrol_activities';
                $id_field = 'patrol_id';
                break;
            case 'checkpoint':
                $table = 'checkpoint_activities';
                $id_field = 'checkpoint_id';
                break;
            case 'oplan':
                $table = 'oplan_activities';
                $id_field = 'oplan_id';
                break;
        }
        
        $update = $conn->prepare("UPDATE $table SET status = ?, admin_remarks = ? WHERE $id_field = ?");
        $update->bind_param("ssi", $new_status, $admin_remarks, $id);
        
        if ($update->execute()) {
            $_SESSION['success'] = 'Report status updated successfully';
            // Refresh the report data
            $report['status'] = $new_status;
            $report['admin_remarks'] = $admin_remarks;
            // If approved, reload to show locked view
            if ($new_status === 'approved') {
                header("Location: view_report.php?type=$type&id=$id");
                exit();
            }
        }
        $update->close();
    }
}
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
        .dropdown-content { display: none; }
        .dropdown.active .dropdown-content { display: block; }
        .rotate-180 { transform: rotate(180deg); }
        #map { height: 300px; width: 100%; border-radius: 12px; z-index: 1; }
        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 1rem;
        }
        .photo-item {
            aspect-ratio: 1/1;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.3s;
            border: 2px solid #e5e7eb;
        }
        .photo-item:hover {
            transform: scale(1.05);
            border-color: #1f6fb2;
        }
        .status-badge {
            padding: 0.5rem 1.5rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.875rem;
        }
        .locked-badge {
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
        .approved-stamp {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            font-size: 4rem;
            font-weight: bold;
            color: rgba(34, 197, 94, 0.2);
            border: 5px solid rgba(34, 197, 94, 0.2);
            padding: 1rem 2rem;
            border-radius: 1rem;
            pointer-events: none;
            z-index: 10;
        }
    </style>
</head>
<body class="flex bg-[#0a3d62]">

    <!-- Sidebar -->
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
            <li class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer mt-5 pt-4 border-t border-[#1a4b6d]">
                <a href="../logout.php" class="text-white no-underline block">
                    <i class="fas fa-sign-out-alt mr-3"></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-8 bg-[#eef2f6] overflow-y-auto h-screen relative">
        
        <?php if ($is_approved): ?>
        <!-- Approved Stamp (visual indicator only) -->
        <div class="approved-stamp hidden md:block">APPROVED</div>
        <?php endif; ?>

        <!-- Display Session Messages -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-lg">
            <i class="fas fa-check-circle mr-2"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-lg">
            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
        <?php endif; ?>

        <!-- Header with Back Button and Status -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6 border-l-4 border-yellow-400 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="javascript:history.back()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <h2 class="text-2xl font-bold text-[#08324f]">
                    <?php 
                    if ($display_type == 'patrol') echo 'Patrol Report';
                    elseif ($display_type == 'checkpoint') echo 'Checkpoint Report';
                    else echo 'Oplan Report';
                    ?>
                </h2>
            </div>
            <div class="flex items-center gap-3">
                <?php if ($is_approved): ?>
                <span class="locked-badge">
                    <i class="fas fa-lock mr-1"></i> LOCKED - Approved
                </span>
                <?php elseif ($is_rejected): ?>
                <span class="locked-badge" style="background: #dc2626;">
                    <i class="fas fa-ban mr-1"></i> REJECTED
                </span>
                <?php endif; ?>
                <span class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded-full text-sm font-semibold">
                    <i class="fas fa-hashtag mr-2"></i> <?php echo strtoupper($type) . '-' . str_pad($id, 5, '0', STR_PAD_LEFT); ?>
                </span>
            </div>
        </div>

        <!-- Report Details Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Column - Main Details (2/3 width) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Officer Information Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-[#08324f] text-white px-6 py-4">
                        <h3 class="font-semibold text-lg"><i class="fas fa-user-shield text-yellow-400 mr-3"></i> Officer Information</h3>
                    </div>
                    <div class="p-6">
                        <div class="flex items-start gap-6">
                            <div class="w-20 h-20 bg-[#1f6fb2] rounded-full flex items-center justify-center text-white text-3xl font-bold border-3 border-yellow-400">
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
                                <h4 class="text-xl font-bold text-[#08324f]"><?php echo $report['officer_name']; ?></h4>
                                <p class="text-gray-600 mt-1">Badge: <?php echo $report['badge_number']; ?></p>
                                <p class="text-gray-600">Email: <?php echo $report['email']; ?></p>
                                <?php if (!empty($report['contact_number'])): ?>
                                <p class="text-gray-600">Contact: <?php echo $report['contact_number']; ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="text-right">
                                <span class="inline-block px-4 py-2 rounded-full text-sm font-semibold
                                    <?php 
                                    echo $report['status'] == 'approved' ? 'bg-green-100 text-green-800' : 
                                        ($report['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); 
                                    ?>">
                                    <i class="fas fa-circle mr-2 text-xs"></i>
                                    <?php echo strtoupper($report['status']); ?>
                                </span>
                                
                                <?php if ($is_approved): ?>
                                <div class="mt-2 text-xs text-gray-500">
                                    <i class="fas fa-lock mr-1"></i> Locked - Cannot be modified
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Details Card - Dynamically shows fields based on type -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-[#08324f] text-white px-6 py-4">
                        <h3 class="font-semibold text-lg"><i class="fas fa-clipboard-list text-yellow-400 mr-3"></i> Activity Details</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Common fields for all types -->
                            <div>
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Activity Type</label>
                                <p class="text-lg font-semibold text-[#08324f]"><?php echo $report['subtype']; ?></p>
                            </div>
                            
                            <div>
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Date & Time</label>
                                <p class="text-lg font-semibold text-[#08324f]">
                                    <?php echo date('M d, Y', strtotime($report['activity_date'])); ?> at 
                                    <?php echo date('h:i A', strtotime($report['activity_time'])); ?>
                                </p>
                            </div>
                            
                            <div>
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Barangay</label>
                                <p class="text-lg font-semibold text-[#08324f]"><?php echo $report['barangay_name']; ?></p>
                            </div>
                            
                            <div>
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Specific Location</label>
                                <p class="text-lg font-semibold text-[#08324f]"><?php echo $report['specific_location']; ?></p>
                            </div>
                            
                            <!-- PATROL SPECIFIC FIELDS -->
                            <?php if ($display_type == 'patrol'): ?>
                            <div>
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Personnel Deployed</label>
                                <p class="text-lg font-semibold text-[#08324f]"><?php echo $report['personnel_count'] ?? '1'; ?></p>
                            </div>
                            
                            <?php if (!empty($report['vehicle_number'])): ?>
                            <div>
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Vehicle/Unit Number</label>
                                <p class="text-lg font-semibold text-[#08324f]"><?php echo $report['vehicle_number']; ?></p>
                            </div>
                            <?php endif; ?>
                            <?php endif; ?>
                            
                            <!-- CHECKPOINT SPECIFIC FIELDS -->
                            <?php if ($display_type == 'checkpoint'): ?>
                            <div>
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Border Control Ops</label>
                                <p class="text-lg font-semibold text-[#08324f]"><?php echo $report['border_control_ops'] ?? '0'; ?></p>
                            </div>
                            
                            <div>
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Mobile Checkpoint Ops</label>
                                <p class="text-lg font-semibold text-[#08324f]"><?php echo $report['mobile_checkpoint_ops'] ?? '0'; ?></p>
                            </div>
                            
                            <div>
                                <label class="text-xs text-gray-500 uppercase tracking-wider">TCT/OVR Accomplishments</label>
                                <p class="text-lg font-semibold text-[#08324f]"><?php echo $report['tct_ovr_accomplishment'] ?? '0'; ?></p>
                            </div>
                            
                            <div>
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Arrests Made</label>
                                <p class="text-lg font-semibold text-[#08324f]"><?php echo $report['arrested_accomplishment'] ?? '0'; ?></p>
                            </div>
                            
                            <div>
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Personnel Deployed</label>
                                <p class="text-lg font-semibold text-[#08324f]"><?php echo ($report['border_personnel'] + $report['mobile_personnel']) ?? '0'; ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <!-- OPLAN SPECIFIC FIELDS -->
                            <?php if ($display_type == 'oplan'): ?>
                            <div>
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Operations Count</label>
                                <p class="text-lg font-semibold text-[#08324f]"><?php echo $report['operations_count'] ?? '1'; ?></p>
                            </div>
                            
                            <div>
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Personnel Deployed</label>
                                <p class="text-lg font-semibold text-[#08324f]"><?php echo $report['personnel_count'] ?? '1'; ?></p>
                            </div>
                            
                            <?php if ($report['oplan_type'] == 'Oplan Bakal'): ?>
                            <div>
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Firearms Seized</label>
                                <p class="text-lg font-semibold text-[#08324f]"><?php echo $report['firearms_seized'] ?? '0'; ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($report['oplan_type'] == 'Oplan Sita'): ?>
                            <div>
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Contraband (kg)</label>
                                <p class="text-lg font-semibold text-[#08324f]"><?php echo number_format($report['contraband_kg'] ?? 0, 2); ?> kg</p>
                            </div>
                            <?php endif; ?>
                            
                            <div>
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Arrests Made</label>
                                <p class="text-lg font-semibold text-[#08324f]"><?php echo $report['arrests_made'] ?? '0'; ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <!-- GPS Coordinates (common for all) -->
                            <?php if ($report['latitude'] && $report['longitude']): ?>
                            <div class="md:col-span-2">
                                <label class="text-xs text-gray-500 uppercase tracking-wider">GPS Coordinates</label>
                                <p class="text-sm font-mono bg-gray-100 p-2 rounded">
                                    <?php echo number_format($report['latitude'], 6); ?>° N, 
                                    <?php echo number_format($report['longitude'], 6); ?>° E
                                    <?php if ($report['gps_accuracy']): ?>
                                    <span class="ml-4 text-gray-500">Accuracy: <?php echo $report['gps_accuracy']; ?> meters</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Accomplishment Description Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-[#08324f] text-white px-6 py-4">
                        <h3 class="font-semibold text-lg"><i class="fas fa-trophy text-yellow-400 mr-3"></i> Accomplishment Description</h3>
                    </div>
                    <div class="p-6">
                        <div class="bg-gray-50 p-4 rounded-lg text-gray-700 leading-relaxed">
                            <?php echo nl2br(htmlspecialchars($report['accomplishment_description'])); ?>
                        </div>
                    </div>
                </div>

                <!-- Admin Remarks Card - READ ONLY if approved -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-[#08324f] text-white px-6 py-4 flex justify-between items-center">
                        <h3 class="font-semibold text-lg"><i class="fas fa-comment text-yellow-400 mr-3"></i> Admin Remarks</h3>
                        <?php if ($is_approved): ?>
                        <span class="text-xs bg-gray-600 text-white px-2 py-1 rounded">
                            <i class="fas fa-lock mr-1"></i> READ ONLY
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="p-6">
                        <?php if ($is_approved || $is_rejected): ?>
                            <!-- READ ONLY MODE - Show remarks without form -->
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($report['admin_remarks'] ?? 'No remarks added.')); ?></p>
                            </div>
                            <div class="mt-4 text-sm text-gray-500 italic">
                                <i class="fas fa-info-circle mr-1"></i> 
                                <?php echo $is_approved ? 'This report has been approved and cannot be modified.' : 'This report has been rejected.'; ?>
                            </div>
                        <?php else: ?>
                            <!-- EDITABLE MODE - Only for pending reports -->
                            <form method="POST" class="space-y-4">
                                <textarea name="admin_remarks" rows="4" 
                                          class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2] focus:border-transparent"
                                          placeholder="Add your remarks here..."><?php echo htmlspecialchars($report['admin_remarks'] ?? ''); ?></textarea>
                                
                                <div class="flex gap-3">
                                    <button type="submit" name="status" value="approved" 
                                            class="flex-1 bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg transition font-semibold flex items-center justify-center gap-2">
                                        <i class="fas fa-check-circle"></i> Approve Report
                                    </button>
                                    <button type="submit" name="status" value="rejected" 
                                            class="flex-1 bg-red-600 hover:bg-red-700 text-white py-3 rounded-lg transition font-semibold flex items-center justify-center gap-2">
                                        <i class="fas fa-times-circle"></i> Reject Report
                                    </button>
                                    <button type="submit" name="status" value="pending" 
                                            class="flex-1 bg-yellow-600 hover:bg-yellow-700 text-white py-3 rounded-lg transition font-semibold flex items-center justify-center gap-2">
                                        <i class="fas fa-clock"></i> Keep Pending
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column - Map and Photos (1/3 width) -->
            <div class="space-y-6">
                <!-- Location Map Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-[#08324f] text-white px-6 py-4">
                        <h3 class="font-semibold"><i class="fas fa-map-marked-alt text-yellow-400 mr-2"></i> Report Location</h3>
                    </div>
                    <div class="p-4">
                        <div id="map" class="w-full rounded-lg border-2 border-gray-200"></div>
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
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-[#08324f] text-white px-6 py-4 flex justify-between items-center">
                        <h3 class="font-semibold"><i class="fas fa-images text-yellow-400 mr-2"></i> Photo Evidence</h3>
                        <span class="bg-yellow-400 text-[#08324f] px-3 py-1 rounded-full text-xs font-bold">
                            <?php echo $photos->num_rows; ?> Photos
                        </span>
                    </div>
                    <div class="p-4">
                        <?php if ($photos->num_rows > 0): ?>
                        <div class="photo-grid">
                            <?php while ($photo = $photos->fetch_assoc()): ?>
                            <a href="../<?php echo $photo['photo_path']; ?>" 
                               data-lightbox="report-photos" 
                               data-title="<?php echo htmlspecialchars($photo['photo_name']); ?>">
                                <img src="../<?php echo $photo['photo_path']; ?>" 
                                     class="photo-item w-full h-full object-cover" 
                                     alt="Activity Photo">
                            </a>
                            <?php endwhile; ?>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-camera-slash text-4xl mb-2"></i>
                            <p>No photos uploaded for this report</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Report Metadata Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-[#08324f] text-white px-6 py-4">
                        <h3 class="font-semibold"><i class="fas fa-info-circle text-yellow-400 mr-2"></i> Submission Details</h3>
                    </div>
                    <div class="p-4">
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-600">Report ID:</span>
                                <span class="font-mono font-medium"><?php echo strtoupper($type) . '-' . str_pad($id, 5, '0', STR_PAD_LEFT); ?></span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-600">Submitted:</span>
                                <span><?php echo date('M d, Y h:i A', strtotime($report['submitted_at'])); ?></span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-600">Last Updated:</span>
                                <span><?php echo date('M d, Y h:i A', strtotime($report['updated_at'])); ?></span>
                            </div>
                            <?php if ($is_approved): ?>
                            <div class="flex justify-between py-2 border-b border-gray-100 text-green-600">
                                <span class="text-gray-600">Status:</span>
                                <span class="font-semibold"><i class="fas fa-check-circle mr-1"></i> APPROVED - LOCKED</span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
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

        // Initialize Map
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($report['latitude'] && $report['longitude']): ?>
            const lat = <?php echo $report['latitude']; ?>;
            const lng = <?php echo $report['longitude']; ?>;
            
            const map = L.map('map').setView([lat, lng], 17);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);
            
            const marker = L.marker([lat, lng]).addTo(map);
            marker.bindPopup(`
                <b><?php echo addslashes($report['subtype']); ?></b><br>
                <?php echo addslashes($report['barangay_name']); ?><br>
                <?php echo date('M d, Y', strtotime($report['activity_date'])); ?>
            `).openPopup();
            
            <?php if ($report['gps_accuracy']): ?>
            L.circle([lat, lng], {
                radius: <?php echo $report['gps_accuracy']; ?>,
                color: '#1f6fb2',
                fillColor: '#1f6fb2',
                fillOpacity: 0.1
            }).addTo(map);
            <?php endif; ?>
            
            <?php else: ?>
            document.getElementById('map').innerHTML = '<div class="flex items-center justify-center h-full bg-gray-100 rounded-lg"><p class="text-gray-500">No location data available</p></div>';
            <?php endif; ?>
        });

        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true,
            'albumLabel': 'Photo %1 of %2'
        });
    </script>
</body>
</html>
<?php 
if (isset($stmt)) $stmt->close();
if (isset($photo_stmt)) $photo_stmt->close();
$conn->close(); 
?>