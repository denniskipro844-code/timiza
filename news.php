<?php
$page_title = "News & Events";
include 'includes/header.php';

$news = getNews();
?>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-primary to-secondary py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <h1 class="text-4xl md:text-5xl font-bold mb-6" data-aos="fade-up">
            News & Events
        </h1>
        <p class="text-xl max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="200">
            Stay updated with our latest activities, impact stories, and upcoming events
        </p>
    </div>
</section>

<!-- News Grid -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if (!empty($news)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($news as $index => $article): ?>
            <?php
                $imagePath = !empty($article['image']) ? site_url_path($article['image']) : site_url_path('assets/images/news/placeholder.jpg');
            ?>
            <article class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="w-full h-48 object-cover">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-gray-500"><?php echo formatDate($article['created_at']); ?></span>
                        <span class="bg-primary text-white text-xs px-2 py-1 rounded-full"><?php echo htmlspecialchars($article['category'] ?? 'News'); ?></span>
                    </div>
                    <h3 class="text-xl font-semibold text-dark mb-3 hover:text-primary transition-colors">
                        <a href="<?php echo site_url_path('article?slug=' . $article['slug']); ?>"><?php echo htmlspecialchars($article['title']); ?></a>
                    </h3>
                    <p class="text-gray-600 mb-4"><?php echo truncateText($article['excerpt']); ?></p>
                    <a href="<?php echo site_url_path('article?slug=' . $article['slug']); ?>" class="text-primary hover:text-secondary font-medium inline-flex items-center">
                        Read More
                        <i class="fas fa-arrow-right ml-1 text-sm"></i>
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-16">
            <i class="fas fa-newspaper text-6xl text-gray-300 mb-6"></i>
            <h3 class="text-2xl font-semibold text-gray-600 mb-4">No News Yet</h3>
            <p class="text-gray-500">Check back soon for updates on our activities and impact stories.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Newsletter Signup -->
<section class="py-16 bg-neutral">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-dark mb-6" data-aos="fade-up">
            Stay Updated
        </h2>
        <p class="text-gray-600 text-lg mb-8" data-aos="fade-up" data-aos-delay="200">
            Subscribe to our newsletter to receive the latest news and updates directly in your inbox.
        </p>
        
        <div class="bg-white p-8 rounded-lg shadow-lg max-w-md mx-auto" data-aos="fade-up" data-aos-delay="300">
            <form class="space-y-4">
                <input type="email" placeholder="Enter your email address" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                <button type="submit" class="w-full bg-primary text-white py-3 rounded-lg hover:bg-opacity-90 transition-all">
                    Subscribe to Newsletter
                </button>
            </form>
            <p class="text-xs text-gray-500 mt-4">
                We respect your privacy. Unsubscribe at any time.
            </p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>