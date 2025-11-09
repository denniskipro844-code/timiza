<?php
$page_title = "About Us";
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-primary to-secondary py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <h1 class="text-4xl md:text-5xl font-bold mb-6" data-aos="fade-up">
            About Timiza Youth Initiative
        </h1>
        <p class="text-xl max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="200">
            Empowering young people to become agents of positive change in their communities
        </p>
    </div>
</section>

<!-- Mission, Vision, Values -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center" data-aos="fade-up" data-aos-delay="100">
                <div class="bg-primary text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-bullseye text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-dark mb-4">Our Mission</h3>
                <p class="text-gray-600">
                    To empower youth in Kilifi County through comprehensive programs that promote health, 
                    education, gender equality, environmental sustainability, and peace-building.
                </p>
            </div>
            
            <div class="text-center" data-aos="fade-up" data-aos-delay="200">
                <div class="bg-secondary text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-eye text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-dark mb-4">Our Vision</h3>
                <p class="text-gray-600">
                    A thriving community where young people are empowered to lead sustainable development 
                    initiatives and create lasting positive change.
                </p>
            </div>
            
            <div class="text-center" data-aos="fade-up" data-aos-delay="300">
                <div class="bg-accent text-dark w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-heart text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-dark mb-4">Our Values</h3>
                <p class="text-gray-600">
                    Integrity, inclusivity, sustainability, innovation, and community-centered approaches 
                    guide everything we do.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Our Story -->
<section class="py-16 bg-neutral">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div data-aos="fade-right">
                <h2 class="text-3xl md:text-4xl font-bold text-dark mb-6">Our Story</h2>
                <p class="text-gray-600 mb-6">
                    Timiza Youth Initiative is a Community Based Organization (CBO) formed in 2021 by a group of young volunteers. The Organization is driven by a human-centered design for solutions to problems faced by vulnerable and marginalized groups in Kilifi County. Starting with 
                    small community projects, we have grown into a recognized organization making 
                    significant impact across multiple sectors.
                </p>
                <p class="text-gray-600 mb-6">
                    Our name "Timiza" comes from the Swahili word meaning "to fulfill" or "to accomplish," 
                    reflecting our commitment to helping young people fulfill their potential and accomplish 
                    their dreams while serving their communities.
                </p>
                <p class="text-gray-600">
                    Today, we work with over 1,200 young people across 15 communities, implementing 
                    programs that address the most pressing challenges facing our region.
                </p>
            </div>
            <div data-aos="fade-left">
                <br>
                <img src="<?php echo site_url_path('assets/images/climatechange.webp'); ?>" alt="Our Story" class="rounded-lg shadow-lg w-full">
                </br>
            <br>
                <img src="<?php echo site_url_path('assets/images/disabilityinclusion.webp'); ?>" alt="Our Story" class="rounded-lg shadow-lg w-full">
            </br>
                 <img src="<?php echo site_url_path('assets/images/genderpeace.webp'); ?>" alt="Our Story" class="rounded-lg shadow-lg w-full">
                
            </div>
        </div>
    </div>
</section>

<!-- Timeline -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-dark mb-4" data-aos="fade-up">
                Our Journey
            </h2>
            <p class="text-gray-600 text-lg" data-aos="fade-up" data-aos-delay="200">
                Key milestones in our growth and impact
            </p>
        </div>
        
        <div class="relative">
            <!-- Timeline line -->
            <div class="absolute left-1/2 transform -translate-x-px h-full w-0.5 bg-primary"></div>
            
            <!-- Timeline items -->
            <div class="space-y-12">
                <div class="relative flex items-center" data-aos="fade-right">
                    <div class="flex-1 text-right pr-8">
                        <h3 class="text-xl font-semibold text-dark">Foundation</h3>
                        <p class="text-gray-600">Timiza Youth Initiative was officially founded by 5 young visionaries</p>
                    </div>
                    <div class="relative flex items-center justify-center w-12 h-12 bg-primary rounded-full">
                        <span class="text-white font-bold">2021</span>
                    </div>
                    <div class="flex-1 pl-8"></div>
                </div>
                
                <div class="relative flex items-center" data-aos="fade-left">
                    <div class="flex-1 pr-8"></div>
                    <div class="relative flex items-center justify-center w-12 h-12 bg-secondary rounded-full">
                        <span class="text-white font-bold">2022</span>
                    </div>
                    <div class="flex-1 text-left pl-8">
                        <h3 class="text-xl font-semibold text-dark">First Programs</h3>
                        <p class="text-gray-600">Launched health education and climate action initiatives</p>
                    </div>
                </div>
                
                <div class="relative flex items-center" data-aos="fade-right">
                    <div class="flex-1 text-right pr-8">
                        <h3 class="text-xl font-semibold text-dark">Community Recognition</h3>
                        <p class="text-gray-600">Received official recognition from Kilifi County Government</p>
                    </div>
                    <div class="relative flex items-center justify-center w-12 h-12 bg-accent rounded-full">
                        <span class="text-dark font-bold">2023</span>
                    </div>
                    <div class="flex-1 pl-8"></div>
                </div>
                
                <div class="relative flex items-center" data-aos="fade-left">
                    <div class="flex-1 pr-8"></div>
                    <div class="relative flex items-center justify-center w-12 h-12 bg-teal rounded-full">
                        <span class="text-white font-bold">2024</span>
                    </div>
                    <div class="flex-1 text-left pl-8">
                        <h3 class="text-xl font-semibold text-dark">Expansion</h3>
                        <p class="text-gray-600">Expanded to serve 15 communities with 1,200+ youth participants</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-16 bg-neutral">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-dark mb-4" data-aos="fade-up">
                Our Team
            </h2>
            <p class="text-gray-600 text-lg" data-aos="fade-up" data-aos-delay="200">
                Meet the passionate individuals driving our mission forward
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Team members would be dynamically loaded from database -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="100">
                <img src="<?php echo site_url_path('assets/images/brian.jpg'); ?>" alt="Team Member" class="w-full h-64 object-cover">
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-dark mb-2">Brian Mundia</h3>
                    <p class="text-primary font-medium mb-3">Executive Director</p>
                    <p class="text-gray-600 text-sm">
                        Passionate about youth empowerment and community development with 8+ years of experience.
                    </p>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="200">
                <img src="<?php echo site_url_path('assets/images/Screenshot 2025-10-28 120727_edited.png'); ?>" alt="Team Member" class="w-full h-64 object-cover">
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-dark mb-2">Violet Maangi</h3>
                    <p class="text-primary font-medium mb-3">Finance Manager</p>
                    <p class="text-gray-600 text-sm">
                        Coordinates all program activities and ensures quality implementation across communities.
                    </p>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="300">
                <img src="<?php echo site_url_path('assets/images/Screenshot 2025-10-28 124506_edited.png'); ?>" alt="Team Member" class="w-full h-64 object-cover">
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-dark mb-2">Joel Baariu</h3>
                    <p class="text-primary font-medium mb-3">Human Resource</p>
                    <p class="text-gray-600 text-sm">
                        Builds strong relationships with communities and ensures inclusive participation.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Partners Section -->
<section class="py-20 bg-gradient-to-b from-gray-50 to-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Title -->
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-extrabold text-gray-800 tracking-tight mb-4" data-aos="fade-up">
                Our Partners
            </h2>
            <p class="text-gray-600 text-lg md:text-xl" data-aos="fade-up" data-aos-delay="200">
                Working together to create lasting impact
            </p>
            <div class="mt-4 w-24 h-1 bg-teal-500 mx-auto rounded-full"></div>
        </div>

        <!-- Partner Logos (Marquee Effect) -->
        <div class="relative overflow-hidden">
            <div class="flex items-center gap-12 animate-marquee">
                <div class="p-4 transition-all duration-300">
                    <img src="<?php echo site_url_path('assets/images/GMRC.webp'); ?>" 
                         alt="Partner 1" 
                         class="h-20 md:h-24 mx-auto object-contain transition duration-300 transform hover:scale-105">
                </div>

                <div class="p-4 transition-all duration-300">
                    <img src="<?php echo site_url_path('assets/images/KESCO-P1.webp'); ?>" 
                         alt="Partner 2" 
                         class="h-20 md:h-24 mx-auto object-contain transition duration-300 transform hover:scale-105">
                </div>

                <div class="p-4 transition-all duration-300">
                    <img src="<?php echo site_url_path('assets/images/KILIFI-GOVT.webp'); ?>" 
                         alt="Partner 3" 
                         class="h-20 md:h-24 mx-auto object-contain transition duration-300 transform hover:scale-105">
                </div>

                <div class="p-4 transition-all duration-300">
                    <img src="<?php echo site_url_path('assets/images/LCVT-P3.webp'); ?>" 
                         alt="Partner 4" 
                         class="h-20 md:h-24 mx-auto object-contain transition duration-300 transform hover:scale-105">
                </div>

                <!-- Additional Partners -->
                <div class="p-4 transition-all duration-300">
                    <img src="<?php echo site_url_path('assets/images/TICAH-P2.webp'); ?>" 
                         alt="Partner 5" 
                         class="h-20 md:h-24 mx-auto object-contain transition duration-300 transform hover:scale-105">
                </div>

                <div class="p-4 transition-all duration-300">
                    <img src="<?php echo site_url_path('assets/images/ajira.png'); ?>" 
                         alt="Partner 6" 
                         class="h-20 md:h-24 mx-auto object-contain transition duration-300 transform hover:scale-105">
                </div>

                <div class="p-4 transition-all duration-300">
                    <img src="<?php echo site_url_path('assets/images/shofco.png'); ?>" 
                         alt="Partner 7" 
                         class="h-20 md:h-24 mx-auto object-contain transition duration-300 transform hover:scale-105">
                </div>

                <div class="p-4 transition-all duration-300">
                    <img src="<?php echo site_url_path('assets/images/partner-8.webp'); ?>" 
                         alt="Partner 8" 
                         class="h-20 md:h-24 mx-auto object-contain transition duration-300 transform hover:scale-105">
                </div>

                <div class="p-4 transition-all duration-300">
                    <img src="<?php echo site_url_path('assets/images/partner-9.webp'); ?>" 
                         alt="Partner 9" 
                         class="h-20 md:h-24 mx-auto object-contain transition duration-300 transform hover:scale-105">
                </div>

                <div class="p-4 transition-all duration-300">
                    <img src="<?php echo site_url_path('assets/images/partner-10.webp'); ?>" 
                         alt="Partner 10" 
                         class="h-20 md:h-24 mx-auto object-contain transition duration-300 transform hover:scale-105">
                </div>
            </div>
        </div>
    </div>

    <!-- Marquee Animation -->
    <style>
        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        .animate-marquee {
            display: flex;
            width: max-content;
            animation: marquee 30s linear infinite;
        }

        /* Pause marquee on hover */
        .animate-marquee:hover {
            animation-play-state: paused;
        }
    </style>
</section>



<?php include 'includes/footer.php'; ?>