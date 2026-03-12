<?php
session_start();
// If already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/admin_dashboard.php');
    } else {
        header('Location: user/user_dashboard.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="image/pnplogo.png">
    <title>PNP | Login</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .backdrop-blur-custom { backdrop-filter: blur(5px); }
        body { background-image: url("image/pnpBGlogo.jpg"); background-position: center; background-repeat: no-repeat; background-size: cover; }
    </style>
</head>
<body class="h-screen flex items-center justify-center bg-[#0a3d62] relative">

    <div class="absolute inset-0 bg-black/20"></div>

    <div class="relative w-[420px] p-10 bg-white/35 backdrop-blur-custom rounded-2xl text-center shadow-xl">
        <img src="image/pnplogo.png" class="w-20 mx-auto mb-3" alt="PNP Logo">
        <h2 class="text-3xl font-serif font-bold mb-5 text-gray-800">LOGIN</h2>

        <!-- Display error message if any -->
        <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm">
            <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']);
            ?>
        </div>
        <?php endif; ?>

        <!-- Display success message if any (like registration success) -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">
            <?php 
                echo $_SESSION['success']; 
                unset($_SESSION['success']);
            ?>
        </div>
        <?php endif; ?>

        <!-- FIXED: Form now submits to login_process.php instead of directly to dashboard -->
        <form action="includes/login_process.php" class="flex flex-col" method="POST">
            <label class="text-left text-sm font-serif mt-3 text-gray-700">Email</label>
            <input type="email" name="email" required class="p-2.5 mt-1 border-2 border-black rounded focus:outline-none focus:border-[#1f6fb2] transition">

            <label class="text-left text-sm font-serif mt-4 text-gray-700">Password</label>
            <input type="password" name="password" required class="p-2.5 mt-1 border-2 border-black rounded focus:outline-none focus:border-[#1f6fb2] transition">

            <button type="submit" class="mt-6 py-3 bg-[#1f6fb2] text-white font-serif text-base rounded-full hover:bg-[#0a3d62] transition duration-300 shadow-md">
                LOGIN
            </button>

        <!--    <a href="register.php" class="mt-3 text-black font-serif text-sm hover:underline">
                Register & Create Account  
            </a>  -->
        </form>
    </div>
</body>
</html>