<?php
// =====================================================
// FILE: admin/admin_dashboard.php
// PURPOSE: Admin dashboard with real data from database
// =====================================================

require_once '../config/db_connect.php';
requireAdmin(); // Function to ensure only admins can access

// Get statistics from database
$stats = [];

// Total users
$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$stats['total_users'] = $result->fetch_assoc()['total'];

// Active users
$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'user' AND account_status = 'active'");
$stats['active_users'] = $result->fetch_assoc()['total'];

// Total patrols
$result = $conn->query("SELECT COUNT(*) as total FROM patrol_activities");
$stats['total_patrols'] = $result->fetch_assoc()['total'];

// Total checkpoints
$result = $conn->query("SELECT COUNT(*) as total FROM checkpoint_activities");
$stats['total_checkpoints'] = $result->fetch_assoc()['total'];

// Total oplans
$result = $conn->query("SELECT COUNT(*) as total FROM oplan_activities");
$stats['total_oplans'] = $result->fetch_assoc()['total'];

// Foot patrols
$result = $conn->query("SELECT COUNT(*) as total FROM patrol_activities WHERE patrol_type = 'Foot Patrol'");
$stats['foot_patrols'] = $result->fetch_assoc()['total'];

// Mobile patrols
$result = $conn->query("SELECT COUNT(*) as total FROM patrol_activities WHERE patrol_type = 'Mobile Patrol'");
$stats['mobile_patrols'] = $result->fetch_assoc()['total'];

// Motor patrols
$result = $conn->query("SELECT COUNT(*) as total FROM patrol_activities WHERE patrol_type = 'Motorcycle Patrol'");
$stats['motor_patrols'] = $result->fetch_assoc()['total'];

// Border control ops
$result = $conn->query("SELECT SUM(border_control_ops) as total FROM checkpoint_activities");
$stats['border_ops'] = $result->fetch_assoc()['total'] ?? 0;

// Mobile checkpoint ops
$result = $conn->query("SELECT SUM(mobile_checkpoint_ops) as total FROM checkpoint_activities");
$stats['mobile_checkpoint_ops'] = $result->fetch_assoc()['total'] ?? 0;

// TCT/OVR accomplishments
$result = $conn->query("SELECT SUM(tct_ovr_accomplishment) as total FROM checkpoint_activities");
$stats['tct_ovr'] = $result->fetch_assoc()['total'] ?? 0;

// Arrests from checkpoints
$result = $conn->query("SELECT SUM(arrested_accomplishment) as total FROM checkpoint_activities");
$stats['checkpoint_arrests'] = $result->fetch_assoc()['total'] ?? 0;

// Oplan Bakal
$result = $conn->query("SELECT COUNT(*) as total FROM oplan_activities WHERE oplan_type = 'Oplan Bakal'");
$stats['oplan_bakal'] = $result->fetch_assoc()['total'];

// Oplan Sita
$result = $conn->query("SELECT COUNT(*) as total FROM oplan_activities WHERE oplan_type = 'Oplan Sita'");
$stats['oplan_sita'] = $result->fetch_assoc()['total'];

// Firearms seized
$result = $conn->query("SELECT SUM(firearms_seized) as total FROM oplan_activities WHERE oplan_type = 'Oplan Bakal'");
$stats['firearms'] = $result->fetch_assoc()['total'] ?? 0;

// Contraband seized
$result = $conn->query("SELECT SUM(contraband_kg) as total FROM oplan_activities WHERE oplan_type = 'Oplan Sita'");
$stats['contraband'] = $result->fetch_assoc()['total'] ?? 0;

// Oplan arrests
$result = $conn->query("SELECT SUM(arrests_made) as total FROM oplan_activities");
$stats['oplan_arrests'] = $result->fetch_assoc()['total'] ?? 0;

// Recent activities for display
$recent = [];

// Get recent patrols
$patrols = $conn->query("
    SELECT p.patrol_id as id, 'patrol' as type, p.patrol_type as subtype, 
           CONCAT(u.rank, ' ', u.first_name, ' ', u.last_name) as officer_name,
           b.barangay_name, p.specific_location, p.patrol_date, p.patrol_time, p.status,
           p.submitted_at
    FROM patrol_activities p
    JOIN users u ON p.user_id = u.user_id
    JOIN barangays b ON p.barangay_id = b.barangay_id
    ORDER BY p.submitted_at DESC
    LIMIT 5
");

while ($row = $patrols->fetch_assoc()) {
    $recent[] = $row;
}

// Get recent checkpoints
$checkpoints = $conn->query("
    SELECT c.checkpoint_id as id, 'checkpoint' as type, 'Checkpoint' as subtype,
           CONCAT(u.rank, ' ', u.first_name, ' ', u.last_name) as officer_name,
           b.barangay_name, c.specific_location, c.checkpoint_date, c.checkpoint_time, c.status,
           c.submitted_at
    FROM checkpoint_activities c
    JOIN users u ON c.user_id = u.user_id
    JOIN barangays b ON c.barangay_id = b.barangay_id
    ORDER BY c.submitted_at DESC
    LIMIT 5
");

while ($row = $checkpoints->fetch_assoc()) {
    $recent[] = $row;
}

// Get recent oplans
$oplans = $conn->query("
    SELECT o.oplan_id as id, 'oplan' as type, o.oplan_type as subtype,
           CONCAT(u.rank, ' ', u.first_name, ' ', u.last_name) as officer_name,
           b.barangay_name, o.specific_location, o.oplan_date, o.oplan_time, o.status,
           o.submitted_at
    FROM oplan_activities o
    JOIN users u ON o.user_id = u.user_id
    JOIN barangays b ON o.barangay_id = b.barangay_id
    ORDER BY o.submitted_at DESC
    LIMIT 5
");

while ($row = $oplans->fetch_assoc()) {
    $recent[] = $row;
}

// Sort recent activities by date (most recent first)
usort($recent, function($a, $b) {
    return strtotime($b['submitted_at']) - strtotime($a['submitted_at']);
});

// Take only top 8
$recent = array_slice($recent, 0, 8);

// Get top performing officers
$top_officers = $conn->query("
    SELECT u.user_id, u.rank, u.first_name, u.last_name,
           (SELECT COUNT(*) FROM patrol_activities WHERE user_id = u.user_id) as patrol_count,
           (SELECT COUNT(*) FROM checkpoint_activities WHERE user_id = u.user_id) as checkpoint_count,
           (SELECT COUNT(*) FROM oplan_activities WHERE user_id = u.user_id) as oplan_count,
           ((SELECT COUNT(*) FROM patrol_activities WHERE user_id = u.user_id) +
            (SELECT COUNT(*) FROM checkpoint_activities WHERE user_id = u.user_id) +
            (SELECT COUNT(*) FROM oplan_activities WHERE user_id = u.user_id)) as total_activities
    FROM users u
    WHERE u.role = 'user'
    ORDER BY total_activities DESC
    LIMIT 3
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../image/pnplogo.png">
    <title>PNP | Admin Dashboard</title>
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
        
        /* PNP Official Colors */
        .pnp-navy { background-color: #0a2b3c; }
        .pnp-navy-light { background-color: #1e4a6a; }
        .pnp-gold { color: #ffc107; }
        .pnp-gold-bg { background-color: #ffc107; }
        .pnp-red { background-color: #c41e3a; }
        .pnp-red-light { background-color: #dc3545; }
        
        /* Gradient Cards */
        .gradient-patrol {
            background: linear-gradient(135deg, #0a2b3c 0%, #1e4a6a 100%);
        }
        .gradient-checkpoint {
            background: linear-gradient(135deg, #c41e3a 0%, #dc3545 100%);
        }
        .gradient-oplan {
            background: linear-gradient(135deg, #0a2b3c 0%, #1e4a6a 100%);
        }
    </style>
</head>
<body class="flex bg-[#0a2b3c]">

    <!-- Sidebar - PNP Navy -->
    <div class="w-[240px] h-screen bg-[#08324f] text-white p-5 sticky top-0 overflow-y-auto">
        <div class="flex items-center gap-3 mb-6 pb-3 border-b border-[#1e4a6a]">
            <img src="../image/pnplogo.png" class="w-8 h-8 object-contain" alt="PNP Logo">
            <h2 class="text-xl font-semibold">PNP Admin</h2>
        </div>

        <!-- Admin Info -->
        <div class="bg-[#1e4a6a] p-3 rounded-lg mb-4 text-center">
            <p class="text-sm text-yellow-400 font-medium"><?php echo $_SESSION['full_name']; ?></p>
            <p class="text-xs text-gray-300 mt-1"><?php echo $_SESSION['email']; ?></p>
        </div>

        <ul class="space-y-1">
            <li class="p-3 rounded bg-[#1e4a6a] border-l-4 border-[#ffc107]">
                <a href="admin_dashboard.php" class="text-white no-underline block">
                    <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                </a>
            </li>

            <li class="p-3 rounded hover:bg-[#1e4a6a] cursor-pointer">
                <a href="checkpoint.php" class="text-white no-underline block">
                    <i class="fas fa-map-marker-alt mr-3"></i> Checkpoint
                </a>
            </li>

            <li class="dropdown">
                <div class="p-3 rounded hover:bg-[#1e4a6a] cursor-pointer flex items-center justify-between" onclick="toggleDropdown(this)">
                    <span><i class="fas fa-walking mr-3"></i> Patrol</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300"></i>
                </div>
                <ul class="pl-8 mt-1 space-y-1 dropdown-content">
                    <li class="py-2 px-3 text-sm hover:bg-[#1e4a6a] rounded"><a href="footpatrol.php" class="text-white no-underline block">Foot Patrol</a></li>
                    <li class="py-2 px-3 text-sm hover:bg-[#1e4a6a] rounded"><a href="mobilepatrol.php" class="text-white no-underline block">Mobile Patrol</a></li>
                    <li class="py-2 px-3 text-sm hover:bg-[#1e4a6a] rounded"><a href="motorpatrol.php" class="text-white no-underline block">Motorcycle Patrol</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <div class="p-3 rounded hover:bg-[#1e4a6a] cursor-pointer flex items-center justify-between" onclick="toggleDropdown(this)">
                    <span><i class="fas fa-shield-alt mr-3"></i> Oplan Bakal / Sita</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300"></i>
                </div>
                <ul class="pl-8 mt-1 space-y-1 dropdown-content">
                    <li class="py-2 px-3 text-sm hover:bg-[#1e4a6a] rounded"><a href="oplanbakal.php" class="text-white no-underline block">Oplan Bakal</a></li>
                    <li class="py-2 px-3 text-sm hover:bg-[#1e4a6a] rounded"><a href="oplansita.php" class="text-white no-underline block">Oplan Sita</a></li>
                </ul>
            </li>

            <li class="p-3 rounded hover:bg-[#1e4a6a] cursor-pointer">
                <a href="admin_users.php" class="text-white no-underline block">
                    <i class="fas fa-users mr-3"></i> Users
                </a>
            </li>
            
            <li class="p-3 rounded hover:bg-[#1e4a6a] cursor-pointer">
                <a href="accomplishment_report.php" class="text-white no-underline block">
                    <i class="fas fa-file-alt mr-3"></i> Accomplishment Report
                </a>
            </li>

            <li class="p-3 rounded hover:bg-[#1e4a6a] cursor-pointer mt-5 pt-4 border-t border-[#1e4a6a]">
                <a href="../logout.php" class="text-white no-underline block">
                    <i class="fas fa-sign-out-alt mr-3"></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content - NO CHARTS, ONLY CARDS -->
    <div class="flex-1 p-8 bg-[#eef2f6] overflow-y-auto h-screen">
        
        <!-- Header with PNP Gold Accent -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6 border-l-4 border-[#ffc107]">
            <h2 class="text-2xl font-bold text-[#0a2b3c]">Dashboard Overview</h2>
            <p class="text-gray-600 mt-1">Welcome back, <?php echo $_SESSION['full_name']; ?>. System monitoring panel.</p>
        </div>

        <!-- QUICK STATS ROW - Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-5 rounded-lg shadow-md border-l-4 border-[#0a2b3c]">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Total Personnel</p>
                        <p class="text-3xl font-bold text-[#0a2b3c] mt-1"><?php echo $stats['total_users']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-[#0a2b3c] bg-opacity-10 rounded-full flex items-center justify-center">
                        <i class="fas fa-users text-[#0a2b3c] text-xl"></i>
                    </div>
                </div>
                <p class="text-xs text-green-600 mt-2"><i class="fas fa-arrow-up mr-1"></i> <?php echo $stats['active_users']; ?> active</p>
            </div>
            
            <div class="bg-white p-5 rounded-lg shadow-md border-l-4 border-[#0a2b3c]">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Active Officers</p>
                        <p class="text-3xl font-bold text-[#0a2b3c] mt-1"><?php echo $stats['active_users']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-[#0a2b3c] bg-opacity-10 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-check text-[#0a2b3c] text-xl"></i>
                    </div>
                </div>
                <p class="text-xs text-green-600 mt-2"><i class="fas fa-chart-line mr-1"></i> <?php echo $stats['active_users'] > 0 ? round(($stats['active_users']/$stats['total_users'])*100) : 0; ?>% participation</p>
            </div>
            
            <div class="bg-white p-5 rounded-lg shadow-md border-l-4 border-[#c41e3a]">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Total Operations</p>
                        <p class="text-3xl font-bold text-[#0a2b3c] mt-1"><?php echo $stats['total_patrols'] + $stats['total_checkpoints'] + $stats['total_oplans']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-[#c41e3a] bg-opacity-10 rounded-full flex items-center justify-center">
                        <i class="fas fa-calendar-check text-[#c41e3a] text-xl"></i>
                    </div>
                </div>
                <p class="text-xs text-green-600 mt-2"><i class="fas fa-chart-line mr-1"></i> All-time total</p>
            </div>
            
            <div class="bg-white p-5 rounded-lg shadow-md border-l-4 border-[#ffc107]">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Success Rate</p>
                        <p class="text-3xl font-bold text-[#0a2b3c] mt-1">96%</p>
                    </div>
                    <div class="w-12 h-12 bg-[#ffc107] bg-opacity-10 rounded-full flex items-center justify-center">
                        <i class="fas fa-trophy text-[#ffc107] text-xl"></i>
                    </div>
                </div>
                <p class="text-xs text-green-600 mt-2"><i class="fas fa-star mr-1"></i> Excellent performance</p>
            </div>
        </div>

        <!-- PATROL STATISTICS CARDS -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-[#0a2b3c] mb-4 flex items-center gap-2">
                <i class="fas fa-walking text-[#ffc107]"></i> Patrol Statistics
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <!-- Foot Patrol Card -->
                <div class="stat-card gradient-patrol p-5 rounded-lg shadow-lg text-white">
                    <div class="flex items-center justify-between mb-3">
                        <i class="fas fa-walking text-3xl opacity-80"></i>
                        <span class="text-xs bg-white/20 px-3 py-1 rounded-full text-[#ffc107]">Total: <?php echo $stats['foot_patrols']; ?></span>
                    </div>
                    <p class="text-3xl font-bold"><?php echo $stats['foot_patrols']; ?></p>
                    <p class="text-sm opacity-90">Total Foot Patrols</p>
                </div>

                <!-- Mobile Patrol Card -->
                <div class="stat-card gradient-patrol p-5 rounded-lg shadow-lg text-white">
                    <div class="flex items-center justify-between mb-3">
                        <i class="fas fa-car text-3xl opacity-80"></i>
                        <span class="text-xs bg-white/20 px-3 py-1 rounded-full text-[#ffc107]">Total: <?php echo $stats['mobile_patrols']; ?></span>
                    </div>
                    <p class="text-3xl font-bold"><?php echo $stats['mobile_patrols']; ?></p>
                    <p class="text-sm opacity-90">Total Mobile Patrols</p>
                </div>

                <!-- Motorcycle Patrol Card -->
                <div class="stat-card gradient-patrol p-5 rounded-lg shadow-lg text-white">
                    <div class="flex items-center justify-between mb-3">
                        <i class="fas fa-motorcycle text-3xl opacity-80"></i>
                        <span class="text-xs bg-white/20 px-3 py-1 rounded-full text-[#ffc107]">Total: <?php echo $stats['motor_patrols']; ?></span>
                    </div>
                    <p class="text-3xl font-bold"><?php echo $stats['motor_patrols']; ?></p>
                    <p class="text-sm opacity-90">Total Motor Patrols</p>
                </div>
            </div>
        </div>

        <!-- CHECKPOINT & OPLAN STATISTICS - Side by Side -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- CHECKPOINT STATISTICS -->
            <div>
                <h3 class="text-lg font-semibold text-[#0a2b3c] mb-4 flex items-center gap-2">
                    <i class="fas fa-map-marker-alt text-[#ffc107]"></i> Checkpoint Statistics
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <!-- Total Checkpoints -->
                    <div class="stat-card gradient-checkpoint p-5 rounded-lg shadow-lg text-white">
                        <i class="fas fa-map-pin text-3xl opacity-80 mb-3 block"></i>
                        <p class="text-3xl font-bold"><?php echo $stats['total_checkpoints']; ?></p>
                        <p class="text-sm opacity-90">Total Checkpoints</p>
                    </div>
                    <!-- Border Control -->
                    <div class="stat-card gradient-checkpoint p-5 rounded-lg shadow-lg text-white">
                        <i class="fas fa-border-all text-3xl opacity-80 mb-3 block"></i>
                        <p class="text-3xl font-bold"><?php echo $stats['border_ops']; ?></p>
                        <p class="text-sm opacity-90">Border Control Ops</p>
                    </div>
                    <!-- Mobile Checkpoint -->
                    <div class="stat-card gradient-checkpoint p-5 rounded-lg shadow-lg text-white">
                        <i class="fas fa-truck text-3xl opacity-80 mb-3 block"></i>
                        <p class="text-3xl font-bold"><?php echo $stats['mobile_checkpoint_ops']; ?></p>
                        <p class="text-sm opacity-90">Mobile Checkpoint</p>
                    </div>
                    <!-- TCT/OVR -->
                    <div class="stat-card gradient-checkpoint p-5 rounded-lg shadow-lg text-white">
                        <i class="fas fa-file-alt text-3xl opacity-80 mb-3 block"></i>
                        <p class="text-3xl font-bold"><?php echo $stats['tct_ovr']; ?></p>
                        <p class="text-sm opacity-90">TCT/OVR Accomps</p>
                    </div>
                </div>
                <!-- Checkpoint Accomplishments Summary -->
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div class="bg-white p-3 rounded-lg shadow-sm border-l-2 border-[#c41e3a]">
                        <p class="text-xs text-gray-500">Checkpoint Arrests</p>
                        <p class="text-xl font-bold text-[#0a2b3c]"><?php echo $stats['checkpoint_arrests']; ?></p>
                    </div>
                </div>
            </div>

            <!-- OPLAN STATISTICS -->
            <div>
                <h3 class="text-lg font-semibold text-[#0a2b3c] mb-4 flex items-center gap-2">
                    <i class="fas fa-shield-alt text-[#ffc107]"></i> Oplan Statistics
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <!-- Total Oplan -->
                    <div class="stat-card gradient-patrol p-5 rounded-lg shadow-lg text-white">
                        <i class="fas fa-shield-alt text-3xl opacity-80 mb-3 block"></i>
                        <p class="text-3xl font-bold"><?php echo $stats['total_oplans']; ?></p>
                        <p class="text-sm opacity-90">Total Operations</p>
                    </div>
                    <!-- Oplan Bakal -->
                    <div class="stat-card gradient-patrol p-5 rounded-lg shadow-lg text-white">
                        <i class="fas fa-gun text-3xl opacity-80 mb-3 block"></i>
                        <p class="text-3xl font-bold"><?php echo $stats['oplan_bakal']; ?></p>
                        <p class="text-sm opacity-90">Oplan Bakal</p>
                    </div>
                    <!-- Oplan Sita -->
                    <div class="stat-card gradient-patrol p-5 rounded-lg shadow-lg text-white">
                        <i class="fas fa-magnifying-glass text-3xl opacity-80 mb-3 block"></i>
                        <p class="text-3xl font-bold"><?php echo $stats['oplan_sita']; ?></p>
                        <p class="text-sm opacity-90">Oplan Sita</p>
                    </div>
                    <!-- Oplan Arrests -->
                    <div class="stat-card gradient-patrol p-5 rounded-lg shadow-lg text-white">
                        <i class="fas fa-users text-3xl opacity-80 mb-3 block"></i>
                        <p class="text-3xl font-bold"><?php echo $stats['oplan_arrests']; ?></p>
                        <p class="text-sm opacity-90">Total Arrests</p>
                    </div>
                </div>
                <!-- Oplan Accomplishments Summary -->
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div class="bg-white p-3 rounded-lg shadow-sm border-l-2 border-[#0a2b3c]">
                        <p class="text-xs text-gray-500">Bakal Firearms</p>
                        <p class="text-xl font-bold text-[#0a2b3c]"><?php echo $stats['firearms']; ?></p>
                    </div>
                    <div class="bg-white p-3 rounded-lg shadow-sm border-l-2 border-[#ffc107]">
                        <p class="text-xs text-gray-500">Sita Contraband</p>
                        <p class="text-xl font-bold text-[#0a2b3c]"><?php echo $stats['contraband']; ?> kg</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- TOP PERFORMERS - From Database -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-[#0a2b3c] mb-4 flex items-center gap-2">
                <i class="fas fa-crown text-[#ffc107]"></i> Top Performing Officers
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <?php while ($officer = $top_officers->fetch_assoc()): ?>
                <div class="bg-white p-4 rounded-lg shadow-md flex items-center gap-3">
                    <div class="w-12 h-12 bg-[#0a2b3c] rounded-full flex items-center justify-center text-[#ffc107] font-bold text-lg">
                        <?php echo substr($officer['first_name'], 0, 1) . substr($officer['last_name'], 0, 1); ?>
                    </div>
                    <div>
                        <p class="font-semibold text-[#0a2b3c]"><?php echo $officer['rank'] . ' ' . $officer['first_name'] . ' ' . $officer['last_name']; ?></p>
                        <p class="text-xs text-gray-500"><?php echo $officer['total_activities']; ?> total activities</p>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- RECENT ACTIVITY MINI CARDS - From Database -->
        <div>
            <h3 class="text-lg font-semibold text-[#0a2b3c] mb-4 flex items-center gap-2">
                <i class="fas fa-clock text-[#ffc107]"></i> Recent Activities
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <?php foreach ($recent as $activity): ?>
                <div class="bg-white p-3 rounded-lg shadow-sm border-l-4 
                    <?php 
                    echo $activity['type'] == 'patrol' ? 'border-blue-500' : 
                        ($activity['type'] == 'checkpoint' ? 'border-red-500' : 'border-green-500'); 
                    ?>">
                    <p class="text-xs text-gray-500"><?php echo date('M d, Y h:i A', strtotime($activity['submitted_at'])); ?></p>
                    <p class="font-medium">
                        <?php 
                        if ($activity['type'] == 'patrol') echo $activity['subtype'];
                        elseif ($activity['type'] == 'checkpoint') echo 'Checkpoint';
                        else echo $activity['subtype'];
                        ?> - <?php echo $activity['barangay_name']; ?>
                    </p>
                    <p class="text-xs text-gray-600"><?php echo $activity['officer_name']; ?></p>
                    <p class="text-xs mt-1">
                        <span class="
                        <?php 
                        echo $activity['status'] == 'approved' ? 'text-green-600' : 
                            ($activity['status'] == 'pending' ? 'text-yellow-600' : 'text-red-600'); 
                        ?>">
                        <?php echo ucfirst($activity['status']); ?>
                        </span>
                    </p>
                </div>
                <?php endforeach; ?>
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