<?php

namespace NSWDPC\AssetScan;

use Psr\Log\LoggerInterface;
use SilverStripe\Core\Injector\Injector;

class Logger
{
    public static function log($message, $level = "DEBUG")
    {
        Injector::inst()->get(LoggerInterface::class)->log($level, $message);
    }
}
