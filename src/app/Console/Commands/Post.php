<?php

namespace App\Console\Commands;

use App\Services\PostingService;
use Illuminate\Console\Command;

class Post extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'post {from} {to} {peer} {--per-day=20}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts autoposting';

    /**
     * Execute the console command.
     */
    public function handle(PostingService $postingService)
    {
        return $postingService->generatePostJobs(
            $this->argument('from'),
            $this->argument('to'),
            $this->argument('peer'),
            $this->option('per-day'),

            [$this, 'info']
        );
    }
}
