<?php

namespace App\Console\Commands;

use danog\MadelineProto\API;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CreateMadelineSession extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create-madeline-session';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a madeline session in telegram';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $sessionPath = storage_path('app/private/madeline/madeline.session');
        if (File::exists($sessionPath)) {
            $this->error('[*] Madeline session already exists. [*]');
            return 1;
        }

        File::makeDirectory(storage_path('app/private/madeline/'));

        $this->info("[!] Creating MadelineProto session [!]");

        $MadelineProto = new API($sessionPath);
        $MadelineProto->start();

        $this->info("[+] Session created successfully [+]");

        return 0;
    }
}
