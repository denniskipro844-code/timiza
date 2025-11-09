<?php
$page_title = "Get Involved";
include 'includes/header.php';

$volunteerMessage = null;
$donationMessage = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'volunteer') {
        $volunteerMessage = handleVolunteerForm();
    }

    if ($_POST['action'] === 'donate') {
        $donationMessage = handleDonationForm();
    }
}
?>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-primary to-secondary py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <h1 class="text-4xl md:text-5xl font-bold mb-6" data-aos="fade-up">
            Get Involved
        </h1>
        <p class="text-xl max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="200">
            Join us in creating positive change in Kilifi County. Every contribution makes a difference.
        </p>
    </div>
</section>

<!-- Ways to Get Involved -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-dark mb-4" data-aos="fade-up">
                Ways to Make a Difference
            </h2>
            <p class="text-gray-600 text-lg" data-aos="fade-up" data-aos-delay="200">
                Choose how you'd like to contribute to our mission
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center bg-neutral p-8 rounded-lg shadow-lg" data-aos="fade-up" data-aos-delay="100">
                <div class="bg-primary text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-hands-helping text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-dark mb-4">Volunteer</h3>
                <p class="text-gray-600 mb-6">
                    Join our team of dedicated volunteers and directly impact communities through our programs.
                </p>
                <a href="#volunteer-form" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-opacity-90 transition-all">
                    Apply Now
                </a>
            </div>
            
            <div class="text-center bg-neutral p-8 rounded-lg shadow-lg" data-aos="fade-up" data-aos-delay="200">
                <div class="bg-secondary text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-handshake text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-dark mb-4">Partner</h3>
                <p class="text-gray-600 mb-6">
                    Collaborate with us as an organization, business, or institution to amplify our impact.
                </p>
                <a href="#partnership" class="bg-secondary text-white px-6 py-3 rounded-lg hover:bg-opacity-90 transition-all">
                    Learn More
                </a>
            </div>
            
            <div class="text-center bg-neutral p-8 rounded-lg shadow-lg" data-aos="fade-up" data-aos-delay="300">
                <div class="bg-accent text-dark w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-heart text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-dark mb-4">Donate</h3>
                <p class="text-gray-600 mb-6">
                    Support our programs financially and help us reach more young people in need.
                </p>
                <a href="#donate" class="bg-accent text-dark px-6 py-3 rounded-lg hover:bg-opacity-90 transition-all">
                    Donate Now
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Volunteer Form -->
<section id="volunteer-form" class="py-16 bg-neutral">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-dark mb-4" data-aos="fade-up">
                Volunteer Application
            </h2>
            <p class="text-gray-600 text-lg" data-aos="fade-up" data-aos-delay="200">
                Ready to make a difference? Fill out the form below to join our volunteer team.
            </p>
        </div>
        
        <?php if ($volunteerMessage): ?>
        <div class="mb-8 p-4 rounded-lg <?php echo $volunteerMessage['success'] ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-300'; ?>" data-aos="fade-up">
            <?php echo htmlspecialchars($volunteerMessage['message']); ?>
        </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-lg shadow-lg p-8" data-aos="fade-up" data-aos-delay="300">
            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" value="volunteer">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                        <input type="text" id="name" name="name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                        <input type="email" id="email" name="email" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    
                    <div>
                        <label for="interest" class="block text-sm font-medium text-gray-700 mb-2">Area of Interest *</label>
                        <select id="interest" name="interest" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select an area</option>
                            <option value="Health & Gender">Health & Gender</option>
                            <option value="Climate Change">Climate Change</option>
                            <option value="Peace & Security">Peace & Security</option>
                            <option value="Disability Inclusion">Disability Inclusion</option>
                            <option value="Education & Skills">Education & Skills</option>
                            <option value="General Support">General Support</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label for="experience" class="block text-sm font-medium text-gray-700 mb-2">Relevant Experience (Optional)</label>
                    <textarea id="experience" name="experience" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="Tell us about any relevant experience, skills, or qualifications..."></textarea>
                </div>
                
                <div class="text-center">
                    <button type="submit" class="bg-primary text-white px-8 py-3 rounded-lg hover:bg-opacity-90 transition-all transform hover:scale-105">
                        Submit Application
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Partnership Section -->
<section id="partnership" class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-dark mb-4" data-aos="fade-up">
                Partnership Opportunities
            </h2>
            <p class="text-gray-600 text-lg max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="200">
                We welcome partnerships with organizations, businesses, and institutions that share our vision 
                of youth empowerment and community transformation.
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-neutral p-6 rounded-lg shadow-lg" data-aos="fade-up" data-aos-delay="100">
                <h3 class="text-xl font-semibold text-dark mb-4">Corporate Partnerships</h3>
                <p class="text-gray-600 mb-4">
                    Partner with us through CSR initiatives, employee volunteer programs, and skills-based volunteering.
                </p>
                <ul class="text-gray-600 space-y-2">
                    <li class="flex items-center"><i class="fas fa-check text-primary mr-2"></i> Skills development programs</li>
                    <li class="flex items-center"><i class="fas fa-check text-primary mr-2"></i> Mentorship opportunities</li>
                    <li class="flex items-center"><i class="fas fa-check text-primary mr-2"></i> Resource sharing</li>
                </ul>
            </div>
            
            <div class="bg-neutral p-6 rounded-lg shadow-lg" data-aos="fade-up" data-aos-delay="200">
                <h3 class="text-xl font-semibold text-dark mb-4">NGO Collaborations</h3>
                <p class="text-gray-600 mb-4">
                    Join forces with us to maximize impact through joint programs and resource sharing.
                </p>
                <ul class="text-gray-600 space-y-2">
                    <li class="flex items-center"><i class="fas fa-check text-secondary mr-2"></i> Joint program implementation</li>
                    <li class="flex items-center"><i class="fas fa-check text-secondary mr-2"></i> Knowledge sharing</li>
                    <li class="flex items-center"><i class="fas fa-check text-secondary mr-2"></i> Advocacy campaigns</li>
                </ul>
            </div>
            
            <div class="bg-neutral p-6 rounded-lg shadow-lg" data-aos="fade-up" data-aos-delay="300">
                <h3 class="text-xl font-semibold text-dark mb-4">Academic Partnerships</h3>
                <p class="text-gray-600 mb-4">
                    Collaborate with educational institutions for research, internships, and capacity building.
                </p>
                <ul class="text-gray-600 space-y-2">
                    <li class="flex items-center"><i class="fas fa-check text-accent mr-2"></i> Research partnerships</li>
                    <li class="flex items-center"><i class="fas fa-check text-accent mr-2"></i> Student internships</li>
                    <li class="flex items-center"><i class="fas fa-check text-accent mr-2"></i> Training programs</li>
                </ul>
            </div>
        </div>
        
        <div class="text-center mt-12" data-aos="fade-up">
            <a href="<?php echo site_url_path('contact'); ?>" class="bg-primary text-white px-8 py-3 rounded-lg hover:bg-opacity-90 transition-all">
                Discuss Partnership
            </a>
        </div>
    </div>
</section>

<!-- Donation Section -->
<section id="donate" class="py-16 bg-gradient-to-r from-primary to-secondary">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <h2 class="text-3xl md:text-4xl font-bold mb-6" data-aos="fade-up">
            Support Our Mission
        </h2>
        <p class="text-xl mb-8 max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="200">
            Your donation helps us reach more young people and create lasting change in communities across Kilifi County.
        </p>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            <div class="bg-white bg-opacity-10 p-6 rounded-lg" data-aos="fade-up" data-aos-delay="100">
                <h3 class="text-2xl font-bold mb-2">KSh 1,000</h3>
                <p class="text-lg">Provides educational materials for 5 youth</p>
            </div>
            <div class="bg-white bg-opacity-10 p-6 rounded-lg" data-aos="fade-up" data-aos-delay="200">
                <h3 class="text-2xl font-bold mb-2">KSh 5,000</h3>
                <p class="text-lg">Sponsors a youth for skills training program</p>
            </div>
            <div class="bg-white bg-opacity-10 p-6 rounded-lg" data-aos="fade-up" data-aos-delay="300">
                <h3 class="text-2xl font-bold mb-2">KSh 10,000</h3>
                <p class="text-lg">Funds a community workshop for 50 participants</p>
            </div>
        </div>
        
        <div class="bg-white bg-opacity-10 p-8 rounded-lg max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="400">
            <h3 class="text-2xl font-bold mb-6">Make a Donation</h3>

            <?php if ($donationMessage): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $donationMessage['success'] ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-300'; ?>">
                <div class="font-semibold mb-1"><?php echo $donationMessage['success'] ? 'Thank you!' : 'Oops!'; ?></div>
                <p><?php echo htmlspecialchars($donationMessage['message']); ?></p>
                <?php if (!empty($donationMessage['reference'])): ?>
                <p class="text-sm mt-2 opacity-80">Reference: <?php echo htmlspecialchars($donationMessage['reference']); ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6 text-left bg-white bg-opacity-90 rounded-lg p-6">
                <input type="hidden" name="action" value="donate">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="donor_name" class="block text-sm font-semibold text-gray-700 mb-2">Full Name *</label>
                        <input type="text" id="donor_name" name="donor_name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label for="donor_email" class="block text-sm font-semibold text-gray-700 mb-2">Email (optional)</label>
                        <input type="email" id="donor_email" name="donor_email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="you@example.com">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="donor_phone" class="block text-sm font-semibold text-gray-700 mb-2">Phone Number (M-Pesa) *</label>
                        <input type="tel" id="donor_phone" name="donor_phone" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="07XXXXXXXX">
                    </div>
                    <div>
                        <label for="amount" class="block text-sm font-semibold text-gray-700 mb-2">Amount (KES) *</label>
                        <input type="number" id="amount" name="amount" min="1" step="0.01" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                </div>

                <div>
                    <span class="block text-sm font-semibold text-gray-700 mb-2">Payment Method *</span>

                    <div class="space-y-4">
                        <label class="flex items-start space-x-3 bg-gray-100 bg-opacity-70 rounded-lg p-4">
                            <input type="radio" name="payment_method" value="mpesa" class="mt-1 h-4 w-4 text-primary focus:ring-primary" checked>
                            <div>
                                <span class="font-semibold text-gray-800">M-Pesa (Till/PayBill)</span>
                                <p class="text-sm text-gray-600">We will send an STK push to your phone. Choose whether to deposit to our Till or PayBill.</p>

                                <div class="mt-3 space-y-2" data-mpesa-channel>
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" name="mpesa_channel" value="till" class="h-4 w-4 text-primary focus:ring-primary" checked>
                                        <span class="text-sm text-gray-600">Till Number</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" name="mpesa_channel" value="paybill" class="h-4 w-4 text-primary focus:ring-primary">
                                        <span class="text-sm text-gray-600">PayBill</span>
                                    </label>
                                </div>
                            </div>
                        </label>

                        <label class="flex items-start space-x-3 bg-gray-100 bg-opacity-70 rounded-lg p-4">
                            <input type="radio" name="payment_method" value="payhero" class="mt-1 h-4 w-4 text-primary focus:ring-primary">
                            <div>
                                <span class="font-semibold text-gray-800">PayHero (Bank Collection)</span>
                                <p class="text-sm text-gray-600">We’ll send the request to PayHero to deposit directly into our bank account. You’ll receive follow-up instructions.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <button type="submit" class="w-full bg-accent text-dark font-semibold py-3 rounded-lg hover:bg-opacity-90 transition-all transform hover:scale-105">
                    Donate Now
                </button>

                <p class="text-xs text-gray-500 text-center">
                    By donating, you agree that we may contact you about this contribution. All payments are processed securely via Safaricom Daraja and PayHero.
                </p>
            </form>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            var methodRadios = document.querySelectorAll('input[name="payment_method"]');
            var channelSection = document.querySelector('[data-mpesa-channel]');

            methodRadios.forEach(function (radio) {
                radio.addEventListener('change', function () {
                    if (channelSection) {
                        channelSection.classList.toggle('hidden', this.value !== 'mpesa');
                    }
                });
            });
        });
        </script>
    </div>
</section>

<?php include 'includes/footer.php'; ?>