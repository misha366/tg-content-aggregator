<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class StorageService
{
    public function saveParsedImagesToStorage(string $pathToRawImages, callable $info): void
    {
        $info('[!] Saving images to storage [!]');

        $currentDirectoryInImageStorage = storage_path('app/private/images/'.
            now()->format('Y-m-d_H-i-s'));

        File::makeDirectory($currentDirectoryInImageStorage, 0775, true);

        foreach (File::files($pathToRawImages) as $file) {
            $dest = $currentDirectoryInImageStorage.DIRECTORY_SEPARATOR.$file->getFilename();
            File::copy($file->getPathname(), $dest);
        }

        $info('[+] Images successfully saved to storage [+]');
    }

    public function retrieveRandomImage() {}
}
