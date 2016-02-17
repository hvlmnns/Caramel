<?php

namespace Caramel;

/**
 * Class PluginBase
 * @package Caramel
 */
abstract class Function_Base
{

    /** @var  Config $config */
    public $config;

    /** @var  Storage $variables */
    public $variables;

    /** @var  integer $position */
    protected $position;

    /**
     * PluginBase constructor.
     * @param Caramel $milk
     */
    public function __construct(Caramel $milk)
    {
        $this->milk      = $milk;
        $this->config    = $milk->config();
        $this->variables = $milk->variables();
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getPosition()
    {
        if (!is_null($this->position)) {
            return $this->position;
        } else {
            $pluginName = str_replace("\\", "&#92;", get_class($this));
            throw new \Exception("you need to set a position for " . $pluginName . "!");
        }
    }


    /**
     * @return string
     */
    public function getName()
    {
        return get_class($this);
    }

    /**
     * this is called before we even touch a node
     * so we can add stuff to our config etc
     * @var array $dom
     * @return array $dom
     * has to return $dom
     */
    public function preProcess($dom)
    {
        return $dom;
    }

    /**
     * processes the actual node
     * @var Storage $node
     * @return Storage $node
     * hast to return $node
     */
    public function process($node)
    {
        return $node;
    }

    /**
     * this is called after the plugins processed
     * all nodes
     * @var array $dom
     * @return array $dom
     * * has to return $dom
     */
    public function postProcess($dom)
    {
        return $dom;
    }

    /**
     * this is called after the plugins processed
     * all nodes and converted it into a html string
     * @var array $dom
     * @return array $dom
     * * has to return $dom
     */
    public function processOutput($output)
    {
        return $output;
    }

}