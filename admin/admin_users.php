<?php
// =====================================================
// FILE: admin/admin_users.php
// PURPOSE: Manage all PNP personnel accounts
// =====================================================

session_start();
require_once '../config/db_connect.php';
requireAdmin();

// Handle user actions (activate/deactivate)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $user_id = (int)$_GET['id'];
    
    if ($action === 'activate') {
        $stmt = $conn->prepare("UPDATE users SET account_status = 'active' WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "User activated successfully";
        }
    } elseif ($action === 'deactivate') {
        $stmt = $conn->prepare("UPDATE users SET account_status = 'inactive' WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "User deactivated successfully";
        }
    }
    header('Location: admin_users.php');
    exit();
}

// Get all users
$users = $conn->query("
    SELECT user_id, badge_number, rank, first_name, last_name, email, 
           account_status, last_login, date_hired, role
    FROM users 
    ORDER BY 
        CASE 
            WHEN account_status = 'active' THEN 1
            WHEN account_status = 'inactive' THEN 2
            ELSE 3
        END,
        last_name, first_name
");

// Get statistics
$stats = [];
$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$stats['total'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'user' AND account_status = 'active'");
$stats['active'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'user' AND account_status = 'inactive'");
$stats['inactive'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
$stats['admins'] = $result->fetch_assoc()['total'];
?>

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
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
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

            <li class="p-3 rounded bg-[#0a3d62] border-l-4 border-yellow-400">
                <a href="admin_users.php" class="text-white no-underline block">
                    <i class="fas fa-users mr-3"></i> Users
                </a>
            </li>

            <li class="p-3 rounded hover:bg-[#1e4a6a] cursor-pointer">
                <a href="accomplishment_report.php" class="text-white no-underline block">
                    <i class="fas fa-file-alt mr-3"></i> Accomplishment Report
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
        <div class="bg-white p-6 rounded-lg shadow-md mb-6 border-l-4 border-yellow-400">
            <h2 class="text-2xl font-bold text-[#08324f]">User Management</h2>
            <p class="text-gray-600 mt-1">Manage all PNP personnel accounts</p>
        </div>

        <!-- Display Session Messages -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-lg">
            <i class="fas fa-check-circle mr-2"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-lg">
            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-blue-500">
                <p class="text-sm text-gray-500">Total Users</p>
                <p class="text-2xl font-bold text-[#08324f]"><?php echo $stats['total']; ?></p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-green-500">
                <p class="text-sm text-gray-500">Active Users</p>
                <p class="text-2xl font-bold text-[#08324f]"><?php echo $stats['active']; ?></p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-red-500">
                <p class="text-sm text-gray-500">Inactive Users</p>
                <p class="text-2xl font-bold text-[#08324f]"><?php echo $stats['inactive']; ?></p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-purple-500">
                <p class="text-sm text-gray-500">Administrators</p>
                <p class="text-2xl font-bold text-[#08324f]"><?php echo $stats['admins']; ?></p>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="bg-white p-4 rounded-lg shadow-md mb-6">
            <input type="text" id="searchInput" placeholder="Search by name, badge, email..." class="w-full p-2 border border-gray-300 rounded-lg" onkeyup="searchTable()">
        </div>

        <!-- Users Table -->
        <div class="bg-white p-5 rounded-lg shadow-md overflow-x-auto">
            <table class="w-full border-collapse" id="usersTable">
                <thead>
                    <tr class="bg-[#08324f] text-white">
                        <th class="p-3 text-left">Badge #</th>
                        <th class="p-3 text-left">Name</th>
                        <th class="p-3 text-left">Rank</th>
                        <th class="p-3 text-left">Email</th>
                        <th class="p-3 text-left">Role</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-left">Last Login</th>
                        <th class="p-3 text-left">Date Hired</th>
                        <th class="p-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users->num_rows == 0): ?>
                    <tr>
                        <td colspan="9" class="p-6 text-center text-gray-500">No users found</td>
                    </tr>
                    <?php else: ?>
                    <?php while ($user = $users->fetch_assoc()): ?>
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="p-3 font-mono text-sm"><?php echo $user['badge_number']; ?></td>
                        <td class="p-3 font-medium"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></td>
                        <td class="p-3"><?php echo $user['rank']; ?></td>
                        <td class="p-3 text-sm"><?php echo $user['email']; ?></td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded-full text-xs 
                                <?php echo $user['role'] == 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded-full text-xs 
                                <?php 
                                echo $user['account_status'] == 'active' ? 'bg-green-100 text-green-800' : 
                                    ($user['account_status'] == 'inactive' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); 
                                ?>">
                                <?php echo ucfirst($user['account_status']); ?>
                            </span>
                        </td>
                        <td class="p-3 text-sm">
                            <?php echo $user['last_login'] ? date('M d, Y', strtotime($user['last_login'])) : 'Never'; ?>
                        </td>
                        <td class="p-3 text-sm">
                            <?php echo $user['date_hired'] ? date('M d, Y', strtotime($user['date_hired'])) : 'N/A'; ?>
                        </td>
                        <td class="p-3">
                            <div class="flex gap-2">
                                <a href="view_user.php?id=<?php echo $user['user_id']; ?>" class="bg-[#1f6fb2] text-white px-3 py-1 rounded text-xs hover:bg-[#0a3d62]" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <?php if ($user['account_status'] == 'active'): ?>
                                <a href="?action=deactivate&id=<?php echo $user['user_id']; ?>" class="bg-red-500 text-white px-3 py-1 rounded text-xs hover:bg-red-600" title="Deactivate" onclick="return confirm('Deactivate this user?')">
                                    <i class="fas fa-ban"></i>
                                </a>
                                <?php else: ?>
                                <a href="?action=activate&id=<?php echo $user['user_id']; ?>" class="bg-green-500 text-white px-3 py-1 rounded text-xs hover:bg-green-600" title="Activate" onclick="return confirm('Activate this user?')">
                                    <i class="fas fa-check-circle"></i>
                                </a>
                                <?php endif; ?>
                            </div>
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

        // Search functionality
        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('usersTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const badge = row.cells[0]?.textContent.toLowerCase() || '';
                const name = row.cells[1]?.textContent.toLowerCase() || '';
                const rank = row.cells[2]?.textContent.toLowerCase() || '';
                const email = row.cells[3]?.textContent.toLowerCase() || '';
                
                if (badge.includes(filter) || name.includes(filter) || rank.includes(filter) || email.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>