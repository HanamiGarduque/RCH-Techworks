<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCH Water | Rider Login</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="bg-white min-h-screen flex justify-center items-center relative">

    <!-- Fixed-width container wrapper -->
    <div class="bg-blue-500 w-[375px] h-[750px] mx-auto relative rounded-3xl px-4 overflow-hidden border shadow-lg">

        <!-- Decorative circles -->
        <div class="absolute w-[250px] h-[250px] bg-white/20 rounded-full top-20 left-4 z-10"></div>
        <div class="absolute w-[180px] h-[180px] bg-white/20 rounded-full bottom-20 right-4 z-10"></div>

        <div class="mt-20 flex flex-col justify-center items-center bg-blue-500 mt-10 px-4">
            <!-- Header -->
            <div class="text-center text-white mb-6">
                <div class="items-center justify-center mb-2 p-3 text-5xl">
                    <i class="fas fa-tint text-white"></i>
                </div>

                <h1 class="text-2xl font-semibold flex items-center justify-center gap-2 mb-3">
                    RCH Water
                </h1>

                <p class="text-sm mt-2">Join us today and enjoy fresh, clean water delivered right at your doorstep.</p>
            </div>
            <!-- Login Box -->
            <div class="relative z-10 bg-white w-full max-w-[375px] rounded-[25px] shadow-xl p-7 text-center">

                <h2 class="text-xl font-semibold mb-5 text-black">Sign in as Rider</h2>

                <form id="loginForm" class="flex flex-col items-center gap-3">

                    <!-- Username Field with Icon -->
                    <div class="flex items-center w-5/6 border border-gray-300 rounded-lg px-3 py-2 bg-gray-50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.121 17.804A13.937 13.937 0 0112 15c2.45 0 4.747.586 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <input
                            type="text"
                            name="username"
                            placeholder="Username or Email"
                            required
                            class="w-full bg-transparent text-sm focus:outline-none">
                    </div>

                    <!-- Password Field with Icon -->
                    <div class="flex items-center w-5/6 border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15c.828 0 1.5-.672 1.5-1.5V11A1.5 1.5 0 0012 9.5 1.5 1.5 0 0010.5 11v2.5c0 .828.672 1.5 1.5 1.5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.25 11V8.25A5.25 5.25 0 006.75 8.25V11M6 19h12a2 2 0 002-2v-4a2 2 0 00-2-2H6a2 2 0 00-2 2v4a2 2 0 002 2z" />
                        </svg>
                        <input
                            type="password"
                            name="password"
                            placeholder="Password"
                            required
                            id="password"
                            class="w-full bg-transparent text-sm focus:outline-none pr-8">

                        <!-- Eye icon button -->
                        <button type="button" class="absolute right-3 text-gray-400" onclick="togglePassword()">
                            <i id="eyeIcon" class="fas fa-eye"></i>
                        </button>
                    </div>

                    <!-- Login Button -->
                    <button
                        type="submit"
                        class="bg-blue-600 w-5/6 py-2.5 rounded-full text-white font-medium text-base mt-2 shadow-md hover:bg-blue-700 transition">
                        Login
                    </button>
                </form>

                <!-- Error -->
                <div id="errorMsg" class="text-red-600 text-sm mt-3 min-h-[18px]"></div>


                <!-- Footer -->
                <div class="text-blue-500 text-xs text-center mt-5">
                    © RCH Water Refilling System
                </div>
            </div>
        </div>

        <script>
            function togglePassword() {
                const passwordInput = document.getElementById('password');
                const eyeIcon = document.getElementById('eyeIcon');
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    eyeIcon.classList.remove('fa-eye');
                    eyeIcon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    eyeIcon.classList.remove('fa-eye-slash');
                    eyeIcon.classList.add('fa-eye');
                }
            }
            const form = document.getElementById('loginForm');
            const errorDiv = document.getElementById('errorMsg');

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);

                fetch('login_backend.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = data.redirect; // use backend redirect
                        } else {
                            errorDiv.textContent = data.message;
                            setTimeout(() => {
                                errorDiv.textContent = '';
                            }, 3000);
                        }
                    })
                    .catch(err => console.error(err));
            });
        </script>

</body>

</html>