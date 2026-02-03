<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class QuranApiService
{
    private const API_BASE = 'https://api.alquran.cloud/v1';
    private const CACHE_DAYS = 30;

    /**
     * Names that are common personal names or too short — require "surah/surat" prefix context.
     */
    private const AMBIGUOUS_NAMES = [
        // Personal names
        'muhammad', 'mohammad', 'mohammed', 'mohamad',
        'ibrahim', 'ibraheem',
        'yusuf', 'yousuf', 'yousaf', 'yoosuf', 'yousef',
        'yunus', 'yoonus',
        'hud', 'hood',
        'nuh', 'nooh', 'nouh',
        'maryam', 'mariam',
        'luqman', 'lukman',
        // Short aliases (≤4 chars) that match inside unrelated words or reciter names
        'raad', 'rum', 'tur', 'tin', 'nur', 'saad', 'qaf', 'rad',
        'nisa', 'saba', 'taha', 'hajj', 'saff', 'jinn', 'fajr',
        'layl', 'lail', 'duha', 'fil', 'nas', 'nasr', 'asr',
        'ala', 'nahl', 'fath',
    ];

    /**
     * Fetch and cache the list of all 114 surahs.
     */
    public function getSurahList(): array
    {
        return Cache::remember('quran_surah_list', now()->addDays(self::CACHE_DAYS), function () {
            $response = Http::timeout(10)->get(self::API_BASE . '/surah');

            if (!$response->ok()) {
                return [];
            }

            return $response->json('data') ?? [];
        });
    }

    /**
     * Fetch and cache a surah's ayahs (Uthmani text).
     */
    public function getSurahText(int $surahNumber): ?array
    {
        if ($surahNumber < 1 || $surahNumber > 114) {
            return null;
        }

        return Cache::remember("quran_surah_{$surahNumber}", now()->addDays(self::CACHE_DAYS), function () use ($surahNumber) {
            $response = Http::timeout(10)->get(self::API_BASE . "/surah/{$surahNumber}/quran-uthmani");

            if (!$response->ok()) {
                return null;
            }

            return $response->json('data');
        });
    }

    /**
     * Detect surah (and optional ayah range) from a YouTube video title.
     *
     * Returns ['number' => int, 'englishName' => string, 'numberOfAyahs' => int,
     *          'ayahStart' => int|null, 'ayahEnd' => int|null] or null.
     */
    public function detectSurahFromTitle(string $title): ?array
    {
        $normalized = $this->normalizeTitle($title);

        // Step 0: Special phrases (highest priority, exact semantic matches)
        $specialResult = $this->detectSpecialPhrase($normalized);
        if ($specialResult) {
            return $specialResult;
        }

        // Step 0B: Juz references — "Juz Amma", "Juz 30", "Juz Tabarak"
        $juzResult = $this->detectJuzReference($normalized);
        if ($juzResult) {
            return $juzResult;
        }

        // Step 1: Try numeric patterns — "surah 67", "067", "surah_067"
        // Negative lookahead: reject "surah 105-114" multi-surah ranges
        if (preg_match('/(?:surah|sura|surat)\s*[#_\-]?\s*(\d{1,3})\b(?!\s*-\s*\d)/i', $normalized, $m)) {
            $num = (int) $m[1];
            if ($num >= 1 && $num <= 114) {
                $result = $this->buildResult($num);
                if ($result) {
                    return $this->detectAyahRange($normalized, $result);
                }
            }
        }

        // Step 1B: Parse surah:ayah notation — "36:1-83", "2:255", "Quran 67:1-30"
        if (preg_match('/\b(\d{1,3}):(\d+)(?:\s*[-–—]\s*(\d+))?\b/', $normalized, $m)) {
            $num = (int) $m[1];
            if ($num >= 1 && $num <= 114) {
                $result = $this->buildResult($num);
                if ($result) {
                    $result['ayahStart'] = max(1, (int) $m[2]);
                    if (!empty($m[3])) {
                        $result['ayahEnd'] = min($result['numberOfAyahs'] ?: 999, (int) $m[3]);
                    } else {
                        $result['ayahEnd'] = $result['ayahStart'];
                    }
                    return $result;
                }
            }
        }

        // Standalone 2-3 digit number at start (e.g., "067 - Al Mulk")
        // Negative lookahead prevents matching ranges like "105-114"
        if (preg_match('/^(\d{2,3})\s*[\-\.\|](?!\d)/', $normalized, $m)) {
            $num = (int) $m[1];
            if ($num >= 1 && $num <= 114) {
                $result = $this->buildResult($num);
                if ($result) {
                    return $this->detectAyahRange($normalized, $result);
                }
            }
        }

        // Step 2: Build lookup map and try longest-match
        $map = $this->buildLookupMap();

        // Sort by key length descending so longer names match first
        uksort($map, fn($a, $b) => mb_strlen($b) - mb_strlen($a));

        // First pass: try names that appear right after "surah/surat" — highest confidence
        foreach ($map as $alias => $number) {
            $isArabic = preg_match('/[\x{0600}-\x{06FF}]/u', $alias);
            if ($isArabic) {
                $pattern = '/(?:سورة)\s*' . preg_quote($alias, '/') . '/u';
            } else {
                $pattern = '/(?:surah|surat|sura)\s+' . preg_quote($alias, '/') . '(?:\b|$)/iu';
            }
            if (preg_match($pattern, $normalized)) {
                $result = $this->buildResult($number);
                if ($result) {
                    return $this->detectAyahRange($normalized, $result);
                }
            }
        }

        // Second pass: match any name in the title (non-ambiguous only)
        foreach ($map as $alias => $number) {
            $isArabic = preg_match('/[\x{0600}-\x{06FF}]/u', $alias);

            // Skip ambiguous personal names / short aliases without explicit "surah" prefix
            if (!$isArabic && in_array($alias, self::AMBIGUOUS_NAMES, true)) {
                continue;
            }

            if ($isArabic) {
                if (str_contains($normalized, $alias)) {
                    $result = $this->buildResult($number);
                    if ($result) {
                        return $this->detectAyahRange($normalized, $result);
                    }
                }
            } else {
                $pattern = '/(?:^|\b)' . preg_quote($alias, '/') . '(?:\b|$)/u';
                if (preg_match($pattern, $normalized)) {
                    $result = $this->buildResult($number);
                    if ($result) {
                        return $this->detectAyahRange($normalized, $result);
                    }
                }
            }
        }

        return null;
    }

    /**
     * Detect special phrases like "ayatul kursi", "last two ayah baqarah".
     */
    private function detectSpecialPhrase(string $normalized): ?array
    {
        $phrases = config('quran.special_phrases', []);

        foreach ($phrases as $phrase => $data) {
            if (str_contains($normalized, $phrase)) {
                $result = $this->buildResult($data['surah']);
                if ($result) {
                    $result['ayahStart'] = $data['ayahStart'];
                    $result['ayahEnd'] = $data['ayahEnd'];
                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * Detect Juz references like "Juz Amma", "Juz 30", "Juz Tabarak".
     */
    private function detectJuzReference(string $normalized): ?array
    {
        // Match "juz amma", "juz tabarak", etc.
        $juzMap = config('quran.juz_map', []);

        foreach ($juzMap as $name => $data) {
            $pattern = '/\bjuz[\'"]?\s+' . preg_quote($name, '/') . '\b/i';
            if (preg_match($pattern, $normalized)) {
                $result = $this->buildResult($data['surah']);
                if ($result) {
                    $result['ayahStart'] = $data['ayahStart'] ?? null;
                    $result['ayahEnd'] = null;
                    return $result;
                }
            }
        }

        // Match "juz 30", "juz 29", etc. — map number to first surah of that juz
        if (preg_match('/\bjuz[\'"]?\s*(\d{1,2})\b/i', $normalized, $m)) {
            $juzNum = (int) $m[1];
            foreach ($juzMap as $name => $data) {
                if (($data['juz'] ?? 0) === $juzNum) {
                    $result = $this->buildResult($data['surah']);
                    if ($result) {
                        $result['ayahStart'] = $data['ayahStart'] ?? null;
                        $result['ayahEnd'] = null;
                        return $result;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Detect ayah range from title patterns like "ayah 1-50", "verse 255", "ayat 1 to 10".
     */
    private function detectAyahRange(string $normalized, array $result): array
    {
        // Pattern: "ayah 1-50", "ayat 1 - 50", "verse 1-50", "ayah 1 to 50"
        if (preg_match('/(?:ayah?|ayat|verse|ayahs?|verses?)\s*(\d+)\s*(?:[-–—\s]|to)\s*(\d+)/i', $normalized, $m)) {
            $result['ayahStart'] = max(1, (int) $m[1]);
            $result['ayahEnd'] = min($result['numberOfAyahs'], (int) $m[2]);
            return $result;
        }

        // Single ayah: "ayah 255", "verse 3"
        if (preg_match('/(?:ayah?|ayat|verse)\s*(\d+)\b/i', $normalized, $m)) {
            $num = (int) $m[1];
            if ($num >= 1 && $num <= $result['numberOfAyahs']) {
                $result['ayahStart'] = $num;
                $result['ayahEnd'] = $num;
                return $result;
            }
        }

        return $result;
    }

    /**
     * Build a result array for a surah number using cached API data.
     */
    private function buildResult(int $number): ?array
    {
        $surahs = $this->getSurahList();

        foreach ($surahs as $surah) {
            if (($surah['number'] ?? 0) === $number) {
                return [
                    'number' => $number,
                    'englishName' => $surah['englishName'] ?? "Surah {$number}",
                    'numberOfAyahs' => $surah['numberOfAyahs'] ?? 0,
                    'ayahStart' => null,
                    'ayahEnd' => null,
                ];
            }
        }

        // Fallback if surah list is not cached yet
        if ($number >= 1 && $number <= 114) {
            return [
                'number' => $number,
                'englishName' => "Surah {$number}",
                'numberOfAyahs' => 0,
                'ayahStart' => null,
                'ayahEnd' => null,
            ];
        }

        return null;
    }

    /**
     * Build the full lookup map: aliases from config + API english/arabic names.
     */
    private function buildLookupMap(): array
    {
        $map = [];

        // Config aliases
        foreach (config('quran.surah_aliases', []) as $alias => $number) {
            $map[strtolower($alias)] = $number;
        }

        // API english names
        $surahs = $this->getSurahList();
        foreach ($surahs as $surah) {
            $number = $surah['number'] ?? 0;
            if ($number < 1) continue;

            // English name (e.g., "Al-Faatiha")
            if (!empty($surah['englishName'])) {
                $map[strtolower($surah['englishName'])] = $number;
                // Also without "Al-" prefix
                $withoutAl = preg_replace('/^al[- ]?/i', '', $surah['englishName']);
                if ($withoutAl !== $surah['englishName']) {
                    $map[strtolower($withoutAl)] = $number;
                }
            }

            // English name translation (e.g., "The Opening")
            if (!empty($surah['englishNameTranslation'])) {
                $map[strtolower($surah['englishNameTranslation'])] = $number;
            }

            // Arabic name — strip diacritics so it matches plain Arabic titles
            if (!empty($surah['name'])) {
                $plainName = $this->stripArabicDiacritics($surah['name']);
                $map[$plainName] = $number;
                // Without "سورة" prefix
                $withoutSurah = preg_replace('/^سورة\s*/u', '', $plainName);
                if ($withoutSurah !== $plainName) {
                    $map[trim($withoutSurah)] = $number;
                }
            }
        }

        return $map;
    }

    /**
     * Get reference timing proportions for a surah from QUL data.
     *
     * Returns an array keyed by ayah number:
     * [1 => ['proportion' => 0.032, 'wordProportions' => [0.25, 0.30, ...]], ...]
     * or null if no timing data is available.
     */
    public function getReferenceTiming(int $surahNumber): ?array
    {
        if ($surahNumber < 1 || $surahNumber > 114) {
            return null;
        }

        $path = storage_path("app/quran-timing/surah-{$surahNumber}.json");

        if (!file_exists($path)) {
            return null;
        }

        $raw = json_decode(file_get_contents($path), true);
        if (!$raw || !is_array($raw)) {
            return null;
        }

        // Calculate total surah duration from segment data
        $totalDuration = 0;
        $ayahData = [];

        foreach ($raw as $key => $segment) {
            // Key format: "1:1", "1:2", etc.
            $parts = explode(':', $key);
            $ayahNum = (int) ($parts[1] ?? 0);
            if ($ayahNum < 1) continue;

            $duration = $segment['duration_sec'] ?? 0;
            $totalDuration += $duration;

            // Extract word proportions from segments
            $wordProportions = [];
            $segments = $segment['segments'] ?? [];
            if (!empty($segments)) {
                $segmentDurations = [];
                foreach ($segments as $seg) {
                    $segDuration = ($seg[2] ?? 0) - ($seg[1] ?? 0);
                    $segmentDurations[] = max(0, $segDuration);
                }
                $segTotal = array_sum($segmentDurations);
                if ($segTotal > 0) {
                    $wordProportions = array_map(fn($d) => round($d / $segTotal, 4), $segmentDurations);
                }
            }

            $ayahData[$ayahNum] = [
                'duration' => $duration,
                'wordProportions' => $wordProportions,
            ];
        }

        if ($totalDuration <= 0) {
            return null;
        }

        // Convert durations to proportions
        $result = [];
        foreach ($ayahData as $ayahNum => $data) {
            $result[$ayahNum] = [
                'proportion' => round($data['duration'] / $totalDuration, 6),
                'wordProportions' => $data['wordProportions'],
            ];
        }

        return $result;
    }

    /**
     * Strip Arabic diacritics (harakat, small letters, etc.) from text.
     */
    private function stripArabicDiacritics(string $text): string
    {
        return preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}]/u', '', $text);
    }

    /**
     * Normalize a title for matching: lowercase, strip diacritics, simplify.
     */
    private function normalizeTitle(string $title): string
    {
        $title = Str::lower($title);

        // Strip Arabic diacritics so API names match plain Arabic in titles
        $title = $this->stripArabicDiacritics($title);

        // Transliterate common Latin diacritical marks
        $title = str_replace(
            ['á', 'à', 'â', 'ä', 'ā', 'é', 'è', 'ê', 'ë', 'ē', 'í', 'ì', 'î', 'ï', 'ī', 'ó', 'ò', 'ô', 'ö', 'ō', 'ú', 'ù', 'û', 'ü', 'ū'],
            ['a', 'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'u'],
            $title
        );

        // Replace common separators with space, but preserve hyphens between digits (e.g., "105-114", "1-83")
        $title = preg_replace('/(\d)\-(\d)/', '$1{{HYPHEN}}$2', $title);
        $title = preg_replace('/[_\-\|·•]+/', ' ', $title);
        $title = str_replace('{{HYPHEN}}', '-', $title);

        // Collapse whitespace
        $title = preg_replace('/\s+/', ' ', trim($title));

        return $title;
    }
}
