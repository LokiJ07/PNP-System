<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../image/pnplogo.png">
    <title>PNP | Mobile Patrol Reports</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dropdown-content { display: none; }
        .dropdown.active .dropdown-content { display: block; }
        .rotate-180 { transform: rotate(180deg); }
    </style>
</head>
<body class="flex bg-[#0a3d62]">

    <!-- Sidebar (same structure, highlight Mobile Patrol) -->
    <div class="w-[240px] h-screen bg-[#08324f] text-white p-5 sticky top-0 overflow-y-auto">
        <div class="flex items-center gap-3 mb-6 pb-3 border-b border-[#1a4b6d]">
            <img src="../image/pnplogo.png" class="w-8 h-8 object-contain" alt="PNP Logo">
            <h2 class="text-xl font-semibold">PNP Admin</h2>
        </div>

        <ul class="space-y-1">
            <li class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer">
                <a href="admin_dashboard.php" class="text-white no-underline block"><i class="fas fa-tachometer-alt mr-3"></i> Dashboard</a>
            </li>
            <li class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer">
                <a href="checkpoint.php" class="text-white no-underline block"><i class="fas fa-map-marker-alt mr-3"></i> Checkpoint</a>
            </li>
            <li class="dropdown active">
                <div class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer flex items-center justify-between bg-[#0a3d62]" onclick="toggleDropdown(this)">
                    <span><i class="fas fa-walking mr-3"></i> Patrol</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300 rotate-180"></i>
                </div>
                <ul class="pl-8 mt-1 space-y-1 dropdown-content block">
                    <li class="py-2 px-3 text-sm hover:bg-[#0a3d62] rounded"><a href="footpatrol.php" class="text-white no-underline block">Foot Patrol</a></li>
                    <li class="py-2 px-3 text-sm bg-[#0a3d62] rounded"><a href="mobilepatrol.php" class="text-white no-underline block">Mobile Patrol</a></li>
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
                <a href="admin_users.php" class="text-white no-underline block"><i class="fas fa-users mr-3"></i> Users</a>
            </li>
            <li class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer mt-5 pt-4 border-t border-[#1a4b6d]">
                <a href="../index.php" class="text-white no-underline block"><i class="fas fa-sign-out-alt mr-3"></i> Logout</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-8 bg-[#eef2f6] overflow-y-auto h-screen">
        <h2 class="text-2xl font-bold text-[#08324f] mb-6">Mobile Patrol Reports</h2>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow-sm">
                <p class="text-gray-600 text-sm">Total Mobile Patrols</p>
                <p class="text-2xl font-bold text-[#08324f]">24</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm">
                <p class="text-gray-600 text-sm">Vehicles Used</p>
                <p class="text-2xl font-bold text-[#08324f]">8</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm">
                <p class="text-gray-600 text-sm">Distance Covered</p>
                <p class="text-2xl font-bold text-[#08324f]">156 km</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm">
                <p class="text-gray-600 text-sm">Reports Filed</p>
                <p class="text-2xl font-bold text-[#08324f]">24</p>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white p-5 rounded-lg shadow-md">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-[#08324f] text-white">
                        <th class="p-3 text-left">Name</th>
                        <th class="p-3 text-left">Date</th>
                        <th class="p-3 text-left">Time</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="p-3">Juan Dela Cruz</td>
                        <td class="p-3">June 10, 2026</td>
                        <td class="p-3">9:30 AM</td>
                        <td class="p-3"><span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs">Pending</span></td>
                        <td class="p-3"><button class="bg-[#0a3d62] text-white px-4 py-1.5 rounded text-sm hover:bg-[#08324f]" onclick="viewReport(1)">View</button></td>
                    </tr>
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="p-3">Maria Santos</td>
                        <td class="p-3">June 10, 2026</td>
                        <td class="p-3">10:15 AM</td>
                        <td class="p-3"><span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs">Approved</span></td>
                        <td class="p-3"><button class="bg-[#0a3d62] text-white px-4 py-1.5 rounded text-sm hover:bg-[#08324f]" onclick="viewReport(2)">View</button></td>
                    </tr>
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="p-3">Pedro Reyes</td>
                        <td class="p-3">June 11, 2026</td>
                        <td class="p-3">7:45 AM</td>
                        <td class="p-3"><span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs">Pending</span></td>
                        <td class="p-3"><button class="bg-[#0a3d62] text-white px-4 py-1.5 rounded text-sm hover:bg-[#08324f]" onclick="viewReport(3)">View</button></td>
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

        function viewReport(id) {
            alert('Viewing report #' + id);
        }
    </script>
</body>
</html>