<?php

declare(strict_types=1);

namespace DavidLienhard;

use DavidLienhard\Log\Log;
use DavidLienhard\Log\LogInterface;
use PHPUnit\Framework\TestCase;

define("TEMP_DIR", sys_get_temp_dir().DIRECTORY_SEPARATOR);

class ScriptLogTest extends TestCase
{
    /**
     * @covers \DavidLienhard\Log\Log
    */
    public function testCannotBeCreatedWithoutAttributes(): void
    {
        $this->expectException(\ArgumentCountError::class);

        $log = new Log;
    }


    /**
     * @covers \DavidLienhard\Log\Log
    */
    public function testCanBeCreated() : void
    {
        $log = new Log(TEMP_DIR."testfile_".rand(0, 10000).".log.gz");

        $this->assertInstanceOf(Log::class, $log);

        $this->assertInstanceOf(LogInterface::class, $log);
    }


    /**
     * @covers \DavidLienhard\Log\Log
    */
    public function testFileIsCreated() : void
    {
        $file = TEMP_DIR."testfile_".rand(0, 10000).".log";

        $log = new Log($file);

        $this->assertInstanceOf(Log::class, $log);

        $log->silent(true);
        $log->write("test");

        $this->assertTrue(file_exists($file));
    }



    /**
     * @covers \DavidLienhard\Log\Log::write()
    */
    public function testStringCanBeWritten() : void
    {
        $log = new Log(TEMP_DIR."testfile_".rand(0, 10000).".log.gz");

        $this->assertInstanceOf(Log::class, $log);

        $log->silent(true);
        $this->assertTrue($log->write("test"));
    }



    /**
     * @covers \DavidLienhard\Log\Log::close()
    */
    public function testCanCloseFile() : void
    {
        $log = new Log(TEMP_DIR."testfile_".rand(0, 10000).".log.gz");

        $this->assertInstanceOf(Log::class, $log);

        $this->assertTrue($log->close());
    }



    /**
     * @covers \DavidLienhard\Log\Log::close()
    */
    public function testCloseDeletesFileWithZeroWrites() : void
    {
        $file = TEMP_DIR."testfile_".rand(0, 10000).".log.gz";

        $log = new Log($file);

        $this->assertInstanceOf(Log::class, $log);

        $this->assertTrue($log->close());
        $this->assertFalse(file_exists($file));
    }


    /**
     * @covers \DavidLienhard\Log\Log::silent()
    */
    public function testCanSetSilentToTrue() : void
    {
        $log = new Log(TEMP_DIR."testfile_".rand(0, 10000).".log.gz");
        $log->silent(true);
        $this->assertTrue(true);
    }


    /**
     * @covers \DavidLienhard\Log\Log::silent()
    */
    public function testCanSetSilentToFalse() : void
    {
        $log = new Log(TEMP_DIR."testfile_".rand(0, 10000).".log.gz");
        $log->silent(false);
        $this->assertTrue(true);
    }
}
