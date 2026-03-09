<?php
session_start();
// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/admin_dashboard.php');
    } else {
        header('Location: user/user_dashboard.php');
    }
    exit();
}

// Preserve form data from previous submission
$old_email = $_SESSION['old_email'] ?? '';
$old_firstname = $_SESSION['old_firstname'] ?? '';
$old_lastname = $_SESSION['old_lastname'] ?? '';
$old_rank = $_SESSION['old_rank'] ?? '';

// Clear old data
unset($_SESSION['old_email'], $_SESSION['old_firstname'], $_SESSION['old_lastname'], $_SESSION['old_rank']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="image/pnplogo.png">
    <title>PNP | Register</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .backdrop-blur-custom { backdrop-filter: blur(10px); }
        body { background-image: url("image/pnpBGlogo.jpg"); background-position: center; background-repeat: no-repeat; background-size: cover; }
        .error-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .error-list li {
            font-size: 0.875rem;
            padding: 0.25rem 0;
        }
    </style>
</head>
<body class="h-screen flex items-center justify-center bg-[#0a3d62] relative">

    <div class="absolute inset-0 bg-black/20"></div>

    <div class="relative w-[420px] p-10 bg-white/35 backdrop-blur-custom rounded-2xl text-center shadow-xl max-h-[90vh] overflow-y-auto">
        <img src="image/pnplogo.png" class="w-20 mx-auto mb-3" alt="PNP Logo">
        <h2 class="text-3xl font-serif font-bold mb-5 text-gray-800">REGISTER</h2>

        <!-- Display error messages if any -->
        <?php if (isset($_SESSION['errors']) && is_array($_SESSION['errors'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm text-left">
            <ul class="error-list">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <li><i class="fas fa-exclamation-circle mr-2 text-red-500"></i><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
            <?php unset($_SESSION['errors']); ?>
        </div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form action="includes/register_process.php" class="flex flex-col" method="POST">
            <label class="text-left text-sm font-serif mt-2 text-gray-700">Email</label>
            <input type="email" name="email" required value="<?php echo htmlspecialchars($old_email); ?>" 
                   class="p-2.5 mt-1 border-2 border-black rounded focus:outline-none focus:border-[#1f6fb2] transition"
                   placeholder="your.email@pnp.gov.ph">

            <label class="text-left text-sm font-serif mt-3 text-gray-700">Rank</label>
            <select name="rank" required class="p-2.5 mt-1 border-2 border-black rounded focus:outline-none focus:border-[#1f6fb2] transition bg-white">
                <option value="" disabled <?php echo empty($old_rank) ? 'selected' : ''; ?>>Select Rank</option>
                <option value="PO1" <?php echo $old_rank == 'PO1' ? 'selected' : ''; ?>>PO1</option>
                <option value="PO2" <?php echo $old_rank == 'PO2' ? 'selected' : ''; ?>>PO2</option>
                <option value="PO3" <?php echo $old_rank == 'PO3' ? 'selected' : ''; ?>>PO3</option>
                <option value="SPO1" <?php echo $old_rank == 'SPO1' ? 'selected' : ''; ?>>SPO1</option>
                <option value="SPO2" <?php echo $old_rank == 'SPO2' ? 'selected' : ''; ?>>SPO2</option>
                <option value="SPO3" <?php echo $old_rank == 'SPO3' ? 'selected' : ''; ?>>SPO3</option>
                <option value="SPO4" <?php echo $old_rank == 'SPO4' ? 'selected' : ''; ?>>SPO4</option>
            </select>

            <label class="text-left text-sm font-serif mt-3 text-gray-700">Firstname</label>
            <input type="text" name="firstname" required value="<?php echo htmlspecialchars($old_firstname); ?>" 
                   class="p-2.5 mt-1 border-2 border-black rounded focus:outline-none focus:border-[#1f6fb2] transition"
                   placeholder="Juan">

            <label class="text-left text-sm font-serif mt-3 text-gray-700">Lastname</label>
            <input type="text" name="lastname" required value="<?php echo htmlspecialchars($old_lastname); ?>" 
                   class="p-2.5 mt-1 border-2 border-black rounded focus:outline-none focus:border-[#1f6fb2] transition"
                   placeholder="Dela Cruz">

            <label class="text-left text-sm font-serif mt-3 text-gray-700">Password</label>
            <input type="password" name="password" required 
                   class="p-2.5 mt-1 border-2 border-black rounded focus:outline-none focus:border-[#1f6fb2] transition"
                   placeholder="Minimum 6 characters">

            <label class="text-left text-sm font-serif mt-3 text-gray-700">Confirm Password</label>
            <input type="password" name="confirm_password" required 
                   class="p-2.5 mt-1 border-2 border-black rounded focus:outline-none focus:border-[#1f6fb2] transition"
                   placeholder="Re-enter password">

            <button type="submit" class="mt-6 py-3 bg-[#1f6fb2] text-white font-serif text-base rounded-full hover:bg-[#0a3d62] transition duration-300 shadow-md">
                <i class="fas fa-user-plus mr-2"></i> REGISTER
            </button>

            <div class="mt-4 text-center">
                <a href="index.php" class="text-black font-serif text-sm hover:underline">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Login
                </a>
            </div>
        </form>

        <!-- Password requirements hint -->
        <div class="mt-4 text-xs text-gray-600 text-left border-t pt-3">
            <p><i class="fas fa-info-circle mr-1"></i> Password must be at least 6 characters long.</p>
            <p><i class="fas fa-id-card mr-1"></i> You will receive a unique badge number upon registration.</p>
        </div>
    </div>
</body>
</html>
<?php
// Clear any remaining session data
unset($_SESSION['old_email'], $_SESSION['old_firstname'], $_SESSION['old_lastname'], $_SESSION['old_rank']);
?>