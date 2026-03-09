<?php
// =====================================================
// FILE: admin/view_report.php
// PURPOSE: Display complete activity report details
// =====================================================

require_once '../config/db_connect.php';
requireAdmin();

$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? 0;

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
                   b.barangay_name, b.latitude as barangay_lat, b.longitude as barangay_lng
            FROM patrol_activities p
            JOIN users u ON p.user_id = u.user_id
            JOIN barangays b ON p.barangay_id = b.barangay_id
            WHERE p.patrol_id = ?
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $report = $stmt->get_result()->fetch_assoc();
        $report['activity_type_display'] = $report['patrol_type'];
        $report['date'] = $report['patrol_date'];
        $report['time'] = $report['patrol_time'];
        $report['activity_table'] = 'patrol_activities';
        $report['activity_id_field'] = 'patrol_id';
        break;

    case 'checkpoint':
        $query = "
            SELECT c.*, 
                   CONCAT(u.rank, ' ', u.first_name, ' ', u.last_name) as officer_name,
                   u.badge_number, u.email, u.contact_number,
                   b.barangay_name, b.latitude as barangay_lat, b.longitude as barangay_lng
            FROM checkpoint_activities c
            JOIN users u ON c.user_id = u.user_id
            JOIN barangays b ON c.barangay_id = b.barangay_id
            WHERE c.checkpoint_id = ?
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $report = $stmt->get_result()->fetch_assoc();
        $report['activity_type_display'] = 'Checkpoint Operation';
        $report['date'] = $report['checkpoint_date'];
        $report['time'] = $report['checkpoint_time'];
        $report['activity_table'] = 'checkpoint_activities';
        $report['activity_id_field'] = 'checkpoint_id';
        break;

    case 'oplan':
    case 'oplanbakal':
    case 'oplansita':
        $query = "
            SELECT o.*, 
                   CONCAT(u.rank, ' ', u.first_name, ' ', u.last_name) as officer_name,
                   u.badge_number, u.email, u.contact_number,
                   b.barangay_name, b.latitude as barangay_lat, b.longitude as barangay_lng
            FROM oplan_activities o
            JOIN users u ON o.user_id = u.user_id
            JOIN barangays b ON o.barangay_id = b.barangay_id
            WHERE o.oplan_id = ?
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $report = $stmt->get_result()->fetch_assoc();
        $report['activity_type_display'] = $report['oplan_type'];
        $report['date'] = $report['oplan_date'];
        $report['time'] = $report['oplan_time'];
        $report['activity_table'] = 'oplan_activities';
        $report['activity_id_field'] = 'oplan_id';
        break;
}

if (!$report) {
    $_SESSION['error'] = 'Report not found';
    header('Location: admin_dashboard.php');
    exit();
}

// Get photos
$photo_stmt = $conn->prepare("SELECT * FROM activity_photos WHERE activity_type = ? AND activity_id = ?");
$photo_stmt->bind_param("si", $type, $id);
$photo_stmt->execute();
$photos = $photo_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../image/pnplogo.png">
    <title>PNP | View Report</title>
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
        #map { height: 250px; width: 100%; border-radius: 12px; z-index: 1; }
        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 0.75rem;
        }
        .photo-item {
            aspect-ratio: 1/1;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .photo-item:hover { transform: scale(1.05); }
    </style>
</head>
<body class="flex flex-col md:flex-row bg-[#0a3d62] min-h-screen">

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
            <h2 class="text-lg md:text-xl font-semibold">PNP Admin</h2>
        </div>

        <!-- Admin Info -->
        <div class="bg-[#1e4a6a] p-3 rounded-lg mb-4 text-center">
            <p class="text-sm text-yellow-400 font-medium"><?php echo $_SESSION['full_name']; ?></p>
            <p class="text-xs text-gray-300 mt-1"><?php echo $_SESSION['email']; ?></p>
        </div>

        <ul class="space-y-1">
            <li class="p-2 md:p-3 rounded hover:bg-[#0a3d62] transition">
                <a href="admin_dashboard.php" class="text-white no-underline block text-sm md:text-base"><i class="fas fa-tachometer-alt mr-3 w-5"></i> Dashboard</a>
            </li>
            <li class="p-2 md:p-3 rounded hover:bg-[#0a3d62] transition">
                <a href="checkpoint.php" class="text-white no-underline block text-sm md:text-base"><i class="fas fa-map-marker-alt mr-3 w-5"></i> Checkpoint</a>
            </li>
            <li class="dropdown">
                <div class="p-2 md:p-3 rounded hover:bg-[#0a3d62] cursor-pointer flex items-center justify-between" onclick="toggleDropdown(this)">
                    <span class="text-sm md:text-base"><i class="fas fa-walking mr-3 w-5"></i> Patrol</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300"></i>
                </div>
                <ul class="pl-8 md:pl-10 mt-1 space-y-1 dropdown-content">
                    <li class="py-1 md:py-2 px-2 md:px-3 text-xs md:text-sm hover:bg-[#0a3d62] rounded"><a href="footpatrol.php" class="text-white no-underline block">Foot Patrol</a></li>
                    <li class="py-1 md:py-2 px-2 md:px-3 text-xs md:text-sm hover:bg-[#0a3d62] rounded"><a href="mobilepatrol.php" class="text-white no-underline block">Mobile Patrol</a></li>
                    <li class="py-1 md:py-2 px-2 md:px-3 text-xs md:text-sm hover:bg-[#0a3d62] rounded"><a href="motorpatrol.php" class="text-white no-underline block">Motorcycle Patrol</a></li>
                </ul>
            </li>
            <li class="dropdown">
                <div class="p-2 md:p-3 rounded hover:bg-[#0a3d62] cursor-pointer flex items-center justify-between" onclick="toggleDropdown(this)">
                    <span class="text-sm md:text-base"><i class="fas fa-shield-alt mr-3 w-5"></i> Oplan Bakal / Sita</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300"></i>
                </div>
                <ul class="pl-8 md:pl-10 mt-1 space-y-1 dropdown-content">
                    <li class="py-1 md:py-2 px-2 md:px-3 text-xs md:text-sm hover:bg-[#0a3d62] rounded"><a href="oplanbakal.php" class="text-white no-underline block">Oplan Bakal</a></li>
                    <li class="py-1 md:py-2 px-2 md:px-3 text-xs md:text-sm hover:bg-[#0a3d62] rounded"><a href="oplansita.php" class="text-white no-underline block">Oplan Sita</a></li>
                </ul>
            </li>
            <li class="p-2 md:p-3 rounded bg-[#0a3d62] border-l-4 border-yellow-400">
                <a href="admin_users.php" class="text-white no-underline block text-sm md:text-base"><i class="fas fa-users mr-3 w-5"></i> Users</a>
            </li>
            <li class="p-2 md:p-3 rounded hover:bg-[#0a3d62] transition mt-5 pt-4 border-t border-[#1a4b6d]">
                <a href="../logout.php" class="text-white no-underline block text-sm md:text-base"><i class="fas fa-sign-out-alt mr-3 w-5"></i> Logout</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-3 md:p-6 lg:p-8 bg-[#eef2f6] overflow-y-auto min-h-screen">
        
        <!-- Header with Back Button -->
        <div class="bg-white p-3 md:p-4 rounded-lg shadow-sm mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="javascript:history.back()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i>
                    <span class="hidden sm:inline">Back</span>
                </a>
                <h2 class="text-xl md:text-2xl font-bold text-[#08324f]">Activity Report</h2>
            </div>
            <span class="bg-yellow-100 text-yellow-800 px-3 py-1.5 rounded-full text-xs font-semibold">
                <i class="fas fa-file-alt mr-1"></i> Report #: <?php echo $type . '-' . str_pad($id, 5, '0', STR_PAD_LEFT); ?>
            </span>
        </div>

        <!-- Report Details - Card Based Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
            
            <!-- Left Column - Main Report Details -->
            <div class="lg:col-span-2 space-y-4">
                <!-- Submitted By Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-[#08324f] text-white px-4 py-3">
                        <h3 class="font-semibold"><i class="fas fa-user-shield text-yellow-400 mr-2"></i> Submitted By</h3>
                    </div>
                    <div class="p-4">
                        <div class="flex items-center gap-4">
                            <div class="bg-[#1f6fb2] w-16 h-16 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                                <?php echo substr($report['first_name'] ?? $report['officer_name'], 0, 1) . substr($report['last_name'] ?? '', 0, 1); ?>
                            </div>
                            <div>
                                <h4 class="font-bold text-lg"><?php echo $report['officer_name']; ?></h4>
                                <p class="text-sm text-gray-600">Badge: <?php echo $report['badge_number']; ?></p>
                                <p class="text-sm text-gray-500 mt-1"><i class="fas fa-map-marker-alt mr-1 text-red-400"></i> <?php echo $report['station'] ?? 'Manolo Fortich MPS'; ?></p>
                            </div>
                            <div class="ml-auto">
                                <span class="px-3 py-1.5 rounded-full text-xs font-semibold
                                    <?php 
                                    echo $report['status'] == 'approved' ? 'bg-green-100 text-green-700' : 
                                        ($report['status'] == 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700'); 
                                    ?>">
                                    <?php echo ucfirst($report['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Details Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-[#08324f] text-white px-4 py-3">
                        <h3 class="font-semibold"><i class="fas fa-clipboard-list text-yellow-400 mr-2"></i> Activity Details</h3>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Activity Type -->
                            <div class="bg-gray-50 p-3 rounded-lg border-l-4 border-blue-500">
                                <p class="text-xs text-gray-500">Activity Type</p>
                                <p class="font-semibold text-lg"><?php echo $report['activity_type_display']; ?></p>
                            </div>
                            
                            <!-- Date & Time -->
                            <div class="bg-gray-50 p-3 rounded-lg border-l-4 border-green-500">
                                <p class="text-xs text-gray-500">Date & Time</p>
                                <p class="font-semibold"><?php echo date('M d, Y', strtotime($report['date'])) . ' - ' . date('h:i A', strtotime($report['time'])); ?></p>
                            </div>
                            
                            <!-- Barangay -->
                            <div class="bg-gray-50 p-3 rounded-lg border-l-4 border-purple-500">
                                <p class="text-xs text-gray-500">Barangay</p>
                                <p class="font-semibold"><?php echo $report['barangay_name']; ?></p>
                            </div>
                            
                            <!-- Specific Location -->
                            <div class="bg-gray-50 p-3 rounded-lg border-l-4 border-yellow-500">
                                <p class="text-xs text-gray-500">Specific Location</p>
                                <p class="font-semibold"><?php echo $report['specific_location']; ?></p>
                            </div>
                            
                            <?php if ($type == 'patrol' || strpos($type, 'patrol') !== false): ?>
                            <!-- Personnel Deployed -->
                            <div class="bg-gray-50 p-3 rounded-lg border-l-4 border-red-500">
                                <p class="text-xs text-gray-500">Personnel Deployed</p>
                                <p class="font-semibold"><?php echo $report['personnel_count'] ?? 1; ?> personnel</p>
                            </div>
                            
                            <!-- Vehicle Number -->
                            <?php if (!empty($report['vehicle_number'])): ?>
                            <div class="bg-gray-50 p-3 rounded-lg border-l-4 border-indigo-500">
                                <p class="text-xs text-gray-500">Vehicle/Unit</p>
                                <p class="font-semibold"><?php echo $report['vehicle_number']; ?></p>
                            </div>
                            <?php endif; ?>
                            <?php endif; ?>

                            <?php if ($type == 'checkpoint'): ?>
                            <!-- Border Control Ops -->
                            <div class="bg-gray-50 p-3 rounded-lg border-l-4 border-red-500">
                                <p class="text-xs text-gray-500">Border Control Ops</p>
                                <p class="font-semibold"><?php echo $report['border_control_ops'] ?? 0; ?></p>
                            </div>
                            
                            <!-- Mobile Checkpoint Ops -->
                            <div class="bg-gray-50 p-3 rounded-lg border-l-4 border-orange-500">
                                <p class="text-xs text-gray-500">Mobile Checkpoint Ops</p>
                                <p class="font-semibold"><?php echo $report['mobile_checkpoint_ops'] ?? 0; ?></p>
                            </div>
                            
                            <!-- TCT/OVR -->
                            <div class="bg-gray-50 p-3 rounded-lg border-l-4 border-purple-500">
                                <p class="text-xs text-gray-500">TCT/OVR</p>
                                <p class="font-semibold"><?php echo $report['tct_ovr_accomplishment'] ?? 0; ?></p>
                            </div>
                            
                            <!-- Arrests -->
                            <div class="bg-gray-50 p-3 rounded-lg border-l-4 border-green-500">
                                <p class="text-xs text-gray-500">Arrests Made</p>
                                <p class="font-semibold"><?php echo $report['arrested_accomplishment'] ?? 0; ?></p>
                            </div>
                            <?php endif; ?>

                            <?php if ($type == 'oplan' || strpos($type, 'oplan') !== false): ?>
                            <!-- Operations Count -->
                            <div class="bg-gray-50 p-3 rounded-lg border-l-4 border-red-500">
                                <p class="text-xs text-gray-500">Operations Count</p>
                                <p class="font-semibold"><?php echo $report['operations_count'] ?? 1; ?></p>
                            </div>
                            
                            <!-- Personnel -->
                            <div class="bg-gray-50 p-3 rounded-lg border-l-4 border-orange-500">
                                <p class="text-xs text-gray-500">Personnel Deployed</p>
                                <p class="font-semibold"><?php echo $report['personnel_count'] ?? 1; ?></p>
                            </div>
                            
                            <!-- Firearms (Bakal) -->
                            <?php if ($report['oplan_type'] == 'Oplan Bakal' && !empty($report['firearms_seized'])): ?>
                            <div class="bg-gray-50 p-3 rounded-lg border-l-4 border-purple-500">
                                <p class="text-xs text-gray-500">Firearms Seized</p>
                                <p class="font-semibold"><?php echo $report['firearms_seized']; ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Contraband (Sita) -->
                            <?php if ($report['oplan_type'] == 'Oplan Sita' && !empty($report['contraband_kg'])): ?>
                            <div class="bg-gray-50 p-3 rounded-lg border-l-4 border-green-500">
                                <p class="text-xs text-gray-500">Contraband Seized</p>
                                <p class="font-semibold"><?php echo $report['contraband_kg']; ?> kg</p>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Arrests -->
                            <div class="bg-gray-50 p-3 rounded-lg border-l-4 border-yellow-500">
                                <p class="text-xs text-gray-500">Arrests Made</p>
                                <p class="font-semibold"><?php echo $report['arrests_made'] ?? 0; ?></p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- GPS Coordinates -->
                        <?php if ($report['latitude'] && $report['longitude']): ?>
                        <div class="mt-4 bg-gray-50 p-3 rounded-lg border-l-4 border-gray-700">
                            <p class="text-xs text-gray-500">GPS Coordinates</p>
                            <p class="font-mono text-sm"><i class="fas fa-satellite text-gray-700 mr-2"></i>
                                <?php echo number_format($report['latitude'], 6); ?>° N, <?php echo number_format($report['longitude'], 6); ?>° E
                                <?php if ($report['gps_accuracy']): ?> (Accuracy: <?php echo $report['gps_accuracy']; ?>m)<?php endif; ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Accomplishment Description Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-[#08324f] text-white px-4 py-3">
                        <h3 class="font-semibold"><i class="fas fa-trophy text-yellow-400 mr-2"></i> Accomplishment Description</h3>
                    </div>
                    <div class="p-4">
                        <div class="bg-blue-50 p-4 rounded-lg text-gray-700 border border-blue-100">
                            <?php echo nl2br(htmlspecialchars($report['accomplishment_description'])); ?>
                        </div>
                    </div>
                </div>

                <!-- Admin Remarks Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-[#08324f] text-white px-4 py-3">
                        <h3 class="font-semibold"><i class="fas fa-comment text-yellow-400 mr-2"></i> Admin Remarks</h3>
                    </div>
                    <div class="p-4">
                        <form action="update_report_status.php" method="POST">
                            <input type="hidden" name="type" value="<?php echo $type; ?>">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <textarea name="admin_remarks" class="w-full p-3 border border-gray-300 rounded-lg text-sm" rows="3"><?php echo $report['admin_remarks'] ?? ''; ?></textarea>
                            <div class="flex flex-wrap gap-2 mt-3 justify-end">
                                <button type="submit" name="status" value="approved" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700">
                                    <i class="fas fa-check mr-2"></i> Approve
                                </button>
                                <button type="submit" name="status" value="rejected" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700">
                                    <i class="fas fa-times mr-2"></i> Reject
                                </button>
                                <button type="submit" name="status" value="pending" class="bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-yellow-700">
                                    <i class="fas fa-clock mr-2"></i> Mark Pending
                                </button>
                                <button type="submit" name="save_remarks" value="1" class="bg-[#1f6fb2] text-white px-4 py-2 rounded-lg text-sm hover:bg-[#0a3d62]">
                                    <i class="fas fa-save mr-2"></i> Save Remarks Only
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column - Map and Photos -->
            <div class="space-y-4">
                <!-- Location Map Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-[#08324f] text-white px-4 py-3">
                        <h3 class="font-semibold"><i class="fas fa-map-marked-alt text-yellow-400 mr-2"></i> Report Location</h3>
                    </div>
                    <div class="p-4">
                        <div id="map" class="rounded-lg border-2 border-gray-200"></div>
                        <?php if ($report['latitude'] && $report['longitude']): ?>
                        <a href="https://www.google.com/maps?q=<?php echo $report['latitude']; ?>,<?php echo $report['longitude']; ?>" target="_blank" class="mt-3 block text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm transition">
                            <i class="fas fa-external-link-alt mr-2"></i> Open in Google Maps
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Photo Evidence Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-[#08324f] text-white px-4 py-3 flex items-center justify-between">
                        <h3 class="font-semibold"><i class="fas fa-images text-yellow-400 mr-2"></i> Photo Evidence</h3>
                        <span class="bg-yellow-400 text-[#08324f] px-2 py-1 rounded-full text-xs font-bold"><?php echo $photos->num_rows; ?> Photos</span>
                    </div>
                    <div class="p-4">
                        <?php if ($photos->num_rows > 0): ?>
                        <div class="photo-grid">
                            <?php while ($photo = $photos->fetch_assoc()): ?>
                            <a href="../<?php echo $photo['photo_path']; ?>" data-lightbox="report-photos" data-title="<?php echo $photo['photo_name']; ?>">
                                <img src="../<?php echo $photo['photo_path']; ?>" class="photo-item w-full h-full object-cover" alt="Activity Photo">
                            </a>
                            <?php endwhile; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-gray-500 text-center py-4">No photos uploaded</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Report Metadata Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-[#08324f] text-white px-4 py-3">
                        <h3 class="font-semibold"><i class="fas fa-info-circle text-yellow-400 mr-2"></i> Submission Details</h3>
                    </div>
                    <div class="p-4">
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-500">Report ID:</span>
                                <span class="font-mono font-medium"><?php echo $type . '-' . str_pad($id, 5, '0', STR_PAD_LEFT); ?></span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-500">Submitted:</span>
                                <span><?php echo date('Y-m-d h:i A', strtotime($report['submitted_at'])); ?></span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-500">Last Updated:</span>
                                <span><?php echo date('Y-m-d h:i A', strtotime($report['updated_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mobile Menu
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

        // Dropdown
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

        // Map
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($report['latitude'] && $report['longitude']): ?>
            const lat = <?php echo $report['latitude']; ?>;
            const lng = <?php echo $report['longitude']; ?>;
            
            const map = L.map('map').setView([lat, lng], 17);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
            
            const marker = L.marker([lat, lng]).addTo(map);
            marker.bindPopup(`
                <b><?php echo $report['activity_type_display']; ?></b><br>
                <?php echo $report['barangay_name']; ?><br>
                <?php echo date('M d, Y', strtotime($report['date'])); ?>
            `).openPopup();
            
            <?php if ($report['gps_accuracy']): ?>
            L.circle([lat, lng], { radius: <?php echo $report['gps_accuracy']; ?>, color: '#1f6fb2', fillOpacity: 0.1 }).addTo(map);
            <?php endif; ?>
            <?php else: ?>
            document.getElementById('map').innerHTML = '<div class="flex items-center justify-center h-full bg-gray-100 rounded-lg"><p class="text-gray-500">No location data available</p></div>';
            <?php endif; ?>
        });

        // Lightbox
        lightbox.option({ 'resizeDuration': 200, 'wrapAround': true });
    </script>
</body>
</html>
<?php 
if (isset($stmt)) $stmt->close();
if (isset($photo_stmt)) $photo_stmt->close();
$conn->close(); 
?>