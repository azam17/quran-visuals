<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
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
