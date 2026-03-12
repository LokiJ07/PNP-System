<?php
// =====================================================
// FILE: admin/notifications.php
// PURPOSE: Display all notifications for admin
// FIXED: Removed report_type dependency
// =====================================================

session_start();
require_once '../config/db_connect.php';
requireAdmin();

$user_id = $_SESSION['user_id'];

// Mark all as read when viewing this page
$stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

// Get all notifications - FIXED: simplified query
$stmt = $conn->prepare("
    SELECT n.*
    FROM notifications n
    WHERE n.user_id = ?
    ORDER BY n.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result();

// Admin info for sidebar
$admin_name = $_SESSION['full_name'] ?? 'Admin';
$admin_email = $_SESSION['email'] ?? 'admin@pnp.gov.ph';

// Helper function for time ago
function timeAgo($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    
    $minutes = round($seconds / 60);
    $hours = round($seconds / 3600);
    $days = round($seconds / 86400);
    
    if ($seconds <= 60) {
        return "Just now";
    } else if ($minutes <= 60) {
        return ($minutes == 1) ? "1 minute ago" : "$minutes minutes ago";
    } else if ($hours <= 24) {
        return ($hours == 1) ? "1 hour ago" : "$hours hours ago";
    } else if ($days <= 7) {
        return ($days == 1) ? "Yesterday" : "$days days ago";
    } else {
        return date('M d, Y', $time_ago);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../image/pnplogo.png">
    <title>PNP | Notifications</title>
    
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
        
        /* Notification item hover */
        .notification-item {
            transition: all 0.2s ease;
        }
        .notification-item:hover {
            background-color: #f8fafc;
            transform: translateX(2px);
        }
        
        /* Status badges */
        .badge-new {
            background-color: #3b82f6;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 600;
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
            
            <li><a href="admin_users.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1e4a6a] transition"><i class="fas fa-users w-5"></i> Users</a></li>
            <li><a href="accomplishment_report.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1e4a6a] transition"><i class="fas fa-file-alt w-5"></i> Accomplishment Report</a></li>
            <li><a href="all_reports.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1e4a6a] transition"><i class="fas fa-folder-open w-5"></i> All Reports</a></li>
            <li class="bg-[#1e4a6a] rounded-lg"><a href="notifications.php" class="flex items-center gap-3 p-3"><i class="fas fa-bell w-5 text-yellow-400"></i> Notifications</a></li>
            
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
        <div class="bg-white p-4 md:p-6 rounded-lg shadow-md mb-6 border-l-4 border-yellow-400 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-[#08324f] flex items-center gap-2">
                    <i class="fas fa-bell text-yellow-500"></i>
                    Notifications
                </h2>
                <p class="text-sm text-gray-600 mt-1">All system notifications and alerts</p>
            </div>
            <div class="flex gap-2">
                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">
                    <i class="fas fa-check-circle mr-1"></i> Auto-Approved
                </span>
                <button onclick="markAllAsRead()" class="bg-[#1f6fb2] text-white px-4 py-2 rounded-lg hover:bg-[#0a3d62] transition text-sm flex items-center gap-2">
                    <i class="fas fa-check-double"></i> Mark All Read
                </button>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <?php if ($notifications->num_rows > 0): ?>
                <div class="divide-y divide-gray-100">
                    <?php while ($notif = $notifications->fetch_assoc()): 
                        $icon = 'fa-bell';
                        $color = 'text-gray-600';
                        $bgColor = $notif['is_read'] ? 'bg-white' : 'bg-blue-50';
                        
                        if (strpos($notif['message'], 'patrol') !== false) {
                            $icon = 'fa-walking';
                            $color = 'text-blue-600';
                        } elseif (strpos($notif['message'], 'checkpoint') !== false) {
                            $icon = 'fa-map-marker-alt';
                            $color = 'text-red-600';
                        } elseif (strpos($notif['message'], 'oplan') !== false) {
                            $icon = 'fa-shield-alt';
                            $color = 'text-green-600';
                        }
                        
                        $time_ago = timeAgo($notif['created_at']);
                        
                        // Create link based on message
                        $link = '#';
                        if (strpos($notif['message'], 'patrol') !== false) {
                            $link = 'all_reports.php?view=daily';
                        } elseif (strpos($notif['message'], 'checkpoint') !== false) {
                            $link = 'checkpoint.php';
                        } elseif (strpos($notif['message'], 'oplan') !== false) {
                            $link = 'oplanbakal.php';
                        }
                    ?>
                    <div class="notification-item <?php echo $bgColor; ?> hover:bg-gray-50 transition">
                        <a href="<?php echo $link; ?>" class="block p-4 md:p-5">
                            <div class="flex items-start gap-3 md:gap-4">
                                <!-- Icon -->
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center <?php echo $color; ?>">
                                        <i class="fas <?php echo $icon; ?> text-lg"></i>
                                    </div>
                                </div>
                                
                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm md:text-base text-gray-800"><?php echo htmlspecialchars($notif['message']); ?></p>
                                    <div class="flex flex-wrap items-center gap-2 md:gap-4 mt-2">
                                        <span class="text-xs text-gray-500 flex items-center gap-1">
                                            <i class="far fa-clock"></i> <?php echo $time_ago; ?>
                                        </span>
                                        <?php if (!$notif['is_read']): ?>
                                            <span class="badge-new">New</span>
                                        <?php endif; ?>
                                        <span class="text-xs text-blue-600 hover:text-blue-800 flex items-center gap-1 ml-auto">
                                            View Details <i class="fas fa-chevron-right text-xs"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="py-16 px-4 text-center text-gray-500">
                    <div class="text-6xl mb-4">
                        <i class="fas fa-bell-slash text-gray-300"></i>
                    </div>
                    <p class="text-lg font-medium">No notifications yet</p>
                    <p class="text-sm mt-2 max-w-md mx-auto">
                        Notifications will appear here when users submit new reports.
                    </p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Footer note -->
        <div class="mt-4 text-xs text-gray-500 text-center">
            <i class="fas fa-info-circle mr-1"></i> 
            All reports are automatically approved. Notifications are for informational purposes only.
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

        // Mark all as read
        function markAllAsRead() {
            fetch('get_notifications.php?action=mark_read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to mark notifications as read');
            });
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>