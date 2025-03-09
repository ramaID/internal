<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProcessingSubtitleController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Media $media, Request $request)
    {
        \Laravolt\SemanticForm\SemanticForm::class;
        $title = 'Processing Subtitle: ' . $media->id;

        $stream = $media->stream();
        $content = stream_get_contents($stream);
        // Close the stream after reading
        fclose($stream);

        $subtitles = $this->parseSbvToJson($content);
        $vocabularies = $this->getVocabularies();

        // Generate suggested vocabularies based on subtitle content
        $suggestedVocabularies = $this->suggestVocabularies($subtitles);

        return view('processing-subtitle', compact('title', 'subtitles', 'vocabularies', 'suggestedVocabularies'));
    }

    /**
     * Parse SBV content to JSON format
     *
     * @param string $sbvContent
     * @return array
     */
    private function parseSbvToJson(string $sbvContent): array
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

    protected function getVocabularies(): array
    {
        return [
            ['find' => 'kisy Dev', 'replace' => 'QisthiDev', 'case_sensitive' => true, 'whole_word' => true],
            ['find' => 'larafold', 'replace' => 'Laravolt', 'case_sensitive' => false, 'whole_word' => true],
            ['find' => 'Lara volold', 'replace' => 'Laravolt', 'case_sensitive' => false, 'whole_word' => true],
            ['find' => 'directtory', 'replace' => 'directory', 'case_sensitive' => false, 'whole_word' => true],
            ['find' => 'lar ', 'replace' => 'Laravel ', 'case_sensitive' => false, 'whole_word' => false],
            ['find' => 'Lara Fel', 'replace' => 'Laravel', 'case_sensitive' => false, 'whole_word' => true],
            ['find' => 'Rama id Laravolt', 'replace' => 'ramaid/laravolt', 'case_sensitive' => false, 'whole_word' => true],
            ['find' => 'composer Jasson', 'replace' => 'composer.json', 'case_sensitive' => false, 'whole_word' => true],
            ['find' => 'laraavel', 'replace' => 'Laravel', 'case_sensitive' => false, 'whole_word' => true],
        ];
    }

    /**
     * Suggests vocabulary corrections based on subtitle content
     *
     * @param array $subtitles
     * @return array
     */
    private function suggestVocabularies(array $subtitles): array
    {
        // Get all text content from subtitles
        $allText = '';
        foreach ($subtitles as $subtitle) {
            $allText .= ' ' . $subtitle['text'];
        }

        $suggested = [];
        $knownWords = $this->getKnownWords();
        $existingFinds = array_column($this->getVocabularies(), 'find');

        // Common tech terms that might be misspelled
        $techTerms = [
            'laravel' => ['laravell', 'larvel', 'laravle', 'lar', 'lara'],
            'php' => ['php7', 'php8', 'php5'],
            'javascript' => ['js', 'javascript', 'javascrpt'],
            'database' => ['databse', 'datbase', 'db'],
            'repository' => ['repo', 'repositry'],
            'directory' => ['directtory', 'directry', 'dir'],
            'package' => ['packge', 'pkg'],
            'query' => ['qury', 'querry'],
            'controller' => ['cntrl', 'contrl'],
            'model' => ['mdl', 'modl'],
            'migration' => ['migrtion', 'migrate'],
            'composer' => ['composr', 'compoer'],
            'artisan' => ['artsan', 'artsn'],
            'middleware' => ['middlewar', 'mdlware'],
            'authentication' => ['auth', 'authen'],
            'authorization' => ['authz', 'author'],
            'bootstrap' => ['bootstrp', 'bstrap'],
            'configuration' => ['config', 'confg'],
            'eloquent' => ['elqnt', 'eloqnt'],
            'validation' => ['valid', 'validtion'],
        ];

        // Word pattern for finding potential terms
        $pattern = '/\b[a-zA-Z]{3,}(?:[a-zA-Z]+)?\b/';
        preg_match_all($pattern, $allText, $matches);

        $words = array_count_values($matches[0]);

        // Find potentially misspelled words or common abbreviations
        foreach ($words as $word => $count) {
            // Skip words that are already in our vocabulary corrections
            if (in_array($word, $existingFinds)) {
                continue;
            }

            // Check if it's a known misspelling of a tech term
            foreach ($techTerms as $correctTerm => $misspellings) {
                if (in_array(strtolower($word), $misspellings)) {
                    $suggested[] = [
                        'find' => $word,
                        'replace' => $correctTerm,
                        'case_sensitive' => false,
                        'whole_word' => true,
                        'confidence' => 'high',
                        'count' => $count
                    ];
                    continue 2; // Skip to the next word
                }
            }

            // Check against known words
            $similarWords = $this->findSimilarWords($word, $knownWords);
            if (!empty($similarWords)) {
                $suggested[] = [
                    'find' => $word,
                    'replace' => $similarWords[0], // Use the most similar word
                    'case_sensitive' => false,
                    'whole_word' => true,
                    'confidence' => 'medium',
                    'count' => $count
                ];
            }

            // Limit to 10 suggestions
            if (count($suggested) >= 10) {
                break;
            }
        }

        // Sort by occurrence count
        usort($suggested, function ($a, $b) {
            return $b['count'] - $a['count'];
        });

        // Remove the count field before returning
        return array_map(function ($item) {
            unset($item['count']);
            unset($item['confidence']);
            return $item;
        }, $suggested);
    }

    /**
     * Find similar words using levenshtein distance
     *
     * @param string $word
     * @param array $knownWords
     * @return array
     */
    private function findSimilarWords(string $word, array $knownWords): array
    {
        $word = strtolower($word);
        $similar = [];

        foreach ($knownWords as $knownWord) {
            $distance = levenshtein($word, strtolower($knownWord));

            // Accept words with small levenshtein distance
            if ($distance <= 2) {
                $similar[$knownWord] = $distance;
            }
        }

        // Sort by similarity (smaller distance = more similar)
        asort($similar);

        return array_keys($similar);
    }

    /**
     * Get dictionary of known programming-related words
     *
     * @return array
     */
    private function getKnownWords(): array
    {
        return [
            'Laravel',
            'PHP',
            'JavaScript',
            'Database',
            'Migration',
            'Controller',
            'Model',
            'View',
            'Route',
            'Middleware',
            'Eloquent',
            'Query',
            'Builder',
            'Package',
            'Composer',
            'Artisan',
            'Config',
            'Environment',
            'Authentication',
            'Authorization',
            'Validation',
            'Request',
            'Response',
            'Session',
            'Cookie',
            'Cache',
            'Queue',
            'Job',
            'Event',
            'Listener',
            'Notification',
            'Mail',
            'Blade',
            'Template',
            'Component',
            'Service',
            'Provider',
            'Facade',
            'Contract',
            'Interface',
            'Trait',
            'Class',
            'Method',
            'Function',
            'Variable',
            'Constant',
            'Array',
            'Object',
            'String',
            'Integer',
            'Boolean',
            'Float',
            'Repository',
            'Factory',
            'Seeder',
            'Database',
            'Table',
            'Column',
            'Index',
            'Foreign',
            'Key',
            'Primary',
            'Laravolt',
            'QisthiDev',
            'Directory',
            'API',
            'REST',
            'JSON',
            'XML',
            'HTTP',
            'Semantic',
            'Form',
            'Bootstrap',
            'Tailwind',
            'CSS'
        ];
    }
}
