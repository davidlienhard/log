<?php declare(strict_types=1);

/**
 * contains Log class
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

namespace DavidLienhard\Log;

use DavidLienhard\Log\LogInterface;

/**
 * class for logging
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */
class Log implements LogInterface
{
    /** file to save the logs */
    private string $file;

    /** whether to use gz compression */
    private bool $gz;

    /** whether to write data with compression or not */
    private bool $writeGz;

    /** whether to append data to the file */
    private bool $append;

    /**
     * filepointer
     * @var             resource
     */
    private $fp;

    /**
     * list of errors
     * @var             array
     */
    private array $errors = [];

    /** stay silent or print the errors to stdout */
    private bool $silent = false;

    /**
     * if the file exists when calling the class
     * will be used to determine if the file can be deleted when closing
     */
    private bool $exists = false;

    /** number of write commands */
    private int $writes = 0;

    /** function name to use to open file */
    private string $openFunction;

    /** function name to use to write to file */
    private string $writeFunction;

    /** function name to use to close file */
    private string $closeFunction;



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
        // check if parameter silent is set in command line
        $this->silent = in_array(
            strtolower("silent"),
            array_map("strtolower", $_SERVER['argv'] ?? []),
            true
        );

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
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $text   the line to write
     * @param           bool            $nl     create a newline after the text
     * @param           bool            $date   add the date before the text
     */
    public function write(string $text, bool $nl = true, bool $date = true) : bool
    {
        if ($this->fp === null) {
            $path = pathinfo($this->file);

            // check if dirname can be fetched
            if (!isset($path['dirname'])) {
                $this->errors[] = "direcory name cannot be fetched'";
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
            if (!is_callable($fn)) {
                $this->errors[] = "function '".$fn."' is not callable";
                return false;
            }

            $fp = $fn($this->file, $mode);

            if ($fp === false) {
                $this->errors[] = "could not open file '".$this->file."' for writing";
                return false;
            }

            $this->fp = $fp;
        }//end if


        $text = ($date ? date("d.m.y H:i:s")." " : "").$text.($nl ? "\n" : "");

        if (!is_resource($this->fp)) {
            $this->errors[] = "\$fp is not a resource";
            return false;
        }

        echo !$this->silent ? $text : "";

        $fn = $this->writeFunction;
        if (!is_callable($fn)) {
            $this->errors[] = "function '".$fn."' is not callable";
            return false;
        }

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
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
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
        if (!is_callable($fn)) {
            $this->errors[] = "function '".$fn."' is not callable";
            return false;
        }

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
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           bool            $silent         state of the silent flag
     */
    public function silent(bool $silent = true) : void
    {
        $this->silent = $silent;
    }

    /**
     * returns all errors occurred duing logging
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @return          array
     */
    public function getErrors() : array
    {
        return $this->errors;
    }

    /**
     * gzips a given file
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    private function gzip() : bool
    {
        $temp = sys_get_temp_dir().DIRECTORY_SEPARATOR.rand(0, 99999).".gz";
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
            gzwrite($fp_out, fread($fp_in, 1024 * 512) ?: "");
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
