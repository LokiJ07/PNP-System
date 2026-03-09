<?php
// =====================================================
// FILE: admin/checkpoint.php
// PURPOSE: Display all checkpoint reports
// =====================================================

require_once '../config/db_connect.php';
requireAdmin();

// Get filter parameters
$status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$query = "
    SELECT c.checkpoint_id, c.specific_location, c.checkpoint_date, c.checkpoint_time, 
           c.border_control_ops, c.mobile_checkpoint_ops, c.tct_ovr_accomplishment,
           c.arrested_accomplishment, c.status, c.submitted_at,
           CONCAT(u.rank, ' ', u.first_name, ' ', u.last_name) as officer_name,
           u.badge_number,
           b.barangay_name
    FROM checkpoint_activities c
    JOIN users u ON c.user_id = u.user_id
    JOIN barangays b ON c.barangay_id = b.barangay_id
    WHERE 1=1
";

if ($status !== 'all') {
    $query .= " AND c.status = '" . $conn->real_escape_string($status) . "'";
}

if (!empty($search)) {
    $query .= " AND (u.first_name LIKE '%$search%' OR u.last_name LIKE '%$search%' OR b.barangay_name LIKE '%$search%')";
}

$query .= " ORDER BY c.submitted_at DESC";

$checkpoints = $conn->query($query);

// Get statistics
$stats = [];
$result = $conn->query("SELECT COUNT(*) as total FROM checkpoint_activities");
$stats['total'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT SUM(border_control_ops) as total FROM checkpoint_activities");
$stats['border_ops'] = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("SELECT SUM(mobile_checkpoint_ops) as total FROM checkpoint_activities");
$stats['mobile_ops'] = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("SELECT SUM(tct_ovr_accomplishment) as total FROM checkpoint_activities");
$stats['tct_ovr'] = $result->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../image/pnplogo.png">
    <title>PNP | Checkpoint Reports</title>
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

    <!-- Sidebar -->
    <div class="w-[240px] h-screen bg-[#08324f] text-white p-5 sticky top-0 overflow-y-auto">
        <div class="flex items-center gap-3 mb-6 pb-3 border-b border-[#1a4b6d]">
            <img src="../image/pnplogo.png" class="w-8 h-8 object-contain" alt="PNP Logo">
            <h2 class="text-xl font-semibold">PNP Admin</h2>
        </div>

        <!-- Admin Info -->
        <div class="bg-[#1e4a6a] p-3 rounded-lg mb-4 text-center">
            <p class="text-sm text-yellow-400 font-medium"><?php echo $_SESSION['full_name']; ?></p>
            <p class="text-xs text-gray-300 mt-1"><?php echo $_SESSION['email']; ?></p>
        </div>

        <ul class="space-y-1">
            <li class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer">
                <a href="admin_dashboard.php" class="text-white no-underline block">
                    <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                </a>
            </li>

            <li class="p-3 rounded bg-[#0a3d62] border-l-4 border-yellow-400">
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
                <a href="../logout.php" class="text-white no-underline block">
                    <i class="fas fa-sign-out-alt mr-3"></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-8 bg-[#eef2f6] overflow-y-auto h-screen">
        <h2 class="text-2xl font-bold text-[#08324f] mb-6">Checkpoint Reports</h2>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow-sm">
                <p class="text-gray-600 text-sm">Total Checkpoints</p>
                <p class="text-2xl font-bold text-[#08324f]"><?php echo $stats['total']; ?></p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm">
                <p class="text-gray-600 text-sm">Border Control</p>
                <p class="text-2xl font-bold text-[#08324f]"><?php echo $stats['border_ops']; ?></p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm">
                <p class="text-gray-600 text-sm">Mobile Checkpoints</p>
                <p class="text-2xl font-bold text-[#08324f]"><?php echo $stats['mobile_ops']; ?></p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm">
                <p class="text-gray-600 text-sm">TCT/OVR</p>
                <p class="text-2xl font-bold text-[#08324f]"><?php echo $stats['tct_ovr']; ?></p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white p-4 rounded-lg shadow-md mb-6">
            <form method="GET" class="flex flex-wrap gap-4">
                <div>
                    <select name="status" class="p-2 border border-gray-300 rounded-lg">
                        <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo $status == 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $status == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                <div class="flex-1">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by officer or barangay..." class="w-full p-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <button type="submit" class="bg-[#1f6fb2] text-white px-4 py-2 rounded-lg hover:bg-[#0a3d62]">Filter</button>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white p-5 rounded-lg shadow-md overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-[#08324f] text-white">
                        <th class="p-3 text-left">Officer</th>
                        <th class="p-3 text-left">Barangay</th>
                        <th class="p-3 text-left">Location</th>
                        <th class="p-3 text-left">Date</th>
                        <th class="p-3 text-left">Time</th>
                        <th class="p-3 text-center">Border</th>
                        <th class="p-3 text-center">Mobile</th>
                        <th class="p-3 text-center">TCT/OVR</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($checkpoints->num_rows == 0): ?>
                    <tr>
                        <td colspan="10" class="p-6 text-center text-gray-500">No checkpoint reports found</td>
                    </tr>
                    <?php else: ?>
                    <?php while ($row = $checkpoints->fetch_assoc()): ?>
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="p-3"><?php echo $row['officer_name']; ?></td>
                        <td class="p-3"><?php echo $row['barangay_name']; ?></td>
                        <td class="p-3"><?php echo substr($row['specific_location'], 0, 30) . '...'; ?></td>
                        <td class="p-3"><?php echo date('M d, Y', strtotime($row['checkpoint_date'])); ?></td>
                        <td class="p-3"><?php echo date('h:i A', strtotime($row['checkpoint_time'])); ?></td>
                        <td class="p-3 text-center"><?php echo $row['border_control_ops']; ?></td>
                        <td class="p-3 text-center"><?php echo $row['mobile_checkpoint_ops']; ?></td>
                        <td class="p-3 text-center"><?php echo $row['tct_ovr_accomplishment']; ?></td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded-full text-xs 
                                <?php 
                                echo $row['status'] == 'approved' ? 'bg-green-100 text-green-800' : 
                                    ($row['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); 
                                ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                        </td>
                        <td class="p-3">
                            <a href="view_report.php?type=checkpoint&id=<?php echo $row['checkpoint_id']; ?>" class="bg-[#0a3d62] text-white px-3 py-1 rounded text-xs hover:bg-[#08324f]">View</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php endif; ?>
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
    </script>
</body>
</html>
<?php $conn->close(); ?>