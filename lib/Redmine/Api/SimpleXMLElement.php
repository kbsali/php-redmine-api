<?php

namespace Redmine\Api;

class SimpleXMLElement extends \SimpleXMLElement
{
    /**
     * Makes sure string is properly escaped
     * http://stackoverflow.com/questions/552957/rationale-behind-simplexmlelements-handling-of-text-values-in-addchild-and-adda).
     */
    public function addChild($name, $value = null, $ns = null)
    {
        $args = func_get_args();
        if (count($args) > 1 && is_string($args[1])) {
            // use the property assignment to set the text correctly
            $text = $args[1];
            // we need to clear "$value" argument value cause it will product Unterminated entity reference for "&"
            $args[1] = '';
            $node = call_user_func_array(array('parent', 'addChild'), $args);
            // next, all characters like "&", "<", ">" will be properly escaped
            $node->{intval(0)} = $text;
        } else {
            $node = call_user_func_array(array('parent', 'addChild'), $args);
        }

        return $node;
    }
}
