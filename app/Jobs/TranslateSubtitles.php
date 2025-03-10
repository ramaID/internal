<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TranslateSubtitles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600; // 10 minutes

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The subtitles to be translated.
     *
     * @var array
     */
    protected $subtitles;

    /**
     * The unique job ID for tracking progress.
     *
     * @var string
     */
    protected $jobId;

    /**
     * Create a new job instance.
     */
    public function __construct(array $subtitles, string $jobId)
    {
        $this->subtitles = $subtitles;
        $this->jobId = $jobId;

        // Initialize progress in cache
        Cache::put("subtitle_translation_{$jobId}", [
            'status' => 'processing',
            'progress' => 0,
            'total' => count($subtitles),
            'translated' => [],
        ], 3600); // Store for 1 hour
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $translatedSubtitles = [];
        $total = count($this->subtitles);

        // Process subtitles in batches to avoid token limits
        foreach (array_chunk($this->subtitles, 20) as $batchIndex => $batch) {
            // Process each subtitle individually
            foreach ($batch as $index => $subtitle) {
                $textToTranslate = $subtitle['text'];

                // Call OpenAI API for each subtitle individually
                try {
                    $response = OpenAI::chat()->create([
                        'model' => 'gpt-3.5-turbo',
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => 'You are a professional translator. Translate the following subtitle from Indonesian to English. Maintain the original meaning and tone. Return only the translation.'
                            ],
                            [
                                'role' => 'user',
                                'content' => $textToTranslate
                            ]
                        ],
                        'temperature' => 0.3,
                    ]);

                    // Get translated text directly from the response
                    $translatedText = trim($response->choices[0]->message->content);

                    // Create translated subtitle
                    $translatedSubtitle = $subtitle;
                    $translatedSubtitle['text'] = $translatedText;
                    $translatedSubtitles[] = $translatedSubtitle;

                    // Calculate progress
                    $currentIndex = ($batchIndex * 20) + $index + 1;
                    $progress = min(100, round(($currentIndex / $total) * 100));

                    // Update progress in cache
                    Cache::put("subtitle_translation_{$this->jobId}", [
                        'status' => 'processing',
                        'progress' => $progress,
                        'total' => $total,
                        'completed' => $currentIndex,
                        'translated' => $translatedSubtitles,
                    ], 3600);
                } catch (\Exception $e) {
                    // Log error but continue with other subtitles
                    Log::error("Error translating subtitle: {$e->getMessage()}");

                    // Create untranslated subtitle to preserve order
                    $translatedSubtitle = $subtitle;
                    $translatedSubtitle['text'] = "[Translation error] " . $subtitle['text'];
                    $translatedSubtitles[] = $translatedSubtitle;
                }
            }
        }

        // Save final result to cache
        Cache::put("subtitle_translation_{$this->jobId}", [
            'status' => 'completed',
            'progress' => 100,
            'total' => $total,
            'completed' => $total,
            'original_subtitles' => $this->subtitles,
            'translated_subtitles' => $translatedSubtitles,
        ], 86400); // Store for 24 hours
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Cache::put("subtitle_translation_{$this->jobId}", [
            'status' => 'failed',
            'error' => $exception->getMessage(),
        ], 3600);

        Log::error("Subtitle translation job failed: {$exception->getMessage()}");
    }
}
