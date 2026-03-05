<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../image/pnplogo.png">
    <title>PNP Manolo Fortich | Admin Dashboard</title>
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .animate-slideIn { animation: slideIn 0.3s ease; }
        .sidebar-item.active { background: #1e4a6a; border-left-color: #ffc107; }
        .modal { transition: all 0.3s ease; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-gray-100 font-sans">

    <!-- Main Container -->
    <div class="flex h-screen overflow-hidden">
        
        <!-- ========== SIDEBAR ========== -->
        <aside class="w-72 bg-[#0a2b3c] text-white flex flex-col shadow-xl">
            <div class="p-6 border-b border-[#1e4a6a]">
                <div class="flex items-center gap-3">
                    <i class="fas fa-shield-alt text-3xl text-[#ffc107]"></i>
                    <div>
                        <h1 class="text-xl font-bold">PNP Admin</h1>
                        <p class="text-xs text-gray-300">Manolo Fortich MPS</p>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto py-4">
                <nav class="space-y-1">
                    <!-- Dashboard -->
                    <a href="#" onclick="showSection('dashboard'); return false;" class="sidebar-item active flex items-center gap-3 px-6 py-3 text-gray-200 hover:bg-[#1e4a6a] transition border-l-4 border-transparent hover:border-[#ffc107]">
                        <i class="fas fa-tachometer-alt w-6"></i>
                        <span>Dashboard</span>
                    </a>
                    
                    <!-- User Management -->
                    <a href="#" onclick="showSection('users'); return false;" class="sidebar-item flex items-center gap-3 px-6 py-3 text-gray-200 hover:bg-[#1e4a6a] transition border-l-4 border-transparent hover:border-[#ffc107]">
                        <i class="fas fa-users-cog w-6"></i>
                        <span>User Management</span>
                    </a>
                    
                    <!-- Patrol Activities -->
                    <a href="#" onclick="showSection('patrols'); return false;" class="sidebar-item flex items-center gap-3 px-6 py-3 text-gray-200 hover:bg-[#1e4a6a] transition border-l-4 border-transparent hover:border-[#ffc107]">
                        <i class="fas fa-walking w-6"></i>
                        <span>Patrol Activities</span>
                    </a>
                    
                    <!-- Checkpoint Activities -->
                    <a href="#" onclick="showSection('checkpoints'); return false;" class="sidebar-item flex items-center gap-3 px-6 py-3 text-gray-200 hover:bg-[#1e4a6a] transition border-l-4 border-transparent hover:border-[#ffc107]">
                        <i class="fas fa-map-marker-alt w-6"></i>
                        <span>Checkpoint Ops</span>
                    </a>
                    
                    <!-- Oplan Activities -->
                    <a href="#" onclick="showSection('oplans'); return false;" class="sidebar-item flex items-center gap-3 px-6 py-3 text-gray-200 hover:bg-[#1e4a6a] transition border-l-4 border-transparent hover:border-[#ffc107]">
                        <i class="fas fa-shield-alt w-6"></i>
                        <span>Oplan Bakal/Sita</span>
                    </a>
                    
                    <!-- Barangay Management -->
                    <a href="#" onclick="showSection('barangays'); return false;" class="sidebar-item flex items-center gap-3 px-6 py-3 text-gray-200 hover:bg-[#1e4a6a] transition border-l-4 border-transparent hover:border-[#ffc107]">
                        <i class="fas fa-map-pin w-6"></i>
                        <span>Barangay Management</span>
                    </a>
                    
                    <!-- Reports -->
                    <a href="#" onclick="showSection('reports'); return false;" class="sidebar-item flex items-center gap-3 px-6 py-3 text-gray-200 hover:bg-[#1e4a6a] transition border-l-4 border-transparent hover:border-[#ffc107]">
                        <i class="fas fa-chart-bar w-6"></i>
                        <span>Analytics & Reports</span>
                    </a>
                    
                    <!-- Activity Logs -->
                    <a href="#" onclick="showSection('logs'); return false;" class="sidebar-item flex items-center gap-3 px-6 py-3 text-gray-200 hover:bg-[#1e4a6a] transition border-l-4 border-transparent hover:border-[#ffc107]">
                        <i class="fas fa-history w-6"></i>
                        <span>Activity Logs</span>
                    </a>
                </nav>
            </div>

            <!-- Admin Profile -->
            <div class="p-4 border-t border-[#1e4a6a]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-[#ffc107] rounded-full flex items-center justify-center text-[#0a2b3c] font-bold text-lg">A</div>
                    <div>
                        <p class="font-semibold">Admin User</p>
                        <p class="text-xs text-gray-400">administrator@pnp.gov.ph</p>
                    </div>
                    <button class="ml-auto text-gray-400 hover:text-white" onclick="logout()">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </div>
            </div>
        </aside>

        <!-- ========== MAIN CONTENT ========== -->
        <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
            
            <!-- Top Bar -->
            <div class="flex justify-between items-center mb-6 bg-white p-4 rounded-xl shadow-sm">
                <h2 class="text-2xl font-bold text-[#0a2b3c]" id="pageTitle">Dashboard Overview</h2>
                <div class="flex items-center gap-4">
                    <span class="bg-[#ffc107] text-[#0a2b3c] px-4 py-2 rounded-full text-sm font-semibold">
                        <i class="fas fa-calendar mr-2"></i> <span id="currentDate"></span>
                    </span>
                    <div class="relative">
                        <button class="bg-gray-100 p-2 rounded-full relative" onclick="toggleNotifications()">
                            <i class="fas fa-bell text-[#0a2b3c]"></i>
                            <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ========== DASHBOARD SECTION ========== -->
            <div id="dashboard-section" class="section active">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="stat-card bg-white rounded-xl p-6 shadow-sm border-l-4 border-[#0a2b3c]">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500 text-sm">Total Personnel</p>
                                <p class="text-3xl font-bold text-[#0a2b3c]" id="totalPersonnel">24</p>
                            </div>
                            <div class="w-12 h-12 bg-[#0a2b3c] bg-opacity-10 rounded-full flex items-center justify-center">
                                <i class="fas fa-users text-2xl text-[#0a2b3c]"></i>
                            </div>
                        </div>
                        <p class="text-green-600 text-sm mt-2"><i class="fas fa-arrow-up"></i> +3 this month</p>
                    </div>
                    
                    <div class="stat-card bg-white rounded-xl p-6 shadow-sm border-l-4 border-[#1e4a6a]">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500 text-sm">Total Patrols</p>
                                <p class="text-3xl font-bold text-[#0a2b3c]" id="totalPatrols">156</p>
                            </div>
                            <div class="w-12 h-12 bg-[#1e4a6a] bg-opacity-10 rounded-full flex items-center justify-center">
                                <i class="fas fa-walking text-2xl text-[#1e4a6a]"></i>
                            </div>
                        </div>
                        <p class="text-green-600 text-sm mt-2"><i class="fas fa-arrow-up"></i> +12 this week</p>
                    </div>
                    
                    <div class="stat-card bg-white rounded-xl p-6 shadow-sm border-l-4 border-[#c41e3a]">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500 text-sm">Checkpoints</p>
                                <p class="text-3xl font-bold text-[#0a2b3c]" id="totalCheckpoints">89</p>
                            </div>
                            <div class="w-12 h-12 bg-[#c41e3a] bg-opacity-10 rounded-full flex items-center justify-center">
                                <i class="fas fa-map-marker-alt text-2xl text-[#c41e3a]"></i>
                            </div>
                        </div>
                        <p class="text-green-600 text-sm mt-2"><i class="fas fa-arrow-up"></i> +5 today</p>
                    </div>
                    
                    <div class="stat-card bg-white rounded-xl p-6 shadow-sm border-l-4 border-[#ffc107]">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500 text-sm">Oplan Ops</p>
                                <p class="text-3xl font-bold text-[#0a2b3c]" id="totalOplans">67</p>
                            </div>
                            <div class="w-12 h-12 bg-[#ffc107] bg-opacity-10 rounded-full flex items-center justify-center">
                                <i class="fas fa-shield-alt text-2xl text-[#ffc107]"></i>
                            </div>
                        </div>
                        <p class="text-green-600 text-sm mt-2"><i class="fas fa-arrow-up"></i> +8 this week</p>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <h3 class="font-semibold text-[#0a2b3c] mb-4">Activity Trends (Last 7 Days)</h3>
                        <canvas id="activityChart" height="250"></canvas>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <h3 class="font-semibold text-[#0a2b3c] mb-4">Accomplishments by Barangay</h3>
                        <canvas id="barangayChart" height="250"></canvas>
                    </div>
                </div>

                <!-- Recent Activities Table -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="font-semibold text-[#0a2b3c]">Recent Activities</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Officer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barangay</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date/Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200" id="recentActivitiesTable">
                                <!-- Dynamically populated -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ========== USERS SECTION ========== -->
            <div id="users-section" class="section hidden">
                <div class="bg-white rounded-xl shadow-sm">
                    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="font-semibold text-[#0a2b3c]">PNP Personnel Management</h3>
                        <button class="bg-[#0a2b3c] text-white px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-[#1e4a6a]" onclick="openUserModal()">
                            <i class="fas fa-plus"></i> Add New Officer
                        </button>
                    </div>
                    <div class="p-4">
                        <!-- Search and Filter -->
                        <div class="flex gap-4 mb-4">
                            <input type="text" placeholder="Search officers..." class="flex-1 p-2 border border-gray-300 rounded-lg" id="userSearch">
                            <select class="p-2 border border-gray-300 rounded-lg" id="userRoleFilter">
                                <option value="">All Roles</option>
                                <option value="patrol_officer">Patrol Officer</option>
                                <option value="checkpoint_officer">Checkpoint Officer</option>
                                <option value="oplan_officer">Oplan Officer</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <!-- Users Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left">Badge #</th>
                                        <th class="px-4 py-3 text-left">Name</th>
                                        <th class="px-4 py-3 text-left">Rank</th>
                                        <th class="px-4 py-3 text-left">Role</th>
                                        <th class="px-4 py-3 text-left">Status</th>
                                        <th class="px-4 py-3 text-left">Last Login</th>
                                        <th class="px-4 py-3 text-left">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="usersTableBody">
                                    <!-- Dynamic -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== PATROL SECTION ========== -->
            <div id="patrols-section" class="section hidden">
                <div class="bg-white rounded-xl shadow-sm">
                    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="font-semibold text-[#0a2b3c]">Patrol Activities</h3>
                        <div class="flex gap-2">
                            <select class="p-2 border border-gray-300 rounded-lg" id="patrolFilter" onchange="filterPatrols()">
                                <option value="all">All Types</option>
                                <option value="Foot Patrol">Foot Patrol</option>
                                <option value="Mobile Patrol">Mobile Patrol</option>
                                <option value="Motorcycle Patrol">Motorcycle Patrol</option>
                            </select>
                            <button class="bg-[#0a2b3c] text-white px-4 py-2 rounded-lg flex items-center gap-2" onclick="exportToExcel('patrol')">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left">ID</th>
                                        <th class="px-4 py-3 text-left">Officer</th>
                                        <th class="px-4 py-3 text-left">Type</th>
                                        <th class="px-4 py-3 text-left">Barangay</th>
                                        <th class="px-4 py-3 text-left">Location</th>
                                        <th class="px-4 py-3 text-left">Date</th>
                                        <th class="px-4 py-3 text-left">Personnel</th>
                                        <th class="px-4 py-3 text-left">Accomplishments</th>
                                        <th class="px-4 py-3 text-left">Photos</th>
                                        <th class="px-4 py-3 text-left">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="patrolsTableBody">
                                    <!-- Dynamic -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== CHECKPOINT SECTION ========== -->
            <div id="checkpoints-section" class="section hidden">
                <div class="bg-white rounded-xl shadow-sm">
                    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="font-semibold text-[#0a2b3c]">Checkpoint Operations</h3>
                        <button class="bg-[#0a2b3c] text-white px-4 py-2 rounded-lg flex items-center gap-2" onclick="exportToExcel('checkpoint')">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                    <div class="p-4">
                        <!-- Summary Cards -->
                        <div class="grid grid-cols-4 gap-4 mb-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">Border Control Ops</p>
                                <p class="text-2xl font-bold text-[#0a2b3c]" id="totalBorderOps">45</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">Mobile Checkpoints</p>
                                <p class="text-2xl font-bold text-[#0a2b3c]" id="totalMobileOps">32</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">TCT/OVR Accomps</p>
                                <p class="text-2xl font-bold text-[#0a2b3c]" id="totalTctOvr">28</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">Arrested/Filed</p>
                                <p class="text-2xl font-bold text-[#0a2b3c]" id="totalArrested">15</p>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left">ID</th>
                                        <th class="px-4 py-3 text-left">Officer</th>
                                        <th class="px-4 py-3 text-left">Barangay</th>
                                        <th class="px-4 py-3 text-left">Location</th>
                                        <th class="px-4 py-3 text-left">Date/Time</th>
                                        <th class="px-4 py-3 text-left">Border Ops</th>
                                        <th class="px-4 py-3 text-left">Mobile Ops</th>
                                        <th class="px-4 py-3 text-left">TCT/OVR</th>
                                        <th class="px-4 py-3 text-left">Arrested</th>
                                        <th class="px-4 py-3 text-left">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="checkpointsTableBody">
                                    <!-- Dynamic -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== OPLAN SECTION ========== -->
            <div id="oplans-section" class="section hidden">
                <div class="bg-white rounded-xl shadow-sm">
                    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="font-semibold text-[#0a2b3c]">Oplan Bakal / Sita Operations</h3>
                        <div class="flex gap-2">
                            <select class="p-2 border border-gray-300 rounded-lg" onchange="filterOplans(this.value)">
                                <option value="all">All Types</option>
                                <option value="Oplan Bakal">Oplan Bakal</option>
                                <option value="Oplan Sita">Oplan Sita</option>
                            </select>
                            <button class="bg-[#0a2b3c] text-white px-4 py-2 rounded-lg flex items-center gap-2" onclick="exportToExcel('oplan')">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                    <div class="p-4">
                        <!-- Summary Cards -->
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">Oplan Bakal</p>
                                <p class="text-2xl font-bold text-[#0a2b3c]" id="totalOplanBakal">42</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">Oplan Sita</p>
                                <p class="text-2xl font-bold text-[#0a2b3c]" id="totalOplanSita">25</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">Total Personnel</p>
                                <p class="text-2xl font-bold text-[#0a2b3c]" id="totalOplanPersonnel">156</p>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left">ID</th>
                                        <th class="px-4 py-3 text-left">Officer</th>
                                        <th class="px-4 py-3 text-left">Type</th>
                                        <th class="px-4 py-3 text-left">Barangay</th>
                                        <th class="px-4 py-3 text-left">Location</th>
                                        <th class="px-4 py-3 text-left">Date/Time</th>
                                        <th class="px-4 py-3 text-left">Personnel</th>
                                        <th class="px-4 py-3 text-left">Accomplishments</th>
                                        <th class="px-4 py-3 text-left">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="oplansTableBody">
                                    <!-- Dynamic -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== BARANGAY SECTION ========== -->
            <div id="barangays-section" class="section hidden">
                <div class="bg-white rounded-xl shadow-sm">
                    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="font-semibold text-[#0a2b3c]">Barangay Management</h3>
                        <button class="bg-[#0a2b3c] text-white px-4 py-2 rounded-lg flex items-center gap-2" onclick="openBarangayModal()">
                            <i class="fas fa-plus"></i> Add Barangay
                        </button>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-4 gap-4 mb-4">
                            <div class="bg-gray-50 p-4 rounded-lg text-center">
                                <p class="text-3xl font-bold text-[#0a2b3c]" id="totalBarangays">19</p>
                                <p class="text-sm text-gray-600">Total Barangays</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg text-center">
                                <p class="text-3xl font-bold text-[#0a2b3c]" id="activeBarangays">19</p>
                                <p class="text-sm text-gray-600">Active</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg text-center">
                                <p class="text-3xl font-bold text-[#0a2b3c]" id="barangayPatrols">156</p>
                                <p class="text-sm text-gray-600">Total Patrols</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg text-center">
                                <p class="text-3xl font-bold text-[#0a2b3c]" id="barangayCheckpoints">89</p>
                                <p class="text-sm text-gray-600">Checkpoints</p>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left">ID</th>
                                        <th class="px-4 py-3 text-left">Barangay Name</th>
                                        <th class="px-4 py-3 text-left">Municipality</th>
                                        <th class="px-4 py-3 text-left">Patrols</th>
                                        <th class="px-4 py-3 text-left">Checkpoints</th>
                                        <th class="px-4 py-3 text-left">Oplans</th>
                                        <th class="px-4 py-3 text-left">Status</th>
                                        <th class="px-4 py-3 text-left">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="barangaysTableBody">
                                    <!-- Dynamic -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== REPORTS SECTION ========== -->
            <div id="reports-section" class="section hidden">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Report Filters -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-xl p-6 shadow-sm">
                            <h3 class="font-semibold text-[#0a2b3c] mb-4">Generate Report</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                                    <select class="w-full p-2 border border-gray-300 rounded-lg" id="reportType">
                                        <option value="daily">Daily Summary</option>
                                        <option value="weekly">Weekly Report</option>
                                        <option value="monthly">Monthly Report</option>
                                        <option value="barangay">Barangay Performance</option>
                                        <option value="officer">Officer Performance</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                    <input type="date" class="w-full p-2 border border-gray-300 rounded-lg" id="startDate">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                    <input type="date" class="w-full p-2 border border-gray-300 rounded-lg" id="endDate">
                                </div>
                                <button class="w-full bg-[#0a2b3c] text-white py-2 rounded-lg hover:bg-[#1e4a6a]" onclick="generateReport()">
                                    <i class="fas fa-file-pdf mr-2"></i> Generate Report
                                </button>
                                <button class="w-full border border-[#0a2b3c] text-[#0a2b3c] py-2 rounded-lg hover:bg-gray-50" onclick="exportReport()">
                                    <i class="fas fa-file-excel mr-2"></i> Export to Excel
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Report Preview -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-xl p-6 shadow-sm">
                            <h3 class="font-semibold text-[#0a2b3c] mb-4">Report Preview</h3>
                            <div id="reportPreview" class="space-y-4">
                                <!-- Dynamic report content -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== LOGS SECTION ========== -->
            <div id="logs-section" class="section hidden">
                <div class="bg-white rounded-xl shadow-sm">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="font-semibold text-[#0a2b3c]">System Activity Logs</h3>
                    </div>
                    <div class="p-4">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left">Timestamp</th>
                                        <th class="px-4 py-3 text-left">User</th>
                                        <th class="px-4 py-3 text-left">Action</th>
                                        <th class="px-4 py-3 text-left">Table</th>
                                        <th class="px-4 py-3 text-left">Record ID</th>
                                        <th class="px-4 py-3 text-left">Details</th>
                                    </tr>
                                </thead>
                                <tbody id="logsTableBody">
                                    <!-- Dynamic -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== MODALS ========== -->

            <!-- User Modal -->
            <div id="userModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
                <div class="bg-white rounded-xl w-full max-w-2xl p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-[#0a2b3c]">Add/Edit Officer</h3>
                        <button class="text-gray-500 hover:text-gray-700" onclick="closeUserModal()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="userForm" class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Badge Number</label>
                            <input type="text" class="w-full p-2 border border-gray-300 rounded-lg" id="badgeNumber" required>
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Rank</label>
                            <select class="w-full p-2 border border-gray-300 rounded-lg" id="rank">
                                <option value="PO1">PO1</option>
                                <option value="PO2">PO2</option>
                                <option value="PO3">PO3</option>
                                <option value="SPO1">SPO1</option>
                                <option value="SPO2">SPO2</option>
                                <option value="SPO3">SPO3</option>
                                <option value="SPO4">SPO4</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                            <input type="text" class="w-full p-2 border border-gray-300 rounded-lg" id="firstName" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                            <input type="text" class="w-full p-2 border border-gray-300 rounded-lg" id="lastName" required>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" class="w-full p-2 border border-gray-300 rounded-lg" id="email" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                            <select class="w-full p-2 border border-gray-300 rounded-lg" id="role">
                                <option value="patrol_officer">Patrol Officer</option>
                                <option value="checkpoint_officer">Checkpoint Officer</option>
                                <option value="oplan_officer">Oplan Officer</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select class="w-full p-2 border border-gray-300 rounded-lg" id="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                        <div class="col-span-2 flex justify-end gap-2 mt-4">
                            <button type="button" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50" onclick="closeUserModal()">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-[#0a2b3c] text-white rounded-lg hover:bg-[#1e4a6a]">Save Officer</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Barangay Modal -->
            <div id="barangayModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
                <div class="bg-white rounded-xl w-full max-w-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-[#0a2b3c]">Add Barangay</h3>
                        <button class="text-gray-500 hover:text-gray-700" onclick="closeBarangayModal()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="barangayForm">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Barangay Name</label>
                            <input type="text" class="w-full p-2 border border-gray-300 rounded-lg" id="barangayName" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Municipality</label>
                            <input type="text" class="w-full p-2 border border-gray-300 rounded-lg" id="barangayMunicipality" value="Manolo Fortich" readonly>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Province</label>
                            <input type="text" class="w-full p-2 border border-gray-300 rounded-lg" id="barangayProvince" value="Bukidnon" readonly>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50" onclick="closeBarangayModal()">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-[#0a2b3c] text-white rounded-lg hover:bg-[#1e4a6a]">Add Barangay</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- View Activity Modal -->
            <div id="viewActivityModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
                <div class="bg-white rounded-xl w-full max-w-4xl p-6 max-h-[90vh] overflow-y-auto">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-[#0a2b3c]" id="modalTitle">Activity Details</h3>
                        <button class="text-gray-500 hover:text-gray-700" onclick="closeViewModal()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="activityDetails" class="space-y-4">
                        <!-- Dynamic content -->
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // ========== GLOBAL VARIABLES ==========
        let currentSection = 'dashboard';
        let activities = JSON.parse(localStorage.getItem('pnpManoloFortichActivities')) || [];
        let users = JSON.parse(localStorage.getItem('pnpUsers')) || [];
        let barangays = JSON.parse(localStorage.getItem('pnpBarangays')) || [
            "Agusan Canyon", "Alae", "Dahilayan", "Dalirig", "Damilag",
            "Dicklum", "Guilang-guilang", "Kalugmanan", "Lindaban", "Lurugan",
            "Manolo Fortich Poblacion", "Mambatangan", "Minsuro", "Mantibugao",
            "Sankanan", "Santiago", "Santo Niño", "Tankulan", "Ticala"
        ];
        let logs = JSON.parse(localStorage.getItem('pnpActivityLogs')) || [];

        // ========== INITIALIZATION ==========
        document.addEventListener('DOMContentLoaded', function() {
            updateDate();
            initializeSampleData();
            updateDashboardStats();
            renderCharts();
            loadRecentActivities();
            loadUsers();
            loadPatrols();
            loadCheckpoints();
            loadOplans();
            loadBarangays();
            loadLogs();
        });

        function updateDate() {
            const now = new Date();
            document.getElementById('currentDate').innerText = now.toLocaleDateString('en-US', { 
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
            });
        }

        // ========== SECTION NAVIGATION ==========
        window.showSection = function(section) {
            // Hide all sections
            document.querySelectorAll('.section').forEach(el => el.classList.add('hidden'));
            document.getElementById(section + '-section').classList.remove('hidden');
            
            // Update sidebar active state
            document.querySelectorAll('.sidebar-item').forEach(el => el.classList.remove('active'));
            event.currentTarget.classList.add('active');
            
            // Update page title
            const titles = {
                'dashboard': 'Dashboard Overview',
                'users': 'User Management',
                'patrols': 'Patrol Activities',
                'checkpoints': 'Checkpoint Operations',
                'oplans': 'Oplan Bakal/Sita',
                'barangays': 'Barangay Management',
                'reports': 'Analytics & Reports',
                'logs': 'Activity Logs'
            };
            document.getElementById('pageTitle').innerText = titles[section];
            currentSection = section;
        };

        // ========== DASHBOARD FUNCTIONS ==========
        function updateDashboardStats() {
            // Calculate stats from activities
            let totalPatrols = activities.filter(a => a.type === 'patrol').length;
            let totalCheckpoints = activities.filter(a => a.type === 'checkpoint').length;
            let totalOplans = activities.filter(a => a.type === 'oplan').length;
            
            document.getElementById('totalPatrols').innerText = totalPatrols || 156;
            document.getElementById('totalCheckpoints').innerText = totalCheckpoints || 89;
            document.getElementById('totalOplans').innerText = totalOplans || 67;
            document.getElementById('totalPersonnel').innerText = users.length || 24;
        }

        function renderCharts() {
            // Activity Chart
            const ctx = document.getElementById('activityChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Patrols',
                        data: [12, 19, 15, 17, 24, 23, 20],
                        borderColor: '#0a2b3c',
                        backgroundColor: 'rgba(10, 43, 60, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Checkpoints',
                        data: [8, 12, 10, 14, 18, 16, 15],
                        borderColor: '#c41e3a',
                        backgroundColor: 'rgba(196, 30, 58, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Oplans',
                        data: [5, 8, 7, 10, 12, 9, 11],
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });

            // Barangay Chart
            const ctx2 = document.getElementById('barangayChart').getContext('2d');
            new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: ['Tankulan', 'Alae', 'Dahilayan', 'Damilag', 'Sankanan'],
                    datasets: [{
                        label: 'Accomplishments',
                        data: [45, 38, 32, 28, 25],
                        backgroundColor: '#0a2b3c',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }

        function loadRecentActivities() {
            const tbody = document.getElementById('recentActivitiesTable');
            tbody.innerHTML = '';
            
            // Get last 10 activities
            let recent = [...activities].sort((a,b) => new Date(b.timestamp) - new Date(a.timestamp)).slice(0, 10);
            
            recent.forEach(act => {
                let row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                row.innerHTML = `
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full ${act.type === 'patrol' ? 'bg-blue-100 text-blue-800' : act.type === 'checkpoint' ? 'bg-purple-100 text-purple-800' : 'bg-red-100 text-red-800'}">
                            ${act.type.toUpperCase()}
                        </span>
                    </td>
                    <td class="px-6 py-4">${act.officerName || 'PO3 Juan Dela Cruz'}</td>
                    <td class="px-6 py-4">${act.barangay || 'Tankulan'}</td>
                    <td class="px-6 py-4">${act.location || 'Poblacion'}</td>
                    <td class="px-6 py-4">${new Date(act.timestamp).toLocaleString()}</td>
                    <td class="px-6 py-4">
                        <button class="text-blue-600 hover:text-blue-800 mr-2" onclick="viewActivity('${act.id}')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // ========== USER MANAGEMENT ==========
        function loadUsers() {
            const tbody = document.getElementById('usersTableBody');
            if (!tbody) return;
            
            tbody.innerHTML = '';
            let sampleUsers = [
                { badge: 'PNP-2024-0123', name: 'Juan Dela Cruz', rank: 'PO3', role: 'patrol_officer', status: 'active', lastLogin: '2024-01-15 08:30' },
                { badge: 'PNP-2024-0124', name: 'Maria Santos', rank: 'SPO1', role: 'checkpoint_officer', status: 'active', lastLogin: '2024-01-15 09:15' },
                { badge: 'PNP-2024-0125', name: 'Pedro Reyes', rank: 'PO2', role: 'oplan_officer', status: 'active', lastLogin: '2024-01-14 22:45' },
                { badge: 'PNP-2024-0126', name: 'Ana Lopez', rank: 'SPO2', role: 'admin', status: 'active', lastLogin: '2024-01-15 10:00' }
            ];
            
            sampleUsers.forEach(user => {
                let row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                row.innerHTML = `
                    <td class="px-4 py-3">${user.badge}</td>
                    <td class="px-4 py-3 font-medium">${user.name}</td>
                    <td class="px-4 py-3">${user.rank}</td>
                    <td class="px-4 py-3 capitalize">${user.role.replace('_', ' ')}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full ${user.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${user.status}
                        </span>
                    </td>
                    <td class="px-4 py-3">${user.lastLogin}</td>
                    <td class="px-4 py-3">
                        <button class="text-blue-600 hover:text-blue-800 mr-2" onclick="editUser('${user.badge}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="text-red-600 hover:text-red-800" onclick="deleteUser('${user.badge}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // ========== PATROL MANAGEMENT ==========
        function loadPatrols() {
            const tbody = document.getElementById('patrolsTableBody');
            if (!tbody) return;
            
            tbody.innerHTML = '';
            let patrols = activities.filter(a => a.type === 'patrol').slice(0, 10);
            
            patrols.forEach((patrol, index) => {
                let row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                row.innerHTML = `
                    <td class="px-4 py-3">${index + 1}</td>
                    <td class="px-4 py-3">PO3 Juan Dela Cruz</td>
                    <td class="px-4 py-3">${patrol.patrolType || 'Foot Patrol'}</td>
                    <td class="px-4 py-3">${patrol.barangay || 'Tankulan'}</td>
                    <td class="px-4 py-3">${patrol.location || 'Poblacion'}</td>
                    <td class="px-4 py-3">${new Date(patrol.timestamp).toLocaleDateString()}</td>
                    <td class="px-4 py-3">${patrol.personnel || '4'}</td>
                    <td class="px-4 py-3">${patrol.accomplishments?.length || 0}</td>
                    <td class="px-4 py-3">
                        ${patrol.image ? '<i class="fas fa-image text-green-600"></i>' : '<i class="fas fa-times text-red-400"></i>'}
                    </td>
                    <td class="px-4 py-3">
                        <button class="text-blue-600 hover:text-blue-800 mr-2" onclick="viewActivity('${patrol.id}')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // ========== CHECKPOINT MANAGEMENT ==========
        function loadCheckpoints() {
            const tbody = document.getElementById('checkpointsTableBody');
            if (!tbody) return;
            
            tbody.innerHTML = '';
            let checkpoints = activities.filter(a => a.type === 'checkpoint').slice(0, 10);
            
            // Update summary totals
            document.getElementById('totalBorderOps').innerText = '45';
            document.getElementById('totalMobileOps').innerText = '32';
            document.getElementById('totalTctOvr').innerText = '28';
            document.getElementById('totalArrested').innerText = '15';
            
            checkpoints.forEach((cp, index) => {
                let row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                row.innerHTML = `
                    <td class="px-4 py-3">${index + 1}</td>
                    <td class="px-4 py-3">SPO1 Maria Santos</td>
                    <td class="px-4 py-3">${cp.barangay || 'Tankulan'}</td>
                    <td class="px-4 py-3">${cp.location || 'National Highway'}</td>
                    <td class="px-4 py-3">${new Date(cp.timestamp).toLocaleString()}</td>
                    <td class="px-4 py-3">${cp.borderControlOps || '8'}</td>
                    <td class="px-4 py-3">${cp.mobileCheckpointOps || '5'}</td>
                    <td class="px-4 py-3">${cp.tctOvrAccom || '3'}</td>
                    <td class="px-4 py-3">${cp.arrestedAccom || '2'}</td>
                    <td class="px-4 py-3">
                        <button class="text-blue-600 hover:text-blue-800" onclick="viewActivity('${cp.id}')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // ========== OPLAN MANAGEMENT ==========
        function loadOplans() {
            const tbody = document.getElementById('oplansTableBody');
            if (!tbody) return;
            
            tbody.innerHTML = '';
            let oplans = activities.filter(a => a.type === 'oplan').slice(0, 10);
            
            // Update summary totals
            document.getElementById('totalOplanBakal').innerText = '42';
            document.getElementById('totalOplanSita').innerText = '25';
            document.getElementById('totalOplanPersonnel').innerText = '156';
            
            oplans.forEach((oplan, index) => {
                let row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                row.innerHTML = `
                    <td class="px-4 py-3">${index + 1}</td>
                    <td class="px-4 py-3">PO2 Pedro Reyes</td>
                    <td class="px-4 py-3">${oplan.oplanType || 'Oplan Bakal'}</td>
                    <td class="px-4 py-3">${oplan.barangay || 'Dahilayan'}</td>
                    <td class="px-4 py-3">${oplan.location || 'Public Market'}</td>
                    <td class="px-4 py-3">${new Date(oplan.timestamp).toLocaleString()}</td>
                    <td class="px-4 py-3">${oplan.personnel || '6'}</td>
                    <td class="px-4 py-3">${oplan.accomplishments?.length || 0}</td>
                    <td class="px-4 py-3">
                        <button class="text-blue-600 hover:text-blue-800" onclick="viewActivity('${oplan.id}')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // ========== BARANGAY MANAGEMENT ==========
        function loadBarangays() {
            const tbody = document.getElementById('barangaysTableBody');
            if (!tbody) return;
            
            tbody.innerHTML = '';
            document.getElementById('totalBarangays').innerText = barangays.length;
            document.getElementById('activeBarangays').innerText = barangays.length;
            
            barangays.forEach((barangay, index) => {
                let row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                row.innerHTML = `
                    <td class="px-4 py-3">${index + 1}</td>
                    <td class="px-4 py-3 font-medium">${barangay}</td>
                    <td class="px-4 py-3">Manolo Fortich</td>
                    <td class="px-4 py-3">${Math.floor(Math.random() * 20) + 5}</td>
                    <td class="px-4 py-3">${Math.floor(Math.random() * 10) + 2}</td>
                    <td class="px-4 py-3">${Math.floor(Math.random() * 8) + 1}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span>
                    </td>
                    <td class="px-4 py-3">
                        <button class="text-blue-600 hover:text-blue-800 mr-2" onclick="editBarangay('${barangay}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="text-red-600 hover:text-red-800" onclick="deleteBarangay('${barangay}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // ========== LOGS ==========
        function loadLogs() {
            const tbody = document.getElementById('logsTableBody');
            if (!tbody) return;
            
            tbody.innerHTML = '';
            for (let i = 0; i < 20; i++) {
                let row = document.createElement('tr');
                row.className = 'hover:bg-gray-50 text-sm';
                row.innerHTML = `
                    <td class="px-4 py-2">${new Date().toLocaleString()}</td>
                    <td class="px-4 py-2">admin@system</td>
                    <td class="px-4 py-2">INSERT</td>
                    <td class="px-4 py-2">patrol_activities</td>
                    <td class="px-4 py-2">${i + 100}</td>
                    <td class="px-4 py-2 text-gray-600">New patrol record added</td>
                `;
                tbody.appendChild(row);
            }
        }

        // ========== MODAL FUNCTIONS ==========
        window.openUserModal = function() {
            document.getElementById('userModal').classList.remove('hidden');
            document.getElementById('userModal').classList.add('flex');
        };

        window.closeUserModal = function() {
            document.getElementById('userModal').classList.add('hidden');
            document.getElementById('userModal').classList.remove('flex');
        };

        window.openBarangayModal = function() {
            document.getElementById('barangayModal').classList.remove('hidden');
            document.getElementById('barangayModal').classList.add('flex');
        };

        window.closeBarangayModal = function() {
            document.getElementById('barangayModal').classList.add('hidden');
            document.getElementById('barangayModal').classList.remove('flex');
        };

        window.viewActivity = function(id) {
            let activity = activities.find(a => a.id == id);
            let modal = document.getElementById('viewActivityModal');
            let details = document.getElementById('activityDetails');
            
            if (activity) {
                details.innerHTML = `
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold mb-2">Basic Information</h4>
                        <p><strong>Type:</strong> ${activity.type}</p>
                        <p><strong>Barangay:</strong> ${activity.barangay || 'N/A'}</p>
                        <p><strong>Location:</strong> ${activity.location || 'N/A'}</p>
                        <p><strong>Date/Time:</strong> ${new Date(activity.timestamp).toLocaleString()}</p>
                    </div>
                    ${activity.accomplishments ? `
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold mb-2">Accomplishments</h4>
                        ${activity.accomplishments.map(acc => `
                            <div class="mb-3 pb-2 border-b">
                                <p><strong>${acc.title || 'Accomplishment'}</strong></p>
                                <p class="text-gray-600">${acc.description || 'No description'}</p>
                            </div>
                        `).join('')}
                    </div>
                    ` : ''}
                    ${activity.image ? `
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold mb-2">Photo</h4>
                        <img src="${activity.image}" class="max-h-64 rounded-lg" alt="Activity photo">
                    </div>
                    ` : ''}
                `;
            }
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        };

        window.closeViewModal = function() {
            document.getElementById('viewActivityModal').classList.add('hidden');
            document.getElementById('viewActivityModal').classList.remove('flex');
        };

        // ========== FILTER FUNCTIONS ==========
        window.filterPatrols = function() {
            let filter = document.getElementById('patrolFilter').value;
            // Implement filter logic
            console.log('Filtering patrols:', filter);
        };

        window.filterOplans = function(type) {
            console.log('Filtering oplans:', type);
        };

        // ========== EXPORT FUNCTIONS ==========
        window.exportToExcel = function(type) {
            alert(`Exporting ${type} data to Excel...`);
        };

        // ========== REPORT FUNCTIONS ==========
        window.generateReport = function() {
            alert('Generating report...');
        };

        window.exportReport = function() {
            alert('Exporting report to Excel...');
        };

        // ========== UTILITY FUNCTIONS ==========
        function initializeSampleData() {
            if (activities.length === 0) {
                // Add sample patrol
                activities.push({
                    id: Date.now() - 86400000,
                    type: 'patrol',
                    patrolType: 'Foot Patrol',
                    barangay: 'Tankulan',
                    location: 'Poblacion',
                    timestamp: new Date(Date.now() - 86400000).toISOString(),
                    personnel: 4,
                    accomplishments: [
                        { title: 'Patrol Completion', description: 'Completed regular patrol of market area' },
                        { title: 'Assistance', description: 'Assisted 3 senior citizens' }
                    ],
                    image: null
                });
                
                // Add sample checkpoint
                activities.push({
                    id: Date.now() - 43200000,
                    type: 'checkpoint',
                    barangay: 'Alae',
                    location: 'National Highway',
                    timestamp: new Date(Date.now() - 43200000).toISOString(),
                    borderControlOps: 8,
                    mobileCheckpointOps: 3,
                    tctOvrAccom: 2,
                    arrestedAccom: 1,
                    additionalAccomplishments: [
                        { title: 'Traffic Violation', description: 'Issued 3 citation tickets' }
                    ]
                });
                
                // Add sample oplan
                activities.push({
                    id: Date.now() - 21600000,
                    type: 'oplan',
                    oplanType: 'Oplan Bakal',
                    barangay: 'Dahilayan',
                    location: 'Tourist Area',
                    timestamp: new Date(Date.now() - 21600000).toISOString(),
                    personnel: 6,
                    accomplishments: [
                        { title: 'Firearm Check', description: 'Checked 15 individuals for firearms' }
                    ]
                });
                
                localStorage.setItem('pnpManoloFortichActivities', JSON.stringify(activities));
            }
        }

        window.toggleNotifications = function() {
            alert('No new notifications');
        };

        window.logout = function() {
            alert('Logging out...');
            window.location.href = 'login.html';
        };
    </script>
</body>
</html>