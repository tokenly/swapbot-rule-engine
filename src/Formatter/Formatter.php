<?php

namespace Tokenly\SwapbotRuleEngine\Formatter;

use PhpParser\PrettyPrinter\Standard;



class Formatter {

    public function format($stmts) {
        $prettyPrinter = new Standard();
        return $prettyPrinter->prettyPrintFile($stmts);
    }


}

