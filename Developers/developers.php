<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Our Developers - RCH Water</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <style>
    * { font-family: 'Poppins', sans-serif; }
    html { scroll-behavior: smooth; }
    :root { --nav-h: 4rem; }
    body { padding-top: var(--nav-h); }
    nav { height: var(--nav-h); }
    section { scroll-margin-top: calc(var(--nav-h) + 0.5rem); }
    
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    @keyframes slideInLeft {
      from { transform: translateX(-50px); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }

    @keyframes slideInRight {
      from { transform: translateX(50px); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes pulse-glow {
      0%, 100% { box-shadow: 0 0 20px rgba(37, 99, 235, 0.3); }
      50% { box-shadow: 0 0 40px rgba(37, 99, 235, 0.5); }
    }
    
    .animate-fadeInUp {
      animation: fadeInUp 0.8s ease-out;
    }
    
    .animate-slideInLeft {
      animation: slideInLeft 0.6s ease-out;
    }

    .animate-slideInRight {
      animation: slideInRight 0.6s ease-out;
    }
    
    .animate-pulse-glow {
      animation: pulse-glow 3s ease-in-out infinite;
    }
    
    .team-card {
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }
    
    .team-card:hover {
      transform: translateY(-15px);
      box-shadow: 0 25px 50px rgba(37, 99, 235, 0.2);
    }

    .team-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(6, 182, 212, 0.1));
      opacity: 0;
      transition: opacity 0.3s ease;
      z-index: 1;
    }

    .team-card:hover::before {
      opacity: 1;
    }

    .team-card-content {
      position: relative;
      z-index: 2;
    }
    
    .skill-badge {
      display: inline-block;
      padding: 0.5rem 1rem;
      background: linear-gradient(135deg, #2563eb, #1d4ed8);
      color: white;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
      margin: 0.25rem;
      transition: all 0.3s ease;
    }

    .skill-badge:hover {
      transform: scale(1.05);
      box-shadow: 0 5px 15px rgba(37, 99, 235, 0.3);
    }

    .icon-circle {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
      margin: 0 auto 1.5rem;
      background: linear-gradient(135deg, #2563eb, #1d4ed8);
      color: white;
      box-shadow: 0 10px 25px rgba(37, 99, 235, 0.2);
    }

    .stat-box {
      background: linear-gradient(135deg, #2563eb 0%, #06b6d4 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      font-weight: 700;
      font-size: 2.5rem;
    }

    .hero-gradient {
      background: linear-gradient(135deg, #2563eb 0%, #06b6d4 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .btn-primary {
      background: linear-gradient(135deg, #2563eb, #1d4ed8);
      transition: all 0.3s ease;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
    }

    .role-badge {
      display: inline-block;
      padding: 0.4rem 0.8rem;
      background: #dbeafe;
      color: #1e40af;
      border-radius: 6px;
      font-size: 0.75rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
  </style>
</head>

<body class="bg-white text-gray-800">

  <!-- NAVBAR -->
  <nav class="fixed top-0 left-0 right-0 z-50 border-b border-gray-100 bg-white backdrop-blur-md">
    <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
      <div class="flex items-center gap-3 group cursor-pointer">
        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center">
          <i class="bi bi-droplet text-white text-xl"></i>
        </div>
        <span class="font-bold text-lg text-gray-900 group-hover:text-blue-600 transition">RCH Water</span>
      </div>

      <!-- Added new navigation links to navbar -->
      <ul class="hidden md:flex gap-10 text-sm font-medium text-gray-700">
        <li><a href="/" class="hover:text-blue-600 transition flex items-center gap-2"><i class="bi bi-house"></i> Home</a></li>
        <li><a href="#developers" class="hover:text-blue-600 transition flex items-center gap-2"><i class="bi bi-people"></i> Developers</a></li>
        <li><a href="#tech-stack" class="hover:text-blue-600 transition flex items-center gap-2"><i class="bi bi-code-slash"></i> Tech Stack</a></li>
      </ul>
    </div>
  </nav>

  <!-- HERO SECTION -->
  <section class="min-h-screen pt-20 flex items-center relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-blue-50 via-white to-cyan-50"></div>
    <div class="absolute top-20 left-10 w-72 h-72 bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse-glow"></div>
    <div class="absolute bottom-0 right-10 w-72 h-72 bg-cyan-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20" style="animation: pulse-glow 3s ease-in-out infinite 1s;"></div>
    
    <div class="max-w-6xl mx-auto px-6 py-20 relative z-10 w-full">
      <div class="grid md:grid-cols-2 gap-12 items-center">
        <div class="animate-slideInLeft">
          <div class="inline-flex items-center gap-2 bg-blue-100 text-blue-700 px-4 py-2 rounded-full mb-6 font-medium text-sm">
            <i class="bi bi-code-square"></i> Talented Team Behind the Platform
          </div>
          <h1 class="text-5xl md:text-6xl font-extrabold text-gray-900 leading-tight mb-6">
            Meet Our<br><span class="hero-gradient">Development Team</span>
          </h1>
          <p class="text-xl text-gray-600 mb-8 leading-relaxed max-w-lg">
          A team of dedicated developers committed to creating innovative solutions for clean and accessible water. We work tirelessly to shape the future of water delivery—one smart system and one line of code at a time.
          </p>
        </div>
        
        <div class="animate-slideInRight hidden md:flex justify-center">
          <div class="relative">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-400 to-cyan-400 rounded-3xl transform -rotate-6 opacity-30"></div>
            <img src="img/home3.jpg" alt="Development team" class="relative w-full h-[500px] rounded-3xl shadow-2xl object-cover">
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- DEVELOPERS SECTION -->
  <section id="developers" class="py-24 bg-gradient-to-b from-white to-gray-50">
    <div class="max-w-6xl mx-auto px-6">
      <div class="text-center mb-16">
        <!-- Changed heading from "Meet the Team" to "Our Developers" -->
        <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">The Developers</h2>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto">Brilliant minds working together to innovate water delivery solutions.</p>
      </div>

      <!-- Changed grid to lg:grid-cols-3 for 3 people only -->
      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- Developer 1 -->
        <div class="team-card bg-white rounded-2xl border border-gray-100 p-8">
          <div class="team-card-content">
            <!-- Added img tag with src for team member profile -->
            <img src="img/nami.jpg" alt="Hanami Garduque - Lead Backend Developer" class="w-24 h-24 rounded-full mx-auto mb-4 object-cover border-4 border-blue-100 shadow-md">
            <h3 class="text-2xl font-bold text-gray-900 text-center mb-2">Hanami Garduque</h3>
            <div class="text-center mb-4">
              <span class="role-badge">Project Manager</span>
            </div>
            <div class="text-center mb-4">
              <span class="role-badge">Lead Backend Developer</span>
            </div>
            <div class="text-center mb-4">
              <span class="role-badge">Frontend Developer</span>
            </div>
            <div class="text-center mb-4">
              <span class="role-badge">UI/UX Designer</span>
            </div>
          </div>
        </div>

        <!-- Developer 2 -->
        <div class="team-card bg-white rounded-2xl border border-gray-100 p-8">
          <div class="team-card-content">
            <!-- Added img tag with src for team member profile -->
            <img src="img/tintin.jpg" alt="Christine May Padua - Frontend Lead" class="w-24 h-24 rounded-full mx-auto mb-4 object-cover border-4 border-blue-100 shadow-md">
            <h3 class="text-2xl font-bold text-gray-900 text-center mb-2">Christine May Padua</h3>
            <div class="text-center mb-4">
              <span class="role-badge">Frontend Developer</span>
            </div>
            <div class="text-center mb-4">
              <span class="role-badge">Backend Developer</span>
            </div>
            <div class="text-center mb-4">
              <span class="role-badge">Document Specialist</span>
            </div>
            <div class="text-center mb-4">
              <span class="role-badge">UI/UX Designer</span>
            </div>
          </div>
        </div>

        <!-- Developer 3 -->
        <div class="team-card bg-white rounded-2xl border border-gray-100 p-8">
          <div class="team-card-content">
            <!-- Added img tag with src for team member profile -->
            <img src="img/ramon.jpg" alt="Ramon Fontelera - DevOps Engineer" class="w-24 h-24 rounded-full mx-auto mb-4 object-cover border-4 border-blue-100 shadow-md">
            <h3 class="text-2xl font-bold text-gray-900 text-center mb-2">Ramon Fontelera</h3>
            <div class="text-center mb-4">
              <span class="role-badge">Frontend Developer</span>
            </div>
            <div class="text-center mb-4">
              <span class="role-badge">Backend Developer</span>
            </div>
            <div class="text-center mb-4">
              <span class="role-badge">Document Specialist</span>
            </div>
            <div class="text-center mb-4">
              <span class="role-badge">UI/UX Designer</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- TECH STACK SECTION -->
  <section id="tech-stack" class="py-24 bg-gradient-to-b from-white to-gray-50">
    <div class="max-w-6xl mx-auto px-6">
      <div class="text-center mb-16">
        <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">Tech Stack</h2>
        <p class="text-xl text-gray-600">The tools and technologies powering RCH Water</p>
      </div>

      <div class="grid md:grid-cols-3 gap-8">
        <!-- Frontend Stack -->
        <div class="bg-white rounded-2xl border border-gray-100 p-8 hover:shadow-lg transition">
          <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center mb-4">
            <i class="bi bi-palette text-white text-2xl"></i>
          </div>
          <h3 class="text-2xl font-bold text-gray-900 mb-4">Frontend</h3>
          <ul class="space-y-3">
            <li class="flex items-start gap-3">
              <i class="bi bi-check-circle-fill text-blue-600 mt-1"></i>
              <div>
                <p class="font-semibold text-gray-900">HTML</p>
                <p class="text-sm text-gray-600">Semantic markup</p>
              </div>
            </li>
            <li class="flex items-start gap-3">
              <i class="bi bi-check-circle-fill text-blue-600 mt-1"></i>
              <div>
                <p class="font-semibold text-gray-900">Tailwind CSS</p>
                <p class="text-sm text-gray-600">Utility-first styling</p>
              </div>
            </li>
            <li class="flex items-start gap-3">
              <i class="bi bi-check-circle-fill text-blue-600 mt-1"></i>
              <div>
                <p class="font-semibold text-gray-900">JavaScript</p>
                <p class="text-sm text-gray-600">Interactive functionality</p>
              </div>
            </li>
          </ul>
        </div>

        <!-- Backend Stack -->
        <div class="bg-white rounded-2xl border border-gray-100 p-8 hover:shadow-lg transition">
          <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center mb-4">
            <i class="bi bi-gear text-white text-2xl"></i>
          </div>
          <h3 class="text-2xl font-bold text-gray-900 mb-4">Backend</h3>
          <ul class="space-y-3">
            <li class="flex items-start gap-3">
              <i class="bi bi-check-circle-fill text-blue-600 mt-1"></i>
              <div>
                <p class="font-semibold text-gray-900">PHP</p>
                <p class="text-sm text-gray-600">Server-side logic</p>
              </div>
            </li>
            <li class="flex items-start gap-3">
              <i class="bi bi-check-circle-fill text-blue-600 mt-1"></i>
              <div>
                <p class="font-semibold text-gray-900">JavaScript</p>
                <p class="text-sm text-gray-600">Node.js runtime</p>
              </div>
            </li>
          </ul>
        </div>

        <!-- Database Stack -->
        <div class="bg-white rounded-2xl border border-gray-100 p-8 hover:shadow-lg transition">
          <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center mb-4">
            <i class="bi bi-database text-white text-2xl"></i>
          </div>
          <h3 class="text-2xl font-bold text-gray-900 mb-4">Database</h3>
          <ul class="space-y-3">
            <li class="flex items-start gap-3">
              <i class="bi bi-check-circle-fill text-blue-600 mt-1"></i>
              <div>
                <p class="font-semibold text-gray-900">MySQL</p>
                <p class="text-sm text-gray-600">Relational database</p>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="bg-blue-600 text-white py-4">
  <div class="max-w-6xl mx-auto px-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <!-- Left: Copyright -->
      <div class="text-sm">
        <p>&copy; 2025 RCH Water. All rights reserved. Built with passion by our amazing team.</p>
      </div>

      <!-- Center: Navigation Links -->
      <div class="flex flex-wrap gap-6 text-sm justify-center flex-1">
        <a href="home/home_page.php" class="hover:underline transition">Home</a>
        <a href="#developers" class="hover:underline transition">Developers</a>
        <a href="#tech-stack" class="hover:underline transition">Tech Stack</a>
      </div>

      <!-- Right: Social Media Icons -->
      <div class="flex gap-4 text-sm">
        <a href="#" class="hover:text-blue-500 transition">
          <i class="bi bi-facebook"></i>
        </a>
        <a href="#" class="hover:text-pink-500 transition">
          <i class="bi bi-instagram"></i>
        </a>
        <a href="#" class="hover:text-gray-400 transition">
          <i class="bi bi-twitter"></i>
        </a>
      </div>
    </div>
  </div>
</footer>

  <script>
    // Smooth scroll for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({ behavior: 'smooth' });
        }
      });
    });

    // Animate elements on scroll
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
        }
      });
    }, observerOptions);

    document.querySelectorAll('.team-card').forEach(card => {
      card.style.opacity = '0';
      card.style.transform = 'translateY(20px)';
      card.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
      observer.observe(card);
    });
  </script>

</body>
</html>
