<?php

namespace Temple\Engine\Languages;


use Temple\Engine\Cache\VariablesBaseCache;
use Temple\Engine\Instance;
use Temple\Engine\EventManager\Event;
use Temple\Engine\Exception\Exception;


class Language
{

    /** @var  string $path */
    private $path;

    /** @var Instance $Instance */
    protected $Instance;

    /** @var string $name */
    protected $name;

    /** @var VariablesBaseCache $variableCache */
    private $variableCache = null;


    public function __construct(Instance $Instance, $name, $path)
    {

        // todo: add this Event to the cache invalidator to clear the cache if we change a node
        $this->Instance = $Instance;
        $this->name   = $name;
        $this->path   = $path;

        $this->setupVariableCache();

    }


    /**
     * this function is used to register all nodes and plugins for the language
     * return void
     *
     * @throws Exception
     */
    public function register()
    {
        throw new Exception(1, "Please implement the register function for %" . get_class($this) . "%", __FILE__);
    }


    /**
     * this function can be used to implement a variable cache
     *
     * @return null|VariablesBaseCache
     */
    public function registerVariableCache()
    {
        return null;
    }


    /**
     * subscribes an event into the scoped language
     *
     * @param       $name
     * @param Event $event
     */
    public function subscribe($name, Event $event)
    {
        $this->Instance->EventManager()->subscribe($this->name, $name, $event);
    }


    /**
     * sets up the class config
     *
     * @return bool
     * @throws Exception
     */
    private function setupVariableCache()
    {
        $this->variableCache = $this->registerVariableCache();
        if ($this->variableCache instanceof VariablesBaseCache) {
            $this->variableCache->setInstance($this->Instance);
        }
        $Config = $this->Instance->Config()->getLanguageConfig($this->name);
        $Config->setVariableCache($this->variableCache);
    }


    /**
     * @return LanguageConfig
     */
    public function getConfig()
    {
        return $this->Instance->Config()->getLanguageConfig($this->name);
    }

}