<?php
require_once __DIR__.'/../CSSParser.php';
/**
 * 
 */
class CSSDeclarationBlockTest extends PHPUnit_Framework_TestCase
{
  /**
   * @dataProvider testGetAppliedRuleProvider
   **/
  public function testGetAppliedRule($sCss, $sExpected) {
    $oParser = new CSSParser($sCss);
    $oDoc = $oParser->parse();
    foreach($oDoc->getAllDeclarationBlocks() as $oDeclaration) {
      $oRule = $oDeclaration->getAppliedRule('border-width');
      $this->assertEquals($sExpected, (string)$oRule);
    }
  }
  public function testGetAppliedRuleProvider() {
    return array(
      array(
        'p{border-width: 1px; border-width: 2px;}',
        'border-width: 2px;'
      ),  
      array(
        'p{border-width: 3px; border-width: 2px !important;}',
        'border-width: 2px !important;'
      ),
      array(
        'p{border-width: 2px !important; border-width: 3px;}',
        'border-width: 2px !important;'
      ),
      array(
        'p{border-width: 1px !important; border-width: 2px !important;}',
        'border-width: 2px !important;'
      )
    );
  }

  /**
   * @dataProvider expandBorderShorthandProvider
   **/
  public function testExpandBorderShorthand($sCss, $sExpected)
  {
    $oParser = new CSSParser($sCss);
    $oDoc = $oParser->parse();
    foreach($oDoc->getAllDeclarationBlocks() as $oDeclaration)
    {
      $oDeclaration->expandBorderShorthand();
    }
    $this->assertEquals($sExpected, (string)$oDoc);
  }
  public function expandBorderShorthandProvider()
  {
    return array(
      array('body{ border: 2px solid rgb(0,0,0) }', 'body {border-width: 2px;border-style: solid;border-color: rgb(0,0,0);}'),
      array('body{ border: none }', 'body {border-style: none;}'),
      array('body{ border: 2px }', 'body {border-width: 2px;}'),
      array('body{ border: rgb(255,0,0) }', 'body {border-color: rgb(255,0,0);}'),
      array('body{ border: 1em solid }', 'body {border-width: 1em;border-style: solid;}'),
      array('body{ margin: 1em; }', 'body {margin: 1em;}'),
      array(
        'p { border: 1px solid rgb(0,0,0); border-right: none; }',
        'p {border-width: 1px;border-style: solid;border-color: rgb(0,0,0);border-right-style: none;}'
      ),
      // Test order & importance
      array(
        'p {border: 2px dotted rgb(0,0,255) !important;}',
        'p {border-width: 2px !important;border-style: dotted !important;border-color: rgb(0,0,255) !important;}'
      ),
      array(
        'p {border: 2px dotted rgb(0,0,255) !important;border-style: solid;}',
        'p {border-width: 2px !important;border-style: dotted !important;border-color: rgb(0,0,255) !important;border-style: solid;}'
      ),
      array(
        'p {border: 2px dotted rgb(0,0,255);border-style: solid;}',
        'p {border-width: 2px;border-color: rgb(0,0,255);border-style: solid;}'
      ),
      array(
        'p {border: 2px dotted rgb(0,0,255);border-style: solid !important;}',
        'p {border-width: 2px;border-color: rgb(0,0,255);border-style: solid !important;}'
      )
    );
  }

  /**
   * @dataProvider expandFontShorthandProvider
   **/
  public function testExpandFontShorthand($sCss, $sExpected)
  {
    $oParser = new CSSParser($sCss);
    $oDoc = $oParser->parse();
    foreach($oDoc->getAllDeclarationBlocks() as $oDeclaration)
    {
      $oDeclaration->expandFontShorthand();
    }
    $this->assertEquals($sExpected, (string)$oDoc);
  }
  public function expandFontShorthandProvider()
  {
    return array(
      array(
        'body{ margin: 1em; }',
        'body {margin: 1em;}'
      ),
      array(
        'body {font: 12px serif;}',
        'body {font-style: normal;font-variant: normal;font-weight: normal;font-size: 12px;line-height: normal;font-family: serif;}'
      ),
      array(
        'body {font: italic 12px serif;}',
        'body {font-style: italic;font-variant: normal;font-weight: normal;font-size: 12px;line-height: normal;font-family: serif;}'
      ),
      array(
        'body {font: italic bold 12px serif;}',
        'body {font-style: italic;font-variant: normal;font-weight: bold;font-size: 12px;line-height: normal;font-family: serif;}'
      ),
      array(
        'body {font: italic bold 12px/1.6 serif;}',
        'body {font-style: italic;font-variant: normal;font-weight: bold;font-size: 12px;line-height: 1.6;font-family: serif;}'
      ),
      array(
        'body {font: italic small-caps bold 12px/1.6 serif;}',
        'body {font-style: italic;font-variant: small-caps;font-weight: bold;font-size: 12px;line-height: 1.6;font-family: serif;}'
      ),
    );
  }

  /**
   * @dataProvider expandBackgroundShorthandProvider
   **/
  public function testExpandBackgroundShorthand($sCss, $sExpected)
  {
    $oParser = new CSSParser($sCss);
    $oDoc = $oParser->parse();
    foreach($oDoc->getAllDeclarationBlocks() as $oDeclaration)
    {
      $oDeclaration->expandBackgroundShorthand();
    }
    $this->assertEquals($sExpected, (string)$oDoc);
  }
  public function expandBackgroundShorthandProvider()
  {
    return array(
      array('body {border: 1px;}', 'body {border: 1px;}'),
      array('body {background: rgb(255,0,0);}','body {background-color: rgb(255,0,0);background-image: none;background-repeat: repeat;background-attachment: scroll;background-position: 0% 0%;}'),
      array('body {background: rgb(255,0,0) url("foobar.png");}','body {background-color: rgb(255,0,0);background-image: url("foobar.png");background-repeat: repeat;background-attachment: scroll;background-position: 0% 0%;}'),
      array('body {background: rgb(255,0,0) url("foobar.png") no-repeat;}','body {background-color: rgb(255,0,0);background-image: url("foobar.png");background-repeat: no-repeat;background-attachment: scroll;background-position: 0% 0%;}'),
      array('body {background: rgb(255,0,0) url("foobar.png") no-repeat center;}','body {background-color: rgb(255,0,0);background-image: url("foobar.png");background-repeat: no-repeat;background-attachment: scroll;background-position: center center;}'),
      array('body {background: rgb(255,0,0) url("foobar.png") no-repeat top left;}','body {background-color: rgb(255,0,0);background-image: url("foobar.png");background-repeat: no-repeat;background-attachment: scroll;background-position: top left;}'),
      // support for functions in background-image
      array('body {background: linear-gradient(#f00,#00f);}','body {background-color: transparent;background-image: linear-gradient(rgb(255,0,0),rgb(0,0,255));background-repeat: repeat;background-attachment: scroll;background-position: 0% 0%;}')
    );
  }

  /**
   * @dataProvider expandDimensionsShorthandProvider
   **/
  public function testExpandDimensionsShorthand($sCss, $sExpected)
  {
    $oParser = new CSSParser($sCss);
    $oDoc = $oParser->parse();
    foreach($oDoc->getAllDeclarationBlocks() as $oDeclaration)
    {
      $oDeclaration->expandDimensionsShorthand();
    }
    $this->assertEquals($sExpected, (string)$oDoc);
  }
  public function expandDimensionsShorthandProvider()
  {
    return array(
      array('body {border: 1px;}', 'body {border: 1px;}'),
      array('body {margin-top: 1px;}', 'body {margin-top: 1px;}'),
      array('body {margin: 1em;}','body {margin-top: 1em;margin-right: 1em;margin-bottom: 1em;margin-left: 1em;}'), 
      array('body {margin: 1em 2em;}','body {margin-top: 1em;margin-right: 2em;margin-bottom: 1em;margin-left: 2em;}'), 
      array('body {margin: 1em 2em 3em;}','body {margin-top: 1em;margin-right: 2em;margin-bottom: 3em;margin-left: 2em;}'), 
    );
  }

	/**
	 * @dataProvider testExpandShorthandsProvider
	 * @depends testExpandBorderShorthand
	 * @depends testExpandBackgroundShorthand
	 * @depends testExpandDimensionsShorthand
	 * @depends testExpandFontShorthand
	 **/
	public function testExpandShorthands($sCSS, $sExpected) {
		$oParser = new CSSParser($sCSS);
		$oDoc = $oParser->parse();
		$oDoc->expandShorthands();
		$this->assertEquals($sExpected, (string)$oDoc);
	}
	public function testExpandShorthandsProvider() {
		return array(
			array(
				'p {border-right: none;border: 1px solid rgb(0,0,0);}',
				'p {border-right-style: none;border-top-width: 1px;border-right-width: 1px;border-bottom-width: 1px;border-left-width: 1px;border-top-style: solid;border-right-style: solid;border-bottom-style: solid;border-left-style: solid;border-top-color: rgb(0,0,0);border-right-color: rgb(0,0,0);border-bottom-color: rgb(0,0,0);border-left-color: rgb(0,0,0);}'
			),	
			array(
				'p { border: 1px solid rgb(0,0,0); border-right: none; }',
				'p {border-top-width: 1px;border-right-width: 1px;border-bottom-width: 1px;border-left-width: 1px;border-top-style: solid;border-bottom-style: solid;border-left-style: solid;border-top-color: rgb(0,0,0);border-right-color: rgb(0,0,0);border-bottom-color: rgb(0,0,0);border-left-color: rgb(0,0,0);border-right-style: none;}'
      )
		);
	}

  /**
   * @dataProvider createBorderShorthandProvider
   **/
  public function testCreateBorderShorthand($sCss, $sExpected)
  {
    $oParser = new CSSParser($sCss);
    $oDoc = $oParser->parse();
    foreach($oDoc->getAllDeclarationBlocks() as $oDeclaration)
    {
      $oDeclaration->createBorderShorthand();
    }
    $this->assertEquals($sExpected, (string)$oDoc);
  }
  public function createBorderShorthandProvider()
  {
    return array(
      array('body {border-width: 2px;border-style: solid;border-color: rgb(0,0,0);}', 'body {border: 2px solid rgb(0,0,0);}'),
      array('body {border-style: none;}', 'body {border: none;}'),
      array('body {border-width: 1em;border-style: solid;}', 'body {border: 1em solid;}'),
      array('body {margin: 1em;}', 'body {margin: 1em;}'),
      // Test order & importance  
      array(
        'p {border: 2px dotted rgb(0,0,255); border-style: solid}',
        'p {border: 2px solid rgb(0,0,255);}'
      ),
      array(
        'p {border: 2px dotted rgb(0,0,255) !important; border-style: solid}',
        'p {border: 2px dotted rgb(0,0,255) !important;}'
      ),
      array(
        'p {border-style: solid !important;border-width: 2px !important;border-color: rgb(0,0,255) !important;}',
        'p {border: 2px solid rgb(0,0,255) !important;}'
      ),
      // If the importance is not equal, no merging should happen
      array(
        'p {border-style: solid;border-width: 2px;border-color: rgb(0,0,255) !important;}',
        'p {border-color: rgb(0,0,255) !important;border: 2px solid;}'
      ),
      array(
        'p {border: 2px dotted rgb(0,0,255); border-style: solid !important;}',
        'p {border-style: solid !important;border: 2px rgb(0,0,255);}'
      ),
    );
  }

  /**
   * @dataProvider createFontShorthandProvider
   **/
  public function testCreateFontShorthand($sCss, $sExpected)
  {
    $oParser = new CSSParser($sCss);
    $oDoc = $oParser->parse();
    foreach($oDoc->getAllDeclarationBlocks() as $oDeclaration)
    {
      $oDeclaration->createFontShorthand();
    }
    $this->assertEquals($sExpected, (string)$oDoc);
  }
  public function createFontShorthandProvider()
  {
    return array(
      array('body {font-size: 12px; font-family: serif}', 'body {font: 12px serif;}'),
      array('body {font-size: 12px; font-family: serif; font-style: italic;}', 'body {font: italic 12px serif;}'),
      array('body {font-size: 12px; font-family: serif; font-style: italic; font-weight: bold;}', 'body {font: italic bold 12px serif;}'),
      array('body {font-size: 12px; font-family: serif; font-style: italic; font-weight: bold; line-height: 1.6;}', 'body {font: italic bold 12px/1.6 serif;}'),
      array('body {font-size: 12px; font-family: serif; font-style: italic; font-weight: bold; line-height: 1.6; font-variant: small-caps;}', 'body {font: italic small-caps bold 12px/1.6 serif;}'),
      array('body {margin: 1em;}', 'body {margin: 1em;}')
    );
  }

  /**
   * @dataProvider createDimensionsShorthandProvider
   **/
  public function testCreateDimensionsShorthand($sCss, $sExpected)
  {
    $oParser = new CSSParser($sCss);
    $oDoc = $oParser->parse();
    foreach($oDoc->getAllDeclarationBlocks() as $oDeclaration)
    {
      $oDeclaration->createDimensionsShorthand();
    }
    $this->assertEquals($sExpected, (string)$oDoc);
  }
  public function createDimensionsShorthandProvider()
  {
    return array(
      array('body {border: 1px;}', 'body {border: 1px;}'),
      array('body {margin-top: 1px;}', 'body {margin-top: 1px;}'),
      array('body {margin-top: 1em; margin-right: 1em; margin-bottom: 1em; margin-left: 1em;}','body {margin: 1em;}'), 
      array('body {margin-top: 1em; margin-right: 2em; margin-bottom: 1em; margin-left: 2em;}','body {margin: 1em 2em;}'), 
      array('body {margin-top: 1em; margin-right: 2em; margin-bottom: 3em; margin-left: 2em;}','body {margin: 1em 2em 3em;}'), 
    );
  }

  /**
   * @dataProvider createBackgroundShorthandProvider
   **/
  public function testCreateBackgroundShorthand($sCss, $sExpected)
  {
    $oParser = new CSSParser($sCss);
    $oDoc = $oParser->parse();
    foreach($oDoc->getAllDeclarationBlocks() as $oDeclaration)
    {
      $oDeclaration->createBackgroundShorthand();
    }
    $this->assertEquals($sExpected, (string)$oDoc);
  }
  public function createBackgroundShorthandProvider()
  {
    return array(
      array('body {border: 1px;}', 'body {border: 1px;}'),
      array('body {background-color: rgb(255,0,0);}', 'body {background: rgb(255,0,0);}'),
      array('body {background-color: rgb(255,0,0);background-image: url(foobar.png);}', 'body {background: rgb(255,0,0) url("foobar.png");}'),
      array('body {background-color: rgb(255,0,0);background-image: url(foobar.png);background-repeat: no-repeat;}', 'body {background: rgb(255,0,0) url("foobar.png") no-repeat;}'),
      array('body {background-color: rgb(255,0,0);background-image: url(foobar.png);background-repeat: no-repeat;}', 'body {background: rgb(255,0,0) url("foobar.png") no-repeat;}'),
      array('body {background-color: rgb(255,0,0);background-image: url(foobar.png);background-repeat: no-repeat;background-position: center;}', 'body {background: rgb(255,0,0) url("foobar.png") no-repeat center;}'),
      array('body {background-color: rgb(255,0,0);background-image: url(foobar.png);background-repeat: no-repeat;background-position: top left;}', 'body {background: rgb(255,0,0) url("foobar.png") no-repeat top left;}'),
    );
  }

  /**
	 * @dataProvider testCreateShorthandsProvider
	 * @depends testCreateBorderShorthand
	 * @depends testCreateBackgroundShorthand
	 * @depends testCreateDimensionsShorthand
	 * @depends testCreateFontShorthand
	 **/
	public function testCreateShorthands($sCSS, $sExpected) {
    $this->markTestSkipped("Fix expandShorthands first !");
		$oParser = new CSSParser($sCSS);
		$oDoc = $oParser->parse();
		$oDoc->createShorthands();
		$this->assertEquals($sExpected, (string)$oDoc);
	}
	public function testCreateShorthandsProvider() {
		return array(
      // createShorthands should preserve multiple values
      array('p{
  background-color: #f00;
  background:-webkit-foo(bar);
  background-color: #0f0;
  background:-moz-bar(baz);
  background-color: #00f;
  background-image: -webkit-bar(baz);
}',
        'p {background-color: rgb(255,0,0);background: -webkit-foo(bar);backround-color: rgb(0,255,0);background: -moz-bar(baz);background-color: rgb(0,0,255);background-image: -webkit-bar(baz);}'
      ),
      array('p{
  background:-webkit-foo(bar);
  background-color: #0f0;
  background-image: -moz-foo(bar);
}',
        ''
      ),
      array('p{border-color: #00f;border: solid;}', 'p {border: solid;}'),  
      array('p{border-color: #00f;border: solid;border-width:2px;}', '')  
		);
	}
}
