<?php

namespace Underware\Models;


use Underware\EventManager\Event;
use Underware\Exception\Exception;
use Underware\Instance;
use Underware\Utilities\Storage;


/**
 * Class Plugins
 *
 * @package Underware
 */
class Plugin extends Event implements PluginInterface
{

    /** @var Instance $Instance */
    protected $Instance;

    /** @var  Storage $attributes */
    protected $attributes = array();


    function dispatch($args, Instance $Instance)
    {
        $this->Instance = $Instance;

        if (!$this->check($args)) {
            return $args;
        }
        
        if (sizeof($this->attributes) > 0) {
            $this->generateAttributes($args);
        }

        return $this->process($args);
    }


    /**
     * checking if the arguments are valid for this plugin
     *
     * @param mixed $args
     *
     * @return bool
     */
    public function check($args)
    {
        return false;
    }


    /**
     * returns the plugin name
     *
     * @return string
     */
    public function getName()
    {
        return strrev(explode("\\", strrev(get_class($this)))[0]);
    }


    public function generateAttributes($args)
    {
        if (is_array($args)) {
            foreach ($args as $arg) {
                $this->mapAttributes($arg);
            }
        } else {
            $this->mapAttributes($args);
        }
    }


    /**
     * map the attributes to the defined array
     *
     * @param $node
     *
     * @throws Exception
     */
    private function mapAttributes($node)
    {
        if ($node instanceof BaseNode) {
            $attributes = new Storage();
            $count      = 0;
            foreach ($node->get("attributes") as $name => $value) {
                if ($value == "") {
                    $value = $name;
                }
                if (isset($this->attributes[ $count ])) {
                    $name = $this->attributes[ $count ];
                    $attributes->set($name, $value);
                    $count = $count + 1;
                }
            }
            $this->attributes = $attributes;
        }
    }


    /**
     * the actual plugin process method
     *
     * @var mixed $element
     * @return mixed $element
     * @@throws Exception
     */
    public function process($args)
    {
        $name = $this->getName();
        throw new Exception("Please implement the 'process' method for the plugin '$name'!");
    }

}