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
        @media (max-width: 640px) {
            .sidebar-mobile {
                position: fixed;
                left: -100%;
                transition: left 0.3s ease;
                z-index: 50;
                width: 80%;
                max-width: 280px;
            }
            .sidebar-mobile.open { left: 0; }
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
            <h2 class="text-lg md:text-xl font-semibold">PNP Admin</h2>
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
                <a href="../index.php" class="text-white no-underline block text-sm md:text-base"><i class="fas fa-sign-out-alt mr-3 w-5"></i> Logout</a>
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
                <i class="fas fa-file-alt mr-1"></i> Report #: RPT-2026-06-10-001
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
                            <div class="bg-[#1f6fb2] w-16 h-16 rounded-full flex items-center justify-center text-white text-2xl font-bold">JD</div>
                            <div>
                                <h4 class="font-bold text-lg">PO3 Juan Dela Cruz</h4>
                                <p class="text-sm text-gray-600">Badge: PNP-2024-0123</p>
                                <p class="text-sm text-gray-500 mt-1"><i class="fas fa-map-marker-alt mr-1 text-red-400"></i> Manolo Fortich MPS</p>
                            </div>
                            <div class="ml-auto">
                                <span class="bg-green-100 text-green-700 px-3 py-1.5 rounded-full text-xs font-semibold">Approved</span>
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
                                <p class="font-semibold text-lg"><i class="fas fa-walking text-blue-500 mr-2"></i>Foot Patrol</p>
                            </div>
                            
                            <!-- Date & Time -->
                            <div class="bg-gray-50 p-3 rounded-lg border-l-4 border-green-500">
                                <p class="text-xs text-gray-500">Date & Time</p>
                                <p class="font-semibold"><i class="fas fa-calendar text-green-500 mr-2"></i>June 10, 2026 - 9:30 AM</p>
                            </div>
                            
                            <!-- Barangay -->
                            <div class="bg-gray-50 p-3 rounded-lg border-l-4 border-purple-500">
                                <p class="text-xs text-gray-500">Barangay</p>
                                <p class="font-semibold"><i class="fas fa-map-pin text-purple-500 mr-2"></i>Tankulan</p>
                            </div>
                            
                            <!-- Specific Location -->
                            <div class="bg-gray-50 p-3 rounded-lg border-l-4 border-yellow-500">
                                <p class="text-xs text-gray-500">Specific Location</p>
                                <p class="font-semibold"><i class="fas fa-location-dot text-yellow-500 mr-2"></i>Poblacion, near Municipal Hall</p>
                            </div>
                            
                            <!-- Personnel Deployed -->
                            <div class="bg-gray-50 p-3 rounded-lg border-l-4 border-red-500">
                                <p class="text-xs text-gray-500">Personnel Deployed</p>
                                <p class="font-semibold"><i class="fas fa-users text-red-500 mr-2"></i>4 personnel</p>
                            </div>
                            
                            <!-- Duration -->
                            <div class="bg-gray-50 p-3 rounded-lg border-l-4 border-indigo-500">
                                <p class="text-xs text-gray-500">Duration</p>
                                <p class="font-semibold"><i class="fas fa-clock text-indigo-500 mr-2"></i>2.5 hours</p>
                            </div>
                        </div>

                        <!-- GPS Coordinates Card -->
                        <div class="mt-4 bg-gray-50 p-3 rounded-lg border-l-4 border-gray-700">
                            <p class="text-xs text-gray-500">GPS Coordinates</p>
                            <p class="font-mono text-sm"><i class="fas fa-satellite text-gray-700 mr-2"></i>8.369800° N, 124.863400° E (Accuracy: 5.2m)</p>
                        </div>
                    </div>
                </div>

                <!-- Accomplishment Description Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-[#08324f] text-white px-4 py-3">
                        <h3 class="font-semibold"><i class="fas fa-trophy text-yellow-400 mr-2"></i> Accomplishment Description</h3>
                    </div>
                    <div class="p-4">
                        <div class="bg-blue-50 p-4 rounded-lg italic text-gray-700 border border-blue-100">
                            "Conducted routine foot patrol around public market area. Assisted 3 senior citizens crossing the street. Checked 15 establishments for compliance. No unusual incidents reported. All businesses following regulations."
                        </div>
                    </div>
                </div>

                <!-- Admin Remarks Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-[#08324f] text-white px-4 py-3">
                        <h3 class="font-semibold"><i class="fas fa-comment text-yellow-400 mr-2"></i> Admin Remarks</h3>
                    </div>
                    <div class="p-4">
                        <textarea class="w-full p-3 border border-gray-300 rounded-lg text-sm" rows="3">Report verified. Good work. Approved.</textarea>
                        <div class="flex flex-wrap gap-2 mt-3 justify-end">
                            <button class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700"><i class="fas fa-check mr-2"></i> Approve</button>
                            <button class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700"><i class="fas fa-times mr-2"></i> Reject</button>
                            <button class="bg-[#1f6fb2] text-white px-4 py-2 rounded-lg text-sm hover:bg-[#0a3d62]"><i class="fas fa-save mr-2"></i> Save Remarks</button>
                        </div>
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
                        <a href="https://www.google.com/maps?q=8.3698,124.8634" target="_blank" class="mt-3 block text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm transition">
                            <i class="fas fa-external-link-alt mr-2"></i> Open in Google Maps
                        </a>
                    </div>
                </div>

                <!-- Photo Evidence Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-[#08324f] text-white px-4 py-3 flex items-center justify-between">
                        <h3 class="font-semibold"><i class="fas fa-images text-yellow-400 mr-2"></i> Photo Evidence</h3>
                        <span class="bg-yellow-400 text-[#08324f] px-2 py-1 rounded-full text-xs font-bold">3 Photos</span>
                    </div>
                    <div class="p-4">
                        <div class="photo-grid">
                            <a href="https://via.placeholder.com/800x600/1f6fb2/ffffff?text=Patrol" data-lightbox="report-photos"><img src="https://via.placeholder.com/150x150/1f6fb2/ffffff?text=Photo+1" class="photo-item"></a>
                            <a href="https://via.placeholder.com/800x600/22c55e/ffffff?text=Market" data-lightbox="report-photos"><img src="https://via.placeholder.com/150x150/22c55e/ffffff?text=Photo+2" class="photo-item"></a>
                            <a href="https://via.placeholder.com/800x600/eab308/ffffff?text=Assistance" data-lightbox="report-photos"><img src="https://via.placeholder.com/150x150/eab308/ffffff?text=Photo+3" class="photo-item"></a>
                        </div>
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
                                <span class="font-mono font-medium">RPT-2026-06-10-001</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-500">Submitted:</span>
                                <span>2026-06-10 09:30:45</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-500">Device:</span>
                                <span class="font-mono">PNP-MOBILE-023</span>
                            </div>
                            <div class="flex justify-between py-2">
                                <span class="text-gray-500">IP Address:</span>
                                <span class="font-mono">192.168.1.105</span>
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
            const map = L.map('map').setView([8.3698, 124.8634], 17);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
            L.marker([8.3698, 124.8634]).addTo(map).bindPopup('Report Location').openPopup();
            L.circle([8.3698, 124.8634], { radius: 5.2, color: '#1f6fb2', fillOpacity: 0.1 }).addTo(map);
        });

        // Lightbox
        lightbox.option({ 'resizeDuration': 200, 'wrapAround': true });
    </script>
</body>
</html>