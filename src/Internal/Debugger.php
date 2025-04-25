<?php

namespace FumeApp\ModelTyper\Internal;

class Debugger
{
    private static string|null $lastCommandOutput = null;

    public static function setLastCommandOutput(string $output)
    {
        self::$lastCommandOutput = $output;
    }

    public static function getLastCommandOutput() : string|null
    {
        return self::$lastCommandOutput;
    }
}
