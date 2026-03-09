<?php
// =====================================================
// FILE: admin/view_user.php
// PURPOSE: Display complete user profile with statistics
// ADDED: Profile update functionality and fixed dates
// =====================================================

session_start();
require_once '../config/db_connect.php';
requireAdmin();

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id === 0) {
    $_SESSION['error'] = 'Invalid user ID';
    header('Location: admin_users.php');
    exit();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $rank = $_POST['rank'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $contact_number = $_POST['contact_number'] ?? '';
    $station = $_POST['station'] ?? '';
    $unit = $_POST['unit'] ?? '';
    $address = $_POST['address'] ?? '';
    $emergency_contact = $_POST['emergency_contact'] ?? '';
    $emergency_number = $_POST['emergency_number'] ?? '';
    
    $update_stmt = $conn->prepare("
        UPDATE users SET 
            rank = ?, first_name = ?, last_name = ?, email = ?,
            contact_number = ?, station = ?, unit = ?, address = ?,
            emergency_contact_name = ?, emergency_contact_number = ?
        WHERE user_id = ?
    ");
    
    $update_stmt->bind_param("ssssssssssi", 
        $rank, $first_name, $last_name, $email,
        $contact_number, $station, $unit, $address,
        $emergency_contact, $emergency_number, $user_id
    );
    
    if ($update_stmt->execute()) {
        $_SESSION['success'] = 'Profile updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update profile';
    }
    $update_stmt->close();
    
    header("Location: view_user.php?id=$user_id");
    exit();
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $new_password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
    
    $pass_stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $pass_stmt->bind_param("si", $new_password, $user_id);
    
    if ($pass_stmt->execute()) {
        $_SESSION['success'] = "Password reset successfully. New password: $new_password";
    } else {
        $_SESSION['error'] = 'Failed to reset password';
    }
    $pass_stmt->close();
    
    header("Location: view_user.php?id=$user_id");
    exit();
}

// Get user details
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    $_SESSION['error'] = 'User not found';
    header('Location: admin_users.php');
    exit();
}

// Get user statistics
$stats = [];

// Patrol statistics
$result = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN patrol_type = 'Foot Patrol' THEN 1 ELSE 0 END) as foot,
        SUM(CASE WHEN patrol_type = 'Mobile Patrol' THEN 1 ELSE 0 END) as mobile,
        SUM(CASE WHEN patrol_type = 'Motorcycle Patrol' THEN 1 ELSE 0 END) as motor,
        SUM(personnel_count) as total_personnel
    FROM patrol_activities 
    WHERE user_id = $user_id
");
$patrol_stats = $result->fetch_assoc();
$stats['patrols'] = $patrol_stats['total'] ?? 0;
$stats['patrols_approved'] = $patrol_stats['approved'] ?? 0;
$stats['patrols_pending'] = $patrol_stats['pending'] ?? 0;
$stats['patrols_rejected'] = $patrol_stats['rejected'] ?? 0;
$stats['foot'] = $patrol_stats['foot'] ?? 0;
$stats['mobile'] = $patrol_stats['mobile'] ?? 0;
$stats['motor'] = $patrol_stats['motor'] ?? 0;
$stats['patrol_personnel'] = $patrol_stats['total_personnel'] ?? 0;

// Checkpoint statistics
$result = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(border_control_ops) as border_ops,
        SUM(mobile_checkpoint_ops) as mobile_ops,
        SUM(tct_ovr_accomplishment) as tct_ovr,
        SUM(arrested_accomplishment) as arrests,
        SUM(border_personnel + mobile_personnel) as total_personnel
    FROM checkpoint_activities 
    WHERE user_id = $user_id
");
$checkpoint_stats = $result->fetch_assoc();
$stats['checkpoints'] = $checkpoint_stats['total'] ?? 0;
$stats['checkpoints_approved'] = $checkpoint_stats['approved'] ?? 0;
$stats['checkpoints_pending'] = $checkpoint_stats['pending'] ?? 0;
$stats['checkpoints_rejected'] = $checkpoint_stats['rejected'] ?? 0;
$stats['border_ops'] = $checkpoint_stats['border_ops'] ?? 0;
$stats['checkpoint_mobile'] = $checkpoint_stats['mobile_ops'] ?? 0;
$stats['tct_ovr'] = $checkpoint_stats['tct_ovr'] ?? 0;
$stats['checkpoint_arrests'] = $checkpoint_stats['arrests'] ?? 0;
$stats['checkpoint_personnel'] = $checkpoint_stats['total_personnel'] ?? 0;

// Oplan statistics
$result = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN oplan_type = 'Oplan Bakal' THEN 1 ELSE 0 END) as bakal,
        SUM(CASE WHEN oplan_type = 'Oplan Sita' THEN 1 ELSE 0 END) as sita,
        SUM(firearms_seized) as firearms,
        SUM(contraband_kg) as contraband,
        SUM(arrests_made) as arrests,
        SUM(personnel_count) as total_personnel
    FROM oplan_activities 
    WHERE user_id = $user_id
");
$oplan_stats = $result->fetch_assoc();
$stats['oplans'] = $oplan_stats['total'] ?? 0;
$stats['oplans_approved'] = $oplan_stats['approved'] ?? 0;
$stats['oplans_pending'] = $oplan_stats['pending'] ?? 0;
$stats['oplans_rejected'] = $oplan_stats['rejected'] ?? 0;
$stats['bakal'] = $oplan_stats['bakal'] ?? 0;
$stats['sita'] = $oplan_stats['sita'] ?? 0;
$stats['firearms'] = $oplan_stats['firearms'] ?? 0;
$stats['contraband'] = $oplan_stats['contraband'] ?? 0;
$stats['oplan_arrests'] = $oplan_stats['arrests'] ?? 0;
$stats['oplan_personnel'] = $oplan_stats['total_personnel'] ?? 0;

// Total activities
$stats['total_activities'] = $stats['patrols'] + $stats['checkpoints'] + $stats['oplans'];
$stats['total_approved'] = $stats['patrols_approved'] + $stats['checkpoints_approved'] + $stats['oplans_approved'];
$stats['total_pending'] = $stats['patrols_pending'] + $stats['checkpoints_pending'] + $stats['oplans_pending'];
$stats['total_rejected'] = $stats['patrols_rejected'] + $stats['checkpoints_rejected'] + $stats['oplans_rejected'];
$stats['total_personnel'] = $stats['patrol_personnel'] + $stats['checkpoint_personnel'] + $stats['oplan_personnel'];

// Calculate approval rate (only consider reports that have been decided)
$total_decided = $stats['total_approved'] + $stats['total_rejected'];
$stats['approval_rate'] = $total_decided > 0 ? round(($stats['total_approved'] / $total_decided) * 100, 1) : 0;

// Get recent activities for this user
$recent = [];

// Recent patrols
$patrols = $conn->query("
    SELECT 'patrol' as type, patrol_type as subtype, specific_location, 
           patrol_date as activity_date, patrol_time as activity_time, status,
           submitted_at
    FROM patrol_activities 
    WHERE user_id = $user_id
    ORDER BY submitted_at DESC
    LIMIT 3
");
while ($row = $patrols->fetch_assoc()) $recent[] = $row;

// Recent checkpoints
$checkpoints = $conn->query("
    SELECT 'checkpoint' as type, 'Checkpoint' as subtype, specific_location,
           checkpoint_date as activity_date, checkpoint_time as activity_time, status,
           submitted_at
    FROM checkpoint_activities 
    WHERE user_id = $user_id
    ORDER BY submitted_at DESC
    LIMIT 3
");
while ($row = $checkpoints->fetch_assoc()) $recent[] = $row;

// Recent oplans
$oplans = $conn->query("
    SELECT 'oplan' as type, oplan_type as subtype, specific_location,
           oplan_date as activity_date, oplan_time as activity_time, status,
           submitted_at
    FROM oplan_activities 
    WHERE user_id = $user_id
    ORDER BY submitted_at DESC
    LIMIT 3
");
while ($row = $oplans->fetch_assoc()) $recent[] = $row;

// Sort by most recent
usort($recent, function($a, $b) {
    return strtotime($b['submitted_at']) - strtotime($a['submitted_at']);
});
$recent = array_slice($recent, 0, 5);

// Format dates properly
$last_login = $user['last_login'] ? date('F d, Y h:i A', strtotime($user['last_login'])) : 'Never logged in';
$member_since = $user['date_hired'] ? date('F d, Y', strtotime($user['date_hired'])) : 'Not specified';
$date_hired_display = $user['date_hired'] ? date('Y-m-d', strtotime($user['date_hired'])) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../image/pnplogo.png">
    <title>PNP | User Profile</title>
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
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
    </style>
</head>
<body class="flex bg-[#0a3d62]">

    <!-- Edit Profile Modal -->
    <div id="editProfileModal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-[#08324f]">Edit User Profile</h3>
                <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            
            <form method="POST" class="space-y-4">
                <input type="hidden" name="update_profile" value="1">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rank</label>
                        <select name="rank" class="w-full p-2 border border-gray-300 rounded-lg">
                            <option value="PO1" <?php echo $user['rank'] == 'PO1' ? 'selected' : ''; ?>>PO1</option>
                            <option value="PO2" <?php echo $user['rank'] == 'PO2' ? 'selected' : ''; ?>>PO2</option>
                            <option value="PO3" <?php echo $user['rank'] == 'PO3' ? 'selected' : ''; ?>>PO3</option>
                            <option value="SPO1" <?php echo $user['rank'] == 'SPO1' ? 'selected' : ''; ?>>SPO1</option>
                            <option value="SPO2" <?php echo $user['rank'] == 'SPO2' ? 'selected' : ''; ?>>SPO2</option>
                            <option value="SPO3" <?php echo $user['rank'] == 'SPO3' ? 'selected' : ''; ?>>SPO3</option>
                            <option value="SPO4" <?php echo $user['rank'] == 'SPO4' ? 'selected' : ''; ?>>SPO4</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="<?php echo $user['email']; ?>" class="w-full p-2 border border-gray-300 rounded-lg">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                        <input type="text" name="first_name" value="<?php echo $user['first_name']; ?>" class="w-full p-2 border border-gray-300 rounded-lg">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                        <input type="text" name="last_name" value="<?php echo $user['last_name']; ?>" class="w-full p-2 border border-gray-300 rounded-lg">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                        <input type="text" name="contact_number" value="<?php echo $user['contact_number'] ?? ''; ?>" class="w-full p-2 border border-gray-300 rounded-lg">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Station</label>
                        <input type="text" name="station" value="<?php echo $user['station'] ?? 'Manolo Fortich MPS'; ?>" class="w-full p-2 border border-gray-300 rounded-lg">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                        <input type="text" name="unit" value="<?php echo $user['unit'] ?? 'Patrol Unit'; ?>" class="w-full p-2 border border-gray-300 rounded-lg">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <textarea name="address" rows="2" class="w-full p-2 border border-gray-300 rounded-lg"><?php echo $user['address'] ?? ''; ?></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Emergency Contact Name</label>
                        <input type="text" name="emergency_contact" value="<?php echo $user['emergency_contact_name'] ?? ''; ?>" class="w-full p-2 border border-gray-300 rounded-lg">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Emergency Contact Number</label>
                        <input type="text" name="emergency_number" value="<?php echo $user['emergency_contact_number'] ?? ''; ?>" class="w-full p-2 border border-gray-300 rounded-lg">
                    </div>
                </div>
                
                <div class="flex gap-3 justify-end mt-6">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-[#1f6fb2] text-white rounded-lg hover:bg-[#0a3d62]">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div id="resetPasswordModal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-[#08324f]">Reset Password</h3>
                <button onclick="closeResetModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            
            <p class="text-gray-600 mb-6">Are you sure you want to reset the password for this user? A new random password will be generated and displayed.</p>
            
            <form method="POST">
                <input type="hidden" name="reset_password" value="1">
                
                <div class="flex gap-3 justify-end">
                    <button type="button" onclick="closeResetModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Reset Password</button>
                </div>
            </form>
        </div>
    </div>

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
            <li class="p-3 rounded hover:bg-[#0a3d62] cursor-pointer mt-5 pt-4 border-t border-[#1a4b6d]">
                <a href="../logout.php" class="text-white no-underline block">
                    <i class="fas fa-sign-out-alt mr-3"></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-8 bg-[#eef2f6] overflow-y-auto h-screen">
        
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

        <!-- Header with Back Button -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6 border-l-4 border-yellow-400 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="admin_users.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
                <h2 class="text-2xl font-bold text-[#08324f]">User Profile</h2>
            </div>
            <span class="bg-blue-100 text-blue-700 px-4 py-2 rounded-full text-sm font-semibold">
                <i class="fas fa-id-card mr-2"></i> <?php echo $user['badge_number']; ?>
            </span>
        </div>

        <!-- User Profile Card -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <!-- Profile Header -->
            <div class="bg-[#08324f] p-8 text-white relative">
                <div class="flex items-start gap-8">
                    <!-- Profile Avatar -->
                    <div class="relative">
                        <div class="w-28 h-28 bg-yellow-400 rounded-full flex items-center justify-center text-[#08324f] text-4xl font-bold border-4 border-white shadow-lg">
                            <?php echo substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1); ?>
                        </div>
                        <div class="absolute -bottom-2 -right-2 px-3 py-1 rounded-full text-xs font-bold
                            <?php echo $user['account_status'] == 'active' ? 'bg-green-500' : 'bg-red-500'; ?> text-white">
                            <?php echo strtoupper($user['account_status']); ?>
                        </div>
                    </div>
                    
                    <!-- Basic Info -->
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold"><?php echo $user['rank'] . ' ' . $user['first_name'] . ' ' . $user['last_name']; ?></h1>
                        <p class="text-yellow-400 mt-2 text-lg"><?php echo $user['email']; ?></p>
                        <div class="flex gap-4 mt-4">
                            <div class="bg-[#1e4a6a] px-4 py-2 rounded-lg">
                                <p class="text-xs text-gray-300">Station</p>
                                <p class="font-semibold"><?php echo $user['station'] ?? 'Manolo Fortich MPS'; ?></p>
                            </div>
                            <div class="bg-[#1e4a6a] px-4 py-2 rounded-lg">
                                <p class="text-xs text-gray-300">Unit</p>
                                <p class="font-semibold"><?php echo $user['unit'] ?? 'Patrol Unit'; ?></p>
                            </div>
                            <div class="bg-[#1e4a6a] px-4 py-2 rounded-lg">
                                <p class="text-xs text-gray-300">Role</p>
                                <p class="font-semibold capitalize"><?php echo $user['role']; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Last Login - FIXED -->
                    <div class="text-right">
                        <p class="text-sm text-gray-300">Last Login</p>
                        <p class="font-semibold"><?php echo $last_login; ?></p>
                        <p class="text-sm text-gray-300 mt-2">Member Since</p>
                        <p class="font-semibold"><?php echo $member_since; ?></p>
                    </div>
                </div>
            </div>

            <!-- Profile Details -->
            <div class="p-8">
                <!-- Statistics Summary -->
                <h3 class="text-xl font-semibold text-[#08324f] mb-6 flex items-center gap-2">
                    <i class="fas fa-chart-pie text-yellow-500"></i> Activity Statistics
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <!-- Total Activities -->
                    <div class="stat-card bg-gradient-to-br from-blue-50 to-blue-100 p-5 rounded-lg shadow-md border-l-4 border-blue-500">
                        <p class="text-sm text-gray-600">Total Activities</p>
                        <p class="text-3xl font-bold text-[#08324f]"><?php echo $stats['total_activities']; ?></p>
                        <p class="text-xs text-gray-500 mt-2">All-time total</p>
                    </div>
                    
                    <!-- Total Personnel Deployed -->
                    <div class="stat-card bg-gradient-to-br from-green-50 to-green-100 p-5 rounded-lg shadow-md border-l-4 border-green-500">
                        <p class="text-sm text-gray-600">Personnel Deployed</p>
                        <p class="text-3xl font-bold text-[#08324f]"><?php echo $stats['total_personnel']; ?></p>
                        <p class="text-xs text-gray-500 mt-2">Across all ops</p>
                    </div>
                    
                    <!-- Arrests Total -->
                    <div class="stat-card bg-gradient-to-br from-red-50 to-red-100 p-5 rounded-lg shadow-md border-l-4 border-red-500">
                        <p class="text-sm text-gray-600">Total Arrests</p>
                        <p class="text-3xl font-bold text-[#08324f]"><?php echo $stats['checkpoint_arrests'] + $stats['oplan_arrests']; ?></p>
                        <p class="text-xs text-gray-500 mt-2">Checkpoint + Oplan</p>
                    </div>
                    
                    <!-- Approval Rate Card -->
                    <div class="stat-card bg-gradient-to-br from-yellow-50 to-yellow-100 p-5 rounded-lg shadow-md border-l-4 border-yellow-500">
                        <p class="text-sm text-gray-600">Approval Rate</p>
                        <div class="flex items-center justify-between">
                            <p class="text-3xl font-bold text-[#08324f]"><?php echo $stats['approval_rate']; ?>%</p>
                            <?php if ($stats['total_approved'] > 0 || $stats['total_rejected'] > 0): ?>
                            <div class="text-right">
                                <p class="text-xs text-green-600">Approved: <?php echo $stats['total_approved']; ?></p>
                                <p class="text-xs text-red-600">Rejected: <?php echo $stats['total_rejected']; ?></p>
                                <p class="text-xs text-gray-500">Pending: <?php echo $stats['total_pending']; ?></p>
                            </div>
                            <?php else: ?>
                            <p class="text-xs text-gray-500">No reports with decision</p>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            <?php 
                            if ($stats['total_approved'] + $stats['total_rejected'] > 0) {
                                echo $stats['total_approved'] . ' approved out of ' . ($stats['total_approved'] + $stats['total_rejected']) . ' decided reports';
                            } else {
                                echo 'No reports have been reviewed yet';
                            }
                            ?>
                        </p>
                    </div>
                </div>

                <!-- Contact Information Card - NEW -->
                <div class="mb-8 p-6 bg-gray-50 rounded-lg border-l-4 border-blue-500">
                    <h4 class="text-lg font-semibold text-[#08324f] mb-4 flex items-center gap-2">
                        <i class="fas fa-address-card text-yellow-500"></i> Contact Information
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Contact Number</p>
                            <p class="font-semibold"><?php echo $user['contact_number'] ?? 'Not specified'; ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Address</p>
                            <p class="font-semibold"><?php echo $user['address'] ?? 'Not specified'; ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Emergency Contact</p>
                            <p class="font-semibold"><?php echo $user['emergency_contact_name'] ?? 'Not specified'; ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Emergency Number</p>
                            <p class="font-semibold"><?php echo $user['emergency_contact_number'] ?? 'Not specified'; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Patrol Statistics with Status Breakdown -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-[#08324f] mb-4 flex items-center gap-2">
                        <i class="fas fa-walking text-yellow-500"></i> Patrol Operations
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <!-- Status Summary -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600 mb-2">Status Breakdown</p>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-green-600">Approved:</span>
                                    <span class="font-bold"><?php echo $stats['patrols_approved']; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-yellow-600">Pending:</span>
                                    <span class="font-bold"><?php echo $stats['patrols_pending']; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-red-600">Rejected:</span>
                                    <span class="font-bold"><?php echo $stats['patrols_rejected']; ?></span>
                                </div>
                            </div>
                        </div>
                        <!-- Type Summary -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600 mb-2">By Type</p>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span>Foot Patrol:</span>
                                    <span class="font-bold"><?php echo $stats['foot']; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Mobile Patrol:</span>
                                    <span class="font-bold"><?php echo $stats['mobile']; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Motor Patrol:</span>
                                    <span class="font-bold"><?php echo $stats['motor']; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Checkpoint Statistics with Status Breakdown -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-[#08324f] mb-4 flex items-center gap-2">
                        <i class="fas fa-map-marker-alt text-yellow-500"></i> Checkpoint Operations
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <!-- Status Summary -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600 mb-2">Status Breakdown</p>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-green-600">Approved:</span>
                                    <span class="font-bold"><?php echo $stats['checkpoints_approved']; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-yellow-600">Pending:</span>
                                    <span class="font-bold"><?php echo $stats['checkpoints_pending']; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-red-600">Rejected:</span>
                                    <span class="font-bold"><?php echo $stats['checkpoints_rejected']; ?></span>
                                </div>
                            </div>
                        </div>
                        <!-- Accomplishments Summary -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600 mb-2">Accomplishments</p>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span>Border Control:</span>
                                    <span class="font-bold"><?php echo $stats['border_ops']; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Mobile Ops:</span>
                                    <span class="font-bold"><?php echo $stats['checkpoint_mobile']; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>TCT/OVR:</span>
                                    <span class="font-bold"><?php echo $stats['tct_ovr']; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Arrests:</span>
                                    <span class="font-bold"><?php echo $stats['checkpoint_arrests']; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Oplan Statistics with Status Breakdown -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-[#08324f] mb-4 flex items-center gap-2">
                        <i class="fas fa-shield-alt text-yellow-500"></i> Oplan Operations
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <!-- Status Summary -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600 mb-2">Status Breakdown</p>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-green-600">Approved:</span>
                                    <span class="font-bold"><?php echo $stats['oplans_approved']; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-yellow-600">Pending:</span>
                                    <span class="font-bold"><?php echo $stats['oplans_pending']; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-red-600">Rejected:</span>
                                    <span class="font-bold"><?php echo $stats['oplans_rejected']; ?></span>
                                </div>
                            </div>
                        </div>
                        <!-- Accomplishments Summary -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600 mb-2">Accomplishments</p>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span>Oplan Bakal:</span>
                                    <span class="font-bold"><?php echo $stats['bakal']; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Oplan Sita:</span>
                                    <span class="font-bold"><?php echo $stats['sita']; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Firearms:</span>
                                    <span class="font-bold"><?php echo $stats['firearms']; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Contraband:</span>
                                    <span class="font-bold"><?php echo number_format($stats['contraband'], 2); ?> kg</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Arrests:</span>
                                    <span class="font-bold"><?php echo $stats['oplan_arrests']; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-[#08324f] mb-4 flex items-center gap-2">
                        <i class="fas fa-history text-yellow-500"></i> Recent Activities
                    </h4>
                    <div class="space-y-3">
                        <?php if (empty($recent)): ?>
                        <p class="text-gray-500 text-center py-4">No recent activities found</p>
                        <?php else: ?>
                        <?php foreach ($recent as $activity): ?>
                        <div class="bg-gray-50 p-4 rounded-lg border-l-4 
                            <?php 
                            echo $activity['type'] == 'patrol' ? 'border-blue-500' : 
                                ($activity['type'] == 'checkpoint' ? 'border-red-500' : 'border-green-500'); 
                            ?>">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="font-medium">
                                        <?php 
                                        if ($activity['type'] == 'patrol') echo $activity['subtype'];
                                        elseif ($activity['type'] == 'checkpoint') echo 'Checkpoint Operation';
                                        else echo $activity['subtype'];
                                        ?>
                                    </span>
                                    <p class="text-sm text-gray-600 mt-1"><?php echo $activity['specific_location']; ?></p>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-medium
                                        <?php 
                                        echo $activity['status'] == 'approved' ? 'text-green-600' : 
                                            ($activity['status'] == 'pending' ? 'text-yellow-600' : 'text-red-600'); 
                                        ?>">
                                        <?php echo ucfirst($activity['status']); ?>
                                    </span>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <?php echo date('M d, Y', strtotime($activity['activity_date'])); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Action Buttons - UPDATED -->
                <div class="flex gap-3 justify-end border-t pt-6">
                    <button onclick="openEditModal()" class="bg-[#1f6fb2] hover:bg-[#0a3d62] text-white px-6 py-2 rounded-lg transition flex items-center gap-2">
                        <i class="fas fa-edit"></i> Edit Profile
                    </button>
                    
                    <button onclick="openResetModal()" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded-lg transition flex items-center gap-2">
                        <i class="fas fa-key"></i> Reset Password
                    </button>
                    
                    <?php if ($user['account_status'] == 'active'): ?>
                    <a href="admin_users.php?action=deactivate&id=<?php echo $user['user_id']; ?>" 
                       class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg transition flex items-center gap-2"
                       onclick="return confirm('Deactivate this user?')">
                        <i class="fas fa-ban"></i> Deactivate
                    </a>
                    <?php else: ?>
                    <a href="admin_users.php?action=activate&id=<?php echo $user['user_id']; ?>" 
                       class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition flex items-center gap-2"
                       onclick="return confirm('Activate this user?')">
                        <i class="fas fa-check-circle"></i> Activate
                    </a>
                    <?php endif; ?>
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

        function openEditModal() {
            document.getElementById('editProfileModal').classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('editProfileModal').classList.remove('active');
        }

        function openResetModal() {
            document.getElementById('resetPasswordModal').classList.add('active');
        }

        function closeResetModal() {
            document.getElementById('resetPasswordModal').classList.remove('active');
        }

        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            const editModal = document.getElementById('editProfileModal');
            const resetModal = document.getElementById('resetPasswordModal');
            
            if (event.target === editModal) {
                editModal.classList.remove('active');
            }
            if (event.target === resetModal) {
                resetModal.classList.remove('active');
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>