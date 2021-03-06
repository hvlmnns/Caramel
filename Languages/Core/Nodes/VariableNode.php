<?php

namespace Temple\Languages\Core\Nodes;


use Temple\Engine\Structs\Node\Node;


/**
 * Class VariableNode
 *
 * @package Temple\Languages\Core\Nodes
 */
class VariableNode extends Node
{

    /** @var  string $variableName */
    private $variableName;

    /** @var  string $variableValue */
    private $variableValue;


    public function check()
    {
//        if ($this->getTag() == "var") {
//            return true;
//        }
        return false;
    }


    /** @inheritdoc */
    public function setup()
    {
        $this->setSelfClosing(true);
        $this->getVariableName();
        $this->getVariableValue();
        $this->setFunction(true);
        $this->variableValue = $this->Instance->EventManager()->dispatch($this->getLanguageName(), "plugin.variableNode.variableReturn", array($this->variableValue, $this->getDom()->getVariables()));
        $this->getDom()->getVariables()->set($this->variableName, $this->variableValue);

        return $this;
    }


    private function getVariableName()
    {
        $this->variableName = trim(str_replace($this->getTag(), "", preg_split("/=/", $this->plain, 2)[0]));
    }


    private function getVariableValue()
    {
        $value = trim(preg_split("/=/", $this->plain, 2)[1]);
        $value = json_decode(json_encode(array($value), JSON_NUMERIC_CHECK), true);
        $value = $value[0];


        if ($value == "true" || $value == "false") {
            $value = ($value === "true");
        } elseif (is_string($value)) {
            $value = $this->checkForQuotedString($value);
            $value = $this->checkForArrayDefinition($value);
        }

        $this->variableValue = $value;
    }


    /**
     * unquotes an already quoted string
     *
     * @param $value
     *
     * @return string
     */
    private function checkForQuotedString($value)
    {
        $quote    = substr($value, 0, 1);
        $endQuote = substr(strrev($value), 0, 1);
        if (($quote == "'" || $quote == '"') && $endQuote == $quote) {
            $value = substr($value, 1, (sizeof($value) - 2));
        }

        return $value;
    }


    /**
     * returns array if the definition matches
     *
     * @param $value
     *
     * @return array|string
     */
    private function checkForArrayDefinition($value)
    {
        $bracket    = substr($value, 0, 1);
        $endBracket = substr(strrev($value), 0, 1);
        if ($bracket == "[" && $endBracket == "]") {
            $array = array();
            $value = substr($value, 1, (sizeof($value) - 2));
            $value = explode(",", $value);
            foreach ($value as $item) {
                $exploded = explode("=", $item);
                if (sizeof($exploded) > 1) {
                    $key        = $exploded[0];
                    $arrayValue = $exploded[1];
                } else {
                    $key        = false;
                    $arrayValue = $exploded[0];
                }

                $arrayValue = $this->Instance->EventManager()->dispatch($this->getLanguageName(), "plugin.variableNode.variableReturn", array($arrayValue, $this->getDom()->getVariables()));

                if ($key !== false) {
                    $array[ $key ] = $arrayValue;
                } else {
                    $array[] = $arrayValue;
                }

            }
            $value = $array;
        }

        return $value;
    }


    /**
     * creates the output
     *
     * @return string
     */
    public function compile()
    {
        return "";
    }


}