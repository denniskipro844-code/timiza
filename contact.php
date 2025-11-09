<?php
$page_title = "Contact Us";
$extra_styles = [
    '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-sA+4wMgiU06Ds0x4qVLmT1Ns5jT1kzHhrNRb9dr0Ee4=" crossorigin="" />',
    '<style>
.custom-div-icon { background: transparent !important; border: none !important; }
.leaflet-popup-content { margin: 8px 12px; }
.leaflet-popup-content h4 { margin-bottom: 4px; }
.leaflet-popup-content button { transition: all 0.3s ease; }
.faq-question { transition: all 0.3s ease; }
.faq-answer { transition: all 0.3s ease; }
#contact-map { z-index: 1; }
    </style>'
];
$extra_scripts = [
    '<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-o9N1j7kSMKVkdfE7l3qFU0KWScAz7LpwXa1uwZve0m8=" crossorigin=""></script>',
    <<<'SCRIPT'
<script>
// Enhanced Map Initialization
window.initContactMap = function () {
    const mapElement = document.getElementById("contact-map");
    if (!mapElement || typeof L === "undefined") {
        return;
    }

    if (mapElement.dataset.initialized === "true") {
        return;
    }
    mapElement.dataset.initialized = "true";

    // Create map focused on Kilifi region
    const map = L.map("contact-map").setView([-3.6335, 39.8659], 11);

    // Base street layer
    const streetLayer = L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    // Optional satellite layer
    const satelliteLayer = L.tileLayer(
        "https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}",
        { attribution: "Tiles &copy; Esri et al." }
    );

    L.control.layers(
        { "Street Map": streetLayer, "Satellite": satelliteLayer }
    ).addTo(map);

    // Icons
    const mainIcon = L.divIcon({
        html: '<i class="fas fa-building text-white bg-primary rounded-full p-2 shadow-lg"></i>',
        iconSize: [40, 40],
        className: 'custom-div-icon'
    });

    const communityIcon = L.divIcon({
        html: '<i class="fas fa-users text-white bg-secondary rounded-full p-2 shadow-lg"></i>',
        iconSize: [40, 40],
        className: 'custom-div-icon'
    });

    // HQ marker
    L.marker([-3.6335, 39.8659], { icon: mainIcon }).addTo(map)
        .bindPopup(`
            <div class="p-2">
                <h4 class="font-bold text-lg text-primary">Timiza Youth Initiative HQ</h4>
                <p class="text-sm text-gray-600">Kilifi Town Center</p>
                <p class="text-sm">Main Headquarters</p>
                <button onclick="getDirectionsToHQ()" class="mt-2 bg-primary text-white px-3 py-1 rounded text-xs hover:bg-opacity-90">
                    Get Directions
                </button>
            </div>
        `);

    // Community markers
    [
        { coords: [-3.2200, 40.1167], name: "Malindi Youth Center", type: "Community Center" },
        { coords: [-4.0547, 39.6636], name: "Mombasa Outreach Office", type: "Outreach Office" },
        { coords: [-3.3500, 40.0000], name: "Watamu Community Hub", type: "Community Hub" },
        { coords: [-3.9500, 39.7500], name: "Mtwapa Youth Space", type: "Youth Space" }
    ].forEach(center => {
        L.marker(center.coords, { icon: communityIcon }).addTo(map)
            .bindPopup(`
                <div class="p-2">
                    <h4 class="font-bold text-lg text-secondary">${center.name}</h4>
                    <p class="text-sm text-gray-600">${center.type}</p>
                    <p class="text-sm">Open: Mon-Fri, 8AM-5PM</p>
                </div>
            `);
    });

    // Service-area circle
    L.circle([-3.6335, 39.8659], {
        color: '#007B5F',
        fillColor: '#007B5F',
        fillOpacity: 0.1,
        radius: 15000
    }).addTo(map).bindPopup("Our primary service area in Kilifi County");
};

function getDirections() {
    const url = "https://www.google.com/maps/dir/?api=1&destination=-3.6335,39.8659";
    window.open(url, "_blank");
}

function getDirectionsToHQ() {
    getDirections();
}

// FAQ toggle + map observer + form validation
document.addEventListener('DOMContentLoaded', function() {
    const faqQuestions = document.querySelectorAll('.faq-question');
    faqQuestions.forEach(question => {
        question.addEventListener('click', function() {
            const answer = this.nextElementSibling;
            const icon = this.querySelector('i');
            answer.classList.toggle('hidden');
            icon.classList.toggle('fa-chevron-down');
            icon.classList.toggle('fa-chevron-up');

            faqQuestions.forEach(other => {
                if (other !== question) {
                    const otherAnswer = other.nextElementSibling;
                    const otherIcon = other.querySelector('i');
                    otherAnswer.classList.add('hidden');
                    otherIcon.classList.remove('fa-chevron-up');
                    otherIcon.classList.add('fa-chevron-down');
                }
            });
        });
    });

    const mapElement = document.getElementById('contact-map');
    if (mapElement) {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    initContactMap();
                    observer.unobserve(entry.target);
                }
            });
        });
        observer.observe(mapElement);
    }

    const contactForm = document.querySelector('form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let valid = true;
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    field.classList.add('border-red-500');
                } else {
                    field.classList.remove('border-red-500');
                }
            });
            if (!valid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    }
});
</script>
SCRIPT
];
include 'includes/header.php';

$message = '';
if ($_POST) {
    if (isset($_POST['action']) && $_POST['action'] === 'contact') {
        $result = handleContactForm();
        $message = $result;
    }
}

?>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-primary to-secondary py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <h1 class="text-4xl md:text-5xl font-bold mb-6" data-aos="fade-up">
            Contact Us
        </h1>
        <p class="text-xl max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="200">
            Get in touch with us. We'd love to hear from you and answer any questions you may have.
        </p>
    </div>
</section>

<!-- Contact Information -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="text-center" data-aos="fade-up" data-aos-delay="100">
                <div class="bg-primary text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-map-marker-alt text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-dark mb-2">Our Location</h3>
                <p class="text-gray-600">
                    Kilifi County, Kenya<br>
                    Coastal Region
                </p>
            </div>
            <div class="text-center" data-aos="fade-up" data-aos-delay="200">
                <div class="bg-secondary text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-phone text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-dark mb-2">Phone</h3>
                <p class="text-gray-600">
                    +254727721896  or 0715561186<br>
                    <span class="text-sm">Mon - Fri, 8AM - 5PM</span>
                </p>
            </div>
            <div class="text-center" data-aos="fade-up" data-aos-delay="300">
                <div class="bg-accent text-dark w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-envelope text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-dark mb-2">Email</h3>
                <p class="text-gray-600">
                    info@timizayouth.org<br>
                    programs@timizayouth.org
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form -->
<section class="py-16 bg-neutral">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-dark mb-4" data-aos="fade-up">
                Send Us a Message
            </h2>
            <p class="text-gray-600 text-lg" data-aos="fade-up" data-aos-delay="200">
                Have a question or want to learn more about our programs? We're here to help.
            </p>
        </div>

        <?php if ($message): ?>
        <div class="mb-8 p-4 rounded-lg <?php echo $message['success'] ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-300'; ?>" data-aos="fade-up">
            <?php echo htmlspecialchars($message['message']); ?>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-lg p-8" data-aos="fade-up" data-aos-delay="300">
            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" value="contact">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                        <input type="text" id="name" name="name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-300">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                        <input type="email" id="email" name="email" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-300">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-300">
                    </div>
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                        <select id="subject" name="subject" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-300">
                            <option value="">Select a subject</option>
                            <option value="general">General Inquiry</option>
                            <option value="programs">Program Information</option>
                            <option value="partnership">Partnership Opportunity</option>
                            <option value="volunteer">Volunteer Inquiry</option>
                            <option value="donation">Donation Question</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
                    <textarea id="message" name="message" rows="6" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-300" placeholder="Tell us how we can help you..."></textarea>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="newsletter" name="newsletter" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                    <label for="newsletter" class="ml-2 text-sm text-gray-700">
                        Subscribe to our newsletter for updates and opportunities
                    </label>
                </div>

                <div class="text-center">
                    <button type="submit" class="bg-primary text-white px-8 py-4 rounded-lg hover:bg-opacity-90 transition-all transform hover:scale-105 duration-300 font-semibold shadow-lg hover:shadow-xl">
                        Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-dark mb-4" data-aos="fade-up">
                Find Us
            </h2>
            <p class="text-gray-600 text-lg max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="200">
                We're located in the heart of Kilifi County, serving communities across the Coastal Region of Kenya. 
                Visit our headquarters or explore our community outreach locations.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
            <div class="bg-neutral rounded-lg p-6 shadow-md" data-aos="fade-right" data-aos-delay="200">
                <h3 class="text-xl font-semibold text-dark mb-4 flex items-center">
                    <i class="fas fa-headquarters mr-3 text-primary"></i>
                    Main Headquarters
                </h3>
                <p class="text-gray-600 mb-2">
                    <strong>Address:</strong><br>
                    Kilifi Town Center<br>
                    Near County Government Offices<br>
                    Kilifi County, Kenya
                </p>
                <p class="text-gray-600 mb-2">
                    <strong>Hours:</strong><br>
                    Monday - Friday: 8:00 AM - 5:00 PM<br>
                    Saturday: 9:00 AM - 1:00 PM
                </p>
            </div>

            <div class="bg-neutral rounded-lg p-6 shadow-md" data-aos="fade-up" data-aos-delay="300">
                <h3 class="text-xl font-semibold text-dark mb-4 flex items-center">
                    <i class="fas fa-map-marked-alt mr-3 text-secondary"></i>
                    Community Centers
                </h3>
                <ul class="text-gray-600 space-y-2">
                    <li class="flex items-start">
                        <i class="fas fa-circle text-xs mt-2 mr-2 text-primary"></i>
                        <span>Malindi Youth Center</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-circle text-xs mt-2 mr-2 text-primary"></i>
                        <span>Mombasa Outreach Office</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-circle text-xs mt-2 mr-2 text-primary"></i>
                        <span>Watamu Community Hub</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-circle text-xs mt-2 mr-2 text-primary"></i>
                        <span>Mtwapa Youth Space</span>
                    </li>
                </ul>
            </div>

            <div class="bg-neutral rounded-lg p-6 shadow-md" data-aos="fade-left" data-aos-delay="400">
                <h3 class="text-xl font-semibold text-dark mb-4 flex items-center">
                    <i class="fas fa-directions mr-3 text-accent"></i>
                    Getting Here
                </h3>
                <p class="text-gray-600 mb-3">
                    We are easily accessible by public transport from major towns in the Coastal region.
                </p>
                <div class="space-y-2 text-sm text-gray-600">
                    <div class="flex items-center">
                        <i class="fas fa-bus mr-2 text-primary"></i>
                        <span>Matatu routes: Kilifi-Mombasa, Kilifi-Malindi</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-taxi mr-2 text-secondary"></i>
                        <span>Tuk-tuk and boda-boda available locally</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-car mr-2 text-accent"></i>
                        <span>Parking available onsite</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl shadow-2xl overflow-hidden border-4 border-white" data-aos="fade-up" data-aos-delay="500">
            <div id="contact-map" class="h-96 w-full bg-gray-200"></div>
            <div class="bg-white px-6 py-4 border-t border-gray-200">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-dark mb-1">Timiza Youth Initiative - Kilifi Headquarters</h3>
                        <p class="text-gray-600 text-sm">
                            Click on the map markers to explore our locations across Kilifi County
                        </p>
                    </div>
                    <div class="mt-2 md:mt-0">
                        <button onclick="getDirections()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-opacity-90 transition-all flex items-center">
                            <i class="fas fa-route mr-2"></i>
                            Get Directions
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-16 bg-neutral">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-dark mb-4" data-aos="fade-up">
                Frequently Asked Questions
            </h2>
            <p class="text-gray-600 text-lg" data-aos="fade-up" data-aos-delay="200">
                Quick answers to common questions about contacting us
            </p>
        </div>

        <div class="space-y-4" data-aos="fade-up" data-aos-delay="300">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <button class="faq-question w-full text-left p-6 font-semibold text-dark hover:bg-gray-50 transition-all flex justify-between items-center">
                    <span>What's the best way to reach your team quickly?</span>
                    <i class="fas fa-chevron-down transition-transform"></i>
                </button>
                <div class="faq-answer hidden p-6 pt-0 text-gray-600 border-t border-gray-200">
                    For urgent matters, please call our main office during business hours. For non-urgent inquiries, email is the most efficient method as our team can respond when available.
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <button class="faq-question w-full text-left p-6 font-semibold text-dark hover:bg-gray-50 transition-all flex justify-between items-center">
                    <span>Do you offer virtual meetings or consultations?</span>
                    <i class="fas fa-chevron-down transition-transform"></i>
                </button>
                <div class="faq-answer hidden p-6 pt-0 text-gray-600 border-t border-gray-200">
                    Yes! We offer virtual consultations via Zoom, Google Meet, or phone calls. Please indicate your preference when contacting us.
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <button class="faq-question w-full text-left p-6 font-semibold text-dark hover:bg-gray-50 transition-all flex justify-between items-center">
                    <span>How can I volunteer with Timiza Youth Initiative?</span>
                    <i class="fas fa-chevron-down transition-transform"></i>
                </button>
                <div class="faq-answer hidden p-6 pt-0 text-gray-600 border-t border-gray-200">
                    We're always looking for passionate volunteers! Please fill out the contact form and select "Volunteer Inquiry" as the subject. Our volunteer coordinator will reach out with current opportunities.
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Social Media -->
<section class="py-16 bg-gradient-to-r from-primary to-secondary">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <h2 class="text-3xl md:text-4xl font-bold mb-6" data-aos="fade-up">
            Connect With Us
        </h2>
        <p class="text-xl mb-8 max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="200">
            Follow us on social media for the latest updates, success stories, and behind-the-scenes content from our community programs
        </p>

        <div class="flex justify-center space-x-6" data-aos="fade-up" data-aos-delay="300">
            <a href="#" class="bg-white bg-opacity-20 hover:bg-opacity-30 w-16 h-16 rounded-full flex items-center justify-center transition-all transform hover:scale-110 shadow-lg">
                <i class="fab fa-facebook-f text-2xl"></i>
            </a>
            <a href="#" class="bg-white bg-opacity-20 hover:bg-opacity-30 w-16 h-16 rounded-full flex items-center justify-center transition-all transform hover:scale-110 shadow-lg">
                <i class="fab fa-instagram text-2xl"></i>
            </a>
            <a href="#" class="bg-white bg-opacity-20 hover:bg-opacity-30 w-16 h-16 rounded-full flex items-center justify-center transition-all transform hover:scale-110 shadow-lg">
                <i class="fab fa-twitter text-2xl"></i>
            </a>
            <a href="#" class="bg-white bg-opacity-20 hover:bg-opacity-30 w-16 h-16 rounded-full flex items-center justify-center transition-all transform hover:scale-110 shadow-lg">
                <i class="fab fa-linkedin-in text-2xl"></i>
            </a>
            <a href="#" class="bg-white bg-opacity-20 hover:bg-opacity-30 w-16 h-16 rounded-full flex items-center justify-center transition-all transform hover:scale-110 shadow-lg">
                <i class="fab fa-youtube text-2xl"></i>
            </a>
        </div>

        <div class="mt-12 bg-white bg-opacity-10 rounded-2xl p-8 max-w-4xl mx-auto" data-aos="fade-up" data-aos-delay="400">
            <h3 class="text-2xl font-bold mb-4">Stay Updated</h3>
            <p class="mb-6 text-lg">Subscribe to our newsletter for program announcements, success stories, and youth empowerment resources</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <input type="email" placeholder="Enter your email" class="px-4 py-3 rounded-lg flex-grow max-w-md text-dark">
                <button class="bg-accent text-dark px-6 py-3 rounded-lg font-semibold hover:bg-opacity-90 transition-all transform hover:scale-105">
                    Subscribe
                </button>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>