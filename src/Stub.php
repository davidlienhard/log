<?php
/**
 * contains Log Stub
 *
 * @package         tourBase
 * @author          David Lienhard <david.lienhard@tourasia.ch>
 * @version         1.0.1, 30.11.2020
 * @since           1.0.1, 30.11.2020, created
 * @copyright       tourasia
 */

declare(strict_types=1);

namespace DavidLienhard\Log;

use \DavidLienhard\Log\LogInterface;

/**
 * stub for logging
 *
 * @author          David Lienhard <david.lienhard@tourasia.ch>
 * @version         1.0.1, 30.11.2020
 * @since           1.0.1, 30.11.2020, created
 * @copyright       tourasia
 */
class Stub implements LogInterface
{
    /**
     * file to save the logs
     * @var             string
     */
    private $file;

    /**
     * whether to use gz compression
     * @var             bool
     */
    private $gz;

    /**
     * whether to append data to the file
     * @var             bool
     */
    private $append;

    /**
     * list of errors
     * @var             array
     */
    private $errors = [ ];

    /**
     * stay silent or print the errors to stdout
     * @var             bool
     */
    private $silent = false;



    /**
     * initializes the class
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @version         1.0.1, 30.11.2020
     * @since           1.0.1, 30.11.2020, created
     * @copyright       tourasia
     * @param           string          $file       the file to save the logfiles to
     * @param           bool            $gz         whether to save data data with gz compression
     * @param           bool            $append     whether to append data to the file
     * @return          void
     * @uses            self::$file
     * @uses            self::$gz
     * @uses            self::$append
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
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @version         1.0.1, 30.11.2020
     * @since           1.0.1, 30.11.2020, created
     * @copyright       tourasia
     * @param           string          $text   the line to write
     * @param           bool            $nl     create a newline after the text
     * @param           bool            $date   add thge date before the text
     * @return          bool
     */
    public function write(string $text, bool $nl = true, bool $date = true) : bool
    {
        echo ($date ? date("d.m.y H:i:s")." " : "") . $text . ($nl ? "\n" : "");

        return true;
    }

    /**
     * close the logfile
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @version         1.0.1, 30.11.2020
     * @since           1.0.1, 30.11.2020, created
     * @copyright       tourasia
     * @return          bool
     */
    public function close() : bool
    {
        return true;
    }

    /**
     * sets the silent flag
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @version         1.0.1, 30.11.2020
     * @since           1.0.1, 30.11.2020, created
     * @copyright       tourasia
     * @param           bool            $silent         state of the silent flag
     * @return          void
     * @uses            self::$silent
     */
    public function silent(bool $silent = true) : void
    {
        $this->silent = $silent;
    }

    /**
     * returns all errors occurred during logging
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @version         1.0.1, 30.11.2020
     * @since           1.0.1, 30.11.2020, created
     * @copyright       tourasia
     * @return          array
     * @uses            self::$errors
     */
    public function getErrors() : array
    {
        return $this->errors;
    }
}
