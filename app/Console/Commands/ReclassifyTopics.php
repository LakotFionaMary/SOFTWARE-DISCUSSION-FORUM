<?php

namespace App\Console\Commands;

use App\Models\Topic;
use App\Services\TopicClassifierService;
use Illuminate\Console\Command;

class ReclassifyTopics extends Command
{
    /**
     * php artisan topics:reclassify         → reclassify every topic
     * php artisan topics:reclassify --dry-run → show what would change, write nothing
     */
    protected $signature = 'topics:reclassify {--dry-run : Show changes without saving}';

    protected $description = 'Re-run every topic title through the classifier and update stale categories (post-bugfix cleanup).';

    public function handle(TopicClassifierService $classifier): int
    {
        $dryRun = $this->option('dry-run');
        $topics = Topic::all(['topic_id', 'title', 'category']);

        $this->info("Reclassifying {$topics->count()} topics" . ($dryRun ? ' (dry run — no changes will be saved)' : '') . '...');

        $changed = 0;
        $unchanged = 0;
        $bar = $this->output->createProgressBar($topics->count());
        $bar->start();

        foreach ($topics as $topic) {
            $newCategory = $classifier->classify($topic->title);
            $oldCategory = $topic->category;

            if ($newCategory !== $oldCategory) {
                $this->newLine();
                $this->line("  #{$topic->topic_id} \"{$topic->title}\": {$oldCategory} → {$newCategory}");

                if (! $dryRun) {
                    $topic->update(['category' => $newCategory]);
                }
                $changed++;
            } else {
                $unchanged++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done. Changed: {$changed}, unchanged: {$unchanged}.");

        if ($dryRun && $changed > 0) {
            $this->comment('This was a dry run — re-run without --dry-run to save these changes.');
        }

        if (! $dryRun && $changed > 0) {
            $this->comment('Categories updated. Recommendations were computed from the old categories — run "php artisan recommendations:generate" next to refresh match scores.');
        }

        return self::SUCCESS;
    }
}
