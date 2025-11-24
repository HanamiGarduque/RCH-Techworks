<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>RCH Water</title>
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- BOOTSTRAP ICONS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    html { scroll-behavior: smooth; }
    /* navbar height set to 4rem (64px) */
    :root{ --nav-h: 4rem; }
    body { font-family: 'Poppins', sans-serif; padding-top: var(--nav-h); }
    nav { height: var(--nav-h); }
    /* ensure anchor targets are not hidden under fixed nav */
    section { scroll-margin-top: calc(var(--nav-h) + 0.5rem); }
  </style>
</head>

<body class="bg-white text-gray-700">

  <!-- NAVIGATION -->
  <nav class="fixed top-0 left-0 right-0 z-50 border-b bg-white">
    <div class="max-w-6xl mx-auto px-6 h-16 flex items-center">
      <!-- left: logo -->
      <div class="flex items-center gap-3">
        <i class="bi bi-droplet-fill text-blue-600 text-2xl"></i>
        <span class="font-semibold text-lg text-gray-800">RCH Water</span>
      </div>

      <!-- center: nav links (centered, spacing from logo) -->
      <div class="flex-1 flex justify-center">
        <ul class="hidden md:flex gap-10 text-sm font-medium text-blue-600">
          <li><a href="#home" class="hover:underline">Home</a></li>
          <li><a href="#about" class="hover:underline">About</a></li>
          <li><a href="#contact" class="hover:underline">Contact</a></li>
          <li><a href="developers.html" class="hover:underline">Developer</a></li>
        </ul>
      </div>

      <!-- right: actions -->
      <div class="flex items-center gap-3">
        <a href="login.html" class="inline-block px-4 py-1 rounded-full border border-blue-400 text-blue-600 hover:bg-blue-50 transition">Login</a>
        <button class="px-4 py-1 rounded-full bg-blue-500 text-white hover:bg-blue-600 transition">Sign Up</button>
      </div>
    </div>
  </nav>

  <!-- HERO SECTION -->
  <section id="home" class="min-h-screen max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 items-center gap-12 px-6 py-20">
    <div class="px-6 md:px-0">
      <h1 class="text-5xl md:text-6xl lg:text-7xl font-extrabold text-blue-500 leading-tight italic tracking-tight"> 
        Fresh Water,
        <br>
        Delivered Daily
      </h1>

      <p class="mt-6 text-gray-600 text-base md:text-lg max-w-xl">
        We make it easy to stay hydrated with pure water delivered whenever you need it.
      </p>

      <a href="login.html" class="inline-block mt-10 px-5 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition text-sm">ORDER NOW</a>
    </div>

    <div class="flex justify-center md:justify-end">
      <img src="img/home.jpg" alt="Water Image" class="rounded-lg w-80 md:w-[420px] shadow-lg border border-white">
    </div>
  </section>

  <!-- ABOUT SECTION -->
  <section id="about" class="min-h-screen w-full bg-blue-500 text-white py-20">
    <div class="max-w-4xl mx-auto text-center px-6">
      <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold uppercase mb-8">ABOUT RCH</h2>

      <p class="mx-auto leading-relaxed text-sm md:text-base">
        Welcome to our Water Delivery System — a service designed to make clean, safe, and refreshing drinking water accessible to everyone, every day. We understand the importance of reliable hydration, which is why our system ensures that fresh water reaches your home, office, or business exactly when you need it.
        <br><br>
        Our delivery process is built on three core principles: quality, convenience, and consistency. Every drop of water we provide goes through a thorough purification process, ensuring it meets the highest standards for safety and taste.
        <br><br>
        Whether you're a household, a workplace, or a business with regular water needs, our Water Delivery System is here to support you with reliable service and clean drinking water you can trust.
      </p>

      <p class="mt-8 font-semibold">Pure water. Simple process. Everyday delivery.</p>
    </div>
  </section>

  <!-- CONTACT SECTION -->
  <section id="contact" class="min-h-screen max-w-6xl mx-auto px-6 py-20">
    <h2 class="text-3xl md:text-4xl font-bold text-blue-500 text-center mb-12">CONTACT US</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
      <!-- Contact Info Column -->
      <div class="space-y-6">
        <div class="flex items-start gap-4 bg-blue-500 text-white rounded-xl p-5 shadow">
          <div class="flex-shrink-0 bg-blue-600 p-3 rounded-full">
            <i class="bi bi-clock text-white text-lg"></i>
          </div>
          <div>
            <h4 class="font-semibold">Working Hours</h4>
            <p class="text-sm">8:00 AM - 6:00 PM<br>Monday - Sunday</p>
          </div>
        </div>

        <div class="flex items-start gap-4 bg-blue-500 text-white rounded-xl p-5 shadow">
          <div class="flex-shrink-0 bg-blue-600 p-3 rounded-full">
            <i class="bi bi-geo-alt-fill text-white text-lg"></i>
          </div>
          <div>
            <p class="text-sm">123 Pag-asa Street, Barangay Uno, Lipa City, Batangas</p>
          </div>
        </div>

        <div class="flex items-start gap-4 bg-blue-500 text-white rounded-xl p-5 shadow">
          <div class="flex-shrink-0 bg-blue-600 p-3 rounded-full">
            <i class="bi bi-envelope-fill text-white text-lg"></i>
          </div>
          <div>
            <p class="text-sm">rch@gmail.com</p>
          </div>
        </div>
      </div>

      <!-- Contact Form occupies 2 columns on wider screens -->
      <div class="md:col-span-2">
        <form class="p-6 md:p-8 border rounded-xl shadow bg-white">
          <h3 class="text-xl font-semibold text-blue-500 mb-4">Send us your concern.</h3>

          <label class="text-sm">Name: <span class="text-red-500">*</span></label>
          <input class="w-full border border-gray-300 p-2 rounded mb-4" type="text" required>

          <label class="text-sm">Address: <span class="text-red-500">*</span></label>
          <input class="w-full border border-gray-300 p-2 rounded mb-4" type="text" required>

          <label class="text-sm">Email: <span class="text-red-500">*</span></label>
          <input class="w-full border border-gray-300 p-2 rounded mb-4" type="email" required>

          <label class="text-sm">Message: <span class="text-red-500">*</span></label>
          <textarea class="w-full border border-gray-300 p-2 rounded h-32 mb-4" required></textarea>

          <button class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">Submit</button>
        </form>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer id="developer" class="w-full bg-blue-500 text-white mt-12">
    <div class="max-w-6xl mx-auto px-6 py-8 flex flex-col md:flex-row items-center justify-between gap-6">
      <div class="flex items-center gap-3">
        <i class="bi bi-droplet-fill text-white text-2xl"></i>
        <div>
          <div class="font-semibold">RCH Water</div>
          <div class="text-xs opacity-80">© RCH 2025. All rights reserved.</div>
        </div>
      </div>

      <div class="hidden md:flex gap-6 text-sm">
        <a href="#home">Home</a>
        <a href="#about">About</a>
        <a href="#contact">Contact</a>
        <a href="developers.html">Developer</a>
      </div>

      <div class="flex items-center gap-4">
        <a class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center" href="#"><i class="bi bi-facebook"></i></a>
        <a class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center" href="#"><i class="bi bi-instagram"></i></a>
        <a class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center" href="#"><i class="bi bi-github"></i></a>
      </div>
    </div>
  </footer>

</body>
</html>