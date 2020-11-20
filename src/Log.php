<?php
/**
 * contains Log class
 *
 * @package         tourBase
 * @author          David Lienhard <david.lienhard@tourasia.ch>
 * @version         1.0.0, 20.11.2020
 * @since           1.0.0, 20.11.2020, created
 * @copyright       tourasia
 */

declare(strict_types=1);

namespace DavidLienhard\Log;

use \DavidLienhard\Log\LogInterface;

/**
 * class for logging
 *
 * @author          David Lienhard <david.lienhard@tourasia.ch>
 * @version         1.0.0, 20.11.2020
 * @since           1.0.0, 20.11.2020, created
 * @copyright       tourasia
 */
class Log implements LogInterface
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
     * filepointer
     * @var             resource
     */
    private $fp;

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
     * if the file exists when calling the class
     * will be used to determine if the file can be deleted when closing
     * @var            bool
     */
    private $exists = false;

    /**
     * number of write commands
     * @var            int
     */
    private $writes = 0;

    /**
     * function name to use to open file
     */
    private $openFunction;

    /**
     * function name to use to write to file
     */
    private $writeFunction;

    /**
     * function name to use to close file
     */
    private $closeFunction;



    /**
     * initializes the class
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @version         1.0.0, 20.11.2020
     * @since           1.0.0, 20.11.2020, created
     * @copyright       tourasia
     * @param           string          $file       the file to save the logfiles to
     * @param           bool            $gz         whether to save data data with gz compression
     * @param           bool            $append     whether to append data to the file
     * @return          void
     * @uses            self::$silent
     * @uses            self::$file
     * @uses            self::$gz
     * @uses            self::$append
     * @uses            self::$exists
     */
    public function __construct(string $file, bool $gz = true, bool $append = false)
    {
        // check if parameter silent is set in command line
        $this->silent = in_array(strtolower("silent"), array_map("strtolower", $_SERVER['argv'] ?? [ ]));

        $this->file = $file;
        $this->gz = $gz;
        $this->append = $append;
        $this->exists = file_exists($file);

        // only write gzip data if gz is enabled and data will be appended
        $this->writeGz = $this->gz && $this->append && $this->exists;
        $this->openFunction = $this->writeGz ? "gzopen" : "fopen";
        $this->writeFunction = $this->writeGz ? "gzwrite" : "fwrite";
        $this->closeFunction = $this->writeGz ? "gzclose" : "fclose";

        if (!$this->writeGz && strtolower(substr($this->file, -3, 3)) === ".gz") {
            $this->file = substr($this->file, 0, -3);
        }
    }

    /**
     * write a new line
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @version         1.0.0, 20.11.2020
     * @since           1.0.0, 20.11.2020, created
     * @copyright       tourasia
     * @param           string          $text   the line to write
     * @param           bool            $nl     create a newline after the text
     * @param           bool            $date   add thge date before the text
     * @return          bool
     * @uses            self::$fp
     * @uses            self::$file
     * @uses            self::$errors
     * @uses            self::$silent
     * @uses            self::$writes
     */
    public function write(string $text, bool $nl = true, bool $date = true) : bool
    {
        if ($this->fp === null) {
            $path = pathinfo($this->file);
            if ($path === false) {
                $this->errors[] = "could not get pathinfo for '".$this->file."'";
                return false;
            }

            // create log folder if necessary
            if (!is_dir($path['dirname']) && mkdir($path['dirname'], 0755, true) === false) {
                $this->errors[] = "could not create folder '".$path['dirname']."'";
                return false;
            }


            $mode = $this->append ? "a" : "w";
            $mode .= $this->writeGz ? "9" : "";

            $fn = $this->openFunction;
            $fp = $fn($this->file, $mode);

            if ($fp === false) {
                $this->errors[] = "could not open file '".$this->file."' for writing";
                return false;
            }

            $this->fp = $fp;
        }


        $text = ($date ? date("d.m.y H:i:s")." " : "") . $text . ($nl ? "\n" : "");

        if (!is_resource($this->fp)) {
            $this->errors[] = "\$fp is not a resource";
            return false;
        }

        echo !$this->silent ? $text : "";

        $fn = $this->writeFunction;
        if ($fn($this->fp, $text) === false) {
            $this->errors[] = "could not write text '".$text."'";
            return false;
        }

        $this->writes++;

        return true;
    }

    /**
     * close the logfile
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @version         1.0.0, 20.11.2020
     * @since           1.0.0, 20.11.2020, created
     * @copyright       tourasia
     * @return          bool
     * @uses            self::$fp
     * @uses            self::$errors
     * @uses            self::$writes
     */
    public function close() : bool
    {
        if ($this->fp === null) {
            return true;
        }

        if (!is_resource($this->fp)) {
            $this->errors[] = "\$fp is not a resource";
            return false;
        }

        $fn = $this->closeFunction;
        if ($fn($this->fp) === false) {
            $this->errors[] = "could not close file";
            return false;
        }

        // delete file if nothing was written and
        // file did not exists at the beginning
        if ($this->writes === 0 && !$this->exists && file_exists($this->file)) {
            unlink($this->file);
        } elseif ($this->gz && !$this->writeGz) {
            $this->gzip();
        }

        return true;
    }

    /**
     * sets the silent flag
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @version         1.0.0, 20.11.2020
     * @since           1.0.0, 20.11.2020, created
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
     * returns all errors occurred duing logging
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @version         1.0.0, 20.11.2020
     * @since           1.0.0, 20.11.2020, created
     * @copyright       tourasia
     * @return          array
     * @uses            self::$errors
     */
    public function getErrors() : array
    {
        return $this->errors;
    }

    /**
     * gzips a given file
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @version         1.0.0, 20.11.2020
     * @since           1.0.0, 20.11.2020, created
     * @copyright       tourasia
     * @return          bool
     */
    private function gzip() : bool
    {
        $temp = sys_get_temp_dir() . rand(0, 99999) . ".gz";
        $source = $this->file;
        $destination = strtolower(substr($source, -3, 3)) === ".gz" ? $source : $source.".gz";
        $mode = "wb9";

        $fp_out = gzopen($temp, $mode);

        if ($fp_out === false) {
            return false;
        }

        $fp_in = fopen($source, "rb");

        if ($fp_in === false) {
            return false;
        }

        while (!feof($fp_in)) {
            gzwrite($fp_out, fread($fp_in, 1024 * 512));
        }

        fclose($fp_in);
        gzclose($fp_out);

        if (!unlink($source)) {
            return false;
        }

        if (!rename($temp, $destination)) {
            return false;
        }

        return true;
    }
}
