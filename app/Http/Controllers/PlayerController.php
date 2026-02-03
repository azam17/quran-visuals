<?php

namespace App\Http\Controllers;

use App\Services\QuranApiService;
use App\Services\QuranUrlInspector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PlayerController extends Controller
{
    public function index(): Response
    {
        return response()->view('player', [
            'presets' => config('quran.presets'),
            'reciters' => config('quran.reciters', []),
        ]);
    }

    public function validateUrl(Request $request, QuranUrlInspector $inspector, QuranApiService $quranApi): JsonResponse
    {
        $data = $request->validate([
            'url' => ['required', 'string', 'max:2048'],
        ]);

        $result = $inspector->inspect($data['url']);

        if ($result['ok'] ?? false) {
            // Priority 1: Existing Whisper subtitle file (backward compat)
            if (($result['type'] ?? '') === 'youtube') {
                $videoId = $this->extractVideoId($data['url']);
                if ($videoId && file_exists(storage_path("app/public/subtitles/{$videoId}.json"))) {
                    $result['subtitle_slug'] = $videoId;
                    return response()->json($result);
                }
            }

            // Priority 2: Detect surah from video title via Quran API
            $title = $result['title'] ?? '';
            if ($title !== '') {
                $detected = $quranApi->detectSurahFromTitle($title);
                if ($detected) {
                    $result['surah_number'] = $detected['number'];
                    $result['surah_name'] = $detected['englishName'];
                    $result['surah_ayah_count'] = $detected['numberOfAyahs'];
                    $result['surah_ayah_start'] = $detected['ayahStart'];
                    $result['surah_ayah_end'] = $detected['ayahEnd'];
                    $result['subtitle_source'] = 'quran_api';
                }
            }
        }

        return response()->json($result);
    }

    /**
     * Return cached surah ayahs for the frontend subtitle system.
     */
    public function surahData(int $number, QuranApiService $quranApi): JsonResponse
    {
        if ($number < 1 || $number > 114) {
            return response()->json(['error' => 'Invalid surah number.'], 404);
        }

        $surahData = $quranApi->getSurahText($number);

        if (!$surahData) {
            return response()->json(['error' => 'Could not fetch surah data.'], 502);
        }

        // Load QUL reference timing if available
        $timing = $quranApi->getReferenceTiming($number);
        $timingAyahs = $timing['ayahs'] ?? [];

        $ayahs = collect($surahData['ayahs'] ?? [])->map(function ($ayah) use ($timingAyahs) {
            $text = trim($ayah['text'] ?? '');
            // Strip BOM and zero-width characters
            $text = preg_replace('/[\x{FEFF}\x{200B}\x{200C}\x{200D}]/u', '', $text);

            $ayahNum = $ayah['numberInSurah'] ?? 0;
            $ayahTiming = null;
            if (isset($timingAyahs[$ayahNum])) {
                $ayahTiming = $timingAyahs[$ayahNum];
            }

            return [
                'numberInSurah' => $ayahNum,
                'text' => $text,
                'words' => preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY),
                'timing' => $ayahTiming,
            ];
        })->values()->all();

        return response()->json([
            'surah' => [
                'number' => $surahData['number'] ?? $number,
                'englishName' => $surahData['englishName'] ?? "Surah {$number}",
                'numberOfAyahs' => $surahData['numberOfAyahs'] ?? count($ayahs),
            ],
            'totalTimingDuration' => $timing['totalDuration'] ?? null,
            'ayahs' => $ayahs,
        ]);
    }

    private function extractVideoId(string $url): ?string
    {
        $parsed = parse_url($url);
        $host = strtolower($parsed['host'] ?? '');

        if ($host === 'youtu.be') {
            $path = trim($parsed['path'] ?? '', '/');
            return $path !== '' ? $path : null;
        }

        parse_str($parsed['query'] ?? '', $query);

        return $query['v'] ?? null;
    }
}
