<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * ML Classification and Recommendation module (SDD 5.8).
 *
 * Calls out to the Python Flask ML microservice (ml_service/app.py) for
 * text classification and content-based recommendation scoring. Falls
 * back to a lightweight keyword-based classifier if the ML service is
 * unreachable, so topic creation never fails due to the ML layer being
 * down (matches the platform's offline-resilience design).
 */
class TopicClassifierService
{
    private const ML_SERVICE_URL = 'http://127.0.0.1:5001';

    private const CATEGORY_KEYWORDS = [
        'Networking' => ['network', 'tcp', 'ip', 'router', 'protocol'],
        'Databases' => ['database', 'sql', 'query', 'schema', 'index'],
        'Programming' => ['code', 'function', 'bug', 'compile', 'algorithm'],
        'Machine Learning' => ['model', 'training', 'classifier', 'dataset', 'neural'],
        'Software Design' => ['architecture', 'design', 'uml', 'pattern', 'module'],
    ];

    public function classify(string $title): string
    {
        try {
            $response = Http::timeout(3)->post(self::ML_SERVICE_URL . '/classify', [
                'text' => $title,
            ]);

            if ($response->successful() && $response->json('category')) {
                return $response->json('category');
            }
        } catch (\Throwable $e) {
            Log::warning('ML service unreachable, falling back to keyword classifier: ' . $e->getMessage());
        }

        return $this->classifyLocally($title);
    }

    /** Lightweight keyword-based fallback classifier. */
    private function classifyLocally(string $title): string
    {
        $lower = strtolower($title);

        foreach (self::CATEGORY_KEYWORDS as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($lower, $keyword)) {
                    return $category;
                }
            }
        }

        return 'General';
    }

    /**
     * Content-based + collaborative relevance score in [0, 1] for a given
     * user/topic pairing. Placeholder scoring based on shared category
     * interest; replace with a real recommender when available.
     */
    public function relevanceScore(int $matchingCategoryCount, int $totalUserTopics): float
    {
        if ($totalUserTopics === 0) {
            return 0.5;
        }

        return round(min(1, $matchingCategoryCount / $totalUserTopics), 3);
    }
}