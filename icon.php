<?php
$page_title = "PWA Icon Generator";
require_once '../config/database.php';
require_once '../includes/functions.php';
requireAuth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Timiza Youth Initiative</title>
    <link rel="icon" href="../assets/images/favicon.ico" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#007B5F',
                        secondary: '#00BFA6',
                        neutral: '#F8FAFC',
                        slate: {
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            500: '#64748b',
                            600: '#475569',
                            800: '#1e293b',
                            900: '#0f172a'
                        }
                    }
                }
            }
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: "Poppins", sans-serif; }
        canvas { border-radius: 24px; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen">
    <?php include '../includes/nav-admin.php'; ?>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">
        <header class="space-y-2">
            <h1 class="text-3xl font-bold text-slate-900">PWA Icon Generator</h1>
            <p class="text-slate-600">
                Upload your base logo once, then export the 192×192 and 512×512 icons required for the manifest.json file.
            </p>
        </header>

        <section class="grid gap-6 md:grid-cols-2">
            <div class="bg-white rounded-3xl shadow-lg p-6 space-y-4">
                <h2 class="text-xl font-semibold text-slate-800">1. Base logo</h2>
                <label class="block">
                    <span class="text-sm font-medium text-slate-600">Upload PNG/SVG (square works best)</span>
                    <input id="logo-input" type="file" accept="image/*" class="mt-2 block w-full border border-slate-200 rounded-lg px-3 py-2">
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-slate-600">Background color</span>
                    <input id="bg-picker" type="color" value="#ffffff" class="mt-2 h-10 w-24 border border-slate-300 rounded">
                </label>

                <div class="space-y-2">
                    <span class="text-sm font-medium text-slate-600">Scale (%)</span>
                    <input id="scale-range" type="range" min="40" max="95" value="80" class="w-full accent-primary">
                    <p class="text-xs text-slate-500">Adjust how large the logo appears inside the icon.</p>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-lg p-6 space-y-5">
                <h2 class="text-xl font-semibold text-slate-800">2. Download icons</h2>

                <div class="space-y-4">
                    <figure class="space-y-3 text-center">
                        <canvas id="canvas-192" width="192" height="192"></canvas>
                        <button data-size="192"
                                class="download-btn inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-white font-semibold hover:bg-primary/90 transition">
                            Download 192×192
                        </button>
                    </figure>

                    <figure class="space-y-3 text-center">
                        <canvas id="canvas-512" width="512" height="512"></canvas>
                        <button data-size="512"
                                class="download-btn inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-white font-semibold hover:bg-primary/90 transition">
                            Download 512×512
                        </button>
                    </figure>
                </div>
            </div>
        </section>
    </main>

    <script>
        const DEFAULT_LOGO = '../assets/images/logo.png';
        const canvases = {
            192: document.getElementById('canvas-192'),
            512: document.getElementById('canvas-512')
        };

        const state = {
            image: new Image(),
            bgColor: '#ffffff',
            scale: 0.8
        };
        state.image.crossOrigin = 'anonymous';

        function drawIcons() {
            Object.entries(canvases).forEach(([size, canvas]) => {
                const ctx = canvas.getContext('2d');
                const s = Number(size);
                ctx.fillStyle = state.bgColor;
                ctx.fillRect(0, 0, s, s);

                if (!state.image.complete || state.image.naturalWidth === 0) return;

                const maxSide = s * state.scale;
                const ratio = state.image.naturalWidth / state.image.naturalHeight;
                let drawWidth, drawHeight;

                if (ratio >= 1) {
                    drawWidth = maxSide;
                    drawHeight = maxSide / ratio;
                } else {
                    drawHeight = maxSide;
                    drawWidth = maxSide * ratio;
                }

                const dx = (s - drawWidth) / 2;
                const dy = (s - drawHeight) / 2;
                ctx.drawImage(state.image, dx, dy, drawWidth, drawHeight);
            });
        }

        function loadLogo(src) {
            state.image.onload = drawIcons;
            state.image.src = src;
        }

        document.getElementById('logo-input').addEventListener('change', (event) => {
            const file = event.target.files?.[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (e) => loadLogo(e.target.result);
            reader.readAsDataURL(file);
        });

        document.getElementById('bg-picker').addEventListener('input', (event) => {
            state.bgColor = event.target.value;
            drawIcons();
        });

        document.getElementById('scale-range').addEventListener('input', (event) => {
            state.scale = Number(event.target.value) / 100;
            drawIcons();
        });

        document.querySelectorAll('.download-btn').forEach((button) => {
            button.addEventListener('click', () => {
                const size = button.getAttribute('data-size');
                const canvas = canvases[size];
                const link = document.createElement('a');
                link.download = `logo-${size}.png`;
                link.href = canvas.toDataURL('image/png');
                link.click();
            });
        });

        loadLogo(DEFAULT_LOGO);
    </script>
</body>
</html>