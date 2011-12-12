<?php
/**
 * 
 */
class CSSCharsetUtils
{
  static $CHARSET_DETECTION_MAP = array(
    array(
      'pattern' => '#^\xEF\xBB\xBF\x40\x63\x68\x61\x72\x73\x65\x74\x20\x22([\x20-\x7F]*)\x22\x3B#',
      'charset' => null,
      'endianness' => null
    ),
    array(
      'pattern' => '#^\xEF\xBB\xBF#',
      'charset' => "UTF-8",
      'endianness' => null
    ),
    array(
      'pattern' => '#^\x40\x63\x68\x61\x72\x73\x65\x74\x20\x22([\x20-\x7F]*)\x22\x3B#',
      'charset' => null,
      'endianness' => null 
    ),
    array(
      'pattern' => '#^\xFE\xFF\x00\x40\x00\x63\x00\x68\x00\x61\x00\x72\x00\x73\x00\x65\x00\x74\x00\x20\x00\x22((?:\x00[\x20-\x7F])*)\x00\x22\x00\x3B#',
      'charset' => null,
      'endianness' => 'BE'
    ),
    array(
      'pattern' => '#^\x00\x40\x00\x63\x00\x68\x00\x61\x00\x72\x00\x73\x00\x65\x00\x74\x00\x20\x00\x22((?:\x00[\x20-\x7F])*)\x00\x22\x00\x3B#',
      'charset' => null,
      'endianness' => 'BE' 
    ),
    array(
      'pattern' => '#^\xFF\xFE\x40\x00\x63\x00\x68\x00\x61\x00\x72\x00\x73\x00\x65\x00\x74\x00\x20\x00\x22\x00((?:\x00[\x20-\x7F])*)\x22\x00\x3B\x00#',
      'charset' => null,
      'endianness' => 'BE' 
    ),
    array(
      'pattern' => '#^\x40\x00\x63\x00\x68\x00\x61\x00\x72\x00\x73\x00\x65\x00\x74\x00\x20\x00\x22\x00((?:\x00[\x20-\x7F])*)\x22\x00\x3B\x00#',
      'charset' => null,
      'endianness' => 'LE' 
    ),
    array(
      'pattern' => '#^\x00\x00\xFE\xFF\x00\x00\x00\x40\x00\x00\x00\x63\x00\x00\x00\x68\x00\x00\x00\x61\x00\x00\x00\x72\x00\x00\x00\x73\x00\x00\x00\x65\x00\x00\x00\x74\x00\x00\x00\x20\x00\x00\x00\x22((?:\x00\x00\x00[\x20-\x7F])*)\x00\x00\x00\x22\x00\x00\x00\x3B#',
      'charset' => null,
      'endianness' => 'BE'
    ),
    array(
      'pattern' => '#^\x00\x00\x00\x40\x00\x00\x00\x63\x00\x00\x00\x68\x00\x00\x00\x61\x00\x00\x00\x72\x00\x00\x00\x73\x00\x00\x00\x65\x00\x00\x00\x74\x00\x00\x00\x20\x00\x00\x00\x22((?:\x00\x00\x00[\x20-\x7F])*)\x00\x00\x00\x22\x00\x00\x00\x3B#',
      'charset' => null,
      'endianness' => 'BE' 
    ),
    array(
      'pattern' => '#^\x00\x00\xFF\xFE\x00\x00\x40\x00\x00\x00\x63\x00\x00\x00\x68\x00\x00\x00\x61\x00\x00\x00\x72\x00\x00\x00\x73\x00\x00\x00\x65\x00\x00\x00\x74\x00\x00\x00\x20\x00\x00\x00\x22\x00((?:\x00\x00[\x20-\x7F]\x00)*)\x00\x00\x22\x00\x00\x00\x3B\x00#',
      'charset' => null,
      'endianness' => '2143'
    ),
    array(
      'pattern' => '#^\x00\x00\x40\x00\x00\x00\x63\x00\x00\x00\x68\x00\x00\x00\x61\x00\x00\x00\x72\x00\x00\x00\x73\x00\x00\x00\x65\x00\x00\x00\x74\x00\x00\x00\x20\x00\x00\x00\x22\x00((?:\x00\x00[\x20-\x7F]\x00)*)\x00\x00\x22\x00\x00\x00\x3B\x00#',
      'charset' => null,
      'endianness' => '2143' 
    ),
    array(
      'pattern' => '#^\xFE\xFF\x00\x00\x00\x40\x00\x00\x00\x63\x00\x00\x00\x68\x00\x00\x00\x61\x00\x00\x00\x72\x00\x00\x00\x73\x00\x00\x00\x65\x00\x00\x00\x74\x00\x00\x00\x20\x00\x00\x00\x22\x00\x00((?:\x00[\x20-\x7F]\x00\x00)*)\x00\x22\x00\x00\x00\x3B\x00\x00#',
      'charset' => null,
      'endianness' => '3412'
    ),
    array(
      'pattern' => '#^\x00\x40\x00\x00\x00\x63\x00\x00\x00\x68\x00\x00\x00\x61\x00\x00\x00\x72\x00\x00\x00\x73\x00\x00\x00\x65\x00\x00\x00\x74\x00\x00\x00\x20\x00\x00\x00\x22\x00\x00((?:\x00[\x20-\x7F]\x00\x00)*)\x00\x22\x00\x00\x00\x3B\x00\x00#',
      'charset' => null,
      'endianness' => '3412' 
    ),
    array(
      'pattern' => '#^\xFF\xFE\x00\x00\x40\x00\x00\x00\x63\x00\x00\x00\x68\x00\x00\x00\x61\x00\x00\x00\x72\x00\x00\x00\x73\x00\x00\x00\x65\x00\x00\x00\x74\x00\x00\x00\x20\x00\x00\x00\x22\x00\x00\x00((?:[\x20-\x7F]\x00\x00\x00)*)\x22\x00\x00\x00\x3B\x00\x00\x00#',
      'charset' => null,
      'endianness' => 'LE'
    ),
    array(
      'pattern' => '#^\x40\x00\x00\x00\x63\x00\x00\x00\x68\x00\x00\x00\x61\x00\x00\x00\x72\x00\x00\x00\x73\x00\x00\x00\x65\x00\x00\x00\x74\x00\x00\x00\x20\x00\x00\x00\x22\x00\x00\x00((?:[\x20-\x7F]\x00\x00\x00)*)\x22\x00\x00\x00\x3B\x00\x00\x00#',
      'charset' => null,
      'endianness' => 'LE' 
    ),
    array(
      'pattern' => '#^\x00\x00\xFE\xFF#',
      'charset' => 'UTF-32BE',
      'endianness' => null
    ),
    array(
      'pattern' => '#^\xFF\xFE\x00\x00#',
      'charset' => 'UTF-32LE',
      'endianness' => null
    ),
    array(
      'pattern' => '#^\x00\x00\xFF\xFE#',
      'charset' => 'UTF-32-2143',
      'endianness' => null
    ),
    array(
      'pattern' => '#^\xFE\xFF\x00\x00#',
      'charset' => 'UTF-32-3412',
      'endianness' => null
    ),
    array(
      'pattern' => '#^\xFE\xFF#',
      'charset' => "UTF-16BE",
      'endianness' => null
    ),
    array(
      'pattern' => '#^\xFF\xFE#',
      'charset' => 'UTF-16LE',
      'endianness' => null
    ),
    /**
     * These encodings are not supported by mbstring extension.
     **/
    //array(
      //'pattern' => '/^\x7C\x83\x88\x81\x99\xA2\x85\xA3\x40\x7F(YY)*\x7F\x5E/',
      //'charset' => null,
      //'endianness' => null,
      //'transcoded-from' => 'EBCDIC'
    //),
    //array(
      //'pattern' => '/^\xAE\x83\x88\x81\x99\xA2\x85\xA3\x40\xFC(YY)*\xFC\x5E/',
      //'charset' => null,
      //'endianness' => null, 
      //'transcoded-from' => 'IBM1026'
    //),
    //array(
      //'pattern' => '/^\x00\x63\x68\x61\x72\x73\x65\x74\x20\x22(YY)*\x22\x3B/',
      //'charset' => null,
      //'endianness' => null 
      //'transcoded-from' => 'GSM 03.38'
    //),
  );

  static function detectCharset($sText) {
    $aSupportedEncodings = mb_list_encodings();
    foreach (self::$CHARSET_DETECTION_MAP as $aCharsetMap) {
      $sPattern = $aCharsetMap['pattern'];
      $aMatches = array();
      if(preg_match($sPattern, $sText, $aMatches)) {
        if($aCharsetMap['charset']) {
          $sCharset = $aCharsetMap['charset'];
        } else {
          $sCharset = $aMatches[1];
        }
        return $sCharset;
      }
    }
    return false;
  }

  static function convert($sSubject, $sFromCharset, $sToCharset) {
    return mb_convert_encoding($sSubject, $sToCharset, $sFromCharset);
    //return iconv($sFromCharset, $sToCharset, $sSubject);
  }

  static function removeBOM($sText) {
    $iLen = strlen($sText);
    if($iLen > 3) {
      switch ($sText[0]) {
        case "\xEF":
          if(("\xBB" == $sText[1]) && ("\xBF" == $sText[2])) {
            // EF BB BF  UTF-) encoded BOM
            return substr($sText, 3);
          }
          break;
        case "\xFE":
          if (("\xFF" == $sText[1]) && ("\x00" == $sText[2]) && ("\x00" == $sText[3])) {
            // FE FF 00 00  UCS-4, unusual octet order BOM (3412)
            return substr($sText, 4);
          } else if ("\xFF" == $sText[1]) {
             // FE FF  UTF-16, big endian BOM
            return substr($sText, 2);
          }
          break;
        case "\x00":
          if (("\x00" == $sText[1]) && ("\xFE" == $sText[2]) && ("\xFF" == $sText[3])) {
            // 00 00 FE FF  UTF-32, big-endian BOM
            return substr($sText, 4);
          } else if (("\x00" == $sText[1]) && ("\xFF" == $sText[2]) && ("\xFE" == $sText[3])) {
            // 00 00 FF FE  UCS-4, unusual octet order BOM (2143)
            return substr($sText, 4);
          }
          break;
        case "\xFF":
          if (("\xFE" == $sText[1]) && ("\x00" == $sText[2]) && ("\x00" == $sText[3])) {
            // FF FE 00 00  UTF-32, little-endian BOM
            return substr($sText, 4);
          } else if ("\xFE" == $sText[1]) {
            // FF FE  UTF-16, little endian BOM
            return substr($sText, 2);
          }
          break;
      }
    }
    return $sText;
  }
  
  static function checkForBOM($sText) {
    $iLen = strlen($sText);
    if($iLen > 3) {
      switch ($sText[0]) {
        case "\xEF":
          if(("\xBB" == $sText[1]) && ("\xBF" == $sText[2])) {
            // EF BB BF  UTF-) encoded BOM
            return 'UTF-8';
          }
          break;
        case "\xFE":
          if (("\xFF" == $sText[1]) && ("\x00" == $sText[2]) && ("\x00" == $sText[3])) {
            // FE FF 00 00  UCS-4, unusual octet order BOM (3412)
            return "X-ISO-10646-UCS-4-3412";
          } else if ("\xFF" == $sText[1]) {
             // FE FF  UTF-16, big endian BOM
            return "UTF-16BE";
          }
          break;
        case "\x00":
          if (("\x00" == $sText[1]) && ("\xFE" == $sText[2]) && ("\xFF" == $sText[3])) {
            // 00 00 FE FF  UTF-32, big-endian BOM
            return "UTF-32BE";
          } else if (("\x00" == $sText[1]) && ("\xFF" == $sText[2]) && ("\xFE" == $sText[3])) {
            // 00 00 FF FE  UCS-4, unusual octet order BOM (2143)
            return "X-ISO-10646-UCS-4-2143";
          }
          break;
        case "\xFF":
          if (("\xFE" == $sText[1]) && ("\x00" == $sText[2]) && ("\x00" == $sText[3])) {
            // FF FE 00 00  UTF-32, little-endian BOM
            return "UTF-32LE";
          } else if ("\xFE" == $sText[1]) {
            // FF FE  UTF-16, little endian BOM
            return "UTF-16LE";
          }
          break;
      }
    }
    return false;
  }

  static function printBytes($sString, $iLen=null)
  {
    if($iLen == null) $iLen = strlen($sString);
    $aBytes = array();
    for($i = 0; $i < $iLen; $i++) {
      $aBytes[] = "0x".dechex(ord($sString[$i]));
    }
    return implode(' ', $aBytes);
  }

}
