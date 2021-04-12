<?php
/**
 * contains Log interface
 *
 * @package         tourBase
 * @author          David Lienhard <david.lienhard@tourasia.ch>
 * @copyright       tourasia
 */

declare(strict_types=1);

namespace DavidLienhard\Log;

/**
 * interface for logging
 *
 * @author          David Lienhard <david.lienhard@tourasia.ch>
 * @copyright       tourasia
 */
interface LogInterface
{
    /**
     * initializes the class
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @copyright       tourasia
     * @param           string          $file       the file to save the logfiles to
     * @param           bool            $gz         whether to save data data with gz compression
     * @param           bool            $append     whether to append data to the file
     * @return          void
     */
    public function __construct(string $file, bool $gz = true, bool $append = false);

    /**
     * write a new line
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @copyright       tourasia
     * @param           string          $text   the line to write
     * @param           bool            $nl     create a newline after the text
     * @param           bool            $date   add the date before the text
     */
    public function write(string $text, bool $nl = true, bool $date = true) : bool;

    /**
     * close the logfile
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @copyright       tourasia
     */
    public function close() : bool;

    /**
     * sets the silent flag
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @copyright       tourasia
     * @param           bool            $silent         state of the silent flag
     */
    public function silent(bool $silent = true) : void;

    /**
     * returns all errors occurred duing logging
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @copyright       tourasia
     * @return          array
     */
    public function getErrors() : array;
}
