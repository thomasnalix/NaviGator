<?php

namespace Navigator\Lib;

class TimerUtils
{
    private static array $timers = [];

    public static function startTimer(string $timerName): void
    {
        self::$timers[$timerName] = [
            "start" => microtime(true),
            "effectiveStart" => microtime(true),
            "elapsed" => 0
        ];
    }

    public static function stopTimer(string $timerName): void
    {
        self::$timers[$timerName]["elapsed"] += microtime(true) - self::$timers[$timerName]["effectiveStart"];
    }

    public static function printAllTimers(): void
    {
        foreach (self::$timers as $timerName => $timerValue) {
            echo $timerName . ": " . round($timerValue["elapsed"], 6) . "s<br>";
        }
    }

    public static function stopAllTimers(): void
    {
        foreach (self::$timers as $timerName => $timerValue) {
            self::stopTimer($timerName);
        }
    }

    public static function startOrRestartTimer(string $timerName): void
    {
        if (isset(self::$timers[$timerName])) {
            self::$timers[$timerName]["effectiveStart"] = microtime(true);
        } else {
            self::startTimer($timerName);
        }
    }

    public static function pauseTimer(string $timerName): void
    {
        self::$timers[$timerName]["elapsed"] += microtime(true) - self::$timers[$timerName]["effectiveStart"];
    }

}