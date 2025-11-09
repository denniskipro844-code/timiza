<?php
$page_title = "Article";
include 'includes/header.php';

$slug = $_GET['slug'] ?? '';
$article = $slug ? getNewsBySlug($slug) : null;

if (!$article) {
    http_response_code(404);
}

$relatedArticles = array_filter(getNews(3), function ($item) use ($article) {
    if (!$article) {
        return true;
    }
    return isset($item['id']) && (int) $item['id'] !== (int) $article['id'];
});
$relatedArticles = array_slice($relatedArticles, 0, 3);
?>

<!-- Hero Section -->
<section class="relative bg-gradient-to-r from-primary to-secondary py-20 text-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if ($article): ?>
        <p class="text-sm uppercase tracking-widest text-white/80 mb-4" data-aos="fade-up">
            <?php echo htmlspecialchars($article['category'] ?? 'News'); ?>
        </p>
        <h1 class="text-4xl md:text-5xl font-bold mb-6" data-aos="fade-up" data-aos-delay="100">
            <?php echo htmlspecialchars($article['title']); ?>
        </h1>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between text-white/80 text-sm" data-aos="fade-up" data-aos-delay="150">
            <span><i class="fas fa-calendar mr-2"></i><?php echo formatDate($article['created_at']); ?></span>
            <?php if (!empty($article['author'])): ?>
            <span class="mt-2 sm:mt-0"><i class="fas fa-user mr-2"></i><?php echo htmlspecialchars($article['author']); ?></span>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <h1 class="text-4xl md:text-5xl font-bold mb-6" data-aos="fade-up">Article Not Found</h1>
        <p class="text-lg text-white/80" data-aos="fade-up" data-aos-delay="100">
            The story you're looking for isn't available. Explore other updates below.
        </p>
        <?php endif; ?>
    </div>
</section>

<?php if ($article): ?>
<!-- Feature Image -->
<section class="bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if (!empty($article['image'])): ?>
        <div class="-mt-16 relative z-10" data-aos="fade-up">
            <img src="<?php echo htmlspecialchars(site_url_path($article['image'])); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="w-full h-[420px] object-cover rounded-3xl shadow-xl">
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Article Content -->
<section class="py-16 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if (!empty($article['excerpt'])): ?>
        <div class="bg-neutral border border-white rounded-2xl p-6 mb-10 shadow-sm" data-aos="fade-up">
            <h2 class="text-xl font-semibold text-dark mb-3">In Summary</h2>
            <p class="text-gray-600 leading-relaxed"><?php echo nl2br(htmlspecialchars($article['excerpt'])); ?></p>
        </div>
        <?php endif; ?>

        <article class="prose prose-lg max-w-none text-gray-700 leading-relaxed" data-aos="fade-up" data-aos-delay="100">
            <?php echo nl2br(htmlspecialchars($article['content'])); ?>
        </article>

        <div class="mt-12 flex flex-wrap gap-4 items-center text-sm text-gray-500" data-aos="fade-up">
            <span class="inline-flex items-center bg-primary/10 text-primary px-3 py-1 rounded-full">
                <i class="fas fa-tag mr-2 text-xs"></i>
                <?php echo htmlspecialchars($article['category'] ?? 'News'); ?>
            </span>
            <?php if (!empty($article['updated_at'])): ?>
            <span><i class="fas fa-clock mr-2"></i>Updated <?php echo formatDate($article['updated_at']); ?></span>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Related Articles -->
<section class="py-16 bg-neutral">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-10">
            <div>
                <h2 class="text-3xl font-bold text-dark" data-aos="fade-up">More Stories</h2>
                <p class="text-gray-600" data-aos="fade-up" data-aos-delay="100">Discover other highlights from Timiza Youth Initiative.</p>
            </div>
            <a href="<?php echo site_url_path('news'); ?>" class="mt-4 md:mt-0 inline-flex items-center text-primary hover:text-secondary font-medium" data-aos="fade-up" data-aos-delay="200">
                View All News
                <i class="fas fa-arrow-right ml-2 text-sm"></i>
            </a>
        </div>

        <?php if (!empty($relatedArticles)): ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($relatedArticles as $index => $item): ?>
            <?php
                $image = !empty($item['image']) ? site_url_path($item['image']) : site_url_path('assets/images/news/placeholder.jpg');
                $delay = ($index + 1) * 100;
            ?>
            <article class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-lg transition-shadow" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-44 object-cover">
                <div class="p-6">
                    <span class="text-xs uppercase tracking-wide text-primary font-semibold"><?php echo htmlspecialchars($item['category'] ?? 'News'); ?></span>
                    <h3 class="text-lg font-semibold text-dark mt-2 mb-3">
                        <a href="<?php echo site_url_path('article?slug=' . $item['slug']); ?>" class="hover:text-primary transition-colors"><?php echo htmlspecialchars($item['title']); ?></a>
                    </h3>
                    <p class="text-sm text-gray-600 mb-4"><?php echo htmlspecialchars(truncateText($item['excerpt'] ?? $item['content'] ?? '', 120)); ?></p>
                    <a href="<?php echo site_url_path('article?slug=' . $item['slug']); ?>" class="inline-flex items-center text-primary text-sm font-medium">
                        Read More
                        <i class="fas fa-arrow-right ml-2 text-xs"></i>
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-2xl p-12 text-center shadow" data-aos="fade-up">
            <i class="fas fa-newspaper text-5xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-dark mb-2">No other articles right now</h3>
            <p class="text-gray-600">We will publish more updates soon. Stay tuned!</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>