<?php

use App\Models\FeedbackItem;
use App\Models\User;
use App\Services\ArabicNormalizer;
use App\Services\QuranApiService;
use App\Services\QuranUrlInspector;
use App\Services\WhisperAligner;
use App\Services\YouTubeCaptionService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('quran:ingest {url} {--format=mp3}', function () {
    $url = trim($this->argument('url'));
    $format = $this->option('format') ?: 'mp3';
    $binary = config('quran.yt_dlp_binary', 'yt-dlp');

    if ($url === '') {
        $this->error('URL is required.');
        return 1;
    }

    $targetDir = storage_path('app/public/ingested');
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $slug = Str::lower(Str::random(8));
    $template = $targetDir.'/quran-'.$slug.'.%(ext)s';

    $this->info('Downloading and extracting audio...');
    $process = Process::timeout(300)->run([
        $binary,
        '--extract-audio',
        '--audio-format', $format,
        '--audio-quality', '0',
        '-o', $template,
        $url,
    ]);

    if (!$process->successful()) {
        $this->error('yt-dlp failed. Ensure yt-dlp and ffmpeg are installed.');
        $this->line($process->errorOutput());
        return 1;
    }

    $files = glob($targetDir.'/quran-'.$slug.'.*');
    if (!$files) {
        $this->error('No output file found.');
        return 1;
    }

    $outputPath = $files[0];
    $fileName = basename($outputPath);
    $publicUrl = Storage::url('ingested/'.$fileName);

    $meta = [
        'source_url' => $url,
        'file' => $fileName,
        'created_at' => now()->toDateTimeString(),
    ];
    file_put_contents($targetDir.'/quran-'.$slug.'.json', json_encode($meta, JSON_PRETTY_PRINT));

    $this->info('Done.');
    $this->line('Local file: '.$outputPath);
    $this->line('Public URL (requires storage:link): '.$publicUrl);
    return 0;
})->purpose('Download YouTube audio to a local file for reactive visuals');

// ── quran:validate ──────────────────────────────────────────────────────
Artisan::command('quran:validate {url}', function () {
    $url = trim($this->argument('url'));
    $inspector = app(QuranUrlInspector::class);
    $result = $inspector->inspect($url);

    if (!($result['ok'] ?? false)) {
        $this->error('Blocked: '.($result['reason'] ?? 'Unknown reason'));
        return 1;
    }

    $this->table(
        ['Field', 'Value'],
        [
            ['Type', $result['type'] ?? '-'],
            ['Title', $result['title'] ?? '-'],
            ['Author', $result['author'] ?? '-'],
            ['Embed URL', $result['embed_url'] ?? '-'],
            ['Audio URL', $result['audio_url'] ?? '-'],
            ['Reactive', ($result['reactive'] ?? false) ? 'Yes' : 'No'],
            ['Warning', $result['warning'] ?? 'None'],
        ]
    );

    return 0;
})->purpose('Validate a URL and show Quran content metadata');

// ── quran:presets ───────────────────────────────────────────────────────
Artisan::command('quran:presets', function () {
    $presets = config('quran.presets', []);

    if (empty($presets)) {
        $this->warn('No presets configured.');
        return 0;
    }

    $rows = [];
    foreach ($presets as $preset) {
        $effects = collect($preset['layers'] ?? [])
            ->pluck('effect')
            ->implode(', ');

        $rows[] = [
            $preset['id'],
            $preset['name'],
            $preset['vars']['--accent'] ?? '-',
            $preset['vars']['--bg-1'] ?? '-',
            $effects ?: '-',
        ];
    }

    $this->table(['ID', 'Name', 'Accent', 'Background', 'Effects'], $rows);
    return 0;
})->purpose('List all visual presets with their colors and effects');

// ── quran:stats ─────────────────────────────────────────────────────────
Artisan::command('quran:stats', function () {
    $statuses = [
        FeedbackItem::STATUS_UNDER_REVIEW,
        FeedbackItem::STATUS_PLANNED,
        FeedbackItem::STATUS_IN_PROGRESS,
        FeedbackItem::STATUS_DONE,
    ];

    $this->info('Feedback by status:');
    $statusRows = [];
    foreach ($statuses as $status) {
        $statusRows[] = [
            str_replace('_', ' ', ucfirst($status)),
            FeedbackItem::where('status', $status)->count(),
        ];
    }
    $this->table(['Status', 'Count'], $statusRows);

    $this->newLine();
    $this->info('Top 5 voted items:');
    $top = FeedbackItem::withCount('votes')
        ->orderByDesc('votes_count')
        ->limit(5)
        ->get();

    if ($top->isEmpty()) {
        $this->line('  No feedback items yet.');
    } else {
        $topRows = [];
        foreach ($top as $item) {
            $topRows[] = [$item->id, Str::limit($item->title, 50), $item->votes_count];
        }
        $this->table(['ID', 'Title', 'Votes'], $topRows);
    }

    $this->newLine();
    $this->line('Total items: '.FeedbackItem::count());
    $this->line('Total users: '.User::count());

    return 0;
})->purpose('Show feedback statistics and top voted items');

// ── quran:check-reciters ────────────────────────────────────────────────
Artisan::command('quran:check-reciters', function () {
    $reciters = config('quran.reciters', []);

    if (empty($reciters)) {
        $this->warn('No reciters configured in config/quran.php.');
        return 0;
    }

    $this->info('Checking '.count($reciters).' reciters against YouTube oEmbed...');
    $rows = [];

    foreach ($reciters as $reciter) {
        $videoId = $reciter['videoId'] ?? '';
        $url = "https://www.youtube.com/watch?v={$videoId}";
        $status = 'FAIL';
        $title = '-';

        try {
            $response = Http::timeout(5)->get('https://www.youtube.com/oembed', [
                'url' => $url,
                'format' => 'json',
            ]);

            if ($response->ok()) {
                $status = 'OK';
                $title = $response->json('title') ?? '-';
            } else {
                $status = 'HTTP '.$response->status();
            }
        } catch (\Throwable $e) {
            $status = 'FAIL';
        }

        $rows[] = [
            $reciter['name'],
            $videoId,
            $status,
            Str::limit($title, 60),
        ];
    }

    $this->table(['Reciter', 'Video ID', 'Status', 'Title'], $rows);
    return 0;
})->purpose('Check all curated reciters against YouTube oEmbed API');

// ── quran:deploy ────────────────────────────────────────────────────────
Artisan::command('quran:deploy', function () {
    $this->info('Pushing to origin master...');
    $push = Process::timeout(30)->run(['git', 'push', 'origin', 'master']);
    if (!$push->successful()) {
        $this->error('Git push failed.');
        $this->line($push->errorOutput());
        return 1;
    }
    $this->line($push->output());

    $this->info('Waiting 10s for Forge auto-deploy...');
    sleep(10);

    $this->info('Running remote migration...');
    $migrate = Process::timeout(30)->run([
        'ssh', 'diecasthub',
        'cd ~/quran-visuals.on-forge.com/current && php artisan migrate --force',
    ]);
    if (!$migrate->successful()) {
        $this->warn('Remote migration may have failed:');
        $this->line($migrate->errorOutput());
    } else {
        $this->line($migrate->output());
    }

    $this->info('Running health check...');
    try {
        $response = Http::timeout(10)->get('https://quran-visuals.on-forge.com/up');
        if ($response->ok()) {
            $this->info('Health check passed (HTTP '.$response->status().').');
        } else {
            $this->warn('Health check returned HTTP '.$response->status().'.');
        }
    } catch (\Throwable $e) {
        $this->error('Health check failed: '.$e->getMessage());
    }

    return 0;
})->purpose('Push to master, wait for Forge deploy, run migration, and health check');

// ── quran:transcribe ────────────────────────────────────────────────────
Artisan::command('quran:transcribe {url} {--model=base} {--language=ar} {--slug=}', function () {
    $url = trim($this->argument('url'));
    $model = $this->option('model') ?: 'base';
    $language = $this->option('language') ?: 'ar';
    $slug = $this->option('slug');
    $ytDlp = config('quran.yt_dlp_binary', 'yt-dlp');
    $python = config('quran.python_binary', '/opt/homebrew/bin/python3');
    $scriptPath = base_path('scripts/whisper_transcribe.py');

    // Pre-check: yt-dlp
    $ytCheck = Process::timeout(5)->run([$ytDlp, '--version']);
    if (!$ytCheck->successful()) {
        $this->error('yt-dlp not found. Install it: brew install yt-dlp');
        return 1;
    }

    // Pre-check: whisper
    $whisperCheck = Process::timeout(10)->run([$python, '-c', 'import whisper']);
    if (!$whisperCheck->successful()) {
        $this->error('openai-whisper not found. Install it: pip3 install openai-whisper');
        return 1;
    }

    // Pre-check: script exists
    if (!file_exists($scriptPath)) {
        $this->error('Whisper script not found at: '.$scriptPath);
        return 1;
    }

    // Generate slug from video ID if not provided
    if (!$slug) {
        // Try to extract YouTube video ID
        parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $query);
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
        if ($host === 'youtu.be') {
            $slug = trim(parse_url($url, PHP_URL_PATH) ?? '', '/');
        } else {
            $slug = $query['v'] ?? '';
        }
        if (!$slug) {
            $slug = Str::lower(Str::random(8));
        }
    }

    $subtitleDir = storage_path('app/public/subtitles');
    if (!is_dir($subtitleDir)) {
        mkdir($subtitleDir, 0755, true);
    }

    $tmpDir = storage_path('app/tmp-transcribe');
    if (!is_dir($tmpDir)) {
        mkdir($tmpDir, 0755, true);
    }

    $wavPath = $tmpDir.'/'.$slug.'.wav';
    $outputPath = $subtitleDir.'/'.$slug.'.json';

    // Step 1: Download audio as WAV
    $this->info('Downloading audio as WAV...');
    $download = Process::timeout(300)->run([
        $ytDlp,
        '--extract-audio',
        '--audio-format', 'wav',
        '--audio-quality', '0',
        '-o', $wavPath,
        $url,
    ]);

    if (!$download->successful()) {
        $this->error('Audio download failed.');
        $this->line($download->errorOutput());
        return 1;
    }

    // yt-dlp may add its own extension — find the actual file
    if (!file_exists($wavPath)) {
        $candidates = glob($tmpDir.'/'.$slug.'.*');
        $wavPath = $candidates[0] ?? $wavPath;
    }

    if (!file_exists($wavPath)) {
        $this->error('Downloaded file not found.');
        return 1;
    }

    // Step 2: Run Whisper transcription
    $this->info("Transcribing with Whisper (model: {$model}, language: {$language})...");
    $transcribe = Process::timeout(600)->run([
        $python,
        $scriptPath,
        '--audio', $wavPath,
        '--output', $outputPath,
        '--model', $model,
        '--language', $language,
    ]);

    if (!$transcribe->successful()) {
        $this->error('Transcription failed.');
        $this->line($transcribe->errorOutput());
        @unlink($wavPath);
        return 1;
    }

    $this->line($transcribe->output());

    // Step 3: Save metadata sidecar
    $metaPath = $subtitleDir.'/'.$slug.'.meta.json';
    file_put_contents($metaPath, json_encode([
        'source_url' => $url,
        'slug' => $slug,
        'model' => $model,
        'language' => $language,
        'created_at' => now()->toDateTimeString(),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // Step 4: Cleanup WAV
    @unlink($wavPath);

    $this->info('Done. Subtitle file: '.$outputPath);
    $this->line('Public URL (requires storage:link): '.Storage::url('subtitles/'.$slug.'.json'));
    return 0;
})->purpose('Download audio and generate Whisper subtitles for a Quran recitation');

// ── quran:fetch-captions ───────────────────────────────────────────────
Artisan::command('quran:fetch-captions {videoId} {surah} {--force : Overwrite existing aligned file}', function () {
    $videoId = $this->argument('videoId');
    $surah = (int) $this->argument('surah');
    $force = $this->option('force');

    if ($surah < 1 || $surah > 114) {
        $this->error('Surah number must be between 1 and 114.');
        return 1;
    }

    $subtitleDir = storage_path('app/public/subtitles');
    $alignedPath = "{$subtitleDir}/{$videoId}.aligned.json";

    if (file_exists($alignedPath) && !$force) {
        $this->warn("Aligned file already exists: {$alignedPath}");
        $this->line('Use --force to overwrite.');
        return 1;
    }

    // Step 1: Fetch captions from YouTube
    $this->info("Fetching Arabic captions for {$videoId}...");
    $captionService = app(YouTubeCaptionService::class);
    $whisperData = $captionService->fetch($videoId);

    if (!$whisperData || empty($whisperData['segments'])) {
        $this->error('No Arabic captions found for this video.');
        return 1;
    }

    $wordCount = 0;
    foreach ($whisperData['segments'] as $seg) {
        $wordCount += count($seg['words'] ?? []);
    }
    $this->info("Fetched " . count($whisperData['segments']) . " caption events ({$wordCount} words).");

    // Step 2: Save raw Whisper-format JSON
    if (!is_dir($subtitleDir)) {
        mkdir($subtitleDir, 0755, true);
    }

    $rawPath = "{$subtitleDir}/{$videoId}.json";
    file_put_contents($rawPath, json_encode($whisperData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $this->line("Saved raw captions: {$rawPath}");

    // Step 3: Run alignment
    $this->info("Aligning with Surah {$surah}...");
    $aligner = app(WhisperAligner::class);
    $result = $aligner->align($whisperData, $surah);

    if (!$result) {
        $this->error('Alignment failed — could not fetch Quran text from API.');
        return 1;
    }

    // Step 4: Save aligned file
    file_put_contents($alignedPath, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // Output stats
    $stats = $result['match_stats'] ?? [];
    $segments = $result['segments'] ?? [];
    $quranSegments = array_filter($segments, fn($s) => !isset($s['type']));
    $gapSegments = array_filter($segments, fn($s) => ($s['type'] ?? '') === 'gap');
    $gapDuration = array_sum(array_map(fn($s) => $s['end'] - $s['start'], $gapSegments));

    $this->newLine();
    $this->info('Alignment complete.');
    $this->table(['Metric', 'Value'], [
        ['Quran words', $stats['quran_words'] ?? 0],
        ['Caption words', $stats['whisper_words'] ?? 0],
        ['Matched words', $stats['matched'] ?? 0],
        ['Confidence', ($result['confidence'] ?? 0) * 100 . '%'],
        ['Al-Fatihah detected', ($result['includes_fatihah'] ?? false) ? 'Yes' : 'No'],
        ['Ayah segments', count($quranSegments)],
        ['Gap segments', count($gapSegments)],
        ['Total gap duration', round($gapDuration, 1) . 's'],
    ]);
    $this->line("Output: {$alignedPath}");

    return 0;
})->purpose('Fetch YouTube captions and align with Quran text (no Whisper needed)');

// ── quran:cache-surahs ─────────────────────────────────────────────────
Artisan::command('quran:cache-surahs {--all : Cache all 114 surahs}', function () {
    $quranApi = app(QuranApiService::class);

    $this->info('Caching surah list...');
    $surahs = $quranApi->getSurahList();

    if (empty($surahs)) {
        $this->error('Failed to fetch surah list from API.');
        return 1;
    }

    $this->info('Cached '.count($surahs).' surahs in the list.');

    if ($this->option('all')) {
        $this->info('Caching all 114 surahs...');
        $bar = $this->output->createProgressBar(114);
        $bar->start();

        for ($i = 1; $i <= 114; $i++) {
            $quranApi->getSurahText($i);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('All 114 surahs cached.');
    } else {
        // Cache surahs detected from curated reciters' video titles
        $reciters = config('quran.reciters', []);
        $cached = [];

        foreach ($reciters as $reciter) {
            $videoId = $reciter['videoId'] ?? '';
            if (!$videoId) continue;

            try {
                $response = Http::timeout(5)->get('https://www.youtube.com/oembed', [
                    'url' => "https://www.youtube.com/watch?v={$videoId}",
                    'format' => 'json',
                ]);

                if (!$response->ok()) continue;

                $title = $response->json('title') ?? '';
                $detected = $quranApi->detectSurahFromTitle($title);

                if ($detected && !in_array($detected['number'], $cached, true)) {
                    $this->line("  {$reciter['name']}: {$detected['englishName']} (#{$detected['number']})");
                    $quranApi->getSurahText($detected['number']);
                    $cached[] = $detected['number'];
                }
            } catch (\Throwable $e) {
                $this->warn("  {$reciter['name']}: skipped ({$e->getMessage()})");
            }
        }

        $this->info('Cached '.count($cached).' surahs from curated reciters.');
    }

    return 0;
})->purpose('Pre-cache Quran surah data from api.alquran.cloud');

// ── quran:download-timing ─────────────────────────────────────────────
Artisan::command('quran:download-timing {--reciter=7 : QDC reciter ID (default: 7 = Mishary Rashid al-Afasy)}', function () {
    $reciterId = (int) $this->option('reciter');
    $baseUrl = 'https://api.qurancdn.com/api/qdc/audio/reciters/' . $reciterId . '/audio_files';

    $timingDir = storage_path('app/quran-timing');
    if (!is_dir($timingDir)) {
        mkdir($timingDir, 0755, true);
    }

    $this->info("Downloading QDC timing data for reciter ID {$reciterId}...");
    $bar = $this->output->createProgressBar(114);
    $bar->start();

    $allData = [];
    $successCount = 0;

    for ($surah = 1; $surah <= 114; $surah++) {
        try {
            $response = Http::timeout(15)->get($baseUrl, [
                'chapter' => $surah,
                'segments' => 'true',
            ]);

            if ($response->ok()) {
                $audioFiles = $response->json('audio_files') ?? [];

                if (!empty($audioFiles)) {
                    $verseTimings = $audioFiles[0]['verse_timings'] ?? [];

                    if (!empty($verseTimings)) {
                        // Convert to our keyed format: { "1:1": { duration_sec, segments, ... } }
                        $surahData = [];
                        foreach ($verseTimings as $vt) {
                            $key = $vt['verse_key'] ?? '';
                            if (!$key) continue;

                            $durationMs = $vt['duration'] ?? (($vt['timestamp_to'] ?? 0) - ($vt['timestamp_from'] ?? 0));
                            $surahData[$key] = [
                                'duration_sec' => round($durationMs / 1000, 3),
                                'timestamp_from' => sprintf('%02d:%02d:%02d.%03d', ($vt['timestamp_from'] ?? 0) / 3600000, (($vt['timestamp_from'] ?? 0) % 3600000) / 60000, (($vt['timestamp_from'] ?? 0) % 60000) / 1000, ($vt['timestamp_from'] ?? 0) % 1000),
                                'timestamp_to' => sprintf('%02d:%02d:%02d.%03d', ($vt['timestamp_to'] ?? 0) / 3600000, (($vt['timestamp_to'] ?? 0) % 3600000) / 60000, (($vt['timestamp_to'] ?? 0) % 60000) / 1000, ($vt['timestamp_to'] ?? 0) % 1000),
                                'segments' => $vt['segments'] ?? [],
                            ];
                        }

                        // Save per-surah file
                        file_put_contents(
                            "{$timingDir}/surah-{$surah}.json",
                            json_encode($surahData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        );

                        // Merge into combined file
                        foreach ($surahData as $key => $segment) {
                            $allData[$key] = $segment;
                        }

                        $successCount++;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Continue to next surah on failure
        }

        $bar->advance();

        // Small delay to avoid rate limiting
        usleep(100_000);
    }

    $bar->finish();
    $this->newLine();

    // Save combined reference file
    file_put_contents(
        "{$timingDir}/reference.json",
        json_encode($allData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

    $this->info("Downloaded timing data for {$successCount}/114 surahs.");
    $this->line("Saved to: {$timingDir}/");

    return 0;
})->purpose('Download QUL reference timing data for Quran recitation word-level timing');

// ── quran:align ──────────────────────────────────────────────────────
Artisan::command('quran:align {slug} {surah} {--force : Overwrite existing aligned file}', function () {
    $slug = $this->argument('slug');
    $surah = (int) $this->argument('surah');
    $force = $this->option('force');

    if ($surah < 1 || $surah > 114) {
        $this->error('Surah number must be between 1 and 114.');
        return 1;
    }

    $whisperPath = storage_path("app/public/subtitles/{$slug}.json");
    if (!file_exists($whisperPath)) {
        $this->error("Whisper file not found: {$whisperPath}");
        return 1;
    }

    $outputPath = storage_path("app/public/subtitles/{$slug}.aligned.json");
    if (file_exists($outputPath) && !$force) {
        $this->warn("Aligned file already exists: {$outputPath}");
        $this->line('Use --force to overwrite.');
        return 1;
    }

    $this->info("Aligning {$slug} with Surah {$surah}...");

    $whisperData = json_decode(file_get_contents($whisperPath), true);
    if (!$whisperData || empty($whisperData['segments'])) {
        $this->error('Invalid or empty Whisper JSON.');
        return 1;
    }

    $aligner = app(WhisperAligner::class);
    $result = $aligner->align($whisperData, $surah);

    if (!$result) {
        $this->error('Alignment failed — could not fetch Quran text from API.');
        return 1;
    }

    file_put_contents($outputPath, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // Output stats
    $stats = $result['match_stats'] ?? [];
    $segments = $result['segments'] ?? [];
    $quranSegments = array_filter($segments, fn($s) => !isset($s['type']));
    $gapSegments = array_filter($segments, fn($s) => ($s['type'] ?? '') === 'gap');
    $gapDuration = array_sum(array_map(fn($s) => $s['end'] - $s['start'], $gapSegments));

    $this->newLine();
    $this->info('Alignment complete.');
    $this->table(['Metric', 'Value'], [
        ['Quran words', $stats['quran_words'] ?? 0],
        ['Whisper words', $stats['whisper_words'] ?? 0],
        ['Matched words', $stats['matched'] ?? 0],
        ['Confidence', ($result['confidence'] ?? 0) * 100 . '%'],
        ['Al-Fatihah detected', ($result['includes_fatihah'] ?? false) ? 'Yes' : 'No'],
        ['Ayah segments', count($quranSegments)],
        ['Gap segments', count($gapSegments)],
        ['Total gap duration', round($gapDuration, 1) . 's'],
    ]);
    $this->line("Output: {$outputPath}");

    return 0;
})->purpose('Align Whisper transcription with Quran text for accurate subtitles');

// ── quran:align-all ──────────────────────────────────────────────────
Artisan::command('quran:align-all {--force : Overwrite existing aligned files}', function () {
    $reciters = config('quran.reciters', []);
    $quranApi = app(QuranApiService::class);
    $captionService = app(YouTubeCaptionService::class);
    $force = $this->option('force');

    if (empty($reciters)) {
        $this->warn('No reciters configured in config/quran.php.');
        return 0;
    }

    $this->info('Aligning subtitles for ' . count($reciters) . ' reciters...');
    $results = [];

    foreach ($reciters as $reciter) {
        $videoId = $reciter['videoId'] ?? '';
        $name = $reciter['name'] ?? 'Unknown';

        if (!$videoId) {
            $results[] = [$name, $videoId, '-', 'No video ID'];
            continue;
        }

        $alignedPath = storage_path("app/public/subtitles/{$videoId}.aligned.json");

        // Check if aligned file already exists
        if (file_exists($alignedPath) && !$force) {
            $results[] = [$name, $videoId, '-', 'Already aligned (use --force)'];
            continue;
        }

        // Detect surah from YouTube title
        $surahNumber = null;
        try {
            $response = Http::timeout(5)->get('https://www.youtube.com/oembed', [
                'url' => "https://www.youtube.com/watch?v={$videoId}",
                'format' => 'json',
            ]);
            if ($response->ok()) {
                $title = $response->json('title') ?? '';
                $detected = $quranApi->detectSurahFromTitle($title);
                if ($detected) {
                    $surahNumber = $detected['number'];
                }
            }
        } catch (\Throwable $e) {
            // Fall through
        }

        if (!$surahNumber) {
            $results[] = [$name, $videoId, '-', 'Could not detect surah from title'];
            continue;
        }

        // Try existing Whisper file first, then fall back to YouTube captions
        $whisperPath = storage_path("app/public/subtitles/{$videoId}.json");
        $source = 'whisper';

        if (file_exists($whisperPath)) {
            // Use existing Whisper file
            $this->line("  Aligning {$name} (Surah {$surahNumber}) from Whisper file...");

            $exitCode = Artisan::call('quran:align', [
                'slug' => $videoId,
                'surah' => $surahNumber,
                '--force' => $force,
            ]);
        } else {
            // Fetch YouTube captions and align
            $this->line("  Aligning {$name} (Surah {$surahNumber}) from YouTube captions...");
            $source = 'captions';

            $exitCode = Artisan::call('quran:fetch-captions', [
                'videoId' => $videoId,
                'surah' => $surahNumber,
                '--force' => $force,
            ]);
        }

        if ($exitCode === 0 && file_exists($alignedPath)) {
            $aligned = json_decode(file_get_contents($alignedPath), true);
            $confidence = ($aligned['confidence'] ?? 0) * 100;
            $results[] = [$name, $videoId, "Surah {$surahNumber}", "OK ({$confidence}% via {$source})"];
        } else {
            $results[] = [$name, $videoId, "Surah {$surahNumber}", 'FAILED'];
        }
    }

    $this->newLine();
    $this->table(['Reciter', 'Video ID', 'Surah', 'Status'], $results);

    return 0;
})->purpose('Align subtitles for all curated reciters (uses YouTube captions or Whisper files)');
