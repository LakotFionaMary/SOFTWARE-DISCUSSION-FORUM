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

    // NOTE: keywords are matched with str_contains() against a lowercased
    // title, so use STEMS (not exact words) to catch plurals/inflections.
    // e.g. 'quer' matches "query", "queries", "querying" — using the exact
    // word 'query' would silently miss "queries" (a real bug found via a
    // title like "play with queries and normalization" never matching
    // Databases and falling through to General/Programming instead).
    private const CATEGORY_KEYWORDS = [
        'Networking' => ['network', 'tcp', 'ip', 'router', 'protocol'],
        'Databases' => ['database', 'sql', 'quer', 'schema', 'index', 'mongodb', 'nosql'],
        'Programming' => ['code', 'function', 'bug', 'compile', 'algorithm'],
        'Machine Learning' => ['model', 'training', 'classifier', 'dataset', 'neural'],
        'Software Design' => ['architecture', 'design', 'uml', 'pattern', 'module'],
    ];

    /**
     * Maps the raw snake_case category slugs the ML model was trained on
     * (see ml_service/merge_datasets.py BUCKETS) to clean display names
     * used throughout the app.
     */
    private const ML_LABEL_MAP = [
        'oop_concepts' => 'OOP Concepts',
        'data_structures_algorithms' => 'Data Structures & Algorithms',
        'databases' => 'Databases',
        'web_development' => 'Web Development',
        'software_engineering_process' => 'Software Engineering',
        'systems_hardware_os' => 'Systems & OS',
        'networking' => 'Networking',
        'security' => 'Security',
        'devops_cloud' => 'DevOps & Cloud',
        'ai_ml' => 'Machine Learning',
        'distributed_systems' => 'Distributed Systems',
        'theoretical_cs_math' => 'Theoretical CS & Math',
        'emerging_tech' => 'Emerging Tech',
        'programming_languages' => 'Programming Languages',
        'general_cs' => 'General',
    ];

    public function classify(string $title): string
    {
        try {
            $response = Http::timeout(3)
                ->withHeaders([
                    'X-API-KEY' => config('services.ml.api_key'),
                ])
                ->post(self::ML_SERVICE_URL . '/classify', [
                    'text' => $title,
                ]);

            if ($response->successful() && $response->json('category')) {
                $rawCategory = $response->json('category');

                if (!isset(self::ML_LABEL_MAP[$rawCategory])) {
                    Log::warning('ML service returned an unmapped category label', [
                        'raw_category' => $rawCategory,
                    ]);
                }

                return self::ML_LABEL_MAP[$rawCategory] ?? 'General';
            }

            // Request went through but wasn't a success — don't fail silently.
            Log::warning('ML service returned non-success response, falling back to keyword classifier', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
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
     *
     * NOTE: this method is currently unused by the live recommendation
     * flow — RecommendationService::generateForUser() calls the Flask
     * /recommend endpoint directly instead. Kept here as a fallback stub;
     * wire it up if the ML service's /recommend endpoint is ever removed.
     */
    public function relevanceScore(int $matchingCategoryCount, int $totalUserTopics): float
    {
        if ($totalUserTopics === 0) {
            return 0.5;
        }

        return round(min(1, $matchingCategoryCount / $totalUserTopics), 3);
    }
}
