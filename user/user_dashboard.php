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
    <!-- Leaflet CSS (for mapping) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Leaflet Locate Control (for better location tracking) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.locatecontrol@0.79.0/dist/L.Control.Locate.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/leaflet.locatecontrol@0.79.0/dist/L.Control.Locate.min.js" charset="utf-8"></script>
    <style>
        /* Custom styles for map */
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
        .dropdown-content { 
            display: none; 
            max-height: 0;
            opacity: 0;
            transition: all 0.3s ease;
        }
        .dropdown.active .dropdown-content { 
            display: block;
            max-height: 300px;
            opacity: 1;
        }
        .rotate-180 { transform: rotate(180deg); }
        .accuracy-circle {
            stroke: #22c55e;
            stroke-opacity: 0.3;
            fill: #22c55e;
            fill-opacity: 0.1;
        }
        
        /* Mobile optimizations */
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
            .main-content-mobile {
                width: 100%;
                margin-left: 0;
            }
            .mobile-menu-btn {
                display: block;
            }
            .stat-card {
                padding: 12px;
            }
        }
        
        /* Touch-friendly buttons */
        button, .clickable {
            min-height: 44px;
            min-width: 44px;
        }
        
        /* Better scrolling on mobile */
        .overflow-scroll-touch {
            -webkit-overflow-scrolling: touch;
        }
        
        /* Hide scrollbar but keep functionality */
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
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

    <!-- ===== SIMPLIFIED SIDEBAR - Only Profile and Logout ===== -->
    <div id="sidebar" class="w-full md:w-[240px] bg-[#08324f] text-white p-4 md:p-5 md:sticky md:top-0 md:h-screen overflow-y-auto sidebar-mobile fixed top-0 left-[-100%] h-screen z-50 transition-all duration-300 ease-in-out">
        <!-- Close button for mobile -->
        <button id="closeSidebar" class="md:hidden absolute top-4 right-4 text-white text-xl">
            <i class="fas fa-times"></i>
        </button>
        
        <!-- Logo -->
        <div class="flex items-center gap-3 mb-6 pb-3 border-b border-[#1a4b6d] mt-12 md:mt-0">
            <img src="../image/pnplogo.png" class="w-8 h-8 md:w-10 md:h-10 object-contain" alt="PNP Logo">
            <h2 class="text-lg md:text-xl font-semibold">PNP User</h2>
        </div>

        <!-- ===== USER PROFILE SECTION ===== -->
        <div class="bg-gradient-to-b from-[#0a3d62] to-[#08324f] p-5 rounded-xl mb-6 text-center border border-[#1a4b6d] shadow-lg">
            <!-- Profile Avatar -->
            <div class="relative mx-auto w-20 h-20 mb-3">
                <div class="absolute inset-0 bg-yellow-400 rounded-full animate-pulse opacity-20"></div>
                <div class="relative w-full h-full bg-[#1f6fb2] rounded-full flex items-center justify-center border-3 border-yellow-400 shadow-lg">
                    <span class="text-3xl font-bold text-white">J</span>
                </div>
                <div class="absolute bottom-1 right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white"></div>
            </div>
            
            <!-- User Details -->
            <h3 class="text-lg font-bold text-yellow-400">PO3 Juan Dela Cruz</h3>
            <p class="text-xs text-gray-300 mb-2">Badge: PNP-2024-0123</p>
            
            <!-- User Info Grid -->
            <div class="grid grid-cols-2 gap-2 mt-3 text-xs">
                <div class="bg-[#0a3d62] p-2 rounded">
                    <p class="text-gray-400">Rank</p>
                    <p class="font-semibold text-white">PO3</p>
                </div>
                <div class="bg-[#0a3d62] p-2 rounded">
                    <p class="text-gray-400">Station</p>
                    <p class="font-semibold text-white">MPS</p>
                </div>
                <div class="bg-[#0a3d62] p-2 rounded col-span-2">
                    <p class="text-gray-400">Assignment</p>
                    <p class="font-semibold text-white">Patrol Unit</p>
                </div>
            </div>
            
            <!-- Status Badge -->
            <div class="mt-3 flex justify-center">
                <span class="bg-green-500 text-white text-xs px-3 py-1 rounded-full flex items-center gap-1">
                    <i class="fas fa-circle text-[8px] animate-pulse"></i> Active on Duty
                </span>
            </div>
        </div>

        <!-- ===== SIMPLE MENU - Only Dashboard and Logout ===== -->
        <ul class="space-y-2">
            <!-- Dashboard Link -->
            <li class="p-3 rounded-lg bg-[#0a3d62] border-l-4 border-yellow-400 hover:bg-[#1f6fb2] transition">
                <a href="user_dashboard.php" class="text-white no-underline block text-sm md:text-base font-medium">
                    <i class="fas fa-tachometer-alt mr-3 w-5 text-yellow-400"></i> Dashboard
                </a>
            </li>
            
            <!-- Divider -->
            <li class="my-4 border-t border-[#1a4b6d]"></li>
            
            <!-- Logout Button -->
            <li class="p-3 rounded-lg bg-red-600 hover:bg-red-700 transition cursor-pointer">
                <a href="../index.php" class="text-white no-underline block text-sm md:text-base font-medium">
                    <i class="fas fa-sign-out-alt mr-3 w-5"></i> Logout
                </a>
            </li>
            
            <!-- Version Info -->
            <li class="mt-6 text-center text-xs text-gray-400">
                <p>PNP Manolo Fortich v2.0</p>
                <p class="mt-1">© 2026 All Rights Reserved</p>
            </li>
        </ul>
    </div>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="flex-1 p-3 md:p-6 lg:p-8 bg-[#eef2f6] overflow-y-auto min-h-screen main-content-mobile">
        
        <!-- Header - Responsive -->
        <div class="bg-white p-3 md:p-4 rounded-lg shadow-sm mb-4 md:mb-6 flex flex-col sm:flex-row gap-3 sm:gap-0 justify-between items-start sm:items-center">
            <div class="ml-10 md:ml-0">
                <h2 class="text-xl md:text-2xl font-bold text-[#08324f]">User Dashboard</h2>
                <p class="text-xs md:text-sm text-gray-600 mt-1">Welcome back, PO3 Juan Dela Cruz</p>
            </div>
            <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                <div class="bg-green-100 text-green-700 px-3 md:px-4 py-1.5 md:py-2 rounded-full text-xs md:text-sm font-semibold flex items-center">
                    <i class="fas fa-circle text-[6px] md:text-[8px] text-green-500 mr-1 md:mr-2"></i> GPS: Active
                </div>
                <div class="bg-[#08324f] text-yellow-400 px-3 md:px-4 py-1.5 md:py-2 rounded-full text-xs md:text-sm font-semibold flex items-center">
                    <i class="fas fa-map-marker-alt mr-1 md:mr-2 text-xs"></i> On Duty
                </div>
            </div>
        </div>

        <!-- ===== MAP SECTION - Fully Responsive ===== -->
        <div class="bg-white p-3 md:p-5 rounded-lg shadow-md mb-4 md:mb-6">
            <h3 class="text-base md:text-lg font-semibold text-[#08324f] mb-3 md:mb-4 flex items-center">
                <i class="fas fa-map-marked-alt mr-2 text-yellow-500 text-lg md:text-xl"></i> 
                <span class="text-sm md:text-base">Real-Time Location & Activity Mapping</span>
            </h3>
            
            <!-- Barangay Selector and Map Controls - Stack on mobile -->
            <div class="flex flex-col lg:flex-row gap-3 md:gap-4 mb-3 md:mb-4">
                <!-- Barangay Selection - Full width on mobile -->
                <div class="w-full lg:w-1/2">
                    <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">Select Barangay (Exact Location)</label>
                    <select id="barangaySelect" class="w-full p-2 md:p-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2] focus:border-transparent" onchange="zoomToBarangay(this.value)">
                        <option value="">-- Select Barangay --</option>
                        <option value="Agusan Canyon">Agusan Canyon</option>
                        <option value="Alae">Alae</option>
                        <option value="Dahilayan">Dahilayan</option>
                        <option value="Dalirig">Dalirig</option>
                        <option value="Damilag">Damilag</option>
                        <option value="Dicklum">Dicklum</option>
                        <option value="Guilang-guilang">Guilang-guilang</option>
                        <option value="Kalugmanan">Kalugmanan</option>
                        <option value="Lindaban">Lindaban</option>
                        <option value="Lurugan">Lurugan</option>
                        <option value="Manolo Fortich Poblacion">Manolo Fortich Poblacion</option>
                        <option value="Mambatangan">Mambatangan</option>
                        <option value="Minsuro">Minsuro</option>
                        <option value="Mantibugao">Mantibugao</option>
                        <option value="Sankanan">Sankanan</option>
                        <option value="Santiago">Santiago</option>
                        <option value="Santo Niño">Santo Niño</option>
                        <option value="Tankulan">Tankulan</option>
                        <option value="Ticala">Ticala</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1 hidden sm:block"><i class="fas fa-info-circle"></i> Select barangay to zoom to exact location</p>
                </div>

                <!-- Map Controls - Stack on mobile -->
                <div class="w-full lg:w-1/2 flex flex-wrap gap-2 items-end">
                    <button onclick="getUserLocation()" class="flex-1 bg-[#1f6fb2] text-white px-2 md:px-4 py-2 md:py-2.5 rounded-lg hover:bg-[#0a3d62] transition flex items-center justify-center gap-1 md:gap-2 text-xs md:text-sm min-h-[44px]">
                        <i class="fas fa-location-dot"></i>
                        <span class="hidden xs:inline">Track</span> Location
                    </button>
                    <button onclick="stopTracking()" class="flex-1 bg-gray-500 text-white px-2 md:px-4 py-2 md:py-2.5 rounded-lg hover:bg-gray-600 transition flex items-center justify-center gap-1 md:gap-2 text-xs md:text-sm min-h-[44px]">
                        <i class="fas fa-stop"></i>
                        <span class="hidden xs:inline">Stop</span>
                    </button>
                    <button onclick="resetMapView()" class="flex-1 bg-yellow-500 text-white px-2 md:px-4 py-2 md:py-2.5 rounded-lg hover:bg-yellow-600 transition flex items-center justify-center gap-1 md:gap-2 text-xs md:text-sm min-h-[44px]">
                        <i class="fas fa-globe"></i>
                        <span class="hidden xs:inline">Reset</span>
                    </button>
                </div>
            </div>

            <!-- Map Container - Responsive height -->
            <div id="map" class="w-full h-[300px] sm:h-[350px] md:h-[400px] lg:h-[450px] rounded-lg border-2 border-gray-200"></div>
            
            <!-- Real-Time Location Info - Responsive grid -->
            <div id="locationInfo" class="mt-3 p-2 md:p-3 bg-blue-50 rounded-lg hidden">
                <div class="flex flex-col sm:grid sm:grid-cols-2 gap-2 md:gap-4">
                    <div>
                        <p class="text-xs md:text-sm text-gray-700 break-words"><i class="fas fa-info-circle text-[#1f6fb2] mr-1 md:mr-2"></i><span id="locationText"></span></p>
                        <p class="text-xs text-gray-500 mt-1 break-words" id="coordinatesText"></p>
                    </div>
                    <div class="sm:text-right">
                        <p class="text-xs text-gray-500"><i class="fas fa-crosshairs mr-1"></i> Accuracy: <span id="accuracyText">N/A</span></p>
                        <p class="text-xs text-gray-500"><i class="fas fa-satellite mr-1"></i> Source: <span id="sourceText">GPS</span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Form and Stats Grid - Stack on mobile -->
        <div class="flex flex-col lg:grid lg:grid-cols-3 gap-4 md:gap-6">
            <!-- Activity Form - Full width on mobile -->
            <div class="lg:col-span-2">
                <div class="bg-white p-3 md:p-5 rounded-lg shadow-md">
                    <h3 class="text-base md:text-lg font-semibold text-[#08324f] mb-3 md:mb-4">Report Current Activity</h3>
                    
                    <form id="activityForm" onsubmit="submitActivity(event)">
                        <!-- Selected Location (from map) -->
                        <input type="hidden" id="selectedLat" name="latitude">
                        <input type="hidden" id="selectedLng" name="longitude">
                        <input type="hidden" id="selectedBarangay" name="barangay">
                        <input type="hidden" id="locationAccuracy" name="accuracy">

                        <!-- Form fields - Responsive grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-4">
                            <!-- Activity Type -->
                            <div>
                                <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">Activity Type</label>
                                <select name="activity_type" required class="w-full p-2 text-sm border border-gray-300 rounded-lg" onchange="toggleActivityFields(this.value)">
                                    <option value="">Select Type</option>
                                    <option value="foot_patrol">Foot Patrol</option>
                                    <option value="mobile_patrol">Mobile Patrol</option>
                                    <option value="motor_patrol">Motorcycle Patrol</option>
                                    <option value="checkpoint">Checkpoint</option>
                                    <option value="oplan_bakal">Oplan Bakal</option>
                                    <option value="oplan_sita">Oplan Sita</option>
                                </select>
                            </div>

                            <!-- Specific Location -->
                            <div>
                                <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">Specific Location</label>
                                <input type="text" id="specificLocation" name="specific_location" readonly 
                                       class="w-full p-2 text-sm border border-gray-300 rounded-lg bg-gray-50" 
                                       placeholder="Click on map">
                            </div>

                            <!-- Date -->
                            <div>
                                <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">Date</label>
                                <input type="date" name="date" required value="<?php echo date('Y-m-d'); ?>" 
                                       class="w-full p-2 text-sm border border-gray-300 rounded-lg">
                            </div>

                            <!-- Time -->
                            <div>
                                <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">Time</label>
                                <input type="time" name="time" required value="<?php echo date('H:i'); ?>" 
                                       class="w-full p-2 text-sm border border-gray-300 rounded-lg">
                            </div>

                            <!-- Personnel (dynamic) -->
                            <div id="personnelField" class="hidden">
                                <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">Number of Personnel</label>
                                <input type="number" name="personnel" min="1" value="1" 
                                       class="w-full p-2 text-sm border border-gray-300 rounded-lg">
                            </div>

                            <!-- Vehicle/Unit -->
                            <div id="vehicleField" class="hidden">
                                <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">Vehicle/Unit Number</label>
                                <input type="text" name="vehicle_number" placeholder="e.g., MCS-101" 
                                       class="w-full p-2 text-sm border border-gray-300 rounded-lg">
                            </div>
                        </div>

                        <!-- Checkpoint Specific Fields -->
                        <div id="checkpointFields" class="hidden mt-3 md:mt-4 p-3 bg-gray-50 rounded-lg">
                            <h4 class="font-medium text-sm mb-2">Checkpoint Details</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 md:gap-4">
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Border Control Ops</label>
                                    <input type="number" name="border_control_ops" value="0" min="0" class="w-full p-1.5 md:p-2 text-sm border rounded">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Mobile Checkpoint Ops</label>
                                    <input type="number" name="mobile_checkpoint_ops" value="0" min="0" class="w-full p-1.5 md:p-2 text-sm border rounded">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">TCT/OVR Accomplishments</label>
                                    <input type="number" name="tct_ovr" value="0" min="0" class="w-full p-1.5 md:p-2 text-sm border rounded">
                                </div>
                            </div>
                        </div>

                        <!-- Accomplishment Description -->
                        <div class="mt-3 md:mt-4">
                            <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">Accomplishment Description</label>
                            <textarea name="accomplishment" rows="3" 
                                      class="w-full p-2 text-sm border border-gray-300 rounded-lg" 
                                      placeholder="Describe what you accomplished..."></textarea>
                        </div>

                        <!-- Photo Upload -->
                        <div class="mt-3 md:mt-4">
                            <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">Upload Photo Evidence</label>
                            <input type="file" name="photo" accept="image/*" 
                                   class="w-full p-1.5 md:p-2 text-sm border border-gray-300 rounded-lg">
                            <p class="text-xs text-gray-500 mt-1">Max: 15MB. JPG, PNG only</p>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-4 md:mt-6">
                            <button type="submit" 
                                    class="w-full bg-[#1f6fb2] text-white py-2.5 md:py-3 rounded-lg hover:bg-[#0a3d62] transition font-semibold text-sm md:text-base">
                                <i class="fas fa-paper-plane mr-2"></i> SUBMIT ACTIVITY REPORT
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Stats and Recent Activities - Responsive -->
            <div class="space-y-4 md:space-y-6">
                <!-- Today's Stats -->
                <div class="bg-white p-3 md:p-5 rounded-lg shadow-md">
                    <h3 class="text-sm md:text-lg font-semibold text-[#08324f] mb-2 md:mb-4">Today's Stats</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center p-2 border-b">
                            <span class="text-xs md:text-sm text-gray-600"><i class="fas fa-walking mr-2 text-blue-500"></i> Patrols</span>
                            <span class="font-bold text-sm md:text-base">3</span>
                        </div>
                        <div class="flex justify-between items-center p-2 border-b">
                            <span class="text-xs md:text-sm text-gray-600"><i class="fas fa-map-marker-alt mr-2 text-red-500"></i> Checkpoints</span>
                            <span class="font-bold text-sm md:text-base">1</span>
                        </div>
                        <div class="flex justify-between items-center p-2 border-b">
                            <span class="text-xs md:text-sm text-gray-600"><i class="fas fa-shield-alt mr-2 text-green-500"></i> Oplans</span>
                            <span class="font-bold text-sm md:text-base">2</span>
                        </div>
                        <div class="flex justify-between items-center p-2">
                            <span class="text-xs md:text-sm text-gray-600"><i class="fas fa-clock mr-2 text-yellow-500"></i> Hours on Duty</span>
                            <span class="font-bold text-sm md:text-base">6.5 hrs</span>
                        </div>
                    </div>
                </div>

                <!-- My Recent Activities - Scrollable on mobile -->
                <div class="bg-white p-3 md:p-5 rounded-lg shadow-md">
                    <h3 class="text-sm md:text-lg font-semibold text-[#08324f] mb-2 md:mb-4">My Recent Activities</h3>
                    <div class="space-y-2 max-h-[250px] md:max-h-[300px] overflow-y-auto hide-scrollbar pr-1">
                        <div class="p-2 md:p-3 bg-gray-50 rounded-lg border-l-4 border-blue-500 hover:shadow-md transition">
                            <p class="font-medium text-xs md:text-sm">Foot Patrol - Tankulan</p>
                            <p class="text-xs text-gray-500 mt-1">Today, 9:30 AM • 4 personnel</p>
                            <p class="text-xs text-gray-400 mt-1 truncate"><i class="fas fa-map-pin"></i> Poblacion, Tankulan</p>
                        </div>
                        <div class="p-2 md:p-3 bg-gray-50 rounded-lg border-l-4 border-red-500 hover:shadow-md transition">
                            <p class="font-medium text-xs md:text-sm">Checkpoint - Alae</p>
                            <p class="text-xs text-gray-500 mt-1">Today, 7:15 AM • Border Control</p>
                            <p class="text-xs text-gray-400 mt-1 truncate"><i class="fas fa-map-pin"></i> National Highway, Alae</p>
                        </div>
                        <div class="p-2 md:p-3 bg-gray-50 rounded-lg border-l-4 border-green-500 hover:shadow-md transition">
                            <p class="font-medium text-xs md:text-sm">Oplan Bakal - Dahilayan</p>
                            <p class="text-xs text-gray-500 mt-1">Yesterday, 2:30 PM</p>
                            <p class="text-xs text-gray-400 mt-1 truncate"><i class="fas fa-map-pin"></i> Dahilayan Forest Park</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ===== MOBILE MENU FUNCTIONS =====
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

        if (menuBtn) {
            menuBtn.addEventListener('click', openMobileMenu);
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', closeMobileMenu);
        }

        if (overlay) {
            overlay.addEventListener('click', closeMobileMenu);
        }

        // Close menu on window resize if open
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                closeMobileMenu();
            }
        });

        // ===== MAP INITIALIZATION =====
        let map;
        let marker;
        let userMarker;
        let accuracyCircle;
        let watchId = null;
        let currentLat = 8.3782;
        let currentLng = 124.8658;

        // EXACT coordinates for each barangay
        const barangayCoords = {
            "Agusan Canyon": [8.3891, 124.8523],
            "Alae": [8.4215, 124.8012],
            "Dahilayan": [8.3256, 124.8567],
            "Dalirig": [8.3934, 124.9102],
            "Damilag": [8.3547, 124.8431],
            "Dicklum": [8.3678, 124.7895],
            "Guilang-guilang": [8.3456, 124.8234],
            "Kalugmanan": [8.4123, 124.9345],
            "Lindaban": [8.3789, 124.7789],
            "Lurugan": [8.4012, 124.9678],
            "Manolo Fortich Poblacion": [8.3698, 124.8634],
            "Mambatangan": [8.3345, 124.8012],
            "Minsuro": [8.3567, 124.7234],
            "Mantibugao": [8.3891, 124.7234],
            "Sankanan": [8.3123, 124.8890],
            "Santiago": [8.4234, 124.9123],
            "Santo Niño": [8.3456, 124.7789],
            "Tankulan": [8.3782, 124.8658],
            "Ticala": [8.34093, 124.89242]
        };

        // Initialize map when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
        });

        function initMap() {
            // Adjust zoom level based on screen size
            let zoomLevel = window.innerWidth < 540 ? 11 : 12;
            
            map = L.map('map').setView([currentLat, currentLng], zoomLevel);
            
            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 19
            }).addTo(map);

            // Add scale bar (hide on very small screens)
            if (window.innerWidth >= 540) {
                L.control.scale({ imperial: false, metric: true }).addTo(map);
            }

            // Add click event to map
            map.on('click', function(e) {
                placeMarker(e.latlng.lat, e.latlng.lng);
                reverseGeocode(e.latlng.lat, e.latlng.lng);
            });

            // Handle map resize on orientation change
            window.addEventListener('orientationchange', function() {
                setTimeout(function() {
                    map.invalidateSize();
                }, 200);
            });
        }

        // Place marker on map
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
                }).addTo(map);
            }
            
            document.getElementById('selectedLat').value = lat.toFixed(6);
            document.getElementById('selectedLng').value = lng.toFixed(6);
            
            document.getElementById('locationInfo').classList.remove('hidden');
            document.getElementById('locationText').innerHTML = `Selected: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            document.getElementById('coordinatesText').innerHTML = `Lat: ${lat.toFixed(6)}, Long: ${lng.toFixed(6)}`;
        }

        // Reverse geocode
        function reverseGeocode(lat, lng) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
                .then(response => response.json())
                .then(data => {
                    let locationName = data.display_name || `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    document.getElementById('specificLocation').value = locationName.substring(0, 100);
                    
                    if (data.address) {
                        let barangay = data.address.village || data.address.town || data.address.city_district || data.address.suburb || '';
                        if (barangay) {
                            document.getElementById('selectedBarangay').value = barangay;
                            
                            let select = document.getElementById('barangaySelect');
                            for (let i = 0; i < select.options.length; i++) {
                                if (select.options[i].text.toLowerCase().includes(barangay.toLowerCase())) {
                                    select.value = select.options[i].value;
                                    break;
                                }
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error('Geocoding error:', error);
                    document.getElementById('specificLocation').value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                });
        }

        // Zoom to selected barangay
        function zoomToBarangay(barangay) {
            if (barangay && barangayCoords[barangay]) {
                const coords = barangayCoords[barangay];
                let zoomLevel = window.innerWidth < 640 ? 15 : 16;
                map.setView(coords, zoomLevel);
                placeMarker(coords[0], coords[1]);
                document.getElementById('selectedBarangay').value = barangay;
                
                document.getElementById('locationInfo').classList.remove('hidden');
                document.getElementById('locationText').innerHTML = `Barangay: ${barangay}`;
                document.getElementById('coordinatesText').innerHTML = `Location: ${coords[0].toFixed(6)}, ${coords[1].toFixed(6)}`;
            }
        }

        // Get user's current location
        function getUserLocation() {
            if (navigator.geolocation) {
                document.getElementById('locationInfo').classList.remove('hidden');
                document.getElementById('locationText').innerHTML = 'Acquiring GPS signal...';
                
                const options = {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                };

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        const accuracy = position.coords.accuracy;
                        
                        let zoomLevel = accuracy < 10 ? 18 : accuracy < 50 ? 17 : 16;
                        map.setView([lat, lng], zoomLevel);
                        
                        if (userMarker) map.removeLayer(userMarker);
                        if (accuracyCircle) map.removeLayer(accuracyCircle);
                        
                        accuracyCircle = L.circle([lat, lng], {
                            radius: accuracy,
                            color: '#22c55e',
                            weight: 1,
                            opacity: 0.3,
                            fillColor: '#22c55e',
                            fillOpacity: 0.1
                        }).addTo(map);
                        
                        userMarker = L.marker([lat, lng], {
                            icon: L.divIcon({
                                className: 'user-location-marker',
                                html: '<div class="user-location-marker"></div>',
                                iconSize: [20, 20]
                            })
                        }).addTo(map).bindPopup('Your Location').openPopup();
                        
                        placeMarker(lat, lng);
                        reverseGeocode(lat, lng);
                        
                        document.getElementById('accuracyText').innerHTML = accuracy.toFixed(1) + 'm';
                        document.getElementById('sourceText').innerHTML = 'GPS';
                        document.getElementById('locationAccuracy').value = accuracy;
                        
                        let accuracyMsg = accuracy < 20 ? '✓ High accuracy' : 'Location detected';
                        alert(accuracyMsg + ' (' + accuracy.toFixed(1) + 'm accuracy)');
                    },
                    function(error) {
                        let errorMsg = 'Location error: ';
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMsg += 'Please enable location access.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMsg += 'Location unavailable.';
                                break;
                            case error.TIMEOUT:
                                errorMsg += 'Request timed out.';
                                break;
                            default:
                                errorMsg += error.message;
                        }
                        alert(errorMsg);
                        document.getElementById('locationInfo').classList.add('hidden');
                    },
                    options
                );

                if (watchId) {
                    navigator.geolocation.clearWatch(watchId);
                }
                
                watchId = navigator.geolocation.watchPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        const accuracy = position.coords.accuracy;
                        
                        if (userMarker) {
                            userMarker.setLatLng([lat, lng]);
                        }
                        
                        if (accuracyCircle) {
                            accuracyCircle.setLatLng([lat, lng]);
                            accuracyCircle.setRadius(accuracy);
                        }
                        
                        document.getElementById('accuracyText').innerHTML = accuracy.toFixed(1) + 'm';
                        document.getElementById('locationAccuracy').value = accuracy;
                    },
                    function(error) {
                        console.log('Watch error:', error);
                    },
                    {
                        enableHighAccuracy: true,
                        maximumAge: 0,
                        timeout: 5000
                    }
                );
            } else {
                alert('Geolocation not supported');
            }
        }

        // Stop tracking
        function stopTracking() {
            if (watchId) {
                navigator.geolocation.clearWatch(watchId);
                watchId = null;
                
                if (userMarker) {
                    map.removeLayer(userMarker);
                    userMarker = null;
                }
                if (accuracyCircle) {
                    map.removeLayer(accuracyCircle);
                    accuracyCircle = null;
                }
                
                alert('Tracking stopped.');
            }
        }

        // Reset map view
        function resetMapView() {
            let zoomLevel = window.innerWidth < 640 ? 11 : 12;
            map.setView([8.3782, 124.8658], zoomLevel);
            if (marker) map.removeLayer(marker);
            marker = null;
            document.getElementById('specificLocation').value = '';
            document.getElementById('locationInfo').classList.add('hidden');
            document.getElementById('selectedLat').value = '';
            document.getElementById('selectedLng').value = '';
            document.getElementById('selectedBarangay').value = '';
            document.getElementById('barangaySelect').value = '';
        }

        // Toggle activity fields
        function toggleActivityFields(activityType) {
            document.getElementById('personnelField').classList.add('hidden');
            document.getElementById('vehicleField').classList.add('hidden');
            document.getElementById('checkpointFields').classList.add('hidden');

            if (activityType.includes('patrol') || activityType.includes('oplan')) {
                document.getElementById('personnelField').classList.remove('hidden');
            }
            
            if (activityType === 'mobile_patrol' || activityType === 'motor_patrol') {
                document.getElementById('vehicleField').classList.remove('hidden');
            }
            
            if (activityType === 'checkpoint') {
                document.getElementById('checkpointFields').classList.remove('hidden');
                document.getElementById('personnelField').classList.remove('hidden');
            }
        }

        // Submit activity
        function submitActivity(event) {
            event.preventDefault();
            
            if (!document.getElementById('selectedLat').value) {
                alert('Please select a location on the map first.');
                return;
            }
            
            let accuracy = document.getElementById('accuracyText').innerHTML;
            let location = document.getElementById('specificLocation').value;
            
            alert(`✓ Activity Reported!\n\nLocation: ${location.substring(0, 50)}...\nAccuracy: ${accuracy}`);
            
            event.target.reset();
            resetMapView();
            
            document.querySelector('input[name="date"]').value = new Date().toISOString().split('T')[0];
            document.querySelector('input[name="time"]').value = new Date().toTimeString().slice(0,5);
        }

        // Dropdown toggle
        function toggleDropdown(element) {
            const parent = element.closest('.dropdown');
            parent.classList.toggle('active');
            const arrow = element.querySelector('.fa-chevron-down');
            if (arrow) arrow.classList.toggle('rotate-180');
        }

        // Close other dropdowns
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

        // Clean up on unload
        window.addEventListener('beforeunload', function() {
            if (watchId) {
                navigator.geolocation.clearWatch(watchId);
            }
        });
    </script>
</body>
</html>