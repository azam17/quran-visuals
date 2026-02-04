<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;

class YouTubeCaptionService
{
    private WhisperAligner $aligner;

    public function __construct(WhisperAligner $aligner)
    {
        $this->aligner = $aligner;
    }

    /**
     * Fetch Arabic auto-captions from YouTube via yt-dlp and convert to Whisper-compatible format.
     *
     * @param string $videoId  YouTube video ID
     * @return array|null  Whisper-format array with 'segments', or null on failure
     */
    public function fetch(string $videoId): ?array
    {
        $binary = config('quran.yt_dlp_binary', 'yt-dlp');
        $tmpDir = storage_path('app/tmp-captions');

        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $url = "https://www.youtube.com/watch?v={$videoId}";

        // Run yt-dlp to fetch auto-generated Arabic captions in json3 format
        $process = Process::timeout(15)->run([
            $binary,
            '--write-auto-sub',
            '--sub-lang', 'ar',
            '--sub-format', 'json3',
            '--skip-download',
            '-o', "{$tmpDir}/{$videoId}",
            $url,
        ]);

        if (!$process->successful()) {
            return null;
        }

        // yt-dlp writes the file as {videoId}.ar.json3
        $json3Path = "{$tmpDir}/{$videoId}.ar.json3";
        if (!file_exists($json3Path)) {
            return null;
        }

        $raw = file_get_contents($json3Path);
        @unlink($json3Path);

        $json3 = json_decode($raw, true);
        if (!$json3 || empty($json3['events'])) {
            return null;
        }

        return $this->convertJson3ToWhisperFormat($json3);
    }

    /**
     * Fetch captions, align with Quran text, save the aligned file, and return the slug.
     *
     * @param string $videoId     YouTube video ID
     * @param int    $surahNumber Target surah (1-114)
     * @return string|null  Subtitle slug (e.g. "MlCXPjpTVZk.aligned") or null on failure
     */
    public function fetchAndAlign(string $videoId, int $surahNumber): ?string
    {
        $subtitleDir = storage_path('app/public/subtitles');
        $alignedPath = "{$subtitleDir}/{$videoId}.aligned.json";

        // Already cached — return immediately
        if (file_exists($alignedPath)) {
            return "{$videoId}.aligned";
        }

        // Fetch captions from YouTube
        $whisperData = $this->fetch($videoId);
        if (!$whisperData || empty($whisperData['segments'])) {
            return null;
        }

        // Run alignment
        $result = $this->aligner->align($whisperData, $surahNumber);
        if (!$result) {
            return null;
        }

        // Save aligned file
        if (!is_dir($subtitleDir)) {
            mkdir($subtitleDir, 0755, true);
        }

        file_put_contents($alignedPath, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return "{$videoId}.aligned";
    }

    /**
     * Convert YouTube json3 caption format to Whisper-compatible segment/word format.
     *
     * json3 structure:
     *   events: [ { tStartMs, dDurationMs, segs: [{utf8, tOffsetMs?}, ...] }, ... ]
     *
     * Output (Whisper-compatible):
     *   segments: [ { start, end, text, words: [{text, start, end}, ...] }, ... ]
     */
    private function convertJson3ToWhisperFormat(array $json3): array
    {
        $segments = [];

        foreach ($json3['events'] ?? [] as $event) {
            $tStartMs = $event['tStartMs'] ?? 0;
            $dDurationMs = $event['dDurationMs'] ?? 0;
            $segs = $event['segs'] ?? [];

            if (empty($segs)) {
                continue;
            }

            // Build word list from this event
            $words = [];
            foreach ($segs as $seg) {
                $text = $seg['utf8'] ?? '';
                $text = ltrim($text);

                // Skip empty, newline-only, and music/noise tags
                if ($text === '' || $text === "\n" || $text === "\r\n") {
                    continue;
                }
                if (preg_match('/^\[.*\]$/', $text)) {
                    continue;
                }

                $tOffsetMs = $seg['tOffsetMs'] ?? 0;
                $wordStartMs = $tStartMs + $tOffsetMs;

                $words[] = [
                    'text' => $text,
                    'startMs' => $wordStartMs,
                ];
            }

            if (empty($words)) {
                continue;
            }

            // Calculate end times: each word ends when the next starts, last word ends at event end
            $eventEndMs = $tStartMs + $dDurationMs;
            $whisperWords = [];

            for ($i = 0; $i < count($words); $i++) {
                $startMs = $words[$i]['startMs'];
                $endMs = isset($words[$i + 1]) ? $words[$i + 1]['startMs'] : $eventEndMs;

                // Split multi-word segments (YouTube sometimes groups words in one seg)
                $subWords = preg_split('/\s+/u', $words[$i]['text'], -1, PREG_SPLIT_NO_EMPTY);
                if (empty($subWords)) {
                    continue;
                }

                $subDuration = ($endMs - $startMs) / count($subWords);
                foreach ($subWords as $j => $subWord) {
                    $whisperWords[] = [
                        'text' => $subWord,
                        'start' => round(($startMs + $j * $subDuration) / 1000, 3),
                        'end' => round(($startMs + ($j + 1) * $subDuration) / 1000, 3),
                    ];
                }
            }

            if (empty($whisperWords)) {
                continue;
            }

            $segmentText = implode(' ', array_column($whisperWords, 'text'));
            $segments[] = [
                'start' => $whisperWords[0]['start'],
                'end' => end($whisperWords)['end'],
                'text' => $segmentText,
                'words' => $whisperWords,
            ];
        }

        return ['segments' => $segments];
    }
}
