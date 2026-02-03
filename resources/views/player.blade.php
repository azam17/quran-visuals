<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quran Visuals</title>
    <meta property="og:title" content="Quran Visuals — Cinematic Quran Recitation Player">
    <meta property="og:description" content="Experience Quran recitations with beautiful audio-reactive visualizations">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ asset('og-image.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Quran Visuals">
    <meta name="twitter:description" content="Cinematic audio-reactive Quran recitation player">
    <meta name="twitter:image" content="{{ asset('og-image.png') }}">
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
                radial-gradient(circle at 20% 20%, rgba(255, 255, 255, 0.02) 0, transparent 35%),
                radial-gradient(circle at 70% 30%, rgba(255, 255, 255, 0.015) 0, transparent 40%);
            pointer-events: none;
            mix-blend-mode: screen;
            z-index: -1;
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
            background: linear-gradient(120deg, color-mix(in srgb, var(--accent) 18%, transparent), color-mix(in srgb, var(--accent) 35%, transparent));
            cursor: pointer;
            font-weight: 600;
            letter-spacing: 0.04em;
        }

        .stage {
            position: relative;
            border-radius: 24px;
            overflow: hidden;
            background: rgb(5, 5, 7);
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
            background: linear-gradient(120deg, color-mix(in srgb, var(--accent) 25%, transparent), color-mix(in srgb, var(--accent) 50%, transparent));
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
            background: color-mix(in srgb, var(--accent) 90%, transparent);
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

        /* ── Toast notification ──────────────────────────────────────── */
        .toast {
            position: fixed;
            bottom: 32px;
            left: 50%;
            transform: translateX(-50%) translateY(20px);
            padding: 10px 22px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(20, 22, 28, 0.92);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            color: var(--text);
            font-family: "Inter", sans-serif;
            font-size: 0.88rem;
            z-index: 9999;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s, transform 0.3s;
        }

        .toast.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        /* ── Control icon buttons (repeat, screenshot, share, shortcuts) ── */
        .ctrl-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            position: relative;
            width: 38px;
            height: 38px;
            padding: 0;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, 0.14);
            background: rgba(8, 9, 12, 0.7);
            color: var(--muted);
            cursor: pointer;
            transition: color 0.2s, border-color 0.2s, background 0.2s;
        }

        .ctrl-btn:hover {
            color: var(--text);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .ctrl-btn.active {
            color: var(--accent);
            border-color: var(--accent);
        }

        .ctrl-btn svg {
            width: 18px;
            height: 18px;
            fill: currentColor;
        }

        .ctrl-btn .badge {
            position: absolute;
            top: -4px;
            right: -4px;
            min-width: 18px;
            height: 18px;
            padding: 0 4px;
            border-radius: 9px;
            background: var(--accent);
            color: #fff;
            font-size: 0.65rem;
            font-family: "Inter", sans-serif;
            font-weight: 600;
            line-height: 18px;
            text-align: center;
            pointer-events: none;
        }

        /* ── Keyboard shortcuts modal ───────────────────────────────────── */
        .shortcuts-overlay {
            position: fixed;
            inset: 0;
            z-index: 10000;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            display: none;
            place-items: center;
        }

        .shortcuts-overlay.open {
            display: grid;
        }

        .shortcuts-modal {
            width: min(440px, 90vw);
            padding: 28px 32px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(14, 16, 22, 0.97);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .shortcuts-modal h2 {
            font-family: "Cinzel", serif;
            font-size: 1.15rem;
            margin-bottom: 18px;
            letter-spacing: 0.04em;
        }

        .shortcuts-modal .shortcut-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 7px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-family: "Inter", sans-serif;
            font-size: 0.88rem;
        }

        .shortcuts-modal .shortcut-row:last-child {
            border-bottom: none;
        }

        .shortcuts-modal .shortcut-row .action {
            color: var(--muted);
        }

        .shortcuts-modal kbd {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 5px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            background: rgba(255, 255, 255, 0.06);
            font-family: "Inter", sans-serif;
            font-size: 0.8rem;
            color: var(--text);
            min-width: 28px;
            text-align: center;
        }

        /* ── Surah name display ─────────────────────────────────────────── */
        #surah-display {
            position: absolute;
            top: 18%;
            left: 0;
            right: 0;
            z-index: 50;
            text-align: center;
            font-family: "Cinzel", serif;
            font-size: clamp(1.6rem, 2.5vw + 1rem, 3rem);
            font-weight: 600;
            letter-spacing: 0.06em;
            color: rgba(255, 255, 255, 0.7);
            text-shadow: 0 2px 20px rgba(0, 0, 0, 0.6);
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.8s ease;
        }

        #surah-display.visible {
            opacity: 1;
        }

        .stage:fullscreen #surah-display,
        .stage:-webkit-full-screen #surah-display {
            font-size: clamp(2rem, 3vw + 1.2rem, 4rem);
            top: 12%;
        }

        /* ── Screenshot flash ───────────────────────────────────────────── */
        .screenshot-flash {
            position: absolute;
            inset: 0;
            background: #fff;
            opacity: 0;
            pointer-events: none;
            z-index: 500;
            animation: flashAnim 0.4s ease-out forwards;
        }

        @keyframes flashAnim {
            0% { opacity: 0.35; }
            100% { opacity: 0; }
        }

        /* ── Curated reciters grid ──────────────────────────────────────── */
        .reciters-panel {
            position: absolute;
            inset: 0;
            z-index: 20;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .reciters-panel[hidden] {
            display: none;
        }

        .reciters-inner {
            text-align: center;
            max-width: 600px;
        }

        .reciters-inner h2 {
            font-family: "Cinzel", serif;
            font-size: 1.2rem;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
        }

        .reciters-inner p {
            font-family: "Inter", sans-serif;
            font-size: 0.85rem;
            color: var(--muted);
            margin-bottom: 20px;
        }

        .reciters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
        }

        .reciter-card {
            padding: 14px 12px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(8, 9, 12, 0.5);
            color: var(--text);
            font-family: "Cinzel", serif;
            font-size: 0.82rem;
            letter-spacing: 0.03em;
            cursor: pointer;
            transition: border-color 0.2s, background 0.2s, transform 0.15s;
        }

        .reciter-card:hover {
            border-color: var(--accent);
            background: rgba(90, 143, 168, 0.08);
            transform: translateY(-2px);
        }

        /* ── Footer shortcuts hint ──────────────────────────────────────── */
        .shortcuts-hint {
            position: fixed;
            bottom: 8px;
            right: 12px;
            z-index: 100;
            font-family: "Inter", sans-serif;
            font-size: 0.72rem;
            color: rgba(178, 176, 189, 0.4);
            cursor: pointer;
            transition: color 0.2s;
        }

        .shortcuts-hint:hover {
            color: var(--muted);
        }

        /* ── Subtitle overlay ──────────────────────────────────────── */
        #subtitle-overlay {
            position: absolute;
            bottom: 8%;
            left: 0;
            right: 0;
            z-index: 50;
            text-align: center;
            direction: rtl;
            pointer-events: none;
            padding: 0 24px;
        }

        #subtitle-overlay .segment-text {
            display: inline;
            font-family: "Cinzel", serif;
            font-size: clamp(1.2rem, 2vw + 0.6rem, 2.4rem);
            line-height: 1.6;
            color: var(--text);
            text-shadow: 0 2px 12px rgba(0, 0, 0, 0.8), 0 0 4px rgba(0, 0, 0, 0.6);
        }

        #subtitle-overlay .word {
            display: inline-block;
            padding: 0 3px;
            transition: color 0.15s, text-shadow 0.15s;
        }

        #subtitle-overlay .word.active {
            color: var(--accent);
            text-shadow: 0 0 18px color-mix(in srgb, var(--accent) 60%, transparent),
                         0 2px 12px rgba(0, 0, 0, 0.8);
        }

        .stage:fullscreen #subtitle-overlay,
        .stage:-webkit-full-screen #subtitle-overlay {
            bottom: 10%;
            font-size: clamp(1.6rem, 2.5vw + 0.8rem, 3.2rem);
        }

        @media (max-width: 760px) {
            header {
                grid-template-columns: 1fr;
            }

            .controls input {
                width: 100%;
                min-width: unset;
            }

            .reciters-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            #subtitle-overlay .segment-text {
                font-size: clamp(0.9rem, 4vw, 1.6rem);
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

                {{-- Repeat/Loop --}}
                <button type="button" class="ctrl-btn" id="repeat-btn" title="Repeat: Off">
                    <svg viewBox="0 0 24 24"><path d="M7 7h10v3l4-4-4-4v3H5v6h2V7zm10 10H7v-3l-4 4 4 4v-3h12v-6h-2v4z"/></svg>
                    <span class="badge" id="repeat-badge" style="display:none;">1</span>
                </button>

                {{-- Screenshot --}}
                <button type="button" class="ctrl-btn" id="screenshot-btn" title="Capture Screenshot">
                    <svg viewBox="0 0 24 24"><path d="M9 2L7.17 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2h-3.17L15 2H9zm3 15c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.65 0-3 1.35-3 3s1.35 3 3 3 3-1.35 3-3-1.35-3-3-3z"/></svg>
                </button>

                {{-- Share --}}
                <button type="button" class="ctrl-btn" id="share-link-btn" title="Share Link">
                    <svg viewBox="0 0 24 24"><path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92s2.92-1.31 2.92-2.92-1.31-2.92-2.92-2.92z"/></svg>
                </button>

                {{-- Keyboard Shortcuts --}}
                <button type="button" class="ctrl-btn" id="shortcuts-btn" title="Keyboard Shortcuts (?)">
                    <svg viewBox="0 0 24 24"><path d="M20 5H4c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm-9 3h2v2h-2V8zm0 3h2v2h-2v-2zM8 8h2v2H8V8zm0 3h2v2H8v-2zm-1 2H5v-2h2v2zm0-3H5V8h2v2zm9 7H8v-2h8v2zm0-4h-2v-2h2v2zm0-3h-2V8h2v2zm3 3h-2v-2h2v2zm0-3h-2V8h2v2z"/></svg>
                </button>
            </form>
        </header>

        <section class="stage" id="stage">
            <canvas id="visuals"></canvas>
            <div id="surah-display"></div>
            <div id="subtitle-overlay"></div>
            <div class="player">
                <iframe id="yt-player" title="YouTube Quran Player" allow="autoplay; fullscreen" allowfullscreen hidden></iframe>
                <audio id="audio-player" controls hidden></audio>
            </div>
            <div class="message" id="message">
                Paste a Quran YouTube link or a direct audio file to begin.
            </div>
            <div class="reciters-panel" id="reciters-panel">
                <div class="reciters-inner">
                    <h2>Discover Reciters</h2>
                    <p>Select a reciter to start listening instantly</p>
                    <div class="reciters-grid" id="reciters-grid"></div>
                </div>
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

    {{-- Keyboard shortcuts modal --}}
    <div class="shortcuts-overlay" id="shortcuts-overlay">
        <div class="shortcuts-modal">
            <h2>Keyboard Shortcuts</h2>
            <div class="shortcut-row"><span class="action">Play / Pause</span> <kbd>Space</kbd></div>
            <div class="shortcut-row"><span class="action">Mute / Unmute</span> <kbd>M</kbd></div>
            <div class="shortcut-row"><span class="action">Toggle Fullscreen</span> <kbd>F</kbd></div>
            <div class="shortcut-row"><span class="action">Seek backward 10s</span> <kbd>&larr;</kbd></div>
            <div class="shortcut-row"><span class="action">Seek forward 10s</span> <kbd>&rarr;</kbd></div>
            <div class="shortcut-row"><span class="action">Volume up</span> <kbd>&uarr;</kbd></div>
            <div class="shortcut-row"><span class="action">Volume down</span> <kbd>&darr;</kbd></div>
            <div class="shortcut-row"><span class="action">Cycle loop mode</span> <kbd>L</kbd></div>
            <div class="shortcut-row"><span class="action">Show / hide this help</span> <kbd>?</kbd></div>
            <div class="shortcut-row"><span class="action">Close / exit fullscreen</span> <kbd>Esc</kbd></div>
        </div>
    </div>

    {{-- Shortcuts hint --}}
    <span class="shortcuts-hint" id="shortcuts-hint">Press ? for shortcuts</span>

    {{-- Toast container --}}
    <div class="toast" id="toast"></div>

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

        // ── New feature elements ──────────────────────────────────────────
        const toast = document.getElementById('toast');
        const repeatBtn = document.getElementById('repeat-btn');
        const repeatBadge = document.getElementById('repeat-badge');
        const screenshotBtn = document.getElementById('screenshot-btn');
        const shareLinkBtn = document.getElementById('share-link-btn');
        const shortcutsBtn = document.getElementById('shortcuts-btn');
        const shortcutsOverlay = document.getElementById('shortcuts-overlay');
        const shortcutsHint = document.getElementById('shortcuts-hint');
        const surahDisplay = document.getElementById('surah-display');
        const recitersPanel = document.getElementById('reciters-panel');
        const recitersGrid = document.getElementById('reciters-grid');

        // ── Toast utility ─────────────────────────────────────────────────
        let toastTimer = null;
        function showToast(msg) {
            toast.textContent = msg;
            toast.classList.add('show');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => toast.classList.remove('show'), 2500);
        }

        // ── Repeat / Loop Mode ────────────────────────────────────────────
        let repeatMode = 'off'; // 'off' | 'one' | 'all'

        function cycleRepeatMode() {
            if (repeatMode === 'off') repeatMode = 'one';
            else if (repeatMode === 'one') repeatMode = 'all';
            else repeatMode = 'off';
            updateRepeatUI();
        }

        function updateRepeatUI() {
            if (repeatMode === 'off') {
                repeatBtn.classList.remove('active');
                repeatBadge.style.display = 'none';
                repeatBtn.title = 'Repeat: Off';
            } else if (repeatMode === 'one') {
                repeatBtn.classList.add('active');
                repeatBadge.style.display = '';
                repeatBtn.title = 'Repeat: One';
            } else {
                repeatBtn.classList.add('active');
                repeatBadge.style.display = 'none';
                repeatBtn.title = 'Repeat: All';
            }
        }

        repeatBtn.addEventListener('click', cycleRepeatMode);

        // Handle audio ended for repeat
        audioPlayer.addEventListener('ended', () => {
            if (repeatMode === 'one' || repeatMode === 'all') {
                audioPlayer.currentTime = 0;
                audioPlayer.play().catch(() => {});
            }
        });

        // ── Surah Name Display ────────────────────────────────────────────
        let surahHideTimer = null;

        function extractSurahName(rawTitle) {
            if (!rawTitle) return '';
            let name = rawTitle;
            // Strip file extensions
            name = name.replace(/\.(mp3|wav|ogg|m4a|aac|webm|opus)$/i, '');
            // Underscores/hyphens to spaces
            name = name.replace(/[_-]/g, ' ');
            // Strip common prefixes
            name = name.replace(/^(surah|surat|سورة)\s*/i, '');
            // Strip reciter patterns like "by Sheikh X", "- Reciter Name", etc.
            name = name.replace(/\s*[-–—|]\s*(by\s+)?(sheikh|imam|qari|hafiz)\s+.*/i, '');
            name = name.replace(/\s+by\s+(sheikh|imam|qari|hafiz)\s+.*/i, '');
            // Strip common YouTube suffixes
            name = name.replace(/\s*[-–—|]\s*(full|complete|hd|4k|audio|video|recitation|tilawat|tilawah).*$/i, '');
            // Clean up extra whitespace
            name = name.replace(/\s+/g, ' ').trim();
            return name;
        }

        function showSurahName(title) {
            const name = extractSurahName(title);
            if (!name) return;
            surahDisplay.textContent = name;
            surahDisplay.classList.add('visible');
            clearTimeout(surahHideTimer);
            surahHideTimer = setTimeout(() => surahDisplay.classList.remove('visible'), 5000);
        }

        stage.addEventListener('mouseenter', () => {
            if (surahDisplay.textContent && mediaActive) {
                surahDisplay.classList.add('visible');
                clearTimeout(surahHideTimer);
            }
        });

        stage.addEventListener('mouseleave', () => {
            if (surahDisplay.textContent) {
                surahHideTimer = setTimeout(() => surahDisplay.classList.remove('visible'), 2000);
            }
        });

        // ── Screenshot Capture ────────────────────────────────────────────
        function captureScreenshot() {
            const w = canvas.width;
            const h = canvas.height;
            const ratio = window.devicePixelRatio || 1;

            // Create offscreen canvas for compositing
            const offscreen = document.createElement('canvas');
            offscreen.width = w;
            offscreen.height = h;
            const offCtx = offscreen.getContext('2d');

            // Draw main visualization
            offCtx.drawImage(canvas, 0, 0);

            // Draw surah name if visible
            const surahText = surahDisplay.textContent;
            if (surahText) {
                const fontSize = Math.round(32 * ratio);
                offCtx.font = `600 ${fontSize}px Cinzel, serif`;
                offCtx.textAlign = 'center';
                offCtx.fillStyle = 'rgba(255, 255, 255, 0.7)';
                offCtx.shadowColor = 'rgba(0, 0, 0, 0.6)';
                offCtx.shadowBlur = 20 * ratio;
                offCtx.fillText(surahText, w / 2, h * 0.18 + fontSize);
                offCtx.shadowBlur = 0;
            }

            // Draw watermark
            const wmSize = Math.round(12 * ratio);
            offCtx.font = `${wmSize}px Inter, sans-serif`;
            offCtx.textAlign = 'right';
            offCtx.fillStyle = 'rgba(255, 255, 255, 0.10)';
            offCtx.fillText('QuranVisuals.com', w - 14 * ratio, h - 12 * ratio);

            // Flash effect
            const flash = document.createElement('div');
            flash.className = 'screenshot-flash';
            stage.appendChild(flash);
            flash.addEventListener('animationend', () => flash.remove());

            // Download
            offscreen.toBlob((blob) => {
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `quran-visual-${Date.now()}.png`;
                a.click();
                URL.revokeObjectURL(url);
                showToast('Screenshot saved');
            }, 'image/png');
        }

        screenshotBtn.addEventListener('click', captureScreenshot);

        // ── Share Link ────────────────────────────────────────────────────
        async function shareLink() {
            const url = window.location.href;
            if (navigator.share) {
                try {
                    await navigator.share({ title: 'Quran Visuals', url });
                } catch (e) { /* user cancelled */ }
            } else {
                await navigator.clipboard.writeText(url);
                showToast('Link copied to clipboard');
            }
        }

        shareLinkBtn.addEventListener('click', shareLink);

        // ── Keyboard Shortcuts ────────────────────────────────────────────
        function toggleShortcutsModal() {
            shortcutsOverlay.classList.toggle('open');
        }

        shortcutsBtn.addEventListener('click', toggleShortcutsModal);
        shortcutsHint.addEventListener('click', toggleShortcutsModal);

        shortcutsOverlay.addEventListener('click', (e) => {
            if (e.target === shortcutsOverlay) shortcutsOverlay.classList.remove('open');
        });

        // ── Curated Reciters ──────────────────────────────────────────────
        const curatedReciters = @json($reciters);

        curatedReciters.forEach(reciter => {
            const card = document.createElement('button');
            card.className = 'reciter-card';
            card.textContent = reciter.name;
            card.addEventListener('click', () => {
                const url = `https://www.youtube.com/watch?v=${reciter.videoId}`;
                input.value = url;
                form.dispatchEvent(new Event('submit', { cancelable: true }));
            });
            recitersGrid.appendChild(card);
        });

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
        let silentFrames = 0;

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

        // Pseudo-random hash for organic simulation
        function simHash(n) {
            return ((Math.sin(n) * 43758.5453) % 1 + 1) % 1;
        }

        function simulateFrequencies() {
            const t = performance.now() / 1000;

            // Multiple layered rhythms for organic feel
            const slow = Math.sin(t * 0.4) * 0.5 + 0.5;       // Breathing rhythm
            const med = Math.sin(t * 1.1) * 0.5 + 0.5;        // Phrase rhythm
            const fast = Math.sin(t * 2.7) * 0.5 + 0.5;       // Word rhythm
            const pulse = Math.pow(Math.sin(t * 1.8), 8);      // Sharp peaks

            // Pseudo-random variation so it doesn't feel like a loop
            const drift = simHash(Math.floor(t * 0.3)) * 0.2;
            const jitter = simHash(Math.floor(t * 4)) * 0.15;

            // Recitation has strong bass presence, variable mid, subtle high
            const rawBass = 0.25 + slow * 0.35 + pulse * 0.25 + drift;
            const rawMid = 0.15 + med * 0.3 + fast * 0.15 + jitter;
            const rawHigh = 0.05 + fast * 0.2 + pulse * 0.1;
            const rawVol = rawBass * 0.5 + rawMid * 0.3 + rawHigh * 0.2;

            // Smooth with faster attack (0.25) for responsiveness
            audio.bass += (rawBass - audio.bass) * 0.25;
            audio.mid += (rawMid - audio.mid) * 0.25;
            audio.high += (rawHigh - audio.high) * 0.3;
            audio.volume += (rawVol - audio.volume) * 0.25;

            // Peak — fast attack, slow decay
            if (rawVol > audio.peak) audio.peak = rawVol;
            else audio.peak *= 0.93;

            audio.energy = audio.energy * 0.97 + rawVol * 0.03;
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

        function clearCanvas() {
            ctx.clearRect(0, 0, canvas.clientWidth, canvas.clientHeight);
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
            // Clear old effect trails
            clearCanvas();
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
            const newPrimary = getLayersForHue(hsl.h, hex, darker);
            // Only clear canvas if primary effect type changed
            const oldEffect = preset.layers[0] && preset.layers[0].effect;
            const newEffect = newPrimary[0] && newPrimary[0].effect;
            if (oldEffect !== newEffect) clearCanvas();
            // Replace primary layer but preserve secondary layers with updated color
            preset.layers[0] = newPrimary[0];
            for (let i = 1; i < preset.layers.length; i++) {
                if (preset.layers[i].params) {
                    preset.layers[i].params.color = hex;
                }
            }
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

            // Fade old frames — faster when quiet so trails clear sooner
            const activity = audio.volume + audio.peak;
            if (activity < 0.02) {
                silentFrames++;
                // After ~30 silent frames (~0.5s), fully clear
                if (silentFrames > 30) {
                    ctx.clearRect(0, 0, width, height);
                } else {
                    ctx.fillStyle = 'rgba(0, 0, 0, 0.3)';
                    ctx.fillRect(0, 0, width, height);
                }
            } else {
                silentFrames = 0;
                const fade = 0.12 + audio.volume * 0.12;
                ctx.fillStyle = `rgba(0, 0, 0, ${fade})`;
                ctx.fillRect(0, 0, width, height);
            }

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

        // ── Subtitle Sync Engine ─────────────────────────────────────────
        const subtitleOverlay = document.getElementById('subtitle-overlay');
        let subtitleData = null;
        let currentSegmentId = -1;

        async function loadSubtitles(slug) {
            subtitleData = null;
            currentSegmentId = -1;
            subtitleOverlay.textContent = '';

            if (!slug) return;

            try {
                const res = await fetch(`/storage/subtitles/${encodeURIComponent(slug)}.json`);
                if (!res.ok) return;
                subtitleData = await res.json();
            } catch (e) {
                // Subtitle file not available — silent fail
            }
        }

        function renderSegment(seg) {
            subtitleOverlay.textContent = '';
            const span = document.createElement('span');
            span.className = 'segment-text';

            if (seg.words && seg.words.length > 0) {
                seg.words.forEach((w, i) => {
                    const wordEl = document.createElement('span');
                    wordEl.className = 'word';
                    wordEl.dataset.start = w.start;
                    wordEl.dataset.end = w.end;
                    wordEl.textContent = w.text;
                    span.appendChild(wordEl);
                    if (i < seg.words.length - 1) {
                        span.appendChild(document.createTextNode(' '));
                    }
                });
            } else {
                span.textContent = seg.text;
            }

            subtitleOverlay.appendChild(span);
        }

        function updateSubtitles(currentTime) {
            if (!subtitleData || !subtitleData.segments) {
                if (subtitleOverlay.childNodes.length) subtitleOverlay.textContent = '';
                return;
            }

            // Find the current segment
            let seg = null;
            for (const s of subtitleData.segments) {
                if (currentTime >= s.start && currentTime <= s.end) {
                    seg = s;
                    break;
                }
            }

            if (!seg) {
                if (currentSegmentId !== -1) {
                    subtitleOverlay.textContent = '';
                    currentSegmentId = -1;
                }
                return;
            }

            // Render new segment if changed
            if (seg.id !== currentSegmentId) {
                currentSegmentId = seg.id;
                renderSegment(seg);
            }

            // Highlight active word
            const words = subtitleOverlay.querySelectorAll('.word');
            words.forEach(el => {
                const ws = parseFloat(el.dataset.start);
                const we = parseFloat(el.dataset.end);
                if (currentTime >= ws && currentTime <= we) {
                    el.classList.add('active');
                } else {
                    el.classList.remove('active');
                }
            });
        }

        function getPlaybackTime() {
            if (ytMode && ytApiPlayer && typeof ytApiPlayer.getCurrentTime === 'function') {
                return ytApiPlayer.getCurrentTime();
            }
            if (!audioPlayer.paused) {
                return audioPlayer.currentTime;
            }
            return -1;
        }

        // Subtitle update loop (separate rAF for frame-accurate sync)
        (function subtitleLoop() {
            requestAnimationFrame(subtitleLoop);
            const t = getPlaybackTime();
            if (t >= 0) updateSubtitles(t);
        })();

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
                        const state = event.data;
                        if (state === YT.PlayerState.PLAYING) {
                            ytPlaying = true;
                            syncPlayPauseIcon(true);
                        } else if (state === YT.PlayerState.BUFFERING) {
                            // Keep simulation running during buffering
                            ytPlaying = true;
                        } else if (state === YT.PlayerState.ENDED) {
                            // Handle repeat mode for YouTube
                            if (repeatMode === 'one' || repeatMode === 'all') {
                                ytApiPlayer.seekTo(0);
                                ytApiPlayer.playVideo();
                            } else {
                                ytPlaying = false;
                                syncPlayPauseIcon(false);
                            }
                        } else if (state === YT.PlayerState.PAUSED) {
                            ytPlaying = false;
                            syncPlayPauseIcon(false);
                        }
                    },
                    onReady: function() {
                        // Show surah name from YT video title
                        try {
                            const data = ytApiPlayer.getVideoData();
                            if (data && data.title) showSurahName(data.title);
                        } catch (e) {}
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
            const isFullscreen = !!(document.fullscreenElement || document.webkitFullscreenElement);
            meta.style.opacity = '0';
            shareBtn.classList.remove('visible');
            // Only auto-hide play/pause in fullscreen; keep visible when windowed
            if (isFullscreen) {
                playpauseBtn.classList.remove('visible');
            }
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
            // Show play/pause whenever media is active (fullscreen or not)
            playpauseBtn.hidden = !mediaActive;
            fullscreenBtn.hidden = true;
            resizeCanvas();

            if (isFullscreen) {
                startOverlayHideTimer();
            } else {
                clearTimeout(overlayHideTimer);
                showOverlays();
                // Keep play/pause always visible when not fullscreen
                playpauseBtn.classList.add('visible');
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
            // Ignore when typing in inputs
            const tag = e.target.tagName;
            if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return;

            switch (e.code) {
                case 'Space':
                    e.preventDefault();
                    playpauseBtn.click();
                    break;
                case 'KeyM':
                    if (ytMode && ytApiPlayer) {
                        if (ytApiPlayer.isMuted()) ytApiPlayer.unMute();
                        else ytApiPlayer.mute();
                    } else if (!audioPlayer.hidden) {
                        audioPlayer.muted = !audioPlayer.muted;
                    }
                    showToast(((ytMode && ytApiPlayer && ytApiPlayer.isMuted()) || audioPlayer.muted) ? 'Muted' : 'Unmuted');
                    break;
                case 'KeyF':
                    if (document.fullscreenElement || document.webkitFullscreenElement) {
                        exitCinema();
                    } else {
                        enterCinema();
                    }
                    break;
                case 'ArrowLeft':
                    e.preventDefault();
                    if (ytMode && ytApiPlayer) {
                        ytApiPlayer.seekTo(Math.max(0, ytApiPlayer.getCurrentTime() - 10), true);
                    } else if (!audioPlayer.hidden) {
                        audioPlayer.currentTime = Math.max(0, audioPlayer.currentTime - 10);
                    }
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    if (ytMode && ytApiPlayer) {
                        ytApiPlayer.seekTo(ytApiPlayer.getCurrentTime() + 10, true);
                    } else if (!audioPlayer.hidden) {
                        audioPlayer.currentTime = Math.min(audioPlayer.duration, audioPlayer.currentTime + 10);
                    }
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    if (ytMode && ytApiPlayer) {
                        ytApiPlayer.setVolume(Math.min(100, ytApiPlayer.getVolume() + 10));
                    } else if (!audioPlayer.hidden) {
                        audioPlayer.volume = Math.min(1, audioPlayer.volume + 0.1);
                    }
                    break;
                case 'ArrowDown':
                    e.preventDefault();
                    if (ytMode && ytApiPlayer) {
                        ytApiPlayer.setVolume(Math.max(0, ytApiPlayer.getVolume() - 10));
                    } else if (!audioPlayer.hidden) {
                        audioPlayer.volume = Math.max(0, audioPlayer.volume - 0.1);
                    }
                    break;
                case 'KeyL':
                    cycleRepeatMode();
                    showToast('Repeat: ' + repeatMode.charAt(0).toUpperCase() + repeatMode.slice(1));
                    break;
                case 'Escape':
                    if (shortcutsOverlay.classList.contains('open')) {
                        shortcutsOverlay.classList.remove('open');
                    } else if (document.fullscreenElement || document.webkitFullscreenElement) {
                        exitCinema();
                    }
                    break;
                default:
                    if (e.key === '?') {
                        toggleShortcutsModal();
                    }
                    break;
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

            // Clear any active subtitles
            subtitleData = null;
            currentSegmentId = -1;
            subtitleOverlay.textContent = '';

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
                recitersPanel.hidden = true;
                setMeta(data.title || 'Quran Recitation', data.author || 'Verified input');

                mediaActive = true;
                playpauseBtn.hidden = false;
                playpauseBtn.classList.add('visible');

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
                    // Show surah name from title or filename
                    showSurahName(data.title || url.split('/').pop());
                }

                // Load subtitles if available
                if (data.subtitle_slug) {
                    loadSubtitles(data.subtitle_slug);
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
