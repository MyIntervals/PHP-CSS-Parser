<?php

define('OS_WIN32', defined('OS_WINDOWS') ? OS_WINDOWS : !strncasecmp(PHP_OS, 'win', 3));

class CSSUrlUtils {

  /**
   * Requests the contents of an URL
   *
   * @param   string $sURL the URL to fetch
   * @returns array        an array in the form:
   *   'charset'  => the charset of the response as specified by the
   *                 HTTP Content-Type header, if specified
   *   'response' => the response body
   **/
  static public function loadURL($sURL) {
    $rCurl = curl_init();
    curl_setopt($rCurl, CURLOPT_URL, $sURL);
    //curl_setopt($rCurl, CURLOPT_HEADER, true);
    curl_setopt($rCurl, CURLOPT_ENCODING, 'deflate,gzip');
    curl_setopt($rCurl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($rCurl, CURLOPT_USERAGENT, 'PHP-CSS-Parser v0.1');
    curl_setopt($rCurl, CURLOPT_RETURNTRANSFER, true);
    $mResponse = curl_exec($rCurl);
    $aInfos = curl_getinfo($rCurl);
    curl_close($rCurl);
    if(false === $mResponse) return false;
    $aResult = array(
      'charset' => null,
      'response' => $mResponse  
    );
    if($aInfos['content_type']) {
      if(preg_match('/charset=([a-zA-Z0-9-]*)/', $aInfos['content_type'], $aMatches)) {
        $aResult['charset'] = $aMatches[0];
      }
    }
    return $aResult;
  }

  /**
   * CSSUrlUtils::joinPaths( string $head, string $tail [, string $...] )
   *
   * @param   string $head the head component of the path
   * @param   string $tail at least one path component
   * @returns string       the resulting path
   **/
  static public function joinPaths() {
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
   * @param   string  $path Given path
   * @return  boolean       True if the path is absolute, false if it is not
   */
  static public function isAbsPath($sPath) {
    if (preg_match('#(?:/|\\\)\.\.(?=/|$)#', $sPath)) {
      return false;
    }
    if (OS_WIN32) {
      return (($sPath[0] == '/') ||  preg_match('#^[a-zA-Z]:(\\\|/)#', $sPath));
    }
    return ($sPath[0] == '/') || ($sPath[0] == '~');
  }

  /**
   * Tests if an URL is absolute
   *
   * @param  string  $sURL
   * @return boolean
   **/
  static public function isAbsURL($sURL) {
    return preg_match('#^(http|https|ftp)://#', $sURL);
  }

  /**
   * Returns the parent path of an URL or path
   * 
   * @param   string $sURL an URL
   * @returns string       an URL
   **/
  static public function dirname($sURL) {
    $aURL = parse_url($sURL);
    if(isset($aURL['path'])) {
      $sPath = dirname($aURL['path']);
      if($sPath == '/') {
        unset($aURL['path']);
      } else {
        $aURL['path'] = $sPath;
      }
    }
    return self::buildURL($aURL);
  }
  
  /**
   * Builds an URL from an array of URL parts
   *
   * @param  array  $aURL   URL parts in the format returned by parse_url
   * @return string         the builded URL
   * @see http://php.net/manual/function.parse-url.php 
   **/
  static public function buildURL(array $aURL)
  {
    $sURL = '';
    if(isset($aURL['scheme'])) {
      $sURL .= $aURL['scheme'] . '://';
    }
    if(isset($aURL['user'])) {
      $sURL .= $aURL['user'];
      if(isset($aURL['pass'])) {
        $sURL .= ':' . $aURL['pass'];
      }
      $sURL .= '@';
    }
    if(isset($aURL['host'])) {
      $sURL .= $aURL['host'];
    }
    if(isset($aURL['port'])) {
      $sURL .= ':' . $aURL['port'];
    }
    if(isset($aURL['path'])) {
      if(strpos($aURL['path'], '/') !== 0) $sURL .= '/';
      $sURL .= $aURL['path'];
    }
    if(isset($aURL['query'])) {
      $sURL .= '?' . $aURL['query'];
    }
    if(isset($aURL['fragment'])) {
      $sURL .= '#' . $aURL['fragment'];
    }
    return $sURL;
  }
}
