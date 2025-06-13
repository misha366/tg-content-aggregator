<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class ParserService {
    const PROCESS_LIFETIME = 3600; // for low-end computers

    public function parse(string $pinUrl, callable $info) : int {
        $info('[!] URL: ' . $pinUrl);

        $parserGithubUrl = 'https://github.com/sean1832/pinterest-dl';

        $parserPath = app_path('PyModules/pinterest-dl');

        $venvPath = app_path('PyModules/venv');
        $pip3InVenvPath = app_path('PyModules/venv/bin/pip3');

        $parserBuildPath = app_path('PyModules/pinterest-dl/build');
        $builtParserInVenvPath = app_path('PyModules/venv/bin/pinterest-dl');

        $rawImagesPath = storage_path('app/private/raw-images');

        if (!is_dir($parserPath)) {
            $info('[-] Parser not found. Downloading... [-]');
            $this->executeCommandAndShowOutput(
                'git clone ' .
                $parserGithubUrl . ' ' .
                $parserPath .
                ' --progress'
            );
            $info('[+] Parser downloaded successfully [+]');
        } else {
            $info('[+] Parser found. Continuing... [+]');
        }

        if (!is_dir($venvPath)) {
            $info('[-] Virtual environment not found in PyModules. Creating... [-]');
            $this->executeCommandAndShowOutput('python3 -m venv ' . $venvPath);
            $info('[+] Virtual environment in PyModules created successfully [+]');
        } else {
            $info('[+] Virtual environment found in PyModules. Continuing... [+]');
        }

        if (!is_dir($parserBuildPath)) {
            $info('[-] Parser not built. Building... [-]');
            $this->executeCommandAndShowOutput($pip3InVenvPath . ' install ' . $parserPath);
            $info('[+] Parser was built successfully [+]');
        } else {
            $info('[+] Parser is already built. Continuing... [+]');
        }

        $info('[!] Starting pinterest-dl process [!]');

        // $this->executeCommandAndShowOutput(
        //     $builtParserInVenvPath . ' scrape '. $pinUrl . ' -n 1000 -o ' . $rawImagesPath
        // );

        $info('[+] Images were downloaded successfully. Continuing... [+]');

        if (!is_dir($rawImagesPath)) {
            echo '[*] An error occurred while parsing, please try again [*]';
            return 1;
        }

        //

        // File::deleteDirectory($rawImagesPath);

        $info('[+] Parsing completed successfully, images saved to storage. [+]');

        return 0;
    }

    private function executeCommandAndShowOutput(string $command) : void {
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(self::PROCESS_LIFETIME);
        $process->run(function ($type, $buffer) {
            if (str_contains($buffer, 'cannot identify image file')) return; // skip heic files
            if (str_contains($buffer, 'No data found in response')) return; // end scarping

            echo $buffer;
            flush();
        });
    }
}
