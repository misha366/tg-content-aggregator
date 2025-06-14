<?php

namespace App\Jobs;

use App\Services\StorageService;
use danog\MadelineProto\API;
use danog\MadelineProto\Exception;
use danog\MadelineProto\LocalFile;
use danog\MadelineProto\ParseMode;
use danog\MadelineProto\Settings;
use danog\MadelineProto\Logger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use danog\MadelineProto\Settings\Logger as LoggerSettings;
use danog\MadelineProto\Settings\Peer as PeerSettings;

class PostJob implements ShouldQueue
{
    use Queueable;

    private string $peer;

    /**
     * Create a new job instance.
     */
    public function __construct(string $peer)
    {
        $this->peer = $peer;
    }

    /**
     * Execute the job.
     */
    public function handle(StorageService $storageService): void
    {
        $randomImage = $storageService->retrieveRandomImage();

        $sessionPath = storage_path('app/private/madeline/madeline.session');
        if (!File::exists($sessionPath)) {
            throw new Exception('Missing madeline session. Please run artisan `create-madeline-session` and'.
                'try calling `post` command again.');
        }

        $loggerPath = storage_path('app/private/madeline/madeline.log');

        $settings = new Settings;

        $settings->setLogger((new LoggerSettings)
            ->setType(Logger::FILE_LOGGER)
            ->setExtra($loggerPath)
            ->setMaxSize(50 * 1024 * 1024)
        );

        $settings->setPeer((new PeerSettings)->setCacheAllPeersOnStartup(true));

        $madeline = new API($sessionPath, $settings);
        $madeline->start();

        $messageDefaultText
            = ' click <a href="https://github.com/misha366/tg-content-aggregator">here</a> to see the source code';

        $stickerSet = $madeline->messages->getStickerSet([
            'stickerset' => [
                '_' => 'inputStickerSetID',
                'id' => '3118192303240380433',
                'access_hash' => '8700508660480848372'
            ],
        ]);

        // random premium emoji
        $firstEmojiIndex = random_int(0, count($stickerSet['documents']) - 1);
        $firstEmojiId = $stickerSet['documents'][$firstEmojiIndex]['id'];
        $firstEmojiAlt = $stickerSet['documents'][$firstEmojiIndex]['attributes'][1]['alt'];
        $firstEmojiAltLen = 2;
        $firstEmojiOffset = 0;

        $message = $firstEmojiAlt . $messageDefaultText;

        $entities = [
            [
                '_' => 'messageEntityCustomEmoji',
                'offset' => $firstEmojiOffset,
                'length' => $firstEmojiAltLen,
                'document_id' => $firstEmojiId
            ],
        ];

        $madeline->messages->sendMedia(
            peer: $this->peer,
            media: [
                '_' => 'inputMediaUploadedPhoto',
                'file' => new LocalFile($randomImage)
            ],
            message: $message,
            entities: $entities,
            parse_mode: ParseMode::HTML
        );

        $storageService->deleteImage($randomImage);
    }
}
