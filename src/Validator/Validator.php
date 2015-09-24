<?php

namespace Tokenly\SwapbotRuleEngine\Validator;

use PhpParser\Error;
use PhpParser\NodeTraverser;
use Tokenly\SwapbotRuleEngine\Validator\ValidatorVisitor;
use Exception;


class Validator {

    protected $validator_vistor = null;
    protected $parse_error = null;


    public function __construct() {

    }

    public function validate($stmts) {
        // traverse the nodes
        $traverser = new NodeTraverser();
        $traverser->addVisitor($this->initValidatorVisitor());
        try {
            $stmts = $traverser->traverse($stmts);
        } catch (Error $e) {
            $this->parse_error = $e->getMessage();
            return false;
        }

        return $this->validator_vistor->isValid();
    }

    public function getErrors() {
        if ($this->validator_vistor === null) { throw new Exception("Must call validate first", 1); }
        if ($this->parse_error !== null) {
            return $this->parse_error;
        }
        return $this->validator_vistor->getErrors();
    }

    public function getNodeCount() {
        if ($this->validator_vistor === null) { throw new Exception("Must call validate first", 1); }
        return $this->validator_vistor->getNodeCount();
    }

    protected function initValidatorVisitor() {
        $this->validator_vistor = new ValidatorVisitor();
        return $this->validator_vistor;
    }
}

