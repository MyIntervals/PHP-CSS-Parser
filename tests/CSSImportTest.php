<?php
require_once dirname(__FILE__).'/../CSSParser.php';

class CSSImportTest extends PHPUnit_Framework_TestCase
{

  /**
   * @dataProvider testAbsoluteUrlsProvider
   **/
  public function testAbsoluteUrls($sCSS, $sBaseUrl, $sExpected)
  {
    $parser = new CSSParser(array(
      'absolute_urls' => true,
      'base_url' => $sBaseUrl
    ));
    $oDoc = $parser->parseString($sCSS);
    $this->assertEquals($oDoc->__toString(), $sExpected);
  }
  public function testAbsoluteUrlsProvider()
  {
    return array(
      array(
        '@import "styles.css";body{background: url("image.jpg")}',
        'http://foobar.biz',
        '@import url("http://foobar.biz/styles.css");body {background: url("http://foobar.biz/image.jpg");}'  
      ),  
      array(
        '@import "sub/styles.css";body{background: url("/root/image.jpg")}',
        'http://foobar.biz',
        '@import url("http://foobar.biz/sub/styles.css");body {background: url("http://foobar.biz/root/image.jpg");}'  
      ),  
      array(
        '@import "sub/styles.css";body{background: url("/root/image.jpg")}',
        '/home/user/www',
        '@import url("/home/user/www/sub/styles.css");body {background: url("/root/image.jpg");}'  
      ),  
    );
  }
  
}
