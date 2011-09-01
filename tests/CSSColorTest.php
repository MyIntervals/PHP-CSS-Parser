<?php
require_once __DIR__.'/../CSSParser.php';

class CSSColorTest extends PHPUnit_Framework_TestCase
{
  /**
   * @dataProvider fromRGBProvider
   **/
  public function testFromRGB($aRGB, $aExpectedColor, $aExpectedDescription)
	{
		$oColor = new CSSColor();
		$oColor->fromRGB($aRGB);
    $this->assertEquals($oColor->getColor(), $aExpectedColor);
    $this->assertEquals($oColor->getColorDescription(), $aExpectedDescription);
  }
  public function fromRGBProvider()
  {
    return array(
			array(
				array('r'=>0, 'g'=>255, 'b'=>255),
				array(
					'r'=> new CSSSize(0, null, true),
					'g'=> new CSSSize(255, null, true),
					'b'=> new CSSSize(255, null, true)
				),
				'rgb'
			),
			array(
				array('r'=>'100%', 'g'=>-5, 'b'=>303),
				array(
					'r'=> new CSSSize(255, null, true),
					'g'=> new CSSSize(0, null, true),
					'b'=> new CSSSize(255, null, true),
				),
				'rgb'
			),
			array(
				array('r'=>0, 'g'=>0, 'b'=>0, 'a'=>2),
				array(
					'r'=> new CSSSize(0, null, true),
					'g'=> new CSSSize(0, null, true),
					'b'=> new CSSSize(0, null, true)
				),
				'rgb'
			),
		);
  }

  /**
   * @dataProvider fromHSLProvider
   **/
  public function testFromHSL($aHSL, $aExpectedColor, $aExpectedDescription)
	{
		$oColor = new CSSColor();
		$oColor->fromHSL($aHSL);
    $this->assertEquals($oColor->getColor(), $aExpectedColor);
    $this->assertEquals($oColor->getColorDescription(), $aExpectedDescription);
  }
  public function fromHSLProvider()
  {
    return array(
			array(
				array('h'=>60, 's'=>'100%', 'l'=>'50%'),
				array(
					'r'=> new CSSSize(255, null, true),
					'g'=> new CSSSize(255, null, true),
					'b'=> new CSSSize(0, null, true)
				),
				'rgb'
			),
			array(
				array('h'=>540, 's'=>'120%', 'l'=>'50%'),
				array(
					'r'=> new CSSSize(0, null, true),
					'g'=> new CSSSize(255, null, true),
					'b'=> new CSSSize(255, null, true)
				),
				'rgb'
			),
			array(
				array('h'=>480, 's'=>'120%', 'l'=>'-50%', 'a'=>0.3),
				array(
					'r'=> new CSSSize(0, null, true),
					'g'=> new CSSSize(0, null, true),
					'b'=> new CSSSize(0, null, true),
					'a'=> new CSSSize(0.3, null, true)
				),
				'rgba'
			),
		);
  }

  /**
   * @dataProvider toHSLProvider
   **/
  public function testToHSL($oColor, $aExpectedColor)
	{
		$aOriginalColor = $oColor->getColor();
		$oColor->toHSL();
    $this->assertEquals($oColor->getColor(), $aExpectedColor);
		$oColor->toRGB();
		$this->assertEquals(
			$oColor->getColor(), $aOriginalColor, 
			'Failed to convert color back to RGB'
		);
  }
  public function toHSLProvider()
  {
    return array(
			array(
				new CSSColor('blue'),
				array(
					'h'=> new CSSSize(240, null, true),
					's'=> new CSSSize(100, '%', true),
					'l'=> new CSSSize(50, '%', true)
				)
			),
			array(
				new CSSColor(array('r'=>255, 'g'=>0, 'b'=>0)),
				array(
					'h'=> new CSSSize(0, null, true),
					's'=> new CSSSize(100, '%', true),
					'l'=> new CSSSize(50, '%', true)
				)
			),
			array(
				new CSSColor('transparent'),
				array(
					'h'=> new CSSSize(0, null, true),
					's'=> new CSSSize(0, '%', true),
					'l'=> new CSSSize(0, '%', true),
					'a'=> new CSSSize(0, null, true)
				)
			),
		);
  }
}
