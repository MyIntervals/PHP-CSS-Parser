<?php
require_once __DIR__.'/../../CSSParser.php';
/**
 * Test case for GH27
 * https://github.com/sabberworm/PHP-CSS-Parser/issues/27
 */
class GH27_Test extends PHPUnit_Framework_TestCase
{
  /**
   * @dataProvider testNamespacesProvider
   **/
  public function testNamespaces($sCss, $sExpected)
  {
    $oParser = new CSSParser($sCss);
    $oDoc = $oParser->parse();
    $this->assertEquals((string)$oDoc, $sExpected);
  }
  public function testNamespacesProvider()
  {
    return array(
      array(
        '@namespace "http://www.w3.org/1999/xhtml";',
        '@namespace url("http://www.w3.org/1999/xhtml");',
      ),
      array(
        '@namespace svg "http://www.w3.org/2000/svg";',
        '@namespace svg url("http://www.w3.org/2000/svg");',
      )
    );
  }
}
