<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RCH Water - Fresh Water Delivered Daily</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        html {
            scroll-behavior: smooth;
        }

        :root {
            --nav-h: 4rem;
        }

        body {
            padding-top: var(--nav-h);
        }

        nav {
            height: var(--nav-h);
        }

        section {
            scroll-margin-top: calc(var(--nav-h) + 0.5rem);
        }

        /* Animations */
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

        @keyframes slideIn {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes pulse-slow {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.8s ease-out;
        }

        .animate-slideIn {
            animation: slideIn 0.6s ease-out;
        }

        .animate-pulse-slow {
            animation: pulse-slow 2s ease-in-out infinite;
        }

        .feature-card {
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(37, 99, 235, 0.2);
        }

        .icon-box {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #2563eb, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-text {
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

            <ul class="hidden md:flex gap-10 text-sm font-medium text-gray-700">
                <li><a href="#home" class="hover:text-blue-600 transition flex items-center gap-2"><i class="bi bi-house"></i> Home</a></li>
                <li><a href="#features" class="hover:text-blue-600 transition flex items-center gap-2"><i class="bi bi-star"></i> Features</a></li>
                <li><a href="#about" class="hover:text-blue-600 transition flex items-center gap-2"><i class="bi bi-info-circle"></i> About</a></li>
                <li><a href="#contact" class="hover:text-blue-600 transition flex items-center gap-2"><i class="bi bi-envelope"></i> Contact</a></li>
                <li><a class="go-developers hover:text-blue-600 transition flex items-center gap-2 cursor-pointer"><i class="bi bi-people"></i> Developers</a></li>
            </ul>

            <div class="flex items-center gap-3">
                <button class="go-login hidden md:inline-block px-4 py-2 text-blue-600 border border-blue-600 rounded-lg hover:bg-blue-50 transition font-medium text-sm">Login</button>
                <button class="go-signup px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-lg hover:shadow-lg transition font-medium text-sm flex items-center justify-center gap-2"><i class="bi bi-person-plus"></i> Sign Up</button>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section id="home" class="min-h-screen pt-20 flex items-center relative overflow-hidden">
        <!-- Updated hero to use water splash background design -->
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('img/home3.jpg');"></div>

        <!-- Added dark overlay for better text contrast -->
        <div class="absolute inset-0 bg-black/20"></div>

        <div class="max-w-6xl mx-auto px-6 py-20 relative z-10 w-full h-full flex items-center justify-center">
            <div class="text-center">
                <div class="animate-fadeInUp text-center">
                    <h1
                        class="text-5xl md:text-6xl lg:text-7xl font-extrabold text-white leading-tight italic mb-6 drop-shadow-lg"
                        style="text-shadow: 0 2px 10px rgba(0,0,0,0.8), 0 0 30px rgba(37, 99, 235, 0.6);">
                        Fresh Water,<br>
                        <span class=>Delivered Daily</span>
                    </h1>

                    <p class="mt-6 text-white text-base md:text-lg mx-auto max-w-2xl drop-shadow-md">
                        Enjoy pure, refreshing water brought straight to your doorstep—simple, reliable, and always on time.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center mt-8">
                        <button class="go-order btn-primary px-10 py-4 text-white rounded-lg font-semibold flex items-center justify-center gap-3 text-lg shadow-lg hover:shadow-xl transition"><i class="bi bi-cart-plus-fill text-xl"></i> ORDER NOW</button>
                    </div>
                </div>

            </div>
        </div>
    </section>


    <!-- FEATURES SECTION -->
    <section id="features" class="py-24 bg-gradient-to-b from-white to-gray-50">
        <div class="max-w-6xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">Why Choose RCH Water?</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">Experience the difference with our water ordering, refilling, and delivery service</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Feature 1 -->
                <div class="feature-card bg-white p-8 rounded-2xl border border-gray-100">
                    <div class="icon-box w-14 h-14 mb-6">
                        <i class="bi bi-shield-check text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">100% Safe</h3>
                    <p class="text-gray-600">Rigorous quality testing and certification ensure your water meets the highest purity standards.</p>
                </div>

                <!-- Feature 2 -->
                <div class="feature-card bg-white p-8 rounded-2xl border border-gray-100">
                    <div class="icon-box w-14 h-14 mb-6">
                        <i class="bi bi-lightning-charge text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Fast Delivery</h3>
                    <p class="text-gray-600">Schedule pick up and deliveries at your convenience with our flexible booking system and real-time tracking.</p>
                </div>

                <!-- Feature 3 -->
                <div class="feature-card bg-white p-8 rounded-2xl border border-gray-100">
                    <div class="icon-box w-14 h-14 mb-6">
                        <i class="bi bi-cash-coin text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Affordable Rates</h3>
                    <p class="text-gray-600">Transparent pricing with no hidden fees. Great value for premium quality water.</p>
                </div>

                <!-- Feature 4 -->
                <div class="feature-card bg-white p-8 rounded-2xl border border-gray-100">
                    <div class="icon-box w-14 h-14 mb-6">
                        <i class="bi bi-cart text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Streamlined Ordering/Refilling Management</h3>
                    <p class="text-gray-600">Easily view and manage your order/refill requests in one place. Check order status and stay updated with a clean, organized dashboard built for fast, hassle-free water order/refill management.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS SECTION -->
    <section class="py-24 bg-white">
        <div class="max-w-6xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">How It Works</h2>
                <p class="text-xl text-gray-600">Simple steps to get fresh water delivered to your home</p>
            </div>

            <div class="grid md:grid-cols-4 gap-8">
                <!-- Step 1 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-white text-2xl font-bold">1</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Sign Up</h3>
                    <p class="text-gray-600">Create your RCH account in just a few clicks to start ordering and tracking your water deliveries.</p>
                </div>

                <!-- Step 2 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-white text-2xl font-bold">2</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Login</h3>
                    <p class="text-gray-600">Access the platform by logging in through the web-based dashboard.</p>
                </div>

                <!-- Step 3 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-white text-2xl font-bold">3</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Place Your Order</h3>
                    <p class="text-gray-600">Choose your gallon size, quantity, and gallon pick up and delivery schedule—then confirm your order instantly.</p>
                </div>

                <!-- Step 4 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-white text-2xl font-bold">4</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Track and Receive</h3>
                    <p class="text-gray-600">Monitor your order status in real time and receive your fresh, clean water right at your doorstep.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- TESTIMONIALS SECTION -->
    <section class="py-24 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-6xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">What Our Customers Say</h2>
                <p class="text-xl text-gray-600">Join hundreds of satisfied customers across Lipa City, Batangas</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Testimonial 1 -->
                <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100">
                    <div class="flex items-center gap-1 mb-4">
                        <i class="bi bi-star-fill text-yellow-400"></i>
                        <i class="bi bi-star-fill text-yellow-400"></i>
                        <i class="bi bi-star-fill text-yellow-400"></i>
                        <i class="bi bi-star-fill text-yellow-400"></i>
                        <i class="bi bi-star-fill text-yellow-400"></i>
                    </div>
                    <p class="text-gray-700 mb-6">"Excellent service! The water quality is exceptional and delivery is always on time. I've been a customer for 2 years and wouldn't switch!"</p>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-blue-200 rounded-full flex items-center justify-center">
                            <i class="bi bi-person-fill text-blue-600"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Maria Santos</p>
                            <p class="text-sm text-gray-600">Tambo, Lipa City</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100">
                    <div class="flex items-center gap-1 mb-4">
                        <i class="bi bi-star-fill text-yellow-400"></i>
                        <i class="bi bi-star-fill text-yellow-400"></i>
                        <i class="bi bi-star-fill text-yellow-400"></i>
                        <i class="bi bi-star-fill text-yellow-400"></i>
                        <i class="bi bi-star-fill text-yellow-400"></i>
                    </div>
                    <p class="text-gray-700 mb-6">"Perfect for my office! The ordering process is simple, and their customer support is incredibly responsive and helpful."</p>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-cyan-200 rounded-full flex items-center justify-center">
                            <i class="bi bi-person-fill text-cyan-600"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Juan dela Cruz</p>
                            <p class="text-sm text-gray-600">Business Owner</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 3 -->
                <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100">
                    <div class="flex items-center gap-1 mb-4">
                        <i class="bi bi-star-fill text-yellow-400"></i>
                        <i class="bi bi-star-fill text-yellow-400"></i>
                        <i class="bi bi-star-fill text-yellow-400"></i>
                        <i class="bi bi-star-fill text-yellow-400"></i>
                        <i class="bi bi-star-fill text-yellow-400"></i>
                    </div>
                    <p class="text-gray-700 mb-6">"Great value for money. The flexibility in scheduling deliveries and their transparent pricing is exactly what I was looking for!"</p>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-blue-200 rounded-full flex items-center justify-center">
                            <i class="bi bi-person-fill text-blue-600"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Rosa Villanueva</p>
                            <p class="text-sm text-gray-600">Sabang, Lipa City</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ABOUT SECTION -->
    <section id="about" class="py-24 bg-gradient-to-r from-blue-600 to-cyan-600 text-white">
        <div class="max-w-6xl mx-auto px-6">
            <!-- Use flex for centering -->
            <div class="justify-center">
                <div class="max-w-6xl text-center">
                    <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">About RCH</h2>
                    <p class="text-lg text-white mb-4 leading-relaxed">
                        Welcome to RCH Water Ordering, Refilling and Delivery Platform, a smart solution that ensures clean, safe, and refreshing drinking water is always within reach. Designed for homes, offices, and businesses, our system integrates water refilling with real-time delivery tracking, making hydration seamless and reliable.
                    </p>
                    <p class="text-lg text-white mb-6 leading-relaxed">
                        Our delivery process is built on three core principles: quality, convenience, and consistency. Every drop of water we provide goes through a thorough purification process, ensuring it meets the highest standards for safety and taste. With our connected system, you can schedule refills, track deliveries, and manage your water needs effortlessly through a user-friendly interface.
                    </p>
                    <p class="text-lg text-white mb-6 leading-relaxed">
                        Whether for daily household use or business operations, RCH provides dependable water supply and smart delivery management you can trust.
                    </p>
                    <p class="mt-8 font-semibold">Smart hydration. Seamless delivery. Anytime, anywhere.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTACT SECTION -->
    <section id="contact" class="py-24 bg-white">
        <div class="max-w-6xl mx-auto px-6">
            <h2 class="text-4xl md:text-5xl font-bold text-gray-900 text-center mb-16">Get In Touch</h2>

            <div class="grid md:grid-cols-3 gap-8 mb-12">
                <!-- Contact Info 1 -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-2xl p-8 shadow-lg">
                    <i class="bi bi-clock-history text-3xl mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Working Hours</h3>
                    <p class="text-blue-100">7:00 AM - 6:00 PM</p>
                    <p class="text-blue-100">Monday - Sunday</p>
                </div>

                <!-- Contact Info 2 -->
                <div class="bg-gradient-to-br from-cyan-500 to-cyan-600 text-white rounded-2xl p-8 shadow-lg">
                    <i class="bi bi-geo-alt text-3xl mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Location</h3>
                    <p class="text-cyan-100">123 Pag-asa Street</p>
                    <p class="text-cyan-100">Lipa City, Batangas</p>
                </div>

                <!-- Contact Info 3 -->
                <div class="bg-gradient-to-br from-blue-600 to-cyan-600 text-white rounded-2xl p-8 shadow-lg">
                    <i class="bi bi-envelope text-3xl mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Email</h3>
                    <p class="text-blue-100">rchwater1@gmail.com</p>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="max-w-2xl mx-auto bg-gradient-to-br from-gray-50 to-gray-100 p-10 rounded-3xl border border-gray-200">
                <h3 class="text-2xl font-bold text-gray-900 mb-8 flex items-center gap-2">
                    <i class=></i> Send Us Your Message/Feedback
                </h3>

                <form class="space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">
                                <i class="bi bi-person"></i> Full Name: <span class="text-red-500">*</span></label>
                            </label>
                            <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="Your name">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">
                                <i class="bi bi-envelope"></i> Email Address: <span class="text-red-500">*</span></label>
                            </label>
                            <input type="email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="your@email.com">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            <i class="bi bi-house"></i> Address: <span class="text-red-500">*</span></label>
                        </label>
                        <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="Your address">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            <i class="bi bi-pencil"></i> Message/Feedback: <span class="text-red-500">*</span></label>
                        </label>
                        <textarea class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 resize-none h-32" placeholder="Tell us what's on your mind..."></textarea>
                    </div>

                    <button type="submit" class="btn-primary w-full py-3 text-white rounded-lg font-bold flex items-center justify-center gap-2">
                        <i class="bi bi-send"></i> Send Message/Feedback
                    </button>
                </form>
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
                    <a href="home" class="hover:underline transition">Home</a>
                    <a href="features" class="hover:underline transition">Features</a>
                    <a href="about" class="hover:underline transition">About</a>
                    <a href="contact" class="hover:underline transition">Contact</a>
                    <a class="go-developers hover:underline transition flex items-center gap-2 cursor-pointer"><i class="bi bi-people"></i> Developers</a>
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
        // Smooth scroll interactions
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fadeInUp');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.feature-card, .stat-number').forEach(el => {
            observer.observe(el);
        });

        // Navbar scroll effect
        const navbar = document.querySelector('nav');
        let lastScrollTop = 0;

        window.addEventListener('scroll', () => {
            let scrollTop = window.pageYOffset;
            if (scrollTop > 100) {
                navbar.classList.add('shadow-md');
                navbar.classList.add('bg-white/95');
            } else {
                navbar.classList.remove('shadow-md');
            }
            lastScrollTop = scrollTop;
        });

        // Form submission
        document.querySelector('form').addEventListener('submit', e => {
            e.preventDefault();
            alert('Thank you for your message! We will get back to you soon.');
            e.target.reset();
        });
    </script>

    <script>
        // Redirect to Developers Page (Navbar & Footer)
        document.querySelectorAll('.go-developers').forEach(btn => {
            btn.addEventListener('click', () => {
                window.location.href = "../Developers/developers.php";
            });
        });

        // Redirect to Login Page
        document.querySelectorAll('.go-login').forEach(btn => {
            btn.addEventListener('click', () => {
                window.location.href = "../index.php";
            });
        });

        // Redirect to Order Now Button → index.php
        document.querySelectorAll('.go-order').forEach(btn => {
            btn.addEventListener('click', () => {
                window.location.href = "../index.php";
            });
        });

        // Redirect to Sign Up Page
        document.querySelectorAll('.go-signup').forEach(btn => {
            btn.addEventListener('click', () => {
                window.location.href = "../signup.php";
            });
        });
    </script>


</body>

</html>