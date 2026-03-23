<?php
// =====================================================
// FILE: admin/admin_users.php
// PURPOSE: Manage all PNP personnel accounts with ADD user functionality
// UPDATED: Fixed last login display (full datetime, timezone)
// =====================================================

// Set Philippine timezone for accurate datetime display
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../config/db_connect.php';
requireAdmin();

// Handle Add User Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_user') {
    
    $badge_number = mysqli_real_escape_string($conn, $_POST['badge_number']);
    $rank = mysqli_real_escape_string($conn, $_POST['rank']);
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $middle_name = !empty($_POST['middle_name']) ? mysqli_real_escape_string($conn, $_POST['middle_name']) : null;
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = $_POST['role'] ?? 'user';
    $password = $_POST['password'];
    
    // Validation
    $errors = [];
    
    // Check if badge number already exists
    $check_badge = $conn->query("SELECT user_id FROM users WHERE badge_number = '$badge_number'");
    if ($check_badge->num_rows > 0) {
        $errors[] = "Badge number already exists";
    }
    
    // Check if email already exists
    $check_email = $conn->query("SELECT user_id FROM users WHERE email = '$email'");
    if ($check_email->num_rows > 0) {
        $errors[] = "Email already exists";
    }
    
    // Validate password length
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Simplified INSERT - only required fields + defaults for others
        $sql = "INSERT INTO users (
            badge_number, rank, first_name, last_name, middle_name, email, password,
            role, account_status
        ) VALUES (
            '$badge_number', '$rank', '$first_name', '$last_name', " . 
            ($middle_name ? "'$middle_name'" : "NULL") . ", 
            '$email', '$hashed_password', '$role', 'active'
        )";
        
        if ($conn->query($sql)) {
            $_SESSION['success'] = "New user added successfully! Badge: $badge_number, Password: $password";
        } else {
            $_SESSION['error'] = "Error adding user: " . $conn->error;
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
    
    header('Location: admin_users.php');
    exit();
}
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

// Get ranks for dropdown
$ranks = [
    'PAT', 'PO1', 'PO2', 'PO3', 'SPO1', 'SPO2', 'SPO3', 'SPO4',
    'PMSg', 'PMSg', 'PCMS', 'PEMS', 'PLT', 'PCPT', 'PMAJ', 'PLTCOL', 'PCOL'
];

// Get units for dropdown
$units = ['Patrol Unit', 'Checkpoint Unit', 'Oplan Unit', 'Investigation Unit', 'Traffic Unit', 'Administrative Unit'];

// Get stations
$stations = ['Manolo Fortich MPS', 'Malaybalay CPS', 'Valencia CPS', 'Bukidnon PPO'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../image/pnplogo.png">
    <title>PNP | User Management</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
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
        
        /* Status badges */
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        
        /* Card hover effects */
        .stat-card {
            transition: all 0.3s ease;
            border-left-width: 4px;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
        }
        
        /* Modal styles */
        .modal {
            transition: opacity 0.3s ease;
        }
        .modal.hidden {
            display: none;
        }
        
        /* Form styles */
        .form-input {
            transition: all 0.2s ease;
        }
        .form-input:focus {
            border-color: #1f6fb2;
            box-shadow: 0 0 0 3px rgba(31, 111, 178, 0.1);
        }
        
        /* Table styles */
        .table-container {
            border-radius: 0.5rem;
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #08324f;
            color: white;
            padding: 0.75rem 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.875rem;
        }
        td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        tr:hover {
            background-color: #f9fafb;
        }
        
        /* Sidebar scroll */
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
        }
        
        /* Tooltip */
        .last-login-tooltip {
            cursor: help;
            border-bottom: 1px dotted #9ca3af;
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
                <?php echo substr($_SESSION['full_name'] ?? 'Admin', 0, 1); ?>
            </div>
            <p class="font-medium text-yellow-400"><?php echo $_SESSION['full_name'] ?? 'Admin'; ?></p>
            <p class="text-xs text-gray-300 mt-1 break-all"><?php echo $_SESSION['email'] ?? 'admin@pnp.gov.ph'; ?></p>
        </div>

        <!-- Navigation Menu -->
        <ul class="space-y-1 px-3 pb-5">
            <li><a href="admin_dashboard.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1e4a6a] transition"><i class="fas fa-tachometer-alt w-5"></i> Dashboard</a></li>
            <li><a href="checkpoint.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1e4a6a] transition"><i class="fas fa-map-marker-alt w-5"></i> Checkpoint</a></li>
            
            <li class="dropdown">
                <div class="flex items-center justify-between p-3 rounded-lg hover:bg-[#1e4a6a] cursor-pointer transition" onclick="toggleDropdown(this)">
                    <div class="flex items-center gap-3"><i class="fas fa-walking w-5"></i> Patrol</div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300"></i>
                </div>
                <ul class="dropdown-content pl-4 ml-4 space-y-1 border-l border-[#1e4a6a]">
                    <li><a href="footpatrol.php" class="block p-2 text-sm hover:bg-[#1e4a6a] rounded-lg transition">Foot Patrol</a></li>
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
            
            <li class="bg-[#1e4a6a] rounded-lg"><a href="admin_users.php" class="flex items-center gap-3 p-3"><i class="fas fa-users w-5 text-yellow-400"></i> Users</a></li>
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
    <div class="flex-1 p-4 md:p-6 lg:p-8 bg-[#eef2f6] overflow-y-auto min-h-screen">
        
        <!-- Header with Add Button -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6 border-l-4 border-yellow-400 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-[#08324f]">👥 User Management</h2>
                <p class="text-gray-600 mt-1">Manage all PNP personnel accounts</p>
            </div>
            <button onclick="openAddUserModal()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition flex items-center gap-2 shadow-md">
                <i class="fas fa-plus-circle"></i> Add New User
            </button>
        </div>

        <!-- Display Session Messages -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2 text-green-600"></i>
                <span><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2 text-red-600"></i>
                <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="stat-card bg-white p-5 rounded-lg shadow-md border-l-4 border-blue-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-gray-500">Total Users</p>
                        <p class="text-2xl font-bold text-[#08324f] mt-1"><?php echo $stats['total']; ?></p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-users text-blue-600"></i>
                    </div>
                </div>
            </div>
            <div class="stat-card bg-white p-5 rounded-lg shadow-md border-l-4 border-green-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-gray-500">Active Users</p>
                        <p class="text-2xl font-bold text-[#08324f] mt-1"><?php echo $stats['active']; ?></p>
                    </div>
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-check text-green-600"></i>
                    </div>
                </div>
            </div>
            <div class="stat-card bg-white p-5 rounded-lg shadow-md border-l-4 border-red-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-gray-500">Inactive Users</p>
                        <p class="text-2xl font-bold text-[#08324f] mt-1"><?php echo $stats['inactive']; ?></p>
                    </div>
                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-slash text-red-600"></i>
                    </div>
                </div>
            </div>
            <div class="stat-card bg-white p-5 rounded-lg shadow-md border-l-4 border-purple-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-gray-500">Administrators</p>
                        <p class="text-2xl font-bold text-[#08324f] mt-1"><?php echo $stats['admins']; ?></p>
                    </div>
                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-tie text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="bg-white p-4 rounded-lg shadow-md mb-6">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                <input type="text" id="searchInput" placeholder="Search by name, badge number, rank, or email..." 
                       class="w-full p-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2] focus:border-transparent"
                       onkeyup="searchTable()">
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full" id="usersTable">
                    <thead>
                        <tr class="bg-[#08324f] text-white">
                            <th class="p-3 text-left">Badge #</th>
                            <th class="p-3 text-left">Full Name</th>
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
                            <td colspan="9" class="p-8 text-center text-gray-500">
                                <i class="fas fa-users text-4xl mb-3"></i>
                                <p>No users found</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php while ($user = $users->fetch_assoc()): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                            <td class="p-3 font-mono text-sm font-medium"><?php echo $user['badge_number']; ?></td>
                            <td class="p-3 font-medium"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></td>
                            <td class="p-3"><?php echo $user['rank']; ?></td>
                            <td class="p-3 text-sm"><?php echo $user['email']; ?></td>
                            <td class="p-3">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold
                                    <?php echo $user['role'] == 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td class="p-3">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold
                                    <?php 
                                    echo $user['account_status'] == 'active' ? 'bg-green-100 text-green-800' : 
                                        ($user['account_status'] == 'inactive' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); 
                                    ?>">
                                    <?php echo ucfirst($user['account_status']); ?>
                                </span>
                            </td>
                            <td class="p-3 text-sm">
                                <?php 
                                if ($user['last_login'] && $user['last_login'] != '0000-00-00 00:00:00') {
                                    // Format with full datetime
                                    $timestamp = strtotime($user['last_login']);
                                    echo '<span class="last-login-tooltip" title="' . date('F d, Y h:i:s A', $timestamp) . '">';
                                    echo date('M d, Y h:i A', $timestamp);
                                    echo '</span>';
                                } else {
                                    echo '<span class="text-gray-400">Never</span>';
                                }
                                ?>
                            </td>
                            <td class="p-3 text-sm">
                                <?php echo $user['date_hired'] ? date('M d, Y', strtotime($user['date_hired'])) : '<span class="text-gray-400">N/A</span>'; ?>
                            </td>
                            <td class="p-3">
                                <div class="flex gap-2">
                                    <a href="view_user.php?id=<?php echo $user['user_id']; ?>" 
                                       class="bg-[#1f6fb2] text-white px-3 py-1.5 rounded text-xs hover:bg-[#0a3d62] transition flex items-center gap-1"
                                       title="View Details">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    
                                    <?php if ($user['account_status'] == 'active'): ?>
                                    <a href="?action=deactivate&id=<?php echo $user['user_id']; ?>" 
                                       class="bg-red-500 text-white px-3 py-1.5 rounded text-xs hover:bg-red-600 transition flex items-center gap-1"
                                       title="Deactivate" 
                                       onclick="return confirm('Are you sure you want to deactivate this user?')">
                                        <i class="fas fa-ban"></i> Deactivate
                                    </a>
                                    <?php else: ?>
                                    <a href="?action=activate&id=<?php echo $user['user_id']; ?>" 
                                       class="bg-green-500 text-white px-3 py-1.5 rounded text-xs hover:bg-green-600 transition flex items-center gap-1"
                                       title="Activate" 
                                       onclick="return confirm('Are you sure you want to activate this user?')">
                                        <i class="fas fa-check-circle"></i> Activate
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
            
            <!-- Table Footer -->
            <div class="p-4 bg-gray-50 border-t border-gray-200 text-sm text-gray-600">
                <span>Showing <strong><?php echo $users->num_rows; ?></strong> users</span>
            </div>
        </div>
    </div>

<!-- Add User Modal (unchanged) -->
<div id="addUserModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4 modal" onclick="closeModalOnOutsideClick(event)">
    <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl" onclick="event.stopPropagation()">
        
        <!-- Modal Header -->
        <div class="bg-[#08324f] text-white p-5 rounded-t-xl flex justify-between items-center sticky top-0">
            <h3 class="text-lg font-semibold flex items-center">
                <i class="fas fa-user-plus text-yellow-400 mr-2"></i>
                Add New Personnel
            </h3>
            <button onclick="closeAddUserModal()" class="text-white hover:text-gray-300 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <form method="POST" action="admin_users.php" class="p-6">
            <input type="hidden" name="action" value="add_user">
            
            <!-- Basic Information ONLY -->
            <div class="mb-6">
                <h4 class="font-semibold text-[#08324f] mb-3 pb-2 border-b border-gray-200 flex items-center">
                    <i class="fas fa-id-card text-yellow-500 mr-2"></i> Basic Information
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Badge Number *</label>
                        <input type="text" name="badge_number" required 
                               class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2] focus:border-transparent"
                               placeholder="e.g., PNP-2024-0001">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rank *</label>
                        <select name="rank" required class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2]">
                            <option value="">Select Rank</option>
                            <?php foreach ($ranks as $rank): ?>
                            <option value="<?php echo $rank; ?>"><?php echo $rank; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                        <input type="text" name="first_name" required 
                               class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2]"
                               placeholder="First name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                        <input type="text" name="last_name" required 
                               class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2]"
                               placeholder="Last name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                        <input type="text" name="middle_name" 
                               class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2]"
                               placeholder="Middle name (optional)">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input type="email" name="email" required 
                               class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2]"
                               placeholder="email@example.com">
                    </div>
                </div>
            </div>
            
            <!-- Account Security ONLY -->
            <div class="mb-6">
                <h4 class="font-semibold text-[#08324f] mb-3 pb-2 border-b border-gray-200 flex items-center">
                    <i class="fas fa-lock text-yellow-500 mr-2"></i> Account Security
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select name="role" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2]">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                        <input type="text" name="password" required value="<?php echo bin2hex(random_bytes(4)); ?>"
                               class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2] font-mono"
                               placeholder="Password">
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i> 
                            Password will be shown to you. User can change it later.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="border-t pt-4 flex flex-col sm:flex-row gap-3 justify-end">
                <button type="button" onclick="closeAddUserModal()" 
                        class="px-6 py-2 border border-gray-300 bg-white rounded-lg hover:bg-gray-100 transition text-sm font-medium">
                    <i class="fas fa-times mr-2"></i> Cancel
                </button>
                <button type="submit" 
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium flex items-center">
                    <i class="fas fa-user-plus mr-2"></i> Add User
                </button>
            </div>
        </form>
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

        // Modal Functions
        function openAddUserModal() {
            document.getElementById('addUserModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeAddUserModal() {
            document.getElementById('addUserModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function closeModalOnOutsideClick(event) {
            if (event.target.id === 'addUserModal') {
                closeAddUserModal();
            }
        }

        // Search Function
        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('usersTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                if (row.cells.length === 1) continue; // Skip "No users found" row
                
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