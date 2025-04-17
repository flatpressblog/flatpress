<?php
/**
 * Smarty Internal Plugin Templateparser Parsetree
 * These are classes to build parsetree in the template parser
 *
 * @package    Smarty
 * @subpackage Compiler
 * @author     Thue Kristensen
 * @author     Uwe Tews
 */

/**
 * @package    Smarty
 * @subpackage Compiler
 * @ignore
 */
abstract class Smarty_Internal_ParseTree
{
    /**
     * Buffer content
     *
     * @var mixed
     */
    public $data;

    /**
     * Subtree array
     *
     * @var array|null
     */
    public $subtrees = array();

    /**
     * Return buffer
     *
     * @param \Smarty_Internal_Templateparser $parser
     *
     * @return string buffer content
     */
    abstract public function to_smarty_php(Smarty_Internal_Templateparser $parser);

    /**
     * Template data object destructor
     */
    public function __destruct()
    {
        $this->data = null;
        $this->subtrees = null;
    }

    /**
     * Append a subtree to this parse tree node.
     *
     * @param \Smarty_Internal_Templateparser $parser
     * @param \Smarty_Internal_ParseTree|null $subtree
     *
     * @return static
     */
    public function append_subtree(Smarty_Internal_Templateparser $parser, ?Smarty_Internal_ParseTree $subtree = null)
    {
        if ($subtree === null) {
            return $this;
        }

        // Default behavior: return self or merge logic here
        return $this;
    }
}
