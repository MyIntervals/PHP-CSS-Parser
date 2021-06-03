<?php

namespace Sabberworm\CSS\CSSList;

use Sabberworm\CSS\Parser;

class AtRuleBlockListTest extends \PHPUnit\Framework\TestCase
{

    public function testMediaQueries()
    {
        $sCss = '@media(min-width: 768px){.class{color:red}}';
        $oParser = new Parser($sCss);
        $oDoc = $oParser->parse();
        $aContents = $oDoc->getContents();
        $oMediaQuery = $aContents[0];
        $this->assertSame('media', $oMediaQuery->atRuleName(), 'Does not interpret the type as a function');
        $this->assertSame('(min-width: 768px)', $oMediaQuery->atRuleArgs(), 'The media query is the value');

        $sCss = '@media (min-width: 768px) {.class{color:red}}';
        $oParser = new Parser($sCss);
        $oDoc = $oParser->parse();
        $aContents = $oDoc->getContents();
        $oMediaQuery = $aContents[0];
        $this->assertSame('media', $oMediaQuery->atRuleName(), 'Does not interpret the type as a function');
        $this->assertSame('(min-width: 768px)', $oMediaQuery->atRuleArgs(), 'The media query is the value');
    }
}
