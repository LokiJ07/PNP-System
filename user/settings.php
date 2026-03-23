<?php
// =====================================================
// FILE: user/settings.php
// PURPOSE: User settings page for profile management
// =====================================================
session_start();
require_once '../config/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $contact_number = $_POST['contact_number'] ?? '';
    $address = $_POST['address'] ?? '';
    $emergency_contact = $_POST['emergency_contact'] ?? '';
    $emergency_number = $_POST['emergency_number'] ?? '';
    
    // Handle profile picture upload (max 15MB)
    $profile_pic_path = null;
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_pic'];
        $file_size = $file['size'] / (1024 * 1024); // Size in MB
        
        if ($file_size <= 15) { // Max 15MB for profile pics
            $upload_dir = '../uploads/profiles/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Get file extension and validate
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($extension, $allowed)) {
                $_SESSION['error'] = 'Only JPG, JPEG, PNG, GIF, and WEBP files are allowed';
                header('Location: settings.php');
                exit();
            }
            
            // Generate unique filename
            $filename = 'profile_' . $user_id . '_' . time() . '.' . $extension;
            $filepath = $upload_dir . $filename;
            $relative_path = 'uploads/profiles/' . $filename;
            
            // Upload file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $profile_pic_path = $relative_path;
                
                // Delete old profile picture if exists
                $old_pic_query = $conn->query("SELECT profile_pic FROM users WHERE user_id = $user_id");
                if ($old_pic_query && $old_pic_query->num_rows > 0) {
                    $old_pic = $old_pic_query->fetch_assoc();
                    if ($old_pic && $old_pic['profile_pic'] && file_exists('../' . $old_pic['profile_pic'])) {
                        unlink('../' . $old_pic['profile_pic']);
                    }
                }
            }
        } else {
            $_SESSION['error'] = 'Profile picture must be less than 15MB';
            header('Location: settings.php');
            exit();
        }
    }
    
    // Update user information
    if ($profile_pic_path) {
        $update_stmt = $conn->prepare("
            UPDATE users SET 
                contact_number = ?, address = ?,
                emergency_contact_name = ?, emergency_contact_number = ?,
                profile_pic = ?
            WHERE user_id = ?
        ");
        $update_stmt->bind_param("sssssi", 
            $contact_number, $address,
            $emergency_contact, $emergency_number, $profile_pic_path, $user_id
        );
    } else {
        $update_stmt = $conn->prepare("
            UPDATE users SET 
                contact_number = ?, address = ?,
                emergency_contact_name = ?, emergency_contact_number = ?
            WHERE user_id = ?
        ");
        $update_stmt->bind_param("ssssi", 
            $contact_number, $address,
            $emergency_contact, $emergency_number, $user_id
        );
    }
    
    if ($update_stmt->execute()) {
        $_SESSION['success'] = 'Profile updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update profile: ' . $conn->error;
    }
    $update_stmt->close();
    
    header('Location: settings.php');
    exit();
}

// Handle password change (one-time change)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Verify current password
    $check_stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $user_data = $result->fetch_assoc();
    
    if ($current_password !== $user_data['password']) {
        $_SESSION['error'] = 'Current password is incorrect';
    } elseif (strlen($new_password) < 6) {
        $_SESSION['error'] = 'New password must be at least 6 characters';
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['error'] = 'New passwords do not match';
    } else {
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $update_stmt->bind_param("si", $new_password, $user_id);
        if ($update_stmt->execute()) {
            $_SESSION['success'] = 'Password changed successfully';
        } else {
            $_SESSION['error'] = 'Failed to change password';
        }
        $update_stmt->close();
    }
    $check_stmt->close();
    
    header('Location: settings.php');
    exit();
}

// Get user information from database
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    session_destroy();
    header('Location: ../index.php');
    exit();
}

// Format dates
if (!empty($user['last_login'])) {
    $last_login = date('F d, Y h:i A', strtotime($user['last_login']));
} else {
    $last_login = 'First login';
}

$member_since = $user['date_hired'] ? date('F d, Y', strtotime($user['date_hired'])) : 'Not specified';

// Default profile picture
if (!empty($user['profile_pic']) && file_exists('../' . $user['profile_pic'])) {
    $profile_pic = '../' . $user['profile_pic'];
    $profile_pic_version = '?v=' . filemtime('../' . $user['profile_pic']);
} else {
    $profile_pic = 'https://ui-avatars.com/api/?name=' . urlencode($user['first_name'] . '+' . $user['last_name']) . '&size=200&background=1f6fb2&color=fff';
    $profile_pic_version = '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <link rel="icon" type="image/png" href="../image/pnplogo.png">
    <title>PNP | Settings</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media (max-width: 640px) {
            .sidebar-mobile {
                position: fixed;
                left: -100%;
                transition: left 0.3s ease;
                z-index: 50;
                width: 80%;
                max-width: 280px;
            }
            .sidebar-mobile.open {
                left: 0;
            }
        }
        button, .clickable {
            min-height: 44px;
            min-width: 44px;
        }
        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #ffc107;
        }
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        .animate-slideIn {
            animation: slideIn 0.3s ease forwards;
        }
    </style>
</head>
<body class="flex flex-col md:flex-row bg-[#0a3d62] min-h-screen">

    <!-- Mobile Menu Button -->
    <button id="mobileMenuBtn" class="md:hidden fixed top-4 left-4 z-50 bg-[#08324f] text-white p-3 rounded-lg shadow-lg">
        <i class="fas fa-bars text-xl"></i>
    </button>

    <!-- Mobile Menu Overlay -->
    <div id="menuOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden" onclick="closeMobileMenu()"></div>

    <!-- Sidebar -->
    <div id="sidebar" class="w-full md:w-[260px] bg-gradient-to-b from-[#003366] to-[#002244] text-white p-4 md:p-5 md:sticky md:top-0 md:h-screen overflow-y-auto sidebar-mobile fixed top-0 left-[-100%] h-screen z-50 transition-all duration-300 ease-in-out shadow-xl">
        <button id="closeSidebar" class="md:hidden absolute top-4 right-4 text-white text-xl">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="flex items-center gap-3 mb-6 pb-3 border-b border-[#FFD700] mt-12 md:mt-0">
            <img src="../image/pnplogo.png" class="w-10 h-10 object-contain" alt="PNP Logo">
            <div>
                <h2 class="text-xl font-bold tracking-wide">PNP</h2>
                <p class="text-xs text-yellow-300">Manolo Fortich</p>
            </div>
        </div>

        <!-- User Profile Section -->
        <div class="bg-gradient-to-b from-[#1a4d8c] to-[#003366] p-5 rounded-xl mb-6 text-center border border-[#FFD700] shadow-lg">
            <div class="relative mx-auto w-24 h-24 mb-3">
                <img src="<?php echo $profile_pic . $profile_pic_version; ?>" class="w-full h-full rounded-full object-cover border-3 border-[#FFD700] shadow-lg" alt="Profile" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($user['first_name'].'+'.$user['last_name']); ?>&size=100&background=003366&color=FFD700'">
                <div class="absolute bottom-1 right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white"></div>
            </div>
            
            <h3 class="text-lg font-bold text-[#FFD700]"><?php echo $user['rank'] . ' ' . $user['first_name'] . ' ' . $user['last_name']; ?></h3>
            <p class="text-xs text-gray-300 mb-2">Badge: <?php echo $user['badge_number']; ?></p>
        </div>

        <!-- Menu -->
        <ul class="space-y-2">
            <li class="p-3 rounded-lg bg-[#1a4d8c] border-l-4 border-[#FFD700] hover:bg-[#2a5d9c] transition">
                <a href="user_dashboard.php" class="text-white no-underline block text-sm md:text-base font-medium">
                    <i class="fas fa-tachometer-alt mr-3 w-5 text-[#FFD700]"></i> Dashboard
                </a>
            </li>
            <li class="p-3 rounded-lg hover:bg-[#1a4d8c] transition">
                <a href="my_reports.php" class="text-white no-underline block text-sm md:text-base font-medium">
                    <i class="fas fa-file-alt mr-3 w-5"></i> My Reports
                </a>
            </li>
            <li class="p-3 rounded-lg hover:bg-[#1a4d8c] transition">
                <a href="settings.php" class="text-white no-underline block text-sm md:text-base font-medium">
                    <i class="fas fa-cog mr-3 w-5"></i> Settings
                </a>
            </li>
            <li class="my-4 border-t border-[#FFD700] opacity-30"></li>
            <li class="p-3 rounded-lg bg-red-600 hover:bg-red-700 transition cursor-pointer">
                <a href="../logout.php" class="text-white no-underline block text-sm md:text-base font-medium">
                    <i class="fas fa-sign-out-alt mr-3 w-5"></i> Logout
                </a>
            </li>
            <li class="mt-6 text-center text-xs text-gray-400">
                <p>PNP Manolo Fortich v2.0</p>
                <p class="mt-1">© 2026 All Rights Reserved</p>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-3 md:p-6 lg:p-8 bg-[#eef2f6] overflow-y-auto min-h-screen">
        
        <!-- Display Session Messages -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-lg animate-slideIn">
            <i class="fas fa-check-circle mr-2"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-lg animate-slideIn">
            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="bg-white p-3 md:p-4 rounded-lg shadow-sm mb-4 md:mb-6 ml-10 md:ml-0">
            <h2 class="text-xl md:text-2xl font-bold text-[#08324f]">Settings</h2>
            <p class="text-xs md:text-sm text-gray-600 mt-1">Manage your profile and account settings</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Profile Settings -->
            <div class="bg-white p-4 md:p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold text-[#08324f] mb-4 flex items-center gap-2">
                    <i class="fas fa-user-circle text-yellow-500"></i> Profile Information
                </h3>
                
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="update_profile" value="1">
                    
                    <!-- Profile Picture Upload (15MB max) -->
                    <div class="text-center mb-6">
                        <div class="relative inline-block">
                            <img id="profilePreview" src="<?php echo $profile_pic . $profile_pic_version; ?>" class="profile-avatar" alt="Profile" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($user['first_name'].'+'.$user['last_name']); ?>&size=200&background=1f6fb2&color=fff'">
                            <label for="profile_pic" class="absolute bottom-0 right-0 bg-[#1f6fb2] text-white p-3 rounded-full cursor-pointer hover:bg-[#0a3d62] transition">
                                <i class="fas fa-camera"></i>
                            </label>
                            <input type="file" id="profile_pic" name="profile_pic" accept="image/jpeg,image/png,image/gif,image/webp" class="hidden" onchange="previewImage(this)">
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Max size: 15MB. JPG, PNG, GIF, WEBP</p>
                    </div>
                    
                    <!-- Personal Information (Read-only) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rank</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['rank']); ?>" class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100" readonly>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Badge Number</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['badge_number']); ?>" class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100" readonly>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['first_name']); ?>" class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100" readonly>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['last_name']); ?>" class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100" readonly>
                        </div>
                    </div>
                    
                    <!-- Editable Contact Information -->
                    <div class="border-t pt-4 mt-4">
                        <h4 class="font-medium text-[#08324f] mb-3">Contact Information</h4>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                                <input type="text" name="contact_number" value="<?php echo htmlspecialchars($user['contact_number'] ?? ''); ?>" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2]">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                <textarea name="address" rows="2" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2]"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Emergency Contact Name</label>
                                <input type="text" name="emergency_contact" value="<?php echo htmlspecialchars($user['emergency_contact_name'] ?? ''); ?>" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2]">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Emergency Contact Number</label>
                                <input type="text" name="emergency_number" value="<?php echo htmlspecialchars($user['emergency_contact_number'] ?? ''); ?>" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2]">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="mt-6">
                        <button type="submit" class="w-full bg-[#1f6fb2] text-white py-3 rounded-lg hover:bg-[#0a3d62] transition font-semibold">
                            <i class="fas fa-save mr-2"></i> Save Profile Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Password Change & Account Info -->
            <div class="space-y-6">
                <!-- Change Password (One-time change) -->
                <div class="bg-white p-4 md:p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold text-[#08324f] mb-4 flex items-center gap-2">
                        <i class="fas fa-lock text-yellow-500"></i> Change Password
                    </h3>
                    
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="change_password" value="1">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                            <input type="password" name="current_password" required class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2]">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input type="password" name="new_password" required class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2]">
                            <p class="text-xs text-gray-500 mt-1">Minimum 6 characters</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                            <input type="password" name="confirm_password" required class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1f6fb2]">
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" class="w-full bg-yellow-500 text-white py-3 rounded-lg hover:bg-yellow-600 transition font-semibold">
                                <i class="fas fa-key mr-2"></i> Change Password
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Account Information -->
                <div class="bg-white p-4 md:p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold text-[#08324f] mb-4 flex items-center gap-2">
                        <i class="fas fa-info-circle text-yellow-500"></i> Account Information
                    </h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b">
                            <span class="text-sm text-gray-600">Member Since</span>
                            <span class="text-sm font-medium text-[#08324f]"><?php echo $member_since; ?></span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b">
                            <span class="text-sm text-gray-600">Last Login</span>
                            <span class="text-sm font-medium text-[#08324f]"><?php echo $last_login; ?></span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b">
                            <span class="text-sm text-gray-600">Account Status</span>
                            <span class="text-sm font-medium text-green-600">Active</span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2">
                            <span class="text-sm text-gray-600">User ID</span>
                            <span class="text-sm font-medium text-[#08324f]"><?php echo $user['user_id']; ?></span>
                        </div>
                    </div>
                </div>
            </div>
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
        window.addEventListener('resize', function() { if (window.innerWidth >= 768) closeMobileMenu(); });

        // Image preview
        function previewImage(input) {
            if (input.files && input.files[0]) {
                // Check file size (15MB max)
                const fileSize = input.files[0].size / (1024 * 1024);
                if (fileSize > 15) {
                    alert('File size must be less than 15MB');
                    input.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profilePreview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>