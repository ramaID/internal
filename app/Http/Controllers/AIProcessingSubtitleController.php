<?php

namespace App\Http\Controllers;

use App\Services\TextProcessing;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AIProcessingSubtitleController extends Controller
{
    public function __construct(
        public TextProcessing $textService
    ) {
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Media $media, Request $request)
    {
        $title = 'Processing Subtitle: ' . $media->id;

        $stream = $media->stream();
        $content = stream_get_contents($stream);
        // Close the stream after reading
        fclose($stream);

        $subtitles = $this->textService->parseSbvToJson($content);

        $postUrl = route('resource.project.ai-translation', $media);

        return view('ai-processing-subtitle', compact('title', 'subtitles', 'postUrl'));
    }
}
