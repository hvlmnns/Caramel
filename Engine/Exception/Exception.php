<?php

namespace Temple\Engine\Exception;


use Temple\Engine\Cache\ClassCache;
use Temple\Engine\Config;
use Temple\Engine\Filesystem\DirectoryHandler;


/**
 * Class Exception
 *
 * @package Temple
 */
class Exception extends \Exception
{

    /** @var Config $Config */
    private $Config;

    /** @var bool|string $customMessage */
    private $customMessage;

    /** @var bool|string $customFile */
    private $customFile;

    /** @var integer|string $customLine */
    private $customLine;

    /** @var integer|string $customCode */
    private $customCode;


    /**
     * Exception constructor.
     *
     * @param int             $code
     * @param string          $message
     * @param bool            $file
     * @param bool            $line
     * @param \Exception|null $previous
     */
    public function __construct($code, $message = "", $file = false, $line = false, \Exception $previous = null)
    {

        # if we'v passed an exception, translate its values to the new one
        if ($message instanceof \Exception) {
            /** @var \Exception $exception */
            $exception = $message;
            $message   = $exception->getMessage();
            $code      = $exception->getCode();
            $previous  = $exception->getPrevious();
        }


        $this->customCode = $code;

        if ($file) {
            $this->customFile = $file;
            $this->touchTemplateFiles($file);
        }

        if ($line) {
            $this->customLine = $line;
        }

        if ($message) {
            $this->customMessage = $message;
            $message             = str_replace("%", "", $message);
        } else {
            $message = "No Message given!";
        }

        # execute the default exception after that
        parent::__construct($message, $code, $previous);

    }


    /**
     * returns the Custom Exception Code
     *
     * @return bool|string
     */
    public function getCustomCode()
    {
        return $this->customCode;
    }


    /**
     * returns the Custom file
     *
     * @return bool|string
     */
    public function getCustomFile()
    {
        return $this->customFile;
    }


    /**
     * returns the Custom line
     *
     * @return bool|int|string
     */
    public function getCustomLine()
    {
        return $this->customLine;
    }


    /**
     * returns the Custom message
     *
     * @return bool|int|string
     */
    public function getCustomMessage()
    {
        return $this->customMessage;
    }


    /**
     * splits file into name and path
     *
     * @param $file
     * @param $root
     *
     * @return array $file
     */
    private function splitFile($file, $root)
    {
        $tempFile     = $file;
        $file         = array();
        $temp         = explode("/", $tempFile);
        $file["name"] = array_pop($temp);
        $file["path"] = str_replace($root . "/", "", implode("/", $temp) . "/");

        return $file;
    }


    /**
     * displays an exception file
     *
     * @param $file
     * @param $root
     * @param $line
     * @param $function
     */
    function displayCustomErrorFile($root, $file, $line = false, $function = false)
    {

        $file = $this->splitFile($file, $root);
        echo $file["path"] . "<b>" . $file["name"] . "</b>";

        if ($line) {
            echo " in line " . "<b>" . $line . "</b>";
        }
        if ($function) {
            echo " in function " . "<b>" . $function . "</b>";
        }
    }


    /**
     * updates the file modified time to keep the exception alive
     *
     * @param string $file
     *
     * @return bool
     */
    private function touchTemplateFiles($file)
    {
//        $DirectoryHandler = new DirectoryHandler($this->Config);
//        $ClassCache       = new ClassCache($DirectoryHandler);
//        $configs          = $ClassCache->getCache();
//        foreach ($configs as $config) {
//            if (isset($config["templateDirs"])) {
//                $templateDirs = $config["templateDirs"];
//                foreach ($templateDirs as $templateDir) {
//                    $file = str_replace($templateDir, "", $file);
//                }
//                if (isset($config["extension"])) {
//                    $extension = "." . $config["extension"];
//                    $file      = preg_replace("/" . preg_quote($extension) . "$/", "", $file) . $extension;
//                    foreach ($templateDirs as $templateDir) {
//                        $template = $templateDir . $file;
//                        if (file_exists($templateDir . $file)) {
//                            touch($template);
//
//                            return true;
//                        }
//                    }
//                }
//            }
//        }

        return false;
    }


}