<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../image/pnplogo.png">
    <title>PNP | Users Management</title>
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

    <!-- Sidebar (same as above) -->
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
            <li class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer bg-[#0a3d62] border-l-4 border-yellow-400">
                <a href="admin_users.php" class="text-white no-underline block"><i class="fas fa-users mr-3"></i> Users</a>
            </li>
            <li class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer mt-5 pt-4 border-t border-[#1a4b6d]">
                <a href="../index.php" class="text-white no-underline block"><i class="fas fa-sign-out-alt mr-3"></i> Logout</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-8 bg-[#eef2f6] overflow-y-auto h-screen">
        <h2 class="text-2xl font-bold text-[#08324f] mb-6">Users Management</h2>

        <!-- Tabs -->
        <div class="flex gap-3 mb-6">
            <button class="bg-[#08324f] text-white px-5 py-2 rounded text-sm font-medium hover:bg-[#0a3d62]" onclick="showTab('active')">Active Users</button>
            <button class="bg-gray-300 text-gray-700 px-5 py-2 rounded text-sm font-medium hover:bg-gray-400" onclick="showTab('inactive')">Inactive Users</button>
        </div>

        <!-- Active Users Table -->
        <div id="active" class="bg-white p-5 rounded-lg shadow-md">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-[#08324f] text-white">
                        <th class="p-3 text-left">Name</th>
                        <th class="p-3 text-left">Email</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="p-3">Juan Dela Cruz</td>
                        <td class="p-3">juan@example.com</td>
                        <td class="p-3"><span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs">Active</span></td>
                        <td class="p-3">
                            <button class="bg-yellow-500 text-white px-3 py-1.5 rounded text-xs mr-2 hover:bg-yellow-600" onclick="deactivateUser(1)">Deactivate</button>
                            <button class="bg-[#0a3d62] text-white px-3 py-1.5 rounded text-xs hover:bg-[#08324f]" onclick="viewUser(1)">View</button>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="p-3">Maria Santos</td>
                        <td class="p-3">maria@example.com</td>
                        <td class="p-3"><span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs">Active</span></td>
                        <td class="p-3">
                            <button class="bg-yellow-500 text-white px-3 py-1.5 rounded text-xs mr-2 hover:bg-yellow-600" onclick="deactivateUser(2)">Deactivate</button>
                            <button class="bg-[#0a3d62] text-white px-3 py-1.5 rounded text-xs hover:bg-[#08324f]" onclick="viewUser(2)">View</button>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="p-3">Pedro Reyes</td>
                        <td class="p-3">pedro@example.com</td>
                        <td class="p-3"><span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs">Active</span></td>
                        <td class="p-3">
                            <button class="bg-yellow-500 text-white px-3 py-1.5 rounded text-xs mr-2 hover:bg-yellow-600" onclick="deactivateUser(3)">Deactivate</button>
                            <button class="bg-[#0a3d62] text-white px-3 py-1.5 rounded text-xs hover:bg-[#08324f]" onclick="viewUser(3)">View</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Inactive Users Table -->
        <div id="inactive" class="bg-white p-5 rounded-lg shadow-md hidden">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-[#08324f] text-white">
                        <th class="p-3 text-left">Name</th>
                        <th class="p-3 text-left">Email</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="p-3">Pedro Reyes</td>
                        <td class="p-3">pedro@example.com</td>
                        <td class="p-3"><span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs">Inactive</span></td>
                        <td class="p-3">
                            <button class="bg-green-500 text-white px-3 py-1.5 rounded text-xs mr-2 hover:bg-green-600" onclick="activateUser(3)">Activate</button>
                            <button class="bg-[#0a3d62] text-white px-3 py-1.5 rounded text-xs hover:bg-[#08324f]" onclick="viewUser(3)">View</button>
                        </td>
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

        function showTab(tab) {
            document.getElementById('active').style.display = 'none';
            document.getElementById('inactive').style.display = 'none';
            document.getElementById(tab).style.display = 'block';
            
            // Update button styles
            document.querySelectorAll('.tabs button').forEach(btn => {
                btn.classList.remove('bg-[#08324f]', 'text-white');
                btn.classList.add('bg-gray-300', 'text-gray-700');
            });
            event.target.classList.remove('bg-gray-300', 'text-gray-700');
            event.target.classList.add('bg-[#08324f]', 'text-white');
        }

        function deactivateUser(id) {
            if(confirm('Deactivate user #' + id + '?')) {
                alert('User deactivated');
            }
        }

        function activateUser(id) {
            if(confirm('Activate user #' + id + '?')) {
                alert('User activated');
            }
        }

        function viewUser(id) {
            alert('Viewing user #' + id);
        }
    </script>
</body>
</html>