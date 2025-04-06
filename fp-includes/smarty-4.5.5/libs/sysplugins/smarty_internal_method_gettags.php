<?php

/**
 * Smarty Method GetTags
 *
 * Smarty::getTags() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_Internal_Method_GetTags
{
    /**
     * Valid for Smarty and template object
     *
     * @var int
     */
    public $objMap = 3;

    /**
     * Return array of tag/attributes of all tags used by an template
     *
     * @api  Smarty::getTags()
     * @link https://www.smarty.net/docs/en/api.get.tags.tpl
     *
     * @param \Smarty_Internal_TemplateBase|\Smarty_Internal_Template|\Smarty $obj
     * @param null|string|Smarty_Internal_Template                            $template
     *
     * @return array<string, mixed> Tag attributes from the template
     * @throws \SmartyException
     */
    public function getTags(Smarty_Internal_TemplateBase $obj, $template = null)
    {
        /* @var Smarty $smarty */
        $smarty = $obj->_getSmartyObj();
        $tpl = null;

        if ($obj->_isTplObj() && !isset($template)) {
            $tpl = clone $obj;
        } elseif ($template instanceof Smarty_Internal_Template && $template->_isTplObj()) {
            $tpl = clone $template;
        } elseif (is_string($template)) {
            $templateClass = $smarty->template_class;
            if (!class_exists($templateClass)) {
                throw new SmartyException('Template class \'' . $templateClass . '\' not found.');
            }

            /* @var Smarty_Internal_Template $tpl */
            $tpl = new $templateClass($template, $smarty);

            if (!$tpl->source->exists) {
                throw new SmartyException('Unable to load template ' . $tpl->source->type . ' \'' . $tpl->source->name . '\'');
            }
        }

        if ($tpl instanceof Smarty_Internal_Template) {
            $tpl->smarty = clone $tpl->smarty;
            $tpl->smarty->_cache['get_used_tags'] = true;
            $tpl->_cache['used_tags'] = array();
            $tpl->smarty->merge_compiled_includes = false;
            $tpl->smarty->disableSecurity();
            $tpl->caching = Smarty::CACHING_OFF;
            $tpl->loadCompiler();
            $tpl->compiler->compileTemplate($tpl);

            return $tpl->_cache['used_tags'];
        }

        throw new SmartyException('Missing or invalid template specification');
    }
}
