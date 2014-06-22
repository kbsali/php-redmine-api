<?php

namespace Redmine\Api;

class SimpleXMLElement extends \SimpleXMLElement
{
    /**
     * Makes sure string is properly escaped
     * http://stackoverflow.com/questions/552957/rationale-behind-simplexmlelements-handling-of-text-values-in-addchild-and-adda)
     */
    public function addChild($name, $value = null, $ns = null)
    {
        $args = func_get_args();
        if (count($args) > 1 && is_string($args[1])) {
            // escape the value properly
            $args[1] = htmlspecialchars($args[1]);
        }

        return call_user_func_array(array('parent', 'addChild'), $args);
    }
}
