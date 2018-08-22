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

    
    
    private $startTime;
    
    public function __construct()
    {
        $this->startTime = microtime(true);
    }
    
    public function getTime()
    {
        return (microtime(true) - $this->startTime);
    }
    
    public function printTime(string $msg="")
    {
        return $msg . number_format($this->getTime(), "3", ".", "") . "[sec]";
    }
    
    public function reset() 
    {
        $this->startTime = microtime(true);
    }


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
