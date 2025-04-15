<?php
/**
 * Smarty Internal Plugin Compile Print Expression
 * Compiles any tag which will output an expression or variable
 *
 * @package    Smarty
 * @subpackage Compiler
 * @author     Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile Print Expression Class
 *
 * @package    Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Private_Print_Expression extends Smarty_Internal_CompileBase
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $optional_attributes = array('assign');

    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $option_flags = array('nocache', 'nofilter');

    /**
     * Compiles code for generating output from any expression
     *
     * @param array                                 $args      array with attributes from parser
     * @param \Smarty_Internal_TemplateCompilerBase $compiler  compiler object
     * @param array                                 $parameter array with compilation parameter
     *
     * @return string
     * @throws \SmartyException
     */
    public function compile($args, Smarty_Internal_TemplateCompilerBase $compiler, $parameter) : string
    {

        // Check and get attributes
        $_attr = $this->getAttributes($compiler, $args);
        $output = $parameter['value'];

        // Apply tag modifiers
        if (!empty($parameter['modifierlist'])) {
            $output = $compiler->compileTag(
                'private_modifier',
                [],
                [
                    'modifierlist' => $parameter['modifierlist'],
                    'value' => $output,
                ]
            );
        }

        // Case: assign output to template variable
        if (isset($_attr['assign'])) {
            return "<?php \$_smarty_tpl->assign(" . $_attr['assign'] . "," . $output . ");?>";
        }

        // Case: direct output, with possible filters
        if (empty($_attr['nofilter'])) {
            // Apply default modifiers
            if (!empty($compiler->smarty->default_modifiers)) {
                if (empty($compiler->default_modifier_list)) {
                    $modifierlist = [];
                    foreach ($compiler->smarty->default_modifiers as $key => $single_default_modifier) {
                        preg_match_all(
                            '/(\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"|:|[^:]+)/',
                            $single_default_modifier,
                            $mod_array
                        );
                        for ($i = 0, $count = count($mod_array[0]); $i < $count; $i++) {
                            if ($mod_array[0][$i] !== ':') {
                                $modifierlist[$key][] = $mod_array[0][$i];
                            }
                        }
                    }
                    $compiler->default_modifier_list = $modifierlist;
                }

                $output = $compiler->compileTag(
                    'private_modifier',
                    [],
                    [
                        'modifierlist' => $compiler->default_modifier_list,
                        'value' => $output,
                    ]
                );
            }

            // Autoescape HTML if enabled
            if ($compiler->template->smarty->escape_html) {
                $output = "htmlspecialchars((string) (" . $output . "), ENT_QUOTES, '" . addslashes(Smarty::$_CHARSET) . "')";
            }

            // Apply registered variable filters
            if (!empty($compiler->template->smarty->registered_filters[Smarty::FILTER_VARIABLE])) {
                foreach ($compiler->template->smarty->registered_filters[Smarty::FILTER_VARIABLE] as $key => $function) {
                    if (!is_array($function)) {
                        $output = $function . "(" . $output . ",\$_smarty_tpl)";
                    } elseif (is_object($function[0])) {
                        $output = "\$_smarty_tpl->smarty->registered_filters[Smarty::FILTER_VARIABLE]['" . $key . "'][0]->" . $function[1] . "(" . $output . ",\$_smarty_tpl)";
                    } else {
                        $output = $function[0] . "::" . $function[1] . "(" . $output . ",\$_smarty_tpl)";
                    }
                }
            }

            // Apply autoload filters (throws if not found)
            if (!empty($compiler->smarty->autoload_filters[Smarty::FILTER_VARIABLE])) {
                foreach ((array)$compiler->template->smarty->autoload_filters[Smarty::FILTER_VARIABLE] as $name) {
                    $output = $this->compile_variable_filter($compiler, $name, $output, true);
                }
            }

            // Apply template-level variable filters
            foreach ($compiler->variable_filters as $filter) {
                if (count($filter) === 1 &&
                    ($result = $this->compile_variable_filter($compiler, $filter[0], $output)) !== false
                ) {
                    $output = $result;
                } else {
                    $output = $compiler->compileTag(
                        'private_modifier',
                        [],
                        ['modifierlist' => [$filter], 'value' => $output]
                    );
                }
            }
        }

        // Final output
        $output = "<?php echo " . $output . ";?>\n";
        return $output;
    }

    /**
     * @param \Smarty_Internal_TemplateCompilerBase $compiler compiler object
     * @param string                                $name     name of variable filter
     * @param string                                $output   embedded output
     * @param bool                                  $throwOnMissing If true, throws when filter not found
     *
     * @return string|false
     * @throws \SmartyException
     */
    private function compile_variable_filter(Smarty_Internal_TemplateCompilerBase $compiler, string $name, string $output, bool $throwOnMissing = false)
    {
        $function = $compiler->getPlugin($name, 'variablefilter');

        if ($function) {
            return $function . "(" . $output . ",\$_smarty_tpl)";
        }

        if ($throwOnMissing) {
            throw new SmartyException("Unable to load variable filter '" . $name . "'");
        }

        return false;
    }
}
