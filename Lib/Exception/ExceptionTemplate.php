<?php

namespace Underware\Exception;


/**
 * Class UnderwareTemplate
 *
 * @package Underware\Exception
 */
class ExceptionTemplate
{

    /** @var Exception $exception */
    private $exception;

    /** @var string $template */
    private $template;


    /**
     * ExceptionTemplate constructor.
     
     * 
*@param Exception $exception
     */
    public function __construct(Exception $exception)
    {
        $this->exception = $exception;
        $this->template  = $this->getTemplate();
        $this->displayMessage();
        $this->displayFile();
        $this->displayStackTrace();
        die($this->template);
    }


    /**
     * reads the content of the html template
     *
     * @return string
     */
    private function getTemplate()
    {
        return file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "ExceptionTemplate.html");
    }


    /**
     * displays the message
     */
    private function displayMessage()
    {
        $message        = preg_replace('/\'(.+?)\'/', "<span class='colored'>$1</span>", $this->exception->getMessage());
        $this->template = str_replace("%message%", $message, $this->template);
    }


    /**
     * displays the file
     */
    private function displayFile()
    {
        $file = $this->colorFilePath($this->exception->getUnderwareFile());
        if ($this->exception->getUnderwareLine()) {
            $file .= " on line <span class='colored'>" . $this->exception->getUnderwareLine() . "</span>";
        }
        $this->template = str_replace("%file%", $file, $this->template);
    }


    /**
     * displays the stack trace
     */
    private function displayStackTrace()
    {
        $output = "";
        $traces = $this->exception->getTrace();
        foreach ($traces as $trace) {
            $output .= "<li>";
            $output .= $this->colorFilePath($trace["file"]) . " on line <span class='colored'>" . $trace["line"] . "</span> <span class='mute'>&rarr;</span> " . $trace["function"] . "()";
            $output .= "</li>";
        }
        $this->template = str_replace("%stacktrace%", $output, $this->template);
    }


    private function colorFilePath($file)
    {

        if (!file_exists($file)) {
            return "<span class='colored'>" . $file . "</span>";
        }

        $file     = array_reverse(explode(DIRECTORY_SEPARATOR, $file));
        $filename = array_shift($file);
        $path     = implode("/", array_reverse($file));
        $output   = "<span class='collapse'><span class='colored'>" . $filename . "</span><span class='mute'>" . $path . "</span></span>";

        return $output;
    }


}