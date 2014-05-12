<?php

namespace Redmine\Api;

class SimpleXMLElement extends \SimpleXMLElement
{
    /**
     * Makes sure string is properly escaped
     * http://stackoverflow.com/questions/552957/rationale-behind-simplexmlelements-handling-of-text-values-in-addchild-and-adda)
     */
    public function addChild()
    {
        $args = func_get_args();
        if (count($args) > 1 && is_string($args[1])) {
            // use the property assignment to set the text correctly
            $text = $args[1];
            $node = call_user_func_array(array('parent', 'addChild'), $args);
            $node->{0} = $text;
        } else {
            $node = call_user_func_array(array('parent', 'addChild'), $args);
        }

        return $node;
    }
}
