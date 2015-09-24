<?php

use PhpParser\NodeDumper;
use Tokenly\SwapbotRuleEngine\Executor\Executor;
use Tokenly\SwapbotRuleEngine\Parser\Parser;
use Tokenly\SwapbotRuleEngine\Validator\Validator;
use \PHPUnit_Framework_Assert as PHPUnit;

/*
* 
*/
class ExecutorTest extends TestCase
{


    public function testExecutor() {
        $test_vars = [
            'quantityIn' => 100,
            'assetIn'    => 'TOKENLY',
        ];

        // ------------------------------------------------------------------------------------------
        $this->execute($_p=<<<'EOT'

$price = $quantityIn / 50;
return $price;

EOT
, $test_vars, 2.0);


        // ------------------------------------------------------------------------------------------
        $this->execute($_p=<<<'EOT'

$test = 'MYASSET';
return $test;

EOT
, $test_vars, 'MYASSET', 'string');


    }



    protected function execute($php_string, $test_vars, $expected_result, $type='float') {
        // parse
        $parser = new Parser();
        $stmts = $parser->parsePHPString($php_string);

        // validate
        $validator = new Validator();
        $is_valid = $validator->validate($stmts);
        PHPUnit::assertTrue($is_valid, "Found unexpected error: ".implode(", ", $validator->getErrors()));

        // execute
        $executor = new Executor();
        $actual_result = $executor->execute($php_string, $test_vars, $type);

        PHPUnit::assertEquals($expected_result, $actual_result);
    }

}
