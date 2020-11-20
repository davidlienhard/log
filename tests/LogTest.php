<?php

declare(strict_types=1);

namespace DavidLienhard;

use \PHPUnit\Framework\TestCase;
use \DavidLienhard\Log\Log;
use \DavidLienhard\Log\LogInterface;

class LogTest extends TestCase
{
    /**
     * @covers \DavidLienhard\Log\Log
    */
    public function testCanBeCreated(): void
    {
        $log = new Log("test.log");

        $this->assertInstanceOf(
            Log::class,
            $log
        );

        $this->assertInstanceOf(
            LogInterface::class,
            $log
        );
    }
}
