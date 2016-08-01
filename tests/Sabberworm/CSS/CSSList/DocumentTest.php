<?php

namespace Sabberworm\CSS\CSSList;

use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\Parser;

class DocumentTest extends \PHPUnit_Framework_TestCase {

	public function testOverrideContents() {
		$sCss = '.thing { left: 10px; }';
		$oParser = new Parser($sCss);
		$oDoc = $oParser->parse();
		$aContents = $oDoc->getContents();
		$this->assertCount(1, $aContents);

		$sCss2 = '.otherthing { right: 10px; }';
		$oParser2 = new Parser($sCss);
		$oDoc2 = $oParser2->parse();
		$aContents2 = $oDoc2->getContents();

		$oDoc->setContents(array($aContents[0], $aContents2[0]));
		$aFinalContents = $oDoc->getContents();
		$this->assertCount(2, $aFinalContents);
	}

	public function testInsertContent() {
		$sCss = '.thing { left: 10px; } .stuff { margin: 1px; } ';
		$oParser = new Parser($sCss);
		$oDoc = $oParser->parse();
		$aContents = $oDoc->getContents();
		$this->assertCount(2, $aContents);

		$oThing = $aContents[0];
		$oStuff = $aContents[1];

		$oFirst = new DeclarationBlock();
		$oFirst->setSelectors('.first');
		$oBetween = new DeclarationBlock();
		$oBetween->setSelectors('.between');
		$oOrphan = new DeclarationBlock();
		$oOrphan->setSelectors('.forever-alone');
		$oNotFound = new DeclarationBlock();
		$oNotFound->setSelectors('.not-found');

		$oDoc->insert($oFirst, $oThing);
		$oDoc->insert($oBetween, $oStuff);
		$oDoc->insert($oOrphan, $oNotFound);

		$aContents = $oDoc->getContents();
		$this->assertCount(5, $aContents);
		$this->assertSame($oFirst, $aContents[0]);
		$this->assertSame($oThing, $aContents[1]);
		$this->assertSame($oBetween, $aContents[2]);
		$this->assertSame($oStuff, $aContents[3]);
		$this->assertSame($oOrphan, $aContents[4]);
	}

}
