<?php
/**
 * contains Log Stub
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

declare(strict_types=1);

namespace DavidLienhard\Log;

use \DavidLienhard\Log\LogInterface;

/**
 * stub for logging
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */
class Stub implements LogInterface
{
    /** file to save the logs */
    private string $file;

    /** whether to use gz compression */
    private bool $gz;

    /** whether to append data to the file */
    private bool $append;

    /**
     * list of errors
     * @var             array
     */
    private array $errors = [];

    /** stay silent or print the errors to stdout */
    private bool $silent = false;



    /**
     * initializes the class
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $file       the file to save the logfiles to
     * @param           bool            $gz         whether to save data data with gz compression
     * @param           bool            $append     whether to append data to the file
     * @return          void
     */
    public function __construct(string $file, bool $gz = true, bool $append = false)
    {
        $this->file = $file;
        $this->gz = $gz;
        $this->append = $append;
    }

    /**
     * write a new line
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $text   the line to write
     * @param           bool            $nl     create a newline after the text
     * @param           bool            $date   add thge date before the text
     */
    public function write(string $text, bool $nl = true, bool $date = true) : bool
    {
        echo ($date ? date("d.m.y H:i:s")." " : "").$text.($nl ? "\n" : "");

        return true;
    }

    /**
     * close the logfile
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function close() : bool
    {
        return true;
    }

    /**
     * sets the silent flag
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           bool            $silent         state of the silent flag
     */
    public function silent(bool $silent = true) : void
    {
        $this->silent = $silent;
    }

    /**
     * returns all errors occurred during logging
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @return          array
     */
    public function getErrors() : array
    {
        return $this->errors;
    }
}
