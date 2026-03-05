<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../image/pnplogo.png">
    <title>PNP | Admin Dashboard</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dropdown-content { display: none; }
        .dropdown.active .dropdown-content { display: block; }
        .rotate-180 { transform: rotate(180deg); }
        .stat-card {
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.2);
        }
        
        /* PNP Official Colors */
        .pnp-navy { background-color: #0a2b3c; }
        .pnp-navy-light { background-color: #1e4a6a; }
        .pnp-gold { color: #ffc107; }
        .pnp-gold-bg { background-color: #ffc107; }
        .pnp-red { background-color: #c41e3a; }
        .pnp-red-light { background-color: #dc3545; }
        
        /* Gradient Cards */
        .gradient-patrol {
            background: linear-gradient(135deg, #0a2b3c 0%, #1e4a6a 100%);
        }
        .gradient-checkpoint {
            background: linear-gradient(135deg, #c41e3a 0%, #dc3545 100%);
        }
        .gradient-oplan {
            background: linear-gradient(135deg, #0a2b3c 0%, #1e4a6a 100%);
        }
    </style>
</head>
<body class="flex bg-[#0a2b3c]">

    <!-- Sidebar - PNP Navy -->
    <div class="w-[240px] h-screen bg-[#08324f] text-white p-5 sticky top-0 overflow-y-auto">
        <div class="flex items-center gap-3 mb-6 pb-3 border-b border-[#1e4a6a]">
            <img src="../image/pnplogo.png" class="w-8 h-8 object-contain" alt="PNP Logo">
            <h2 class="text-xl font-semibold">PNP Admin</h2>
        </div>

        <ul class="space-y-1">
            <li class="p-3 rounded bg-[#1e4a6a] border-l-4 border-[#ffc107]">
                <a href="admin_dashboard.php" class="text-white no-underline block">
                    <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                </a>
            </li>

            <li class="p-3 rounded hover:bg-[#1e4a6a] cursor-pointer">
                <a href="checkpoint.php" class="text-white no-underline block">
                    <i class="fas fa-map-marker-alt mr-3"></i> Checkpoint
                </a>
            </li>

            <li class="dropdown">
                <div class="p-3 rounded hover:bg-[#1e4a6a] cursor-pointer flex items-center justify-between" onclick="toggleDropdown(this)">
                    <span><i class="fas fa-walking mr-3"></i> Patrol</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300"></i>
                </div>
                <ul class="pl-8 mt-1 space-y-1 dropdown-content">
                    <li class="py-2 px-3 text-sm hover:bg-[#1e4a6a] rounded"><a href="footpatrol.php" class="text-white no-underline block">Foot Patrol</a></li>
                    <li class="py-2 px-3 text-sm hover:bg-[#1e4a6a] rounded"><a href="mobilepatrol.php" class="text-white no-underline block">Mobile Patrol</a></li>
                    <li class="py-2 px-3 text-sm hover:bg-[#1e4a6a] rounded"><a href="motorpatrol.php" class="text-white no-underline block">Motorcycle Patrol</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <div class="p-3 rounded hover:bg-[#1e4a6a] cursor-pointer flex items-center justify-between" onclick="toggleDropdown(this)">
                    <span><i class="fas fa-shield-alt mr-3"></i> Oplan Bakal / Sita</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300"></i>
                </div>
                <ul class="pl-8 mt-1 space-y-1 dropdown-content">
                    <li class="py-2 px-3 text-sm hover:bg-[#1e4a6a] rounded"><a href="oplanbakal.php" class="text-white no-underline block">Oplan Bakal</a></li>
                    <li class="py-2 px-3 text-sm hover:bg-[#1e4a6a] rounded"><a href="oplansita.php" class="text-white no-underline block">Oplan Sita</a></li>
                </ul>
            </li>

            <li class="p-3 rounded hover:bg-[#1e4a6a] cursor-pointer">
                <a href="admin_users.php" class="text-white no-underline block">
                    <i class="fas fa-users mr-3"></i> Users
                </a>
            </li>
            
            <li class="p-3 rounded hover:bg-[#1e4a6a] cursor-pointer">
                <a href="all_reports.php" class="text-white no-underline block">
                    <i class="fas fa-file-alt mr-3"></i> Accomplishment Report
                </a>
            </li>

            <li class="p-3 rounded hover:bg-[#1e4a6a] cursor-pointer mt-5 pt-4 border-t border-[#1e4a6a]">
                <a href="../index.php" class="text-white no-underline block">
                    <i class="fas fa-sign-out-alt mr-3"></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content - NO CHARTS, ONLY CARDS -->
    <div class="flex-1 p-8 bg-[#eef2f6] overflow-y-auto h-screen">
        
        <!-- Header with PNP Gold Accent -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6 border-l-4 border-[#ffc107]">
            <h2 class="text-2xl font-bold text-[#0a2b3c]">Dashboard Overview</h2>
            <p class="text-gray-600 mt-1">Welcome back. System monitoring panel.</p>
        </div>

        <!-- QUICK STATS ROW - Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-5 rounded-lg shadow-md border-l-4 border-[#0a2b3c]">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Total Personnel</p>
                        <p class="text-3xl font-bold text-[#0a2b3c] mt-1">24</p>
                    </div>
                    <div class="w-12 h-12 bg-[#0a2b3c] bg-opacity-10 rounded-full flex items-center justify-center">
                        <i class="fas fa-users text-[#0a2b3c] text-xl"></i>
                    </div>
                </div>
                <p class="text-xs text-green-600 mt-2"><i class="fas fa-arrow-up mr-1"></i> 3 new this month</p>
            </div>
            
            <div class="bg-white p-5 rounded-lg shadow-md border-l-4 border-[#0a2b3c]">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Active Officers</p>
                        <p class="text-3xl font-bold text-[#0a2b3c] mt-1">18</p>
                    </div>
                    <div class="w-12 h-12 bg-[#0a2b3c] bg-opacity-10 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-check text-[#0a2b3c] text-xl"></i>
                    </div>
                </div>
                <p class="text-xs text-green-600 mt-2"><i class="fas fa-chart-line mr-1"></i> 75% participation</p>
            </div>
            
            <div class="bg-white p-5 rounded-lg shadow-md border-l-4 border-[#c41e3a]">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Total Operations</p>
                        <p class="text-3xl font-bold text-[#0a2b3c] mt-1">585</p>
                    </div>
                    <div class="w-12 h-12 bg-[#c41e3a] bg-opacity-10 rounded-full flex items-center justify-center">
                        <i class="fas fa-calendar-check text-[#c41e3a] text-xl"></i>
                    </div>
                </div>
                <p class="text-xs text-green-600 mt-2"><i class="fas fa-chart-line mr-1"></i> +12% vs last month</p>
            </div>
            
            <div class="bg-white p-5 rounded-lg shadow-md border-l-4 border-[#ffc107]">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Success Rate</p>
                        <p class="text-3xl font-bold text-[#0a2b3c] mt-1">96%</p>
                    </div>
                    <div class="w-12 h-12 bg-[#ffc107] bg-opacity-10 rounded-full flex items-center justify-center">
                        <i class="fas fa-trophy text-[#ffc107] text-xl"></i>
                    </div>
                </div>
                <p class="text-xs text-green-600 mt-2"><i class="fas fa-star mr-1"></i> Excellent performance</p>
            </div>
        </div>

        <!-- PATROL STATISTICS CARDS -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-[#0a2b3c] mb-4 flex items-center gap-2">
                <i class="fas fa-walking text-[#ffc107]"></i> Patrol Statistics
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <!-- Foot Patrol Card -->
                <div class="stat-card gradient-patrol p-5 rounded-lg shadow-lg text-white">
                    <div class="flex items-center justify-between mb-3">
                        <i class="fas fa-walking text-3xl opacity-80"></i>
                        <span class="text-xs bg-white/20 px-3 py-1 rounded-full text-[#ffc107]">Daily: 24</span>
                    </div>
                    <p class="text-3xl font-bold">156</p>
                    <p class="text-sm opacity-90">Total Foot Patrols</p>
                    <div class="mt-4 pt-3 border-t border-white/20 flex justify-between text-xs">
                        <span><i class="fas fa-arrow-up text-[#ffc107] mr-1"></i> +3 today</span>
                        <span>12 assists</span>
                    </div>
                </div>

                <!-- Mobile Patrol Card -->
                <div class="stat-card gradient-patrol p-5 rounded-lg shadow-lg text-white">
                    <div class="flex items-center justify-between mb-3">
                        <i class="fas fa-car text-3xl opacity-80"></i>
                        <span class="text-xs bg-white/20 px-3 py-1 rounded-full text-[#ffc107]">Daily: 24</span>
                    </div>
                    <p class="text-3xl font-bold">142</p>
                    <p class="text-sm opacity-90">Total Mobile Patrols</p>
                    <div class="mt-4 pt-3 border-t border-white/20 flex justify-between text-xs">
                        <span><i class="fas fa-arrow-up text-[#ffc107] mr-1"></i> +5 today</span>
                        <span>32 citations</span>
                    </div>
                </div>

                <!-- Motorcycle Patrol Card -->
                <div class="stat-card gradient-patrol p-5 rounded-lg shadow-lg text-white">
                    <div class="flex items-center justify-between mb-3">
                        <i class="fas fa-motorcycle text-3xl opacity-80"></i>
                        <span class="text-xs bg-white/20 px-3 py-1 rounded-full text-[#ffc107]">Daily: 24</span>
                    </div>
                    <p class="text-3xl font-bold">134</p>
                    <p class="text-sm opacity-90">Total Motor Patrols</p>
                    <div class="mt-4 pt-3 border-t border-white/20 flex justify-between text-xs">
                        <span><i class="fas fa-arrow-up text-[#ffc107] mr-1"></i> +2 today</span>
                        <span>28 citations</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- CHECKPOINT & OPLAN STATISTICS - Side by Side -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- CHECKPOINT STATISTICS -->
            <div>
                <h3 class="text-lg font-semibold text-[#0a2b3c] mb-4 flex items-center gap-2">
                    <i class="fas fa-map-marker-alt text-[#ffc107]"></i> Checkpoint Statistics
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <!-- Total Checkpoints -->
                    <div class="stat-card gradient-checkpoint p-5 rounded-lg shadow-lg text-white">
                        <i class="fas fa-map-pin text-3xl opacity-80 mb-3 block"></i>
                        <p class="text-3xl font-bold">89</p>
                        <p class="text-sm opacity-90">Total Checkpoints</p>
                        <div class="mt-3 flex justify-between text-xs">
                            <span class="bg-white/20 px-2 py-1 rounded">This month</span>
                        </div>
                    </div>
                    <!-- Border Control -->
                    <div class="stat-card gradient-checkpoint p-5 rounded-lg shadow-lg text-white">
                        <i class="fas fa-border-all text-3xl opacity-80 mb-3 block"></i>
                        <p class="text-3xl font-bold">45</p>
                        <p class="text-sm opacity-90">Border Control</p>
                        <div class="mt-3 flex justify-between text-xs">
                            <span class="bg-white/20 px-2 py-1 rounded">156 personnel</span>
                        </div>
                    </div>
                    <!-- Mobile Checkpoint -->
                    <div class="stat-card gradient-checkpoint p-5 rounded-lg shadow-lg text-white">
                        <i class="fas fa-truck text-3xl opacity-80 mb-3 block"></i>
                        <p class="text-3xl font-bold">32</p>
                        <p class="text-sm opacity-90">Mobile Checkpoint</p>
                        <div class="mt-3 flex justify-between text-xs">
                            <span class="bg-white/20 px-2 py-1 rounded">98 personnel</span>
                        </div>
                    </div>
                    <!-- Overlapping -->
                    <div class="stat-card gradient-checkpoint p-5 rounded-lg shadow-lg text-white">
                        <i class="fas fa-sync-alt text-3xl opacity-80 mb-3 block"></i>
                        <p class="text-3xl font-bold">28</p>
                        <p class="text-sm opacity-90">Overlapping Ops</p>
                        <div class="mt-3 flex justify-between text-xs">
                            <span class="bg-white/20 px-2 py-1 rounded">This week</span>
                        </div>
                    </div>
                </div>
                <!-- Checkpoint Accomplishments Summary -->
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div class="bg-white p-3 rounded-lg shadow-sm border-l-2 border-[#c41e3a]">
                        <p class="text-xs text-gray-500">TCT/OVR</p>
                        <p class="text-xl font-bold text-[#0a2b3c]">28</p>
                    </div>
                    <div class="bg-white p-3 rounded-lg shadow-sm border-l-2 border-[#c41e3a]">
                        <p class="text-xs text-gray-500">Arrests</p>
                        <p class="text-xl font-bold text-[#0a2b3c]">15</p>
                    </div>
                </div>
            </div>

            <!-- OPLAN STATISTICS -->
            <div>
                <h3 class="text-lg font-semibold text-[#0a2b3c] mb-4 flex items-center gap-2">
                    <i class="fas fa-shield-alt text-[#ffc107]"></i> Oplan Statistics
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <!-- Total Oplan -->
                    <div class="stat-card gradient-patrol p-5 rounded-lg shadow-lg text-white">
                        <i class="fas fa-shield-alt text-3xl opacity-80 mb-3 block"></i>
                        <p class="text-3xl font-bold">48</p>
                        <p class="text-sm opacity-90">Total Operations</p>
                    </div>
                    <!-- Oplan Bakal -->
                    <div class="stat-card gradient-patrol p-5 rounded-lg shadow-lg text-white">
                        <i class="fas fa-gun text-3xl opacity-80 mb-3 block"></i>
                        <p class="text-3xl font-bold">20</p>
                        <p class="text-sm opacity-90">Oplan Bakal</p>
                    </div>
                    <!-- Oplan Sita -->
                    <div class="stat-card gradient-patrol p-5 rounded-lg shadow-lg text-white">
                        <i class="fas fa-magnifying-glass text-3xl opacity-80 mb-3 block"></i>
                        <p class="text-3xl font-bold">28</p>
                        <p class="text-sm opacity-90">Oplan Sita</p>
                    </div>
                    <!-- Personnel -->
                    <div class="stat-card gradient-patrol p-5 rounded-lg shadow-lg text-white">
                        <i class="fas fa-users text-3xl opacity-80 mb-3 block"></i>
                        <p class="text-3xl font-bold">112</p>
                        <p class="text-sm opacity-90">Total Personnel</p>
                    </div>
                </div>
                <!-- Oplan Accomplishments Summary -->
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div class="bg-white p-3 rounded-lg shadow-sm border-l-2 border-[#0a2b3c]">
                        <p class="text-xs text-gray-500">Bakal Firearms</p>
                        <p class="text-xl font-bold text-[#0a2b3c]">8</p>
                    </div>
                    <div class="bg-white p-3 rounded-lg shadow-sm border-l-2 border-[#0a2b3c]">
                        <p class="text-xs text-gray-500">Bakal Arrests</p>
                        <p class="text-xl font-bold text-[#0a2b3c]">12</p>
                    </div>
                    <div class="bg-white p-3 rounded-lg shadow-sm border-l-2 border-[#ffc107]">
                        <p class="text-xs text-gray-500">Sita Contraband</p>
                        <p class="text-xl font-bold text-[#0a2b3c]">15 kg</p>
                    </div>
                    <div class="bg-white p-3 rounded-lg shadow-sm border-l-2 border-[#ffc107]">
                        <p class="text-xs text-gray-500">Sita Arrests</p>
                        <p class="text-xl font-bold text-[#0a2b3c]">23</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- TOP PERFORMERS - Simple Cards -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-[#0a2b3c] mb-4 flex items-center gap-2">
                <i class="fas fa-crown text-[#ffc107]"></i> Top Performing Officers
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Officer 1 -->
                <div class="bg-white p-4 rounded-lg shadow-md flex items-center gap-3">
                    <div class="w-12 h-12 bg-[#0a2b3c] rounded-full flex items-center justify-center text-[#ffc107] font-bold text-lg">PR</div>
                    <div>
                        <p class="font-semibold text-[#0a2b3c]">PO2 Pedro Reyes</p>
                        <p class="text-xs text-gray-500">72 total activities</p>
                    </div>
                </div>
                <!-- Officer 2 -->
                <div class="bg-white p-4 rounded-lg shadow-md flex items-center gap-3">
                    <div class="w-12 h-12 bg-[#0a2b3c] rounded-full flex items-center justify-center text-[#ffc107] font-bold text-lg">MS</div>
                    <div>
                        <p class="font-semibold text-[#0a2b3c]">SPO1 Maria Santos</p>
                        <p class="text-xs text-gray-500">68 total activities</p>
                    </div>
                </div>
                <!-- Officer 3 -->
                <div class="bg-white p-4 rounded-lg shadow-md flex items-center gap-3">
                    <div class="w-12 h-12 bg-[#0a2b3c] rounded-full flex items-center justify-center text-[#ffc107] font-bold text-lg">JC</div>
                    <div>
                        <p class="font-semibold text-[#0a2b3c]">PO3 Juan Dela Cruz</p>
                        <p class="text-xs text-gray-500">65 total activities</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- RECENT ACTIVITY MINI CARDS - Lightweight -->
        <div>
            <h3 class="text-lg font-semibold text-[#0a2b3c] mb-4 flex items-center gap-2">
                <i class="fas fa-clock text-[#ffc107]"></i> Recent Activities
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-3 rounded-lg shadow-sm border-l-4 border-blue-500">
                    <p class="text-xs text-gray-500">Today 9:30 AM</p>
                    <p class="font-medium">Foot Patrol - Tankulan</p>
                    <p class="text-xs text-gray-600">PO3 J. Dela Cruz</p>
                </div>
                <div class="bg-white p-3 rounded-lg shadow-sm border-l-4 border-red-500">
                    <p class="text-xs text-gray-500">Today 7:15 AM</p>
                    <p class="font-medium">Checkpoint - Alae</p>
                    <p class="text-xs text-gray-600">SPO1 M. Santos</p>
                </div>
                <div class="bg-white p-3 rounded-lg shadow-sm border-l-4 border-green-500">
                    <p class="text-xs text-gray-500">Yesterday 2:30 PM</p>
                    <p class="font-medium">Oplan Bakal - Dahilayan</p>
                    <p class="text-xs text-gray-600">PO2 P. Reyes</p>
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
    </script>
</body>
</html>