<?php
require_once __DIR__.'/../../CSSParser.php';
/**
 * Test case for GH26
 * https://github.com/sabberworm/PHP-CSS-Parser/issues/26
 */
class GH26_Test extends PHPUnit_Framework_TestCase
{
  /**
   * @dataProvider testMSFiltersProvider
   **/
  public function testMSFilters($sCss, $sExpected)
  {
    $oParser = new CSSParser($sCss);
    $oDoc = $oParser->parse();
    $this->assertEquals((string)$oDoc, $sExpected);
  }
  public function testMSFiltersProvider()
  {
    return array(
      array(
        "div{ filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#ededed'); }",
        'div {filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#ffffff",endColorstr="#ededed");}',
      ),
      array(
        'div{ -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=60)"; }',
        'div {-ms-filter: progid:DXImageTransform.Microsoft.Alpha(Opacity=60);}',
      ) 
    );
  }
}
