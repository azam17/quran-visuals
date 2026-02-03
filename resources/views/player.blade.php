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
            --bg-1: #070a12;
            --bg-2: #0d1118;
            --bg-3: #151a24;
            --accent: #5a8fa8;
            --accent-2: #2d4a5a;
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

        .cinema-playpause {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 100;
            width: 72px;
            height: 72px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.3);
            background: rgba(0, 0, 0, 0.5);
            color: #fff;
            font-size: 0;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.4s, transform 0.2s;
            display: none;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }

        .cinema-playpause[hidden] {
            display: none;
        }

        .cinema-playpause:not([hidden]) {
            display: flex;
        }

        .cinema-playpause.visible {
            opacity: 1;
        }

        .cinema-playpause:hover {
            transform: translate(-50%, -50%) scale(1.1);
            border-color: rgba(255, 255, 255, 0.5);
            background: rgba(0, 0, 0, 0.65);
        }

        .cinema-playpause:active {
            transform: translate(-50%, -50%) scale(0.95);
        }

        .cinema-playpause svg {
            width: 28px;
            height: 28px;
            fill: #fff;
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
            <button id="playpause-btn" class="cinema-playpause" hidden>
                <svg id="play-icon" viewBox="0 0 24 24"><polygon points="6,3 20,12 6,21"/></svg>
                <svg id="pause-icon" viewBox="0 0 24 24" style="display:none;"><rect x="5" y="3" width="4" height="18"/><rect x="15" y="3" width="4" height="18"/></svg>
            </button>
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
        const playpauseBtn = document.getElementById('playpause-btn');
        const playIcon = document.getElementById('play-icon');
        const pauseIcon = document.getElementById('pause-icon');
        let isPlaying = false;

        function syncPlayPauseIcon(playing) {
            isPlaying = playing;
            playIcon.style.display = playing ? 'none' : 'block';
            pauseIcon.style.display = playing ? 'block' : 'none';
        }

        let audioContext = null;
        let analyser = null;
        let analyserData = null;
        let lastReactive = false;
        let flowOffset = 0;
        let mediaSource = null;
        let ytMode = false;
        let ytPlaying = false;
        let ytApiPlayer = null;

        // ── Audio frequency bands (smoothed) ────────────────────────────
        const audio = { bass: 0, mid: 0, high: 0, volume: 0, peak: 0, energy: 0 };
        const smoothing = 0.3; // Lower = smoother, higher = snappier

        function analyseFrequencies(data) {
            if (!data || !data.length) return;
            const len = data.length;
            // Split spectrum into 3 bands
            const bassEnd = Math.floor(len * 0.15);   // ~0-300Hz
            const midEnd = Math.floor(len * 0.5);      // ~300-2kHz
            let bassSum = 0, midSum = 0, highSum = 0;
            for (let i = 0; i < len; i++) {
                if (i < bassEnd) bassSum += data[i];
                else if (i < midEnd) midSum += data[i];
                else highSum += data[i];
            }
            const rawBass = bassSum / bassEnd / 255;
            const rawMid = midSum / (midEnd - bassEnd) / 255;
            const rawHigh = highSum / (len - midEnd) / 255;
            const rawVol = (bassSum + midSum + highSum) / len / 255;

            // Smooth transitions
            audio.bass += (rawBass - audio.bass) * smoothing;
            audio.mid += (rawMid - audio.mid) * smoothing;
            audio.high += (rawHigh - audio.high) * smoothing;
            audio.volume += (rawVol - audio.volume) * smoothing;

            // Peak — fast attack, slow decay for punch detection
            if (rawVol > audio.peak) audio.peak = rawVol;
            else audio.peak *= 0.95;

            // Energy — running accumulator for long-term intensity
            audio.energy = audio.energy * 0.98 + rawVol * 0.02;
        }

        function simulateFrequencies() {
            const t = performance.now() / 1000;
            const base = 0.35 + Math.sin(t * 0.7) * 0.15 + Math.sin(t * 1.3) * 0.1;
            audio.bass += (base * 1.2 - audio.bass) * 0.15;
            audio.mid += (base * 0.9 - audio.mid) * 0.15;
            audio.high += (base * 0.5 + Math.sin(t * 3.7) * 0.15 - audio.high) * 0.15;
            audio.volume += (base - audio.volume) * 0.15;
            if (base > audio.peak) audio.peak = base;
            else audio.peak *= 0.95;
            audio.energy = audio.energy * 0.98 + base * 0.02;
        }

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

        function hexToHsl(hex) {
            const c = hexToRgb(hex);
            const r = c.r / 255, g = c.g / 255, b = c.b / 255;
            const max = Math.max(r, g, b), min = Math.min(r, g, b);
            let h, s, l = (max + min) / 2;
            if (max === min) { h = s = 0; }
            else {
                const d = max - min;
                s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
                if (max === r) h = ((g - b) / d + (g < b ? 6 : 0)) / 6;
                else if (max === g) h = ((b - r) / d + 2) / 6;
                else h = ((r - g) / d + 4) / 6;
            }
            return { h: Math.round(h * 360), s, l };
        }

        function getLayersForHue(hue, color, darker) {
            if ((hue >= 0 && hue < 30) || hue >= 330) {
                // Red — Bars
                return [
                    { effect: 'mirroredBars', params: { color: color, count: 64 } },
                ];
            } else if (hue >= 30 && hue < 70) {
                // Orange — Waves
                return [
                    { effect: 'mirroredWave', params: { color: color, layerCount: 4 } },
                ];
            } else if (hue >= 70 && hue < 170) {
                // Green — Circular
                return [
                    { effect: 'circularWave', params: { color: color, rings: 3 } },
                ];
            } else if (hue >= 170 && hue < 260) {
                // Blue — Waves
                return [
                    { effect: 'mirroredWave', params: { color: color, layerCount: 4 } },
                ];
            } else {
                // Purple — Oscilloscope
                return [
                    { effect: 'oscilloscope', params: { color: color, freqX: 3, freqY: 2 } },
                ];
            }
        }

        // ── Effect registry ───────────────────────────────────────────────
        // Each effect receives: ctx, w, h, audio{bass,mid,high,volume,peak,energy}, flowOffset, params

        const effects = {

            gradientGlow(ctx, w, h, a, flowOffset, params) {
                const color = params.color || '#ffffff';
                const intensity = 0.02 + a.bass * 0.2 + a.peak * 0.08;
                const gradient = ctx.createLinearGradient(0, 0, w, h);
                gradient.addColorStop(0, rgba(color, intensity));
                gradient.addColorStop(0.5, rgba(color, intensity * 0.2));
                gradient.addColorStop(1, 'rgba(0, 0, 0, 0)');
                ctx.fillStyle = gradient;
                ctx.fillRect(0, 0, w, h);
            },

            concentricArcs(ctx, w, h, a, flowOffset, params) {
                const color = params.color || '#ffffff';
                const count = params.count || 5;
                ctx.save();
                ctx.translate(w / 2, h / 2);
                ctx.shadowBlur = 10 + a.bass * 40;
                ctx.shadowColor = color;
                for (let i = 0; i < count; i++) {
                    const expand = a.bass * 180 + a.peak * 60;
                    const radius = (Math.min(w, h) / 5) + i * 50 + expand;
                    const alpha = 0.08 + a.mid * 0.4 + a.peak * 0.15;
                    ctx.beginPath();
                    ctx.strokeStyle = rgba(color, alpha);
                    ctx.lineWidth = 1.5 + a.bass * 4;
                    const arcLen = Math.PI * (0.8 + a.volume * 0.8);
                    ctx.arc(0, 0, radius, flowOffset + i, arcLen + flowOffset + i);
                    ctx.stroke();
                }
                ctx.shadowBlur = 0;
                ctx.restore();
            },

            particles(ctx, w, h, a, flowOffset, params) {
                const color = params.color || '#ffffff';
                const baseCount = params.count || 60;
                const count = Math.floor(baseCount + a.peak * 20);
                const shape = params.shape || 'square';
                ctx.save();
                ctx.globalCompositeOperation = 'lighter';
                const speed = 0.6 + a.energy * 2;
                for (let i = 0; i < count; i++) {
                    const x = (Math.sin(flowOffset * speed + i * 1.1) * (0.35 + a.high * 0.15) + 0.5) * w;
                    const y = (Math.cos(flowOffset * speed * 0.6 + i * 0.9) * (0.35 + a.bass * 0.15) + 0.5) * h;
                    const size = 1 + a.mid * 3 + a.peak * 2;
                    const alpha = a.volume * 0.35 + a.peak * 0.15;
                    if (alpha < 0.01) continue;
                    ctx.fillStyle = rgba(color, alpha);
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

            waveLine(ctx, w, h, a, flowOffset, params) {
                const color = params.color || '#ffffff';
                const yPos = params.yPosition || 0.78;
                const amplitude = a.bass * 50 + a.mid * 25 + a.peak * 10;
                if (amplitude < 1) return;
                const freq = 0.015 + a.high * 0.01;
                const alpha = a.volume * 0.55 + a.peak * 0.15;
                if (alpha < 0.01) return;
                ctx.save();
                ctx.shadowBlur = a.bass * 20;
                ctx.shadowColor = color;
                ctx.strokeStyle = rgba(color, alpha);
                ctx.lineWidth = 0.8 + a.bass * 2;
                ctx.beginPath();
                for (let x = 0; x < w; x += 8) {
                    const wave = Math.sin(flowOffset * 2.5 + x * freq) * amplitude
                        + Math.sin(flowOffset * 1.2 + x * freq * 2.3) * (a.high * 12);
                    ctx.lineTo(x, h * yPos + wave);
                }
                ctx.stroke();
                ctx.shadowBlur = 0;
                ctx.restore();
            },

            pulseRing(ctx, w, h, a, flowOffset, params) {
                const color = params.color || '#ffffff';
                const maxRadius = Math.min(w, h) * 0.4;
                const rings = 3 + Math.floor(a.peak * 3);
                ctx.save();
                ctx.translate(w / 2, h / 2);
                ctx.shadowBlur = 15 + a.bass * 45;
                ctx.shadowColor = color;
                for (let i = 0; i < rings; i++) {
                    const pulse = Math.sin(flowOffset * 3 + i * 0.7) * 0.5 + 0.5;
                    const r = maxRadius * (0.2 + pulse * 0.3 + a.bass * 0.4) + i * 25;
                    const alpha = (0.2 - i * 0.03) + a.volume * 0.35;
                    ctx.beginPath();
                    ctx.strokeStyle = rgba(color, Math.max(0.02, alpha));
                    ctx.lineWidth = 2 + a.bass * 4 - i * 0.3;
                    ctx.arc(0, 0, r, 0, Math.PI * 2);
                    ctx.stroke();
                }
                ctx.shadowBlur = 0;
                ctx.restore();
            },

            starField(ctx, w, h, a, flowOffset, params) {
                const color = params.color || '#ffffff';
                const count = params.count || 100;
                ctx.save();
                ctx.globalCompositeOperation = 'lighter';
                for (let i = 0; i < count; i++) {
                    const sx = ((Math.sin(i * 127.1) * 43758.5453) % 1 + 1) % 1;
                    const sy = ((Math.sin(i * 269.5) * 18642.3217) % 1 + 1) % 1;
                    const x = sx * w;
                    const y = sy * h;
                    const twinkle = Math.sin(flowOffset * 2 + i * 1.7) * 0.5 + 0.5;
                    const burst = (i % 7 === 0) ? a.peak * 6 : 0;
                    const size = 1 + twinkle * 2 + a.high * 4 + burst;
                    ctx.fillStyle = rgba(color, 0.15 + twinkle * 0.4 + a.volume * 0.25);
                    ctx.beginPath();
                    ctx.arc(x, y, size, 0, Math.PI * 2);
                    ctx.fill();
                }
                ctx.restore();
            },

            verticalBars(ctx, w, h, a, flowOffset, params) {
                const color = params.color || '#ffffff';
                const count = params.count || 48;
                const barWidth = w / count;
                ctx.save();
                ctx.globalCompositeOperation = 'lighter';
                for (let i = 0; i < count; i++) {
                    // Map each bar to a simulated frequency bin
                    const ratio = i / count;
                    let barAudio;
                    if (ratio < 0.33) barAudio = a.bass;
                    else if (ratio < 0.66) barAudio = a.mid;
                    else barAudio = a.high;
                    const jitter = Math.sin(flowOffset * 3 + i * 0.5) * 0.15;
                    const barHeight = (barAudio * 0.7 + jitter + a.peak * 0.2) * h * 0.6;
                    const x = i * barWidth;
                    ctx.fillStyle = rgba(color, 0.1 + barAudio * 0.45);
                    ctx.fillRect(x, h - Math.max(2, barHeight), barWidth - 1, Math.max(2, barHeight));
                }
                ctx.restore();
            },

            nebulaClouds(ctx, w, h, a, flowOffset, params) {
                const color = params.color || '#ffffff';
                const c = hexToRgb(color);
                ctx.save();
                ctx.globalCompositeOperation = 'lighter';
                const blobs = [
                    { xf: 0.3, yf: 0.4, rf: 0.25, speed: 0.7, band: 'bass' },
                    { xf: 0.7, yf: 0.6, rf: 0.3, speed: 1.1, band: 'mid' },
                    { xf: 0.5, yf: 0.3, rf: 0.2, speed: 0.9, band: 'high' },
                ];
                for (const blob of blobs) {
                    const bandVal = a[blob.band];
                    const bx = w * blob.xf + Math.sin(flowOffset * blob.speed) * w * (0.06 + bandVal * 0.06);
                    const by = h * blob.yf + Math.cos(flowOffset * blob.speed * 0.8) * h * 0.05;
                    const br = Math.min(w, h) * blob.rf + bandVal * 80 + a.peak * 30;
                    const grad = ctx.createRadialGradient(bx, by, 0, bx, by, br);
                    grad.addColorStop(0, `rgba(${c.r}, ${c.g}, ${c.b}, ${0.06 + bandVal * 0.2})`);
                    grad.addColorStop(1, `rgba(${c.r}, ${c.g}, ${c.b}, 0)`);
                    ctx.fillStyle = grad;
                    ctx.fillRect(bx - br, by - br, br * 2, br * 2);
                }
                ctx.restore();
            },

            geometricMandala(ctx, w, h, a, flowOffset, params) {
                const color = params.color || '#ffffff';
                const sides = params.sides || 6;
                const layerCount = 4 + Math.floor(a.peak * 3);
                ctx.save();
                ctx.translate(w / 2, h / 2);
                ctx.shadowBlur = 8 + a.bass * 30;
                ctx.shadowColor = color;
                for (let layer = 0; layer < layerCount; layer++) {
                    const expand = a.bass * 120 + a.mid * 40;
                    const radius = 50 + layer * 45 + expand;
                    const speed = (0.4 + a.energy * 0.8 + layer * 0.15) * (layer % 2 === 0 ? 1 : -1);
                    const rotation = flowOffset * speed;
                    ctx.beginPath();
                    ctx.strokeStyle = rgba(color, 0.1 + a.volume * 0.4 - layer * 0.01);
                    ctx.lineWidth = 2 + a.bass * 3 - layer * 0.15;
                    for (let i = 0; i <= sides; i++) {
                        const angle = (Math.PI * 2 / sides) * i + rotation;
                        const wobble = 1 + Math.sin(flowOffset * 2 + i + layer) * a.high * 0.15;
                        const x = Math.cos(angle) * radius * wobble;
                        const y = Math.sin(angle) * radius * wobble;
                        if (i === 0) ctx.moveTo(x, y);
                        else ctx.lineTo(x, y);
                    }
                    ctx.closePath();
                    ctx.stroke();
                }
                ctx.shadowBlur = 0;
                ctx.restore();
            },

            mirroredWave(ctx, w, h, a, flowOffset, params) {
                const color = params.color || '#ffffff';
                const layerCount = params.layerCount || 3;
                const yCenter = (params.yCenter || 0.5) * h;
                const activity = a.volume + a.peak * 0.5;
                if (activity < 0.01) return;
                ctx.save();
                ctx.globalCompositeOperation = 'lighter';
                for (let layer = 0; layer < layerCount; layer++) {
                    const band = layer === 0 ? a.bass : layer === 1 ? a.mid : layer === 2 ? a.high : (a.bass + a.mid) * 0.5;
                    const amplitude = band * 55 + a.peak * 20 - layer * 5;
                    if (amplitude < 0.5) continue;
                    const freq = 0.005 + layer * 0.003 + a.high * 0.002;
                    const speed = 1.0 + layer * 0.4;
                    const alpha = band * 0.3 + a.peak * 0.1 - layer * 0.02;
                    if (alpha < 0.01) continue;
                    ctx.shadowBlur = 6 + band * 18;
                    ctx.shadowColor = rgba(color, alpha * 0.6);
                    ctx.strokeStyle = rgba(color, alpha);
                    ctx.lineWidth = 0.6 + band * 1.2;
                    ctx.beginPath();
                    for (let x = 0; x <= w; x += 4) {
                        const wave = Math.sin(flowOffset * speed + x * freq) * amplitude
                            + Math.sin(flowOffset * speed * 0.5 + x * freq * 2.3) * (a.high * 8);
                        const py = yCenter + wave;
                        if (x === 0) ctx.moveTo(x, py);
                        else ctx.lineTo(x, py);
                    }
                    ctx.stroke();
                }
                ctx.shadowBlur = 0;
                ctx.restore();
            },

            circularWave(ctx, w, h, a, flowOffset, params) {
                const color = params.color || '#ffffff';
                const rings = params.rings || 2;
                const baseRadius = params.baseRadius || 0.22 * Math.min(w, h);
                const activity = a.volume + a.peak * 0.5;
                if (activity < 0.01) return;
                ctx.save();
                ctx.translate(w / 2, h / 2);
                ctx.globalCompositeOperation = 'lighter';
                ctx.shadowBlur = a.bass * 30;
                ctx.shadowColor = color;
                const segments = 180;
                for (let ring = 0; ring < rings; ring++) {
                    const rBase = baseRadius + ring * 35 + a.bass * 60;
                    const waveAmp = a.mid * 55 + a.peak * 25 - ring * 5;
                    const detail = a.high * 15;
                    const alpha = a.volume * 0.3 + a.peak * 0.1 - ring * 0.03;
                    if (alpha < 0.01) continue;
                    ctx.beginPath();
                    for (let i = 0; i <= segments; i++) {
                        const angle = (Math.PI * 2 / segments) * i;
                        const wave = Math.sin(angle * 8 + flowOffset * 2 + ring) * waveAmp
                            + Math.sin(angle * 16 + flowOffset * 3.5) * detail;
                        const r = rBase + wave;
                        const x = Math.cos(angle) * r;
                        const y = Math.sin(angle) * r;
                        if (i === 0) ctx.moveTo(x, y);
                        else ctx.lineTo(x, y);
                    }
                    ctx.closePath();
                    ctx.strokeStyle = rgba(color, Math.min(1, alpha));
                    ctx.lineWidth = 0.6 + a.bass * 1.5 - ring * 0.2;
                    ctx.stroke();
                }
                ctx.shadowBlur = 0;
                ctx.restore();
            },

            mirroredBars(ctx, w, h, a, flowOffset, params) {
                const color = params.color || '#ffffff';
                const count = params.count || 64;
                const totalWidth = w * 0.85;
                const offsetX = (w - totalWidth) / 2;
                const step = totalWidth / count;
                const barWidth = Math.max(1, step * 0.5);
                const centerY = h / 2;
                const activity = a.volume + a.peak * 0.5;
                if (activity < 0.01) return;
                ctx.save();
                ctx.globalCompositeOperation = 'lighter';
                for (let i = 0; i < count; i++) {
                    const ratio = i / count;
                    let barAudio;
                    if (ratio < 0.33) barAudio = a.bass;
                    else if (ratio < 0.66) barAudio = a.mid;
                    else barAudio = a.high;
                    const jitter = Math.sin(flowOffset * 3 + i * 0.5) * barAudio * 0.08;
                    const barHeight = (barAudio * 0.5 + jitter + a.peak * 0.15) * h * 0.3;
                    if (barHeight < 1) continue;
                    const x = offsetX + i * step;
                    const alpha = barAudio * 0.3 + a.peak * 0.08;
                    if (alpha < 0.01) continue;
                    ctx.fillStyle = rgba(color, alpha);
                    ctx.fillRect(x, centerY - barHeight, barWidth, barHeight);
                    ctx.fillRect(x, centerY + 1, barWidth, barHeight);
                }
                ctx.restore();
            },

            oscilloscope(ctx, w, h, a, flowOffset, params) {
                const color = params.color || '#ffffff';
                const freqX = params.freqX || 3;
                const freqY = params.freqY || 2;
                const size = (params.size || 0.35) * Math.min(w, h);
                const activity = a.volume + a.peak * 0.5;
                if (activity < 0.01) return;
                ctx.save();
                ctx.translate(w / 2, h / 2);
                ctx.shadowBlur = a.bass * 25;
                ctx.shadowColor = color;
                const scale = size * (0.3 + a.bass * 1.8);
                const phase = flowOffset * (0.5 + a.mid * 1.5);
                const harmonic = a.high * 0.3;
                const alpha = a.volume * 0.3 + a.peak * 0.12;
                if (alpha < 0.01) { ctx.restore(); return; }
                const points = 360;
                ctx.beginPath();
                for (let i = 0; i <= points; i++) {
                    const t = (i / points) * Math.PI * 2;
                    const x = Math.sin(freqX * t + phase + Math.sin(t * 5) * harmonic) * scale;
                    const y = Math.sin(freqY * t) * scale;
                    if (i === 0) ctx.moveTo(x, y);
                    else ctx.lineTo(x, y);
                }
                ctx.closePath();
                ctx.strokeStyle = rgba(color, alpha);
                ctx.lineWidth = 0.6 + a.bass * 1.5;
                ctx.stroke();
                ctx.shadowBlur = 0;
                ctx.restore();
            },

            horizonGlow(ctx, w, h, a, flowOffset, params) {
                const color = params.color || '#ffffff';
                const yPosition = params.yPosition || 0.65;
                const rays = params.rays || 0;
                const c = hexToRgb(color);
                const yCenter = h * yPosition;
                const spread = 20 + a.bass * 100;
                const intensity = 0.03 + a.volume * 0.3 + a.peak * 0.1;
                if (intensity < 0.02) return;
                ctx.save();
                ctx.globalCompositeOperation = 'lighter';
                const grad = ctx.createLinearGradient(0, yCenter - spread, 0, yCenter + spread);
                grad.addColorStop(0, `rgba(${c.r}, ${c.g}, ${c.b}, 0)`);
                grad.addColorStop(0.35, `rgba(${c.r}, ${c.g}, ${c.b}, ${intensity * 0.3})`);
                grad.addColorStop(0.5, `rgba(${c.r}, ${c.g}, ${c.b}, ${intensity})`);
                grad.addColorStop(0.65, `rgba(${c.r}, ${c.g}, ${c.b}, ${intensity * 0.3})`);
                grad.addColorStop(1, `rgba(${c.r}, ${c.g}, ${c.b}, 0)`);
                ctx.fillStyle = grad;
                ctx.fillRect(0, yCenter - spread, w, spread * 2);
                if (rays > 0 && a.high > 0.02) {
                    for (let i = 0; i < rays; i++) {
                        const rx = (w / (rays + 1)) * (i + 1) + Math.sin(flowOffset + i) * 20;
                        const rayHeight = a.high * 150 + Math.sin(flowOffset * 2 + i * 1.3) * a.peak * 30;
                        const rayWidth = 1 + a.peak * 4;
                        const rayAlpha = a.high * 0.18;
                        if (rayAlpha < 0.01 || rayHeight < 1) continue;
                        const rayGrad = ctx.createLinearGradient(0, yCenter, 0, yCenter - rayHeight);
                        rayGrad.addColorStop(0, `rgba(${c.r}, ${c.g}, ${c.b}, ${rayAlpha})`);
                        rayGrad.addColorStop(1, `rgba(${c.r}, ${c.g}, ${c.b}, 0)`);
                        ctx.fillStyle = rayGrad;
                        ctx.fillRect(rx - rayWidth / 2, yCenter - rayHeight, rayWidth, rayHeight);
                    }
                }
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

            const preset = getCurrentPreset();
            const hsl = hexToHsl(hex);
            preset.layers = getLayersForHue(hsl.h, hex, darker);
        }

        // averageVolume kept for backwards compat but unused in draw loop
        function averageVolume(data) {
            if (!data) return 0;
            let sum = 0;
            for (let i = 0; i < data.length; i++) sum += data[i];
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

        // simulatedVolume() removed — replaced by simulateFrequencies()

        // ── Draw visuals (reads preset layers each frame) ─────────────────

        function drawVisuals() {
            requestAnimationFrame(drawVisuals);
            const width = canvas.clientWidth;
            const height = canvas.clientHeight;

            // Faster fade when loud = crisper visuals, slower fade when quiet = trails
            const fade = 0.12 + audio.volume * 0.08;
            ctx.fillStyle = `rgba(0, 0, 0, ${fade})`;
            ctx.fillRect(0, 0, width, height);

            // Analyse audio
            if (analyser) {
                analyser.getByteFrequencyData(analyserData);
                analyseFrequencies(analyserData);
            } else if (ytMode && ytPlaying) {
                simulateFrequencies();
            } else {
                // Decay to zero when nothing is playing
                audio.bass *= 0.95; audio.mid *= 0.95; audio.high *= 0.95;
                audio.volume *= 0.95; audio.peak *= 0.95; audio.energy *= 0.98;
            }

            // Flow speed driven by audio energy
            flowOffset += 0.003 + audio.volume * 0.04 + audio.peak * 0.02;

            const preset = getCurrentPreset();
            const layers = preset.layers || [];

            for (const layer of layers) {
                const fn = effects[layer.effect];
                if (fn) {
                    fn(ctx, width, height, audio, flowOffset, layer.params || {});
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
                            syncPlayPauseIcon(true);
                        } else {
                            ytPlaying = false;
                            syncPlayPauseIcon(false);
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
            playpauseBtn.classList.add('visible');
        }

        function hideOverlays() {
            meta.style.opacity = '0';
            shareBtn.classList.remove('visible');
            playpauseBtn.classList.remove('visible');
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
            playpauseBtn.hidden = !(isFullscreen && mediaActive);
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

        playpauseBtn.addEventListener('click', () => {
            if (ytMode && ytApiPlayer) {
                if (ytPlaying) ytApiPlayer.pauseVideo();
                else ytApiPlayer.playVideo();
            } else if (!audioPlayer.hidden) {
                if (audioPlayer.paused) audioPlayer.play().catch(() => {});
                else audioPlayer.pause();
            }
            startOverlayHideTimer();
        });

        document.addEventListener('keydown', (e) => {
            if (e.code === 'Space' && (document.fullscreenElement || document.webkitFullscreenElement)) {
                e.preventDefault();
                playpauseBtn.click();
            }
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

            // Stop any currently playing media
            if (!audioPlayer.paused) audioPlayer.pause();
            audioPlayer.removeAttribute('src');
            audioPlayer.hidden = true;
            if (ytApiPlayer) {
                try { ytApiPlayer.stopVideo(); } catch (e) {}
            }
            ytPlayer.removeAttribute('src');
            ytPlayer.hidden = true;
            syncPlayPauseIcon(false);

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

        audioPlayer.addEventListener('play', () => syncPlayPauseIcon(true));
        audioPlayer.addEventListener('pause', () => syncPlayPauseIcon(false));

        window.addEventListener('resize', resizeCanvas);
        applyPreset(presetSelect.value);
        resizeCanvas();
        drawVisuals();
    </script>
</body>
</html>
