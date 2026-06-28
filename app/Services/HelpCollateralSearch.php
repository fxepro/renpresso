<?php

namespace App\Services;

class HelpCollateralSearch
{
    /** @param list<array{id: string, title: string, tags: list<string>, body: string}> $articles */
    public function __construct(private array $articles) {}

    public static function fromConfig(): self
    {
        return new self(config('help_collateral.articles', []));
    }

    /** @return list<string> */
    public function initialSuggestions(): array
    {
        return $this->defaultSuggestions();
    }

    /**
     * @return array{answer: string, sources: list<array{id: string, title: string}>, suggestions: list<string>}
     */
    public function answer(string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return [
                'answer' => 'Ask a question about Renpresso — for example how rent collection, leases, or maintenance works.',
                'sources' => [],
                'suggestions' => $this->initialSuggestions(),
            ];
        }

        $terms = $this->terms($query);
        $scored = [];

        foreach ($this->articles as $article) {
            $haystack = strtolower(implode(' ', [
                $article['title'],
                $article['body'],
                implode(' ', $article['tags'] ?? []),
            ]));
            $score = 0;
            foreach ($terms as $term) {
                if (str_contains($haystack, $term)) {
                    $score += strlen($term) >= 5 ? 3 : 1;
                }
                if (str_contains(strtolower($article['title']), $term)) {
                    $score += 2;
                }
            }
            if ($score > 0) {
                $scored[] = ['article' => $article, 'score' => $score];
            }
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);
        $top = array_slice($scored, 0, 3);

        if ($top === []) {
            return [
                'answer' => "I couldn't find a close match in the help docs for \"{$query}\". Try different keywords, browse Help → Collateral, or send feedback on the right — our team reads every note.",
                'sources' => [],
                'suggestions' => $this->defaultSuggestions(),
            ];
        }

        $primary = $top[0]['article'];
        $answer = $primary['body'];
        if (count($top) > 1) {
            $extra = array_map(fn ($row) => $row['article']['title'], array_slice($top, 1));
            $answer .= ' Related topics: '.implode(', ', $extra).'.';
        }

        $sources = array_map(fn ($row) => [
            'id' => $row['article']['id'],
            'title' => $row['article']['title'],
        ], $top);

        $suggestions = $this->relatedSuggestions($primary['id']);

        return [
            'answer' => $answer,
            'sources' => $sources,
            'suggestions' => $suggestions,
        ];
    }

    /** @return list<string> */
    private function terms(string $query): array
    {
        $normalized = preg_replace('/[^a-z0-9\s]/i', ' ', strtolower($query)) ?? '';
        $parts = preg_split('/\s+/', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $stop = ['a', 'an', 'the', 'is', 'are', 'how', 'do', 'i', 'my', 'to', 'for', 'what', 'can', 'does'];

        return array_values(array_filter($parts, fn ($w) => strlen($w) >= 2 && ! in_array($w, $stop, true)));
    }

    /** @return list<string> */
    private function defaultSuggestions(): array
    {
        return [
            'How does rent collection work?',
            'How do I submit a maintenance request?',
            'Where do I update payment methods?',
        ];
    }

    /** @return list<string> */
    private function relatedSuggestions(string $excludeId): array
    {
        $out = [];
        foreach ($this->articles as $article) {
            if ($article['id'] === $excludeId) {
                continue;
            }
            $out[] = 'Tell me about '.$article['title'];
            if (count($out) >= 3) {
                break;
            }
        }

        return $out;
    }
}
