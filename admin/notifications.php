<?php
// =====================================================
// FILE: admin/notifications.php
// PURPOSE: Display all notifications for admin
// =====================================================

session_start();
require_once '../config/db_connect.php';
requireAdmin();

// Mark all as read when viewing this page
$stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();

// Get all notifications
$stmt = $conn->prepare("
    SELECT n.*, 
           CASE 
               WHEN n.report_type = 'patrol' THEN CONCAT('view_report.php?type=patrol&id=', n.report_id)
               WHEN n.report_type = 'checkpoint' THEN CONCAT('view_report.php?type=checkpoint&id=', n.report_id)
               WHEN n.report_type = 'oplan' THEN CONCAT('view_report.php?type=oplan&id=', n.report_id)
           END as report_link
    FROM notifications n
    WHERE n.user_id = ?
    ORDER BY n.created_at DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$notifications = $stmt->get_result();
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
                <a href="notifications.php" class="text-white no-underline block">
                    <i class="fas fa-bell mr-3"></i> Notifications
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
                <h2 class="text-2xl font-bold text-[#08324f]">Notifications</h2>
                <p class="text-gray-600 mt-1">All system notifications and alerts</p>
            </div>
            <button onclick="markAllAsRead()" class="bg-[#1f6fb2] text-white px-4 py-2 rounded-lg hover:bg-[#0a3d62] transition text-sm">
                <i class="fas fa-check-double mr-2"></i> Mark All as Read
            </button>
        </div>

        <!-- Notifications List -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <?php if ($notifications->num_rows > 0): ?>
                <?php while ($notif = $notifications->fetch_assoc()): 
                    $icon = '';
                    $color = '';
                    
                    switch($notif['type']) {
                        case 'new_report':
                            $icon = 'fa-file-alt';
                            $color = 'text-blue-600';
                            break;
                        case 'report_approved':
                            $icon = 'fa-check-circle';
                            $color = 'text-green-600';
                            break;
                        case 'report_rejected':
                            $icon = 'fa-times-circle';
                            $color = 'text-red-600';
                            break;
                        default:
                            $icon = 'fa-bell';
                            $color = 'text-gray-600';
                    }
                    
                    $bgClass = $notif['is_read'] ? 'bg-white' : 'bg-blue-50';
                ?>
                <div class="<?php echo $bgClass; ?> border-b border-gray-200 hover:bg-gray-50 transition">
                    <a href="<?php echo $notif['report_link']; ?>" class="block p-4">
                        <div class="flex items-start gap-4">
                            <div class="<?php echo $color; ?> text-2xl">
                                <i class="fas <?php echo $icon; ?>"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-gray-800"><?php echo $notif['message']; ?></p>
                                <div class="flex items-center gap-4 mt-2 text-sm">
                                    <span class="text-gray-500">
                                        <i class="far fa-clock mr-1"></i> <?php echo date('M d, Y h:i A', strtotime($notif['created_at'])); ?>
                                    </span>
                                    <?php if (!$notif['is_read']): ?>
                                        <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">New</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="p-12 text-center text-gray-500">
                    <i class="fas fa-bell-slash text-5xl mb-4"></i>
                    <p class="text-lg">No notifications yet</p>
                    <p class="text-sm mt-2">Notifications will appear here when there are new reports</p>
                </div>
            <?php endif; ?>
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

        function markAllAsRead() {
            fetch('get_notifications.php?action=mark_read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                }
            }).then(() => {
                location.reload();
            });
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>