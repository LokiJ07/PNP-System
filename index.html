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

        <!-- ===== MAP SECTION - User selects location ===== -->
        <div class="bg-white p-3 md:p-5 rounded-lg shadow-md mb-4 md:mb-6">
            <h3 class="text-base md:text-lg font-semibold text-[#08324f] mb-3 md:mb-4 flex items-center">
                <i class="fas fa-map-marked-alt mr-2 text-yellow-500 text-lg md:text-xl"></i> 
                <span class="text-sm md:text-base">Select Your Location</span>
            </h3>
            
            <!-- Barangay Selector and Map Controls -->
            <div class="flex flex-col lg:flex-row gap-3 md:gap-4 mb-3 md:mb-4">
                <!-- Barangay Selection -->
                <div class="w-full lg:w-1/2">
                    <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">Select Barangay</label>
                    <select id="barangaySelect" class="w-full p-2 md:p-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2]" onchange="zoomToBarangay(this.value)">
                        <option value="">-- Select Barangay --</option>
                        <option value="Agusan Canyon">Agusan Canyon</option>
                        <option value="Alae">Alae</option>
                        <option value="Abyawan">Abyawan</option>
                        <option value="Dahilayan">Dahilayan</option>
                        <option value="Dalirig">Dalirig</option>
                        <option value="Damilag">Damilag</option>
                        <option value="Dicklum">Dicklum</option>
                        <option value="Guilang-guilang">Guilang-guilang</option>
                        <option value="Kalugmanan">Kalugmanan</option>
                        <option value="Lindaban">Lindaban</option>
                        <option value="Lingion">Lingion</option>
                        <option value="Lunocan">Lunocan</option>
                        <option value="Mambatangan">Mambatangan</option>
                        <option value="Minsuro">Minsuro</option>
                        <option value="Mantibugao">Mantibugao</option>
                        <option value="Tankulan">Pob. Tankulan</option>
                        <option value="Sankanan">Sankanan</option>
                        <option value="Santiago">Santiago</option>
                        <option value="San Miguel">San Miguel</option>
                        <option value="Santo Niño">Santo Niño</option>
                        <option value="Ticala">Ticala</option>
                    </select>
                </div>

                <!-- Map Controls -->
                <div class="w-full lg:w-1/2 flex flex-wrap gap-2 items-end">
                    <button onclick="getUserLocation()" class="flex-1 bg-[#1f6fb2] text-white px-2 md:px-4 py-2 md:py-2.5 rounded-lg hover:bg-[#0a3d62] transition flex items-center justify-center gap-1 md:gap-2 text-xs md:text-sm">
                        <i class="fas fa-location-dot"></i> My Location
                    </button>
                    <button onclick="resetMapView()" class="flex-1 bg-yellow-500 text-white px-2 md:px-4 py-2 md:py-2.5 rounded-lg hover:bg-yellow-600 transition flex items-center justify-center gap-1 md:gap-2 text-xs md:text-sm">
                        <i class="fas fa-globe"></i> Reset
                    </button>
                </div>
            </div>

            <!-- Map Container -->
            <div id="map" class="w-full h-[300px] sm:h-[350px] md:h-[400px] lg:h-[450px] rounded-lg border-2 border-gray-200"></div>
            
            <!-- Location Info -->
            <div id="locationInfo" class="mt-3 p-2 md:p-3 bg-blue-50 rounded-lg hidden">
                <p class="text-xs md:text-sm text-gray-700"><i class="fas fa-map-pin text-[#1f6fb2] mr-2"></i><span id="locationText"></span></p>
                <p class="text-xs text-gray-500 mt-1" id="coordinatesText"></p>
            </div>
        </div>

        <!-- ===== ACTIVITY FORM - User INPUTS data ===== -->
        <div class="bg-white p-3 md:p-5 rounded-lg shadow-md">
            <h3 class="text-base md:text-lg font-semibold text-[#08324f] mb-3 md:mb-4">Report New Activity</h3>
            
            <form id="activityForm" onsubmit="submitActivity(event)">
                <!-- Selected Location (from map) -->
                <input type="hidden" id="selectedLat" name="latitude">
                <input type="hidden" id="selectedLng" name="longitude">
                <input type="hidden" id="selectedBarangay" name="barangay">

                <!-- Form fields - User fills these out -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Activity Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Activity Type *</label>
                        <select name="activity_type" required class="w-full p-2.5 text-sm border border-gray-300 rounded-lg" onchange="toggleActivityFields(this.value)">
                            <option value="">Select Type</option>
                            <option value="foot_patrol">Foot Patrol</option>
                            <option value="mobile_patrol">Mobile Patrol</option>
                            <option value="motor_patrol">Motorcycle Patrol</option>
                            <option value="checkpoint">Checkpoint</option>
                            <option value="oplan_bakal">Oplan Bakal</option>
                            <option value="oplan_sita">Oplan Sita</option>
                        </select>
                    </div>

                    <!-- Specific Location (auto-filled from map) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <input type="text" id="specificLocation" name="specific_location" readonly 
                               class="w-full p-2.5 text-sm border border-gray-300 rounded-lg bg-gray-50" 
                               placeholder="Click on map to set location">
                    </div>

                    <!-- Date - FIXED: Shows current Philippine date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                        <input type="date" name="date" required value="<?php echo date('Y-m-d'); ?>" 
                               class="w-full p-2.5 text-sm border border-gray-300 rounded-lg">
                    </div>

                    <!-- Time - FIXED: Shows current Philippine time -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Time *</label>
                        <input type="time" name="time" required value="<?php echo date('H:i'); ?>" 
                               class="w-full p-2.5 text-sm border border-gray-300 rounded-lg">
                    </div>

                    <!-- Personnel (dynamic) -->
                    <div id="personnelField" class="hidden md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Number of Personnel *</label>
                        <input type="number" name="personnel" min="1" value="1" required
                               class="w-full p-2.5 text-sm border border-gray-300 rounded-lg">
                    </div>

                    <!-- Vehicle/Unit (for mobile/motor patrol) -->
                    <div id="vehicleField" class="hidden md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle/Unit Number</label>
                        <input type="text" name="vehicle_number" placeholder="e.g., MCS-101" 
                               class="w-full p-2.5 text-sm border border-gray-300 rounded-lg">
                    </div>
                </div>

                <!-- Checkpoint Specific Fields -->
                <div id="checkpointFields" class="hidden mt-4 p-4 bg-gray-50 rounded-lg">
                    <h4 class="font-medium text-sm mb-3 text-[#08324f]">Checkpoint Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Border Control Ops</label>
                            <input type="number" name="border_control_ops" value="0" min="0" class="w-full p-2 text-sm border rounded">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Mobile Checkpoint Ops</label>
                            <input type="number" name="mobile_checkpoint_ops" value="0" min="0" class="w-full p-2 text-sm border rounded">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">TCT/OVR Accomplishments</label>
                            <input type="number" name="tct_ovr" value="0" min="0" class="w-full p-2 text-sm border rounded">
                        </div>
                    </div>
                </div>

                <!-- Oplan Specific Fields -->
                <div id="oplanFields" class="hidden mt-4 p-4 bg-gray-50 rounded-lg">
                    <h4 class="font-medium text-sm mb-3 text-[#08324f]">Oplan Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Number of Operations</label>
                            <input type="number" name="oplan_ops" value="1" min="1" class="w-full p-2 text-sm border rounded">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Arrests Made</label>
                            <input type="number" name="oplan_arrests" value="0" min="0" class="w-full p-2 text-sm border rounded">
                        </div>
                    </div>
                </div>

                <!-- Accomplishment Description -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Accomplishment Description *</label>
                    <textarea name="accomplishment" rows="4" required
                              class="w-full p-3 text-sm border border-gray-300 rounded-lg" 
                              placeholder="Describe in detail what you accomplished during this activity. Be specific - include number of persons assisted, violations issued, items seized, arrests made, etc."></textarea>
                    <p class="text-xs text-gray-500 mt-1">This will be used by admin to generate accomplishment reports</p>
                </div>

                <!-- Photo Upload -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Upload Photo Evidence</label>
                    <input type="file" name="photo" accept="image/*" 
                           class="w-full p-2 border border-gray-300 rounded-lg">
                    <p class="text-xs text-gray-500 mt-1">Max: 15MB. JPG, PNG only</p>
                </div>

                <!-- Submit Button -->
                <div class="mt-6">
                    <button type="submit" 
                            class="w-full bg-[#1f6fb2] text-white py-3 rounded-lg hover:bg-[#0a3d62] transition font-semibold text-base">
                        <i class="fas fa-paper-plane mr-2"></i> SUBMIT ACTIVITY REPORT
                    </button>
                </div>
            </form>
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

        if (menuBtn) menuBtn.addEventListener('click', openMobileMenu);
        if (closeBtn) closeBtn.addEventListener('click', closeMobileMenu);
        if (overlay) overlay.addEventListener('click', closeMobileMenu);
        window.addEventListener('resize', function() { if (window.innerWidth >= 768) closeMobileMenu(); });

        // ===== MAP INITIALIZATION =====
        let map;
        let marker;
        let userMarker;
        let currentLat = 8.3782;
        let currentLng = 124.8658;

        // EXACT coordinates for each barangay in Manolo Fortich
        const barangayCoords = {
            "Agusan Canyon": [8.333756, 124.815385],
            "Abyawan": [8.425780, 124.937224],
            "Alae": [8.422394, 124.813030],
            "Dahilayan": [8.219238, 124.852093],
            "Dalirig": [8.376396, 124.901176],
            "Damilag": [8.353324, 124.813294],
            "Dicklum": [8.372235, 124.849156],
            "Guilang-guilang": [8.457363, 125.032696],
            "Kalugmanan": [8.276591, 124.859303],
            "Lindaban": [8.290475, 124.848330],
            "Lingion": [8.403194, 124.888303],
            "Lunocan": [8.431587, 124.840309],
            "Maluko": [8.375173, 124.955589],
            "Mambatangan": [8.467822, 124.790619],
            "Minsuro": [8.510253, 124.831259],
            "Mantibugao": [8.458500, 124.824084],
            "Sankanan": [8.315932, 124.857913],
            "Santiago": [8.436308, 124.995782],
            "San Miguel": [8.389048, 124.835936],
            "Santo Niño": [8.428420, 124.864042],
            "Tankulan": [8.366379, 124.864432],
            "Ticala": [8.340187, 124.891891]
        };

        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            // FIXED: Set correct Philippine time
            setPhilippineTime();
        });

        // FIXED: Function to set Philippine time (UTC+8)
        function setPhilippineTime() {
            const now = new Date();
            // Philippine time is UTC+8
            const phTime = new Date(now.getTime() + (8 * 60 * 60 * 1000));
            
            // Format date as YYYY-MM-DD
            const year = phTime.getUTCFullYear();
            const month = String(phTime.getUTCMonth() + 1).padStart(2, '0');
            const day = String(phTime.getUTCDate()).padStart(2, '0');
            const phDate = `${year}-${month}-${day}`;
            
            // Format time as HH:MM (24-hour)
            const hours = String(phTime.getUTCHours()).padStart(2, '0');
            const minutes = String(phTime.getUTCMinutes()).padStart(2, '0');
            const phTimeStr = `${hours}:${minutes}`;
            
            // Set the input values
            document.querySelector('input[name="date"]').value = phDate;
            document.querySelector('input[name="time"]').value = phTimeStr;
        }

        function initMap() {
            let zoomLevel = window.innerWidth < 540 ? 11 : 12;
            map = L.map('map').setView([currentLat, currentLng], zoomLevel);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap',
                maxZoom: 19
            }).addTo(map);

            map.on('click', function(e) {
                placeMarker(e.latlng.lat, e.latlng.lng);
                reverseGeocode(e.latlng.lat, e.latlng.lng);
            });

            window.addEventListener('orientationchange', function() {
                setTimeout(() => map.invalidateSize(), 200);
            });
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
                }).addTo(map);
            }
            
            document.getElementById('selectedLat').value = lat.toFixed(6);
            document.getElementById('selectedLng').value = lng.toFixed(6);
            
            document.getElementById('locationInfo').classList.remove('hidden');
            document.getElementById('locationText').innerHTML = `Selected: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            document.getElementById('coordinatesText').innerHTML = `Lat: ${lat.toFixed(6)}, Long: ${lng.toFixed(6)}`;
        }

        function reverseGeocode(lat, lng) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18`)
                .then(response => response.json())
                .then(data => {
                    let locationName = data.display_name || `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    document.getElementById('specificLocation').value = locationName.substring(0, 100);
                    
                    if (data.address) {
                        let barangay = data.address.village || data.address.town || data.address.city_district || '';
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
                .catch(() => {
                    document.getElementById('specificLocation').value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                });
        }

        function zoomToBarangay(barangay) {
            if (barangay && barangayCoords[barangay]) {
                const coords = barangayCoords[barangay];
                map.setView(coords, 12);
                placeMarker(coords[0], coords[1]);
                document.getElementById('selectedBarangay').value = barangay;
            }
        }

        // FIXED: Improved getUserLocation function with better accuracy
        function getUserLocation() {
            if (navigator.geolocation) {
                // Show loading message
                document.getElementById('locationInfo').classList.remove('hidden');
                document.getElementById('locationText').innerHTML = 'Getting your exact location...';
                
                // Options for high accuracy
                const options = {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                };

                navigator.geolocation.getCurrentPosition(
                    // Success callback
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        const accuracy = position.coords.accuracy;
                        
                        // Center map on user location with high zoom
                        map.setView([lat, lng], 18);
                        
                        // Remove existing user marker if any
                        if (userMarker) {
                            map.removeLayer(userMarker);
                        }
                        
                        // Add user marker with pulsing effect
                        userMarker = L.marker([lat, lng], {
                            icon: L.divIcon({
                                className: 'user-location-marker',
                                html: '<div class="user-location-marker"></div>',
                                iconSize: [20, 20]
                            })
                        }).addTo(map).bindPopup(`<b>Your Current Location</b><br>Accuracy: ${accuracy.toFixed(1)} meters`).openPopup();
                        
                        // Place activity marker at same location
                        placeMarker(lat, lng);
                        reverseGeocode(lat, lng);
                        
                        // Show accuracy info
                        document.getElementById('locationText').innerHTML = `Your location (accuracy: ${accuracy.toFixed(1)}m)`;
                        
                        alert(`✓ Location detected!\nAccuracy: ${accuracy.toFixed(1)} meters`);
                    },
                    // Error callback
                    function(error) {
                        let errorMsg = 'Location error: ';
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMsg += 'Please allow location access in your browser.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMsg += 'Location information unavailable.';
                                break;
                            case error.TIMEOUT:
                                errorMsg += 'Location request timed out.';
                                break;
                            default:
                                errorMsg += 'Unknown error occurred.';
                        }
                        alert(errorMsg);
                        document.getElementById('locationInfo').classList.add('hidden');
                    },
                    options
                );
            } else {
                alert('Geolocation is not supported by your browser');
            }
        }

        function resetMapView() {
            map.setView([8.3782, 124.8658], 12);
            if (marker) map.removeLayer(marker);
            if (userMarker) map.removeLayer(userMarker);
            marker = null;
            userMarker = null;
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
            document.getElementById('oplanFields').classList.add('hidden');

            if (activityType.includes('patrol')) {
                document.getElementById('personnelField').classList.remove('hidden');
            }
            
            if (activityType === 'mobile_patrol' || activityType === 'motor_patrol') {
                document.getElementById('vehicleField').classList.remove('hidden');
                document.getElementById('personnelField').classList.remove('hidden');
            }
            
            if (activityType === 'checkpoint') {
                document.getElementById('checkpointFields').classList.remove('hidden');
                document.getElementById('personnelField').classList.remove('hidden');
            }
            
            if (activityType === 'oplan_bakal' || activityType === 'oplan_sita') {
                document.getElementById('oplanFields').classList.remove('hidden');
                document.getElementById('personnelField').classList.remove('hidden');
            }
        }

        // Submit activity - this sends data to the server/admin
        function submitActivity(event) {
            event.preventDefault();
            
            if (!document.getElementById('selectedLat').value) {
                alert('Please select a location on the map first.');
                return;
            }
            
            // Collect all form data
            const formData = {
                location: document.getElementById('specificLocation').value,
                coordinates: `${document.getElementById('selectedLat').value}, ${document.getElementById('selectedLng').value}`,
                barangay: document.getElementById('selectedBarangay').value,
                activity_type: document.querySelector('select[name="activity_type"]').value,
                date: document.querySelector('input[name="date"]').value,
                time: document.querySelector('input[name="time"]').value,
                personnel: document.querySelector('input[name="personnel"]')?.value || '1',
                accomplishment: document.querySelector('textarea[name="accomplishment"]').value
            };
            
            // Check if checkpoint has additional data
            if (formData.activity_type === 'checkpoint') {
                formData.border_control_ops = document.querySelector('input[name="border_control_ops"]')?.value || '0';
                formData.mobile_checkpoint_ops = document.querySelector('input[name="mobile_checkpoint_ops"]')?.value || '0';
                formData.tct_ovr = document.querySelector('input[name="tct_ovr"]')?.value || '0';
            }
            
            // Check if oplan has additional data
            if (formData.activity_type === 'oplan_bakal' || formData.activity_type === 'oplan_sita') {
                formData.oplan_ops = document.querySelector('input[name="oplan_ops"]')?.value || '1';
                formData.oplan_arrests = document.querySelector('input[name="oplan_arrests"]')?.value || '0';
            }
            
            // Log to console for debugging
            console.log('Submitting to admin:', formData);
            
            alert('✓ Activity Reported Successfully!\n\nYour accomplishment has been recorded and will appear in admin reports.');
            
            // Reset form
            event.target.reset();
            resetMapView();
            
            // Reset date/time to current Philippine time
            setPhilippineTime();
        }

        // Dropdown toggle
        function toggleDropdown(element) {
            const parent = element.closest('.dropdown');
            parent.classList.toggle('active');
            const arrow = element.querySelector('.fa-chevron-down');
            if (arrow) arrow.classList.toggle('rotate-180');
        }
    </script>
</body>
</html>