<?php
// =====================================================
// FILE: admin/footpatrol.php
// PURPOSE: Display foot patrol reports (auto-approved only)
// IMPROVED: Mobile responsive, better UI, with violations
// =====================================================

session_start();
require_once '../config/db_connect.php';
requireAdmin();

// Get filter parameters (removed status filter since all are approved)
$barangay_id = isset($_GET['barangay_id']) ? (int)$_GET['barangay_id'] : 0;
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$search = $_GET['search'] ?? '';

// Get barangays for filter
$barangays = $conn->query("SELECT barangay_id, barangay_name FROM barangays ORDER BY barangay_name");

// Build query - only approved reports
$query = "
    SELECT p.*, 
           CONCAT(u.rank, ' ', u.first_name, ' ', u.last_name) as officer_name,
           u.badge_number,
           b.barangay_name
    FROM patrol_activities p
    JOIN users u ON p.user_id = u.user_id
    JOIN barangays b ON p.barangay_id = b.barangay_id
    WHERE p.patrol_type = 'Foot Patrol' AND p.status = 'approved'
";

if ($barangay_id > 0) {
    $query .= " AND p.barangay_id = $barangay_id";
}

if (!empty($from_date)) {
    $query .= " AND DATE(p.patrol_date) >= '" . $conn->real_escape_string($from_date) . "'";
}

if (!empty($to_date)) {
    $query .= " AND DATE(p.patrol_date) <= '" . $conn->real_escape_string($to_date) . "'";
}

if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $query .= " AND (u.first_name LIKE '%$search%' OR u.last_name LIKE '%$search%' 
                OR u.badge_number LIKE '%$search%' OR b.barangay_name LIKE '%$search%'
                OR p.specific_location LIKE '%$search%')";
}

$query .= " ORDER BY p.patrol_date DESC, p.patrol_time DESC";

$patrols = $conn->query($query);

// Get statistics
$stats = [];

// Basic counts
$result = $conn->query("SELECT COUNT(*) as total FROM patrol_activities WHERE patrol_type = 'Foot Patrol' AND status = 'approved'");
$stats['total'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT SUM(personnel_count) as total FROM patrol_activities WHERE patrol_type = 'Foot Patrol' AND status = 'approved'");
$stats['personnel'] = $result->fetch_assoc()['total'] ?? 0;

// Violations totals for foot patrol
$result = $conn->query("SELECT 
    COALESCE(SUM(drinking_violations), 0) as drinking,
    COALESCE(SUM(smoking_violations), 0) as smoking,
    COALESCE(SUM(halfnaked_violations), 0) as halfnaked,
    COALESCE(SUM(curfew_violations), 0) as curfew,
    COALESCE(SUM(vandalism_violations), 0) as vandalism,
    COALESCE(SUM(other_violations), 0) as other
FROM patrol_activities WHERE patrol_type = 'Foot Patrol' AND status = 'approved'");
$violations = $result->fetch_assoc();
$stats['drinking'] = $violations['drinking'];
$stats['smoking'] = $violations['smoking'];
$stats['halfnaked'] = $violations['halfnaked'];
$stats['curfew'] = $violations['curfew'];
$stats['vandalism'] = $violations['vandalism'];
$stats['other_violations'] = $violations['other'];

$stats['total_violations'] = $violations['drinking'] + $violations['smoking'] + $violations['halfnaked'] + 
                             $violations['curfew'] + $violations['vandalism'] + $violations['other'];

// Admin info for sidebar
$admin_name = $_SESSION['full_name'] ?? 'Admin';
$admin_email = $_SESSION['email'] ?? 'admin@pnp.gov.ph';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../image/pnplogo.png">
    <title>PNP | Foot Patrol Reports</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* Dropdown styles */
        .dropdown-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        .dropdown.active .dropdown-content {
            max-height: 300px;
            transition: max-height 0.5s ease-in;
        }
        .rotate-180 {
            transform: rotate(180deg);
            transition: transform 0.3s ease;
        }
        
        /* Card hover effects */
        .stat-card {
            transition: all 0.3s ease;
            border-left-width: 4px;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.2);
        }
        
        /* Sidebar scrollbar */
        .sidebar-scroll {
            scrollbar-width: thin;
            scrollbar-color: #1e4a6a #08324f;
        }
        .sidebar-scroll::-webkit-scrollbar {
            width: 6px;
        }
        .sidebar-scroll::-webkit-scrollbar-track {
            background: #08324f;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background-color: #1e4a6a;
            border-radius: 20px;
        }
        
        /* Mobile menu */
        @media (max-width: 768px) {
            .sidebar-mobile {
                position: fixed;
                left: -100%;
                transition: left 0.3s ease;
                z-index: 50;
                width: 280px;
                height: 100vh;
            }
            .sidebar-mobile.open {
                left: 0;
            }
            .main-content-mobile {
                width: 100%;
                margin-left: 0;
            }
        }
        
        /* Table styles */
        .table-container {
            overflow-x: auto;
            border-radius: 0.5rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #08324f;
            color: white;
            padding: 0.75rem 0.5rem;
            font-weight: 600;
            font-size: 0.8rem;
            white-space: nowrap;
        }
        td {
            padding: 0.75rem 0.5rem;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.9rem;
        }
        tr:hover {
            background-color: #f9fafb;
        }
        
        /* Filter card */
        .filter-card {
            background: white;
            border-radius: 0.75rem;
            padding: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="flex flex-col md:flex-row bg-[#08324f] min-h-screen">

    <!-- Mobile Menu Button -->
    <button id="mobileMenuBtn" class="md:hidden fixed top-4 left-4 z-50 bg-[#1e4a6a] text-white p-3 rounded-lg shadow-lg">
        <i class="fas fa-bars text-xl"></i>
    </button>

    <!-- Mobile Menu Overlay -->
    <div id="menuOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden" onclick="closeMobileMenu()"></div>

    <!-- Sidebar -->
    <div id="sidebar" class="w-full md:w-[260px] bg-[#08324f] text-white h-screen overflow-y-auto sidebar-scroll sidebar-mobile fixed top-0 left-[-100%] md:left-0 md:sticky z-50 transition-all duration-300 ease-in-out">
        
        <button id="closeSidebar" class="md:hidden absolute top-4 right-4 text-white text-xl">
            <i class="fas fa-times"></i>
        </button>

        <!-- Logo and Title -->
        <div class="flex items-center gap-3 p-5 border-b border-[#1e4a6a] sticky top-0 bg-[#08324f] z-10">
            <img src="../image/pnplogo.png" class="w-10 h-10 object-contain" alt="PNP Logo">
            <div>
                <h2 class="text-lg font-semibold leading-tight">PNP Operation</h2>
                <p class="text-xs text-yellow-400">Admin Panel</p>
            </div>
        </div>

        <!-- Admin Info -->
        <div class="bg-[#1e4a6a] mx-4 my-4 p-4 rounded-lg text-center shadow-lg">
            <div class="w-16 h-16 bg-yellow-400 rounded-full mx-auto mb-3 flex items-center justify-center text-[#08324f] text-2xl font-bold">
                <?php echo substr($admin_name, 0, 1); ?>
            </div>
            <p class="font-medium text-yellow-400"><?php echo $admin_name; ?></p>
            <p class="text-xs text-gray-300 mt-1 break-all"><?php echo $admin_email; ?></p>
        </div>

        <!-- Navigation Menu -->
        <ul class="space-y-1 px-3 pb-5">
            <li><a href="admin_dashboard.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1e4a6a] transition"><i class="fas fa-tachometer-alt w-5"></i> Dashboard</a></li>
            <li><a href="checkpoint.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1e4a6a] transition"><i class="fas fa-map-marker-alt w-5"></i> Checkpoint</a></li>
            
            <li class="dropdown active">
                <div class="flex items-center justify-between p-3 rounded-lg bg-[#1e4a6a] cursor-pointer transition" onclick="toggleDropdown(this)">
                    <div class="flex items-center gap-3"><i class="fas fa-walking w-5 text-yellow-400"></i> Patrol</div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300 rotate-180"></i>
                </div>
                <ul class="dropdown-content pl-4 ml-4 space-y-1 border-l border-[#1e4a6a]" style="display: block;">
                    <li class="bg-[#1e4a6a] rounded-lg"><a href="footpatrol.php" class="block p-2 text-sm text-yellow-400">Foot Patrol</a></li>
                    <li><a href="mobilepatrol.php" class="block p-2 text-sm hover:bg-[#1e4a6a] rounded-lg transition">Mobile Patrol</a></li>
                    <li><a href="motorpatrol.php" class="block p-2 text-sm hover:bg-[#1e4a6a] rounded-lg transition">Motor Patrol</a></li>
                </ul>
            </li>
            
            <li class="dropdown">
                <div class="flex items-center justify-between p-3 rounded-lg hover:bg-[#1e4a6a] cursor-pointer transition" onclick="toggleDropdown(this)">
                    <div class="flex items-center gap-3"><i class="fas fa-shield-alt w-5"></i> Oplan</div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300"></i>
                </div>
                <ul class="dropdown-content pl-4 ml-4 space-y-1 border-l border-[#1e4a6a]">
                    <li><a href="oplanbakal.php" class="block p-2 text-sm hover:bg-[#1e4a6a] rounded-lg transition">Oplan Bakal</a></li>
                    <li><a href="oplansita.php" class="block p-2 text-sm hover:bg-[#1e4a6a] rounded-lg transition">Oplan Sita</a></li>
                </ul>
            </li>
            
            <li><a href="admin_users.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1e4a6a] transition"><i class="fas fa-users w-5"></i> Users</a></li>
            <li><a href="accomplishment_report.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1e4a6a] transition"><i class="fas fa-file-alt w-5"></i> Accomplishment Report</a></li>
            <li><a href="all_reports.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1e4a6a] transition"><i class="fas fa-folder-open w-5"></i> All Reports</a></li>
            <li><a href="activity_logs.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1e4a6a] transition"><i class="fas fa-history w-5"></i> Activity Logs</a></li>
            
            <li class="my-4 border-t border-[#1e4a6a]"></li>
            <li><a href="../logout.php" class="flex items-center gap-3 p-3 rounded-lg bg-red-600 hover:bg-red-700 transition"><i class="fas fa-sign-out-alt w-5"></i> Logout</a></li>
            
            <li class="mt-6 text-center text-xs text-gray-400">
                <p>PNP Manolo Fortich v2.0</p>
                <p class="mt-1">© 2026 All Rights Reserved</p>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-4 md:p-6 lg:p-8 bg-[#eef2f6] overflow-y-auto min-h-screen main-content-mobile">
        
        <!-- Header -->
        <div class="bg-white p-4 md:p-6 rounded-lg shadow-md mb-6 border-l-4 border-yellow-400 flex justify-between items-center">
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-[#08324f] flex items-center gap-2">
                    <i class="fas fa-walking text-yellow-500"></i>
                    Foot Patrol Reports
                </h2>
                <p class="text-sm text-gray-600 mt-1">All foot patrol reports are automatically approved</p>
            </div>
            <div class="bg-green-100 text-green-800 px-4 py-2 rounded-full text-sm font-semibold">
                <i class="fas fa-check-circle mr-1"></i> Auto-Approved
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-6">
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-blue-500">
                <p class="text-xs text-gray-500">Total Foot Patrols</p>
                <p class="text-2xl font-bold text-[#08324f]"><?php echo $stats['total']; ?></p>
            </div>
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-green-500">
                <p class="text-xs text-gray-500">Personnel Deployed</p>
                <p class="text-2xl font-bold text-[#08324f]"><?php echo $stats['personnel']; ?></p>
            </div>
            <div class="stat-card bg-white p-4 rounded-lg shadow-md border-l-4 border-purple-500">
                <p class="text-xs text-gray-500">Violations</p>
                <p class="text-2xl font-bold text-[#08324f]"><?php echo $stats['total_violations']; ?></p>
            </div>
        </div>

        <!-- Violations Summary -->
        <div class="bg-white p-4 rounded-lg shadow-md mb-6">
            <h3 class="text-sm font-semibold text-[#08324f] mb-3 flex items-center gap-2">
                <i class="fas fa-gavel text-yellow-500"></i>
                Violations Encountered
            </h3>
            <div class="grid grid-cols-3 md:grid-cols-6 gap-3">
                <div><span class="text-xs text-gray-500">Drinking:</span> <span class="font-bold"><?php echo $stats['drinking']; ?></span></div>
                <div><span class="text-xs text-gray-500">Smoking:</span> <span class="font-bold"><?php echo $stats['smoking']; ?></span></div>
                <div><span class="text-xs text-gray-500">Half-Naked:</span> <span class="font-bold"><?php echo $stats['halfnaked']; ?></span></div>
                <div><span class="text-xs text-gray-500">Curfew:</span> <span class="font-bold"><?php echo $stats['curfew']; ?></span></div>
                <div><span class="text-xs text-gray-500">Vandalism:</span> <span class="font-bold"><?php echo $stats['vandalism']; ?></span></div>
                <div><span class="text-xs text-gray-500">Other:</span> <span class="font-bold"><?php echo $stats['other_violations']; ?></span></div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-card">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <!-- Barangay Filter -->
                <div class="w-[200px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Barangay</label>
                    <select name="barangay_id" class="w-full p-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#1f6fb2]">
                        <option value="0">All Barangays</option>
                        <?php while ($b = $barangays->fetch_assoc()): ?>
                        <option value="<?php echo $b['barangay_id']; ?>" <?php echo $barangay_id == $b['barangay_id'] ? 'selected' : ''; ?>>
                            <?php echo $b['barangay_name']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Date Range -->
                <div class="w-[150px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
                    <input type="date" name="from_date" value="<?php echo $from_date; ?>" class="w-full p-2 border border-gray-300 rounded-lg text-sm">
                </div>

                <div class="w-[150px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
                    <input type="date" name="to_date" value="<?php echo $to_date; ?>" class="w-full p-2 border border-gray-300 rounded-lg text-sm">
                </div>

                <!-- Search -->
                <div class="flex-1 min-w-[250px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Officer, badge, barangay, location..." 
                           class="w-full p-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#1f6fb2]">
                </div>

                <!-- Buttons -->
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-[#1f6fb2] text-white rounded-lg hover:bg-[#0a3d62] transition text-sm flex items-center gap-1">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="footpatrol.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm flex items-center gap-1">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Results Info -->
        <div class="flex justify-between items-center mb-3 text-sm text-gray-600">
            <span><i class="fas fa-list mr-1"></i> Showing <strong><?php echo $patrols->num_rows; ?></strong> foot patrol reports</span>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Officer</th>
                            <th>Badge</th>
                            <th>Barangay</th>
                            <th>Location</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th class="text-center">Personnel</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($patrols->num_rows == 0): ?>
                        <tr>
                            <td colspan="8" class="p-8 text-center text-gray-500">
                                <i class="fas fa-walking text-4xl mb-3 text-gray-300"></i>
                                <p>No foot patrol reports found</p>
                                <p class="text-sm mt-1">Try adjusting your filters</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php while ($row = $patrols->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="font-medium"><?php echo $row['officer_name']; ?></td>
                            <td class="text-xs font-mono"><?php echo $row['badge_number']; ?></td>
                            <td><?php echo $row['barangay_name']; ?></td>
                            <td class="max-w-xs truncate" title="<?php echo htmlspecialchars($row['specific_location']); ?>">
                                <?php echo substr($row['specific_location'], 0, 30) . '...'; ?>
                            </td>
                            <td class="whitespace-nowrap"><?php echo date('M d, Y', strtotime($row['patrol_date'])); ?></td>
                            <td class="whitespace-nowrap"><?php echo date('h:i A', strtotime($row['patrol_time'])); ?></td>
                            <td class="text-center font-bold"><?php echo $row['personnel_count']; ?></td>
                            <td>
                                <a href="view_report.php?type=patrol&id=<?php echo $row['patrol_id']; ?>" 
                                   class="bg-[#1f6fb2] text-white px-3 py-1 rounded text-xs hover:bg-[#0a3d62] transition inline-flex items-center gap-1">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Legend -->
        <div class="mt-4 text-xs text-gray-500 text-center">
            <i class="fas fa-check-circle text-green-500 mr-1"></i> All foot patrol reports are auto-approved
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

        // Dropdown Functions
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
<?php $conn->close(); ?>