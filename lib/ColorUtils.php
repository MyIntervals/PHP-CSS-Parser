<?php

class ColorUtils {

  static $X11_COLORS_MAP = array(
    'aliceblue'            =>	array('r' => 240, 'g' => 248, 'b' => 255),
    'antiquewhite'         =>	array('r' => 250, 'g' => 235, 'b' => 215),
    'aqua'                 =>	array('r' => 0,   'g' => 255, 'b' => 255),
    'aquamarine'           =>	array('r' => 127, 'g' => 255, 'b' => 212),
    'azure'                =>	array('r' => 240, 'g' => 255, 'b' => 255),
    'beige'                =>	array('r' => 245, 'g' => 245, 'b' => 220),
    'bisque'               =>	array('r' => 255, 'g' => 228, 'b' => 196),
    'black'                =>	array('r' => 0,   'g' => 0,   'b' => 0),
    'blanchedalmond'       =>	array('r' => 255, 'g' => 235, 'b' => 205),
    'blue'                 =>	array('r' => 0,   'g' => 0,   'b' => 255),
    'blueviolet'           =>	array('r' => 138, 'g' => 43,  'b' => 226),
    'brown'                =>	array('r' => 165, 'g' => 42,  'b' => 42),
    'burlywood'            =>	array('r' => 222, 'g' => 184, 'b' => 135),
    'cadetblue'            =>	array('r' => 95,  'g' => 158, 'b' => 160),
    'chartreuse'           =>	array('r' => 127, 'g' => 255, 'b' => 0),
    'chocolate'            =>	array('r' => 210, 'g' => 105, 'b' => 30),
    'coral'                =>	array('r' => 255, 'g' => 127, 'b' => 80),
    'cornflowerblue'       =>	array('r' => 100, 'g' => 149, 'b' => 237),
    'cornsilk'             =>	array('r' => 255, 'g' => 248, 'b' => 220),
    'crimson'              =>	array('r' => 220, 'g' => 20,  'b' => 60),
    'cyan'                 =>	array('r' => 0,   'g' => 255, 'b' => 255),
    'darkblue'             =>	array('r' => 0,   'g' => 0,   'b' => 139),
    'darkcyan'             =>	array('r' => 0,   'g' => 139, 'b' => 139),
    'darkgoldenrod'        =>	array('r' => 184, 'g' => 134, 'b' => 11),
    'darkgray'             =>	array('r' => 169, 'g' => 169, 'b' => 169),
    'darkgreen'            =>	array('r' => 0,   'g' => 100, 'b' => 0),
    'darkgrey'             =>	array('r' => 169, 'g' => 169, 'b' => 169),
    'darkkhaki'            =>	array('r' => 189, 'g' => 183, 'b' => 107),
    'darkmagenta'          =>	array('r' => 139, 'g' => 0,   'b' => 139),
    'darkolivegreen'       =>	array('r' => 85,  'g' => 107, 'b' => 47),
    'darkorange'           =>	array('r' => 255, 'g' => 140, 'b' => 0),
    'darkorchid'           =>	array('r' => 153, 'g' => 50,  'b' => 204),
    'darkred'              =>	array('r' => 139, 'g' => 0,   'b' => 0),
    'darksalmon'           =>	array('r' => 233, 'g' => 150, 'b' => 122),
    'darkseagreen'         =>	array('r' => 143, 'g' => 188, 'b' => 143),
    'darkslateblue'        =>	array('r' => 72,  'g' => 61,  'b' => 139),
    'darkslategray'        =>	array('r' => 47,  'g' => 79,  'b' => 79),
    'darkslategrey'        =>	array('r' => 47,  'g' => 79,  'b' => 79),
    'darkturquoise'        =>	array('r' => 0,   'g' => 206, 'b' => 209),
    'darkviolet'           =>	array('r' => 148, 'g' => 0,   'b' => 211),
    'deeppink'             =>	array('r' => 255, 'g' => 20,  'b' => 147),
    'deepskyblue'          =>	array('r' => 0,   'g' => 191, 'b' => 255),
    'dimgray'              =>	array('r' => 105, 'g' => 105, 'b' => 105),
    'dimgrey'              =>	array('r' => 105, 'g' => 105, 'b' => 105),
    'dodgerblue'           =>	array('r' => 30,  'g' => 144, 'b' => 255),
    'firebrick'            =>	array('r' => 178, 'g' => 34,  'b' => 34),
    'floralwhite'          =>	array('r' => 255, 'g' => 250, 'b' => 240),
    'forestgreen'          =>	array('r' => 34,  'g' => 139, 'b' => 34),
    'fuchsia'              =>	array('r' => 255, 'g' => 0,   'b' => 255),
    'gainsboro'            =>	array('r' => 220, 'g' => 220, 'b' => 220),
    'ghostwhite'           =>	array('r' => 248, 'g' => 248, 'b' => 255),
    'gold'                 =>	array('r' => 255, 'g' => 215, 'b' => 0),
    'goldenrod'            =>	array('r' => 218, 'g' => 165, 'b' => 32),
    'gray'                 =>	array('r' => 128, 'g' => 128, 'b' => 128),
    'green'                =>	array('r' => 0,   'g' => 128, 'b' => 0),
    'greenyellow'          =>	array('r' => 173, 'g' => 255, 'b' => 47),
    'grey'                 =>	array('r' => 128, 'g' => 128, 'b' => 128),
    'honeydew'             =>	array('r' => 240, 'g' => 255, 'b' => 240),
    'hotpink'              =>	array('r' => 255, 'g' => 105, 'b' => 180),
    'indianred'            =>	array('r' => 205, 'g' => 92,  'b' => 92),
    'indigo'               =>	array('r' => 75,  'g' => 0,   'b' => 130),
    'ivory'                =>	array('r' => 255, 'g' => 255, 'b' => 240),
    'khaki'                =>	array('r' => 240, 'g' => 230, 'b' => 140),
    'lavender'             =>	array('r' => 230, 'g' => 230, 'b' => 250),
    'lavenderblush'        =>	array('r' => 255, 'g' => 240, 'b' => 245),
    'lawngreen'            =>	array('r' => 124, 'g' => 252, 'b' => 0),
    'lemonchiffon'         =>	array('r' => 255, 'g' => 250, 'b' => 205),
    'lightblue'            =>	array('r' => 173, 'g' => 216, 'b' => 230),
    'lightcoral'           =>	array('r' => 240, 'g' => 128, 'b' => 128),
    'lightcyan'            =>	array('r' => 224, 'g' => 255, 'b' => 255),
    'lightgoldenrodyellow' =>	array('r' => 250, 'g' => 250, 'b' => 210),
    'lightgray'            =>	array('r' => 211, 'g' => 211, 'b' => 211),
    'lightgreen'           =>	array('r' => 144, 'g' => 238, 'b' => 144),
    'lightgrey'            =>	array('r' => 211, 'g' => 211, 'b' => 211),
    'lightpink'            =>	array('r' => 255, 'g' => 182, 'b' => 193),
    'lightsalmon'          =>	array('r' => 255, 'g' => 160, 'b' => 122),
    'lightseagreen'        =>	array('r' => 32,  'g' => 178, 'b' => 170),
    'lightskyblue'         =>	array('r' => 135, 'g' => 206, 'b' => 250),
    'lightslategray'       =>	array('r' => 119, 'g' => 136, 'b' => 153),
    'lightslategrey'       =>	array('r' => 119, 'g' => 136, 'b' => 153),
    'lightsteelblue'       =>	array('r' => 176, 'g' => 196, 'b' => 222),
    'lightyellow'          =>	array('r' => 255, 'g' => 255, 'b' => 224),
    'lime'                 =>	array('r' => 0,   'g' => 255, 'b' => 0),
    'limegreen'            =>	array('r' => 50,  'g' => 205, 'b' => 50),
    'linen'                =>	array('r' => 250, 'g' => 240, 'b' => 230),
    'magenta'              =>	array('r' => 255, 'g' => 0,   'b' => 255),
    'maroon'               =>	array('r' => 128, 'g' => 0,   'b' => 0),
    'mediumaquamarine'     =>	array('r' => 102, 'g' => 205, 'b' => 170),
    'mediumblue'           =>	array('r' => 0,   'g' => 0,   'b' => 205),
    'mediumorchid'         =>	array('r' => 186, 'g' => 85,  'b' => 211),
    'mediumpurple'         =>	array('r' => 147, 'g' => 112, 'b' => 219),
    'mediumseagreen'       =>	array('r' => 60,  'g' => 179, 'b' => 113),
    'mediumslateblue'      =>	array('r' => 123, 'g' => 104, 'b' => 238),
    'mediumspringgreen'    =>	array('r' => 0,   'g' => 250, 'b' => 154),
    'mediumturquoise'      =>	array('r' => 72,  'g' => 209, 'b' => 204),
    'mediumvioletred'      =>	array('r' => 199, 'g' => 21,  'b' => 133),
    'midnightblue'         =>	array('r' => 25,  'g' => 25,  'b' => 112),
    'mintcream'            =>	array('r' => 245, 'g' => 255, 'b' => 250),
    'mistyrose'            =>	array('r' => 255, 'g' => 228, 'b' => 225),
    'moccasin'             =>	array('r' => 255, 'g' => 228, 'b' => 181),
    'navajowhite'          =>	array('r' => 255, 'g' => 222, 'b' => 173),
    'navy'                 =>	array('r' => 0,   'g' => 0,   'b' => 128),
    'oldlace'              =>	array('r' => 253, 'g' => 245, 'b' => 230),
    'olive'                =>	array('r' => 128, 'g' => 128, 'b' => 0),
    'olivedrab'            =>	array('r' => 107, 'g' => 142, 'b' => 35),
    'orange'               =>	array('r' => 255, 'g' => 165, 'b' => 0),
    'orangered'            =>	array('r' => 255, 'g' => 69,  'b' => 0),
    'orchid'               =>	array('r' => 218, 'g' => 112, 'b' => 214),
    'palegoldenrod'        =>	array('r' => 238, 'g' => 232, 'b' => 170),
    'palegreen'            =>	array('r' => 152, 'g' => 251, 'b' => 152),
    'paleturquoise'        =>	array('r' => 175, 'g' => 238, 'b' => 238),
    'palevioletred'        =>	array('r' => 219, 'g' => 112, 'b' => 147),
    'papayawhip'           =>	array('r' => 255, 'g' => 239, 'b' => 213),
    'peachpuff'            =>	array('r' => 255, 'g' => 218, 'b' => 185),
    'peru'                 =>	array('r' => 205, 'g' => 133, 'b' => 63),
    'pink'                 =>	array('r' => 255, 'g' => 192, 'b' => 203),
    'plum'                 =>	array('r' => 221, 'g' => 160, 'b' => 221),
    'powderblue'           =>	array('r' => 176, 'g' => 224, 'b' => 230),
    'purple'               =>	array('r' => 128, 'g' => 0,   'b' => 128),
    'red'                  =>	array('r' => 255, 'g' => 0,   'b' => 0),
    'rosybrown'            =>	array('r' => 188, 'g' => 143, 'b' => 143),
    'royalblue'            =>	array('r' => 65,  'g' => 105, 'b' => 225),
    'saddlebrown'          =>	array('r' => 139, 'g' => 69,  'b' => 19),
    'salmon'               =>	array('r' => 250, 'g' => 128, 'b' => 114),
    'sandybrown'           =>	array('r' => 244, 'g' => 164, 'b' => 96),
    'seagreen'             =>	array('r' => 46,  'g' => 139, 'b' => 87),
    'seashell'             =>	array('r' => 255, 'g' => 245, 'b' => 238),
    'sienna'               =>	array('r' => 160, 'g' => 82,  'b' => 45),
    'silver'               =>	array('r' => 192, 'g' => 192, 'b' => 192),
    'skyblue'              =>	array('r' => 135, 'g' => 206, 'b' => 235),
    'slateblue'            =>	array('r' => 106, 'g' => 90,  'b' => 205),
    'slategray'            =>	array('r' => 112, 'g' => 128, 'b' => 144),
    'slategrey'            =>	array('r' => 112, 'g' => 128, 'b' => 144),
    'snow'                 =>	array('r' => 255, 'g' => 250, 'b' => 250),
    'springgreen'          =>	array('r' => 0,   'g' => 255, 'b' => 127),
    'steelblue'            =>	array('r' => 70,  'g' => 130, 'b' => 180),
    'tan'                  =>	array('r' => 210, 'g' => 180, 'b' => 140),
    'teal'                 =>	array('r' => 0,   'g' => 128, 'b' => 128),
    'thistle'              =>	array('r' => 216, 'g' => 191, 'b' => 216),
    'tomato'               =>	array('r' => 255, 'g' => 99,  'b' => 71),
    'turquoise'            =>	array('r' => 64,  'g' => 224, 'b' => 208),
    'violet'               =>	array('r' => 238, 'g' => 130, 'b' => 238),
    'wheat'                =>	array('r' => 245, 'g' => 222, 'b' => 179),
    'white'                =>	array('r' => 255, 'g' => 255, 'b' => 255),
    'whitesmoke'           =>	array('r' => 245, 'g' => 245, 'b' => 245),
    'yellow'               =>	array('r' => 255, 'g' => 255, 'b' => 0),
    'yellowgreen'          =>	array('r' => 154, 'g' => 205, 'b' => 50)
  );

  static function namedColor2rgb($sColor, $bAsString=false)
  {
    $sColor = mb_strtolower($sColor);
    if($sColor == 'transparent') {
      return array('r'=>0, 'g'=>0, 'b'=>0, 'a'=>0);
    }
    if(isset(self::$X11_COLORS_MAP[$sColor]))
    {
      $aRGB = self::$X11_COLORS_MAP[$sColor];
      //return $bAsString ? 'rgb('.implode(',', $aRGB).')' : $aRGB;
      return $aRGB;
    }
    return null;
  }

  static function rgb2namedColor($r, $g, $b, $a=1)
  {
    if($a !== 1) return null;
    if($a == 0) return 'transparent';
    $result = array_search(
      array('r' => $r, 'g' => $g, 'b' => $b),
      self::$X11_COLORS_MAP
    );
    return $result === false ? null : $result;
  }

  /**
   * Converts Hexadecimal color to RGB
   **/
  static function hex2rgb($value)
  {
    if($value[0] == '#') $value = substr($value, 1);
    if(strlen($value) == 3)
    {
      $value = $value[0].$value[0].$value[1].$value[1].$value[2].$value[2]; 
    }
    //If a proper hex code, convert using bitwise operation. No overhead... faster
    if (strlen($value) == 6)
    {
      $decimal = hexdec($value);
      return array(
        'r' => 0xFF & ($decimal >> 0x10),
        'g' => 0xFF & ($decimal >> 0x8),
        'b' => 0xFF & $decimal
      );
    }
    return false; //Invalid hex color code
  }

  /**
   * Converts RGB to Hexadecimal
   **/
  static function rgb2hex($r, $g, $b, $asString=true)
  {
    $r = self::normalizeRGBValue($r);
    $g = self::normalizeRGBValue($g);
    $b = self::normalizeRGBValue($b);
    $value = dechex($r << 16 | $g << 8 | $b);
    $value = str_pad($value, 6, '0', STR_PAD_LEFT);
    return $asString ? '#'.$value : (int)'0x'.$value;
  }

  /**
   * Converts HSL to RGB
   **/
  static function hsl2rgb($h, $s, $l, $a=1)
  {
    // normalize to float between 0..1
    $s = self::normalizeFraction($s);
    $l = self::normalizeFraction($l);
    $a = self::constrainValue($a, 0, 1);

    if($l == 1)
    {
      // white
      $aRGB = array('r' => 255, 'g' => 255, 'b' => 255);
      if($a < 1) $aRGB['a'] = $a;
      return $aRGB;
    }
    if ($l == 0)
    {
      // black
      $aRGB = array('r' => 0, 'g' => 0, 'b' => 0);
      if($a < 1) $aRGB['a'] = $a;
      return $aRGB;
    }
    if($s == 0)
    {
      // Grayscale: we don't need no fancy calculation !
      $v = round(255 * $l);
      $aRGB = array('r' => $v, 'g' => $v, 'b' => $v);
      if($a < 1) $aRGB['a'] = $a;
      return $aRGB;
    }
    // normalize to int between [0,360)
    $h = (($h % 360) + 360) % 360; 
    // then to float between 0..1
    $h /= 360;
    
    if($l < 0.5) $m2 = $l * ($s +1);
    else $m2 = ($l + $s) - ($l * $s);
    $m1 = $l * 2 - $m2;
    
    $aRGB = array(
      'r' => round(255 * self::hue2rgb($m1, $m2, $h + (1/3))),
      'g' => round(255 * self::hue2rgb($m1, $m2, $h)),
      'b' => round(255 * self::hue2rgb($m1, $m2, $h - (1/3)))
    );
    if($a < 1) $aRGB['a'] = $a;
    return $aRGB;
  }

  static private function hue2rgb($m1, $m2, $h)
  {
    if($h < 0) $h++;
    if($h > 1) $h--;
    if(($h * 6) < 1) return $m1 + ($m2 - $m1) * $h * 6;
    if(($h * 2) < 1) return $m2;
    if(($h * 3) < 2) return $m1 + ($m2 - $m1) * (2/3 - $h) * 6;
    return $m1;
  }

  /**
   * Converts RGB to HSL colorspace
   * returns S & L as percentages
   **/
  static function rgb2hsl($r, $g, $b, $a=1)
  {
    // normalize to float between 0..1
    $r = self::normalizeRGBValue($r) / 255;
    $g = self::normalizeRGBValue($g) / 255;
    $b = self::normalizeRGBValue($b) / 255;
    $a = self::constrainValue($a, 0, 1);

    $min = min($r, $g, $b); //Min. value of RGB
    $max = max($r, $g, $b); //Max. value of RGB
    $delta_max = $max - $min; //Delta RGB value

    $l = ($max + $min) / 2;

    if($delta_max == 0) //This is a gray, no chroma...
    {
      //HSL results from 0 to 1
      $h = 0;
      $s = 0;
    }
    else //Chromatic data...
    {
      if($l < 0.5)
      {
        $s = $delta_max / ($max + $min);
      }
      else
      {
        $s = $delta_max / (2 - $max - $min);
      }

      $delta_r = ((($max - $r) / 6) + ($delta_max / 2)) / $delta_max;
      $delta_g = ((($max - $g) / 6) + ($delta_max / 2)) / $delta_max;
      $delta_b = ((($max - $b) / 6) + ($delta_max / 2)) / $delta_max;

      if($r == $max)
      {
        $h = $delta_b - $delta_g;
      }
      else if($g == $max)
      {
        $h = (1/3) + $delta_r - $delta_b;
      }
      else if($b == $max)
      {
        $h = (2/3) + $delta_g - $delta_r;
      }
      if ($h < 0) $h++;
      if ($h > 1) $h--;
    }
    $aHSL = array(
      'h' => round($h * 360),
      's' => round($s * 100) . '%',
      'l' => round($l * 100) . '%'
    );
    if($a < 1) $aHSL['a'] = $a;
    return $aHSL;
  }

  /**
   * Normalize a fraction value:
   * @param $value the divided of the fraction, either a percentage or a number. 
   * @param $max the divisor of the fraction.
   * @returns a float in range 0..1
   **/
  static function normalizeFraction($value, $max=100)
  {
    $i = strpos($value, '%');
    if($i !== false)
    {
      $value = substr($value, 0, $i);
      $max = 100;
    }
    $value = self::constrainValue($value, 0, $max);
    return $value / $max;
  }

  /**
   * Normalize a rgb value:
   * @param $value either a percentage or a number
   * @returns an integer in range 0..255
   **/
  static function normalizeRGBValue($value)
  {
    $i = strpos($value, '%');
    // percentage value
    if($i !== false)
    {
      $value = substr($value, 0, $i);
      $value = self::constrainValue($value, 0, 100);
      return round($value * 255 / 100);
    }
    // normal value
    return self::constrainValue($value, 0, 255);
  }

  static function constrainValue($value, $min, $max)
  {
    return max($min, min($value, $max));
  }
}

