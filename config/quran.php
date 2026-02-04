<?php

return [
    'admin_email' => env('ADMIN_EMAIL', 'admin@quranvisuals.com'),

    'keywords' => [
        'quran',
        'qur\'an',
        'quran recitation',
        'surah',
        'surat',
        'tilawah',
        'tilawat',
        'murottal',
        'murotal',
        'qari',
        'imam',
        'recitation',
        'ayat',
        'ayah',
        // Arabic keywords
        'قرآن',
        'قران',
        'سورة',
        'تلاوة',
        'تلاوه',
        'مرتل',
        'ترتيل',
        'تجويد',
        'قارئ',
        'الحرم',
        'رمضان',
        'آية',
        'آيات',
        'الشيخ',
    ],
    'blocked_keywords' => [
        'remix',
        'instrumental',
        'lofi',
        'beats',
        'mix',
        'cover',
        'karaoke',
        'music',
    ],
    'allowed_channels' => [
        // Add trusted YouTube channel names here to allow regardless of keywords.
        // Example: 'Quranic Recitations',
    ],
    'allowed_audio_extensions' => [
        'mp3',
        'm4a',
        'wav',
        'ogg',
    ],
    'presets' => [
        [
            'id' => 'midnight-waves',
            'name' => 'Midnight Waves',
            'vars' => [
                '--bg-1' => '#070a12',
                '--bg-2' => '#0d1118',
                '--bg-3' => '#151a24',
                '--accent' => '#5a8fa8',
                '--accent-2' => '#2d4a5a',
            ],
            'layers' => [
                ['effect' => 'mirroredWave', 'params' => ['color' => '#5a8fa8', 'layerCount' => 4]],
            ],
        ],
        [
            'id' => 'ember-spectrum',
            'name' => 'Ember Spectrum',
            'vars' => [
                '--bg-1' => '#0f0a08',
                '--bg-2' => '#1a110d',
                '--bg-3' => '#241915',
                '--accent' => '#c4845f',
                '--accent-2' => '#6b3d25',
            ],
            'layers' => [
                ['effect' => 'mirroredBars', 'params' => ['color' => '#c4845f', 'count' => 64]],
            ],
        ],
        [
            'id' => 'violet-signal',
            'name' => 'Violet Signal',
            'vars' => [
                '--bg-1' => '#0a0610',
                '--bg-2' => '#110c18',
                '--bg-3' => '#1a1423',
                '--accent' => '#8a7aa6',
                '--accent-2' => '#473d5a',
            ],
            'layers' => [
                ['effect' => 'oscilloscope', 'params' => ['color' => '#8a7aa6', 'freqX' => 3, 'freqY' => 2]],
            ],
        ],
        [
            'id' => 'arctic-pulse',
            'name' => 'Arctic Pulse',
            'vars' => [
                '--bg-1' => '#080d12',
                '--bg-2' => '#0e1419',
                '--bg-3' => '#151c24',
                '--accent' => '#7a9db0',
                '--accent-2' => '#3d5a6b',
            ],
            'layers' => [
                ['effect' => 'circularWave', 'params' => ['color' => '#7a9db0', 'rings' => 3]],
            ],
        ],
        [
            'id' => 'charcoal-horizon',
            'name' => 'Charcoal Horizon',
            'vars' => [
                '--bg-1' => '#0a0a0c',
                '--bg-2' => '#121214',
                '--bg-3' => '#1a1a1d',
                '--accent' => '#9a9aa6',
                '--accent-2' => '#4d4d59',
            ],
            'layers' => [
                ['effect' => 'mirroredWave', 'params' => ['color' => '#9a9aa6', 'layerCount' => 3]],
            ],
        ],
    ],
    'yt_dlp_binary' => 'yt-dlp',
];
