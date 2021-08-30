<?php


namespace Linuzilla\Database\Helpers;


use Exception;
use Ncucc\Dormnet\Tasks\TaskRunner;

class LoggingHelper {
    private static TaskRunner $runner;

    private static function backgroundRunner(): bool {
        if (!isset(self::$runner)) {
            self::$runner = new TaskRunner();
        }
        return self::$runner->isBackgroundRunner();
    }

    public static function logException(string $file, int $line, Exception $e) {
        if (self::backgroundRunner()) {
            printf("%s:%d - %s\n", $file, $line, $e->getMessage());
        } else {
            error_log(sprintf("%s:%d - %s", $file, $line, $e->getMessage()));
        }
    }

    public static function error_log(string $file, int $line, string $message) {
        error_log(sprintf("%s:%d - %s", $file, $line, $message));
    }
}