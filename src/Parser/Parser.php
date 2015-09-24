<?php

namespace Tokenly\SwapbotRuleEngine\Parser;

use PhpParser\Error;
use PhpParser\ParserFactory;



class Parser {

    public function parsePHPString($php_string) {
        $parser = $this->buildParser();

        try {
            $stmts = $parser->parse('<?php '.$php_string);

            // $stmts is an array of statement nodes
            return $stmts;
        } catch (Error $e) {
            echo 'Parse Error: ', $e->getMessage();
            throw $e;
        }
        
    }


    protected function buildParser() {
        // use ParserFactory::ONLY_PHP5.  In the future we may want ParserFactory::PREFER_PHP7
        $parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP5);
        return $parser;

    }
}

