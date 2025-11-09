<?php
$page_title = "Our Programs";
include 'includes/header.php';

$programs = getPrograms();
?>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-primary to-secondary py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <h1 class="text-4xl md:text-5xl font-bold mb-6" data-aos="fade-up">
            Our Programs
        </h1>
        <p class="text-xl max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="200">
            Comprehensive initiatives addressing the most pressing challenges in our communities
        </p>
    </div>
</section>

<!-- Programs Overview -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-dark mb-4" data-aos="fade-up">
                Focus Areas
            </h2>
            <p class="text-gray-600 text-lg max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="200">
                Our programs are designed to create holistic impact across multiple sectors, 
                ensuring sustainable development and youth empowerment.
            </p>
        </div>
    </div>
</section>

<!-- Health & Gender Program -->
<section class="py-16 bg-neutral">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div data-aos="fade-right">
                <div class="flex items-center mb-6">
                    <div class="bg-primary text-white w-16 h-16 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-heartbeat text-2xl"></i>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-bold text-dark">Health & Gender</h2>
                </div>
                <p class="text-gray-600 mb-6 text-lg">
                    Promoting sexual and reproductive health rights, gender equality, and addressing 
                    gender-based violence through education, advocacy, and community engagement.
                </p>
                <div class="space-y-4 mb-8">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-check-circle text-primary mt-1"></i>
                        <span class="text-gray-600">Comprehensive sexuality education workshops</span>
                    </div>
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-check-circle text-primary mt-1"></i>
                        <span class="text-gray-600">Gender equality advocacy campaigns</span>
                    </div>
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-check-circle text-primary mt-1"></i>
                        <span class="text-gray-600">Support groups for survivors of GBV</span>
                    </div>
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-check-circle text-primary mt-1"></i>
                        <span class="text-gray-600">Community health volunteer training</span>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <h4 class="font-semibold text-dark mb-2">Impact So Far:</h4>
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold text-primary">500+</div>
                            <div class="text-sm text-gray-600">Youth Educated</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-primary">25</div>
                            <div class="text-sm text-gray-600">Health Volunteers</div>
                        </div>
                    </div>
                </div>
            </div>
            <div data-aos="fade-left">
                <img src="<?php echo site_url_path('assets/images/health-program.jpg'); ?>" alt="Health & Gender Program" class="rounded-lg shadow-lg w-full">
            </div>
        </div>
    </div>
</section>

<!-- Climate Change Program -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div data-aos="fade-right" class="order-2 lg:order-1">
                <img src="<?php echo site_url_path('assets/images/climate-program.jpg'); ?>" alt="Climate Change Program" class="rounded-lg shadow-lg w-full">
            </div>
            <div data-aos="fade-left" class="order-1 lg:order-2">
                <div class="flex items-center mb-6">
                    <div class="bg-secondary text-white w-16 h-16 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-leaf text-2xl"></i>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-bold text-dark">Climate Action</h2>
                </div>
                <p class="text-gray-600 mb-6 text-lg">
                    Building climate resilience through environmental conservation, sustainable agriculture, 
                    and renewable energy initiatives led by young people.
                </p>
                <div class="space-y-4 mb-8">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-check-circle text-secondary mt-1"></i>
                        <span class="text-gray-600">Tree planting and forest restoration</span>
                    </div>
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-check-circle text-secondary mt-1"></i>
                        <span class="text-gray-600">Sustainable farming training</span>
                    </div>
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-check-circle text-secondary mt-1"></i>
                        <span class="text-gray-600">Clean energy projects</span>
                    </div>
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-check-circle text-secondary mt-1"></i>
                        <span class="text-gray-600">Climate advocacy campaigns</span>
                    </div>
                </div>
                <div class="bg-neutral p-6 rounded-lg shadow-lg">
                    <h4 class="font-semibold text-dark mb-2">Environmental Impact:</h4>
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold text-secondary">5,000+</div>
                            <div class="text-sm text-gray-600">Trees Planted</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-secondary">200</div>
                            <div class="text-sm text-gray-600">Farmers Trained</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Peace & Security Program -->
<section class="py-16 bg-neutral">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div data-aos="fade-right">
                <div class="flex items-center mb-6">
                    <div class="bg-accent text-dark w-16 h-16 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-dove text-2xl"></i>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-bold text-dark">Peace & Security</h2>
                </div>
                <p class="text-gray-600 mb-6 text-lg">
                    Promoting peaceful coexistence, conflict resolution, and community security 
                    through dialogue, mediation training, and peace-building initiatives.
                </p>
                <div class="space-y-4 mb-8">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-check-circle text-teal mt-1"></i>
                        <span class="text-gray-600">Conflict resolution training</span>
                    </div>
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-check-circle text-teal mt-1"></i>
                        <span class="text-gray-600">Community dialogue forums</span>
                    </div>
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-check-circle text-teal mt-1"></i>
                        <span class="text-gray-600">Peace ambassadors program</span>
                    </div>
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-check-circle text-teal mt-1"></i>
                        <span class="text-gray-600">Inter-community exchange programs</span>
                    </div>
                </div>
            </div>
            <div data-aos="fade-left">
                <img src="<?php echo site_url_path('assets/images/peace-program.jpg'); ?>" alt="Peace & Security Program" class="rounded-lg shadow-lg w-full">
            </div>
        </div>
    </div>
</section>

<!-- Other Programs -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Disability Inclusion -->
            <div class="bg-neutral p-8 rounded-lg shadow-lg" data-aos="fade-up" data-aos-delay="100">
                <div class="flex items-center mb-6">
                    <div class="bg-primary text-white w-12 h-12 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-universal-access text-xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-dark">Disability Inclusion</h3>
                </div>
                <p class="text-gray-600 mb-4">
                    Ensuring equal opportunities and full participation of persons with disabilities 
                    in all aspects of community life.
                </p>
                <ul class="space-y-2 text-gray-600">
                    <li class="flex items-center"><i class="fas fa-check text-primary mr-2"></i> Accessibility audits</li>
                    <li class="flex items-center"><i class="fas fa-check text-primary mr-2"></i> Inclusive education advocacy</li>
                    <li class="flex items-center"><i class="fas fa-check text-primary mr-2"></i> Skills training programs</li>
                </ul>
            </div>
            
            <!-- Education & Skills -->
            <div class="bg-neutral p-8 rounded-lg shadow-lg" data-aos="fade-up" data-aos-delay="200">
                <div class="flex items-center mb-6">
                    <div class="bg-secondary text-white w-12 h-12 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-graduation-cap text-xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-dark">Education & Skills</h3>
                </div>
                <p class="text-gray-600 mb-4">
                    Enhancing educational outcomes and building practical skills for economic empowerment 
                    and sustainable livelihoods.
                </p>
                <ul class="space-y-2 text-gray-600">
                    <li class="flex items-center"><i class="fas fa-check text-secondary mr-2"></i> Digital literacy training</li>
                    <li class="flex items-center"><i class="fas fa-check text-secondary mr-2"></i> Vocational skills development</li>
                    <li class="flex items-center"><i class="fas fa-check text-secondary mr-2"></i> Entrepreneurship mentorship</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-16 bg-gradient-to-r from-primary to-secondary">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <h2 class="text-3xl md:text-4xl font-bold mb-6" data-aos="fade-up">
            Join Our Programs
        </h2>
        <p class="text-xl mb-8 max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="200">
            Whether as a participant, volunteer, or partner, there are many ways to get involved 
            in our transformative programs.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center" data-aos="fade-up" data-aos-delay="400">
            <a href="<?php echo site_url_path('get-involved'); ?>" class="bg-accent text-dark px-8 py-3 rounded-full font-semibold hover:bg-opacity-90 transition-all transform hover:scale-105">
                Get Involved
            </a>
            <a href="<?php echo site_url_path('contact'); ?>" class="border-2 border-white text-white px-8 py-3 rounded-full font-semibold hover:bg-white hover:text-primary transition-all">
                Learn More
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>