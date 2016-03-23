<?php

namespace PHPDocMD;

use Twig_Environment;
use Twig_Loader_Filesystem;
use Twig_SimpleFilter;

/**
 * This class takes the output from 'parser', and generate the markdown
 * templates.
 *
 * @copyright Copyright (C) Evert Pot. All rights reserved.
 * @author    Evert Pot (https://evertpot.coom/)
 * @license   MIT
 */
class Generator
{
    /**
     * Output directory.
     *
     * @var string
     */
    protected $outputDir;

    /**
     * The list of classes and interfaces.
     *
     * @var array
     */
    protected $classDefinitions;

    /**
     * Directory containing the twig templates.
     *
     * @var string
     */
    protected $templateDir;

    /**
     * A simple template for generating links.
     *
     * @var string
     */
    protected $linkTemplate;

    /**
     * Filename for API Index.
     *
     * @var string
     */
    protected $apiIndexFile;

    /**
     * a list of available classes.
     *
     * @var array
     */
    protected $classes;

    /**
     * a list of available namespaces.
     *
     * @var array
     */
    protected $namespaces;

    /**
     * a list of parsed files.
     *
     * @var array
     */
    protected $fileList;

    /**
     * a list of parsed files.
     *
     * @var array
     */
    protected $fileMenu = array();


    /**
     * @param array $classDefinitions
     * @param string $outputDir
     * @param string $templateDir
     * @param string $linkTemplate
     * @param string $apiIndexFile
     */
    function __construct(array $classDefinitions, $outputDir, $templateDir, $linkTemplate = '%c.md', $apiIndexFile = 'ApiIndex.md')
    {
        $this->classDefinitions = $classDefinitions;
        $this->templateDir      = __DIR__ . "/../../../../templates/api/phpdocumentor";
        $this->outputDir        = $this->templateDir . "/../generated/api";
    }

    /**
     * Starts the generator.
     */
    function run()
    {

        $this->getClassesAndNamespaces();
        $this->parseTwig();
    }

    /**
     * sets $this->classes to an array with all classes and the classname as key
     * sets $this->namespaces to an array with all available namespaces
     */
    private function getClassesAndNamespaces()
    {
        $namespaces = array();
        $classes    = $this->classDefinitions;
        foreach ($this->classDefinitions as $classDefinition) {
            $namespace = explode("\\", $classDefinition["className"]);
            array_pop($namespace);
            $namespace                                = implode("\\", $namespace);
            $namespaces[ $namespace ]                 = "";
            $classes[ $classDefinition["className"] ] = $classDefinition;
        }
        $namespaces = array_keys($namespaces);
        sort($namespaces);

        $this->namespaces = $namespaces;
        $this->classes    = $classes;
    }


    private function parseTwig()
    {

        $GLOBALS['PHPDocMD_classes']    = $this->classes;
        $GLOBALS['PHPDocMD_namespaces'] = $this->namespaces;

        $twig = $this->setuptwigEnviroment();

        foreach ($this->namespaces as $namespace) {
            $data              = array();
            $namespaceName     = array_reverse(explode("\\", $namespace))[0];
            $filename          = $namespace . "\\" . strrev(explode("\\", strrev($namespace))[0]);
            $level             = substr_count($namespace, "\\");
            $data["level"]     = $level;
            $data["name"]      = $namespaceName;
            $data["id"]        = strtolower(str_replace("\\", "-", preg_replace("/^.*?\\\/", "", $namespace)));
            $data["namespace"] = $namespace;
            $this->parseTwigFile($twig, "namespace", $namespaceName, $filename, $data);
            foreach ($this->classes as $name => $class) {
                $checkname = str_replace($namespace . "\\", "", $name);
                if (substr_count($checkname, "\\") === 0) {
                    $level          = substr_count($namespace, "\\");
                    $class["level"] = $level;
                    $class["id"]    = strtolower(str_replace("\\", "-", preg_replace("/^.*?\\\/", "", $name)));
                    $class["name"]  = $checkname;
                    $filename       = $namespace . "\\" . $checkname;
                    $this->parseTwigFile($twig, "class", $checkname, $filename, $class);
                }
            }
        }


        $fileList     = json_encode($this->fileList, JSON_PRETTY_PRINT);
        $fileListFile = $this->templateDir . "/../../../api_list.json";

        if (file_exists($fileListFile)) {
            unlink($fileListFile);
            touch($fileListFile);
        }
        file_put_contents($fileListFile, $fileList);

        $this->createMenu($this->fileMenu);
        $this->fileMenu = reset($this->fileMenu);
        $fileMenu       = json_encode($this->fileMenu, JSON_PRETTY_PRINT);
        $fileMenuFile   = $this->templateDir . "/../../../api_menu.json";

        if (file_exists($fileMenuFile)) {
            unlink($fileMenuFile);
            touch($fileMenuFile);
        }
        file_put_contents($fileMenuFile, $fileMenu);


        return $twig;
    }

    /**
     * parsed a twig file
     *
     * @param Twig_Environment $twig
     * @param string $type
     * @param string $filename
     * @param array $data
     */
    private function parseTwigFile(Twig_Environment $twig, $type, $name, $filename, array $data)
    {

        $output = $twig->render($type . '.twig', $data);

        $filename                = preg_replace("/\\\/", "/", $filename) . ".html";
        $this->fileList[ $name ] = "api/generated/api/" . $filename;
        $outputFile              = $this->outputDir . "/" . $filename;
        if (!is_dir(dirname($outputFile))) {
            mkdir(dirname($outputFile), 0777, true);
        }
        touch($outputFile);

        file_put_contents($outputFile, $output);
    }


    /**
     * creates the twig environment
     *
     * @return Twig_Environment
     */
    private function setuptwigEnviroment()
    {
        $loader = new Twig_Loader_Filesystem($this->templateDir, ['cache' => false, 'debug' => true,]);
        $twig   = new Twig_Environment($loader);

        $filter = new Twig_SimpleFilter('classlink', ['PHPDocMd\\Generator', 'classLink'], array('is_safe' => array('html')));
        $twig->addFilter($filter);
        $filter = new Twig_SimpleFilter('dump', ['PHPDocMd\\Generator', 'dump'], array('is_safe' => array('html')));
        $twig->addFilter($filter);
        $filter = new Twig_SimpleFilter('instance', ['PHPDocMd\\Generator', 'instance'], array('is_safe' => array('html')));
        $twig->addFilter($filter);

        return $twig;
    }

    /**
     * creates the menu
     *
     * @return mixed
     */
    private function createMenu()
    {
        foreach ($this->classDefinitions as $className => $classInfo) {
            $current = &$this->fileMenu;
            foreach (explode('\\', $className) as $part) {
                if (!isset($current[ $part ])) {
                    $current[ $part ] = [];
                }
                $current = &$current[ $part ];
            }
        }
    }


    /**
     * This is a twig template function.
     *
     * This function allows us to easily link classes to their existing pages.
     *
     * Due to the unfortunate way twig works, this must be static, and we must use a global to
     * achieve our goal.
     *
     * @param string $className
     *
     * @return string
     */
    static function classLink($string)
    {

        $temp = array();

        $strings = explode("|", $string);
        foreach ($strings as $string) {
            $contained = false;
            $namespace = $GLOBALS['PHPDocMD_namespaces'][0];
            if (strpos($string, $namespace) !== false) {
                $class    = strrev(explode("\\", strrev($string))[0]);
                $link     = "#" . strtolower(str_replace("\\", "-", trim(str_replace($namespace, "", $string), "\\")));
                $template = "<a href='" . $link . "' title='" . $class . "'>" . $class . "</a>";
                $temp[]   = $template;
            } else {
                $temp[] = $string;
            }
        }

        $temp = implode("|", $temp);

        return $temp;
    }


    /**
     * This is a twig template function.
     *
     * This function dumps a variable
     *
     * @param string $var
     *
     * @return string
     */
    static function dump($var)
    {
        return '<code><pre>' . print_r($var, true) . '</pre></code><br>';
    }


    /**
     * This is a twig template function.
     *
     * thiw function returns the instance of the passed variable.
     *
     * @param mixed $var
     *
     * @return string
     */
    static function instance($var)
    {
        return get_class($var);
    }

}
