<?php

namespace App\Services;

class ArabicNormalizer
{
    /**
     * Normalize Arabic text for comparison: strip diacritics, normalize letter variants.
     */
    public function normalize(string $text): string
    {
        // Strip tashkeel/harakat (fathah, dammah, kasrah, shadda, sukun, tanwin, etc.)
        $text = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $text);

        // Strip Quranic annotation marks (small letters, meem, etc.)
        $text = preg_replace('/[\x{06D6}-\x{06ED}]/u', '', $text);

        // Normalize alef variants → bare alef
        // Alef with hamza above/below, alef with madda, alef wasla
        $text = preg_replace('/[\x{0622}\x{0623}\x{0625}\x{0671}]/u', "\u{0627}", $text);

        // Normalize teh-marbuta → heh
        $text = str_replace("\u{0629}", "\u{0647}", $text);

        // Normalize alef-maksura → yeh
        $text = str_replace("\u{0649}", "\u{064A}", $text);

        // Remove tatweel (kashida)
        $text = str_replace("\u{0640}", '', $text);

        // Remove zero-width characters (ZWJ, ZWNJ, BOM, etc.)
        $text = preg_replace('/[\x{200B}-\x{200F}\x{FEFF}\x{202A}-\x{202E}]/u', '', $text);

        // Collapse whitespace
        $text = preg_replace('/\s+/u', ' ', trim($text));

        return $text;
    }

    /**
     * Split normalized text into individual words.
     */
    public function words(string $text): array
    {
        $normalized = $this->normalize($text);
        if ($normalized === '') {
            return [];
        }
        return preg_split('/\s+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Compute similarity score between two Arabic words (0.0 to 1.0).
     * Both inputs should already be normalized.
     */
    public function similarity(string $a, string $b): float
    {
        if ($a === '' || $b === '') {
            return 0.0;
        }

        // Exact match
        if ($a === $b) {
            return 1.0;
        }

        // One contains the other (handles Whisper merging/splitting)
        if (mb_strpos($a, $b) !== false || mb_strpos($b, $a) !== false) {
            $shorter = min(mb_strlen($a), mb_strlen($b));
            $longer = max(mb_strlen($a), mb_strlen($b));
            return 0.7 + 0.2 * ($shorter / $longer);
        }

        // Levenshtein-based similarity
        // PHP's levenshtein() works on bytes, so use a character-level approach for Arabic
        $distance = $this->mbLevenshtein($a, $b);
        $maxLen = max(mb_strlen($a), mb_strlen($b));

        return max(0.0, 1.0 - ($distance / $maxLen));
    }

    /**
     * Multi-byte Levenshtein distance for Arabic text.
     */
    private function mbLevenshtein(string $a, string $b): int
    {
        $aChars = mb_str_split($a);
        $bChars = mb_str_split($b);
        $aLen = count($aChars);
        $bLen = count($bChars);

        if ($aLen === 0) return $bLen;
        if ($bLen === 0) return $aLen;

        // Use two-row optimization for memory efficiency
        $prev = range(0, $bLen);
        $curr = [];

        for ($i = 1; $i <= $aLen; $i++) {
            $curr[0] = $i;
            for ($j = 1; $j <= $bLen; $j++) {
                $cost = ($aChars[$i - 1] === $bChars[$j - 1]) ? 0 : 1;
                $curr[$j] = min(
                    $prev[$j] + 1,       // deletion
                    $curr[$j - 1] + 1,    // insertion
                    $prev[$j - 1] + $cost // substitution
                );
            }
            $prev = $curr;
        }

        return $curr[$bLen];
    }
}
