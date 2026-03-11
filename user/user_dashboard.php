<?php
// =====================================================
// FILE: user/user_dashboard.php
// PURPOSE: User dashboard focused on activity reporting
// =====================================================
session_start();
require_once '../config/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user information from database
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    session_destroy();
    header('Location: ../index.php');
    exit();
}

// Get barangays for dropdown
$barangays = $conn->query("SELECT barangay_id, barangay_name, latitude, longitude FROM barangays ORDER BY barangay_name");

// Get user's recent activities
$recent = [];

// Recent patrols
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

// Recent checkpoints
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

// Recent oplans
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

// Sort by most recent
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

$result = $conn->query("
    SELECT (
        SELECT COUNT(*) FROM patrol_activities WHERE user_id = $user_id AND status = 'pending'
    ) + (
        SELECT COUNT(*) FROM checkpoint_activities WHERE user_id = $user_id AND status = 'pending'
    ) + (
        SELECT COUNT(*) FROM oplan_activities WHERE user_id = $user_id AND status = 'pending'
    ) as pending
");
$stats['pending'] = $result->fetch_assoc()['pending'] ?? 0;

// Store barangays for JavaScript
$barangay_data = [];
while ($row = $barangays->fetch_assoc()) {
    $barangay_data[] = $row;
}
$barangays->data_seek(0); // Reset pointer

// Set Philippine Time (UTC+8)
date_default_timezone_set('Asia/Manila');
$current_date = date('Y-m-d');
$current_time = date('H:i');

// Format dates
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
    <title>PNP | User Dashboard</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Additional Leaflet plugins for better map controls -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
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
        /* Map layer control styling */
        .map-layer-control {
            background: white;
            padding: 8px;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            margin: 10px;
        }
        .map-layer-control label {
            display: block;
            margin: 5px 0;
            font-size: 12px;
            cursor: pointer;
        }
        .map-layer-control input {
            margin-right: 5px;
        }
        .map-scale-control {
            background: white;
            padding: 4px 8px;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            margin: 10px;
            font-size: 11px;
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
            
            <!-- Settings Button -->
            <div class="mt-4">
                <a href="settings.php" class="inline-block bg-[#1f6fb2] text-white text-sm px-4 py-2 rounded-lg hover:bg-[#0a3d62] transition w-full">
                    <i class="fas fa-cog mr-2"></i> Settings
                </a>
            </div>
        </div>

        <!-- Simple Menu -->
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
        
        <!-- Display Session Messages -->
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

        <!-- Header with User Info -->
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

        <!-- Map Section with Enhanced Controls -->
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
                <p class="text-xs text-gray-500">Pending</p>
                <p class="text-xl font-bold text-[#08324f]"><?php echo $stats['pending']; ?></p>
            </div>
        </div>

        <!-- Activity Form -->
        <div class="bg-white p-3 md:p-5 rounded-lg shadow-md">
            <h3 class="text-base md:text-lg font-semibold text-[#08324f] mb-3 md:mb-4">Report New Activity</h3>
            
            <form id="activityForm" action="submit_activity.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="selectedLat" name="latitude">
                <input type="hidden" id="selectedLng" name="longitude">
                <input type="hidden" id="selectedBarangayId" name="barangay_id">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <input type="hidden" name="gps_accuracy" id="gps_accuracy" value="">

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

                    <!-- Personnel Field -->
                    <div id="personnelField" class="hidden md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Number of Personnel *</label>
                        <input type="number" name="personnel_count" min="1" value="1"
                               class="w-full p-2.5 text-sm border border-gray-300 rounded-lg">
                    </div>

                    <!-- Vehicle Field -->
                    <div id="vehicleField" class="hidden md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle/Unit Number</label>
                        <input type="text" name="vehicle_number" placeholder="e.g., MCS-101" 
                               class="w-full p-2.5 text-sm border border-gray-300 rounded-lg">
                    </div>
                </div>

                <!-- Checkpoint Fields -->
                <div id="checkpointFields" class="hidden mt-4 p-4 bg-gray-50 rounded-lg">
                    <h4 class="font-medium text-sm mb-3 text-[#08324f]">Checkpoint Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Border Control Ops</label>
                            <input type="number" name="border_control_ops" value="0" min="0" class="w-full p-2 text-sm border rounded">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Border Personnel</label>
                            <input type="number" name="border_personnel" value="0" min="0" class="w-full p-2 text-sm border rounded">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Overlapping Ops</label>
                            <input type="number" name="overlapping_ops" value="0" min="0" class="w-full p-2 text-sm border rounded">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Mobile Checkpoint Ops</label>
                            <input type="number" name="mobile_checkpoint_ops" value="0" min="0" class="w-full p-2 text-sm border rounded">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Mobile Personnel</label>
                            <input type="number" name="mobile_personnel" value="0" min="0" class="w-full p-2 text-sm border rounded">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">TCT/OVR Accomplishment</label>
                            <input type="number" name="tct_ovr_accomplishment" value="0" min="0" class="w-full p-2 text-sm border rounded">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Arrests Made</label>
                            <input type="number" name="arrested_accomplishment" value="0" min="0" class="w-full p-2 text-sm border rounded">
                        </div>
                    </div>
                </div>

                <!-- Oplan Fields -->
                <div id="oplanFields" class="hidden mt-4 p-4 bg-gray-50 rounded-lg">
                    <h4 class="font-medium text-sm mb-3 text-[#08324f]">Oplan Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Operations Count</label>
                            <input type="number" name="operations_count" value="1" min="1" class="w-full p-2 text-sm border rounded">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Arrests Made</label>
                            <input type="number" name="arrests_made" value="0" min="0" class="w-full p-2 text-sm border rounded">
                        </div>
                        <div id="bakalField" class="hidden">
                            <label class="block text-xs text-gray-600 mb-1">Firearms Seized</label>
                            <input type="number" name="firearms_seized" value="0" min="0" class="w-full p-2 text-sm border rounded">
                        </div>
                        <div id="sitaField" class="hidden">
                            <label class="block text-xs text-gray-600 mb-1">Contraband (kg)</label>
                            <input type="number" step="0.01" name="contraband_kg" value="0" min="0" class="w-full p-2 text-sm border rounded">
                        </div>
                    </div>
                </div>

                <!-- Accomplishment Description -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Accomplishment Description *</label>
                    <textarea name="accomplishment_description" rows="4" required
                              class="w-full p-3 text-sm border border-gray-300 rounded-lg" 
                              placeholder="Describe in detail what you accomplished during this activity..."></textarea>
                </div>

                <!-- Multiple Photo Upload -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Upload Photo Evidence (Max 5 photos, up to 15MB total)</label>
                    <input type="file" name="photos[]" multiple accept="image/*" 
                           class="w-full p-2 border border-gray-300 rounded-lg"
                           onchange="validatePhotoUpload(this)">
                    <p class="text-xs text-gray-500 mt-1" id="photoUploadMessage"></p>
                </div>

                <!-- Submit Button -->
                <div class="mt-6">
                    <button type="submit" class="w-full bg-[#1f6fb2] text-white py-3 rounded-lg hover:bg-[#0a3d62] transition font-semibold text-base">
                        <i class="fas fa-paper-plane mr-2"></i> SUBMIT ACTIVITY REPORT
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
                        <span class="text-xs 
                            <?php 
                            echo $activity['status'] == 'approved' ? 'text-green-600' : 
                                ($activity['status'] == 'pending' ? 'text-yellow-600' : 'text-red-600'); 
                            ?>">
                            <?php echo ucfirst($activity['status']); ?>
                        </span>
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

    <style>
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

        // Map Variables
        let map;
        let marker;
        let userMarker;
        let currentLat = 8.366379;
        let currentLng = 124.864432;
        let currentLayer = 'street';
        
        // Map layer definitions
        const mapLayers = {
            street: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19
            }),
            satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
                maxZoom: 19
            }),
            terrain: L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                attribution: 'Map data: &copy; <a href="https://www.opentopomap.org">OpenTopoMap</a> contributors',
                maxZoom: 17
            }),
            hybrid: L.layerGroup([
                L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    attribution: 'Tiles &copy; Esri',
                    maxZoom: 19
                }),
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap',
                    maxZoom: 19,
                    opacity: 0.5
                })
            ])
        };

        // Barangay coordinates from PHP
        const barangayCoords = {
            <?php
            $first = true;
            foreach ($barangay_data as $b): 
                if (!$first) echo ",";
                $first = false;
            ?>
            "<?php echo $b['barangay_id']; ?>": {
                name: "<?php echo addslashes($b['barangay_name']); ?>",
                lat: <?php echo $b['latitude']; ?>,
                lng: <?php echo $b['longitude']; ?>
            }
            <?php endforeach; ?>
        };

        // Initialize Map
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            setPhilippineDateTime();
        });

        // Photo Upload Validation
        function validatePhotoUpload(input) {
            const files = input.files;
            const messageEl = document.getElementById('photoUploadMessage');
            let totalSize = 0;
            
            if (files.length > 5) {
                messageEl.innerHTML = '<span class="text-red-500">Maximum 5 photos allowed</span>';
                input.value = '';
                return;
            }
            
            for (let i = 0; i < files.length; i++) {
                totalSize += files[i].size;
            }
            
            const totalSizeMB = totalSize / (1024 * 1024);
            
            if (totalSizeMB > 15) {
                messageEl.innerHTML = '<span class="text-red-500">Total file size must be less than 15MB</span>';
                input.value = '';
            } else {
                messageEl.innerHTML = `<span class="text-green-500">Selected ${files.length} file(s) (${totalSizeMB.toFixed(2)}MB)</span>`;
            }
        }

        function setPhilippineDateTime() {
            const now = new Date();
            const phTime = new Date(now.getTime() + (8 * 60 * 60 * 1000));
            
            const year = phTime.getUTCFullYear();
            const month = String(phTime.getUTCMonth() + 1).padStart(2, '0');
            const day = String(phTime.getUTCDate()).padStart(2, '0');
            const phDate = `${year}-${month}-${day}`;
            
            const hours = String(phTime.getUTCHours()).padStart(2, '0');
            const minutes = String(phTime.getUTCMinutes()).padStart(2, '0');
            const phTimeStr = `${hours}:${minutes}`;
            
            document.getElementById('activity_date').value = phDate;
            document.getElementById('activity_time').value = phTimeStr;
        }

        function initMap() {
            if (!document.getElementById('map')) return;
            
            let zoomLevel = window.innerWidth < 540 ? 11 : 12;
            
            // Create map with default street layer
            map = L.map('map', {
                layers: [mapLayers.street]
            }).setView([currentLat, currentLng], zoomLevel);

            // Add scale control
            L.control.scale({ imperial: false, metric: true }).addTo(map);

            // Add geocoder control (search)
            L.Control.geocoder({
                defaultMarkGeocode: false,
                placeholder: 'Search location...',
                errorMessage: 'Location not found',
                showResultIcons: true
            }).on('markgeocode', function(e) {
                const latlng = e.geocode.center;
                map.setView(latlng, 16);
                placeMarker(latlng.lat, latlng.lng);
                reverseGeocode(latlng.lat, latlng.lng);
                findNearestBarangay(latlng.lat, latlng.lng);
            }).addTo(map);

            map.on('click', function(e) {
                placeMarker(e.latlng.lat, e.latlng.lng);
                reverseGeocode(e.latlng.lat, e.latlng.lng);
                findNearestBarangay(e.latlng.lat, e.latlng.lng);
            });

            window.addEventListener('orientationchange', function() {
                setTimeout(() => map.invalidateSize(), 200);
            });
        }

        function changeMapLayer(layerType) {
            if (!map) return;
            
            // Remove all layers
            map.eachLayer(function(layer) {
                if (layer instanceof L.TileLayer) {
                    map.removeLayer(layer);
                }
            });
            
            // Add selected layer
            if (layerType === 'street') {
                mapLayers.street.addTo(map);
            } else if (layerType === 'satellite') {
                mapLayers.satellite.addTo(map);
            } else if (layerType === 'terrain') {
                mapLayers.terrain.addTo(map);
            } else if (layerType === 'hybrid') {
                mapLayers.hybrid.addTo(map);
            }
            
            currentLayer = layerType;
        }

        function findNearestBarangay(lat, lng) {
            let nearestBarangay = null;
            let minDistance = Infinity;
            
            for (let id in barangayCoords) {
                const b = barangayCoords[id];
                const distance = calculateDistance(lat, lng, b.lat, b.lng);
                
                if (distance < minDistance) {
                    minDistance = distance;
                    nearestBarangay = { id: id, name: b.name };
                }
            }
            
            if (nearestBarangay && minDistance < 2) {
                document.getElementById('selectedBarangayId').value = nearestBarangay.id;
                const select = document.getElementById('barangaySelect');
                select.value = nearestBarangay.id;
            }
        }

        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = 
                Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
                Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }

        function placeMarker(lat, lng) {
            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng], {
                    icon: L.divIcon({
                        className: 'location-marker',
                        html: '<div class="location-marker"></div>',
                        iconSize: [20, 20]
                    })
                }).addTo(map).bindPopup('Selected Location');
            }
            
            document.getElementById('selectedLat').value = lat.toFixed(6);
            document.getElementById('selectedLng').value = lng.toFixed(6);
            
            document.getElementById('locationInfo').classList.remove('hidden');
            document.getElementById('locationText').innerHTML = `Selected: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            document.getElementById('coordinatesText').innerHTML = `Lat: ${lat.toFixed(6)}, Long: ${lng.toFixed(6)}`;
            
            // Try to get elevation data (optional)
            getElevation(lat, lng);
        }

        function getElevation(lat, lng) {
            // Using Open-Elevation API (free, no API key required)
            fetch(`https://api.open-elevation.com/api/v1/lookup?locations=${lat},${lng}`)
                .then(response => response.json())
                .then(data => {
                    if (data.results && data.results[0]) {
                        const elevation = data.results[0].elevation;
                        document.getElementById('elevationText').innerHTML = `Elevation: ${Math.round(elevation)}m`;
                    }
                })
                .catch(() => {
                    // Silently fail - elevation is optional
                });
        }

        function reverseGeocode(lat, lng) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
                .then(response => response.json())
                .then(data => {
                    let locationName = data.display_name || `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    document.getElementById('specificLocation').value = locationName.substring(0, 100);
                    
                    // Update marker popup with location name
                    if (marker) {
                        marker.bindPopup(locationName.substring(0, 50)).openPopup();
                    }
                })
                .catch(() => {
                    document.getElementById('specificLocation').value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                });
        }

        function zoomToBarangay(select) {
            const barangayId = select.value;
            if (barangayId && barangayCoords[barangayId]) {
                const coords = barangayCoords[barangayId];
                map.setView([coords.lat, coords.lng], 16);
                placeMarker(coords.lat, coords.lng);
                document.getElementById('selectedBarangayId').value = barangayId;
                document.getElementById('specificLocation').value = coords.name + ', Manolo Fortich';
            }
        }

        function getUserLocation() {
            if (navigator.geolocation) {
                document.getElementById('locationInfo').classList.remove('hidden');
                document.getElementById('locationText').innerHTML = 'Getting your exact location...';
                
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        const accuracy = position.coords.accuracy;
                        
                        map.setView([lat, lng], 18);
                        
                        if (userMarker) map.removeLayer(userMarker);
                        
                        userMarker = L.marker([lat, lng], {
                            icon: L.divIcon({
                                className: 'user-location-marker',
                                html: '<div class="user-location-marker"></div>',
                                iconSize: [20, 20]
                            })
                        }).addTo(map).bindPopup(`<b>Your Location</b><br>Accuracy: ${accuracy.toFixed(1)}m`).openPopup();
                        
                        placeMarker(lat, lng);
                        reverseGeocode(lat, lng);
                        findNearestBarangay(lat, lng);
                        document.getElementById('gps_accuracy').value = accuracy;
                        document.getElementById('locationText').innerHTML = `Your location (accuracy: ${accuracy.toFixed(1)}m)`;
                        setPhilippineDateTime();
                    },
                    function(error) {
                        let msg = 'Location error: ';
                        switch(error.code) {
                            case error.PERMISSION_DENIED: msg += 'Please allow location access.'; break;
                            case error.POSITION_UNAVAILABLE: msg += 'Location unavailable.'; break;
                            case error.TIMEOUT: msg += 'Request timed out.'; break;
                            default: msg += 'Unknown error.';
                        }
                        alert(msg);
                        document.getElementById('locationInfo').classList.add('hidden');
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            } else {
                alert('Geolocation not supported');
            }
        }

        function resetMapView() {
            map.setView([8.366379, 124.864432], 12);
            if (marker) map.removeLayer(marker);
            if (userMarker) map.removeLayer(userMarker);
            marker = null;
            userMarker = null;
            document.getElementById('specificLocation').value = '';
            document.getElementById('locationInfo').classList.add('hidden');
            document.getElementById('selectedLat').value = '';
            document.getElementById('selectedLng').value = '';
            document.getElementById('selectedBarangayId').value = '';
            document.getElementById('barangaySelect').value = '';
            document.getElementById('elevationText').innerHTML = '';
            setPhilippineDateTime();
            
            // Reset to street layer if changed
            if (currentLayer !== 'street') {
                document.getElementById('mapLayerSelect').value = 'street';
                changeMapLayer('street');
            }
        }

        function toggleActivityFields(activityType) {
            document.getElementById('personnelField').classList.add('hidden');
            document.getElementById('vehicleField').classList.add('hidden');
            document.getElementById('checkpointFields').classList.add('hidden');
            document.getElementById('oplanFields').classList.add('hidden');
            document.getElementById('bakalField').classList.add('hidden');
            document.getElementById('sitaField').classList.add('hidden');

            // Show personnel field for patrols and oplans, but NOT for checkpoints
            if (activityType.includes('Patrol') || activityType.includes('Oplan')) {
                document.getElementById('personnelField').classList.remove('hidden');
            }
            
            if (activityType === 'Mobile Patrol' || activityType === 'Motorcycle Patrol') {
                document.getElementById('vehicleField').classList.remove('hidden');
            }
            
            if (activityType === 'checkpoint') {
                document.getElementById('checkpointFields').classList.remove('hidden');
                // Personnel field is NOT shown for checkpoints
            }
            
            if (activityType === 'Oplan Bakal') {
                document.getElementById('oplanFields').classList.remove('hidden');
                document.getElementById('bakalField').classList.remove('hidden');
            }
            
            if (activityType === 'Oplan Sita') {
                document.getElementById('oplanFields').classList.remove('hidden');
                document.getElementById('sitaField').classList.remove('hidden');
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>