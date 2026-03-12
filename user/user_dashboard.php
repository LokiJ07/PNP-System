<?php
// =====================================================
// FILE: user/user_dashboard.php
// PURPOSE: User dashboard with updated Oplan Sita/Bakal fields
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

// Get barangays
$barangays = $conn->query("SELECT barangay_id, barangay_name, latitude, longitude FROM barangays ORDER BY barangay_name");

// Get recent activities
$recent = [];

$patrols = $conn->query("
    SELECT 'patrol' as type, patrol_type as subtype, specific_location, 
           patrol_date as activity_date, patrol_time as activity_time, status,
           submitted_at
    FROM patrol_activities 
    WHERE user_id = $user_id
    ORDER BY submitted_at DESC
    LIMIT 3
");
while ($row = $patrols->fetch_assoc()) $recent[] = $row;

$checkpoints = $conn->query("
    SELECT 'checkpoint' as type, 'Checkpoint' as subtype, specific_location,
           checkpoint_date as activity_date, checkpoint_time as activity_time, status,
           submitted_at
    FROM checkpoint_activities 
    WHERE user_id = $user_id
    ORDER BY submitted_at DESC
    LIMIT 3
");
while ($row = $checkpoints->fetch_assoc()) $recent[] = $row;

$oplans = $conn->query("
    SELECT 'oplan' as type, oplan_type as subtype, specific_location,
           oplan_date as activity_date, oplan_time as activity_time, status,
           submitted_at
    FROM oplan_activities 
    WHERE user_id = $user_id
    ORDER BY submitted_at DESC
    LIMIT 3
");
while ($row = $oplans->fetch_assoc()) $recent[] = $row;

usort($recent, function($a, $b) {
    return strtotime($b['submitted_at']) - strtotime($a['submitted_at']);
});
$recent = array_slice($recent, 0, 5);

// Get user stats
$stats = [];

$result = $conn->query("SELECT COUNT(*) as total FROM patrol_activities WHERE user_id = $user_id");
$stats['patrols'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM checkpoint_activities WHERE user_id = $user_id");
$stats['checkpoints'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM oplan_activities WHERE user_id = $user_id");
$stats['oplans'] = $result->fetch_assoc()['total'];

$stats['total_patrols'] = $stats['patrols'];
$stats['total_checkpoints'] = $stats['checkpoints'];
$stats['total_oplans'] = $stats['oplans'];
$stats['pending'] = 0;

// Store barangays for JavaScript
$barangay_data = [];
while ($row = $barangays->fetch_assoc()) {
    $barangay_data[] = $row;
}
$barangays->data_seek(0);

// Set Philippine Time
date_default_timezone_set('Asia/Manila');
$current_date = date('Y-m-d');
$current_time = date('H:i');

// Format last login
if (!empty($user['last_login'])) {
    $last_login_timestamp = strtotime($user['last_login']);
    $last_login_formatted = date('F d, Y h:i A', $last_login_timestamp);
    
    $today_start = strtotime('today midnight');
    $yesterday_start = strtotime('yesterday midnight');
    
    if ($last_login_timestamp >= $today_start) {
        $last_login = 'Today, ' . date('h:i A', $last_login_timestamp);
    } elseif ($last_login_timestamp >= $yesterday_start) {
        $last_login = 'Yesterday, ' . date('h:i A', $last_login_timestamp);
    } else {
        $last_login = $last_login_formatted;
    }
} else {
    $last_login = 'First login';
}

// Profile picture
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
    <title>PNP | User Dashboard</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    
    <!-- Custom CSS -->
    <style>
        #map {
            height: 400px;
            width: 100%;
            border-radius: 12px;
            z-index: 1;
        }
        @media (min-width: 768px) {
            #map {
                height: 450px;
            }
        }
        .leaflet-container {
            font-family: Arial, sans-serif;
        }
        .location-marker {
            background: #1f6fb2;
            border: 3px solid white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        .user-location-marker {
            background: #22c55e;
            border: 3px solid white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }
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
        .section-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #08324f;
            margin-bottom: 0.75rem;
            padding-bottom: 0.25rem;
            border-bottom: 2px solid #ffc107;
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
            
            <div class="mt-4">
                <a href="settings.php" class="inline-block bg-[#1f6fb2] text-white text-sm px-4 py-2 rounded-lg hover:bg-[#0a3d62] transition w-full">
                    <i class="fas fa-cog mr-2"></i> Settings
                </a>
            </div>
        </div>

        <!-- Menu -->
        <ul class="space-y-2">
            <li class="p-3 rounded-lg bg-[#0a3d62] border-l-4 border-yellow-400 hover:bg-[#1f6fb2] transition">
                <a href="user_dashboard.php" class="text-white no-underline block text-sm md:text-base font-medium">
                    <i class="fas fa-tachometer-alt mr-3 w-5 text-yellow-400"></i> Dashboard
                </a>
            </li>
            <li class="p-3 rounded-lg hover:bg-[#1f6fb2] transition">
                <a href="my_reports.php" class="text-white no-underline block text-sm md:text-base font-medium">
                    <i class="fas fa-file-alt mr-3 w-5"></i> My Reports
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
    <div class="flex-1 p-3 md:p-6 lg:p-8 bg-[#eef2f6] overflow-y-auto min-h-screen main-content-mobile">
        
        <!-- Session Messages -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-lg animate-slideIn">
            <i class="fas fa-check-circle mr-2"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-lg animate-slideIn">
            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="bg-white p-3 md:p-4 rounded-lg shadow-sm mb-4 md:mb-6 flex flex-col sm:flex-row gap-3 sm:gap-0 justify-between items-start sm:items-center">
            <div class="ml-10 md:ml-0">
                <h2 class="text-xl md:text-2xl font-bold text-[#08324f]">User Dashboard</h2>
                <p class="text-xs md:text-sm text-gray-600 mt-1">Welcome back, <?php echo $user['first_name']; ?></p>
                <div class="flex items-center gap-2 mt-1">
                    <i class="far fa-clock text-xs text-gray-400"></i>
                    <p class="text-xs text-gray-500">Last login: <span class="font-medium text-[#1f6fb2]"><?php echo $last_login; ?></span></p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                <div class="bg-green-100 text-green-700 px-3 md:px-4 py-1.5 md:py-2 rounded-full text-xs md:text-sm font-semibold flex items-center">
                    <i class="fas fa-circle text-[6px] md:text-[8px] text-green-500 mr-1 md:mr-2"></i> GPS: Ready
                </div>
                <div class="bg-[#08324f] text-yellow-400 px-3 md:px-4 py-1.5 md:py-2 rounded-full text-xs md:text-sm font-semibold flex items-center">
                    <i class="fas fa-map-marker-alt mr-1 md:mr-2 text-xs"></i> On Duty
                </div>
            </div>
        </div>

        <!-- Map Section -->
        <div class="bg-white p-3 md:p-5 rounded-lg shadow-md mb-4 md:mb-6">
            <h3 class="text-base md:text-lg font-semibold text-[#08324f] mb-3 md:mb-4 flex items-center">
                <i class="fas fa-map-marked-alt mr-2 text-yellow-500 text-lg md:text-xl"></i> 
                <span class="text-sm md:text-base">Select Your Location</span>
            </h3>
            
            <div class="flex flex-col lg:flex-row gap-3 md:gap-4 mb-3 md:mb-4">
                <div class="w-full lg:w-1/3">
                    <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">Select Barangay</label>
                    <select id="barangaySelect" class="w-full p-2 md:p-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2]" onchange="zoomToBarangay(this)">
                        <option value="">-- Select Barangay --</option>
                        <?php while ($barangay = $barangays->fetch_assoc()): ?>
                        <option value="<?php echo $barangay['barangay_id']; ?>" 
                                data-name="<?php echo $barangay['barangay_name']; ?>"
                                data-lat="<?php echo $barangay['latitude']; ?>" 
                                data-lng="<?php echo $barangay['longitude']; ?>">
                            <?php echo $barangay['barangay_name']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="w-full lg:w-1/3">
                    <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">Map Layer</label>
                    <select id="mapLayerSelect" class="w-full p-2 md:p-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2]" onchange="changeMapLayer(this.value)">
                        <option value="street">Street Map</option>
                        <option value="satellite">Satellite</option>
                        <option value="terrain">Terrain</option>
                        <option value="hybrid">Satellite with Labels</option>
                    </select>
                </div>

                <div class="w-full lg:w-1/3 flex flex-wrap gap-2 items-end">
                    <button type="button" onclick="getUserLocation()" class="flex-1 bg-[#1f6fb2] text-white px-2 md:px-4 py-2 md:py-2.5 rounded-lg hover:bg-[#0a3d62] transition flex items-center justify-center gap-1 md:gap-2 text-xs md:text-sm">
                        <i class="fas fa-location-dot"></i> My Location
                    </button>
                    <button type="button" onclick="resetMapView()" class="flex-1 bg-yellow-500 text-white px-2 md:px-4 py-2 md:py-2.5 rounded-lg hover:bg-yellow-600 transition flex items-center justify-center gap-1 md:gap-2 text-xs md:text-sm">
                        <i class="fas fa-globe"></i> Reset
                    </button>
                </div>
            </div>

            <div id="map" class="w-full h-[300px] sm:h-[350px] md:h-[400px] lg:h-[450px] rounded-lg border-2 border-gray-200"></div>
            
            <div id="locationInfo" class="mt-3 p-2 md:p-3 bg-blue-50 rounded-lg hidden">
                <p class="text-xs md:text-sm text-gray-700"><i class="fas fa-map-pin text-[#1f6fb2] mr-2"></i><span id="locationText"></span></p>
                <p class="text-xs text-gray-500 mt-1" id="coordinatesText"></p>
                <p class="text-xs text-gray-500 mt-1" id="elevationText"></p>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            <div class="bg-white p-3 rounded-lg shadow-sm border-l-4 border-blue-500">
                <p class="text-xs text-gray-500">My Patrols</p>
                <p class="text-xl font-bold text-[#08324f]"><?php echo $stats['patrols']; ?></p>
            </div>
            <div class="bg-white p-3 rounded-lg shadow-sm border-l-4 border-red-500">
                <p class="text-xs text-gray-500">My Checkpoints</p>
                <p class="text-xl font-bold text-[#08324f]"><?php echo $stats['checkpoints']; ?></p>
            </div>
            <div class="bg-white p-3 rounded-lg shadow-sm border-l-4 border-green-500">
                <p class="text-xs text-gray-500">My Oplans</p>
                <p class="text-xl font-bold text-[#08324f]"><?php echo $stats['oplans']; ?></p>
            </div>
            <div class="bg-white p-3 rounded-lg shadow-sm border-l-4 border-yellow-500">
                <p class="text-xs text-gray-500">Auto-Approved</p>
                <p class="text-xl font-bold text-[#08324f]"><?php echo $stats['total_patrols'] + $stats['total_checkpoints'] + $stats['total_oplans']; ?></p>
            </div>
        </div>

 <!-- Activity Form -->
<div class="bg-white p-3 md:p-5 rounded-lg shadow-md">
    <h3 class="text-base md:text-lg font-semibold text-[#08324f] mb-3 md:mb-4">Report New Activity</h3>
    
    <form id="activityForm" method="POST" action="submit_activity.php" enctype="multipart/form-data">
        <input type="hidden" id="selectedLat" name="latitude">
        <input type="hidden" id="selectedLng" name="longitude">
        <input type="hidden" id="selectedBarangayId" name="barangay_id">
        <input type="hidden" id="gps_accuracy" name="gps_accuracy" value="">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Activity Type *</label>
                <select name="activity_type" id="activity_type" required class="w-full p-2.5 text-sm border border-gray-300 rounded-lg" onchange="toggleActivityFields(this.value)">
                    <option value="">Select Type</option>
                    <option value="Foot Patrol">Foot Patrol</option>
                    <option value="Mobile Patrol">Mobile Patrol</option>
                    <option value="Motorcycle Patrol">Motorcycle Patrol</option>
                    <option value="checkpoint">Checkpoint</option>
                    <option value="Oplan Bakal">Oplan Bakal</option>
                    <option value="Oplan Sita">Oplan Sita</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                <input type="text" id="specificLocation" name="specific_location" readonly 
                       class="w-full p-2.5 text-sm border border-gray-300 rounded-lg bg-gray-50" 
                       placeholder="Click on map to set location" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                <input type="date" name="activity_date" id="activity_date" required value="<?php echo $current_date; ?>" 
                       class="w-full p-2.5 text-sm border border-gray-300 rounded-lg">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Time *</label>
                <input type="time" name="activity_time" id="activity_time" required value="<?php echo $current_time; ?>" 
                       class="w-full p-2.5 text-sm border border-gray-300 rounded-lg">
            </div>
        </div>

        <!-- Personnel Field (for all except checkpoint) -->
        <div id="personnelField" class="mt-4 hidden">
            <label class="block text-sm font-medium text-gray-700 mb-1">Number of Personnel *</label>
            <input type="number" name="personnel_count" min="1" value="1" class="w-full p-2.5 text-sm border border-gray-300 rounded-lg">
        </div>

        <!-- Vehicle Field (for mobile patrols) -->
        <div id="vehicleField" class="mt-4 hidden">
            <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle/Unit Number</label>
            <input type="text" name="vehicle_number" placeholder="e.g., MCS-101" class="w-full p-2.5 text-sm border border-gray-300 rounded-lg">
        </div>

        <!-- CHECKPOINT FIELDS -->
        <div id="checkpointFields" class="hidden mt-4 p-4 bg-gray-50 rounded-lg">
            <h4 class="font-medium text-sm mb-3 text-[#08324f]">Checkpoint Details</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div><label class="block text-xs text-gray-600 mb-1">Border Control Ops</label><input type="number" name="border_control_ops" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                <div><label class="block text-xs text-gray-600 mb-1">Border Personnel</label><input type="number" name="border_personnel" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                <div><label class="block text-xs text-gray-600 mb-1">Overlapping Ops</label><input type="number" name="overlapping_ops" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                <div><label class="block text-xs text-gray-600 mb-1">Mobile Checkpoint Ops</label><input type="number" name="mobile_checkpoint_ops" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                <div><label class="block text-xs text-gray-600 mb-1">Mobile Personnel</label><input type="number" name="mobile_personnel" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                <div><label class="block text-xs text-gray-600 mb-1">TCT/OVR Accomplishment</label><input type="number" name="tct_ovr_accomplishment" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                <div><label class="block text-xs text-gray-600 mb-1">Arrests Made</label><input type="number" name="arrested_accomplishment" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
            </div>
            
            <!-- DISPOSITION SECTION -->
            <div class="mt-4">
                <h5 class="text-xs font-semibold text-gray-600 mb-2">DISPOSITION (Case Outcomes)</h5>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div><label class="block text-xs text-gray-600 mb-1">Fixed/Advised</label><input type="number" name="fixed_count" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                    <div><label class="block text-xs text-gray-600 mb-1">Fined</label><input type="number" name="fined_count" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                    <div><label class="block text-xs text-gray-600 mb-1">Warned/Released</label><input type="number" name="warned_count" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                    <div><label class="block text-xs text-gray-600 mb-1">Charged</label><input type="number" name="charged_count" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                    <div><label class="block text-xs text-gray-600 mb-1">Community Service</label><input type="number" name="community_service" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                    <div class="md:col-span-3"><label class="block text-xs text-gray-600 mb-1">Others (Specify)</label><input type="text" name="disposition_others" placeholder="e.g., Transferred" class="w-full p-2 text-sm border rounded"></div>
                </div>
            </div>
        </div>

        <!-- OPLAN BAKAL FIELDS -->
        <div id="oplanBakalFields" class="hidden mt-4 p-4 bg-gray-50 rounded-lg">
            <h4 class="font-medium text-sm mb-3 text-[#08324f]">Oplan Bakal Details (Firearms)</h4>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                <div><label class="block text-xs text-gray-600 mb-1">Firearms Seized</label><input type="number" name="firearms_seized" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                <div><label class="block text-xs text-gray-600 mb-1">Firearms (CRS)</label><input type="number" name="firearms_crs" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                <div><label class="block text-xs text-gray-600 mb-1">FAS Deposits</label><input type="number" name="fas_deposit" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                <div><label class="block text-xs text-gray-600 mb-1">Renewed FAS</label><input type="number" name="renewed_fas" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                <div><label class="block text-xs text-gray-600 mb-1">Arrests Made</label><input type="number" name="arrests_made" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                <div><label class="block text-xs text-gray-600 mb-1">House Visitations</label><input type="number" name="house_visitations" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
            </div>
        </div>

        <!-- OPLAN SITA FIELDS -->
        <div id="oplanSitaFields" class="hidden mt-4 p-4 bg-gray-50 rounded-lg">
            <h4 class="font-medium text-sm mb-3 text-[#08324f]">Oplan Sita Details</h4>
            
            <!-- ORDINANCE VIOLATIONS -->
            <div class="mb-4">
                <h5 class="text-xs font-semibold text-gray-600 mb-2">ORDINANCE VIOLATIONS</h5>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <div><label class="block text-xs text-gray-600 mb-1">Drinking</label><input type="number" name="drinking_violations" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                    <div><label class="block text-xs text-gray-600 mb-1">Smoking</label><input type="number" name="smoking_violations" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                    <div><label class="block text-xs text-gray-600 mb-1">Half-Naked</label><input type="number" name="halfnaked_violations" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                    <div><label class="block text-xs text-gray-600 mb-1">Curfew</label><input type="number" name="curfew_violations" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                    <div><label class="block text-xs text-gray-600 mb-1">Vandalism</label><input type="number" name="vandalism_violations" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                    <div><label class="block text-xs text-gray-600 mb-1">Other</label><input type="number" name="other_violations" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                </div>
                <input type="text" name="other_violations_desc" placeholder="Specify other violations" class="w-full p-2 mt-2 text-sm border rounded">
            </div>
            
            <!-- APPREHENSIONS -->
            <div class="mb-4">
                <h5 class="text-xs font-semibold text-gray-600 mb-2">APPREHENSIONS</h5>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-xs text-gray-600 mb-1">Arrests Made</label><input type="number" name="arrests_made" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                    <div><label class="block text-xs text-gray-600 mb-1">Contraband (kg)</label><input type="number" step="0.01" name="contraband_kg" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                    <div><label class="block text-xs text-gray-600 mb-1">Kontra Boga</label><input type="number" name="kontra_boga" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                    <div><label class="block text-xs text-gray-600 mb-1">Anti-Vaping</label><input type="number" name="anti_vaping" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                </div>
            </div>
            
            <!-- DISPOSITION (same as checkpoint) -->
            <div>
                <h5 class="text-xs font-semibold text-gray-600 mb-2">DISPOSITION</h5>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div><label class="block text-xs text-gray-600 mb-1">Fixed</label><input type="number" name="fixed_count" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                    <div><label class="block text-xs text-gray-600 mb-1">Fined</label><input type="number" name="fined_count" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                    <div><label class="block text-xs text-gray-600 mb-1">Warned</label><input type="number" name="warned_count" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                    <div><label class="block text-xs text-gray-600 mb-1">Charged</label><input type="number" name="charged_count" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                    <div><label class="block text-xs text-gray-600 mb-1">Community Service</label><input type="number" name="community_service" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                    <div class="md:col-span-3"><label class="block text-xs text-gray-600 mb-1">Others</label><input type="text" name="disposition_others" placeholder="e.g., Transferred" class="w-full p-2 text-sm border rounded"></div>
                </div>
            </div>
        </div>

        <!-- PATROL VIOLATION FIELDS -->
        <div id="patrolViolationFields" class="hidden mt-4 p-4 bg-gray-50 rounded-lg">
            <h4 class="font-medium text-sm mb-3 text-[#08324f]">Violations Encountered</h4>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                <div><label class="block text-xs text-gray-600 mb-1">Drinking</label><input type="number" name="drinking_violations" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                <div><label class="block text-xs text-gray-600 mb-1">Smoking</label><input type="number" name="smoking_violations" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                <div><label class="block text-xs text-gray-600 mb-1">Half-Naked</label><input type="number" name="halfnaked_violations" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                <div><label class="block text-xs text-gray-600 mb-1">Curfew</label><input type="number" name="curfew_violations" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                <div><label class="block text-xs text-gray-600 mb-1">Vandalism</label><input type="number" name="vandalism_violations" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
                <div><label class="block text-xs text-gray-600 mb-1">Other</label><input type="number" name="other_violations" value="0" min="0" class="w-full p-2 text-sm border rounded"></div>
            </div>
            <input type="text" name="other_violations_desc" placeholder="Specify other violations" class="w-full p-2 mt-2 text-sm border rounded">
        </div>

        <!-- Accomplishment Description -->
        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Accomplishment Description *</label>
            <textarea name="accomplishment_description" id="accomplishment_description" rows="4" required
                      class="w-full p-3 text-sm border border-gray-300 rounded-lg" 
                      placeholder="Describe in detail what you accomplished during this activity..."></textarea>
        </div>

        <!-- Photo Upload -->
        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Upload Photo Evidence (Max 5 photos, up to 15MB total)</label>
            <input type="file" id="photos" name="photos[]" multiple accept="image/*" 
                   class="w-full p-2 border border-gray-300 rounded-lg"
                   onchange="validatePhotoUpload(this)">
            <p class="text-xs text-gray-500 mt-1" id="photoUploadMessage"></p>
        </div>

        <!-- Submit Button -->
        <div class="mt-6">
            <button type="submit" class="w-full bg-[#1f6fb2] text-white py-3 rounded-lg hover:bg-[#0a3d62] transition font-semibold text-base">
                <i class="fas fa-paper-plane mr-2"></i> REVIEW & SUBMIT
            </button>
        </div>
    </form>
</div>

        <!-- Recent Activities -->
        <div class="mt-6">
            <h3 class="text-lg font-semibold text-[#08324f] mb-3">My Recent Reports</h3>
            <div class="space-y-3">
                <?php if (empty($recent)): ?>
                <p class="text-gray-500 text-center py-4">No reports yet. Submit your first activity above.</p>
                <?php else: ?>
                <?php foreach ($recent as $activity): ?>
                <div class="bg-white p-3 rounded-lg shadow-sm border-l-4 
                    <?php 
                    echo $activity['type'] == 'patrol' ? 'border-blue-500' : 
                        ($activity['type'] == 'checkpoint' ? 'border-red-500' : 'border-green-500'); 
                    ?>">
                    <div class="flex justify-between">
                        <span class="font-medium">
                            <?php 
                            if ($activity['type'] == 'patrol') echo $activity['subtype'];
                            elseif ($activity['type'] == 'checkpoint') echo 'Checkpoint Operation';
                            else echo $activity['subtype'];
                            ?>
                        </span>
                        <span class="text-xs text-green-600 font-medium">APPROVED</span>
                    </div>
                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($activity['specific_location']); ?></p>
                    <p class="text-xs text-gray-500 mt-1">
                        <?php echo date('M d, Y', strtotime($activity['activity_date'])) . ' at ' . date('h:i A', strtotime($activity['activity_time'])); ?>
                    </p>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4" onclick="closeModalOnOutsideClick(event)">
        <div class="bg-white rounded-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
            <!-- Modal Header -->
            <div class="bg-[#08324f] text-white p-4 rounded-t-xl flex justify-between items-center sticky top-0 z-10">
                <h3 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-check-circle text-yellow-400 mr-2"></i>
                    Confirm Activity Report
                </h3>
                <button type="button" onclick="closeModal()" class="text-white hover:text-gray-300">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6">
                <!-- Auto-Approved Notice -->
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                        <div>
                            <p class="text-sm font-medium text-green-800">This report will be automatically APPROVED</p>
                            <p class="text-xs text-green-600 mt-1">Please review all details below before confirming.</p>
                        </div>
                    </div>
                </div>
                
                <!-- ===== BASIC INFORMATION SECTION ===== -->
                <div class="mb-6">
                    <h4 class="font-semibold text-[#08324f] mb-3 pb-2 border-b border-gray-200 flex items-center">
                        <i class="fas fa-info-circle text-yellow-500 mr-2"></i> Basic Information
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-500 mb-1">Activity Type</p>
                            <p class="font-semibold text-gray-800" id="confirmActivityType">-</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-500 mb-1">Date & Time</p>
                            <p class="font-semibold text-gray-800" id="confirmDateTime">-</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg md:col-span-2">
                            <p class="text-xs text-gray-500 mb-1">Location</p>
                            <p class="font-semibold text-gray-800" id="confirmLocation">-</p>
                            <p class="text-xs text-gray-500 mt-1" id="confirmCoordinates">-</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg md:col-span-2">
                            <p class="text-xs text-gray-500 mb-1">Barangay</p>
                            <p class="font-semibold text-gray-800" id="confirmBarangay">-</p>
                        </div>
                    </div>
                </div>
                
                <!-- ===== PERSONNEL & VEHICLE SECTION ===== -->
                <div id="confirmPersonnelField" class="hidden mb-6">
                    <h4 class="font-semibold text-[#08324f] mb-3 pb-2 border-b border-gray-200 flex items-center">
                        <i class="fas fa-users text-yellow-500 mr-2"></i> Personnel & Vehicle
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-blue-50 p-3 rounded-lg">
                            <p class="text-xs text-blue-600 mb-1">Number of Personnel</p>
                            <p class="font-semibold text-gray-800" id="confirmPersonnel">-</p>
                        </div>
                        <div id="confirmVehicleField" class="hidden bg-blue-50 p-3 rounded-lg">
                            <p class="text-xs text-blue-600 mb-1">Vehicle/Unit Number</p>
                            <p class="font-semibold text-gray-800" id="confirmVehicle">-</p>
                        </div>
                    </div>
                </div>
                
                <!-- ===== CHECKPOINT DETAILS SECTION ===== -->
                <div id="confirmCheckpointFields" class="hidden mb-6">
                    <h4 class="font-semibold text-[#08324f] mb-3 pb-2 border-b border-gray-200 flex items-center">
                        <i class="fas fa-map-marker-alt text-yellow-500 mr-2"></i> Checkpoint Operations
                    </h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="bg-gray-50 p-2 rounded-lg">
                            <p class="text-xs text-gray-500">Border Control Ops</p>
                            <p class="font-semibold" id="confirmBorderOps">0</p>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-lg">
                            <p class="text-xs text-gray-500">Border Personnel</p>
                            <p class="font-semibold" id="confirmBorderPersonnel">0</p>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-lg">
                            <p class="text-xs text-gray-500">Overlapping Ops</p>
                            <p class="font-semibold" id="confirmOverlapping">0</p>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-lg">
                            <p class="text-xs text-gray-500">Mobile Ops</p>
                            <p class="font-semibold" id="confirmMobileOps">0</p>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-lg">
                            <p class="text-xs text-gray-500">Mobile Personnel</p>
                            <p class="font-semibold" id="confirmMobilePersonnel">0</p>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-lg">
                            <p class="text-xs text-gray-500">TCT/OVR Accomps</p>
                            <p class="font-semibold" id="confirmTct">0</p>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-lg">
                            <p class="text-xs text-gray-500">Arrests Made</p>
                            <p class="font-semibold" id="confirmArrests">0</p>
                        </div>
                    </div>
                </div>
                
                <!-- ===== ORDINANCE VIOLATIONS SECTION ===== -->
                <div id="confirmOrdinanceSection" class="hidden mb-6">
                    <h4 class="font-semibold text-[#08324f] mb-3 pb-2 border-b border-gray-200 flex items-center">
                        <i class="fas fa-gavel text-yellow-500 mr-2"></i> Ordinance Violations
                    </h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <div class="bg-gray-50 p-2 rounded-lg">
                            <p class="text-xs text-gray-500">Drinking in Public</p>
                            <p class="font-semibold" id="confirmDrinking">0</p>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-lg">
                            <p class="text-xs text-gray-500">Smoking Ban</p>
                            <p class="font-semibold" id="confirmSmoking">0</p>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-lg">
                            <p class="text-xs text-gray-500">Half-Naked</p>
                            <p class="font-semibold" id="confirmHalfNaked">0</p>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-lg">
                            <p class="text-xs text-gray-500">Curfew</p>
                            <p class="font-semibold" id="confirmCurfew">0</p>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-lg">
                            <p class="text-xs text-gray-500">Vandalism</p>
                            <p class="font-semibold" id="confirmVandalism">0</p>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-lg">
                            <p class="text-xs text-gray-500">Other Violations</p>
                            <p class="font-semibold" id="confirmOtherViolations">0</p>
                            <p class="text-xs text-gray-500" id="confirmOtherViolationsDesc"></p>
                        </div>
                    </div>
                    <div class="mt-2 text-right">
                        <span class="text-xs font-medium text-gray-600">Total Violations: </span>
                        <span class="font-bold text-[#08324f]" id="confirmOrdinanceTotal">0</span>
                    </div>
                </div>
                
                <!-- ===== OPLAN DETAILS SECTION ===== -->
                <div id="confirmOplanFields" class="hidden mb-6">
                    <h4 class="font-semibold text-[#08324f] mb-3 pb-2 border-b border-gray-200 flex items-center">
                        <i class="fas fa-shield-alt text-yellow-500 mr-2"></i> Oplan Details
                    </h4>
                    
                    <!-- Oplan Bakal Specific -->
                    <div id="confirmBakalSummary" class="hidden mb-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">Firearms and FAS:</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <div class="bg-gray-50 p-2 rounded-lg">
                                <p class="text-xs text-gray-500">Firearms Seized</p>
                                <p class="font-semibold" id="confirmFirearms">0</p>
                            </div>
                            <div class="bg-gray-50 p-2 rounded-lg">
                                <p class="text-xs text-gray-500">Firearms (CRS)</p>
                                <p class="font-semibold" id="confirmFirearmsCRS">0</p>
                            </div>
                            <div class="bg-gray-50 p-2 rounded-lg">
                                <p class="text-xs text-gray-500">FAS Deposits</p>
                                <p class="font-semibold" id="confirmFasDeposit">0</p>
                            </div>
                            <div class="bg-gray-50 p-2 rounded-lg">
                                <p class="text-xs text-gray-500">Renewed FAS</p>
                                <p class="font-semibold" id="confirmRenewedFAS">0</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Oplan Sita Specific -->
                    <div id="confirmSitaSummary" class="hidden mb-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">Apprehensions:</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <div class="bg-gray-50 p-2 rounded-lg">
                                <p class="text-xs text-gray-500">Kontra Boga</p>
                                <p class="font-semibold" id="confirmKontraBoga">0</p>
                            </div>
                            <div class="bg-gray-50 p-2 rounded-lg">
                                <p class="text-xs text-gray-500">Anti-Vaping OPNs</p>
                                <p class="font-semibold" id="confirmAntiVaping">0</p>
                            </div>
                            <div class="bg-gray-50 p-2 rounded-lg">
                                <p class="text-xs text-gray-500">Contraband (kg)</p>
                                <p class="font-semibold" id="confirmContraband">0</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Common Oplan Fields -->
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <div class="bg-gray-50 p-2 rounded-lg">
                            <p class="text-xs text-gray-500">Arrests Made</p>
                            <p class="font-semibold" id="confirmOplanArrests">0</p>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-lg">
                            <p class="text-xs text-gray-500">House Visitations</p>
                            <p class="font-semibold" id="confirmHouseVisits">0</p>
                        </div>
                    </div>
                </div>
                
                <!-- ===== DISPOSITION SECTION ===== -->
                <div id="confirmDispositionSection" class="hidden mb-6">
                    <h4 class="font-semibold text-[#08324f] mb-3 pb-2 border-b border-gray-200 flex items-center">
                        <i class="fas fa-balance-scale text-yellow-500 mr-2"></i> Case Disposition
                    </h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="bg-gray-50 p-2 rounded-lg">
                            <p class="text-xs text-gray-500">Fixed/Advised</p>
                            <p class="font-semibold" id="confirmFixed">0</p>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-lg">
                            <p class="text-xs text-gray-500">Fined</p>
                            <p class="font-semibold" id="confirmFined">0</p>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-lg">
                            <p class="text-xs text-gray-500">Warned/Released</p>
                            <p class="font-semibold" id="confirmWarned">0</p>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-lg">
                            <p class="text-xs text-gray-500">Charged</p>
                            <p class="font-semibold" id="confirmCharged">0</p>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-lg">
                            <p class="text-xs text-gray-500">Community Service</p>
                            <p class="font-semibold" id="confirmCommunityService">0</p>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-lg md:col-span-3">
                            <p class="text-xs text-gray-500">Other Disposition</p>
                            <p class="font-semibold" id="confirmDispositionOthers">None</p>
                        </div>
                    </div>
                </div>
                
                <!-- ===== ACCOMPLISHMENT DESCRIPTION ===== -->
                <div class="mb-6">
                    <h4 class="font-semibold text-[#08324f] mb-3 pb-2 border-b border-gray-200 flex items-center">
                        <i class="fas fa-file-alt text-yellow-500 mr-2"></i> Accomplishment Description
                    </h4>
                    <div class="p-4 bg-gray-50 rounded-lg text-sm whitespace-pre-wrap" id="confirmDescription">-</div>
                </div>
                
                <!-- ===== PHOTOS ===== -->
                <div class="mb-6">
                    <h4 class="font-semibold text-[#08324f] mb-3 pb-2 border-b border-gray-200 flex items-center">
                        <i class="fas fa-camera text-yellow-500 mr-2"></i> Photo Evidence
                    </h4>
                    <div class="p-4 bg-gray-50 rounded-lg text-sm" id="confirmPhotos">No photos uploaded</div>
                </div>
                
                <!-- ===== GPS ACCURACY ===== -->
                <div class="text-xs text-gray-500 text-right" id="confirmGps"></div>
            </div>
            
            <!-- Modal Footer -->
            <div class="border-t p-4 bg-gray-50 flex flex-col sm:flex-row gap-3 justify-end rounded-b-xl">
                <button type="button" onclick="closeModal()" class="px-6 py-2 border border-gray-300 bg-white rounded-lg hover:bg-gray-100 transition text-sm font-medium">
                    <i class="fas fa-times mr-2"></i>Cancel
                </button>
                <button type="button" onclick="submitConfirmedReport()" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>CONFIRM & SUBMIT
                </button>
            </div>
        </div>
    </div>

    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    
    <!-- Pass PHP data to JavaScript -->
    <script>
        const barangayData = <?php echo json_encode($barangay_data); ?>;
    </script>
    
    <!-- Custom JavaScript -->
    <script src="js/user_dashboard.js"></script>
    
    <!-- Initialize map with barangay data -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Convert barangayData to the format expected by initMap
            const coords = {};
            barangayData.forEach(b => {
                coords[b.barangay_id] = {
                    name: b.barangay_name,
                    lat: parseFloat(b.latitude),
                    lng: parseFloat(b.longitude)
                };
            });
            
            initMap(coords);
            setPhilippineDateTime();
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>