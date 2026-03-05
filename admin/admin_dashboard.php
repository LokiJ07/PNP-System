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
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dropdown-content { display: none; }
        .dropdown.active .dropdown-content { display: block; }
        .rotate-180 { transform: rotate(180deg); }
    </style>
</head>
<body class="flex bg-[#0a3d62]">

    <!-- Sidebar -->
    <div class="w-[240px] h-screen bg-[#08324f] text-white p-5 sticky top-0 overflow-y-auto">
        <div class="flex items-center gap-3 mb-6 pb-3 border-b border-[#1a4b6d]">
            <img src="../image/pnplogo.png" class="w-8 h-8 object-contain" alt="PNP Logo">
            <h2 class="text-xl font-semibold">PNP Admin</h2>
        </div>

        <ul class="space-y-1">
            <li class="p-3 rounded bg-[#0a3d62] border-l-4 border-yellow-400">
                <a href="admin_dashboard.php" class="text-white no-underline block">
                    <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                </a>
            </li>

            <li class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer">
                <a href="checkpoint.php" class="text-white no-underline block">
                    <i class="fas fa-map-marker-alt mr-3"></i> Checkpoint
                </a>
            </li>

            <li class="dropdown">
                <div class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer flex items-center justify-between" onclick="toggleDropdown(this)">
                    <span><i class="fas fa-walking mr-3"></i> Patrol</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300"></i>
                </div>
                <ul class="pl-8 mt-1 space-y-1 dropdown-content">
                    <li class="py-2 px-3 text-sm hover:bg-[#0a3d62] rounded"><a href="footpatrol.php" class="text-white no-underline block">Foot Patrol</a></li>
                    <li class="py-2 px-3 text-sm hover:bg-[#0a3d62] rounded"><a href="mobilepatrol.php" class="text-white no-underline block">Mobile Patrol</a></li>
                    <li class="py-2 px-3 text-sm hover:bg-[#0a3d62] rounded"><a href="motorpatrol.php" class="text-white no-underline block">Motorcycle Patrol</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <div class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer flex items-center justify-between" onclick="toggleDropdown(this)">
                    <span><i class="fas fa-shield-alt mr-3"></i> Oplan Bakal / Sita</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300"></i>
                </div>
                <ul class="pl-8 mt-1 space-y-1 dropdown-content">
                    <li class="py-2 px-3 text-sm hover:bg-[#0a3d62] rounded"><a href="oplanbakal.php" class="text-white no-underline block">Oplan Bakal</a></li>
                    <li class="py-2 px-3 text-sm hover:bg-[#0a3d62] rounded"><a href="oplansita.php" class="text-white no-underline block">Oplan Sita</a></li>
                </ul>
            </li>

            <li class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer">
                <a href="admin_users.php" class="text-white no-underline block">
                    <i class="fas fa-users mr-3"></i> Users
                </a>
            </li>

            <li class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer mt-5 pt-4 border-t border-[#1a4b6d]">
                <a href="../index.php" class="text-white no-underline block">
                    <i class="fas fa-sign-out-alt mr-3"></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-8 bg-[#eef2f6] overflow-y-auto h-screen">
        
        <!-- Header -->
        <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
            <h2 class="text-2xl font-bold text-[#08324f]">Dashboard Overview</h2>
            <p class="text-gray-600 mt-1">Welcome back. System monitoring panel.</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
            <!-- Patrol Summary Card -->
            <div class="bg-white p-5 rounded-lg shadow-md">
                <h3 class="text-gray-700 font-semibold mb-4">Patrol Summary</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center pb-2 border-b border-gray-200">
                        <span class="text-gray-600">Foot Patrol</span>
                        <span class="font-bold text-[#0a3d62] text-lg">24</span>
                    </div>
                    <div class="flex justify-between items-center pb-2 border-b border-gray-200">
                        <span class="text-gray-600">Mobile Patrol</span>
                        <span class="font-bold text-[#0a3d62] text-lg">24</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Motorcycle Patrol</span>
                        <span class="font-bold text-[#0a3d62] text-lg">24</span>
                    </div>
                </div>
            </div>

            <!-- Checkpoint Card -->
            <div class="bg-white p-5 rounded-lg shadow-md">
                <h3 class="text-gray-700 font-semibold mb-4">Checkpoint</h3>
                <p class="text-[#0a3d62] text-3xl font-bold">3</p>
                <p class="text-sm text-gray-500 mt-2">Active checkpoints today</p>
            </div>

            <!-- Oplan Bakal Card -->
            <div class="bg-white p-5 rounded-lg shadow-md">
                <h3 class="text-gray-700 font-semibold mb-4">Oplan Bakal</h3>
                <p class="text-[#0a3d62] text-3xl font-bold">20</p>
                <p class="text-sm text-gray-500 mt-2">Total operations</p>
            </div>

            <!-- Oplan Sita Card -->
            <div class="bg-white p-5 rounded-lg shadow-md">
                <h3 class="text-gray-700 font-semibold mb-4">Oplan Sita</h3>
                <p class="text-[#0a3d62] text-3xl font-bold">28</p>
                <p class="text-sm text-gray-500 mt-2">Total operations</p>
            </div>
        </div>

        <!-- Charts Area -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="bg-white p-5 rounded-lg shadow-md h-[300px]">
                <canvas id="activityChart"></canvas>
            </div>
            <div class="bg-white p-5 rounded-lg shadow-md h-[300px]">
                <canvas id="barangayChart"></canvas>
            </div>
        </div>

        <!-- Recent Activities Table -->
        <div class="bg-white p-5 rounded-lg shadow-md mt-6">
            <h3 class="text-lg font-semibold text-[#08324f] mb-4">Recent Activities</h3>
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-[#08324f] text-white">
                        <th class="p-3 text-left">Type</th>
                        <th class="p-3 text-left">Name</th>
                        <th class="p-3 text-left">Location</th>
                        <th class="p-3 text-left">Date/Time</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="p-3"><span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">Patrol</span></td>
                        <td class="p-3">Juan Dela Cruz</td>
                        <td class="p-3">Tankulan</td>
                        <td class="p-3">June 10, 9:30 AM</td>
                        <td class="p-3"><span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Approved</span></td>
                        <td class="p-3"><a href="view_report.php?id=1&type=patrol" class="bg-[#0a3d62] text-white px-3 py-1 rounded text-xs hover:bg-[#08324f]">View</a></td>
                    </tr>
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="p-3"><span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Checkpoint</span></td>
                        <td class="p-3">Maria Santos</td>
                        <td class="p-3">Alae</td>
                        <td class="p-3">June 10, 10:15 AM</td>
                        <td class="p-3"><span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Pending</span></td>
                        <td class="p-3"><a href="view_report.php?id=2&type=checkpoint" class="bg-[#0a3d62] text-white px-3 py-1 rounded text-xs hover:bg-[#08324f]">View</a></td>
                    </tr>
                </tbody>
            </table>
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

        // Initialize Charts
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('activityChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Activities',
                        data: [12, 19, 15, 17, 24, 23, 20],
                        borderColor: '#08324f',
                        backgroundColor: 'rgba(8,50,79,0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } }
                }
            });

            const ctx2 = document.getElementById('barangayChart').getContext('2d');
            new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: ['Tankulan', 'Alae', 'Dahilayan'],
                    datasets: [{
                        data: [45, 38, 32],
                        backgroundColor: '#08324f'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } }
                }
            });
        });
    </script>
</body>
</html>