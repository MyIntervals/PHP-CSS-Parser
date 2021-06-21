<?php

namespace Sabberworm\CSS\Tests\CSSList;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Parser;

class DocumentTest extends TestCase
{
    public function testOverrideContents()
    {
        $sCss = '.thing { left: 10px; }';
        $oParser = new Parser($sCss);
        $oDoc = $oParser->parse();
        $aContents = $oDoc->getContents();
        $this->assertCount(1, $aContents);

        $sCss2 = '.otherthing { right: 10px; }';
        $oParser2 = new Parser($sCss);
        $oDoc2 = $oParser2->parse();
        $aContents2 = $oDoc2->getContents();

        $oDoc->setContents([$aContents[0], $aContents2[0]]);
        $aFinalContents = $oDoc->getContents();
        $this->assertCount(2, $aFinalContents);
    }
}
