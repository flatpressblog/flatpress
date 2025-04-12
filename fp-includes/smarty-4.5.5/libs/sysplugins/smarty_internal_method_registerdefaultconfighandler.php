<?php

/**
 * Smarty Method RegisterDefaultConfigHandler
 *
 * Smarty::registerDefaultConfigHandler() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_Internal_Method_RegisterDefaultConfigHandler
{
    /**
     * Valid for Smarty and template object
     *
     * @var int
     */
    public $objMap = 3;

    /**
     * Register config default handler
     *
     * @api Smarty::registerDefaultConfigHandler()
     *
     * @param \Smarty_Internal_TemplateBase        $obj Smarty or Template object
     * @param callable                             $callback class/method name
     *
     * @return \Smarty_Internal_TemplateBase
     * @throws SmartyException                     if $callback is not callable
     */
    public function registerDefaultConfigHandler(Smarty_Internal_TemplateBase $obj, $callback)
    {
        $smarty = $obj->_getSmartyObj();
        if (is_callable($callback)) {
            $smarty->default_config_handler_func = $callback;
        } else {
            throw new SmartyException('Default config handler not callable');
        }
        return $obj;
    }
}
