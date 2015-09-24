<?php

namespace Tokenly\SwapbotRuleEngine\Executor;



class Executor {

    public function executeFloat($__php_string, $__vars) {
        return $this->execute($__php_string, $__vars, 'float');
    }

    public function execute($__php_string, $__vars, $return_type='float') {
        $sandbox_result = $this->runSandbox($__php_string, $__vars);
        settype($sandbox_result, $return_type);
        return $sandbox_result;
    }

    public function runSandbox($__php_string, $__vars) {
        try {

            extract($__vars);
            return eval($__php_string);

        } catch (Exception $e) {
            throw $e;
        }
        
    }


}

