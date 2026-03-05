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
        .dropdown-content { display: none; }
        .dropdown.active .dropdown-content { display: block; }
        .rotate-180 { transform: rotate(180deg); }
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
                <h2 class="text-xl md:text-2xl font-bold text-[#08324f]">User Profile</h2>
            </div>
            <span class="bg-blue-100 text-blue-700 px-3 py-1.5 rounded-full text-xs font-semibold">
                <i class="fas fa-user mr-1"></i> User ID: #U-2024-0123
            </span>
        </div>

        <!-- User Profile Card -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden max-w-4xl mx-auto">
            <!-- Profile Header -->
            <div class="bg-[#08324f] p-6 text-white relative">
                <div class="flex flex-col sm:flex-row items-center gap-6">
                    <!-- Profile Avatar -->
                    <div class="w-24 h-24 bg-yellow-400 rounded-full flex items-center justify-center text-[#08324f] text-3xl font-bold border-4 border-white shadow-lg">
                        JD
                    </div>
                    <div class="text-center sm:text-left">
                        <h1 class="text-2xl font-bold">PO3 Juan Dela Cruz</h1>
                        <p class="text-yellow-400 mt-1">Badge Number: PNP-2024-0123</p>
                        <p class="text-sm text-gray-300 mt-2"><i class="fas fa-map-marker-alt mr-2"></i> Manolo Fortich MPS</p>
                    </div>
                    <div class="ml-auto hidden sm:block">
                        <span class="bg-green-500 text-white px-4 py-2 rounded-full text-sm font-semibold">
                            <i class="fas fa-circle text-xs mr-2 animate-pulse"></i> ACTIVE
                        </span>
                    </div>
                </div>
            </div>

            <!-- Profile Details -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Personal Information -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-[#08324f] border-b pb-2 flex items-center gap-2">
                            <i class="fas fa-user-circle text-yellow-500"></i> Personal Information
                        </h3>
                        
                        <div class="grid grid-cols-3 gap-2 text-sm">
                            <span class="text-gray-500">Full Name:</span>
                            <span class="col-span-2 font-medium">Juan M. Dela Cruz</span>
                            
                            <span class="text-gray-500">Rank:</span>
                            <span class="col-span-2 font-medium">Police Officer 3 (PO3)</span>
                            
                            <span class="text-gray-500">Birthdate:</span>
                            <span class="col-span-2 font-medium">March 15, 1990</span>
                            
                            <span class="text-gray-500">Age:</span>
                            <span class="col-span-2 font-medium">36 years old</span>
                            
                            <span class="text-gray-500">Gender:</span>
                            <span class="col-span-2 font-medium">Male</span>
                            
                            <span class="text-gray-500">Civil Status:</span>
                            <span class="col-span-2 font-medium">Married</span>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-[#08324f] border-b pb-2 flex items-center gap-2">
                            <i class="fas fa-address-card text-yellow-500"></i> Contact Information
                        </h3>
                        
                        <div class="grid grid-cols-3 gap-2 text-sm">
                            <span class="text-gray-500">Email:</span>
                            <span class="col-span-2 font-medium">juan.delacruz@pnp.gov.ph</span>
                            
                            <span class="text-gray-500">Contact #:</span>
                            <span class="col-span-2 font-medium">0912-345-6789</span>
                            
                            <span class="text-gray-500">Address:</span>
                            <span class="col-span-2 font-medium">Poblacion, Tankulan, Manolo Fortich</span>
                            
                            <span class="text-gray-500">Emergency Contact:</span>
                            <span class="col-span-2 font-medium">Maria Dela Cruz - 0918-765-4321</span>
                        </div>
                    </div>

                    <!-- Assignment Details -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-[#08324f] border-b pb-2 flex items-center gap-2">
                            <i class="fas fa-briefcase text-yellow-500"></i> Assignment Details
                        </h3>
                        
                        <div class="grid grid-cols-3 gap-2 text-sm">
                            <span class="text-gray-500">Station:</span>
                            <span class="col-span-2 font-medium">Manolo Fortich MPS</span>
                            
                            <span class="text-gray-500">Unit:</span>
                            <span class="col-span-2 font-medium">Patrol Unit</span>
                            
                            <span class="text-gray-500">Role:</span>
                            <span class="col-span-2 font-medium">Patrol Officer</span>
                            
                            <span class="text-gray-500">Date Hired:</span>
                            <span class="col-span-2 font-medium">January 15, 2020</span>
                            
                            <span class="text-gray-500">Years of Service:</span>
                            <span class="col-span-2 font-medium">6 years</span>
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-[#08324f] border-b pb-2 flex items-center gap-2">
                            <i class="fas fa-lock text-yellow-500"></i> Account Information
                        </h3>
                        
                        <div class="grid grid-cols-3 gap-2 text-sm">
                            <span class="text-gray-500">Username:</span>
                            <span class="col-span-2 font-medium">juan.delacruz</span>
                            
                            <span class="text-gray-500">Last Login:</span>
                            <span class="col-span-2 font-medium">June 10, 2026 08:30 AM</span>
                            
                            <span class="text-gray-500">Last IP:</span>
                            <span class="col-span-2 font-medium">192.168.1.105</span>
                            
                            <span class="text-gray-500">Account Status:</span>
                            <span class="col-span-2"><span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Active</span></span>
                        </div>
                    </div>
                </div>

                <!-- Statistics Summary -->
                <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg text-center">
                        <p class="text-2xl font-bold text-[#08324f]">156</p>
                        <p class="text-xs text-gray-600">Total Patrols</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg text-center">
                        <p class="text-2xl font-bold text-[#08324f]">89</p>
                        <p class="text-xs text-gray-600">Checkpoints</p>
                    </div>
                    <div class="bg-yellow-50 p-4 rounded-lg text-center">
                        <p class="text-2xl font-bold text-[#08324f]">67</p>
                        <p class="text-xs text-gray-600">Oplan Ops</p>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg text-center">
                        <p class="text-2xl font-bold text-[#08324f]">98%</p>
                        <p class="text-xs text-gray-600">Accomplishment Rate</p>
                    </div>
                </div>

                <!-- Recent Activities by this User -->
                <div class="mt-8">
                    <h3 class="text-lg font-semibold text-[#08324f] mb-4 flex items-center gap-2">
                        <i class="fas fa-history text-yellow-500"></i> Recent Activities by this User
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-2 text-left text-xs">Date</th>
                                    <th class="p-2 text-left text-xs">Activity Type</th>
                                    <th class="p-2 text-left text-xs">Location</th>
                                    <th class="p-2 text-left text-xs">Status</th>
                                    <th class="p-2 text-left text-xs">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="p-2 text-sm">June 10, 2026</td>
                                    <td class="p-2 text-sm">Foot Patrol</td>
                                    <td class="p-2 text-sm">Tankulan</td>
                                    <td class="p-2"><span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Approved</span></td>
                                    <td class="p-2"><a href="view_report.php?id=1" class="text-blue-600 hover:underline text-sm">View</a></td>
                                </tr>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="p-2 text-sm">June 9, 2026</td>
                                    <td class="p-2 text-sm">Checkpoint</td>
                                    <td class="p-2 text-sm">Alae</td>
                                    <td class="p-2"><span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Approved</span></td>
                                    <td class="p-2"><a href="view_report.php?id=2" class="text-blue-600 hover:underline text-sm">View</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-8 flex flex-wrap gap-3 justify-end border-t pt-4">
                    <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-edit mr-2"></i> Edit User
                    </button>
                    <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-ban mr-2"></i> Deactivate Account
                    </button>
                    <button class="bg-[#1f6fb2] hover:bg-[#0a3d62] text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-key mr-2"></i> Reset Password
                    </button>
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
    </script>
</body>
</html>