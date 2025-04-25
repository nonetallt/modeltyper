<?php

namespace FumeApp\ModelTyper\Internal;

class Debugger
{
    private static ?string $lastCommandOutput = null;

    public static function setLastCommandOutput(string $output)
    {
        self::$lastCommandOutput = $output;
    }

    public static function getLastCommandOutput(): ?string
    {
        return self::$lastCommandOutput;
    }
}
