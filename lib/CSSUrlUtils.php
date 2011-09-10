<?php

define('OS_WIN32', defined('OS_WINDOWS') ? OS_WINDOWS : !strncasecmp(PHP_OS, 'win', 3));

class CSSUrlUtils {

  static function loadURL($sURL) {
    $rCurl = curl_init();
    curl_setopt($rCurl, CURLOPT_URL, $sURL);
    //curl_setopt($rCurl, CURLOPT_HEADER, true);
    curl_setopt($rCurl, CURLOPT_ENCODING, 'deflate,gzip');
    curl_setopt($rCurl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($rCurl, CURLOPT_USERAGENT, 'PHP-CSS-Parser v0.1');
    curl_setopt($rCurl, CURLOPT_RETURNTRANSFER, true);
    $sResult = curl_exec($rCurl);
    curl_close($rCurl);
    return $sResult;
  }

  /**
   * CSSUrlUtils::joinPaths( string $head, string $tail [, string $...] )
   *
   * @param $head string the head component of the path
   * @param $tail string at least one path component
   * @returns string the resulting path
   **/
  static function joinPaths()
  {
    $num_args = func_num_args();
    if($num_args < 1) return '';
    $args = func_get_args();
    if($num_args == 1) return rtrim($args[0], DIRECTORY_SEPARATOR);

    $head = array_shift($args);
    $head = rtrim($head, DIRECTORY_SEPARATOR);
    $output = array($head);
    foreach ($args as $arg) {
      $output[] = trim($arg, DIRECTORY_SEPARATOR);
    }
    return implode(DIRECTORY_SEPARATOR, $output);
  }

  /**
   * Returns boolean based on whether given path is absolute or not.
   *
   * @static
   * @access  public
   * @param   string  $path Given path
   * @return  boolean True if the path is absolute, false if it is not
   */
  static function isAbsPath($sPath) {
    if (preg_match('#(?:/|\\\)\.\.(?=/|$)#', $sPath)) {
      return false;
    }
    if (OS_WIN32) {
      return (($sPath[0] == '/') ||  preg_match('#^[a-zA-Z]:(\\\|/)#', $sPath));
    }
    return ($sPath[0] == '/') || ($sPath[0] == '~');
  }

  static function isAbsURL($sPath)
  {
    return preg_match('#^(http|https|ftp)://#', $sPath);
  }
}
