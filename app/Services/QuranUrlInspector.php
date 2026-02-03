<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class QuranUrlInspector
{
    public function inspect(string $url): array
    {
        $url = trim($url);

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return $this->blocked('Invalid URL.');
        }

        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');

        if ($this->isYouTubeHost($host)) {
            return $this->inspectYouTube($url);
        }

        return $this->inspectAudio($url);
    }

    private function inspectYouTube(string $url): array
    {
        $videoId = $this->extractYouTubeId($url);
        if ($videoId === null) {
            return $this->blocked('Could not detect a YouTube video ID.');
        }

        $meta = $this->fetchYouTubeOembed($url);
        $title = Arr::get($meta, 'title', '');
        $author = Arr::get($meta, 'author_name', '');

        if (!$this->isQuranMeta($title, $author)) {
            return $this->blocked('This does not look like Quran recitation based on the title/channel.');
        }

        return [
            'ok' => true,
            'type' => 'youtube',
            'title' => $title,
            'author' => $author,
            'embed_url' => 'https://www.youtube-nocookie.com/embed/'.$videoId.'?autoplay=1&rel=0&controls=0&enablejsapi=1&origin='.urlencode(config('app.url')).'&modestbranding=1',
            'audio_url' => null,
            'reactive' => false,
            'warning' => null,
        ];
    }

    private function inspectAudio(string $url): array
    {
        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
        $allowedExtensions = config('quran.allowed_audio_extensions', []);

        if (!in_array($extension, $allowedExtensions, true)) {
            return $this->blocked('Only direct audio files are supported (mp3, m4a, wav, ogg).');
        }

        if (!$this->isQuranMeta($url, '')) {
            return $this->blocked('This audio file does not look like Quran recitation by filename.');
        }

        $contentType = $this->probeContentType($url);
        if ($contentType && !Str::contains($contentType, ['audio/', 'application/octet-stream'])) {
            return $this->blocked('The provided URL does not appear to be an audio file.');
        }

        return [
            'ok' => true,
            'type' => 'audio',
            'title' => basename(parse_url($url, PHP_URL_PATH) ?? ''),
            'author' => null,
            'embed_url' => null,
            'audio_url' => $url,
            'reactive' => true,
            'warning' => null,
        ];
    }

    private function probeContentType(string $url): ?string
    {
        try {
            $response = Http::timeout(5)->head($url);
            if ($response->ok()) {
                return $response->header('Content-Type');
            }
        } catch (\Throwable $e) {
            // Ignore network issues and fallback to extension-only checks.
        }

        return null;
    }

    private function isYouTubeHost(string $host): bool
    {
        return $host === 'youtube.com'
            || $host === 'www.youtube.com'
            || $host === 'm.youtube.com'
            || $host === 'youtu.be';
    }

    private function extractYouTubeId(string $url): ?string
    {
        $parsed = parse_url($url);
        $host = strtolower($parsed['host'] ?? '');
        $path = trim($parsed['path'] ?? '', '/');

        if ($host === 'youtu.be' && $path !== '') {
            return $this->sanitizeYouTubeId($path);
        }

        if (Str::startsWith($path, 'embed/')) {
            return $this->sanitizeYouTubeId(Str::after($path, 'embed/'));
        }

        if (Str::startsWith($path, 'shorts/')) {
            return $this->sanitizeYouTubeId(Str::after($path, 'shorts/'));
        }

        parse_str($parsed['query'] ?? '', $query);
        if (!empty($query['v'])) {
            return $this->sanitizeYouTubeId($query['v']);
        }

        return null;
    }

    private function sanitizeYouTubeId(string $id): ?string
    {
        $id = trim($id);
        if (preg_match('/^[a-zA-Z0-9_-]{6,20}$/', $id)) {
            return $id;
        }

        return null;
    }

    private function fetchYouTubeOembed(string $url): array
    {
        try {
            $response = Http::timeout(5)->get('https://www.youtube.com/oembed', [
                'url' => $url,
                'format' => 'json',
            ]);

            if ($response->ok()) {
                return $response->json();
            }
        } catch (\Throwable $e) {
            // Ignore and fall back to empty meta.
        }

        return [];
    }

    private function isQuranMeta(string $title, string $author): bool
    {
        $allowedChannels = array_map('strtolower', config('quran.allowed_channels', []));
        $blockedKeywords = config('quran.blocked_keywords', []);

        $titleLower = strtolower($title);
        $authorLower = strtolower($author);

        foreach ($allowedChannels as $channel) {
            if ($channel !== '' && str_contains($authorLower, $channel)) {
                return true;
            }
        }

        if ($this->containsAny($titleLower, $blockedKeywords)) {
            return false;
        }

        return $this->containsAny($titleLower, config('quran.keywords', []))
            || $this->containsAny($authorLower, config('quran.keywords', []));
    }

    private function containsAny(string $haystack, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            $keyword = strtolower(trim($keyword));
            if ($keyword !== '' && str_contains($haystack, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function blocked(string $reason): array
    {
        return [
            'ok' => false,
            'reason' => $reason,
        ];
    }
}
