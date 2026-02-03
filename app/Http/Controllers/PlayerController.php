<?php

namespace App\Http\Controllers;

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

    public function validateUrl(Request $request, QuranUrlInspector $inspector): JsonResponse
    {
        $data = $request->validate([
            'url' => ['required', 'string', 'max:2048'],
        ]);

        $result = $inspector->inspect($data['url']);

        // If valid YouTube URL, check for existing subtitle file
        if (($result['ok'] ?? false) && ($result['type'] ?? '') === 'youtube') {
            $videoId = $this->extractVideoId($data['url']);
            if ($videoId && file_exists(storage_path("app/public/subtitles/{$videoId}.json"))) {
                $result['subtitle_slug'] = $videoId;
            }
        }

        return response()->json($result);
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
