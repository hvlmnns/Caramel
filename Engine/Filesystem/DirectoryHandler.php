<?php

namespace Temple\Engine\Filesystem;


use Temple\Engine\Config;
use Temple\Engine\Exception\Exception;
use Temple\Engine\InjectionManager\Injection;


/**
 * Class Directories
 *
 * @package Temple
 */
class DirectoryHandler extends Injection
{

    /** @var  Config $Config */
    protected $Config;


    /** @inheritdoc */
    public function dependencies()
    {
        return array(
            "Engine/Config" => "Config"
        );
    }


    /**
     * just a helper for the exception class
     * DirectoryHandler constructor.
     *
     * @param Config|null $Config
     */
    public function __construct(Config $Config = null)
    {
        $this->Config = $Config;
    }


    /**
     * returns all template Directories
     *
     * @return mixed
     */
    public function getTemplateDirs()
    {
        return $this->check();
    }


    /**
     * returns the directory of the given template
     *
     * @param $file
     * @param $exception bool
     *
     * @return bool|string
     */
    public function getTemplatePath($file, $exception = false)
    {
        return $this->templateExists($file, $exception);
    }


    /**
     * adds a template directory
     *
     * @param $dir
     *
     * @return bool|string
     */
    public function addTemplateDir($dir)
    {
        $dirs = $this->check();
        if (array_key_exists($dir, array_flip($dirs))) {
            return false;
        } else {
            if (!$dir) {
                return false;
            }
            $dir = $this->getPath($dir);

            # always add a trailing space
            if (strrev($dir)[0] != "/") {
                $dir = $dir . "/";
            }

            array_unshift($dirs, $dir);


            $this->Config->setTemplateDirs($dirs);

            return $dir;
        }
    }


    /**
     * removes a template directory
     *
     * @param null $levelOrPath
     *
     * @return array
     */
    public function removeTemplateDir($levelOrPath = null)
    {

        $dirs = $this->Config->getTemplateDirs();

        if (is_numeric($levelOrPath)) {
            if (array_key_exists($levelOrPath, $dirs)) {
                unset($dirs[ $levelOrPath ]);
            }
        } elseif (is_string($levelOrPath)) {
            if (in_array($levelOrPath, $dirs)) {
                $flipped = array_flip($dirs);
                $key = $flipped[ $levelOrPath ];
                unset($dirs[ $key ]);
            }
        }

        return $this->Config->setTemplateDirs($dirs);
    }


    /**
     * @param $file
     * @param $exception bool
     *
     * @return bool|string
     *
     * @throws Exception
     */
    public function templateExists($file, $exception = false)
    {
        $dirs = $this->getTemplateDirs();

        $file = $this->normalizeExtension($file);

        foreach ($dirs as $level => $dir) {
            $checkFile = str_replace($dir, "", $file);
            $checkFile = $dir . $checkFile;
            if (file_exists($checkFile)) {
                return $checkFile;
            }
        }

        if ($exception) {
            throw new Exception(123, "Template file not found", $file);
        }

        return false;
    }


    /**
     * @param $file
     *
     * @return string
     */
    public function normalizeExtension($file)
    {
        $file = $this->cleanExtension($file);
        // todo: add language extension
        $file .= "." . $this->Config->getExtension();

        return $file;
    }


    /**
     * @param $file
     *
     * @return string
     */
    public function cleanExtension($file)
    {
        $file = preg_replace('/\.[^.]*?$/', '', $file);

        return $file;
    }


    /**
     * sets the cache directory
     *
     * @param $dir
     *
     * @return string|void
     */
    public function setCacheDir($dir)
    {
        $dir = $this->validate($dir);
        $dir = $this->Config->setCacheDir($dir);

        return $dir;
    }


    /**
     * returns the current cache directory
     *
     * @return string
     */
    public function getCacheDir()
    {
        $dir = $this->Config->getCacheDir();
        $dir = $this->createDir($dir);
        $dir = realpath($dir) . DIRECTORY_SEPARATOR;
        $dir = $this->validate($dir);

        return $dir;
    }


    /**
     * creates a directory at the given path
     *
     * @param $dir
     *
     * @return string
     * @throws \Temple\Engine\Exception\Exception
     */
    public function createDir($dir)
    {

        $isFile = (preg_replace('#(|/).*?[^/]\.[^/]+?$#', "isFile", $dir) == "isFile");
        if ($isFile) {
            $dir = dirname($this->getPath($dir));
        } else {
            $dir = realpath($this->getPath($dir));
        }

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        return $dir;
    }


    /**
     * checks if we have a relative or an absolute directory
     * and returns the adjusted directory
     *
     * @param $dir
     *
     * @return string
     */
    public function getPath($dir)
    {
        if ($dir[0] != "/") {
            $dir = $this->getFrameworkDirectory() . $dir;
        } else {
            $docRoot = preg_replace("/\/$/", "", $_SERVER["DOCUMENT_ROOT"]);
            $dir = $docRoot . preg_replace("#^" . preg_quote($docRoot) . "#", "", $dir);
        }
        $dir = str_replace("/./", "/", $dir) . "/";
        $dir = preg_replace("/\/+/", "/", $dir);

        return $dir;
    }


    /**
     * returns the path of Temple
     *
     * @return string
     */
    public function getFrameworkDirectory()
    {
        $namespaces = explode("\\", __NAMESPACE__);
        $frameworkName = reset($namespaces);
        $frameworkDir = explode($frameworkName, __DIR__);
        $frameworkDir = $frameworkDir[0] . $frameworkName . DIRECTORY_SEPARATOR;

        return $frameworkDir;
    }


    /**
     * iterates over all template directories and checks if they are valid
     *
     * @return mixed
     * @throws \Temple\Engine\Exception\Exception
     */
    private function check()
    {

        $dirs = $this->Config->getTemplateDirs();
        if (is_array($dirs)) {
            foreach ($dirs as $dir) {
                $this->validate($dir);
            }
        } else {
            $this->validate($dirs);
        }

        return $this->Config->getTemplateDirs();
    }


    /**
     * checks if the passed directory exists
     *
     * @param $dir
     * @param $create
     *
     * @return string
     * @throws Exception
     */
    public function validate($dir, $create = false)
    {
        $dir = $this->getPath($dir);
        if (is_dir($dir)) return $dir;

        if ($create) {
            return $this->createDir($dir);
        }

        throw new Exception(1, "Can't add directory because it does't exist.", $dir);
    }


}