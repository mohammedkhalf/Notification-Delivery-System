<?php

namespace App\Console\Commands;

use App\Application\Services\RetryService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ProcessNotificationRetries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:retry {--limit=50 : Number of notifications to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process failed notifications and schedule retries with exponential backoff';

    /**
     * Execute the console command.
     */
    public function handle(RetryService $retryService): int
    {
        $limit = (int) $this->option('limit');

        $this->info("Processing notification retries (limit: {$limit})...");

        try {
            $retryService->retryFailedNotifications($limit);
            $this->info("Retry processing completed successfully.");
            return CommandAlias::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error processing retries: " . $e->getMessage());
            return CommandAlias::FAILURE;
        }
    }
}
