<?php
$page_title = "Programs";
include 'includes/header.php';

$programs = getPrograms();
$stats = getStats();

$programIcons = [
    'Health & Gender' => 'fas fa-heartbeat',
    'Climate Action' => 'fas fa-leaf',
    'Climate Change' => 'fas fa-leaf',
    'Peace & Security' => 'fas fa-dove',
    'Disability Inclusion' => 'fas fa-universal-access',
    'Education & Skills' => 'fas fa-graduation-cap',
    'Economic Empowerment' => 'fas fa-hand-holding-usd',
    'Leadership & Governance' => 'fas fa-users-cog',
];
?>

<!-- Hero Section -->
<section class="relative bg-gradient-to-r from-primary to-secondary py-24 overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="w-full h-full bg-cover bg-center" style="background-image: url('<?php echo site_url_path('assets/images/programs-hero.jpg'); ?>');"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <span class="inline-flex items-center px-4 py-1 mb-6 text-sm font-medium bg-white/20 rounded-full backdrop-blur">
            <i class="fas fa-layer-group mr-2"></i>
            Transformative Youth Programs
        </span>
        <h1 class="text-4xl md:text-5xl font-bold mb-6" data-aos="fade-up">Empowering Youth, Transforming Communities</h1>
        <p class="text-lg md:text-xl max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="150">
            We design and deliver holistic programs that respond to the social, economic, and environmental challenges facing young people across Kilifi County and beyond.
        </p>
        <div class="mt-10 flex flex-col sm:flex-row justify-center gap-4" data-aos="fade-up" data-aos-delay="250">
            <a href="<?php echo site_url_path('get-involved'); ?>" class="bg-white text-primary font-semibold px-8 py-3 rounded-full shadow hover:bg-opacity-90 transition-all">
                Join Our Programs
            </a>
            <a href="<?php echo site_url_path('contact'); ?>" class="border border-white text-white font-semibold px-8 py-3 rounded-full hover:bg-white hover:text-primary transition-all">
                Partner With Us
            </a>
        </div>
    </div>
</section>

<!-- Impact Snapshot -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center" data-aos="fade-up">
            <div class="bg-neutral rounded-lg p-8 shadow-sm">
                <div class="text-primary text-4xl font-bold mb-3"><?php echo number_format($stats['youth_reached'] ?? 0); ?>+</div>
                <p class="text-gray-600">Youth Reached Through Our Programs</p>
            </div>
            <div class="bg-neutral rounded-lg p-8 shadow-sm">
                <div class="text-secondary text-4xl font-bold mb-3"><?php echo number_format($stats['communities'] ?? 0); ?></div>
                <p class="text-gray-600">Communities Actively Engaged</p>
            </div>
            <div class="bg-neutral rounded-lg p-8 shadow-sm">
                <div class="text-accent text-4xl font-bold mb-3"><?php echo number_format($stats['programs'] ?? count($programs)); ?></div>
                <p class="text-gray-600">Active Programs Driving Change</p>
            </div>
        </div>
    </div>
</section>

<!-- Focus Areas Intro -->
<section class="py-12 bg-neutral">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-dark mb-4" data-aos="fade-up">Our Focus Areas</h2>
        <p class="text-gray-600 text-lg" data-aos="fade-up" data-aos-delay="150">
            Each program is youth-led and context-specific, blending advocacy, capacity building, and community-driven solutions to deliver sustainable impact.
        </p>
    </div>
</section>

<!-- Programs Grid -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if (!empty($programs)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($programs as $index => $program): ?>
            <?php
                $icon = $programIcons[$program['name']] ?? 'fas fa-star';
                $delay = ($index % 3) * 100;
            ?>
            <article class="bg-neutral rounded-2xl shadow-lg overflow-hidden border border-gray-100" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="w-12 h-12 rounded-full bg-white flex items-center justify-center shadow-inner text-primary text-xl">
                                <i class="<?php echo $icon; ?>"></i>
                            </span>
                            <h3 class="text-xl font-bold text-dark"><?php echo htmlspecialchars($program['name']); ?></h3>
                        </div>
                        <span class="text-sm text-gray-500">Active</span>
                    </div>
                    <p class="text-gray-600 leading-relaxed mb-6">
                        <?php echo htmlspecialchars(truncateText($program['description'] ?? 'Program description coming soon.', 160)); ?>
                    </p>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span><i class="fas fa-map-marker-alt mr-2 text-primary"></i><?php echo htmlspecialchars($program['location'] ?? 'Kilifi County'); ?></span>
                        <span><i class="fas fa-calendar-check mr-2 text-primary"></i><?php echo htmlspecialchars($program['status'] ?? 'Ongoing'); ?></span>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="bg-neutral rounded-2xl p-12 text-center shadow-lg" data-aos="fade-up">
            <i class="fas fa-layer-group text-5xl text-gray-300 mb-4"></i>
            <h3 class="text-2xl font-semibold text-dark mb-2">Programs Coming Soon</h3>
            <p class="text-gray-600 max-w-2xl mx-auto">
                We are finalizing new initiatives to better support young people in our communities. Check back soon or subscribe to our newsletter for updates.
            </p>
            <a href="<?php echo site_url_path('get-involved'); ?>" class="inline-flex items-center mt-6 px-6 py-3 bg-primary text-white rounded-full hover:bg-opacity-90 transition-all">
                Stay Informed
                <i class="fas fa-arrow-right ml-2 text-sm"></i>
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Signature Initiatives -->
<section class="py-16 bg-neutral">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div data-aos="fade-right">
                <h2 class="text-3xl md:text-4xl font-bold text-dark mb-4">Signature Initiatives</h2>
                <p class="text-gray-600 mb-6 text-lg">
                    Beyond our core programs, we run flagship initiatives that deepen impact in priority areas such as climate resilience, gender equality, and skills development.
                </p>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex items-start gap-3">
                        <i class="fas fa-check-circle text-primary mt-1"></i>
                        <div>
                            <span class="font-semibold text-dark">Kilifi Climate Champions</span>
                            <p>Equipping youth to lead local climate adaptation and environmental stewardship projects.</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fas fa-check-circle text-primary mt-1"></i>
                        <div>
                            <span class="font-semibold text-dark">She Leads Accelerator</span>
                            <p>Mentoring and resourcing young women driving change in governance, entrepreneurship, and social justice.</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fas fa-check-circle text-primary mt-1"></i>
                        <div>
                            <span class="font-semibold text-dark">Digital Skills for All</span>
                            <p>Delivering digital literacy, innovation labs, and internship pathways for marginalized youth.</p>
                        </div>
                    </li>
                </ul>
            </div>
            <div data-aos="fade-left" class="grid grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-2xl shadow-lg text-center border border-gray-100">
                    <div class="text-primary text-3xl mb-3"><i class="fas fa-seedling"></i></div>
                    <h3 class="font-semibold text-dark mb-2">Climate Resilience</h3>
                    <p class="text-sm text-gray-600">Greening schools, restoring ecosystems, and promoting nature-based solutions.</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-lg text-center border border-gray-100">
                    <div class="text-secondary text-3xl mb-3"><i class="fas fa-hand-holding-heart"></i></div>
                    <h3 class="font-semibold text-dark mb-2">Community Safety</h3>
                    <p class="text-sm text-gray-600">Facilitating peace dialogues, conflict resolution, and youth mentorship networks.</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-lg text-center border border-gray-100">
                    <div class="text-accent text-3xl mb-3"><i class="fas fa-users"></i></div>
                    <h3 class="font-semibold text-dark mb-2">Inclusive Growth</h3>
                    <p class="text-sm text-gray-600">Supporting disability inclusion, education access, and equitable opportunity.</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-lg text-center border border-gray-100">
                    <div class="text-teal text-3xl mb-3"><i class="fas fа-lightbulb"></i></div>
                    <h3 class="font-semibold text-dark mb-2">Innovation Labs</h3>
                    <p class="text-sm text-gray-600">Incubating youth-led ideas through hackathons, bootcamps, and seed funding.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-20 bg-gradient-to-r from-primary to-secondary text-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center" data-aos="fade-up">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">Be Part of the Change</h2>
        <p class="text-lg md:text-xl mb-8">
            Whether you are a young person ready to participate, a partner seeking collaboration, or a supporter who believes in our mission, there is a role for you in our programs.
        </p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="<?php echo site_url_path('get-involved#volunteer-form'); ?>" class="bg-white text-primary font-semibold px-8 py-3 rounded-full shadow hover:bg-opacity-90 transition-all">
                Volunteer With Us
            </a>
            <a href="mailto:<?php echo ADMIN_EMAIL; ?>" class="border border-white text-white font-semibold px-8 py-3 rounded-full hover:bg-white hover:text-primary transition-all">
                Partner & Sponsor
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>