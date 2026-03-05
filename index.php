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
        /* Custom blur and background styles that Tailwind doesn't have by default */
        .backdrop-blur-custom {
            backdrop-filter: blur(5px);
        }
        body {
            background-image: url("image/pnpBGlogo.jpg");
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }
    </style>
</head>
<body class="h-screen flex items-center justify-center bg-[#0a3d62] relative">

    <!-- Background Overlay -->
    <div class="absolute inset-0 bg-black/20"></div>

    <!-- Login Container -->
    <div class="relative w-[420px] p-10 bg-white/35 backdrop-blur-custom rounded-2xl text-center shadow-xl">
        
        <!-- Logo -->
        <img src="image/pnplogo.png" class="w-20 mx-auto mb-3" alt="PNP Logo">

        <!-- Title -->
        <h2 class="text-3xl font-serif font-bold mb-5 text-gray-800">LOGIN</h2>

        <!-- Login Form -->
        <form action="admin/admin_dashboard.php" class="flex flex-col" method="POST">
            
            <!-- Email Field -->
            <label class="text-left text-sm font-serif mt-3 text-gray-700">Email</label>
            <input type="email" name="email" required 
                   class="p-2.5 mt-1 border-2 border-black rounded focus:outline-none focus:border-[#1f6fb2] transition">

            <!-- Password Field -->
            <label class="text-left text-sm font-serif mt-4 text-gray-700">Password</label>
            <input type="password" name="password" required 
                   class="p-2.5 mt-1 border-2 border-black rounded focus:outline-none focus:border-[#1f6fb2] transition">

            <!-- Login Button -->
            <button type="submit" 
                    class="mt-6 py-3 bg-[#1f6fb2] text-white font-serif text-base rounded-full hover:bg-[#0a3d62] transition duration-300 shadow-md">
                LOGIN
            </button>

            <!-- Register Link -->
            <a href="register.php" class="mt-3 text-black font-serif text-sm hover:underline">
                Register & Create Account
            </a>

        </form>

    </div>

</body>
</html>