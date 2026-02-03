#!/usr/bin/env python3
"""
Whisper transcription wrapper for Quran Visuals.

Usage:
    python3 whisper_transcribe.py --audio input.wav --output output.json --model base --language ar

Requires: pip3 install openai-whisper
"""

import argparse
import json
import sys


def main():
    parser = argparse.ArgumentParser(description="Transcribe audio with OpenAI Whisper")
    parser.add_argument("--audio", required=True, help="Path to input audio file (WAV preferred)")
    parser.add_argument("--output", required=True, help="Path to output JSON file")
    parser.add_argument("--model", default="base", help="Whisper model size (tiny, base, small, medium, large)")
    parser.add_argument("--language", default="ar", help="Language code (e.g. ar, en)")
    args = parser.parse_args()

    try:
        import whisper
    except ImportError:
        print("Error: openai-whisper is not installed.", file=sys.stderr)
        print("Install it with: pip3 install openai-whisper", file=sys.stderr)
        sys.exit(1)

    print(f"Loading Whisper model '{args.model}'...")
    model = whisper.load_model(args.model)

    print(f"Transcribing '{args.audio}' (language: {args.language})...")
    result = model.transcribe(
        args.audio,
        language=args.language,
        word_timestamps=True,
    )

    # Build output format
    segments = []
    for seg in result.get("segments", []):
        words = []
        for w in seg.get("words", []):
            words.append({
                "text": w["word"].strip(),
                "start": round(w["start"], 3),
                "end": round(w["end"], 3),
            })

        segments.append({
            "id": seg["id"],
            "start": round(seg["start"], 3),
            "end": round(seg["end"], 3),
            "text": seg["text"].strip(),
            "words": words,
        })

    output = {
        "language": args.language,
        "segments": segments,
    }

    with open(args.output, "w", encoding="utf-8") as f:
        json.dump(output, f, ensure_ascii=False, indent=2)

    print(f"Wrote {len(segments)} segments to '{args.output}'.")


if __name__ == "__main__":
    main()
