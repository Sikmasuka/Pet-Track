<!DOCTYPE html>
<?php session_start(); ?>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="image/MainIcon.png" type="image/x-icon" />
    <title>PetTrack - Professional Pet Healthcare</title>
    <script src="Assets/chart.js"></script>
    <link rel="stylesheet" href="Assets/FontAwsome/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            background-color: #f8fafc;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .hero-section {
            min-height: calc(100vh - 64px);
            position: relative;
        }

        .hero-image {
            position: absolute;
            bottom: 0;
            right: 0;
            max-width: 45%;
        }

        .service-card {
            transition: all 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

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

        @media (max-width: 768px) {
            .hero-image {
                position: static;
                max-width: 100%;
                margin-top: 2rem;
            }

            .hero-section {
                flex-direction: column;
                text-align: center;
                padding: 2rem 1rem;
            }

            .nav-links {
                display: none;
            }

            .footer-content {
                flex-direction: column;
                text-align: center;
            }

            .footer-map {
                height: 250px;
                margin-top: 1rem;
            }
        }

        .footer-map {
            border-radius: 8px;
            overflow: hidden;
            height: 200px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Updated About Section Styles */
        #about {
            background: linear-gradient(to bottom, #f1f5f9, #f8fafc);
        }

        #about .container {
            max-width: 1200px;
        }

        #about h2 {
            font-size: 2rem;
            line-height: 1.2;
            background: linear-gradient(to right, #169976, #1dcd9f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        #about .story-image {
            transition: transform 0.3s ease;
        }

        #about .story-image:hover {
            transform: scale(1.05);
        }

        #about .mission-vision-card {
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
            background: white;
        }

        #about .mission-vision-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }

        #about .why-choose-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
        }

        #about .why-choose-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        #about .stat-card {
            background: linear-gradient(135deg, #169976, #1dcd9f);
            border-radius: 12px;
            padding: 2rem;
            transition: transform 0.3s ease;
        }

        #about .stat-card:hover {
            transform: scale(1.05);
        }

        #about .team-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
        }

        #about .team-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            #about h2 {
                font-size: 1.75rem;
            }

            #about .story-image {
                max-width: 80%;
                margin: 0 auto;
            }
        }

        /* Time slot availability styles */
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

        /* Taken time slots list styles */
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

        /* Additional responsive styles */
        .nav-links.mobile-open {
            display: flex;
            flex-direction: column;
            position: absolute;
            top: 64px;
            left: 0;
            width: 100%;
            background-color: #169976;
            padding: 1rem;
            z-index: 40;
        }

        .nav-links.mobile-open a {
            padding: 0.75rem 0;
            text-align: center;
        }

        /* Professional styling additions */
        .professional-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .professional-card:hover {
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.08);
        }

        .section-title {
            position: relative;
            display: inline-block;
            margin-bottom: 2rem;
        }

        .section-title:after {
            content: '';
            position: absolute;
            width: 50%;
            height: 4px;
            background: linear-gradient(to right, #169976, #1dcd9f);
            bottom: -10px;
            left: 25%;
            border-radius: 2px;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #169976 0%, #1dcd9f 100%);
        }

        .hero-content {
            position: relative;
            z-index: 10;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }

        /* Improved mobile responsiveness */
        @media (max-width: 640px) {
            .hero-section {
                min-height: 70vh;
                padding: 1rem;
            }

            .hero-section h1 {
                font-size: 1.75rem;
                line-height: 1.2;
            }

            .hero-section p {
                font-size: 1rem;
            }

            .section-title {
                font-size: 1.5rem;
            }

            .professional-card {
                padding: 1rem;
            }

            .mission-vision-card {
                padding: 1.5rem;
            }

            .stat-card {
                padding: 1.5rem;
            }

            .team-card {
                padding: 1rem;
            }

            .footer-map {
                height: 180px;
            }
        }

        @media (max-width: 480px) {
            .hero-section h1 {
                font-size: 1.5rem;
            }

            .section-title {
                font-size: 1.25rem;
            }

            .professional-card h3 {
                font-size: 1.125rem;
            }

            .mission-vision-card h3 {
                font-size: 1.125rem;
            }
        }

        /* Mobile fixes for appointment modal */
        @media (max-width: 640px) {
            #appointmentModal>div {
                max-width: calc(100% - 2rem);
                height: 80vh;
                margin: 10vh auto;
                border-radius: 1rem;
            }

            #appointmentForm {
                padding: 1rem;
            }

            #calendarContainer {
                padding: 1rem;
            }

            #appointmentModal>div>.bg-[#169976] {
                position: sticky;
                top: 0;
                z-index: 10;
            }

            #appointmentForm>.flex {
                position: sticky;
                bottom: 0;
                background: white;
                z-index: 10;
                padding-top: 1rem;
                margin-top: 1rem;
            }
        }
    </style>
</head>

<body class="min-h-screen">
    <!-- Header -->
    <header class="fixed top-0 left-0 w-full bg-white shadow-md h-16 z-50">
        <div class="flex items-center justify-between h-full px-4 md:px-6">
            <div class="flex items-center gap-3">
                <img class="w-8 h-8 md:w-10 md:h-10" src="./image/MainIcon.png" alt="">
                <p class="text-[#169976] font-bold text-lg md:text-xl">PetTrack</p>
            </div>

            <div class="flex items-center gap-3 md:gap-4">
                <button id="mobile-menu-button" class="md:hidden text-gray-700 focus:outline-none">
                    <i class="fas fa-bars text-lg"></i>
                </button>
                <nav class="nav-links hidden md:flex items-center gap-4 md:gap-6">
                    <a class="text-gray-700 font-medium hover:text-[#169976] transition-colors duration-200" href="#">
                        Home
                    </a>
                    <a class="text-gray-700 font-medium hover:text-[#169976] transition-colors duration-200" href="#about">
                        About
                    </a>
                    <a class="text-gray-700 font-medium hover:text-[#169976] transition-colors duration-200" href="#services">
                        Services
                    </a>
                    <a class="text-gray-700 font-medium hover:text-[#169976] transition-colors duration-200" href="#contacts">
                        Contact
                    </a>
                </nav>

                <button onclick="openModal()" class="bg-[#169976] text-white font-semibold py-2 px-3 md:px-4 rounded-lg shadow transition duration-300 transform hover:bg-[#18b98e] hover:scale-105 hover:shadow-lg text-sm md:text-base">
                    Appointment
                </button>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="relative min-h-screen w-full px-4 md:px-20 py-16 hero-section flex items-center justify-between bg-cover bg-center" style="background-image: url('./image/HeroBanner.png');">
        <div class="hero-overlay"></div>
        <div class="max-w-2xl space-y-4 md:space-y-6 hero-content">
            <h1 class="text-2xl md:text-4xl lg:text-5xl font-bold text-white leading-tight">Premium Veterinary Care for Your Beloved Pets</h1>
            <p class="text-base md:text-xl text-white leading-relaxed opacity-90">
                Comprehensive healthcare solutions with cutting-edge technology and compassionate expertise.
            </p>
            <div class="flex flex-col sm:flex-row gap-3 md:gap-4 mt-6 md:mt-8">
                <button onclick="openModal()" class="bg-white text-[#169976] font-semibold py-2 md:py-3 px-6 md:px-8 rounded-lg shadow-lg hover:bg-gray-100 transition-colors duration-300 text-base md:text-lg">
                    Schedule Appointment
                </button>
                <a href="#services" class="bg-transparent border-2 border-white text-white font-semibold py-2 md:py-3 px-6 md:px-8 rounded-lg hover:bg-white hover:text-[#169976] transition-colors duration-300 text-base md:text-lg text-center">
                    Our Services
                </a>
            </div>
        </div>
        <div class="hero-image hidden md:block hero-content">
            <img src="./image/dog-cat.png" alt="Cat and Dog" class="w-full md:max-w-2xl" />
        </div>
    </main>

    <!-- Stats Section -->
    <section class="py-8 md:py-12 gradient-bg text-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                <div class="text-center">
                    <p class="text-xl md:text-3xl font-bold mb-1 md:mb-2">5,000+</p>
                    <p class="text-xs md:text-sm">Happy Pets</p>
                </div>
                <div class="text-center">
                    <p class="text-xl md:text-3xl font-bold mb-1 md:mb-2">98%</p>
                    <p class="text-xs md:text-sm">Satisfaction Rate</p>
                </div>
                <div class="text-center">
                    <p class="text-xl md:text-3xl font-bold mb-1 md:mb-2">24/7</p>
                    <p class="text-xs md:text-sm">Emergency Care</p>
                </div>
                <div class="text-center">
                    <p class="text-xl md:text-3xl font-bold mb-1 md:mb-2">50+</p>
                    <p class="text-xs md:text-sm">Veterinary Experts</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-12 md:py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12 md:mb-16">
                <h2 class="section-title text-2xl md:text-3xl lg:text-4xl font-bold text-gray-800">Our Premium Services</h2>
                <p class="text-base md:text-xl text-gray-600 max-w-3xl mx-auto mt-4 md:mt-6">Comprehensive veterinary care designed to keep your pets healthy and happy throughout their lives</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                <div class="professional-card p-4 md:p-6 text-center">
                    <div class="w-12 h-12 md:w-16 md:h-16 bg-[#169976] rounded-full flex items-center justify-center mx-auto mb-3 md:mb-4">
                        <i class="fas fa-stethoscope text-white text-lg md:text-2xl"></i>
                    </div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-2 md:mb-3">Comprehensive Check-ups</h3>
                    <p class="text-gray-600 text-xs md:text-sm leading-relaxed">
                        Thorough health examinations to detect issues early and ensure your pet's ongoing wellness.
                    </p>
                </div>
                <div class="professional-card p-4 md:p-6 text-center">
                    <div class="w-12 h-12 md:w-16 md:h-16 bg-[#169976] rounded-full flex items-center justify-center mx-auto mb-3 md:mb-4">
                        <i class="fas fa-syringe text-white text-lg md:text-2xl"></i>
                    </div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-2 md:mb-3">Vaccination Programs</h3>
                    <p class="text-gray-600 text-xs md:text-sm leading-relaxed">
                        Customized vaccination schedules to protect your pets from preventable diseases.
                    </p>
                </div>
                <div class="professional-card p-4 md:p-6 text-center">
                    <div class="w-12 h-12 md:w-16 md:h-16 bg-[#169976] rounded-full flex items-center justify-center mx-auto mb-3 md:mb-4">
                        <i class="fas fa-user-md text-white text-lg md:text-2xl"></i>
                    </div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-2 md:mb-3">Advanced Surgery</h3>
                    <p class="text-gray-600 text-xs md:text-sm leading-relaxed">
                        State-of-the-art surgical procedures performed by our experienced veterinary surgeons.
                    </p>
                </div>
                <div class="professional-card p-4 md:p-6 text-center">
                    <div class="w-12 h-12 md:w-16 md:h-16 bg-[#169976] rounded-full flex items-center justify-center mx-auto mb-3 md:mb-4">
                        <i class="fas fa-tooth text-white text-lg md:text-2xl"></i>
                    </div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-2 md:mb-3">Dental Care</h3>
                    <p class="text-gray-600 text-xs md:text-sm leading-relaxed">
                        Complete oral health services including cleaning, extractions, and preventative care.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-12 md:py-20">
        <div class="container mx-auto px-4">
            <!-- Header -->
            <div class="text-center mb-12 md:mb-16">
                <h2 class="section-title text-2xl md:text-3xl lg:text-4xl font-bold text-gray-800">About PetTrack</h2>
                <p class="text-base md:text-xl text-gray-600 max-w-3xl mx-auto mt-4 md:mt-6">Your trusted partner in comprehensive pet health management</p>
            </div>

            <!-- Our Story -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 items-center mb-12 md:mb-16">
                <div class="space-y-3 md:space-y-4">
                    <h3 class="text-xl md:text-2xl font-bold text-gray-800">Our Story</h3>
                    <p class="text-gray-600 text-sm md:text-base leading-relaxed">Founded in 2018, PetTrack began with a simple mission: to make pet care management as seamless and stress-free as possible for pet owners. What started as a small team of pet lovers and tech enthusiasts has grown into a comprehensive platform trusted by thousands of pet owners nationwide.</p>
                    <p class="text-gray-600 text-sm md:text-base leading-relaxed">We understand that pets are family, and their health and happiness are paramount. That's why we've dedicated ourselves to creating a system that organizes all aspects of pet care in one convenient place.</p>
                </div>
                <div class="story-image">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='200' viewBox='0 0 300 200'%3E%3Crect fill='%23ffffff' width='300' height='200' rx='15'/%3E%3Cpath fill='%23169976' d='M150,50 Q200,30 250,50 T300,70 L300,180 L0,180 L0,70 Q50,30 100,50 T150,50 Z'/%3E%3Ccircle fill='%23169976' cx='80' cy='40' r='15'/%3E%3Ccircle fill='%23169976' cx='220' cy='40' r='15'/%3E%3C/svg%3E" alt="Happy pets" class="w-full rounded-xl" />
                </div>
            </div>

            <!-- Mission & Vision -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8 mb-12 md:mb-16">
                <div class="mission-vision-card p-6 md:p-8 rounded-xl text-center">
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-[#1DCD9F] rounded-full flex items-center justify-center mx-auto mb-3 md:mb-4">
                        <i class="fas fa-bullseye text-white text-lg md:text-xl"></i>
                    </div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-2 md:mb-3">Our Mission</h3>
                    <p class="text-gray-600 text-sm md:text-base leading-relaxed">To provide pet owners with an intuitive, comprehensive platform that simplifies pet care management, enhances the well-being of pets, and strengthens the bond between pets and their families.</p>
                </div>
                <div class="mission-vision-card p-6 md:p-8 rounded-xl text-center">
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-[#1DCD9F] rounded-full flex items-center justify-center mx-auto mb-3 md:mb-4">
                        <i class="fas fa-eye text-white text-lg md:text-xl"></i>
                    </div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-2 md:mb-3">Our Vision</h3>
                    <p class="text-gray-600 text-sm md:text-base leading-relaxed">To become the world's most trusted pet care management platform, recognized for innovation, reliability, and dedication to improving the lives of pets and their owners.</p>
                </div>
            </div>

            <!-- Why Choose Us -->
            <div class="mb-12 md:mb-16">
                <h3 class="text-xl md:text-2xl font-bold text-gray-800 mb-6 md:mb-8 text-center">Why Choose PetTrack?</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
                    <div class="why-choose-card text-center">
                        <div class="w-12 h-12 md:w-14 md:h-14 bg-[#1DCD9F] rounded-full flex items-center justify-center mx-auto mb-3 md:mb-4">
                            <i class="fas fa-shield-alt text-white text-lg md:text-xl"></i>
                        </div>
                        <h4 class="text-base md:text-lg font-semibold text-gray-800 mb-1 md:mb-2">All-in-One Platform</h4>
                        <p class="text-gray-600 text-xs md:text-sm leading-relaxed">Manage appointments, medical records, and pet information in one secure place.</p>
                    </div>
                    <div class="why-choose-card text-center">
                        <div class="w-12 h-12 md:w-14 md:h-14 bg-[#1DCD9F] rounded-full flex items-center justify-center mx-auto mb-3 md:mb-4">
                            <i class="fas fa-clock text-white text-lg md:text-xl"></i>
                        </div>
                        <h4 class="text-base md:text-lg font-semibold text-gray-800 mb-1 md:mb-2">Time-Saving</h4>
                        <p class="text-gray-600 text-xs md:text-sm leading-relaxed">Automate reminders and streamline your pet care routine with our intuitive system.</p>
                    </div>
                    <div class="why-choose-card text-center">
                        <div class="w-12 h-12 md:w-14 md:h-14 bg-[#1DCD9F] rounded-full flex items-center justify-center mx-auto mb-3 md:mb-4">
                            <i class="fas fa-heart text-white text-lg md:text-xl"></i>
                        </div>
                        <h4 class="text-base md:text-lg font-semibold text-gray-800 mb-1 md:mb-2">Pet-Loved</h4>
                        <p class="text-gray-600 text-xs md:text-sm leading-relaxed">Designed by pet owners for pet owners, with your furry family members' needs in mind.</p>
                    </div>
                </div>
            </div>

            <!-- Team -->
            <div>
                <h3 class="text-xl md:text-2xl font-bold text-gray-800 mb-6 md:mb-8 text-center">Our Team</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
                    <div class="team-card text-center">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120' viewBox='0 0 120 120'%3E%3Ccircle fill='%23169976' cx='60' cy='60' r='60'/%3E%3Ccircle fill='%23ffffff' cx='60' cy='45' r='20'/%3E%3Cpath fill='%23ffffff' d='M40,95 Q60,75 80,95 Z'/%3E%3C/svg%3E" alt="Team member" class="w-16 h-16 md:w-20 md:h-20 rounded-full mx-auto mb-3 md:mb-4" />
                        <h4 class="text-base md:text-lg font-semibold text-gray-800 mb-1 md:mb-2">Sarah Johnson</h4>
                        <p class="text-[#169976] font-medium mb-1 md:mb-2 text-xs md:text-sm">Founder & CEO</p>
                        <p class="text-gray-600 text-xs md:text-sm leading-relaxed">Pet lover with 15+ years of veterinary experience</p>
                    </div>
                    <div class="team-card text-center">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120' viewBox='0 0 120 120'%3E%3Ccircle fill='%23169976' cx='60' cy='60' r='60'/%3E%3Ccircle fill='%23ffffff' cx='60' cy='45' r='20'/%3E%3Cpath fill='%23ffffff' d='M40,95 Q60,75 80,95 Z'/%3E%3C/svg%3E" alt="Team member" class="w-16 h-16 md:w-20 md:h-20 rounded-full mx-auto mb-3 md:mb-4" />
                        <h4 class="text-base md:text-lg font-semibold text-gray-800 mb-1 md:mb-2">Michael Chen</h4>
                        <p class="text-[#169976] font-medium mb-1 md:mb-2 text-xs md:text-sm">Lead Developer</p>
                        <p class="text-gray-600 text-xs md:text-sm leading-relaxed">Tech enthusiast with a passion for pet wellness</p>
                    </div>
                    <div class="team-card text-center">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120' viewBox='0 0 120 120'%3E%3Ccircle fill='%23169976' cx='60' cy='60' r='60'/%3E%3Ccircle fill='%23ffffff' cx='60' cy='45' r='20'/%3E%3Cpath fill='%23ffffff' d='M40,95 Q60,75 80,95 Z'/%3E%3C/svg%3E" alt="Team member" class="w-16 h-16 md:w-20 md:h-20 rounded-full mx-auto mb-3 md:mb-4" />
                        <h4 class="text-base md:text-lg font-semibold text-gray-800 mb-1 md:mb-2">Emily Rodriguez</h4>
                        <p class="text-[#169976] font-medium mb-1 md:mb-2 text-xs md:text-sm">Veterinary Advisor</p>
                        <p class="text-gray-600 text-xs md:text-sm leading-relaxed">Licensed veterinarian with specializations in pet nutrition</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-12 md:py-16 gradient-bg text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-xl md:text-3xl lg:text-4xl font-bold mb-4 md:mb-6">Ready to Give Your Pet the Best Care?</h2>
            <p class="text-base md:text-xl max-w-2xl mx-auto mb-6 md:mb-8">Join thousands of pet owners who trust PetTrack with their furry family members' health and wellbeing.</p>
            <button onclick="openModal()" class="bg-white text-[#169976] font-semibold py-2 md:py-3 px-6 md:px-8 rounded-lg shadow-lg hover:bg-gray-100 transition-colors duration-300 text-base md:text-lg">
                Book Your Appointment Today
            </button>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contacts" class="bg-gray-800 py-8 md:py-12 text-white">
        <div class="container mx-auto px-4 md:px-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
                <div>
                    <div class="flex items-center gap-3 md:gap-4 mb-3 md:mb-4">
                        <div class="w-8 h-8 md:w-10 md:h-10 bg-white rounded-full flex items-center justify-center">
                            <span class="text-[#169976] font-bold text-sm md:text-lg">🐾</span>
                        </div>
                        <p class="text-white font-bold text-lg md:text-xl">PetTrack</p>
                    </div>
                    <p class="mb-3 md:mb-4 text-gray-300 text-sm md:text-base">Your trusted partner in pet health management</p>
                    <div class="flex flex-col gap-1 md:gap-2">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-phone-alt text-xs md:text-sm"></i>
                            <span class="text-sm md:text-base">(123) 456-7890</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-envelope text-xs md:text-sm"></i>
                            <span class="text-sm md:text-base">info@pettrack.com</span>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">Our Location</h3>
                    <div class="flex items-start gap-2 mb-3 md:mb-4">
                        <i class="fas fa-map-marker-alt mt-1 text-xs md:text-sm"></i>
                        <p class="text-gray-300 text-sm md:text-base">PQXJ+Q9J, Butuan - Cagayan de Oro - Iligan Rd, Balingasag, Misamis Oriental</p>
                    </div>
                    <div class="flex gap-3 md:gap-4">
                        <a href="#" class="text-white hover:text-gray-300 transition-colors">
                            <i class="fab fa-facebook-f text-base md:text-lg"></i>
                        </a>
                        <a href="#" class="text-white hover:text-gray-300 transition-colors">
                            <i class="fab fa-instagram text-base md:text-lg"></i>
                        </a>
                        <a href="#" class="text-white hover:text-gray-300 transition-colors">
                            <i class="fab fa-twitter text-base md:text-lg"></i>
                        </a>
                    </div>
                </div>
                <div>
                    <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">Find Us</h3>
                    <div class="footer-map">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d1262.921952432691!2d124.78045648758747!3d8.74964320210998!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x32ffe1fbecdd99ad%3A0x73cf6beb3b523f24!2sBalingasag%20Dog%20And%20Cat%20Clinic!5e1!3m2!1sen!2sph!4v1755610325757!5m2!1sen!2sph" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-6 md:mt-8 pt-4 md:pt-6 text-center text-xs md:text-sm text-gray-400">
                <p>© 2023 PetTrack. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Appointment Modal -->
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
        <?php if (isset($_SESSION['success'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '<?php echo $_SESSION['success']; ?>',
                confirmButtonColor: '#169976'
            });
            <?php unset($_SESSION['success']); ?>
        <?php elseif (isset($_SESSION['error'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo $_SESSION['error']; ?>',
                confirmButtonColor: '#169976'
            });
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

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

        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const navLinks = document.querySelector('.nav-links');
        mobileMenuButton.addEventListener('click', () => {
            navLinks.classList.toggle('mobile-open');
        });
    </script>

    <script src="./js/landing-page.js"></script>
</body>

</html>