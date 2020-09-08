<?php


namespace Phore\Log\Format;


class PhoreLogFormatFactory
{

    public static function Build(string $name="default") : PhoreLogFormat
    {
        switch ($name) {
            case "default":
                return new PhoreDefaultLogFormat();
            default:
                throw new \InvalidArgumentException("Unknown log format '$name'");
        }
    }
}
