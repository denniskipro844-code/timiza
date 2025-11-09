<?php
$page_title = "Gallery";
include 'includes/header.php';

$gallery_images = getGalleryImages();
?>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-primary to-secondary py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <h1 class="text-4xl md:text-5xl font-bold mb-6" data-aos="fade-up">
            Gallery
        </h1>
        <p class="text-xl max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="200">
            Capturing moments of impact, growth, and community transformation
        </p>
    </div>
</section>

<!-- Gallery Grid -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if (!empty($gallery_images)): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php foreach ($gallery_images as $index => $image): ?>
            <div class="relative group cursor-pointer" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 50; ?>">
                <img src="<?php echo site_url_path('assets/images/gallery/' . htmlspecialchars($image['filename'])); ?>" 
                     alt="<?php echo htmlspecialchars($image['caption'] ?? 'Gallery Image'); ?>" 
                     class="w-full h-64 object-cover rounded-lg shadow-lg group-hover:shadow-xl transition-all transform group-hover:scale-105"
                     onclick="openLightbox('<?php echo htmlspecialchars($image['filename']); ?>', '<?php echo htmlspecialchars($image['caption'] ?? ''); ?>')">
                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all rounded-lg flex items-center justify-center">
                    <i class="fas fa-search-plus text-white text-2xl opacity-0 group-hover:opacity-100 transition-all"></i>
                </div>
                <?php if (!empty($image['caption'])): ?>
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-4 rounded-b-lg">
                    <p class="text-white text-sm"><?php echo htmlspecialchars($image['caption']); ?></p>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-16">
            <i class="fas fa-images text-6xl text-gray-300 mb-6"></i>
            <h3 class="text-2xl font-semibold text-gray-600 mb-4">Gallery Coming Soon</h3>
            <p class="text-gray-500">We're working on uploading photos from our programs and events. Check back soon!</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Sample Gallery (Static for Demo) -->
<section class="py-16 bg-neutral">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-dark mb-4" data-aos="fade-up">
                Our Impact in Pictures
            </h2>
            <p class="text-gray-600 text-lg" data-aos="fade-up" data-aos-delay="200">
                Moments that capture the essence of our work and the spirit of our communities
            </p>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <!-- Sample images for demonstration -->
            <div class="relative group cursor-pointer" data-aos="fade-up" data-aos-delay="100">
                <div class="w-full h-64 bg-gradient-to-br from-primary to-secondary rounded-lg shadow-lg group-hover:shadow-xl transition-all transform group-hover:scale-105 flex items-center justify-center">
                    <div class="text-center text-white">
                        <i class="fas fa-users text-4xl mb-2"></i>
                        <p class="text-sm">Youth Workshop</p>
                    </div>
                </div>
            </div>
            
            <div class="relative group cursor-pointer" data-aos="fade-up" data-aos-delay="200">
                <div class="w-full h-64 bg-gradient-to-br from-secondary to-accent rounded-lg shadow-lg group-hover:shadow-xl transition-all transform group-hover:scale-105 flex items-center justify-center">
                    <div class="text-center text-white">
                        <i class="fas fa-seedling text-4xl mb-2"></i>
                        <p class="text-sm">Tree Planting</p>
                    </div>
                </div>
            </div>
            
            <div class="relative group cursor-pointer" data-aos="fade-up" data-aos-delay="300">
                <div class="w-full h-64 bg-gradient-to-br from-accent to-primary rounded-lg shadow-lg group-hover:shadow-xl transition-all transform group-hover:scale-105 flex items-center justify-center">
                    <div class="text-center text-dark">
                        <i class="fas fa-graduation-cap text-4xl mb-2"></i>
                        <p class="text-sm">Skills Training</p>
                    </div>
                </div>
            </div>
            
            <div class="relative group cursor-pointer" data-aos="fade-up" data-aos-delay="400">
                <div class="w-full h-64 bg-gradient-to-br from-teal to-secondary rounded-lg shadow-lg group-hover:shadow-xl transition-all transform group-hover:scale-105 flex items-center justify-center">
                    <div class="text-center text-white">
                        <i class="fas fa-handshake text-4xl mb-2"></i>
                        <p class="text-sm">Community Meeting</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-12" data-aos="fade-up">
            <p class="text-gray-600 mb-4">Want to see more of our work?</p>
            <a href="<?php echo site_url_path('contact'); ?>" class="bg-primary text-white px-8 py-3 rounded-lg hover:bg-opacity-90 transition-all">
                Visit Our Programs
            </a>
        </div>
    </div>
</section>

<!-- Lightbox Modal -->
<div id="lightbox" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden flex items-center justify-center p-4">
    <div class="relative max-w-4xl max-h-full">
        <button onclick="closeLightbox()" class="absolute top-4 right-4 text-white text-2xl hover:text-gray-300 z-10">
            <i class="fas fa-times"></i>
        </button>
        <img id="lightbox-image" src="" alt="" class="max-w-full max-h-full object-contain">
        <div id="lightbox-caption" class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-4">
            <p class="text-white text-center"></p>
        </div>
    </div>
</div>

<script>
const galleryBasePath = "<?php echo addslashes(site_url_path('assets/images/gallery/')); ?>";

function openLightbox(imageSrc, caption) {
    const lightbox = document.getElementById('lightbox');
    const lightboxImage = document.getElementById('lightbox-image');
    const lightboxCaption = document.getElementById('lightbox-caption');
    
    lightboxImage.src = galleryBasePath + imageSrc;
    lightboxCaption.querySelector('p').textContent = caption;
    lightbox.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    const lightbox = document.getElementById('lightbox');
    lightbox.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close lightbox on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLightbox();
    }
});

// Close lightbox on background click
document.getElementById('lightbox').addEventListener('click', function(e) {
    if (e.target === this) {
        closeLightbox();
    }
});
</script>

<?php include 'includes/footer.php'; ?>