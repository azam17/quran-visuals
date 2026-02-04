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
    // Common transliteration variants for surah name detection from video titles.
    // Supplements the API's englishName values (e.g., "Al-Faatiha").
    'surah_aliases' => [
        // 1 - Al-Fatiha
        'fatiha' => 1, 'fatihah' => 1, 'faatiha' => 1, 'fateha' => 1, 'al fatiha' => 1,
        // 2 - Al-Baqarah
        'baqara' => 2, 'baqarah' => 2, 'baqra' => 2, 'al baqara' => 2, 'al baqarah' => 2,
        // 3 - Ali 'Imran
        'imran' => 3, 'ali imran' => 3, 'al imran' => 3, 'ale imran' => 3,
        // 4 - An-Nisa
        'nisa' => 4, 'nisaa' => 4, 'al nisa' => 4, 'an nisa' => 4,
        // 5 - Al-Ma'idah
        'maidah' => 5, 'maida' => 5, 'al maidah' => 5,
        // 6 - Al-An'am
        'anam' => 6, 'al anam' => 6, 'an am' => 6,
        // 7 - Al-A'raf
        'araf' => 7, 'al araf' => 7,
        // 8 - Al-Anfal
        'anfal' => 8, 'al anfal' => 8,
        // 9 - At-Tawbah
        'tawbah' => 9, 'tawba' => 9, 'taubah' => 9, 'at tawbah' => 9,
        // 10 - Yunus
        'yunus' => 10, 'yoonus' => 10,
        // 11 - Hud
        'hud' => 11, 'hood' => 11,
        // 12 - Yusuf
        'yusuf' => 12, 'yousuf' => 12, 'yoosuf' => 12, 'yousaf' => 12, 'yousef' => 12,
        // 13 - Ar-Ra'd
        'rad' => 13, 'ar rad' => 13, 'raad' => 13,
        // 14 - Ibrahim
        'ibrahim' => 14, 'ibraheem' => 14,
        // 15 - Al-Hijr
        'hijr' => 15, 'al hijr' => 15,
        // 16 - An-Nahl
        'nahl' => 16, 'an nahl' => 16,
        // 17 - Al-Isra
        'isra' => 17, 'al isra' => 17, 'israa' => 17, 'bani israel' => 17,
        // 18 - Al-Kahf
        'kahf' => 18, 'al kahf' => 18, 'kehf' => 18,
        // 19 - Maryam
        'maryam' => 19, 'mariam' => 19,
        // 20 - Ta-Ha
        'taha' => 20, 'ta ha' => 20,
        // 21 - Al-Anbiya
        'anbiya' => 21, 'al anbiya' => 21, 'anbiyaa' => 21,
        // 22 - Al-Hajj
        'hajj' => 22, 'al hajj' => 22,
        // 23 - Al-Mu'minun
        'muminun' => 23, 'muminoon' => 23, 'al muminun' => 23,
        // 24 - An-Nur
        'nur' => 24, 'noor' => 24, 'an nur' => 24,
        // 25 - Al-Furqan
        'furqan' => 25, 'al furqan' => 25, 'furqaan' => 25,
        // 26 - Ash-Shu'ara
        'shuara' => 26, 'ash shuara' => 26, 'shu ara' => 26,
        // 27 - An-Naml
        'naml' => 27, 'an naml' => 27,
        // 28 - Al-Qasas
        'qasas' => 28, 'al qasas' => 28,
        // 29 - Al-Ankabut
        'ankabut' => 29, 'ankaboot' => 29, 'al ankabut' => 29,
        // 30 - Ar-Rum
        'rum' => 30, 'ar rum' => 30, 'room' => 30,
        // 31 - Luqman
        'luqman' => 31, 'lukman' => 31,
        // 32 - As-Sajdah
        'sajdah' => 32, 'sajda' => 32, 'as sajdah' => 32,
        // 33 - Al-Ahzab
        'ahzab' => 33, 'al ahzab' => 33,
        // 34 - Saba
        'saba' => 34, 'sabaa' => 34,
        // 35 - Fatir
        'fatir' => 35, 'faatir' => 35,
        // 36 - Ya-Sin
        'yaseen' => 36, 'yasin' => 36, 'ya seen' => 36, 'ya sin' => 36, 'yaasin' => 36,
        // 37 - As-Saffat
        'saffat' => 37, 'as saffat' => 37, 'saaffat' => 37,
        // 38 - Sad
        'saad' => 38, 'suad' => 38,
        // 39 - Az-Zumar
        'zumar' => 39, 'az zumar' => 39,
        // 40 - Ghafir
        'ghafir' => 40, 'al mumin' => 40, 'ghafar' => 40,
        // 41 - Fussilat
        'fussilat' => 41, 'ha meem sajdah' => 41, 'hameem sajdah' => 41,
        // 42 - Ash-Shura
        'shura' => 42, 'ash shura' => 42, 'shuraa' => 42,
        // 43 - Az-Zukhruf
        'zukhruf' => 43, 'az zukhruf' => 43,
        // 44 - Ad-Dukhan
        'dukhan' => 44, 'ad dukhan' => 44, 'dukhaan' => 44,
        // 45 - Al-Jathiya
        'jathiya' => 45, 'al jathiya' => 45, 'jathiyah' => 45,
        // 46 - Al-Ahqaf
        'ahqaf' => 46, 'al ahqaf' => 46,
        // 47 - Muhammad
        'muhammad' => 47, 'mohammad' => 47, 'mohammed' => 47, 'mohamad' => 47,
        // 48 - Al-Fath
        'fath' => 48, 'al fath' => 48, 'fatah' => 48,
        // 49 - Al-Hujurat
        'hujurat' => 49, 'al hujurat' => 49, 'hujraat' => 49,
        // 50 - Qaf
        'qaf' => 50, 'qaaf' => 50,
        // 51 - Adh-Dhariyat
        'dhariyat' => 51, 'adh dhariyat' => 51, 'zariyat' => 51,
        // 52 - At-Tur
        'tur' => 52, 'at tur' => 52, 'toor' => 52,
        // 53 - An-Najm
        'najm' => 53, 'an najm' => 53,
        // 54 - Al-Qamar
        'qamar' => 54, 'al qamar' => 54,
        // 55 - Ar-Rahman
        'rahman' => 55, 'rahmaan' => 55, 'ar rahman' => 55,
        // 56 - Al-Waqi'ah
        'waqiah' => 56, 'al waqiah' => 56, 'waqia' => 56, 'waaqiah' => 56,
        // 57 - Al-Hadid
        'hadid' => 57, 'al hadid' => 57, 'hadeed' => 57,
        // 58 - Al-Mujadila
        'mujadila' => 58, 'al mujadila' => 58, 'mujadilah' => 58,
        // 59 - Al-Hashr
        'hashr' => 59, 'al hashr' => 59,
        // 60 - Al-Mumtahina
        'mumtahina' => 60, 'al mumtahina' => 60, 'mumtahanah' => 60,
        // 61 - As-Saff
        'saff' => 61, 'as saff' => 61,
        // 62 - Al-Jumu'ah
        'jumuah' => 62, 'al jumuah' => 62, 'juma' => 62,
        // 63 - Al-Munafiqun
        'munafiqun' => 63, 'al munafiqun' => 63, 'munafiqoon' => 63,
        // 64 - At-Taghabun
        'taghabun' => 64, 'at taghabun' => 64, 'taghaabun' => 64,
        // 65 - At-Talaq
        'talaq' => 65, 'at talaq' => 65, 'talaaq' => 65,
        // 66 - At-Tahrim
        'tahrim' => 66, 'at tahrim' => 66, 'tahreem' => 66,
        // 67 - Al-Mulk
        'mulk' => 67, 'al mulk' => 67,
        // 68 - Al-Qalam
        'qalam' => 68, 'al qalam' => 68,
        // 69 - Al-Haqqah
        'haqqah' => 69, 'al haqqah' => 69, 'haaqqah' => 69,
        // 70 - Al-Ma'arij
        'maarij' => 70, 'al maarij' => 70, 'ma arij' => 70,
        // 71 - Nuh
        'nuh' => 71, 'nooh' => 71, 'nouh' => 71,
        // 72 - Al-Jinn
        'jinn' => 72, 'al jinn' => 72,
        // 73 - Al-Muzzammil
        'muzzammil' => 73, 'al muzzammil' => 73, 'muzammil' => 73,
        // 74 - Al-Muddaththir
        'muddaththir' => 74, 'al muddaththir' => 74, 'mudassir' => 74,
        // 75 - Al-Qiyamah
        'qiyamah' => 75, 'al qiyamah' => 75, 'qiyama' => 75,
        // 76 - Al-Insan
        'insan' => 76, 'al insan' => 76, 'insaan' => 76, 'dahr' => 76,
        // 77 - Al-Mursalat
        'mursalat' => 77, 'al mursalat' => 77,
        // 78 - An-Naba
        'naba' => 78, 'an naba' => 78, 'nabaa' => 78,
        // 79 - An-Nazi'at
        'naziat' => 79, 'an naziat' => 79, 'naziaat' => 79,
        // 80 - Abasa
        'abasa' => 80,
        // 81 - At-Takwir
        'takwir' => 81, 'at takwir' => 81, 'takweer' => 81,
        // 82 - Al-Infitar
        'infitar' => 82, 'al infitar' => 82, 'infitaar' => 82,
        // 83 - Al-Mutaffifin
        'mutaffifin' => 83, 'al mutaffifin' => 83, 'mutaffifeen' => 83,
        // 84 - Al-Inshiqaq
        'inshiqaq' => 84, 'al inshiqaq' => 84, 'inshiqaaq' => 84,
        // 85 - Al-Buruj
        'buruj' => 85, 'burooj' => 85, 'al buruj' => 85,
        // 86 - At-Tariq
        'tariq' => 86, 'at tariq' => 86, 'taariq' => 86,
        // 87 - Al-A'la
        'ala' => 87, 'al ala' => 87, 'a la' => 87,
        // 88 - Al-Ghashiyah
        'ghashiyah' => 88, 'al ghashiyah' => 88, 'ghashiya' => 88,
        // 89 - Al-Fajr
        'fajr' => 89, 'al fajr' => 89,
        // 90 - Al-Balad
        'balad' => 90, 'al balad' => 90,
        // 91 - Ash-Shams
        'shams' => 91, 'ash shams' => 91,
        // 92 - Al-Layl
        'layl' => 92, 'lail' => 92, 'al layl' => 92,
        // 93 - Ad-Duha
        'duha' => 93, 'ad duha' => 93, 'dhuha' => 93,
        // 94 - Ash-Sharh
        'sharh' => 94, 'ash sharh' => 94, 'inshirah' => 94,
        // 95 - At-Tin
        'tin' => 95, 'at tin' => 95, 'teen' => 95,
        // 96 - Al-'Alaq
        'alaq' => 96, 'al alaq' => 96, 'iqra' => 96,
        // 97 - Al-Qadr
        'qadr' => 97, 'al qadr' => 97,
        // 98 - Al-Bayyinah
        'bayyinah' => 98, 'al bayyinah' => 98, 'bayyina' => 98,
        // 99 - Az-Zalzalah
        'zalzalah' => 99, 'az zalzalah' => 99, 'zilzal' => 99,
        // 100 - Al-'Adiyat
        'adiyat' => 100, 'al adiyat' => 100, 'aadiyat' => 100,
        // 101 - Al-Qari'ah
        'qariah' => 101, 'al qariah' => 101, 'qaaria' => 101,
        // 102 - At-Takathur
        'takathur' => 102, 'at takathur' => 102, 'takaathur' => 102,
        // 103 - Al-'Asr
        'asr' => 103, 'al asr' => 103,
        // 104 - Al-Humazah
        'humazah' => 104, 'al humazah' => 104, 'humaza' => 104,
        // 105 - Al-Fil
        'fil' => 105, 'al fil' => 105, 'feel' => 105,
        // 106 - Quraysh
        'quraysh' => 106, 'quraish' => 106,
        // 107 - Al-Ma'un
        'maun' => 107, 'al maun' => 107, 'maaun' => 107,
        // 108 - Al-Kawthar
        'kawthar' => 108, 'al kawthar' => 108, 'kauthar' => 108, 'kausar' => 108, 'kawsar' => 108,
        // 109 - Al-Kafirun
        'kafirun' => 109, 'al kafirun' => 109, 'kafiroon' => 109, 'kaafiroon' => 109,
        // 110 - An-Nasr
        'nasr' => 110, 'an nasr' => 110,
        // 111 - Al-Masad
        'masad' => 111, 'al masad' => 111, 'lahab' => 111, 'al lahab' => 111,
        // 112 - Al-Ikhlas
        'ikhlas' => 112, 'al ikhlas' => 112, 'ikhlaas' => 112,
        // 113 - Al-Falaq
        'falaq' => 113, 'al falaq' => 113,
        // 114 - An-Nas
        'nas' => 114, 'an nas' => 114, 'naas' => 114,
    ],

    // Juz (part) name-to-surah mapping. Each entry maps to the first surah of the juz.
    'juz_map' => [
        'amma' => ['juz' => 30, 'surah' => 78, 'ayahStart' => 1],
        'tabarak' => ['juz' => 29, 'surah' => 67, 'ayahStart' => 1],
        'qad sami' => ['juz' => 28, 'surah' => 58, 'ayahStart' => 1],
    ],

    // Special well-known phrases that map to specific surah + ayah ranges.
    'special_phrases' => [
        'ayat al kursi' => ['surah' => 2, 'ayahStart' => 255, 'ayahEnd' => 255],
        'ayatul kursi' => ['surah' => 2, 'ayahStart' => 255, 'ayahEnd' => 255],
        'ayat ul kursi' => ['surah' => 2, 'ayahStart' => 255, 'ayahEnd' => 255],
        'ayat alkursi' => ['surah' => 2, 'ayahStart' => 255, 'ayahEnd' => 255],
        'last two ayah baqarah' => ['surah' => 2, 'ayahStart' => 285, 'ayahEnd' => 286],
        'last 2 ayah baqarah' => ['surah' => 2, 'ayahStart' => 285, 'ayahEnd' => 286],
        'last two verses baqarah' => ['surah' => 2, 'ayahStart' => 285, 'ayahEnd' => 286],
        'last 2 verses baqarah' => ['surah' => 2, 'ayahStart' => 285, 'ayahEnd' => 286],
        'last two ayat baqarah' => ['surah' => 2, 'ayahStart' => 285, 'ayahEnd' => 286],
        'last 2 ayat baqarah' => ['surah' => 2, 'ayahStart' => 285, 'ayahEnd' => 286],
    ],

    'yt_dlp_binary' => env('YT_DLP_BINARY', 'yt-dlp'),

    'python_binary' => env('PYTHON_BINARY', '/opt/homebrew/bin/python3'),

    'reciters' => [
        ['name' => 'Mishary Rashid Alafasy', 'videoId' => 'X2YnP50cwNU'],
        ['name' => 'Abdul Rahman Al-Sudais', 'videoId' => 'Mk1NaOjVSBs'],
        ['name' => 'Maher Al Muaiqly', 'videoId' => 'd1yTK45g9EA'],
        ['name' => 'Yasser Al Dosari', 'videoId' => '9-SYYnQzVIY'],
        ['name' => 'Hazza Al Balushi', 'videoId' => 'UlK_aQK4dyY'],
        ['name' => 'Raad Al Kurdi', 'videoId' => 'MlCXPjpTVZk'],
    ],
];
