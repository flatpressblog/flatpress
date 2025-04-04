<?php
/**
 * Smarty Internal Plugin Compile Special Smarty Variable
 * Compiles the special $smarty variables
 *
 * @package    Smarty
 * @subpackage Compiler
 * @author     Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile special Smarty Variable Class
 *
 * @package    Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Private_Special_Variable extends Smarty_Internal_CompileBase
{
    /**
     * Compiles code for the special $smarty variables
     *
     * @param array                                 $args     array with attributes from parser
     * @param \Smarty_Internal_TemplateCompilerBase $compiler compiler object
     * @param string                                $parameter Parameter string representing the special variable
     *
     * @return string compiled code
     * @throws \SmartyCompilerException
     */
    public function compile($args, Smarty_Internal_TemplateCompilerBase $compiler, $parameter)
    {
        $_index = preg_split("/\]\[/", substr($parameter, 1, strlen($parameter) - 2));
        $variable = smarty_strtolower_ascii($compiler->getId($_index[0]));
        if ($variable === false) {
            $compiler->trigger_template_error("special \$Smarty variable name index can not be variable", null, true);
            return '';
        }
        if (!isset($compiler->smarty->security_policy)
            || $compiler->smarty->security_policy->isTrustedSpecialSmartyVar($variable, $compiler)
        ) {
            switch ($variable) {
                case 'foreach':
                case 'section':
                    if (!isset(Smarty_Internal_TemplateCompilerBase::$_tag_objects[$variable])) {
                        $class = 'Smarty_Internal_Compile_' . smarty_ucfirst_ascii($variable);
                        Smarty_Internal_TemplateCompilerBase::$_tag_objects[$variable] = new $class;
                    }
                    return Smarty_Internal_TemplateCompilerBase::$_tag_objects[$variable]->compileSpecialVariable(
                        array(),
                        $compiler,
                        $_index
                    );
                case 'capture':
                    if (class_exists('Smarty_Internal_Compile_Capture')) {
                        return Smarty_Internal_Compile_Capture::compileSpecialVariable(array(), $compiler, $_index);
                    }
                    return '';
                case 'now':
                    return 'time()';
                case 'cookies':
                case 'get':
                case 'post':
                case 'env':
                case 'server':
                case 'session':
                case 'request':
                    if (isset($compiler->smarty->security_policy)
                        && !$compiler->smarty->security_policy->allow_super_globals
                    ) {
                        $compiler->trigger_template_error("(secure mode) super globals not permitted");
                        return '';
                    }
                    $compiled_ref = $variable === 'cookies'
                        ? '$_COOKIE'
                        : '$_' . smarty_strtoupper_ascii($variable);
                    if (isset($_index[1])) {
                        array_shift($_index);
                        foreach ($_index as $_ind) {
                            $compiled_ref .= '[' . $_ind . ']';
                        }
                    }
                    return $compiled_ref;
                case 'template':
                    return 'basename($_smarty_tpl->source->filepath)';
                case 'template_object':
                    if (isset($compiler->smarty->security_policy)) {
                        $compiler->trigger_template_error("(secure mode) template_object not permitted");
                        return '';
                    }
                    return '$_smarty_tpl';
                case 'current_dir':
                    return 'dirname($_smarty_tpl->source->filepath)';
                case 'version':
                    return 'Smarty::SMARTY_VERSION';
                case 'const':
                    if (isset($compiler->smarty->security_policy)
                        && !$compiler->smarty->security_policy->allow_constants
                    ) {
                        $compiler->trigger_template_error("(secure mode) constants not permitted");
                        return '';
                    }
                    if (strpos($_index[1], '$') === false && strpos($_index[1], '\'') === false) {
                        return '(defined(\'' . $_index[1] . '\') ? constant(\'' . $_index[1] . '\') : null)';
                    } else {
                        return '(defined(' . $_index[1] . ') ? constant(' . $_index[1] . ') : null)';
                    }
                case 'config':
                    if (isset($_index[2])) {
                        return '(is_array($tmp = $_smarty_tpl->smarty->ext->configload->_getConfigVariable($_smarty_tpl, ' . $_index[1] . ')) ? $tmp[' . $_index[2] . '] : null)';
                    } else {
                        return '$_smarty_tpl->smarty->ext->configload->_getConfigVariable($_smarty_tpl, ' . $_index[1] . ')';
                    }
                case 'ldelim':
                    return '$_smarty_tpl->smarty->left_delimiter';
                case 'rdelim':
                    return '$_smarty_tpl->smarty->right_delimiter';
                default:
                    $compiler->trigger_template_error('$smarty.' . trim($_index[0], "'") . ' is not defined');
                    return '';
            }
            if (isset($compiled_ref)) {
                if (isset($_index[1])) {
                    array_shift($_index);
                    foreach ($_index as $_ind) {
                        $compiled_ref = $compiled_ref . '[' . $_ind . ']';
                    }
                }
                return $compiled_ref;
            }
        }
        $compiler->trigger_template_error(
            'Unknown or unsupported special variable: $smarty.' . trim($_index[0], "'"),
            null,
            true
        );
        return '';
    }
}
