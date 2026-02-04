<?php

namespace App\Services;

class WhisperAligner
{
    private ArabicNormalizer $normalizer;
    private QuranApiService $quranApi;

    /** Minimum similarity score to accept a word match. */
    private const MATCH_THRESHOLD = 0.5;

    /** How many Whisper words ahead to search for a match. */
    private const SEARCH_WINDOW = 10;

    public function __construct(ArabicNormalizer $normalizer, QuranApiService $quranApi)
    {
        $this->normalizer = $normalizer;
        $this->quranApi = $quranApi;
    }

    /**
     * Align Whisper transcription output with correct Quran text.
     *
     * @param array $whisperData  Parsed Whisper JSON (has 'segments' with 'words')
     * @param int   $surahNumber  Target surah number (1-114)
     * @return array|null  Aligned subtitle data or null on failure
     */
    public function align(array $whisperData, int $surahNumber): ?array
    {
        $whisperWords = $this->flattenWhisperWords($whisperData);
        if (empty($whisperWords)) {
            return null;
        }

        // Build the ordered Quran word list: Al-Fatihah (if surah != 1) + target surah
        $includesFatihah = false;
        $quranWords = [];

        if ($surahNumber !== 1) {
            $fatihahWords = $this->getQuranWords(1);
            if (!empty($fatihahWords)) {
                $quranWords = array_merge($quranWords, $fatihahWords);
                $includesFatihah = true;
            }
        }

        $surahWords = $this->getQuranWords($surahNumber);
        if (empty($surahWords)) {
            return null;
        }
        $quranWords = array_merge($quranWords, $surahWords);

        // Normalize both word lists
        $normalizedWhisper = array_map(
            fn($w) => $this->normalizer->normalize($w['text']),
            $whisperWords
        );
        $normalizedQuran = array_map(
            fn($w) => $this->normalizer->normalize($w['text']),
            $quranWords
        );

        // Sequential matching: walk through Quran words, find best match in forward window.
        // Both lists are sequential, so the whisper pointer only moves forward.
        $whisperIdx = 0;
        $totalWhisper = count($whisperWords);
        $matchCount = 0;

        foreach ($quranWords as $qi => &$qWord) {
            $bestScore = 0;
            $bestWi = -1;

            // Search forward window from current whisper position
            $searchEnd = min($whisperIdx + self::SEARCH_WINDOW, $totalWhisper);
            for ($wi = $whisperIdx; $wi < $searchEnd; $wi++) {
                $score = $this->normalizer->similarity($normalizedQuran[$qi], $normalizedWhisper[$wi]);
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestWi = $wi;
                }
            }

            if ($bestScore >= self::MATCH_THRESHOLD && $bestWi >= 0) {
                $qWord['start'] = $whisperWords[$bestWi]['start'];
                $qWord['end'] = $whisperWords[$bestWi]['end'];
                $qWord['matched'] = true;
                $qWord['matchScore'] = $bestScore;
                // Advance whisper pointer past the matched word
                $whisperIdx = $bestWi + 1;
                $matchCount++;
            }
        }
        unset($qWord);

        // Interpolate timestamps for unmatched words from neighbors
        $this->interpolateTimestamps($quranWords);

        // Regroup matched words back into ayah segments
        $segments = $this->buildSegments($quranWords, $includesFatihah, $surahNumber);

        // Insert gap segments for periods with no Quran content
        $segments = $this->insertGaps($segments, $whisperWords);

        $confidence = count($quranWords) > 0
            ? round($matchCount / count($quranWords), 2)
            : 0;

        return [
            'language' => 'ar',
            'source' => 'whisper_aligned',
            'surah_number' => $surahNumber,
            'includes_fatihah' => $includesFatihah,
            'confidence' => $confidence,
            'match_stats' => [
                'quran_words' => count($quranWords),
                'whisper_words' => $totalWhisper,
                'matched' => $matchCount,
            ],
            'segments' => $segments,
        ];
    }

    /**
     * Flatten all Whisper segments into a single ordered word list with timestamps.
     */
    private function flattenWhisperWords(array $whisperData): array
    {
        $words = [];
        foreach ($whisperData['segments'] ?? [] as $segment) {
            foreach ($segment['words'] ?? [] as $word) {
                if (isset($word['text'], $word['start'], $word['end'])) {
                    $words[] = [
                        'text' => $word['text'],
                        'start' => (float) $word['start'],
                        'end' => (float) $word['end'],
                    ];
                }
            }
        }
        return $words;
    }

    /**
     * Get ordered Quran words for a surah with ayah metadata.
     */
    private function getQuranWords(int $surahNumber): array
    {
        $surahData = $this->quranApi->getSurahText($surahNumber);
        if (!$surahData || empty($surahData['ayahs'])) {
            return [];
        }

        $words = [];
        foreach ($surahData['ayahs'] as $ayah) {
            $ayahNum = $ayah['numberInSurah'] ?? 0;
            $text = trim($ayah['text'] ?? '');
            // Strip BOM and zero-width characters
            $text = preg_replace('/[\x{FEFF}\x{200B}\x{200C}\x{200D}]/u', '', $text);

            $ayahWords = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($ayahWords as $word) {
                $words[] = [
                    'text' => $word,
                    'surahNumber' => $surahNumber,
                    'ayahNumber' => $ayahNum,
                    'start' => null,
                    'end' => null,
                    'matched' => false,
                    'matchScore' => 0,
                ];
            }
        }

        return $words;
    }

    /**
     * Interpolate timestamps for unmatched words based on their matched neighbors.
     */
    private function interpolateTimestamps(array &$words): void
    {
        $total = count($words);

        // Find runs of unmatched words between matched anchors
        $i = 0;
        while ($i < $total) {
            if (!empty($words[$i]['matched'])) {
                $i++;
                continue;
            }

            // Find the start of an unmatched run
            $runStart = $i;
            while ($i < $total && empty($words[$i]['matched'])) {
                $i++;
            }
            $runEnd = $i - 1;

            // Find the anchor before and after the run
            $prevEnd = null;
            $nextStart = null;

            if ($runStart > 0 && !empty($words[$runStart - 1]['matched'])) {
                $prevEnd = $words[$runStart - 1]['end'];
            }
            if ($runEnd < $total - 1 && !empty($words[$runEnd + 1]['matched'])) {
                $nextStart = $words[$runEnd + 1]['start'];
            }

            // Interpolate
            if ($prevEnd !== null && $nextStart !== null) {
                // Both anchors available — distribute evenly
                $gap = $nextStart - $prevEnd;
                $runLen = $runEnd - $runStart + 1;
                $wordDuration = $gap / ($runLen + 0.001);
                for ($j = $runStart; $j <= $runEnd; $j++) {
                    $offset = $j - $runStart;
                    $words[$j]['start'] = round($prevEnd + $offset * $wordDuration, 2);
                    $words[$j]['end'] = round($prevEnd + ($offset + 1) * $wordDuration, 2);
                }
            } elseif ($prevEnd !== null) {
                // Only left anchor — estimate 0.5s per word
                for ($j = $runStart; $j <= $runEnd; $j++) {
                    $offset = $j - $runStart;
                    $words[$j]['start'] = round($prevEnd + $offset * 0.5, 2);
                    $words[$j]['end'] = round($prevEnd + ($offset + 1) * 0.5, 2);
                }
            } elseif ($nextStart !== null) {
                // Only right anchor — estimate 0.5s per word going backwards
                $runLen = $runEnd - $runStart + 1;
                $startTime = max(0, $nextStart - $runLen * 0.5);
                $wordDuration = ($nextStart - $startTime) / $runLen;
                for ($j = $runStart; $j <= $runEnd; $j++) {
                    $offset = $j - $runStart;
                    $words[$j]['start'] = round($startTime + $offset * $wordDuration, 2);
                    $words[$j]['end'] = round($startTime + ($offset + 1) * $wordDuration, 2);
                }
            }
            // If no anchors at all, timestamps remain null (should be rare)
        }
    }

    /**
     * Group matched words back into ayah-level segments.
     */
    private function buildSegments(array $words, bool $includesFatihah, int $targetSurah): array
    {
        $segments = [];
        $currentAyah = null;
        $currentSurah = null;
        $currentWords = [];

        foreach ($words as $word) {
            $ayah = $word['ayahNumber'];
            $surah = $word['surahNumber'];

            if ($ayah !== $currentAyah || $surah !== $currentSurah) {
                // Flush the previous ayah segment
                if (!empty($currentWords)) {
                    $segments[] = $this->createSegment(
                        count($segments),
                        $currentWords,
                        $currentAyah,
                        $currentSurah
                    );
                }
                $currentAyah = $ayah;
                $currentSurah = $surah;
                $currentWords = [];
            }

            $currentWords[] = $word;
        }

        // Flush last segment
        if (!empty($currentWords)) {
            $segments[] = $this->createSegment(
                count($segments),
                $currentWords,
                $currentAyah,
                $currentSurah
            );
        }

        return $segments;
    }

    /**
     * Create a single ayah segment from grouped words.
     */
    private function createSegment(int $id, array $words, int $ayahNumber, int $surahNumber): array
    {
        $wordData = [];
        foreach ($words as $w) {
            $wordData[] = [
                'text' => $w['text'],
                'start' => $w['start'],
                'end' => $w['end'],
            ];
        }

        $texts = array_map(fn($w) => $w['text'], $words);
        $starts = array_filter(array_column($words, 'start'), fn($v) => $v !== null);
        $ends = array_filter(array_column($words, 'end'), fn($v) => $v !== null);

        return [
            'id' => $id,
            'text' => implode(' ', $texts),
            'start' => !empty($starts) ? min($starts) : 0,
            'end' => !empty($ends) ? max($ends) : 0,
            'ayahNumber' => $ayahNumber,
            'surahNumber' => $surahNumber,
            'words' => $wordData,
        ];
    }

    /**
     * Insert gap segments for periods between ayahs where the reciter is silent
     * or performing non-Quran speech (takbir, ameen, etc.).
     */
    private function insertGaps(array $segments, array $whisperWords): array
    {
        if (empty($segments)) {
            return $segments;
        }

        // Determine the full audio range from Whisper data
        $audioEnd = 0;
        if (!empty($whisperWords)) {
            $audioEnd = max(array_column($whisperWords, 'end'));
        }

        $result = [];
        $gapId = 1000; // High ID to avoid collision with ayah segments

        for ($i = 0; $i < count($segments); $i++) {
            $seg = $segments[$i];

            // Check for gap before this segment
            $prevEnd = ($i > 0) ? $segments[$i - 1]['end'] : 0;
            $gapDuration = $seg['start'] - $prevEnd;

            if ($gapDuration > 1.0) { // Only insert gap if > 1 second
                $result[] = [
                    'id' => $gapId++,
                    'text' => '',
                    'start' => $prevEnd,
                    'end' => $seg['start'],
                    'type' => 'gap',
                ];
            }

            $result[] = $seg;
        }

        // Gap after last segment to end of audio
        $lastEnd = end($segments)['end'] ?? 0;
        if ($audioEnd - $lastEnd > 1.0) {
            $result[] = [
                'id' => $gapId++,
                'text' => '',
                'start' => $lastEnd,
                'end' => $audioEnd,
                'type' => 'gap',
            ];
        }

        return $result;
    }
}
