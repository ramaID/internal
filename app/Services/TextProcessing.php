<?php

namespace App\Services;

final class TextProcessing
{
    /**
     * Parse SBV content to JSON format
     *
     * @param string $sbvContent
     * @return array
     */
    public function parseSbvToJson(string $sbvContent): array
    {
        $lines = explode("\n", $sbvContent);
        $subtitles = [];
        $currentSubtitle = null;

        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line)) {
                // Empty line indicates end of current subtitle entry
                if ($currentSubtitle !== null) {
                    $subtitles[] = $currentSubtitle;
                    $currentSubtitle = null;
                }
                continue;
            }

            // Check if line is a timestamp
            if (preg_match('/^\d+:\d+:\d+\.\d+,\d+:\d+:\d+\.\d+$/', $line)) {
                list($start, $end) = explode(',', $line);
                $currentSubtitle = [
                    'start_time' => $this->formatTimestamp($start),
                    'end_time' => $this->formatTimestamp($end),
                    'text' => ''
                ];
            } elseif ($currentSubtitle !== null) {
                // Add text to current subtitle
                if (!empty($currentSubtitle['text'])) {
                    $currentSubtitle['text'] .= ' ' . $line;
                } else {
                    $currentSubtitle['text'] = $line;
                }
            }
        }

        // Add the last subtitle if there is one
        if ($currentSubtitle !== null) {
            $subtitles[] = $currentSubtitle;
        }

        return $subtitles;
    }

    /**
     * Format timestamp for better readability (optional)
     *
     * @param string $timestamp
     * @return string
     */
    private function formatTimestamp(string $timestamp): string
    {
        // You can customize timestamp formatting here if needed
        return $timestamp;
    }
}
