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
        ]);
    }

    public function validateUrl(Request $request, QuranUrlInspector $inspector): JsonResponse
    {
        $data = $request->validate([
            'url' => ['required', 'string', 'max:2048'],
        ]);

        return response()->json($inspector->inspect($data['url']));
    }
}
