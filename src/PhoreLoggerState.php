<?php

namespace Phore\Log;

use Phore\Log\Driver\PhoreLoggerDriver;

final class PhoreLoggerState
{
    /** @var PhoreLoggerDriver[] */
    public array $drivers = [];
    public PhoreLoggerConfig $config;

    public function __construct()
    {
        $this->config = new PhoreLoggerConfig();
    }
}
