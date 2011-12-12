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

  /**
   * @dataProvider testResolveImportsProvider
   **/
  public function testResolveImports($sFile, $sExpected)
  {
    $oParser = new CSSParser(array('resolve_imports' => true));
    $oDoc = $oParser->parseFile($sFile);
    $this->assertEquals((string)$oDoc, $sExpected);
  }
  public function testResolveImportsProvider()
  {
    return array(
      array(
        dirname(__FILE__)."/files/import.css",
        '@font-face {font-family: "CrassRoots";src: url("/home/ju1ius/code/php/third-party/PHP-CSS-Parser/tests/files/../media/cr.ttf");}html, body {font-size: 1.6em;}header {width: 618px;height: 120px;}.euc-jp {content: "もっと強く";}body.im_utf-32 {color: green;}div.im_utf-16 {background: url("/home/ju1ius/code/php/third-party/PHP-CSS-Parser/tests/files/import/barfoo.png");}body#icomelast {color: fuschia;padding: 5px;background-image: url("http://foobar.com");}'
      )
    );
  }
  
}
