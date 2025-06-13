<?php

namespace App\Console\Commands;

use App\Services\ParserService;
use Illuminate\Console\Command;

class Parse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse {url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse image & recommendations to local storage.';

    /**
     * Execute the console command.
     */
    public function handle(ParserService $parserService)
    {
        return $parserService->parse(
            $this->argument('url'),
            [$this, 'info']
        );
    }
}
