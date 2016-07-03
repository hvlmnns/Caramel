<?php

namespace Underware\Models;


/**
 * all NodeModel defaults are set here
 * Class NodeModel
 *
 * @package Underware
 */
class FunctionNode extends BaseNode
{

    /**
     * returns the tag for the current line
     *
     * @param string $line
     * @return string
     */
    protected function tag($line)
    {
        $tag                   = parent::tag($line);
        $tag["tag"]            = substr($tag["tag"], 1);
        $tag["opening"]["tag"] = $tag["tag"];
        $tag["closing"]["tag"] = $tag["tag"];

        return $tag;
    }


    public function isFunction()
    {
        return true;
    }


    /**
     * @param string $line
     * @return array|string
     * @throws \Underware\Exception\Exception
     */
    protected function attributes($line)
    {
        $attributes = array();

        return $attributes;
    }
}
