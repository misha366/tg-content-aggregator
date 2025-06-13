<?php

namespace App\Helpers;

use Symfony\Component\Process\Process;

class ProcessHelper
{
    public static function executeCommandAndShowOutput(
        string $command,
        ?callable $filter = null,
        int $timeout = 3600
    ) : void {
        $process = Process::fromShellCommandline($command);
        $process->setTimeout($timeout);
        $process->run(function ($type, $buffer) use ($filter) {
            if ($filter !== null && $filter($buffer)) return;
            echo $buffer;
            flush();
        });
    }
}
