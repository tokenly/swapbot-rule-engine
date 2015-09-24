<?php

use PhpParser\NodeDumper;
use Tokenly\SwapbotRuleEngine\Formatter\Formatter;
use Tokenly\SwapbotRuleEngine\Parser\Parser;
use Tokenly\SwapbotRuleEngine\Validator\Validator;
use \PHPUnit_Framework_Assert as PHPUnit;

/*
* 
*/
class FormatterTest extends TestCase
{


    public function testFormatter() {
        // ------------------------------------------------------------------------------------------
        $this->format($_p=<<<'EOT'

$price = $quantityIn / 50;


return $price;

EOT
, $_f=<<<'EOT'
<?php

$price = $quantityIn / 50;
return $price;
EOT
);

        // ------------------------------------------------------------------------------------------
        $this->format($_p=<<<'EOT'

$a = $b;      $c = $d; $price = 5;

     return $price;

EOT
, $_f=<<<'EOT'
<?php

$a = $b;
$c = $d;
$price = 5;
return $price;
EOT
);


    }



    protected function format($raw_php_string, $expected_formatted_php_string) {
        // parse
        $parser = new Parser();
        $stmts = $parser->parsePHPString($raw_php_string);

        // validate
        $validator = new Validator();
        $is_valid = $validator->validate($stmts);
        PHPUnit::assertTrue($is_valid, "Found unexpected error: ".implode(", ", $validator->getErrors()));


        // format
        $formatter = new Formatter();
        $actual_formatted_php_string = $formatter->format($stmts);

        PHPUnit::assertEquals($expected_formatted_php_string, $actual_formatted_php_string);
    }

}
