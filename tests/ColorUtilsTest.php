<?php
require_once __DIR__.'/../lib/ColorUtils.php';

class ColorUtilsTest extends PHPUnit_Framework_TestCase
{
  /**
   * @dataProvider normalizeFractionProvider
   **/
  public function testNormalizeFraction($mValue, $fExcpected)
  {
    $fResult = ColorUtils::normalizeFraction($mValue);
    $this->assertEquals($fResult, $fExcpected);
  }
  public function normalizeFractionProvider()
  {
    return array(
      array(150,     1),
      array('150%',  1),
      array('50%',   0.5),
      array(50,      0.5),
      array(-150,    0),
      array('-150%', 0),
    );
  }

  /**
   * @dataProvider normalizeRGBValueProvider
   **/
  public function testNormalizeRGBValue($mValue, $fExcpected)
  {
    $fResult = ColorUtils::normalizeRGBValue($mValue);
    $this->assertEquals($fResult, $fExcpected);
  }
  public function normalizeRGBValueProvider()
  {
    return array(
      array(150,     150),
      array('150%',  255),
      array('50%',   128),
      array(-150,    0),
      array('-150%', 0),
    );
  }

  /**
   * @dataProvider hex2rgbProvider
   **/
  public function testHex2rgb($sHexValue, $aExpected)
  {
    $aRGB = ColorUtils::hex2rgb($sHexValue);
    $this->assertSame($aRGB, $aExpected);
  }
  public function hex2rgbProvider()
  {
    return array(
      array('#ff0000', array('r'=>255, 'g'=>0,   'b'=>0)),
      array('00ff00',  array('r'=>0,   'g'=>255, 'b'=>0)),
      array('#00f',    array('r'=>0,   'g'=>0,   'b'=>255)),
      array('BADA55',  array('r'=>186, 'g'=>218, 'b'=>85)),
      array('#FAIL',    false),
      // TODO: how do we handle that ?
      array('FOOBAR',  array('r'=>0, 'g'=>15, 'b'=>186)),
    );
  }

  /**
   * @dataProvider rgb2hexProvider
   * @depends testNormalizeRGBValue
   **/
  public function testRgb2hex($r, $g, $b, $sExpected)
  {
    $sHexValue = ColorUtils::rgb2hex($r, $g, $b);
    $this->assertSame($sHexValue, $sExpected);
  }
  public function rgb2hexProvider()
  {
    return array(
      array(255, 0,   0,      '#ff0000'),
      array(0,   0,   255,    '#0000ff'),
      array(186, 218, 85,     '#bada55'),
      array(302, -3,  'fail', '#ff0000'),
    );
  }

  /**
   * @dataProvider hsl2rgbProvider
   * @depends testNormalizeFraction
   **/
  public function testHsl2rgb($h, $s, $l, $aExpected)
  {
    $aRGB = ColorUtils::hsl2rgb($h, $s, $l, $aExpected);
    // assertEquals because hsl2rgb returns an array of floats,
    // even if they are rounded
    $this->assertEquals($aRGB, $aExpected);
  }
  public function hsl2rgbProvider()
  {
    return array(
      array(60,  '100%', '50%',  array('r'=>255, 'g'=>255, 'b'=>0)),
      array(60,  '100%', '25%',  array('r'=>128, 'g'=>128, 'b'=>0)),
      array(480, '120%', '-50%', array('r'=>0,   'g'=>0,   'b'=>0)),
      array(540, '120%', '50%',  array('r'=>0,   'g'=>255, 'b'=>255)),
    );
  }

  /**
   * @dataProvider rgb2hslProvider
   * @depends testNormalizeRGBValue
   **/
  public function testRgb2hsl($r, $g, $b, $aExpected)
  {
    $aHSL = ColorUtils::rgb2hsl($r, $g, $b, $aExpected);
    // assertEquals because hsl2rgb returns an array of floats,
    // even if they are rounded
    $this->assertEquals($aHSL, $aExpected);
  }
  public function rgb2hslProvider()
  {
    return array(
      array(0,      0,      255, array('h'=>240, 's'=>'100%', 'l'=>'50%')),
      array(0,      255,    255, array('h'=>180, 's'=>'100%', 'l'=>'50%')),
      array('100%', 255,    0,   array('h'=>60,  's'=>'100%', 'l'=>'50%')),
      array(382,    '150%', -50, array('h'=>60,  's'=>'100%', 'l'=>'50%')),
    );
  }

}
