<?php
// =====================================================
// FILE: admin/all_reports.php
// PURPOSE: Display all reports in one place with sorting and filtering
// =====================================================

session_start();
require_once '../config/db_connect.php';
requireAdmin();

// Get filter parameters
$type = $_GET['type'] ?? 'all';
$status = $_GET['status'] ?? 'all';
$barangay_id = isset($_GET['barangay_id']) ? (int)$_GET['barangay_id'] : 0;
$officer_id = isset($_GET['officer_id']) ? (int)$_GET['officer_id'] : 0;
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$sort = $_GET['sort'] ?? 'newest'; // newest, oldest, status, type
$search = $_GET['search'] ?? '';

// Get all barangays for filter
$barangays = $conn->query("SELECT barangay_id, barangay_name FROM barangays ORDER BY barangay_name");

// Get all officers for filter
$officers = $conn->query("SELECT user_id, rank, first_name, last_name FROM users WHERE role = 'user' ORDER BY last_name, first_name");

// Build the query conditions
$conditions = [];
$params = [];
$types = "";

if ($type !== 'all') {
    if ($type === 'patrol') {
        $conditions[] = "report_type = 'patrol'";
    } elseif ($type === 'checkpoint') {
        $conditions[] = "report_type = 'checkpoint'";
    } elseif ($type === 'oplan') {
        $conditions[] = "report_type = 'oplan'";
    }
}

if ($status !== 'all') {
    $conditions[] = "status = ?";
    $params[] = $status;
    $types .= "s";
}

if ($barangay_id > 0) {
    $conditions[] = "barangay_id = ?";
    $params[] = $barangay_id;
    $types .= "i";
}

if ($officer_id > 0) {
    $conditions[] = "user_id = ?";
    $params[] = $officer_id;
    $types .= "i";
}

if (!empty($from_date)) {
    $conditions[] = "DATE(submitted_at) >= ?";
    $params[] = $from_date;
    $types .= "s";
}

if (!empty($to_date)) {
    $conditions[] = "DATE(submitted_at) <= ?";
    $params[] = $to_date;
    $types .= "s";
}

if (!empty($search)) {
    $conditions[] = "(specific_location LIKE ? OR accomplishment_description LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

$where_clause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

// Determine sorting
$order_by = "ORDER BY ";
switch ($sort) {
    case 'oldest':
        $order_by .= "submitted_at ASC";
        break;
    case 'status':
        $order_by .= "status ASC, submitted_at DESC";
        break;
    case 'type':
        $order_by .= "report_type ASC, submitted_at DESC";
        break;
    default: // newest
        $order_by .= "submitted_at DESC";
}

// Get all reports using UNION
$query = "
    SELECT 
        'patrol' as report_type,
        patrol_id as id,
        patrol_type as subtype,
        specific_location,
        submitted_at,
        status,
        user_id,
        barangay_id,
        accomplishment_description
    FROM patrol_activities
    
    UNION ALL
    
    SELECT 
        'checkpoint' as report_type,
        checkpoint_id as id,
        'Checkpoint' as subtype,
        specific_location,
        submitted_at,
        status,
        user_id,
        barangay_id,
        accomplishment_description
    FROM checkpoint_activities
    
    UNION ALL
    
    SELECT 
        'oplan' as report_type,
        oplan_id as id,
        oplan_type as subtype,
        specific_location,
        submitted_at,
        status,
        user_id,
        barangay_id,
        accomplishment_description
    FROM oplan_activities
";

$query .= " $where_clause $order_by";

// Prepare and execute
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$reports = $stmt->get_result();

// Get counts for summary
$total_reports = $reports->num_rows;

// Get counts by status
$pending_count = 0;
$approved_count = 0;
$rejected_count = 0;

// Get counts by type
$patrol_count = 0;
$checkpoint_count = 0;
$oplan_count = 0;

$reports->data_seek(0);
while ($row = $reports->fetch_assoc()) {
    if ($row['status'] == 'pending') $pending_count++;
    elseif ($row['status'] == 'approved') $approved_count++;
    elseif ($row['status'] == 'rejected') $rejected_count++;
    
    if ($row['report_type'] == 'patrol') $patrol_count++;
    elseif ($row['report_type'] == 'checkpoint') $checkpoint_count++;
    elseif ($row['report_type'] == 'oplan') $oplan_count++;
}
$reports->data_seek(0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../image/pnplogo.png">
    <title>PNP | All Reports</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dropdown-content { display: none; }
        .dropdown.active .dropdown-content { display: block; }
        .rotate-180 { transform: rotate(180deg); }
        .filter-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="flex bg-[#0a3d62]">

    <!-- Sidebar -->
    <div class="w-[240px] h-screen bg-[#08324f] text-white p-5 sticky top-0 overflow-y-auto">
        <div class="flex items-center gap-3 mb-6 pb-3 border-b border-[#1a4b6d]">
            <img src="../image/pnplogo.png" class="w-8 h-8 object-contain" alt="PNP Logo">
            <h2 class="text-xl font-semibold">PNP Admin</h2>
        </div>

        <!-- Admin Info -->
        <div class="bg-[#1e4a6a] p-3 rounded-lg mb-4 text-center">
            <p class="text-sm text-yellow-400 font-medium"><?php echo $_SESSION['full_name'] ?? 'Admin'; ?></p>
            <p class="text-xs text-gray-300 mt-1"><?php echo $_SESSION['email'] ?? 'admin@pnp.gov.ph'; ?></p>
        </div>

        <ul class="space-y-1">
            <li class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer">
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
            <li class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer">
                <a href="accomplishment_report.php" class="text-white no-underline block">
                    <i class="fas fa-file-alt mr-3"></i> Accomplishment Report
                </a>
            </li>
            <li class="p-3 rounded bg-[#0a3d62] border-l-4 border-yellow-400">
                <a href="all_reports.php" class="text-white no-underline block">
                    <i class="fas fa-list mr-3"></i> All Reports
                </a>
            </li>
            <li class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer mt-5 pt-4 border-t border-[#1a4b6d]">
                <a href="../logout.php" class="text-white no-underline block">
                    <i class="fas fa-sign-out-alt mr-3"></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-8 bg-[#eef2f6] overflow-y-auto h-screen">
        
        <!-- Header -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6 border-l-4 border-yellow-400 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-[#08324f]">All Reports</h2>
                <p class="text-gray-600 mt-1">View and filter all submitted reports</p>
            </div>
            <div class="flex gap-2">
                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">
                    Total: <?php echo $total_reports; ?>
                </span>
                <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-semibold">
                    Pending: <?php echo $pending_count; ?>
                </span>
                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
                    Approved: <?php echo $approved_count; ?>
                </span>
                <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-semibold">
                    Rejected: <?php echo $rejected_count; ?>
                </span>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="stat-card border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Patrol Reports</p>
                        <p class="text-2xl font-bold text-[#08324f]"><?php echo $patrol_count; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-walking text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="stat-card border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Checkpoint Reports</p>
                        <p class="text-2xl font-bold text-[#08324f]"><?php echo $checkpoint_count; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-map-marker-alt text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="stat-card border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Oplan Reports</p>
                        <p class="text-2xl font-bold text-[#08324f]"><?php echo $oplan_count; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-shield-alt text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="filter-card">
            <form method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Report Type Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
                        <select name="type" class="w-full p-2 border border-gray-300 rounded-lg">
                            <option value="all" <?php echo $type == 'all' ? 'selected' : ''; ?>>All Types</option>
                            <option value="patrol" <?php echo $type == 'patrol' ? 'selected' : ''; ?>>Patrol</option>
                            <option value="checkpoint" <?php echo $type == 'checkpoint' ? 'selected' : ''; ?>>Checkpoint</option>
                            <option value="oplan" <?php echo $type == 'oplan' ? 'selected' : ''; ?>>Oplan</option>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full p-2 border border-gray-300 rounded-lg">
                            <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>All Status</option>
                            <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $status == 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo $status == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>

                    <!-- Barangay Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Barangay</label>
                        <select name="barangay_id" class="w-full p-2 border border-gray-300 rounded-lg">
                            <option value="0">All Barangays</option>
                            <?php while ($barangay = $barangays->fetch_assoc()): ?>
                            <option value="<?php echo $barangay['barangay_id']; ?>" <?php echo $barangay_id == $barangay['barangay_id'] ? 'selected' : ''; ?>>
                                <?php echo $barangay['barangay_name']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Officer Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Officer</label>
                        <select name="officer_id" class="w-full p-2 border border-gray-300 rounded-lg">
                            <option value="0">All Officers</option>
                            <?php while ($officer = $officers->fetch_assoc()): ?>
                            <option value="<?php echo $officer['user_id']; ?>" <?php echo $officer_id == $officer['user_id'] ? 'selected' : ''; ?>>
                                <?php echo $officer['rank'] . ' ' . $officer['first_name'] . ' ' . $officer['last_name']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Date From -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                        <input type="date" name="from_date" value="<?php echo $from_date; ?>" class="w-full p-2 border border-gray-300 rounded-lg">
                    </div>

                    <!-- Date To -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                        <input type="date" name="to_date" value="<?php echo $to_date; ?>" class="w-full p-2 border border-gray-300 rounded-lg">
                    </div>

                    <!-- Sort By -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                        <select name="sort" class="w-full p-2 border border-gray-300 rounded-lg">
                            <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="oldest" <?php echo $sort == 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                            <option value="status" <?php echo $sort == 'status' ? 'selected' : ''; ?>>By Status</option>
                            <option value="type" <?php echo $sort == 'type' ? 'selected' : ''; ?>>By Type</option>
                        </select>
                    </div>

                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search location or description..." class="w-full p-2 border border-gray-300 rounded-lg">
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <a href="all_reports.php" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                        <i class="fas fa-times mr-2"></i> Clear Filters
                    </a>
                    <button type="submit" class="px-4 py-2 bg-[#1f6fb2] text-white rounded-lg hover:bg-[#0a3d62] transition">
                        <i class="fas fa-search mr-2"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Reports Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-[#08324f] text-white">
                            <th class="p-3 text-left">Type</th>
                            <th class="p-3 text-left">ID</th>
                            <th class="p-3 text-left">Officer</th>
                            <th class="p-3 text-left">Barangay</th>
                            <th class="p-3 text-left">Location</th>
                            <th class="p-3 text-left">Date Submitted</th>
                            <th class="p-3 text-left">Status</th>
                            <th class="p-3 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($reports->num_rows == 0): ?>
                        <tr>
                            <td colspan="8" class="p-8 text-center text-gray-500">
                                <i class="fas fa-folder-open text-4xl mb-3"></i>
                                <p>No reports found matching your filters</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php while ($report = $reports->fetch_assoc()): 
                            // Get officer name
                            $officer_stmt = $conn->prepare("SELECT rank, first_name, last_name FROM users WHERE user_id = ?");
                            $officer_stmt->bind_param("i", $report['user_id']);
                            $officer_stmt->execute();
                            $officer = $officer_stmt->get_result()->fetch_assoc();
                            $officer_name = $officer ? $officer['rank'] . ' ' . $officer['first_name'] . ' ' . $officer['last_name'] : 'Unknown';
                            
                            // Get barangay name
                            $barangay_stmt = $conn->prepare("SELECT barangay_name FROM barangays WHERE barangay_id = ?");
                            $barangay_stmt->bind_param("i", $report['barangay_id']);
                            $barangay_stmt->execute();
                            $barangay = $barangay_stmt->get_result()->fetch_assoc();
                            $barangay_name = $barangay ? $barangay['barangay_name'] : 'Unknown';
                        ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="p-3">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                    <?php 
                                    echo $report['report_type'] == 'patrol' ? 'bg-blue-100 text-blue-800' : 
                                        ($report['report_type'] == 'checkpoint' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'); 
                                    ?>">
                                    <i class="fas 
                                        <?php 
                                        echo $report['report_type'] == 'patrol' ? 'fa-walking' : 
                                            ($report['report_type'] == 'checkpoint' ? 'fa-map-marker-alt' : 'fa-shield-alt'); 
                                        ?> mr-1">
                                    </i>
                                    <?php 
                                    if ($report['report_type'] == 'patrol') echo $report['subtype'];
                                    elseif ($report['report_type'] == 'checkpoint') echo 'Checkpoint';
                                    else echo $report['subtype'];
                                    ?>
                                </span>
                            </td>
                            <td class="p-3 font-mono text-sm"><?php echo strtoupper($report['report_type']) . '-' . str_pad($report['id'], 5, '0', STR_PAD_LEFT); ?></td>
                            <td class="p-3"><?php echo $officer_name; ?></td>
                            <td class="p-3"><?php echo $barangay_name; ?></td>
                            <td class="p-3 max-w-xs truncate"><?php echo htmlspecialchars($report['specific_location']); ?></td>
                            <td class="p-3"><?php echo date('M d, Y h:i A', strtotime($report['submitted_at'])); ?></td>
                            <td class="p-3">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                    <?php 
                                    echo $report['status'] == 'approved' ? 'bg-green-100 text-green-800' : 
                                        ($report['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); 
                                    ?>">
                                    <?php echo ucfirst($report['status']); ?>
                                </span>
                            </td>
                            <td class="p-3">
                                <a href="view_report.php?type=<?php echo $report['report_type']; ?>&id=<?php echo $report['id']; ?>" 
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
            
            <!-- Table Footer with Count -->
            <div class="p-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center text-sm text-gray-600">
                <span>Showing <strong><?php echo $reports->num_rows; ?></strong> of <strong><?php echo $total_reports; ?></strong> reports</span>
                <span class="text-xs text-gray-400">Click on a report to view details</span>
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
<?php $conn->close(); ?>