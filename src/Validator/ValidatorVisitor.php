<?php

namespace Tokenly\SwapbotRuleEngine\Validator;

use PhpParser\Error;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;



class ValidatorVisitor extends NodeVisitorAbstract {

    protected $MAX_ALLOWED_NODE_COUNT = 1000;

    protected $allowed_node_types_map = null;
    protected $allowed_function_calls_map = null;
    protected $disallowed_variables_map = null;

    protected $validation_complete = false;

    public function __construct() {
        $this->errors = [];
        $this->node_count = 0;
        $this->has_return = false;

        $this->initAllowedNodeTypesMap();
        $this->initAllowedFunctionCallsMap();
        $this->initDisallowedVariablesMap();
    }

    public function isValid() {
        $this->completeValidationIfNeeded();

        if ($this->errors) { return false; }
        return true;
    }

    public function getErrors() {
        $this->completeValidationIfNeeded();

        return $this->errors;
    }
    public function getNodeCount() {
        return $this->node_count;
    }

    // public function beforeTraverse(array $nodes)    { }
    // public function enterNode(Node $node) { }
    // public function leaveNode(Node $node) { }
    // public function afterTraverse(array $nodes)     { }

    public function enterNode(Node $node) {
        ++$this->node_count;

        $type = $node->getType();
        if ($type == 'Stmt_Return') { $this->has_return = true; }

        if (!isset($this->allowed_node_types_map[$type])) {
            $this->errors[] = "Illegal instruction of type $type found.";
        }

        // check function calls
        if ($type == 'Expr_FuncCall') {
            $this->validateFunctionCall($node);
        }

        // check variables
        if ($type == 'Expr_Variable') {
            $this->validateVariable($node);
        }
    }


    // ------------------------------------------------------------------------

    protected function validateFunctionCall($node) {
        $function_name = $node->name->toString();
        if (!isset($this->allowed_function_calls_map[$function_name])) {
            $this->errors[] = "Illegal call to function $function_name";
        }
    }
    
    protected function validateVariable($node) {
        $variable_name = $node->name;
        if (isset($this->disallowed_variables_map[$variable_name]) OR substr($variable_name, 0, 2) == '__') {
            $this->errors[] = "Illegal variable name $variable_name";
        }
    }
    

    protected function completeValidationIfNeeded() {
        if (!$this->validation_complete) {
            $this->completeValidation();
            $this->validation_complete = true;
        }
    }

    protected function completeValidation() {
        if ($this->node_count > $this->MAX_ALLOWED_NODE_COUNT) {
            $this->errors[] = "Too many instructions were found.";
        }

        if (!$this->has_return) {
            $this->errors[] = "No return statement found.";
        }

    }

    protected function initAllowedNodeTypesMap() {
        $this->allowed_node_types_map = array_fill_keys($this->getAllowedNodeTypes(), true);
    }
    protected function initAllowedFunctionCallsMap() {
        $this->allowed_function_calls_map = array_fill_keys($this->getAllowedFunctionCalls(), true);
    }
    protected function initDisallowedVariablesMap() {
        $this->disallowed_variables_map = array_fill_keys($this->getDisallowedVariableNames(), true);
    }

    protected function getAllowedNodeTypes() {
        return [
            'Expr_Assign',

            // 'Expr_ArrayDimFetch',
            'Expr_BinaryOp_Div',
            'Expr_BinaryOp_Equal',
            'Expr_BinaryOp_Greater',
            'Expr_BinaryOp_GreaterOrEqual',
            // 'Expr_BinaryOp_Identical',
            'Expr_BinaryOp_LogicalAnd',
            'Expr_BinaryOp_LogicalOr',
            'Expr_BinaryOp_LogicalXor',
            'Expr_BinaryOp_Minus',
            'Expr_BinaryOp_Mod',
            'Expr_BinaryOp_Mul',
            'Expr_BinaryOp_NotEqual',
            // 'Expr_BinaryOp_NotIdentical',
            'Expr_BinaryOp_Plus',
            'Expr_BinaryOp_Smaller',
            'Expr_BinaryOp_SmallerOrEqual',

            'Expr_FuncCall',
            'Expr_Variable',
            'Scalar_DNumber',
            'Scalar_LNumber',
            'Scalar_String',

            'Stmt_If',
            'Stmt_Else',
            'Expr_BinaryOp_Greater',
            'Stmt_Return',
            'Name',
            'Arg',
        ];
    }
    protected function getAllowedFunctionCalls() {
        return [
            'round',
        ];
    }
    protected function getDisallowedVariableNames() {
        return [
            'GLOBALS',
            '_SERVER',
            '_GET',
            '_POST',
            '_FILES',
            '_COOKIE',
            '_SESSION',
            '_REQUEST',
            '_ENV',
        ];
    }

}

