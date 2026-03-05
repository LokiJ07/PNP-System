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
    </style>
</head>
<body class="h-screen flex items-center justify-center bg-[#0a3d62] relative">

    <div class="absolute inset-0 bg-black/20"></div>

    <div class="relative w-[420px] p-10 bg-white/35 backdrop-blur-custom rounded-2xl text-center shadow-xl max-h-[90vh] overflow-y-auto">
        <img src="image/pnplogo.png" class="w-20 mx-auto mb-3" alt="PNP Logo">
        <h2 class="text-3xl font-serif font-bold mb-5 text-gray-800">REGISTER</h2>

        <form action="admin/admin_dashboard.php" class="flex flex-col" method="POST">
            <label class="text-left text-sm font-serif mt-2 text-gray-700">Email</label>
            <input type="email" name="email" required class="p-2.5 mt-1 border-2 border-black rounded focus:outline-none focus:border-[#1f6fb2] transition">

            <label class="text-left text-sm font-serif mt-3 text-gray-700">Rank</label>
            <select name="rank" required class="p-2.5 mt-1 border-2 border-black rounded focus:outline-none focus:border-[#1f6fb2] transition bg-white">
                <option value="" disabled selected>Select Rank</option>
                <option value="PO1">PO1</option>
                <option value="PO2">PO2</option>
                <option value="PO3">PO3</option>
                <option value="SPO1">SPO1</option>
                <option value="SPO2">SPO2</option>
                <option value="SPO3">SPO3</option>
                <option value="SPO4">SPO4</option>
            </select>

            <label class="text-left text-sm font-serif mt-3 text-gray-700">Firstname</label>
            <input type="text" name="firstname" required class="p-2.5 mt-1 border-2 border-black rounded focus:outline-none focus:border-[#1f6fb2] transition">

            <label class="text-left text-sm font-serif mt-3 text-gray-700">Lastname</label>
            <input type="text" name="lastname" required class="p-2.5 mt-1 border-2 border-black rounded focus:outline-none focus:border-[#1f6fb2] transition">

            <label class="text-left text-sm font-serif mt-3 text-gray-700">Password</label>
            <input type="password" name="password" required class="p-2.5 mt-1 border-2 border-black rounded focus:outline-none focus:border-[#1f6fb2] transition">

            <label class="text-left text-sm font-serif mt-3 text-gray-700">Confirm Password</label>
            <input type="password" name="confirm_password" required class="p-2.5 mt-1 border-2 border-black rounded focus:outline-none focus:border-[#1f6fb2] transition">

            <button type="submit" class="mt-6 py-3 bg-[#1f6fb2] text-white font-serif text-base rounded-full hover:bg-[#0a3d62] transition duration-300 shadow-md">
                REGISTER
            </button>

            <a href="index.php" class="mt-3 text-black font-serif text-sm hover:underline">
                Already Have an Account
            </a>
        </form>
    </div>
</body>
</html>