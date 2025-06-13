<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use InvalidArgumentException;

class PostingService {
    public function generatePostJobs(
        string $from,
        string $to,
        string $peer,
        int $perDay,
        callable $info
    ) : int {
        $now = now();
        $startTime = Carbon::parse($from);
        $endTime = Carbon::parse($to);

        if ($startTime <= $now) {
            $startTime->addDay();
            $endTime->addDay();
        }

        if ($endTime <= $startTime) {
            $endTime->addDay();
        }

        $minutesDiff = $endTime->diffInMinutes($startTime, true);

        $info('[!] Start: '       .$startTime->toDateTimeString());
        $info('[!] End: '         .$endTime->toDateTimeString());
        $info('[!] Now: '         .now()->toDateTimeString());
        $info('[!] Minutes diff: '.$minutesDiff);

        $delays = $this->generateUniqueMinutesDelays($perDay, $minutesDiff);

        foreach ($delays as $delay) {
            // start job with delay
        }

        return 0;
    }

    private function generateUniqueMinutesDelays(int $amountOfPosts, int $minutesDiff) : array {
        if ($amountOfPosts > $minutesDiff)
            throw new InvalidArgumentException('Cannot generate'.$amountOfPosts.
                'unique values in range'.$minutesDiff);

        $available = range(0, $minutesDiff - 1);
        shuffle($available);
        return array_slice($available, 0, $amountOfPosts);
    }
}
