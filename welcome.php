<?php
$page_title = "Welcome";

require_once 'config/database.php';
require_once 'includes/functions.php';

$latest_highlights = getNews(3, true);

$highlightTitles = array_values(array_filter(array_map(static function ($item) {
    return isset($item['title']) ? trim(strip_tags((string) $item['title'])) : null;
}, $latest_highlights ?? [])));

$highlightLineText = !empty($highlightTitles)
    ? 'Trending now: ' . implode(' • ', $highlightTitles)
    : 'Trending now: Youth leaders championing health, climate justice, and inclusive opportunities across Kilifi County.';

$femaleGreeting = "Karibu to Timiza Youth Initiative! I'm Amina, your virtual host. We champion youth leadership in health, climate action, peace, and inclusion across Kilifi. Start by tapping the 'Join Timiza Membership' option below so you can register, meet mentors, and stay plugged into our updates. $highlightLineText";
$maleGreeting = "Jambo and welcome! I'm Malik from Team Timiza. I'm here to guide you through volunteering on our programs and the many ways donations fuel scholarships, safe spaces, and green innovations. Choose 'Volunteer with Us' or 'Support Our Work' below so we can match you with the right opportunity. Ready to begin? $highlightLineText";

$trendLineJson = json_encode($highlightLineText, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$messagesJson = json_encode([
    'female' => $femaleGreeting,
    'male' => $maleGreeting,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$extra_styles = [
    <<<HTML
<style>
    .avatar-card { backdrop-filter: blur(10px); }
    .avatar-figure {
        width: 180px;
        height: 220px;
        border-radius: 32px;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .avatar-figure::before {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top, rgba(255,255,255,0.85), rgba(255,255,255,0));
    }
    .avatar-figure img {
        width: 70%;
        height: auto;
        filter: drop-shadow(0 20px 35px rgba(15,118,110,0.25));
    }
    .avatar-mouth {
        position: absolute;
        bottom: 36px;
        left: 50%;
        transform: translateX(-50%);
        width: 48px;
        height: 14px;
        border-radius: 50%;
        background: rgba(30,41,59,0.3);
        transition: transform 0.15s ease, height 0.15s ease;
    }
    .avatar.speaking .avatar-mouth {
        height: 26px;
        transform: translateX(-50%) scale(1.08);
        background: rgba(239,68,68,0.45);
    }
    .avatar-status[data-status="ready"] { color: #0f766e; }
    .avatar-status[data-status="speaking"] { color: #ef4444; }
    .avatar-status[data-status="loading"] { color: #f97316; }
    .avatar-status[data-status="unavailable"] { color: #6b7280; }
</style>
HTML
];

$extra_scripts = [
    <<<HTML
<script>
(function () {
    const supportsSpeech = 'speechSynthesis' in window && 'SpeechSynthesisUtterance' in window;
    const avatars = document.querySelectorAll('[data-avatar]');
    const statusElements = document.querySelectorAll('[data-avatar-status]');
    const playButtons = document.querySelectorAll('[data-avatar-play]');
    const trendLine = {$trendLineJson};
    const messages = {$messagesJson};

    function updateStatus(state) {
        statusElements.forEach((el) => {
            el.dataset.status = state;
            switch (state) {
                case 'speaking':
                    el.textContent = 'Welcoming you now – turn up your speakers!';
                    break;
                case 'loading':
                    el.textContent = 'Loading our hosts...';
                    break;
                case 'ready':
                    el.textContent = 'Ready to greet you – enjoy the messages or tap a host.';
                    break;
                case 'unavailable':
                    el.textContent = 'Speech not supported here – please read the welcome notes below.';
                    break;
                default:
                    el.textContent = 'Ready to greet you – enjoy the messages or tap a host.';
            }
        });
    }

    if (!supportsSpeech) {
        updateStatus('unavailable');
        playButtons.forEach((btn) => {
            btn.disabled = true;
            btn.classList.add('opacity-60', 'cursor-not-allowed');
        });
        return;
    }

    updateStatus('loading');

    let availableVoices = [];
    let autoplayQueued = false;
    let autoplayStarted = false;

    function pickVoice(preferredGender) {
        const genderHints = preferredGender === 'female'
            ? ['Female', 'female', 'Woman', 'woman', 'girl']
            : ['Male', 'male', 'Man', 'man', 'boy'];
        const fallback = availableVoices[0] || null;
        return availableVoices.find((voice) => genderHints.some((hint) => voice.name.includes(hint))) || fallback;
    }

    function speakAvatar(role, onComplete) {
        window.speechSynthesis.cancel();

        const utterance = new SpeechSynthesisUtterance(messages[role] || trendLine);
        const voice = pickVoice(role === 'female' ? 'female' : 'male');
        if (voice) {
            utterance.voice = voice;
        }

        utterance.rate = 1;
        utterance.pitch = role === 'female' ? 1.08 : 0.95;

        avatars.forEach((avatar) => avatar.classList.remove('speaking'));
        updateStatus('speaking');

        utterance.onstart = () => {
            avatars.forEach((avatar) => {
                if (avatar.dataset.avatar === role) {
                    avatar.classList.add('speaking');
                }
            });
        };

        const finish = () => {
            avatars.forEach((avatar) => avatar.classList.remove('speaking'));
            updateStatus('ready');
            if (typeof onComplete === 'function') {
                onComplete();
            }
        };

        utterance.onend = finish;
        utterance.onerror = finish;

        try {
            window.speechSynthesis.speak(utterance);
        } catch (error) {
            finish();
        }

        return utterance;
    }

    function runAutoplay() {
        if (autoplayStarted || availableVoices.length === 0) {
            return;
        }
        autoplayStarted = true;
        const sequence = ['female', 'male'];
        let index = 0;

        const playNext = () => {
            if (index >= sequence.length) {
                updateStatus('ready');
                return;
            }
            speakAvatar(sequence[index++], () => setTimeout(playNext, 400));
        };

        playNext();
    }

    function triggerAutoplayWhenReady() {
        if (autoplayQueued) {
            return;
        }
        autoplayQueued = true;

        const start = () => {
            if (document.visibilityState !== 'visible') {
                document.addEventListener('visibilitychange', function onVisible() {
                    if (document.visibilityState === 'visible') {
                        document.removeEventListener('visibilitychange', onVisible);
                        setTimeout(runAutoplay, 500);
                    }
                });
                return;
            }
            setTimeout(runAutoplay, 500);
        };

        if (document.readyState === 'complete') {
            start();
        } else {
            window.addEventListener('load', start, { once: true });
        }
    }

    const loadVoices = () => {
        availableVoices = window.speechSynthesis.getVoices();
        if (availableVoices.length === 0) {
            window.speechSynthesis.onvoiceschanged = () => {
                availableVoices = window.speechSynthesis.getVoices();
                if (availableVoices.length > 0) {
                    updateStatus('ready');
                    triggerAutoplayWhenReady();
                }
            };
            return;
        }

        updateStatus('ready');
        triggerAutoplayWhenReady();
    };

    loadVoices();

    playButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const role = button.dataset.avatarPlay;
            if (role) {
                autoplayStarted = true;
                speakAvatar(role);
            }
        });
    });

    ['click', 'touchstart', 'keydown'].forEach((eventType) => {
        window.addEventListener(eventType, () => {
            if (!autoplayStarted) {
                runAutoplay();
            }
        }, { once: true, capture: true });
    });
})();
</script>
HTML
];

include 'includes/header.php';
?>

<!-- Welcome Hero -->
<section class="relative overflow-hidden bg-gradient-to-br from-primary via-secondary to-teal text-white py-20">
    <div class="absolute -top-32 -right-24 w-72 h-72 bg-white bg-opacity-10 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-32 -left-24 w-80 h-80 bg-accent bg-opacity-20 rounded-full blur-3xl"></div>

    <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span class="inline-flex items-center px-4 py-1 rounded-full bg-white bg-opacity-20 text-sm md:text-base font-medium tracking-wide uppercase mb-6">Timiza Youth Initiative</span>
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight mb-6" data-aos="zoom-in">
            Meet Our Talking Hosts
        </h1>
        <p class="text-lg md:text-xl max-w-3xl mx-auto leading-relaxed" data-aos="fade-up" data-aos-delay="150">
            Amina and Malik greet you automatically with the latest ways to join Timiza Youth Initiative—turn up your sound to hear how you can become a member, volunteer on our programs, and support our mission through donations.
        </p>
        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4" data-aos="fade-up" data-aos-delay="300">
            <button data-avatar-play="female" class="bg-accent text-dark px-8 py-3 rounded-full font-semibold shadow-lg shadow-accent/30 hover:bg-opacity-90 transition-all transform hover:scale-105">
                Replay Amina's Welcome
            </button>
            <button data-avatar-play="male" class="border-2 border-white text-white px-8 py-3 rounded-full font-semibold hover:bg-white hover:text-primary transition-all">
                Replay Malik's Welcome
            </button>
        </div>
        <p class="mt-6 text-sm uppercase tracking-[0.3em] font-semibold" data-aos="fade-up" data-aos-delay="400">
            <span class="avatar-status" data-avatar-status data-status="loading">Loading our hosts...</span>
        </p>
    </div>
</section>

<!-- Avatars Section -->
<section class="py-20 bg-neutral">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <div class="avatar-card relative bg-white/80 rounded-2xl shadow-xl border border-white/40 overflow-hidden avatar" data-avatar="female" aria-live="assertive" data-aos="fade-right">
                <div class="p-8 flex flex-col gap-6">
                    <div class="flex items-center gap-4">
                        <div class="avatar-figure bg-gradient-to-br from-rose-400 via-pink-500 to-fuchsia-500">
                            <img src="<?php echo site_url_path('assets/images/timiza-6.webp'); ?>" alt="Avatar of Amina welcoming visitors">
                            <div class="avatar-mouth"></div>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-dark">Amina • Youth Advocate</h2>
                            <p class="text-sm text-primary uppercase tracking-wider">Community Programs Lead</p>
                        </div>
                    </div>
                    <p class="text-gray-600 leading-relaxed">
                        Amina invites you to register as a Timiza member, unlock mentorship support, and access resources that help you design inclusive solutions for young people across Kilifi.
                    </p>
                    <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 text-sm text-rose-600">
                        <strong class="block text-rose-700 mb-2">Spotlight:</strong>
                        <?php echo htmlspecialchars($highlightLineText, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                </div>
            </div>

            <div class="avatar-card relative bg-white/80 rounded-2xl shadow-xl border border-white/40 overflow-hidden avatar" data-avatar="male" aria-live="assertive" data-aos="fade-left">
                <div class="p-8 flex flex-col gap-6">
                    <div class="flex items-center gap-4">
                        <div class="avatar-figure bg-gradient-to-br from-sky-400 via-blue-500 to-indigo-500">
                            <img src="<?php echo site_url_path('assets/images/timiza-4.webp'); ?>" alt="Avatar of Malik welcoming visitors">
                            <div class="avatar-mouth"></div>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-dark">Malik • Climate Champion</h2>
                            <p class="text-sm text-primary uppercase tracking-wider">Innovation &amp; Skills Mentor</p>
                        </div>
                    </div>
                    <p class="text-gray-600 leading-relaxed">
                        Malik highlights how to volunteer on our climate, skills, and peace-building projects, and how every donation keeps scholarships and safe youth spaces running.
                    </p>
                    <div class="bg-sky-50 border border-sky-200 rounded-xl p-4 text-sm text-sky-600">
                        <strong class="block text-sky-700 mb-2">Current Focus:</strong>
                        <?php echo htmlspecialchars($highlightLineText, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Action Steps -->
<section class="py-16 bg-gradient-to-r from-neutral via-white to-neutral">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12" data-aos="fade-up">
            <h2 class="text-3xl md:text-4xl font-bold text-dark mb-4">Start your Timiza journey</h2>
            <p class="text-gray-600 max-w-3xl mx-auto">
                Follow the steps below to become part of our community, serve alongside fellow youth, and sustain the work that transforms Kilifi County.
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white rounded-2xl shadow-lg border border-neutral-200 p-8 flex flex-col gap-4" data-aos="fade-up" data-aos-delay="100">
                <div class="text-primary text-3xl"><i class="fas fa-id-card"></i></div>
                <h3 class="text-xl font-semibold text-dark">Join Timiza Membership</h3>
                <p class="text-gray-600 text-sm leading-relaxed">Complete our quick sign-up and choose the membership lane that suits you. You’ll receive onboarding resources, mentorship access, and event alerts.</p>
                <a href="<?php echo site_url_path('get-involved'); ?>" class="inline-flex items-center gap-2 text-primary font-semibold hover:text-secondary transition-colors">
                    Join Timiza Membership
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="bg-white rounded-2xl shadow-lg border border-neutral-200 p-8 flex flex-col gap-4" data-aos="fade-up" data-aos-delay="200">
                <div class="text-primary text-3xl"><i class="fas fa-hands-helping"></i></div>
                <h3 class="text-xl font-semibold text-dark">Volunteer with Us</h3>
                <p class="text-gray-600 text-sm leading-relaxed">Tell us the skills you want to share and we’ll plug you into health outreaches, digital labs, climate actions, and community peace forums.</p>
                <a href="<?php echo site_url_path('get-involved#volunteer'); ?>" class="inline-flex items-center gap-2 text-primary font-semibold hover:text-secondary transition-colors">
                    Volunteer with Us
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="bg-white rounded-2xl shadow-lg border border-neutral-200 p-8 flex flex-col gap-4" data-aos="fade-up" data-aos-delay="300">
                <div class="text-primary text-3xl"><i class="fas fa-heart"></i></div>
                <h3 class="text-xl font-semibold text-dark">Support Our Work</h3>
                <p class="text-gray-600 text-sm leading-relaxed">Your donations power scholarships, safe hubs, and local innovation challenges. Let us know how you’d like to give or partner.</p>
                <a href="<?php echo site_url_path('contact'); ?>" class="inline-flex items-center gap-2 text-primary font-semibold hover:text-secondary transition-colors">
                    Support Our Work
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Highlights Section -->
<section class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 mb-10">
            <div>
                <h2 class="text-3xl font-bold text-dark" data-aos="fade-right">What's making headlines at Timiza</h2>
                <p class="text-gray-600 mt-2" data-aos="fade-right" data-aos-delay="100">
                    Fresh from our newsroom and field activities – these highlights fuel the stories Amina and Malik share with you.
                </p>
            </div>
            <a href="<?php echo site_url_path('news'); ?>" class="inline-flex items-center gap-2 text-primary font-semibold hover:text-secondary transition-colors" data-aos="fade-left" data-aos-delay="150">
                Explore all news
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <?php if (!empty($latest_highlights)): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($latest_highlights as $index => $article): ?>
                    <?php
                        $imagePath = !empty($article['image'])
                            ? site_url_path($article['image'])
                            : site_url_path('assets/images/timiza-4.webp');
                    ?>
                    <article class="bg-neutral rounded-2xl overflow-hidden shadow-lg border border-neutral-200" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 120; ?>">
                        <img src="<?php echo htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($article['title'] ?? 'Timiza Youth highlight', ENT_QUOTES, 'UTF-8'); ?>" class="w-full h-48 object-cover">
                        <div class="p-6 space-y-4">
                            <div class="text-sm text-gray-500 uppercase tracking-wide"><?php echo htmlspecialchars(formatDate($article['created_at'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                            <h3 class="text-xl font-semibold text-dark">
                                <?php echo htmlspecialchars($article['title'] ?? 'Timiza Youth Initiative Update', ENT_QUOTES, 'UTF-8'); ?>
                            </h3>
                            <p class="text-gray-600 text-sm leading-relaxed">
                                <?php echo htmlspecialchars(truncateText($article['excerpt'] ?? ($article['content'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                            <a href="<?php echo site_url_path('news/' . ($article['slug'] ?? '')); ?>" class="inline-flex items-center gap-2 text-primary font-semibold hover:text-secondary transition-colors">
                                Read story
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-neutral rounded-2xl p-10 text-center text-gray-600 border border-dashed border-primary" data-aos="fade-up">
                Stay tuned! New stories from our youth-led programs are coming soon.
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>