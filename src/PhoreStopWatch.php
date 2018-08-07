<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 07.08.18
 * Time: 08:34
 */

namespace Phore\Log;


class PhoreStopWatch
{


    private static $scriptStartTime;

    public static function __Init()
    {
        self::$scriptStartTime = microtime(true);
    }

    public static function GetScriptRunTime() : float
    {
        return microtime(true) - self::$scriptStartTime;
    }


}
