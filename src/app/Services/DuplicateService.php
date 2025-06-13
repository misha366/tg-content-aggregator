<?php

namespace App\Services;

use App\Helpers\ProcessHelper;
use App\Models\Phash;
use Illuminate\Support\Facades\Process as LaravelProcess;

class DuplicateService
{
    public function deleteDuplicatesFrom(string $pathToImages, callable $info)
    {
        $detectDuplicatesScriptPath = app_path('PyModules/detect_duplicates.py');

        $pip3InVenvPath = app_path('PyModules/venv/bin/pip3');
        $python3InVenvPath = app_path('PyModules/venv/bin/python3');

        $imagehashPath = app_path('PyModules/venv/lib/python3.11/site-packages/imagehash');
        $PILPath = app_path('PyModules/venv/lib/python3.11/site-packages/PIL');

        if (! is_dir($imagehashPath) || ! is_dir($PILPath)) {
            $info('[-] Dependencies required by detect duplicates script were not found. Downloading... [-]');
            ProcessHelper::executeCommandAndShowOutput(
                $pip3InVenvPath.' install Pillow imagehash',
            );
            $info('[+] Dependencies downloaded successfully [+]');
        } else {
            $info('[+] All dependencies are satisfied. Continuing... [+]');
        }

        $info('[!] Start searching for duplicates... [!]');

        $phashes = Phash::pluck('hash')->toArray();

        $detectProcess = LaravelProcess::timeout(3600)->run(
            $python3InVenvPath.' '.
            $detectDuplicatesScriptPath.' '.
            $pathToImages.' '.
            escapeshellarg(json_encode($phashes)),
        );

        $detectProcessOutput = json_decode($detectProcess->output());
        $duplicates = $detectProcessOutput->duplicates;
        $originalHashes = $detectProcessOutput->originalHashes;

        foreach ($duplicates as $duplicate) {
            unlink($duplicate);
        }
        $info('[+] Found & deleted '.count($duplicates).' duplicates [+] ');

        $info('[!] Adding original hashes to DB... [!]');
        foreach ($originalHashes as $hash) {
            Phash::create(['hash' => $hash]);
        }

        $info('[+] Duplicates successfully found and removed, original hashes added to DB [+]');
    }
}
