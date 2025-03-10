<?php

namespace App\Http\Controllers;

use App\Jobs\TranslateSubtitles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class StoreAIProcessingSubtitleController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $subtitles = $request->input('subtitles');

        // Generate a unique job ID
        $jobId = Str::uuid()->toString();

        // Dispatch the job to the queue
        TranslateSubtitles::dispatch($subtitles, $jobId);

        return response()->json([
            'message' => 'Translation job started',
            'job_id' => $jobId,
        ]);
    }

    /**
     * Check the status of a translation job
     */
    public function status($jobId)
    {
        $job = Cache::get("subtitle_translation_{$jobId}");

        if (!$job) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Translation job not found',
            ], 404);
        }

        return response()->json($job);
    }

    /**
     * Get the results of a completed translation job
     */
    public function result($jobId)
    {
        $job = Cache::get("subtitle_translation_{$jobId}");

        if (!$job) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Translation job not found',
            ], 404);
        }

        if ($job['status'] !== 'completed') {
            return response()->json([
                'status' => $job['status'],
                'message' => 'Translation job is not completed yet',
                'progress' => $job['progress'] ?? 0,
            ]);
        }

        return response()->json([
            'status' => 'completed',
            'original_subtitles' => $job['original_subtitles'],
            'translated_subtitles' => $job['translated_subtitles'],
        ]);
    }
}
