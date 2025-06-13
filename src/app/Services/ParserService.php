<?php

namespace App\Services;

use App\Helpers\ProcessHelper;
use Illuminate\Support\Facades\File;

class ParserService
{
    public function __construct(
        private DuplicateService $duplicateService,
        private StorageService $storageService,
    ) {}

    public function parse(string $pinUrl, callable $info): int
    {
        $info('[!] URL: '.$pinUrl);

        $parserGithubUrl = 'https://github.com/sean1832/pinterest-dl';

        $parserPath = app_path('PyModules/pinterest-dl');

        $venvPath = app_path('PyModules/venv');
        $pip3InVenvPath = app_path('PyModules/venv/bin/pip3');

        $parserBuildPath = app_path('PyModules/pinterest-dl/build');
        $builtParserInVenvPath = app_path('PyModules/venv/bin/pinterest-dl');

        $rawImagesPath = storage_path('app/private/raw-images');

        if (! is_dir($parserPath)) {
            $info('[-] Parser not found. Downloading... [-]');
            ProcessHelper::executeCommandAndShowOutput(
                'git clone '.
                $parserGithubUrl.' '.
                $parserPath.
                ' --progress'
            );
            $info('[+] Parser downloaded successfully [+]');
        } else {
            $info('[+] Parser found. Continuing... [+]');
        }

        if (! is_dir($venvPath)) {
            $info('[-] Virtual environment not found in PyModules. Creating... [-]');
            ProcessHelper::executeCommandAndShowOutput('python3 -m venv '.$venvPath);
            $info('[+] Virtual environment in PyModules created successfully [+]');
        } else {
            $info('[+] Virtual environment found in PyModules. Continuing... [+]');
        }

        if (! is_dir($parserBuildPath)) {
            $info('[-] Parser not built. Building... [-]');
            ProcessHelper::executeCommandAndShowOutput($pip3InVenvPath.' install '.$parserPath);
            $info('[+] Parser was built successfully [+]');
        } else {
            $info('[+] Parser is already built. Continuing... [+]');
        }

        $info('[!] Starting pinterest-dl process [!]');

        ProcessHelper::executeCommandAndShowOutput(
            $builtParserInVenvPath.' scrape '.$pinUrl.' -n 1000 -o '.$rawImagesPath,

            fn ($buffer) => str_contains($buffer, 'cannot identify image file') || // skip heic files
            str_contains($buffer, 'No data found in response') // don't show warn when images are out
        );

        if (! is_dir($rawImagesPath)) {
            echo '[*] An error occurred while parsing, please try again [*]';

            return 1;
        }

        $info('[+] Images were downloaded successfully. Continuing... [+]');

        $this->duplicateService->deleteDuplicatesFrom($rawImagesPath, $info);
        $this->storageService->saveParsedImagesToStorage($rawImagesPath, $info);

        File::deleteDirectory($rawImagesPath);

        $info('[+] Parsing completed successfully. [+]');

        return 0;
    }
}
