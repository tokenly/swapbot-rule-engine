<?php

use PhpParser\NodeDumper;
use Tokenly\SwapbotRuleEngine\Parser\Parser;
use Tokenly\SwapbotRuleEngine\Validator\Validator;
use \PHPUnit_Framework_Assert as PHPUnit;

/*
* 
*/
class ParserTest extends TestCase
{


    public function testParsePHP() {
        $parser = new Parser();

        $php_string = <<<'EOT'

$a = 5;
$c = $a + 2;
return $c;

EOT;

        $stmts = $parser->parsePHPString($php_string);
        PHPUnit::assertNotEmpty($stmts);
    }

    public function testValidation() {
        // ------------------------------------------------------------------------------------------
        $this->validate($_p=<<<'EOT'
$a = 5;
$c = $a + 2;
$d = $a - 3;
$e = $a / 4;
$f = $a * 5;
$g = $a % 6;
if ($h > 100) {
    return 10;
} else if ($h > 50) {
    return 18;
} else {
    return $c;
}
EOT
, null);


        // ------------------------------------------------------------------------------------------
        $this->validate($_p=<<<'EOT'
$a = 5;
EOT
, 'No return');


        // ------------------------------------------------------------------------------------------
        $this->validate($_p=<<<'EOT'
$GLOBALS = 'foo';
return 1;
EOT
, 'Illegal variable name GLOBALS');

        // ------------------------------------------------------------------------------------------
        $this->validate($_p=<<<'EOT'
$__foo = 'foo';
return 1;
EOT
, 'Illegal variable name __foo');

        // ------------------------------------------------------------------------------------------
        $this->validate($_p=<<<'EOT'
$_POST = 'foo';
return 1;
EOT
, 'Illegal variable name _POST');


        // ------------------------------------------------------------------------------------------
        $this->validate(str_repeat('$a=1;', 1000).'return 1;', 'Too many instructions');


        // ------------------------------------------------------------------------------------------
        $this->validate($_p=<<<'EOT'
return rand(1,100);
EOT
, 'Illegal call to function rand');

        // ------------------------------------------------------------------------------------------
        $this->validate($_p=<<<'EOT'
return $a['foo'];
EOT
, 'Illegal instruction of type Expr_ArrayDimFetch');

        // ------------------------------------------------------------------------------------------
        $this->validate($_p=<<<'EOT'
// comment
$a = 5.1;
return round($a);
EOT
, null);


        // ------------------------------------------------------------------------------------------


    }


    protected function debugDump($php_string) {
        $parser = new Parser();
        $stmts = $parser->parsePHPString($php_string);

        $nodeDumper = new NodeDumper();
        print "\n".$nodeDumper->dump($stmts)."\n";
    }

    protected function validate($php_string, $expected_error) {
        $parser = new Parser();
        $stmts = $parser->parsePHPString($php_string);

        $validator = new Validator();
        $is_valid = $validator->validate($stmts);
        if ($expected_error === null) {
            PHPUnit::assertTrue($is_valid, "Found unexpected error: ".implode(", ", $validator->getErrors()));
            return;
        }


        // make sure error matches
        PHPUnit::assertFalse($is_valid, "Validator was valid, but expected error ".$expected_error);
        PHPUnit::assertContains($expected_error, implode(" | ", $validator->getErrors()), "Expected error not found in validator errors.");
    }

}
