<?php

namespace Temple\Engine\Filesystem;


use Temple\Engine\Config;
use Temple\Engine\Exception\Exception;
use Temple\Engine\InjectionManager\Injection;


class Cache extends Injection
{


    /** @var  Config $Config */
    protected $Config;

    /** @var  DirectoryHandler $DirectoryHandler */
    protected $DirectoryHandler;


    /** @inheritdoc */
    public function dependencies()
    {
        return array(
            "Engine/Config"                      => "Config",
            "Engine/Filesystem/DirectoryHandler" => "DirectoryHandler"
        );
    }


    /** @var string $cacheFile */
    private $cacheFile = "cache";


    /**
     * sets the cache directory
     *
     * @param string $dir
     *
     * @return string
     */
    public function setDirectory($dir)
    {
        $this->DirectoryHandler->createDir($dir);

        return $this->DirectoryHandler->setCacheDir($dir);
    }


    /**
     * returns the cache directory
     *
     * @return string
     */
    public function getDirectory()
    {
        $cacheDir = $this->Config->getCacheDir();
        $this->DirectoryHandler->createDir($cacheDir);

        return $this->DirectoryHandler->getCacheDir();
    }


    /**
     * saves the file to the cache and returns its path
     *
     * @param     $file
     * @param     $content
     *
     * @return string
     * @throws Exception
     */
    public function save($file, $content)
    {
        $this->setTime($file);
        $file = $this->createFile($file);
        file_put_contents($file, $content);

        return $file;
    }


    /**
     * @throws Exception
     * @return bool
     */
    public function invalidate()
    {

        $cacheFile = $this->getPath($this->cacheFile);
        if (file_exists($cacheFile)) {
            if (is_writable($cacheFile)) {
                unlink($cacheFile);
            } else {
                throw new Exception(500, "You don't have the permission to delete this file", $cacheFile);
            }
        }

        return false;
    }


    /**
     * returns if the file passed is newer than the cached file
     *
     * @param $file
     *
     * @return bool
     */
    public function isModified($file)
    {
        if (!$this->Config->isCacheInvalidation()) {
            return false;
        }

        if (!$this->Config->isCacheEnabled()) {
            return true;
        }
        $file  = $this->cleanFile($file);
        $cache = $this->getCache();

        if (!$cache) {
            return true;
        } else {
            $modified = $this->checkModified($file);
            if (!$modified) {
                $modified = $this->checkDependencies($file);
            }
        }

        return $modified;
    }


    /**
     * checks all of the files dependencies and returns true if they are modified
     *
     * @param string $file
     *
     * @return bool
     */
    private function checkDependencies($file)
    {
        $cache    = $this->getCache();
        $modified = false;
        if (isset($cache["dependencies"]) && isset($cache["dependencies"][ $file ])) {
            foreach ($cache["dependencies"][ $file ] as $dependency) {
                $template = $dependency["file"];
                $type     = $dependency["type"];
                $modified = $this->checkModified($template, $type);
                if ($modified) {
                    break;
                }
            }
        }

        return $modified;
    }


    /**
     * check if a file or its parents are modified
     *
     * @param      $file
     * @param bool $needToExist | if the file has to exist withing the cache
     *
     * @return bool
     */
    public function checkModified($file, $needToExist = true)
    {
        $cache         = $this->getCache();
        $templateCache = $cache["templates"];

        $modified = false;

        foreach ($this->getTemplateFiles($file) as $template) {
            $templatePath = $template;
            $template     = $this->cleanFile($template);

            if (isset($templateCache[ $template ])) {

                $cacheTime   = $templateCache[ $template ][ md5($templatePath) ];
                $currentTime = filemtime($templatePath);
                $timeDiffers = $cacheTime != $currentTime;
                $exists      = true;
                if ($needToExist) {
                    $exists = $this->CacheFilesExist($templatePath);
                }
                if ($timeDiffers || !$exists) {
                    $this->setTime($templatePath);
                    $modified = true;
                }
            } else {
                $this->setTime($templatePath);
                $modified = true;
            }
            if ($modified) {
                break;
            }
        }

        return $modified;
    }


    /**
     * check if all needed variable files exist
     *
     * @param string $templatePath
     *
     * @return bool
     */
    private function CacheFilesExist($templatePath)
    {
        $cacheFilePath = $templatePath;
        foreach ($this->DirectoryHandler->getTemplateDirs() as $templateDir) {
            $cacheFilePath = str_replace($templateDir, "", $cacheFilePath);
        }

        // check if all needed variable files exist
        $templateFile = $this->getDirectory() . str_replace("." . $this->Config->getExtension(), ".php", $cacheFilePath);
        $variableFile = $this->getDirectory() . str_replace("." . $this->Config->getExtension(), ".variables.php", $cacheFilePath);

        if (!file_exists($templateFile) || !file_exists($variableFile)) {
            return false;
        }

        return true;
    }


    /**
     * returns a cache file
     *
     * @param $file
     *
     * @return string
     */
    public function getFile($file)
    {
        # returns the cache file
        $file = $this->createFile($file);

        return $file;
    }


    /**
     * adds a dependency to the cache
     *
     * @param string $parent
     * @param string $file
     * @param bool   $needToExist
     *
     * @return bool
     * @throws Exception
     */
    public function addDependency($parent, $file, $needToExist = true)
    {

        if (!$file || $file == "") {
            throw new Exception(1, "Please set a file for your dependency");
        }

        if (!$parent || $parent == "") {
            throw new Exception(1, "Please set a parent file for your dependency");
        }

        $file   = $this->cleanFile($file);
        $parent = $this->cleanFile($parent);

        $cache = $this->getCache();

        if (!isset($cache["templates"][ $file ])) {
            $this->setTime($file);
        }

        if (!isset($cache["dependencies"][ $parent ])) {
            $cache["dependencies"][ $parent ] = array();
        }

        if (!in_array($file, $cache["dependencies"][ $parent ])) {
            array_push($cache["dependencies"][ $parent ], array("file" => $file, "type" => $needToExist));
        }

        return $this->saveCache($cache);
    }


    /**
     * removes the whole cache directory
     *
     * @param null $dir
     *
     * @return bool
     */
    public function clear($dir = null)
    {
        if ($dir == null) {
            $dir = $this->getDirectory();
        }
        foreach (scandir($dir) as $item) {
            if ($item != '..' && $item != '.') {
                $item = $dir . "/" . $item;
                if (!is_dir($item)) {
                    unlink($item);
                } else {
                    $this->clear($item);
                }
            }
        }

        return rmdir($dir);
    }


    /**
     * writes the modify times for the current template
     * into our cache file
     *
     * @param $file
     *
     * @return bool
     */
    private function setTime($file)
    {
        $file      = $this->cleanFile($file);
        $cache     = $this->getCache();
        $templates = $this->getTemplateFiles($file);
        foreach ($templates as $template) {
            $cache["templates"][ $file ][ md5($template) ] = filemtime($template);
        }

        return $this->saveCache($cache);
    }


    /**
     * returns the cache array
     *
     * @return array
     */
    protected function getCache()
    {
        $cacheFile = $this->createFile($this->cacheFile);
        $cache     = unserialize(file_get_contents($cacheFile));


        // set initial templates sub array
        if (!isset($cache["templates"])) {
            $cache["templates"] = array();
        }

        // set initial dependencies sub array
        if (!isset($cache["dependencies"])) {
            $cache["dependencies"] = array();
        }

        return $cache;
    }


    /**
     * saves the array to the cache
     *
     * @param array $cache
     *
     * @return bool
     */
    protected function saveCache($cache)
    {
        $cacheFile = $this->createFile($this->cacheFile);

        return file_put_contents($cacheFile, serialize($cache));
    }


    /**
     * returns all found template files
     *
     * @param $file
     *
     * @return array
     */
    private function getTemplateFiles($file)
    {

        $dirs  = $this->DirectoryHandler->getTemplateDirs();
        $files = array();
        foreach ($dirs as $dir) {
            $templateFile = $dir . $file . "." . $this->Config->getExtension();
            if (file_exists($templateFile)) {
                $files[] = $templateFile;
            }
        }


        return $files;
    }


    /**
     * creates the file if its not already there
     *
     * @param $file
     *
     * @return mixed|string
     */
    private function createFile($file)
    {
        $file = $this->getPath($file);
        # setup the file
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        if (!file_exists($file)) touch($file);

        return $file;
    }


    /**
     * returns the cache path for the given file
     *
     * @param $file
     *
     * @return string
     */
    public function getPath($file)
    {
        # remove the template dir
        $file = $this->cleanFile($file);
        $file = $this->extension($file);
        $file = $this->getDirectory() . $file;

        return $file;
    }


    /**
     * adds a php extension to the files path
     *
     * @param $file
     *
     * @return mixed|string
     */
    private function extension($file)
    {
        $file = str_replace("." . $this->Config->getExtension(), "", $file);
        $file = str_replace(".php", "", $file);
        $file = $file . ".php";

        return $file;
    }


    /**
     * removes the template dirs and the extension form a file path
     *
     * @param $file
     *
     * @return string
     */
    private function cleanFile($file)
    {
        foreach ($this->DirectoryHandler->getTemplateDirs() as $templateDir) {
            $file = str_replace($templateDir, "", $file);
        }

        $file = str_replace("." . $this->Config->getExtension(), "", $file);

        return $file;
    }


}