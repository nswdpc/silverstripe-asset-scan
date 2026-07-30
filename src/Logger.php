<?php

declare(strict_types=1);

namespace NSWDPC\AssetScan;

use Psr\Log\LoggerInterface;
use SilverStripe\Core\Injector\Injector;

class Logger
{
    public static function log(string|\Stringable $message, $level = "DEBUG")
    {
        Injector::inst()->get(LoggerInterface::class)->log($level, $message);
    }
}
