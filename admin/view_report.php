<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../image/pnplogo.png">
    <title>PNP | View Report Details</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Leaflet CSS (for map) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Lightbox for image gallery -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
    <style>
        .dropdown-content { display: none; }
        .dropdown.active .dropdown-content { display: block; }
        .rotate-180 { transform: rotate(180deg); }
        #map { height: 300px; width: 100%; border-radius: 12px; z-index: 1; }
        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
        }
        .photo-item {
            aspect-ratio: 1/1;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.3s;
        }
        .photo-item:hover {
            transform: scale(1.05);
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
    </style>
</head>
<body class="flex flex-col md:flex-row bg-[#0a3d62] min-h-screen">

    <!-- Mobile Menu Button -->
    <button id="mobileMenuBtn" class="md:hidden fixed top-4 left-4 z-50 bg-[#08324f] text-white p-3 rounded-lg shadow-lg">
        <i class="fas fa-bars text-xl"></i>
    </button>

    <!-- Mobile Menu Overlay -->
    <div id="menuOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden" onclick="closeMobileMenu()"></div>

    <!-- ===== SIDEBAR (Admin) ===== -->
    <div id="sidebar" class="w-full md:w-[240px] bg-[#08324f] text-white p-4 md:p-5 md:sticky md:top-0 md:h-screen overflow-y-auto sidebar-mobile fixed top-0 left-[-100%] h-screen z-50 transition-all duration-300 ease-in-out">
        <button id="closeSidebar" class="md:hidden absolute top-4 right-4 text-white text-xl">
            <i class="fas fa-times"></i>
        </button>
        
        <!-- Logo -->
        <div class="flex items-center gap-3 mb-6 pb-3 border-b border-[#1a4b6d] mt-12 md:mt-0">
            <img src="../image/pnplogo.png" class="w-8 h-8 md:w-10 md:h-10 object-contain" alt="PNP Logo">
            <h2 class="text-lg md:text-xl font-semibold">PNP Admin</h2>
        </div>

        <!-- Admin Profile -->
        <div class="bg-[#0a3d62] p-3 rounded-lg mb-4 text-center">
            <div class="w-12 h-12 bg-yellow-400 rounded-full mx-auto mb-2 flex items-center justify-center text-[#08324f] font-bold text-xl">A</div>
            <p class="text-sm font-semibold">Admin User</p>
            <p class="text-xs text-gray-300">administrator@pnp.gov.ph</p>
        </div>

        <!-- Menu -->
        <ul class="space-y-1">
            <li class="p-2 md:p-3 rounded hover:bg-[#0a3d62] transition">
                <a href="admin_dashboard.php" class="text-white no-underline block text-sm md:text-base">
                    <i class="fas fa-tachometer-alt mr-3 w-5"></i> Dashboard
                </a>
            </li>
            <li class="p-2 md:p-3 rounded hover:bg-[#0a3d62] transition">
                <a href="checkpoint.php" class="text-white no-underline block text-sm md:text-base">
                    <i class="fas fa-map-marker-alt mr-3 w-5"></i> Checkpoint
                </a>
            </li>
            <li class="dropdown">
                <div class="p-2 md:p-3 rounded hover:bg-[#0a3d62] cursor-pointer flex items-center justify-between transition" onclick="toggleDropdown(this)">
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
                <div class="p-2 md:p-3 rounded hover:bg-[#0a3d62] cursor-pointer flex items-center justify-between transition" onclick="toggleDropdown(this)">
                    <span class="text-sm md:text-base"><i class="fas fa-shield-alt mr-3 w-5"></i> Oplan Bakal / Sita</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300"></i>
                </div>
                <ul class="pl-8 md:pl-10 mt-1 space-y-1 dropdown-content">
                    <li class="py-1 md:py-2 px-2 md:px-3 text-xs md:text-sm hover:bg-[#0a3d62] rounded"><a href="oplanbakal.php" class="text-white no-underline block">Oplan Bakal</a></li>
                    <li class="py-1 md:py-2 px-2 md:px-3 text-xs md:text-sm hover:bg-[#0a3d62] rounded"><a href="oplansita.php" class="text-white no-underline block">Oplan Sita</a></li>
                </ul>
            </li>
            <li class="p-2 md:p-3 rounded bg-[#0a3d62] border-l-4 border-yellow-400">
                <a href="admin_users.php" class="text-white no-underline block text-sm md:text-base">
                    <i class="fas fa-users mr-3 w-5"></i> Users
                </a>
            </li>
            <li class="p-2 md:p-3 rounded hover:bg-[#0a3d62] transition mt-5 pt-4 border-t border-[#1a4b6d]">
                <a href="../index.php" class="text-white no-underline block text-sm md:text-base">
                    <i class="fas fa-sign-out-alt mr-3 w-5"></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="flex-1 p-3 md:p-6 lg:p-8 bg-[#eef2f6] overflow-y-auto min-h-screen">
        
        <!-- Header with Back Button -->
        <div class="bg-white p-3 md:p-4 rounded-lg shadow-sm mb-4 md:mb-6 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="javascript:history.back()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i>
                    <span class="hidden sm:inline">Back</span>
                </a>
                <h2 class="text-xl md:text-2xl font-bold text-[#08324f]">Report Details</h2>
            </div>
            <div class="flex gap-2">
                <span class="bg-green-100 text-green-700 px-3 py-1.5 rounded-full text-xs font-semibold flex items-center">
                    <i class="fas fa-check-circle mr-1"></i> Report ID: #2026-1234
                </span>
            </div>
        </div>

        <!-- Report Details Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
            
            <!-- Left Column - Report Info (2/3 width) -->
            <div class="lg:col-span-2 space-y-4 md:space-y-6">
                
                <!-- Personnel Information Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-[#08324f] text-white px-4 md:px-6 py-3 flex items-center gap-2">
                        <i class="fas fa-user-shield text-yellow-400"></i>
                        <h3 class="font-semibold">Personnel Information</h3>
                    </div>
                    <div class="p-4 md:p-6">
                        <div class="flex flex-col sm:flex-row gap-4 items-start">
                            <!-- Personnel Avatar -->
                            <div class="bg-[#1f6fb2] w-16 h-16 rounded-full flex items-center justify-center text-white text-2xl font-bold border-3 border-yellow-400">
                                JD
                            </div>
                            <!-- Personnel Details -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 flex-1">
                                <div>
                                    <p class="text-xs text-gray-500">Full Name</p>
                                    <p class="font-semibold">PO3 Juan Dela Cruz</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Badge Number</p>
                                    <p class="font-semibold">PNP-2024-0123</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Rank</p>
                                    <p class="font-semibold">Police Officer 3</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Station</p>
                                    <p class="font-semibold">Manolo Fortich MPS</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Contact</p>
                                    <p class="font-semibold">0912-345-6789</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Email</p>
                                    <p class="font-semibold">juan.delacruz@pnp.gov.ph</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Details Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-[#08324f] text-white px-4 md:px-6 py-3 flex items-center gap-2">
                        <i class="fas fa-clipboard-list text-yellow-400"></i>
                        <h3 class="font-semibold">Activity Details</h3>
                    </div>
                    <div class="p-4 md:p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500">Activity Type</p>
                                <p class="font-semibold text-lg text-[#08324f]">Foot Patrol</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Status</p>
                                <p><span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs">Approved</span></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Date Reported</p>
                                <p class="font-semibold">June 10, 2026</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Time Reported</p>
                                <p class="font-semibold">9:30 AM</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Barangay</p>
                                <p class="font-semibold">Tankulan</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Specific Location</p>
                                <p class="font-semibold">Poblacion, near Municipal Hall</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Personnel Deployed</p>
                                <p class="font-semibold">4 personnel</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Duration</p>
                                <p class="font-semibold">2.5 hours</p>
                            </div>
                        </div>

                        <!-- Checkpoint Specific Fields (if applicable) -->
                        <div class="mt-4 p-4 bg-gray-50 rounded-lg hidden" id="checkpointDetails">
                            <h4 class="font-medium text-sm mb-3 text-[#08324f]">Checkpoint Statistics</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <p class="text-xs text-gray-600">Border Control Ops</p>
                                    <p class="font-semibold">8</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-600">Mobile Checkpoint Ops</p>
                                    <p class="font-semibold">3</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-600">TCT/OVR Accomplishments</p>
                                    <p class="font-semibold">2</p>
                                </div>
                            </div>
                        </div>

                        <!-- Accomplishment Description -->
                        <div class="mt-4">
                            <p class="text-xs text-gray-500 mb-2">Accomplishment Description</p>
                            <div class="bg-gray-50 p-4 rounded-lg text-sm">
                                Conducted routine foot patrol around public market area. 
                                Assisted 3 senior citizens crossing the street. 
                                No unusual incidents reported. All establishments compliant with regulations.
                            </div>
                        </div>

                        <!-- Admin Notes/Remarks -->
                        <div class="mt-4 border-t pt-4">
                            <p class="text-xs text-gray-500 mb-2">Admin Remarks</p>
                            <textarea class="w-full p-3 border border-gray-300 rounded-lg text-sm" rows="2" placeholder="Add remarks...">Report verified and approved. Good work.</textarea>
                            <div class="flex justify-end gap-2 mt-2">
                                <button class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700">Approve</button>
                                <button class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700">Reject</button>
                                <button class="bg-[#1f6fb2] text-white px-4 py-2 rounded-lg text-sm hover:bg-[#0a3d62]">Save Remarks</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Map and Photos (1/3 width) -->
            <div class="space-y-4 md:space-y-6">
                
                <!-- Location Map Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-[#08324f] text-white px-4 md:px-6 py-3 flex items-center gap-2">
                        <i class="fas fa-map-marker-alt text-yellow-400"></i>
                        <h3 class="font-semibold">Report Location</h3>
                    </div>
                    <div class="p-4">
                        <!-- Map Container -->
                        <div id="map" class="w-full h-[250px] rounded-lg border-2 border-gray-200"></div>
                        
                        <!-- Location Details -->
                        <div class="mt-3 p-3 bg-blue-50 rounded-lg">
                            <p class="text-xs text-gray-700"><i class="fas fa-map-pin text-[#1f6fb2] mr-2"></i> <strong>Coordinates:</strong></p>
                            <p class="text-xs text-gray-600 mt-1">Latitude: 8.369800, Longitude: 124.863400</p>
                            <p class="text-xs text-gray-600">Accuracy: 5.2 meters</p>
                            <p class="text-xs text-gray-600 mt-2"><i class="fas fa-clock mr-1"></i> Recorded: June 10, 2026 9:30 AM</p>
                        </div>
                        
                        <!-- Google Maps Link -->
                        <a href="https://www.google.com/maps?q=8.3698,124.8634" target="_blank" class="mt-3 block text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm transition">
                            <i class="fas fa-external-link-alt mr-2"></i> Open in Google Maps
                        </a>
                    </div>
                </div>

                <!-- Photo Gallery Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-[#08324f] text-white px-4 md:px-6 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-images text-yellow-400"></i>
                            <h3 class="font-semibold">Photo Evidence</h3>
                        </div>
                        <span class="bg-yellow-400 text-[#08324f] px-2 py-1 rounded-full text-xs font-bold">3 Photos</span>
                    </div>
                    <div class="p-4">
                        <!-- Photo Grid -->
                        <div class="photo-grid">
                            <a href="https://via.placeholder.com/800x600/1f6fb2/ffffff?text=Patrol+Photo+1" data-lightbox="report-photos" data-title="Patrol Area - Morning Patrol">
                                <img src="https://via.placeholder.com/300x300/1f6fb2/ffffff?text=Photo+1" class="photo-item w-full h-full object-cover" alt="Patrol Photo">
                            </a>
                            <a href="https://via.placeholder.com/800x600/22c55e/ffffff?text=Market+Area" data-lightbox="report-photos" data-title="Public Market Area">
                                <img src="https://via.placeholder.com/300x300/22c55e/ffffff?text=Photo+2" class="photo-item w-full h-full object-cover" alt="Market Photo">
                            </a>
                            <a href="https://via.placeholder.com/800x600/eab308/ffffff?text=Assistance+Provided" data-lightbox="report-photos" data-title="Assisting Senior Citizens">
                                <img src="https://via.placeholder.com/300x300/eab308/ffffff?text=Photo+3" class="photo-item w-full h-full object-cover" alt="Assistance Photo">
                            </a>
                            <a href="https://via.placeholder.com/800x600/ef4444/ffffff?text=Checkpoint+Setup" data-lightbox="report-photos" data-title="Checkpoint Setup">
                                <img src="https://via.placeholder.com/300x300/ef4444/ffffff?text=Photo+4" class="photo-item w-full h-full object-cover" alt="Checkpoint Photo">
                            </a>
                        </div>

                        <!-- Photo Metadata -->
                        <div class="mt-4 space-y-2 text-xs text-gray-600 border-t pt-3">
                            <div class="flex justify-between">
                                <span><i class="fas fa-camera mr-2"></i> Total Photos:</span>
                                <span class="font-semibold">4</span>
                            </div>
                            <div class="flex justify-between">
                                <span><i class="fas fa-weight-hanging mr-2"></i> Total Size:</span>
                                <span class="font-semibold">12.5 MB</span>
                            </div>
                            <div class="flex justify-between">
                                <span><i class="fas fa-calendar mr-2"></i> Uploaded:</span>
                                <span class="font-semibold">June 10, 2026</span>
                            </div>
                        </div>

                        <!-- Download All Button -->
                        <button class="mt-3 w-full bg-[#1f6fb2] text-white py-2 rounded-lg hover:bg-[#0a3d62] transition text-sm flex items-center justify-center gap-2">
                            <i class="fas fa-download"></i> Download All Photos
                        </button>
                    </div>
                </div>

                <!-- Report Metadata Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-[#08324f] text-white px-4 md:px-6 py-3 flex items-center gap-2">
                        <i class="fas fa-info-circle text-yellow-400"></i>
                        <h3 class="font-semibold">Report Metadata</h3>
                    </div>
                    <div class="p-4 space-y-2 text-sm">
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-600">Report ID:</span>
                            <span class="font-mono">RPT-2026-06-10-001</span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-600">Submitted:</span>
                            <span>2026-06-10 09:30:45</span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-600">Last Updated:</span>
                            <span>2026-06-10 14:23:12</span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-600">Device ID:</span>
                            <span class="font-mono">PNP-MOBILE-023</span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span class="text-gray-600">IP Address:</span>
                            <span class="font-mono">192.168.1.105</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 bg-white p-4 rounded-lg shadow-md flex flex-wrap gap-3 justify-end">
            <button class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-print"></i> Print Report
            </button>
            <button class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-check"></i> Approve Report
            </button>
            <button class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-times"></i> Reject Report
            </button>
            <button class="bg-[#1f6fb2] hover:bg-[#0a3d62] text-white px-6 py-2 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-envelope"></i> Email Report
            </button>
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

        // Dropdown Toggle
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

        // Initialize Map with Report Location
        document.addEventListener('DOMContentLoaded', function() {
            // Report coordinates (example: Tankulan)
            const reportLat = 8.3698;
            const reportLng = 124.8634;
            
            const map = L.map('map').setView([reportLat, reportLng], 17);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            // Add marker for report location
            const marker = L.marker([reportLat, reportLng]).addTo(map);
            marker.bindPopup(`
                <b>Report Location</b><br>
                Foot Patrol - Tankulan<br>
                Reported: June 10, 2026 9:30 AM
            `).openPopup();

            // Add accuracy circle if available
            L.circle([reportLat, reportLng], {
                radius: 5.2,
                color: '#1f6fb2',
                fillColor: '#1f6fb2',
                fillOpacity: 0.1
            }).addTo(map);
        });

        // Lightbox configuration
        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true,
            'albumLabel': 'Photo %1 of %2'
        });
    </script>
</body>
</html>