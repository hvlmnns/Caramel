<?php

namespace Temple;


use Temple\DependencyManagement\DependencyContainer;
use Temple\DependencyManagement\DependencyInstance;
use Temple\Exceptions\ExceptionHandler;
use Temple\Exceptions\TempleException;
use Temple\Repositories\Storage;

class Config extends DependencyInstance
{

    /** @var Storage $config */
    private $config;


    /** @inheritdoc */
    public function dependencies()
    {
        return array();
    }


    /**
     * Config constructor.
     *
     * @param DependencyContainer $dependencyContainer
     */
    public function __construct(DependencyContainer $dependencyContainer)
    {
        parent::__construct($dependencyContainer);
        $this->config = new Storage();
    }


    /**
     * merges a new config file into our current config
     *
     * @param $file
     * @throws TempleException
     */
    public function addConfigFile($file)
    {
        if (file_exists($file)) {
            /** @noinspection PhpIncludeInspection */
            require_once $file;
            if (isset($config)) {
                if (sizeof($config) > 0) {
                    $this->config->merge($config);
                    if ($this->config->has("errorhandler") && $this->config->get("errorhandler")) {
                        $this->config->set("errorhandler", new ExceptionHandler());
                    }
                }
            } else {
                throw new TempleException('You must declare an "$config" array!', $file);
            }
        } else {
            throw new TempleException("Can't find the config file!", $file);
        }
    }


    /**
     * @param null $path
     * @return mixed
     * @throws TempleException
     */
    public function get($path = NULL)
    {
        return $this->config->get($path);
    }


    /**
     * @param $path
     * @param $value
     * @return bool
     */
    public function set($path,$value)
    {
        return $this->config->set($path,$value);
    }


    /**
     * @param $path
     * @return array
     */
    public function has($path)
    {
        return $this->config->has($path);
    }

}