<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="image/MainIcon.png" type="image/x-icon" />
    <title>PetTrack - Balingasag Dog and Cat Clinic</title>
    <script src="Assets/chart.js"></script>
    <link rel="stylesheet" href="Assets/FontAwsome/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #fafbfc;
        }

        /* Header Styles */
        header {
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.95);
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
        }

        header.scrolled {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #0a4d3c 0%, #169976 50%, #1dcd9f 100%);
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 50%, rgba(29, 205, 159, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(22, 153, 118, 0.2) 0%, transparent 50%);
            pointer-events: none;
        }

        .hero-content {
            position: relative;
            z-index: 10;
            animation: fadeInUp 0.8s ease-out;
        }

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

        .hero-image-container {
            position: relative;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        /* Glass Card Effect */
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 20px 48px rgba(0, 0, 0, 0.12);
        }

        /* Service Cards */
        .service-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(0, 0, 0, 0.04);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #169976, #1dcd9f);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 36px rgba(22, 153, 118, 0.15);
        }

        .service-card:hover::before {
            transform: scaleX(1);
        }

        .service-icon {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, #169976, #1dcd9f);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            transition: all 0.4s ease;
            position: relative;
        }

        .service-card:hover .service-icon {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 8px 24px rgba(22, 153, 118, 0.3);
        }

        /* Stats Section */
        .stats-section {
            background: linear-gradient(135deg, #0a4d3c 0%, #169976 100%);
            position: relative;
            overflow: hidden;
        }

        .stats-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.5;
        }

        .stat-card {
            text-align: center;
            position: relative;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff, #e0f9f3);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
            display: block;
        }

        /* About Section */
        .about-section {
            background: linear-gradient(180deg, #fafbfc 0%, #ffffff 100%);
        }

        .feature-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(22, 153, 118, 0.12);
        }

        .feature-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #e8f8f3, #d1f2e8);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        /* Team Cards */
        .team-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            transition: all 0.4s ease;
        }

        .team-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 32px rgba(22, 153, 118, 0.15);
        }

        .team-image {
            width: 100%;
            height: 280px;
            background: linear-gradient(135deg, #169976, #1dcd9f);
            position: relative;
            overflow: hidden;
        }

        .team-image::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 100px;
            background: linear-gradient(to top, rgba(255, 255, 255, 0.9), transparent);
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #0a4d3c 0%, #169976 50%, #1dcd9f 100%);
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            top: -250px;
            right: -250px;
            animation: pulse 8s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.5;
            }

            50% {
                transform: scale(1.2);
                opacity: 0.8;
            }
        }

        /* Button Styles */
        .btn-primary {
            background: linear-gradient(135deg, #169976, #1dcd9f);
            color: white;
            padding: 1rem 2.5rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 16px rgba(22, 153, 118, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-primary:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(22, 153, 118, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #169976;
            padding: 1rem 2.5rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid white;
        }

        .btn-secondary:hover {
            background: transparent;
            color: white;
            transform: translateY(-2px);
        }

        /* Footer */
        footer {
            background: linear-gradient(135deg, #0a3d31 0%, #0f5240 100%);
            position: relative;
        }

        .footer-link {
            color: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }

        .footer-link:hover {
            color: #1dcd9f;
            transform: translateX(4px);
        }

        /* Section Titles */
        .section-title {
            font-size: 2.75rem;
            font-weight: 800;
            background: linear-gradient(135deg, #169976, #1dcd9f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
            display: inline-block;
        }

        .section-subtitle {
            font-size: 1.125rem;
            color: #64748b;
            max-width: 600px;
            margin: 1rem auto 0;
            line-height: 1.6;
        }

        /* Appointment Modal - Keep Original Styles */
        .appointment-indicator.full-day {
            position: absolute;
            top: 2px;
            right: 2px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #dc3545;
        }

        #calendarDays div {
            position: relative;
            padding: 4px;
        }

        #timeAvailability {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
        }

        #timeAvailability.available {
            color: #28a745;
        }

        #timeAvailability.taken {
            color: #dc3545;
        }

        #timeAvailability .indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        #timeAvailability.available .indicator {
            background-color: #28a745;
        }

        #timeAvailability.taken .indicator {
            background-color: #dc3545;
        }

        #takenTimeSlots {
            margin-top: 8px;
            padding: 8px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
        }

        #takenTimeSlots ul {
            list-style-type: disc;
            padding-left: 20px;
            margin: 0;
        }

        #takenTimeSlots li {
            color: #dc3545;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        #takenTimeSlots:empty {
            display: none;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .section-title {
                font-size: 2rem;
            }

            .stat-number {
                font-size: 2rem;
            }

            .service-icon {
                width: 56px;
                height: 56px;
            }

            .hero-section {
                min-height: 70vh;
            }

            .nav-links.mobile-open {
                display: flex;
                flex-direction: column;
                position: absolute;
                top: 64px;
                left: 0;
                width: 100%;
                background: rgba(255, 255, 255, 0.98);
                backdrop-filter: blur(20px);
                padding: 1rem;
                z-index: 40;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            }

            .nav-links.mobile-open a {
                padding: 0.75rem 0;
                text-align: center;
                border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            }

            #appointmentModal>div {
                max-width: calc(100% - 2rem);
                height: 80vh;
                margin: 10vh auto;
                border-radius: 1rem;
            }

            #appointmentForm {
                padding: 1rem;
            }
        }

        /* Updated Design Elements: Softer borders, increased padding, subtle animations */
        section {
            transition: opacity 0.5s ease-in-out;
        }

        .glass-card {
            border-radius: 32px;
            padding: 3rem;
        }

        .service-card {
            border-radius: 28px;
            padding: 3rem;
        }

        .feature-card {
            border-radius: 24px;
            padding: 2.5rem;
        }

        .team-card {
            border-radius: 28px;
        }

        .btn-primary,
        .btn-secondary {
            border-radius: 12px;
            padding: 0.5rem 1.5rem;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="fixed top-0 left-0 w-full h-16 z-50">
        <div class="flex items-center justify-between h-full px-4 md:px-8 max-w-7xl mx-auto">
            <div class="flex items-center gap-3">
                <img src="./image/MainIcon.png" alt="PetTrack Logo" class="w-8 h-8">
                <p class="text-[#169976] font-bold text-xl">PetTrack</p>
            </div>

            <div class="flex items-center gap-4">
                <button id="mobile-menu-button" class="md:hidden text-gray-700 focus:outline-none">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <nav class="nav-links hidden md:flex items-center gap-8">
                    <a class="text-gray-700 font-medium hover:text-[#169976] transition-colors" href="#">Home</a>
                    <a class="text-gray-700 font-medium hover:text-[#169976] transition-colors" href="#about">About</a>
                    <a class="text-gray-700 font-medium hover:text-[#169976] transition-colors" href="#services">Services</a>
                    <a class="text-gray-700 font-medium hover:text-[#169976] transition-colors" href="#contacts">Contact</a>
                </nav>

                <button onclick="openModal()" class="btn-primary text-sm md:text-base px-3 py-1 md:px-4 md:py-2">
                    Book Now
                </button>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section flex items-center pt-24 px-4 md:px-8">
        <div class="max-w-7xl mx-auto w-full">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="hero-content text-white">
                    <div class="inline-block bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full mb-6">
                        <p class="text-sm font-medium"><img src="./image/MainIcon.png" alt="Award" class="w-4 h-4 inline mr-1"> Efficient Pet Record Management</p>
                    </div>
                    <h1 class="text-4xl md:text-6xl font-extrabold leading-tight mb-6">
                        Streamline Pet Care at Balingasag Dog and Cat Clinic
                    </h1>
                    <p class="text-lg md:text-xl opacity-90 mb-8 leading-relaxed">
                        PetTrack addresses challenges in managing pet medical records by centralizing data, reducing errors, and ensuring timely access for better pet care.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <button onclick="openModal()" class="btn-secondary">
                            Schedule Appointment
                        </button>
                        <a href="#services" class="btn-primary text-center">
                            Explore Features
                        </a>
                    </div>
                </div>
                <div class="hero-image-container hidden lg:block">
                    <img src="./image/dog-cat.png" alt="Happy Pets" class="w-full max-w-2xl mx-auto drop-shadow-2xl" />
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section py-16 md:py-24">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="stat-card">
                    <span class="stat-number">Efficient</span>
                    <p class="text-white/90 font-medium">Record Management</p>
                </div>
                <div class="stat-card">
                    <span class="stat-number">Secure</span>
                    <p class="text-white/90 font-medium">Data Storage</p>
                </div>
                <div class="stat-card">
                    <span class="stat-number">Easy</span>
                    <p class="text-white/90 font-medium">Access & Updates</p>
                </div>
                <div class="stat-card">
                    <span class="stat-number">Local</span>
                    <p class="text-white/90 font-medium">Clinic Focus</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-20 md:py-32 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="section-title">System Features</h2>
                <p class="section-subtitle">
                    Key capabilities designed to improve veterinary clinic operations and pet care
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-file-medical text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3 text-center">Medical Records</h3>
                    <p class="text-gray-600 text-center leading-relaxed">
                        Centralized storage and management of pet medical histories and treatments.
                    </p>
                </div>

                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-calendar-check text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3 text-center">Appointments</h3>
                    <p class="text-gray-600 text-center leading-relaxed">
                        Easy booking and management of client appointments.
                    </p>
                </div>

                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-users-cog text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3 text-center">User Management</h3>
                    <p class="text-gray-600 text-center leading-relaxed">
                        Admin control over user accounts for vets and staff.
                    </p>
                </div>

                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-chart-bar text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3 text-center">Basic Reporting</h3>
                    <p class="text-gray-600 text-center leading-relaxed">
                        Generate reports on records and transactions.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about-section py-20 md:py-32">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="section-title">About PetTrack</h2>
                <p class="section-subtitle">
                    Web-based management system for Balingasag Dog and Cat Clinic, addressing record-keeping challenges.
                </p>
            </div>

            <!-- Why Choose Us -->
            <div class="mb-16">
                <h3 class="text-3xl font-bold text-gray-800 mb-12 text-center">Significance of PetTrack</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt text-[#169976] text-2xl"></i>
                        </div>
                        <h4 class="text-xl font-semibold text-gray-800 mb-3">For Clinic Staff</h4>
                        <p class="text-gray-600 leading-relaxed">
                            Easier updates to pet records, less paperwork, more time for care.
                        </p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-clock text-[#169976] text-2xl"></i>
                        </div>
                        <h4 class="text-xl font-semibold text-gray-800 mb-3">For Pet Owners</h4>
                        <p class="text-gray-600 leading-relaxed">
                            Convenient online booking and improved communication for better pet health.
                        </p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-heart text-[#169976] text-2xl"></i>
                        </div>
                        <h4 class="text-xl font-semibold text-gray-800 mb-3">For Communities</h4>
                        <p class="text-gray-600 leading-relaxed">
                            Enhances overall pet health, supporting public health in local areas.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Team -->
            <div>
                <h3 class="text-3xl font-bold text-gray-800 mb-12 text-center">Project Team</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="team-card">
                        <div class="team-image flex items-center justify-center">
                            <i class="fas fa-user-circle text-white text-8xl opacity-50"></i>
                        </div>
                        <div class="p-6 text-center">
                            <h4 class="text-xl font-bold text-gray-800 mb-2">Michaela G. Abecia</h4>
                            <p class="text-[#169976] font-semibold mb-3">Project Manager</p>
                            <p class="text-gray-600 text-sm">
                                Information Technology Student at St. Rita’s College
                            </p>
                        </div>
                    </div>

                    <div class="team-card">
                        <div class="team-image flex items-center justify-center">
                            <i class="fas fa-user-circle text-white text-8xl opacity-50"></i>
                        </div>
                        <div class="p-6 text-center">
                            <h4 class="text-xl font-bold text-gray-800 mb-2">Jan Paul Michael Dela Cera</h4>
                            <p class="text-[#169976] font-semibold mb-3">Project Developer</p>
                            <p class="text-gray-600 text-sm">
                                Information Technology Student at St. Rita’s College
                            </p>
                        </div>
                    </div>

                    <div class="team-card">
                        <div class="team-image flex items-center justify-center">
                            <i class="fas fa-user-circle text-white text-8xl opacity-50"></i>
                        </div>
                        <div class="p-6 text-center">
                            <h4 class="text-xl font-bold text-gray-800 mb-2">Sheryl Mae Lozano</h4>
                            <p class="text-[#169976] font-semibold mb-3">Project Designer</p>
                            <p class="text-gray-600 text-sm">
                                Information Technology Student at St. Rita’s College
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section py-20 md:py-32 text-white relative">
        <div class="max-w-4xl mx-auto px-4 text-center relative z-10">
            <h2 class="text-3xl md:text-5xl font-extrabold mb-6">
                Ready to Streamline Your Pet Care?
            </h2>
            <p class="text-xl mb-10 opacity-90 leading-relaxed">
                Join Balingasag Dog and Cat Clinic in using PetTrack for efficient management and superior pet health services.
            </p>
            <button onclick="openModal()" class="btn-secondary text-lg px-8 py-4">
                Book Your Appointment Today
            </button>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contacts" class="py-16 text-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 mb-8">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center">
                            <img src="./image/MainIcon.png" alt="Paw" class="w-6 h-6 inline">
                        </div>
                        <p class="text-white font-bold text-2xl">PetTrack</p>
                    </div>
                    <p class="mb-6 text-white/80 leading-relaxed">Management system for Balingasag Dog and Cat Clinic</p>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 footer-link">
                            <i class="fas fa-phone-alt"></i>
                            <span>(123) 456-7890</span>
                        </div>
                        <div class="flex items-center gap-3 footer-link">
                            <i class="fas fa-envelope"></i>
                            <span>info@pettrack.com</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-xl font-bold mb-4">Our Location</h3>
                    <div class="flex items-start gap-3 mb-6">
                        <i class="fas fa-map-marker-alt mt-1"></i>
                        <p class="text-white/80 leading-relaxed">
                            PQXJ+Q9J, Butuan - Cagayan de Oro - Iligan Rd, Balingasag, Misamis Oriental
                        </p>
                    </div>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center hover:bg-white/20 transition-all">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center hover:bg-white/20 transition-all">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center hover:bg-white/20 transition-all">
                            <i class="fab fa-twitter"></i>
                        </a>
                    </div>
                </div>

                <div>
                    <h3 class="text-xl font-bold mb-4">Find Us</h3>
                    <div class="rounded-xl overflow-hidden h-48 shadow-lg">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d1262.921952432691!2d124.78045648758747!3d8.74964320210998!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x32ffe1fbecdd99ad%3A0x73cf6beb3b523f24!2sBalingasag%20Dog%20And%20Cat%20Clinic!5e1!3m2!1sen!2sph!4v1755610325757!5m2!1sen!2sph" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>

            <div class="border-t border-white/10 pt-8 text-center">
                <p class="text-white/60">© 2025 PetTrack. All rights reserved. Developed for St. Rita’s College of Balingasag, Inc.</p>
            </div>
        </div>
    </footer>

    <!-- Appointment Modal - ORIGINAL FUNCTIONALITY PRESERVED -->
    <div id="appointmentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center" role="dialog" aria-labelledby="modalTitle" aria-modal="true">
        <div class="bg-white w-full max-w-5xl mx-4 h-[88vh] rounded-xl shadow-xl flex flex-col overflow-y-auto" tabindex="-1">

            <!-- Header -->
            <div class="bg-[#169976] px-6 py-4 rounded-t-xl flex justify-between items-center">
                <h2 id="modalTitle" class="text-2xl font-semibold text-white text-center w-full">Book an Appointment</h2>
            </div>

            <!-- Form -->
            <form id="appointmentForm" method="POST" action="./functions/appointment-handler.php" class="flex flex-col justify-between flex-1 p-8">

                <!-- Content area: Calendar + Inputs -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 flex-1">

                    <!-- Left: Calendar -->
                    <div class="flex flex-col justify-center">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                        <div id="calendarContainer" class="p-4 bg-gray-50 rounded-md border border-gray-200 shadow-sm">
                            <div class="flex justify-between items-center mb-3">
                                <button type="button" id="prevMonth" class="px-3 py-1 bg-[#169976] text-white rounded hover:bg-[#137a60]">&lt;</button>
                                <span id="monthYear" class="text-base font-semibold"></span>
                                <button type="button" id="nextMonth" class="px-3 py-1 bg-[#169976] text-white rounded hover:bg-[#137a60]">&gt;</button>
                            </div>
                            <div id="calendarDays" class="grid grid-cols-7 gap-2 text-center"></div>
                            <input type="hidden" id="selectedDate" name="appointment_date" required>
                        </div>
                        <p class="text-sm text-gray-500 mt-2">Please select your preferred date.</p>
                    </div>

                    <!-- Right: Form fields -->
                    <div class="flex flex-col justify-center space-y-4">

                        <!-- Owner -->
                        <div>
                            <label for="owner" class="block text-sm font-medium text-gray-700">Owner Name</label>
                            <input type="text" id="owner" name="owner_name" placeholder="Enter Full Name" required
                                class="mt-1 p-2 text-sm block w-full rounded-md border border-gray-300 shadow-sm focus:ring-[#169976] focus:border-[#169976]" />
                        </div>

                        <!-- Contact -->
                        <div>
                            <label for="contact" class="block text-sm font-medium text-gray-700">Contact Number</label>
                            <input type="text" id="contact" name="contact_number" required
                                pattern="^09\d{9}$"
                                maxlength="11"
                                placeholder="e.g. 09171234567"
                                title="Enter a valid Philippine number starting with 09 and 11 digits long"
                                class="mt-1 p-2 text-sm block w-full rounded-md border border-gray-300 shadow-sm focus:ring-[#169976] focus:border-[#169976]" />
                        </div>

                        <!-- Time -->
                        <div>
                            <label for="time" class="block text-sm font-medium text-gray-700">Time</label>
                            <input type="time" id="time" name="appointment_time" required
                                class="mt-1 p-2 text-sm w-full rounded-md border border-gray-300 focus:ring-[#169976] focus:border-[#169976]"
                                min="08:00" max="18:00" step="1800" />
                            <p id="timeAvailability" class="text-sm mt-1 hidden">
                                <span class="indicator"></span>
                                <span id="timeStatus"></span>
                            </p>
                            <p id="timeError" class="text-sm text-red-500 mt-1 hidden">Please pick a time between 8:00 AM and 6:00 PM.</p>
                            <div id="takenTimeSlots" class="text-sm mt-1"></div>
                            <p class="text-xs text-gray-500 mt-1">Available between 8:00 AM and 6:00 PM. Each appointment is 1 hour and 30 minutes.</p>
                        </div>

                        <!-- Reason -->
                        <div>
                            <label for="reason" class="block text-sm font-medium text-gray-700">Reason for Visit</label>
                            <select id="reason" name="reason" required onchange="toggleOtherReason(this)"
                                class="mt-1 p-2 text-sm block w-full rounded-md border border-gray-300 shadow-sm focus:ring-[#169976] focus:border-[#169976]">
                                <option value="">-- Select Reason --</option>
                                <option value="Checkup">Check-up</option>
                                <option value="Vaccination">Vaccination</option>
                                <option value="Grooming">Grooming</option>
                                <option value="Surgery">Surgery</option>
                                <option value="Emergency">Emergency</option>
                                <option value="Other">Other</option>
                            </select>
                            <input type="text" id="other_reason" name="other_reason" placeholder="Please specify"
                                style="display:none; margin-top:5px;"
                                class="mt-1 p-2 text-sm block w-full rounded-md border border-gray-300 shadow-sm focus:ring-[#169976] focus:border-[#169976]">
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end space-x-3 pt-4 border-t mt-4">
                    <button type="button" onclick="closeModal()" class="px-5 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">Cancel</button>
                    <button type="submit" id="submitButton" class="px-5 py-2 text-sm bg-[#169976] text-white rounded hover:bg-[#18b98e] transition">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Header scroll effect
        window.addEventListener('scroll', () => {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const navLinks = document.querySelector('.nav-links');
        mobileMenuButton.addEventListener('click', () => {
            navLinks.classList.toggle('mobile-open');
        });

        // ORIGINAL APPOINTMENT FUNCTIONALITY - DO NOT MODIFY
        function openModal() {
            document.getElementById("appointmentModal").classList.remove("hidden");
            initializeCalendar();
        }

        function closeModal() {
            document.getElementById("appointmentModal").classList.add("hidden");
            document.getElementById("timeAvailability").classList.add("hidden");
            document.getElementById("timeError").classList.add("hidden");
            document.getElementById("takenTimeSlots").innerHTML = "";
            const submitButton = document.getElementById("submitButton");
            submitButton.disabled = false;
            submitButton.classList.remove("bg-gray-400", "cursor-not-allowed");
            submitButton.classList.add("bg-[#169976]", "hover:bg-[#18b98e]");
        }

        function toggleOtherReason(select) {
            const otherReasonInput = document.getElementById("other_reason");
            if (select.value === "Other") {
                otherReasonInput.style.display = "block";
                otherReasonInput.required = true;
            } else {
                otherReasonInput.style.display = "none";
                otherReasonInput.value = "";
                otherReasonInput.required = false;
            }
        }

        // Calendar Initialization
        let currentDate = new Date();

        function initializeCalendar() {
            const monthYear = document.getElementById("monthYear");
            const calendarDays = document.getElementById("calendarDays");
            const prevMonth = document.getElementById("prevMonth");
            const nextMonth = document.getElementById("nextMonth");
            const selectedDate = document.getElementById("selectedDate");
            const timeInput = document.getElementById("time");

            function renderCalendar() {
                calendarDays.innerHTML = "";
                const year = currentDate.getFullYear();
                const month = currentDate.getMonth();
                monthYear.textContent = currentDate.toLocaleString("default", {
                    month: "long",
                    year: "numeric"
                });

                const firstDay = new Date(year, month, 1);
                const lastDay = new Date(year, month + 1, 0);
                const daysInMonth = lastDay.getDate();
                const startingDay = firstDay.getDay();

                // Add empty days for the first week
                for (let i = 0; i < startingDay; i++) {
                    const emptyDiv = document.createElement("div");
                    calendarDays.appendChild(emptyDiv);
                }

                // Add days of the month
                for (let day = 1; day <= daysInMonth; day++) {
                    const dayDiv = document.createElement("div");
                    dayDiv.textContent = day;
                    dayDiv.classList.add("cursor-pointer", "hover:bg-gray-200", "rounded-full", "p-1");

                    const current = new Date(year, month, day);
                    if (current < new Date()) {
                        dayDiv.classList.add("text-gray-400", "cursor-not-allowed");
                    } else {
                        dayDiv.addEventListener("click", () => {
                            selectedDate.value = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                            const days = document.querySelectorAll("#calendarDays div");
                            days.forEach(d => d.classList.remove("bg-[#169976]", "text-white"));
                            dayDiv.classList.add("bg-[#169976]", "text-white");
                            checkTimeAvailability();
                        });
                    }
                    calendarDays.appendChild(dayDiv);
                }
            }

            prevMonth.addEventListener("click", () => {
                currentDate.setMonth(currentDate.getMonth() - 1);
                renderCalendar();
            });

            nextMonth.addEventListener("click", () => {
                currentDate.setMonth(currentDate.getMonth() + 1);
                renderCalendar();
            });

            // Check time availability on time input change
            timeInput.addEventListener("change", checkTimeAvailability);

            // Initial render
            renderCalendar();

            // Time validation on form submit
            document.getElementById("appointmentForm").addEventListener("submit", function(e) {
                const timeInput = document.getElementById("time");
                const timeError = document.getElementById("timeError");
                const timeAvailability = document.getElementById("timeAvailability");
                const [hours, minutes] = timeInput.value.split(":").map(Number);
                if (hours < 8 || hours > 18 || (hours === 18 && minutes > 0)) {
                    e.preventDefault();
                    timeError.classList.remove("hidden");
                    timeAvailability.classList.add("hidden");
                    document.getElementById("takenTimeSlots").innerHTML = "";
                } else if (!selectedDate.value) {
                    e.preventDefault();
                    alert("Please select a date.");
                } else if (timeAvailability.classList.contains("taken")) {
                    e.preventDefault();
                    timeAvailability.classList.remove("hidden");
                    timeAvailability.textContent = "This time slot is taken.";
                }
            });
        }

        // Function to check time slot availability and display taken slots
        function checkTimeAvailability() {
            const selectedDate = document.getElementById("selectedDate").value;
            const timeInput = document.getElementById("time").value;
            const timeAvailability = document.getElementById("timeAvailability");
            const timeStatus = document.getElementById("timeStatus");
            const submitButton = document.getElementById("submitButton");
            const takenTimeSlots = document.getElementById("takenTimeSlots");
            const timeError = document.getElementById("timeError");

            // Clear previous state
            timeAvailability.classList.add("hidden");
            timeError.classList.add("hidden");
            takenTimeSlots.innerHTML = "";
            submitButton.disabled = false;
            submitButton.classList.remove("bg-gray-400", "cursor-not-allowed");
            submitButton.classList.add("bg-[#169976]", "hover:bg-[#18b98e]");

            // Validate time input
            if (timeInput) {
                const [hours, minutes] = timeInput.split(":").map(Number);
                if (hours < 8 || hours > 18 || (hours === 18 && minutes > 0)) {
                    timeError.classList.remove("hidden");
                    return;
                }
            }

            if (!selectedDate || !timeInput) {
                return;
            }

            fetch(`./functions/get-appointments.php?start=${selectedDate}&end=${selectedDate}`)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(events => {
                    const duration = 90; // 1 hour 30 minutes
                    const selectedTime = new Date(`${selectedDate}T${timeInput}:00`);
                    const selectedEndTime = new Date(selectedTime.getTime() + duration * 60 * 1000);

                    // Check if selected time slot is taken
                    let isTaken = false;
                    for (const event of events) {
                        const eventStart = new Date(event.start);
                        const eventEnd = new Date(eventStart.getTime() + (event.extendedProps.duration || 90) * 60 * 1000);
                        if (selectedTime < eventEnd && selectedEndTime > eventStart) {
                            isTaken = true;
                            break;
                        }
                    }

                    timeAvailability.classList.remove("hidden");
                    if (isTaken) {
                        timeAvailability.classList.remove("available");
                        timeAvailability.classList.add("taken");
                        timeStatus.textContent = "This time slot is taken.";
                        submitButton.disabled = true;
                        submitButton.classList.add("bg-gray-400", "cursor-not-allowed");
                        submitButton.classList.remove("bg-[#169976]", "hover:bg-[#18b98e]");
                    } else {
                        timeAvailability.classList.remove("taken");
                        timeAvailability.classList.add("available");
                        timeStatus.textContent = "This time slot is available.";
                    }

                    // Display all taken time slots
                    if (events.length > 0) {
                        const timeSlotsList = document.createElement("ul");
                        events.forEach(event => {
                            const eventStart = new Date(event.start);
                            const eventEnd = new Date(eventStart.getTime() + (event.extendedProps.duration || 90) * 60 * 1000);
                            const startTime = eventStart.toLocaleTimeString([], {
                                hour: '2-digit',
                                minute: '2-digit',
                                timeZone: 'Asia/Manila'
                            });
                            const endTime = eventEnd.toLocaleTimeString([], {
                                hour: '2-digit',
                                minute: '2-digit',
                                timeZone: 'Asia/Manila'
                            });
                            const li = document.createElement("li");
                            li.textContent = `${startTime} - ${endTime}`;
                            timeSlotsList.appendChild(li);
                        });
                        takenTimeSlots.appendChild(timeSlotsList);
                    }
                })
                .catch(error => {
                    console.error("Error checking time availability:", error);
                    timeAvailability.classList.add("hidden");
                    takenTimeSlots.innerHTML = "";
                    submitButton.disabled = false;
                    submitButton.classList.remove("bg-gray-400", "cursor-not-allowed");
                    submitButton.classList.add("bg-[#169976]", "hover:bg-[#18b98e]");
                });
        }
    </script>

    <script src="./js/landing-page.js"></script>
</body>

</html>