<?php

namespace Temple\Nodes;


/**
 * all NodeModel defaults are set here
 * Class NodeModel
 *
 * @package Temple
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


    /**
     * @param string $line
     * @return array|string
     * @throws \Temple\Exceptions\TempleException
     */
    protected function attributes($line)
    {
        $attributes = array();
        return $attributes;
    }
}
