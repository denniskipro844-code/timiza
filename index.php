<?php
$page_title = "Home";
include 'includes/header.php';

// Get data for homepage
$stats = getStats();
$featured_programs = getPrograms();
$latest_news = getNews(3, true);
?>

<!-- Hero Section -->
<section class="relative min-h-screen flex items-center bg-cover bg-center bg-no-repeat" style="background-image: url('<?php echo site_url_path('assets/images/Timiza-team.webp'); ?>');">
    <div class="absolute inset-0 bg-black bg-opacity-40"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <h1 class="text-4xl md:text-6xl font-bold mb-6" data-aos="fade-up">
            Empowering Youth,<br>
            <span class="text-accent">Transforming Communities</span>
        </h1>
        <p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="200">
            Building a brighter future for Kilifi County through youth empowerment, 
            sustainable development, and community engagement.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center" data-aos="fade-up" data-aos-delay="400">
            <a href="<?php echo site_url_path('get-involved'); ?>" class="bg-accent text-dark px-8 py-3 rounded-full font-semibold hover:bg-opacity-90 transition-all transform hover:scale-105">
                Join Our Mission
            </a>
            <a href="<?php echo site_url_path('about'); ?>" class="border-2 border-white text-white px-8 py-3 rounded-full font-semibold hover:bg-white hover:text-primary transition-all">
                Learn More
            </a>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div data-aos="fade-up" data-aos-delay="100">
                <div class="text-4xl font-bold text-primary mb-2" data-counter="<?php echo $stats['youth_reached']; ?>">0</div>
                <div class="text-gray-600">Youth Reached</div>
            </div>
            <div data-aos="fade-up" data-aos-delay="200">
                <div class="text-4xl font-bold text-primary mb-2" data-counter="<?php echo $stats['communities']; ?>">0</div>
                <div class="text-gray-600">Communities</div>
            </div>
            <div data-aos="fade-up" data-aos-delay="300">
                <div class="text-4xl font-bold text-primary mb-2" data-counter="<?php echo $stats['programs']; ?>">0</div>
                <div class="text-gray-600">Active Programs</div>
            </div>
            <div data-aos="fade-up" data-aos-delay="400">
                <div class="text-4xl font-bold text-primary mb-2" data-counter="<?php echo $stats['volunteers']; ?>">0</div>
                <div class="text-gray-600">Volunteers</div>
            </div>
        </div>
    </div>
</section>

<!-- About Preview -->
<section class="py-16 bg-neutral">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div data-aos="fade-right">
                <h2 class="text-3xl md:text-4xl font-bold text-dark mb-6">
                    Who We Are
                </h2>
                <p class="text-gray-600 mb-6 text-lg">
                    Timiza Youth Initiative is a Community Based Organization (CBO) formed in 2021 by a group of young volunteers. The Organization is driven by a human-centered design for solutions to problems faced by vulnerable and marginalized groups in Kilifi County. We are at the forefront to ensure children, young women, and youths get empowered through our core thematic programs including: Health & Gender, climate change, peace & security, and disability inclusion.
                </p>
                <p class="text-gray-600 mb-8">
                    Through our comprehensive programs in health, education, climate action, and social inclusion, 
                    we're building a generation of leaders who will transform Kenya's future.
                </p>
                <a href="<?php echo site_url_path('about'); ?>" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-opacity-90 transition-all inline-flex items-center">
                    Learn More About Us
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
            <div data-aos="fade-left">
                <img src="<?php echo site_url_path('assets/images/Timiza-team.webp'); ?>" alt="Youth empowerment" class="rounded-lg shadow-lg w-full">
            </div>
        </div>
    </div>
</section>

<!-- Programs Preview -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-dark mb-4" data-aos="fade-up">
                Our Focus Areas
            </h2>
            <p class="text-gray-600 text-lg max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="200">
                We work across multiple sectors to create lasting impact in our communities
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php 
            $program_icons = [
                'Health & Gender' => 'fas fa-heartbeat',
                'Climate Change' => 'fas fa-leaf',
                'Peace & Security' => 'fas fa-dove',
                'Disability Inclusion' => 'fas fa-universal-access',
                'Education & Skills' => 'fas fa-graduation-cap'
            ];
            
            foreach ($featured_programs as $index => $program): 
                $icon = $program_icons[$program['name']] ?? 'fas fa-star';
            ?>
            <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                <div class="text-primary text-4xl mb-4">
                    <i class="<?php echo $icon; ?>"></i>
                </div>
                <h3 class="text-xl font-semibold text-dark mb-3"><?php echo htmlspecialchars($program['name']); ?></h3>
                <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($program['description']); ?></p>
                <a href="<?php echo site_url_path('programs'); ?>" class="text-primary hover:text-secondary font-medium inline-flex items-center">
                    Learn More
                    <i class="fas fa-arrow-right ml-1 text-sm"></i>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-12" data-aos="fade-up">
            <a href="<?php echo site_url_path('programs'); ?>" class="bg-primary text-white px-8 py-3 rounded-lg hover:bg-opacity-90 transition-all">
                View All Programs
            </a>
        </div>
    </div>
</section>

<!-- News Preview -->
<?php if (!empty($latest_news)): ?>
<section class="py-16 bg-neutral">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-dark mb-4" data-aos="fade-up">
                Latest News & Updates
            </h2>
            <p class="text-gray-600 text-lg" data-aos="fade-up" data-aos-delay="200">
                Stay updated with our latest activities and impact stories
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($latest_news as $index => $article): ?>
            <?php
                $imagePath = !empty($article['image'])
                    ? site_url_path($article['image'])
                    : site_url_path('assets/images/timiza-4.webp');
            ?>
            <article class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="w-full h-48 object-cover">
                <div class="p-6">
                    <div class="text-sm text-gray-500 mb-2"><?php echo formatDate($article['created_at']); ?></div>
                    <h3 class="text-xl font-semibold text-dark mb-3"><?php echo htmlspecialchars($article['title']); ?></h3>
                    <p class="text-gray-600 mb-4"><?php echo truncateText($article['excerpt']); ?></p>
                    <a href="<?php echo site_url_path('news/' . $article['slug']); ?>" class="text-primary hover:text-secondary font-medium inline-flex items-center">
                        Read More
                        <i class="fas fa-arrow-right ml-1 text-sm"></i>
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-12" data-aos="fade-up">
            <a href="<?php echo site_url_path('news'); ?>" class="bg-primary text-white px-8 py-3 rounded-lg hover:bg-opacity-90 transition-all">
                View All News
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Call to Action -->
<section class="py-16 bg-gradient-to-r from-primary to-secondary">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <h2 class="text-3xl md:text-4xl font-bold mb-6" data-aos="fade-up">
            Ready to Make a Difference?
        </h2>
        <p class="text-xl mb-8 max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="200">
            Join us in empowering youth and transforming communities. 
            Your contribution can create lasting change.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center" data-aos="fade-up" data-aos-delay="400">
            <a href="<?php echo site_url_path('get-involved'); ?>" class="bg-accent text-dark px-8 py-3 rounded-full font-semibold hover:bg-opacity-90 transition-all transform hover:scale-105">
                Volunteer Today
            </a>
            <a href="<?php echo site_url_path('contact'); ?>" class="border-2 border-white text-white px-8 py-3 rounded-full font-semibold hover:bg-white hover:text-primary transition-all">
                Get in Touch
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>