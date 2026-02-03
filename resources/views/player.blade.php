<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quran Visuals</title>
    <script src="https://www.youtube.com/iframe_api"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600&family=Inter:wght@400;600&display=swap');
        :root {
            --bg-1: #0b0b0d;
            --bg-2: #141318;
            --bg-3: #1b1a22;
            --accent: #c28b3b;
            --accent-2: #6f4b1f;
            --text: #f2f2f2;
            --muted: #b2b0bd;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            color: var(--text);
            background: radial-gradient(circle at top left, var(--bg-3), var(--bg-1)),
                radial-gradient(circle at bottom right, var(--bg-2), var(--bg-1));
            font-family: "Cinzel", "Playfair Display", "Georgia", serif;
            overflow: hidden;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background-image:
                radial-gradient(circle at 20% 20%, rgba(255, 255, 255, 0.04) 0, transparent 35%),
                radial-gradient(circle at 70% 30%, rgba(255, 255, 255, 0.03) 0, transparent 40%),
                radial-gradient(circle at 40% 80%, rgba(255, 255, 255, 0.02) 0, transparent 45%);
            pointer-events: none;
            mix-blend-mode: screen;
        }

        .app {
            position: relative;
            height: 100vh;
            display: grid;
            grid-template-rows: auto 1fr;
            gap: 18px;
            padding: 22px 24px 24px;
        }

        header {
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            gap: 16px;
        }

        .brand {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .brand h1 {
            font-size: clamp(1.4rem, 1.6vw + 1rem, 2.2rem);
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .brand p {
            color: var(--muted);
            font-family: "Inter", "Helvetica Neue", Arial, sans-serif;
            font-size: 0.9rem;
        }

        .controls {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            justify-content: flex-end;
            font-family: "Inter", "Helvetica Neue", Arial, sans-serif;
        }

        .controls input,
        .controls select,
        .controls button {
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.14);
            background: rgba(8, 9, 12, 0.7);
            color: var(--text);
            font-size: 0.9rem;
        }

        .controls input[type="url"] {
            min-width: min(48vw, 520px);
        }

        .controls input[type="color"] {
            width: 38px;
            height: 38px;
            min-width: unset;
            padding: 3px;
            cursor: pointer;
            border-radius: 50%;
        }

        .controls button {
            border-color: var(--accent);
            background: linear-gradient(120deg, rgba(194, 139, 59, 0.18), rgba(194, 139, 59, 0.35));
            cursor: pointer;
            font-weight: 600;
            letter-spacing: 0.04em;
        }

        .stage {
            position: relative;
            border-radius: 24px;
            overflow: hidden;
            background: rgba(5, 5, 7, 0.6);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
        }

        #visuals {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
        }

        .player {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            padding: 24px;
            pointer-events: none;
        }

        .player iframe,
        .player audio {
            pointer-events: auto;
        }

        .player iframe,
        .player audio {
            max-width: 100%;
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(0, 0, 0, 0.65);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.45);
        }

        .player audio {
            width: min(860px, 92%);
        }

        .player iframe {
            position: absolute;
            bottom: 20px;
            right: 20px;
            width: 280px;
            height: 158px;
            border-radius: 12px;
            z-index: 10;
            opacity: 0.85;
            transition: opacity 0.3s, transform 0.3s;
        }

        .player iframe:hover {
            opacity: 1;
            transform: scale(1.05);
        }

        .player audio {
            height: 58px;
        }

        .meta {
            position: absolute;
            inset: auto 28px 28px 28px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            font-family: "Inter", "Helvetica Neue", Arial, sans-serif;
            font-size: 0.92rem;
            color: var(--muted);
            transition: opacity 0.5s ease;
        }

        .meta[hidden] {
            display: none;
        }

        .meta strong {
            color: var(--text);
        }

        .message {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            text-align: center;
            padding: 24px;
            font-family: "Inter", "Helvetica Neue", Arial, sans-serif;
            color: var(--muted);
        }

        .message[hidden] {
            display: none;
        }

        .alert {
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid rgba(255, 120, 120, 0.3);
            background: rgba(120, 40, 40, 0.2);
            color: #ffdede;
        }

        /* Cinema / fullscreen mode */
        .cinema-exit-btn {
            position: absolute;
            top: 18px;
            right: 18px;
            z-index: 100;
            padding: 8px 18px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            background: rgba(0, 0, 0, 0.6);
            color: #fff;
            font-family: "Inter", sans-serif;
            font-size: 0.85rem;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .cinema-exit-btn[hidden] {
            display: none;
        }

        .stage:hover .cinema-exit-btn:not([hidden]) {
            opacity: 1;
        }

        .cinema-fullscreen-btn {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 100;
            padding: 14px 28px;
            border-radius: 12px;
            border: 1px solid var(--accent);
            background: linear-gradient(120deg, rgba(194, 139, 59, 0.25), rgba(194, 139, 59, 0.5));
            color: #fff;
            font-family: "Cinzel", serif;
            font-size: 1.1rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            cursor: pointer;
        }

        .cinema-fullscreen-btn[hidden] {
            display: none;
        }

        .cinema-share-btn {
            position: absolute;
            top: 18px;
            left: 18px;
            z-index: 100;
            padding: 8px 18px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            background: rgba(0, 0, 0, 0.6);
            color: #fff;
            font-family: "Inter", sans-serif;
            font-size: 0.85rem;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.5s;
            display: none;
        }

        .cinema-share-btn[hidden] {
            display: none;
        }

        .stage:fullscreen .cinema-share-btn:not([hidden]),
        .stage:-webkit-full-screen .cinema-share-btn:not([hidden]) {
            display: block;
        }

        .cinema-share-btn.visible {
            opacity: 1;
        }

        .cinema-share-btn .share-feedback {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            padding: 6px 12px;
            border-radius: 6px;
            background: rgba(194, 139, 59, 0.9);
            color: #fff;
            font-size: 0.78rem;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .cinema-share-btn .share-feedback.show {
            opacity: 1;
        }

        /* In-stage preset selector (fullscreen only) */
        .stage-preset-select {
            position: absolute;
            bottom: 18px;
            right: 18px;
            z-index: 100;
            padding: 8px 14px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            background: rgba(0, 0, 0, 0.6);
            color: #fff;
            font-family: "Inter", sans-serif;
            font-size: 0.85rem;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.3s;
            display: none;
        }

        .stage:fullscreen .stage-preset-select,
        .stage:-webkit-full-screen .stage-preset-select {
            display: block;
        }

        .stage-color-picker {
            position: absolute;
            bottom: 18px;
            right: 170px;
            z-index: 100;
            width: 34px;
            height: 34px;
            padding: 2px;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, 0.25);
            background: rgba(0, 0, 0, 0.6);
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.3s;
            display: none;
        }

        .stage:fullscreen .stage-color-picker,
        .stage:-webkit-full-screen .stage-color-picker {
            display: block;
        }

        .stage:hover .stage-preset-select,
        .stage:hover .stage-color-picker {
            opacity: 1;
        }

        .stage:fullscreen .player iframe {
            opacity: 0 !important;
            pointer-events: none !important;
        }

        .stage:fullscreen .player audio {
            opacity: 0 !important;
            pointer-events: none !important;
        }

        .stage:fullscreen .message {
            display: none !important;
        }

        .stage:fullscreen .meta {
            inset: auto 0 40px 0;
            text-align: center;
            align-items: center;
            font-size: 1.15rem;
        }

        .stage:fullscreen .meta strong {
            font-size: 1.4rem;
        }

        .stage:fullscreen {
            border-radius: 0;
            background: #000;
        }

        /* Webkit fullscreen */
        .stage:-webkit-full-screen .player iframe {
            opacity: 0 !important;
            pointer-events: none !important;
        }

        .stage:-webkit-full-screen .player audio {
            opacity: 0 !important;
            pointer-events: none !important;
        }

        .stage:-webkit-full-screen .message {
            display: none !important;
        }

        .stage:-webkit-full-screen .meta {
            inset: auto 0 40px 0;
            text-align: center;
            align-items: center;
            font-size: 1.15rem;
        }

        .stage:-webkit-full-screen .meta strong {
            font-size: 1.4rem;
        }

        .stage:-webkit-full-screen {
            border-radius: 0;
            background: #000;
        }

        @media (max-width: 760px) {
            header {
                grid-template-columns: 1fr;
            }

            .controls input {
                width: 100%;
                min-width: unset;
            }
        }
    </style>
</head>
<body>
    <div class="app">
        <header>
            <div class="brand">
                <h1>Quran Visuals</h1>
                <p>Cinematic audio-reactive visuals for Quran recitation.</p>
                <a href="/feedback" style="color: var(--muted); font-family: 'Inter', 'Helvetica Neue', Arial, sans-serif; font-size: 0.85rem; text-decoration: none; margin-top: 2px;">Feature Requests &amp; Roadmap &rarr;</a>
            </div>
            <form class="controls" id="url-form">
                <input id="url-input" type="url" placeholder="Paste YouTube or direct audio URL..." required>
                <select id="preset-select" aria-label="Select visual preset">
                    @foreach ($presets as $preset)
                        <option value="{{ $preset['id'] }}">{{ $preset['name'] }}</option>
                    @endforeach
                </select>
                <input type="color" id="color-picker" value="{{ $presets[0]['vars']['--accent'] }}" aria-label="Accent color" title="Accent color">
                <button type="submit">Enter Cinema</button>
            </form>
        </header>

        <section class="stage" id="stage">
            <canvas id="visuals"></canvas>
            <div class="player">
                <iframe id="yt-player" title="YouTube Quran Player" allow="autoplay; fullscreen" allowfullscreen hidden></iframe>
                <audio id="audio-player" controls hidden></audio>
            </div>
            <div class="message" id="message">
                Paste a Quran YouTube link or a direct audio file to begin.
            </div>
            <div class="meta" id="meta" hidden>
                <div><strong id="meta-title">Ready</strong></div>
                <div id="meta-subtitle">Awaiting input</div>
                <div id="meta-warning"></div>
            </div>
            <button id="exit-cinema" class="cinema-exit-btn" hidden>Exit Cinema</button>
            <button id="share-btn" class="cinema-share-btn" hidden>&#8599; Share<span class="share-feedback" id="share-feedback"></span></button>
            <button id="fullscreen-btn" class="cinema-fullscreen-btn" hidden>Go Fullscreen</button>
            <input type="color" id="stage-color-picker" class="stage-color-picker" value="{{ $presets[0]['vars']['--accent'] }}" aria-label="Accent color" title="Accent color">
            <select id="stage-preset-select" class="stage-preset-select" aria-label="Change visual preset">
                @foreach ($presets as $preset)
                    <option value="{{ $preset['id'] }}">{{ $preset['name'] }}</option>
                @endforeach
            </select>
        </section>
    </div>

    <script>
        const presets = @json($presets);
        const form = document.getElementById('url-form');
        const input = document.getElementById('url-input');
        const message = document.getElementById('message');
        const meta = document.getElementById('meta');
        const metaTitle = document.getElementById('meta-title');
        const metaSubtitle = document.getElementById('meta-subtitle');
        const metaWarning = document.getElementById('meta-warning');
        const ytPlayer = document.getElementById('yt-player');
        const audioPlayer = document.getElementById('audio-player');
        const canvas = document.getElementById('visuals');
        const ctx = canvas.getContext('2d');
        const presetSelect = document.getElementById('preset-select');
        const stage = document.getElementById('stage');
        const exitCinemaBtn = document.getElementById('exit-cinema');
        const fullscreenBtn = document.getElementById('fullscreen-btn');
        const stagePresetSelect = document.getElementById('stage-preset-select');
        const colorPicker = document.getElementById('color-picker');
        const stageColorPicker = document.getElementById('stage-color-picker');
        const shareBtn = document.getElementById('share-btn');
        const shareFeedback = document.getElementById('share-feedback');

        let audioContext = null;
        let analyser = null;
        let analyserData = null;
        let lastReactive = false;
        let flowOffset = 0;
        let mediaSource = null;
        let ytMode = false;
        let ytPlaying = false;
        let ytApiPlayer = null;

        // ── Color utilities ───────────────────────────────────────────────

        function hexToRgb(hex) {
            hex = hex.replace('#', '');
            if (hex.length === 3) hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
            const n = parseInt(hex, 16);
            return { r: (n >> 16) & 255, g: (n >> 8) & 255, b: n & 255 };
        }

        function rgba(hex, alpha) {
            const c = hexToRgb(hex);
            return `rgba(${c.r}, ${c.g}, ${c.b}, ${alpha})`;
        }

        // ── Effect registry ───────────────────────────────────────────────

        const effects = {

            gradientGlow(ctx, w, h, volume, flowOffset, params) {
                const glow = 0.2 + volume * 0.9;
                const color = params.color || '#ffffff';
                const gradient = ctx.createLinearGradient(0, 0, w, h);
                gradient.addColorStop(0, rgba(color, 0.08 + glow * 0.18));
                gradient.addColorStop(1, 'rgba(0, 0, 0, 0)');
                ctx.fillStyle = gradient;
                ctx.fillRect(0, 0, w, h);
            },

            concentricArcs(ctx, w, h, volume, flowOffset, params) {
                const color = params.color || '#ffffff';
                const count = params.count || 5;
                const glow = 0.2 + volume * 0.9;
                ctx.save();
                ctx.translate(w / 2, h / 2);
                ctx.shadowBlur = 15 + volume * 25;
                ctx.shadowColor = color;
                for (let i = 0; i < count; i++) {
                    const radius = (Math.min(w, h) / 5) + i * 50 + volume * 120;
                    ctx.beginPath();
                    ctx.strokeStyle = rgba(color, 0.12 + glow * 0.3);
                    ctx.lineWidth = 2 + volume * 2;
                    ctx.arc(0, 0, radius, flowOffset + i, Math.PI * 1.2 + flowOffset + i);
                    ctx.stroke();
                }
                ctx.shadowBlur = 0;
                ctx.restore();
            },

            particles(ctx, w, h, volume, flowOffset, params) {
                const color = params.color || '#ffffff';
                const count = params.count || 60;
                const shape = params.shape || 'square';
                ctx.save();
                ctx.globalCompositeOperation = 'lighter';
                for (let i = 0; i < count; i++) {
                    const x = (Math.sin(flowOffset * 2 + i) * 0.4 + 0.5) * w;
                    const y = (Math.cos(flowOffset * 1.3 + i) * 0.4 + 0.5) * h;
                    const size = 3 + volume * 10;
                    ctx.fillStyle = rgba(color, 0.1 + volume * 0.35);
                    if (shape === 'circle') {
                        ctx.beginPath();
                        ctx.arc(x, y, size / 2, 0, Math.PI * 2);
                        ctx.fill();
                    } else {
                        ctx.fillRect(x, y, size, size);
                    }
                }
                ctx.restore();
            },

            waveLine(ctx, w, h, volume, flowOffset, params) {
                const color = params.color || '#ffffff';
                const yPos = params.yPosition || 0.78;
                ctx.save();
                ctx.globalAlpha = 0.5;
                ctx.shadowBlur = 8 + volume * 15;
                ctx.shadowColor = color;
                ctx.strokeStyle = rgba(color, 0.2 + volume * 0.5);
                ctx.lineWidth = 2 + volume * 2;
                ctx.beginPath();
                for (let x = 0; x < w; x += 12) {
                    const wave = Math.sin(flowOffset * 2 + x * 0.02) * (12 + volume * 40);
                    ctx.lineTo(x, h * yPos + wave);
                }
                ctx.stroke();
                ctx.shadowBlur = 0;
                ctx.restore();
            },

            pulseRing(ctx, w, h, volume, flowOffset, params) {
                const color = params.color || '#ffffff';
                const maxRadius = Math.min(w, h) * 0.4;
                const pulse = (Math.sin(flowOffset * 3) * 0.5 + 0.5);
                const radius = maxRadius * (0.3 + pulse * 0.4 + volume * 0.3);
                ctx.save();
                ctx.translate(w / 2, h / 2);
                ctx.shadowBlur = 20 + volume * 30;
                ctx.shadowColor = color;
                for (let i = 0; i < 3; i++) {
                    const r = radius + i * 20;
                    const alpha = (0.3 - i * 0.04) + volume * 0.2;
                    ctx.beginPath();
                    ctx.strokeStyle = rgba(color, Math.max(0, alpha));
                    ctx.lineWidth = 3 + volume * 2 - i * 0.5;
                    ctx.arc(0, 0, r, 0, Math.PI * 2);
                    ctx.stroke();
                }
                ctx.shadowBlur = 0;
                ctx.restore();
            },

            starField(ctx, w, h, volume, flowOffset, params) {
                const color = params.color || '#ffffff';
                const count = params.count || 100;
                ctx.save();
                ctx.globalCompositeOperation = 'lighter';
                for (let i = 0; i < count; i++) {
                    // Deterministic positions seeded by index
                    const sx = ((Math.sin(i * 127.1) * 43758.5453) % 1 + 1) % 1;
                    const sy = ((Math.sin(i * 269.5) * 18642.3217) % 1 + 1) % 1;
                    const x = sx * w;
                    const y = sy * h;
                    const twinkle = Math.sin(flowOffset * 2 + i * 1.7) * 0.5 + 0.5;
                    const size = 1.5 + twinkle * 2.5 + volume * 3;
                    ctx.fillStyle = rgba(color, 0.2 + twinkle * 0.5 + volume * 0.15);
                    ctx.beginPath();
                    ctx.arc(x, y, size, 0, Math.PI * 2);
                    ctx.fill();
                }
                ctx.restore();
            },

            verticalBars(ctx, w, h, volume, flowOffset, params) {
                const color = params.color || '#ffffff';
                const count = params.count || 48;
                const barWidth = w / count;
                ctx.save();
                ctx.globalCompositeOperation = 'lighter';
                for (let i = 0; i < count; i++) {
                    const freq = Math.sin(flowOffset * 3 + i * 0.3) * 0.5 + 0.5;
                    const barHeight = (freq * 0.3 + volume * 0.5) * h * 0.5;
                    const x = i * barWidth;
                    ctx.fillStyle = rgba(color, 0.15 + freq * 0.25 + volume * 0.15);
                    ctx.fillRect(x, h - barHeight, barWidth - 1, barHeight);
                }
                ctx.restore();
            },

            nebulaClouds(ctx, w, h, volume, flowOffset, params) {
                const color = params.color || '#ffffff';
                const c = hexToRgb(color);
                ctx.save();
                ctx.globalCompositeOperation = 'lighter';
                const blobs = [
                    { xf: 0.3, yf: 0.4, rf: 0.25, speed: 0.7 },
                    { xf: 0.7, yf: 0.6, rf: 0.3, speed: 1.1 },
                    { xf: 0.5, yf: 0.3, rf: 0.2, speed: 0.9 },
                ];
                for (const blob of blobs) {
                    const bx = w * blob.xf + Math.sin(flowOffset * blob.speed) * w * 0.08;
                    const by = h * blob.yf + Math.cos(flowOffset * blob.speed * 0.8) * h * 0.06;
                    const br = Math.min(w, h) * blob.rf + volume * 40;
                    const grad = ctx.createRadialGradient(bx, by, 0, bx, by, br);
                    grad.addColorStop(0, `rgba(${c.r}, ${c.g}, ${c.b}, ${0.1 + volume * 0.15})`);
                    grad.addColorStop(1, `rgba(${c.r}, ${c.g}, ${c.b}, 0)`);
                    ctx.fillStyle = grad;
                    ctx.fillRect(bx - br, by - br, br * 2, br * 2);
                }
                ctx.restore();
            },

            geometricMandala(ctx, w, h, volume, flowOffset, params) {
                const color = params.color || '#ffffff';
                const sides = params.sides || 6;
                const layers = 4;
                ctx.save();
                ctx.translate(w / 2, h / 2);
                ctx.shadowBlur = 12 + volume * 20;
                ctx.shadowColor = color;
                for (let layer = 0; layer < layers; layer++) {
                    const radius = 60 + layer * 50 + volume * 80;
                    const rotation = flowOffset * (0.5 + layer * 0.2) * (layer % 2 === 0 ? 1 : -1);
                    ctx.beginPath();
                    ctx.strokeStyle = rgba(color, 0.15 + volume * 0.3 - layer * 0.015);
                    ctx.lineWidth = 2.5 - layer * 0.2;
                    for (let i = 0; i <= sides; i++) {
                        const angle = (Math.PI * 2 / sides) * i + rotation;
                        const x = Math.cos(angle) * radius;
                        const y = Math.sin(angle) * radius;
                        if (i === 0) ctx.moveTo(x, y);
                        else ctx.lineTo(x, y);
                    }
                    ctx.closePath();
                    ctx.stroke();
                }
                ctx.shadowBlur = 0;
                ctx.restore();
            },
        };

        // ── Core functions ────────────────────────────────────────────────

        function getCurrentPreset() {
            return presets.find((p) => p.id === presetSelect.value) || presets[0];
        }

        function resizeCanvas() {
            const ratio = window.devicePixelRatio || 1;
            canvas.width = canvas.clientWidth * ratio;
            canvas.height = canvas.clientHeight * ratio;
            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.scale(ratio, ratio);
        }

        function applyPreset(presetId) {
            const preset = presets.find((item) => item.id === presetId) || presets[0];
            Object.entries(preset.vars).forEach(([key, value]) => {
                document.documentElement.style.setProperty(key, value);
            });
            // Reset color pickers to this preset's accent
            colorPicker.value = preset.vars['--accent'];
            stageColorPicker.value = preset.vars['--accent'];
            // Reset layer colors to originals
            if (preset._originalLayers) {
                preset.layers = JSON.parse(JSON.stringify(preset._originalLayers));
            }
        }

        // Store original layer colors for each preset so we can remap them
        presets.forEach(p => {
            p._originalLayers = JSON.parse(JSON.stringify(p.layers));
        });

        function darkenHex(hex, factor) {
            const c = hexToRgb(hex);
            const r = Math.round(c.r * factor);
            const g = Math.round(c.g * factor);
            const b = Math.round(c.b * factor);
            return '#' + [r, g, b].map(v => v.toString(16).padStart(2, '0')).join('');
        }

        function applyCustomColor(hex) {
            const darker = darkenHex(hex, 0.55);
            document.documentElement.style.setProperty('--accent', hex);
            document.documentElement.style.setProperty('--accent-2', darker);

            // Recolor all layer params in the current preset
            const preset = getCurrentPreset();
            const origAccent = preset._originalLayers ? preset.vars['--accent'] : hex;
            const origAccent2 = preset._originalLayers ? preset.vars['--accent-2'] : darker;

            preset.layers.forEach((layer, i) => {
                if (!layer.params || !layer.params.color) return;
                const orig = preset._originalLayers[i].params.color;
                if (orig === origAccent) {
                    layer.params.color = hex;
                } else if (orig === origAccent2) {
                    layer.params.color = darker;
                } else if (orig !== '#ffffff' && orig !== '#000000') {
                    // Non-white/black accent-adjacent color — remap to custom color
                    layer.params.color = hex;
                }
            });
        }

        function averageVolume(data) {
            if (!data) return 0;
            let sum = 0;
            for (let i = 0; i < data.length; i += 1) {
                sum += data[i];
            }
            return sum / data.length / 255;
        }

        function setupAudioReactive(audioElement) {
            if (!audioContext) {
                audioContext = new (window.AudioContext || window.webkitAudioContext)();
            }
            if (audioContext.state === 'suspended') {
                audioContext.resume();
            }
            if (!mediaSource) {
                mediaSource = audioContext.createMediaElementSource(audioElement);
            }
            if (analyser) {
                analyser.disconnect();
            }
            analyser = audioContext.createAnalyser();
            analyser.fftSize = 1024;
            analyserData = new Uint8Array(analyser.frequencyBinCount);
            mediaSource.connect(analyser);
            analyser.connect(audioContext.destination);
        }

        function simulatedVolume() {
            const t = performance.now() / 1000;
            return 0.35
                + Math.sin(t * 0.7) * 0.15
                + Math.sin(t * 1.3) * 0.10
                + Math.sin(t * 2.7) * 0.06
                + Math.sin(t * 4.1) * 0.04;
        }

        // ── Draw visuals (reads preset layers each frame) ─────────────────

        function drawVisuals() {
            requestAnimationFrame(drawVisuals);
            const width = canvas.clientWidth;
            const height = canvas.clientHeight;
            ctx.fillStyle = 'rgba(0, 0, 0, 0.15)';
            ctx.fillRect(0, 0, width, height);

            let volume;
            if (analyser) {
                analyser.getByteFrequencyData(analyserData);
                volume = averageVolume(analyserData);
            } else if (ytMode && ytPlaying) {
                volume = simulatedVolume();
            } else {
                volume = 0;
            }

            // Slow breathing rhythm: oscillates 0.85–1.15 over ~4 seconds
            const breathe = 1.0 + Math.sin(performance.now() / 1000 * Math.PI * 0.5) * 0.15;
            volume *= breathe;

            flowOffset += 0.004 + volume * 0.03;

            const preset = getCurrentPreset();
            const layers = preset.layers || [];

            for (const layer of layers) {
                const fn = effects[layer.effect];
                if (fn) {
                    fn(ctx, width, height, volume, flowOffset, layer.params || {});
                }
            }
        }

        // ── YouTube IFrame API ────────────────────────────────────────────

        // Global callback required by the YouTube IFrame API
        window.onYouTubeIframeAPIReady = function() {
            // API is ready; player instances are created when a video loads
        };

        function attachYouTubePlayer() {
            if (ytApiPlayer) {
                ytApiPlayer.destroy();
                ytApiPlayer = null;
            }
            ytApiPlayer = new YT.Player('yt-player', {
                events: {
                    onStateChange: function(event) {
                        if (event.data === YT.PlayerState.PLAYING) {
                            ytPlaying = true;
                        } else {
                            ytPlaying = false;
                        }
                    },
                    onReady: function() {
                        ytPlaying = true;
                    },
                },
            });
        }

        // ── Cinema / fullscreen mode ──────────────────────────────────────

        function enterCinema() {
            if (stage.requestFullscreen) {
                stage.requestFullscreen().catch(() => {
                    // User gesture expired after async fetch — show manual button
                    fullscreenBtn.hidden = false;
                });
            } else if (stage.webkitRequestFullscreen) {
                stage.webkitRequestFullscreen();
            }
        }

        function exitCinema() {
            if (document.fullscreenElement || document.webkitFullscreenElement) {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                }
            }
        }

        let overlayHideTimer = null;
        let mediaActive = false;

        function showOverlays() {
            meta.style.opacity = '1';
            shareBtn.classList.add('visible');
        }

        function hideOverlays() {
            meta.style.opacity = '0';
            shareBtn.classList.remove('visible');
        }

        function startOverlayHideTimer() {
            clearTimeout(overlayHideTimer);
            showOverlays();
            overlayHideTimer = setTimeout(hideOverlays, 4000);
        }

        function onFullscreenChange() {
            const isFullscreen = !!(document.fullscreenElement || document.webkitFullscreenElement);
            exitCinemaBtn.hidden = !isFullscreen;
            shareBtn.hidden = !(isFullscreen && mediaActive);
            fullscreenBtn.hidden = true;
            resizeCanvas();

            if (isFullscreen) {
                startOverlayHideTimer();
            } else {
                clearTimeout(overlayHideTimer);
                showOverlays();
            }
        }

        document.addEventListener('fullscreenchange', onFullscreenChange);
        document.addEventListener('webkitfullscreenchange', onFullscreenChange);

        stage.addEventListener('mousemove', () => {
            const isFullscreen = !!(document.fullscreenElement || document.webkitFullscreenElement);
            if (isFullscreen) {
                startOverlayHideTimer();
            }
        });

        exitCinemaBtn.addEventListener('click', exitCinema);
        fullscreenBtn.addEventListener('click', () => {
            fullscreenBtn.hidden = true;
            enterCinema();
        });

        shareBtn.addEventListener('click', async () => {
            const title = metaTitle.textContent || 'Quran Visuals';
            const url = window.location.href;

            if (navigator.share) {
                try {
                    await navigator.share({ title, url });
                } catch (e) {
                    // User cancelled share
                }
            } else {
                await navigator.clipboard.writeText(url);
                shareFeedback.textContent = 'Link copied!';
                shareFeedback.classList.add('show');
                setTimeout(() => shareFeedback.classList.remove('show'), 2000);
            }

            startOverlayHideTimer();
        });

        // ── Messages & meta ───────────────────────────────────────────────

        function setMessage(text, isError = false) {
            message.textContent = '';
            if (isError) {
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert';
                alertDiv.textContent = text;
                message.appendChild(alertDiv);
            } else {
                message.textContent = text;
            }
            message.hidden = false;
        }

        function setMeta(title, subtitle, warning = null) {
            metaTitle.textContent = title || 'Ready';
            metaSubtitle.textContent = subtitle || '';
            metaWarning.textContent = warning || '';
            metaWarning.style.color = warning ? 'var(--accent)' : 'var(--muted)';
            meta.hidden = false;
        }

        // ── Form handler ──────────────────────────────────────────────────

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const url = input.value.trim();
            if (!url) return;

            setMessage('Checking recitation source...', false);
            meta.hidden = true;
            ytPlayer.hidden = true;
            audioPlayer.hidden = true;
            lastReactive = false;
            ytMode = false;
            ytPlaying = false;

            try {
                const response = await fetch('/api/validate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ url }),
                });

                const data = await response.json();
                if (!data.ok) {
                    setMessage(data.reason || 'Blocked: not recognized as Quran recitation.', true);
                    return;
                }

                message.hidden = true;
                setMeta(data.title || 'Quran Recitation', data.author || 'Verified input');

                mediaActive = true;

                if (data.type === 'youtube') {
                    ytPlayer.src = data.embed_url;
                    ytPlayer.hidden = false;
                    ytMode = true;
                    lastReactive = false;
                    analyser = null;
                    // Attach YouTube API player after iframe loads
                    ytPlayer.addEventListener('load', function onLoad() {
                        ytPlayer.removeEventListener('load', onLoad);
                        if (typeof YT !== 'undefined' && YT.Player) {
                            attachYouTubePlayer();
                        }
                    });
                } else {
                    audioPlayer.src = data.audio_url;
                    audioPlayer.hidden = false;
                    audioPlayer.play().catch(() => {});
                    setupAudioReactive(audioPlayer);
                    lastReactive = true;
                }

                // Try to enter cinema mode; may fail if user gesture expired
                enterCinema();
            } catch (error) {
                setMessage('Could not validate the URL. Try again.', true);
            }
        });

        presetSelect.addEventListener('change', (event) => {
            applyPreset(event.target.value);
            stagePresetSelect.value = event.target.value;
        });

        stagePresetSelect.addEventListener('change', (event) => {
            applyPreset(event.target.value);
            presetSelect.value = event.target.value;
        });

        colorPicker.addEventListener('input', (event) => {
            stageColorPicker.value = event.target.value;
            applyCustomColor(event.target.value);
        });

        stageColorPicker.addEventListener('input', (event) => {
            colorPicker.value = event.target.value;
            applyCustomColor(event.target.value);
        });

        window.addEventListener('resize', resizeCanvas);
        applyPreset(presetSelect.value);
        resizeCanvas();
        drawVisuals();
    </script>
</body>
</html>
