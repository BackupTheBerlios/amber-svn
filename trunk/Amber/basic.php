<?php
/**
*
* @package Amber
* @subpackage Basic
*
*/
require_once 'adodb/adodb-time.inc.php';


/**
 *
 * The function format tries to resemble Microsoft Basic's format function
 * @access public
 * @param mixed the value to format
 * @param string format string
 *
 */

function Format($value, $fmt, $prec=2)
{
  static $fmtCache;               //speed: cache _format object (0.9sec)
  $f = $fmtCache[$fmt][$prec];
  if (!$f) {
    $f =& new _format($fmt, $prec);
    $fmtCache[$fmt][$prec] =& $f;
  } else {
  }
  return $f->format($value, $fmt);
}



/**
 *
 * The function iif tries to resemble Microsoft Basic's iif function
 * @access public
 * @param mixed condition to evaluate
 * @param mixed if condition is true return this parameter
 * @param mixed if condition is false return this parameter
 *
 */
function IIf($condition, $truePart, $falsePart)
{
  if ($condition) {
    return $truePart;
  } else {
    return $falsePart;
  }
}

/**
 *
 * The function mid tries to resemble Microsoft Basic's mid function
 * @access public
 * @param string
 * @param integer
 * @param integer
 *
 */
function Mid($string, $start, $length=null)
{
  if (is_null($length)) {
    return substr($string, $start - 1);
  } else {
    return substr($string, $start - 1, $length);
  }
}

/**
 *
 * The function left tries to resemble Microsoft Basic's left function
 * @access public
 * @param string
 * @param integer
 *
 */
function Left($string, $length)
{
  return substr($string, 0, $length);
}

/**
 *
 * The function right tries to resemble Microsoft Basic's right function
 * @access public
 * @param string
 * @param integer
 *
 */
function Right($string, $length)
{
  if ($length == 0) {
    return '';
  } else {
    return substr($string, strlen($string)-$length);
  }
}

/**
 *
 * The function Now tries to resemble Microsoft Basic's Now function
 * @access public
 *
 */
function Now()
{
  $d = getdate();
  return $d[0];
}

/**
 *
 * The function IsNull tries to resemble Microsoft Basic's IsNull function
 * @access public
 * @param string
 *
 */
function IsNull($value)
{
  return is_null($value);
}

function Day($value)
{
  if (is_null($value)) {
      return null;
  } elseif (preg_match(
       "|^([0-9]{4})[-/\.]?([0-9]{1,2})[-/\.]?([0-9]{1,2})[ -]?(([0-9]{1,2}):?([0-9]{1,2}):?([0-9\.]{1,4}))?|", 
  	($value), $rr)) {
    return $rr[3];
  } else {
    die('Basic.Day: not a Date: "' . $value . '"');
  }
}        
    
function Month($value)
{
  if (is_null($value)) {
      return null;
  } elseif (preg_match(
       "|^([0-9]{4})[-/\.]?([0-9]{1,2})[-/\.]?([0-9]{1,2})[ -]?(([0-9]{1,2}):?([0-9]{1,2}):?([0-9\.]{1,4}))?|", 
  	($value), $rr)) {
    return $rr[2];
  } else {
    die('Basic.Month: not a Date: "' . $value . '"');
  }
}        

function Year($value)
{
  if (is_null($value)) {
      return null;
  } elseif (preg_match(
       "|^([0-9]{4})[-/\.]?([0-9]{1,2})[-/\.]?([0-9]{1,2})[ -]?(([0-9]{1,2}):?([0-9]{1,2}):?([0-9\.]{1,4}))?|", 
  	($value), $rr)) {
    return $rr[1];
  } else {
    die('Basic.Year: not a Date: "' . $value . '"');
  }
}        


class _format
{
  var $fmt;           //format string

  var $Token;         //private. TokenID
  var $value;         //private. scanned value of token
  var $lexPos;        //private. Lexer: position of last read char
  var $len;           //private. length of $ftm

  var $formatParts;   //private. array of tokenized format

  var $BOF        =  -1;    //const Begin of String
  var $EOF        =   0;    //const End of String
  var $STRING     =   1;    //const String

  var $DecimalPoint;        //cache for $this->getDecimalPoint()

  /**
   *
   * @access public
   * @param string format
   * @param number precision (number of digits after the decimal point) default=2
   *
   */
  function _format($fmt, $prec=2)
  {
    $stdFormats = getStdFormat(null, $prec);
    $this->prec = $prec;
    $f = $stdFormats[strtolower($fmt)];
    if (!is_null($f)) {
      $this->fmt = $f;
    } elseif ($this->fmt === '') { //General Number
      $this->fmt = null;
      return;
    } else {
      $this->fmt = $fmt;
    }

    $this->len = strlen($this->fmt);
    $this->lexPos = 0;
    $this->Token = $this->BOF;
    $this->formatParts = $this->getTokenArray();
    $this->type = '#null';
    if (is_null($this->formatParts[0])) {
      return;
    }

    $fmtChars = array(
      '#' => 'n',
      '0' => 'n',
      '.' => 'n',
      '%' => 'n',
      '!' => 's',
      '&' => 's',
      '@' => 's',
      '>' => 's',
      '<' => 's',
      ':' => 'd',
      '/' => 'd',
      'd' => 'd',
      'm' => 'd',
      'y' => 'd',
      'h' => 'd',
      'n' => 'd',
      's' => 'd',
      'q' => 'd',
      'w' => 'd');
    foreach($this->formatParts[0] as $token) {
      $ch = $token['token'][0];
      if (!is_null($fmtChars[$ch])) {
        $this->type = $fmtChars[$ch];
        return;
      }
    }
  }

  /**
   *
   * @access private for lexer: push back character
   *
   */
  function pushback()
  {
    $this->lexPos--;
  }

  /**
   *
   * @access private for lexer: read next char
   * @param char
   *
   */
  function nextChar(&$ch)
  {
    if ($this->lexPos >= $this->len) {
      $this->lexPos = $this->len + 1;
      $ch = "\0";
    }
    else
    {
      $ch = $this->fmt[$this->lexPos];
      $this->lexPos ++;
    }
    return $ch;
  }

  /**
   *
   * @access private lexer: return format tokens
   *
   */
  function NextToken()
  {
    $this->Value = '';
    $this->nextChar($ch);

    $this->Value .= $ch;
    if ($ch == "\0")
      $this->Token = $this->EOF;
    elseif ($ch == ';') {
      $this->Token = $ch;
    }
    elseif (($ch == '.') or ($ch == '#') or ($ch == '0') or ($ch == 'e') or ($ch == 'E') or ($ch == ',') or ($ch == '/') or ($ch == ':')) {
      $this->Token = $ch;
    }
    elseif ($ch == "\\") {   // character
      $this->Token = $this->STRING;
      $this->Value = $this->nextChar($ch);
    }
    elseif ($ch == '"') {   // string
      $this->Token = $this->STRING;
      $this->Value = '';
      $EndStr = false;
      while (! $EndStr) {
        if ($this->nextChar($ch)!= '"')
          $this->Value .= $ch;
        elseif ($this->nextChar($ch) == '"')
          $this->Value .= $ch;
        else {
          $this->pushback();
          $EndStr = true;
        }
      }
    }
    else {
      $ch1 = $ch;
      $this->Value = $ch;
      while ($ch1 == $this->nextChar($ch)) {
        $this->Value .= $ch;
      }
      $this->pushback();
      $this->Token = $this->Value;
    }
  }

  /**
   *
   * @access private tokenize format string into array
   *
   */
  function getTokenArray()
  {
    $i = 0;
    $this->NextToken();
    while ($this->Token !== $this->EOF) {
      if ($this->Token === ';') {
        $i++;
      } else {
        $arr[$i][] = array('token' => $this->Token, 'value' => $this->Value);
      }
      $this->NextToken();
    }
    return $arr;
  }

  function format($value, $fmt, $prec=2)
  {
    if (($this->fmt === '') or is_null($this->fmt)) {
      return $this->stdFormat($value);
      #return $value;
    }
    if ($this->type == 'd') return $this->formatDate($value);
    elseif ($this->type == 's') return $this->formatString($value);
    elseif ($this->type == 'n') return $this->formatNumber($value);
    else                        return $this->formatNumber($value);
  }

  function twoDigits($value)
  {
    if ($value < 10) {
      return '0' . $value;
    } else {
      return $value;
    }
  }

  function formatString($value)
  {
  }

  function formatNumber($value)
  {
    if (!is_numeric($value) and !is_bool($value)) {
      $v = str2date($value);
      if (!is_null($v)) {
        $value = $v;
      } elseif (!is_null($value)) {
         return $this->stdFormat($value);
      }
    }

    if (sizeof($this->formatParts) == 1) {
      $fmt = $this->formatParts[0];
    } elseif ($value > 0) {
      $fmt = $this->formatParts[0];
    } elseif ($value < 0) {
      if (!isset($this->formatParts[1])) {
        $fmt = $this->formatParts[0];
      } else {
        $fmt = $this->formatParts[1];
        $value = -$value;
      }
    } elseif (is_null($value)) {
      $fmt =  $this->formatParts[3];
    } elseif ($this->formatParts[2]) {
      $fmt = $this->formatParts[2];
    } else {
      $fmt = $this->formatParts[0];
    }


    if (! isset($fmt)) {
      if (is_null($value)) {
        return '';
      } else {
        return $this->stdFormat($value);
      }
    }

    // start first pass: count the digits
    $decimal = 0;
    $digitPre9 = 0;    #Vorkommastellen #
    $digitPre0 = 0;    #Vorkommastellen 0
    $digitPost0 = 0;    #Nachkommastellen 0
    $digitPost9 = 0;    #Nachkommastellen #
    $haveExpo = false;  # have exponent
    $expoDigits = 0;
    $haveThousandDelim = false;
    //leading #
    for ($pos = 0; $pos < count($fmt); $pos++) {
      if ($fmt[$pos]['token'] == '#') {
        $digitPre9 ++;
      } elseif (($fmt[$pos]['token'] == '0') or ($fmt[$pos]['token'] == '.') or ($fmt[$pos]['token'] == 'e') or ($fmt[$pos]['token'] == 'E')) {
        break;
      } elseif ($fmt[$pos]['token'] == ',') {
        $haveThousandDelim = true;
      } elseif ($fmt[$pos]['token'] == '%') {
        $havePercent = true;
      }
    }

    // leading 0
    for (; $pos < count($fmt); $pos++) {
      if (($fmt[$pos]['token'] == '0') or ($fmt[$pos]['token'] == '#')) {
        $digitPre0 ++;
      } elseif (($fmt[$pos]['token'] == '.') or ($fmt[$pos]['token'] == 'e') or ($fmt[$pos]['token'] == 'E')) {
        break;
      } elseif ($fmt[$pos]['token'] == '%') {
        $havePercent = true;
      } elseif ($fmt[$pos]['token'] == ',') {
        $thousandDelim = true;
      }
    }

    // decimalpoint
    if ($fmt[$pos]['token'] == '.') {
      $pos++;
    }

    // trailing # and 0
    for (;$pos < count($fmt); $pos++) {
      if ($fmt[$pos]['token'] == '#') {
        $digitPost9 ++;
      }
      elseif ($fmt[$pos]['token'] == '0') {
        $digitPost0 += 1 + $digitPost9;
        $digitPost9 = 0;
      }
      elseif (($fmt[$pos]['token'] == 'e') or ($fmt[$pos]['token'] == 'E')) {
        break;
      } elseif ($fmt[$pos]['token'] == ',') {
        $thousandDelim = true;
      } elseif ($fmt[$pos]['token'] == '%') {
        $havePercent = true;
      }
    }

    // exponent
    if (($fmt[$pos]['token'] == 'e') or ($fmt[$pos]['token'] == 'E')) {
      $pos++;
      if (($fmt[$pos]['token'] == '+') or ($fmt[$pos]['token'] == '-')) {
        $haveExpo = true;
        $pos++;
        for (;$pos < count($fmt); $pos++) {
          if (($fmt[$pos]['token'] == '0') or ($fmt[$pos]['token'] == '#')) {
            $expoDigits++;
          } else {
            break;
          }
        }
      }
    }

    // even more trailing # and 0
    for (;$pos < count($fmt); $pos++) {
      if ($fmt[$pos]['token'] == '#') {
        $digitPost9 ++;
      }
      elseif ($fmt[$pos]['token'] == '0') {
        $digitPost0 += 1 + $digitPost9;
        $digitPost9 = 0;
      } elseif ($fmt[$pos]['token'] == '%') {
        $havePercent = true;
      }
    }
    // end first pass


    // prepare the string from value
    // strip sign
    if ($value < 0) {
      $sign = -1;
      $value = -$value;
    } else {
      $sign = 1;
    }

    if ($havePercent) {
      $value = $value * 100;
    }

    if (!$haveExpo) { // no exponent
      //fill leading 0
      $lead = strval($value - fmod($value, 1));
      if (strlen($lead) < $digitPre0) {
        $lead = str_repeat('0', $digitPre0 - strlen($lead)) . $lead;
      }
      // handle leading # and 0
      $digitPre = $digitPre0 + $digitPre9;
      if (strlen($lead) > ($digitPre)) {
        $pre = substr($lead, 0, strlen($lead) - $digitPre);
        $lead = substr($lead, strlen($lead) - $digitPre);
      }

      // start trailer
      $digitPost9 += $digitPost0;

      if (fmod($value, 1) == 0) {
        $trail = '';
      } else {
        $trail = strval(round(fmod($value, 1), $digitPost9));
        $trail = substr($trail, 2);
      }

      // fill trailing 0
      if ($digitPost0 > strlen($trail)) {
        $trail .= str_repeat('0', $digitPost0 - strlen($trail));
      }
      // handle trailing # and 0
      if ($digitPost9 > strlen($trail)) {
        $trail .= str_repeat(' ', $digitPost9 - strlen($trail));
      }
    } else {
      $digitPre = $digitPre0 + $digitPre9;
      $digitPost = $digitPost0 + $digitPost9;
      $digits = $digitPre + $digitPost;

      $exponent = floor(log10($value))+1;
      $mantissa = $value / (pow(10, $exponent));
      $mantissa = round($mantissa, $digits);
      $strMant = substr($mantissa,2) . str_repeat('0', $digits);

      $lead = substr($strMant, 0, $digitPre);
      $trail = substr($strMant, $digitPre, $digitPost);
      $exponent = $exponent - $digitPre;
      if ($expoDigits > 3) {
        $x = 3;
      } else {
        $x = $expoDigits;
      }
      $expostr = substr('000' . $exponent, strlen($exponent)+3-$x);
    }


    // start second pass: fill in the values
    $res = '';
    $leadPos = 0;
    if ($haveThousandDelim) {
       $ThousandDelim = $this->getThousandsSep();
    }
    for ($pos = 0; $pos < count($fmt); $pos++) {
      if (($fmt[$pos]['token'] == '0') or ($fmt[$pos]['token'] == '#')) {
        if ($leadPos == 0) {
          if (!$haveThousandDelim) {
            $res .= $pre;
          } else {
            for ($i=0; $i < strlen($pre); $i++) {
              $res .= $pre[$i];
              if (((strlen($pre) + strlen($lead) - $i - 1) % 3 == 0)
              and ((strlen($pre) + strlen($lead) - $i - 1) > 0)
              and ($pre[$i] <> ' ')){
                $res .= $ThousandDelim;
              }
            }
          }
        }
        $res .= $lead[$leadPos];
        if ($haveThousandDelim) {
          if (((strlen($lead) - $leadPos - 1) % 3 == 0)
          and ((strlen($lead) - $leadPos - 1) > 0)
          and ($lead[$leadPos] <> ' ')) {
            $res .= $ThousandDelim;
          }
        }
        $leadPos++;
      } elseif (($fmt[$pos]['token'] == '.') or ($fmt[$pos]['token'] == 'e') or ($fmt[$pos]['token'] == 'E')) {
        break;
      } elseif ($fmt[$pos]['token'] <> ',') {
        $res .= $fmt[$pos]['value'];
      }
    }

    if ($fmt[$pos]['token'] == '.') {
      $res .= $this->getDecimalPoint();
      $pos++;
    }

    $trailPos = 0;
    for (; $pos < count($fmt); $pos++) {
      if (($fmt[$pos]['token'] == '0') or ($fmt[$pos]['token'] == '#')) {
        $res .= $trail[$trailPos];
        $trailPos++;
      } elseif (($fmt[$pos]['token'] == 'e') or ($fmt[$pos]['token'] == 'E')) {
        break;
      } elseif ($fmt[$pos]['token'] <> ',') {
        $res .= $fmt[$pos]['value'];
      }
    }

    if (($fmt[$pos]['token'] == 'e') or ($fmt[$pos]['token'] == 'E')) {
      if (($fmt[$pos+1]['token'] == '+') or ($fmt[$pos+1]['token'] == '-')) {
        $res .= $fmt[$pos]['token'];
        if ($exponent < 0) {
          $res .= '-';
          $exponent = -$exponent;
        } elseif ($fmt[$pos+1]['token'] == '+') {
          $res .= '+';
        }
        $pos += 2;
        if (($fmt[$pos]['token'] == '0') or ($fmt[$pos]['token'] == '#')) {
          $res .= $expostr;
          $pos += $expoDigits;
        } else {
          $res .= $exponent;
        }
      }
    }

    for (; $pos < count($fmt); $pos++) {
      if (($fmt[$pos]['token'] == '0') or ($fmt[$pos]['token'] == '#')) {
        $res .= $trail[$trailPos];
        $trailPos++;
      } elseif ($fmt[$pos]['token'] <> ',') {
        $res .= $fmt[$pos]['value'];
      }
    }


    if ($sign < 0) {
      $res = '-' . $res;
    }
    // end second pass


    return $res;
  }

  function formatDate($value)
  {
    if (!is_numeric($value)) {
      $v = str2date($value);
      if (!is_null($v)) {
        $value = $v;
      } else {
        return $this->stdFormat($value);
      }
    }
    if (is_numeric($value)) {
      $dateParts = adodb_getdate($value);               //speed: adodb_getdate is slow!!!
      $year = $dateParts['year'];
      $month = $dateParts['mon'];
      $day = $dateParts['mday'];
      $hour = $dateParts['hours'];
      $min = $dateParts['minutes'];
      $secs = $dateParts['seconds'];
      $dow = $dateParts['wday'];
    }

    $_day_power = 86400;
    $_hour_power = 3600;
    $_min_power = 60;


    //cannot replace Epoch by constant, because value of adodb_mktime depends on time zone
    //but we compute it only once ....
    static $_MicrosoftEpoch;                            //speed: cache $epoch (1.5sec)
    if (!$_MicrosoftEpoch) {
      $_MicrosoftEpoch = adodb_mktime(0,0,0,12,30,1899);
    }

    if (sizeof($this->formatParts) == 1) {
      $fmt = $this->formatParts[0];
    } elseif ($value == $_MicrosoftEpoch) {
      if ($this->formatParts[2]) {
        $fmt = $this->formatParts[2];
      } else {
        $fmt = $this->formatParts[0];
      }
    } elseif (is_null($value)) {
      $fmt = $this->formatParts[3];
    } else {
      $fmt = $this->formatParts[0];
    }

    $res = '';
    if (! isset($fmt)) {
      if (is_null($value)) {
        return '';
      } else {
        return $this->stdFormat($value);
      }
    }

    foreach ($fmt as $fmtItem) {
      switch ($fmtItem['token']) {
      case $this->STRING:
        $res .= $fmtItem['value'];
        break;
      case 'd':
        $res .= $day;
        $this->OnDayFormat();
        break;
      case 'dd':
        $res .= $this->twoDigits($day);
        $this->OnDayFormat();
        break;
      case 'ddd':
        $res .= strftime('%a', $_day_power*(3+$dow));
        break;
      case 'dddd':
        $res .= strftime('%A', $_day_power*(3+$dow));
        break;
      case 'ddddd':
        $res .= format($value, 'dd.mm.yy');
        break;
      case 'dddddd':
        $res .= format($value, 'dd.mmmm.yyyy');
        break;
      case 'm':
        $res .= $month;
        $this->OnMonthFormat();
        break;
      case 'mm':
        $res .= $this->twoDigits($month);
        $this->OnMonthFormat();
        break;
      case 'mmm':
        $res .= strftime('%b', mktime(0,0,0,$month,2,1971));
        $this->OnMonthFormat();
        break;
      case 'mmmm':
        $res .= strftime('%B', mktime(0,0,0,$month,2,1971));
        $this->OnMonthFormat();
        break;
      case 'y':
        $res .= $dateParts['yday'] + 1;
        break;
      case 'yy':
        $y = $year % 100;
        $res .= $this->twoDigits($y);
        break;
      case 'yyyy':
        $res .= $year;
        break;
      case 'h':
        $res .= $hour;
        break;
      case 'hh':
        $res .= $this->twoDigits($hour);
        break;
      case 'n':
        $res .= $min;
        break;
      case 'nn':
        $res .= $this->twoDigits($min);
        break;
      case 's':
        $res .= $secs;
        break;
      case 'ss':
        $res .= $this->twoDigits($secs);
        break;
      case 'ttttt':
        $res .= format('h:mm:ss', $value);
        break;
      case '/':
        $res .= $this->getDateSep();
        break;
      default:
        $res .= $fmtItem['value'];
      }
    }
    return $res;
  }

  var $_DecimalPoint;
  var $_locale_Monetary;
  var $_locale_Numeric;
  function getDecimalPoint()
  {
    if ((!$this->_DecimalPoint)  or ($this->_locale_Monetary <> setlocale(LC_MONETARY, 0)) or ($this->_locale_Monetary = setlocale(LC_MONETARY, 0))) {
      $locale_info = localeconv();
      if ($locale_info['mon_decimal_point']) {
        $this->_DecimalPoint = $locale_info['mon_decimal_point'];
      } elseif ($locale_info['decimal_point']) {
        $this->_DecimalPoint = $locale_info['decimal_point'];
      } else {
        $this->_DecimalPoint = '.';
      }
      $this->_locale_Monetary = setlocale(LC_MONETARY, 0);
      $this->_locale_Numeric  = setlocale(LC_NUMERIC,  0);
    }
    return $this->_DecimalPoint;
  }

  function getThousandsSep()
  {
    $locale_info = localeconv();
    if ($locale_info['mon_decimal_point']) {
      return $locale_info['mon_thousands_sep'];
    } elseif ($locale_info['decimal_point']) {
      return $locale_info['thousands_sep'];
    } else {
      return ',';
    }
  }


  var $_dateSep = 0; // 0: don't know, 1: dd.mm.yyyy, 2: mm/dd/yyyy

  function OnDayFormat()
  {
    if ($this->_dateSepType == 0) {
      $this->_dateSepType = 1;
    }
  }

  function OnMonthFormat()
  {
    if ($this->_dateSepType == 0) {
      $this->_dateSepType = 2;
    }
  }

  function getDateSep()
  {
    if ($this->_dateSepType == 1) {
      return '.';
    } else {
      return '/';
    }
  }

  function stdFormat($value)
  {
    if (is_null($value)) {
      return '';
    } elseif (is_numeric($value)) {
      $p = _format::getDecimalPoint();
      #$p = ',';
      return str_replace('.', $p,  strval($value));
    } else {
      return $value;
    }
  }
}

function str2date($d)
{
  if (!preg_match(
    "|^([0-9]{4})[-/\.]?([0-9]{1,2})[-/\.]?([0-9]{1,2})[ -]?(([0-9]{1,2}):?([0-9]{1,2}):?([0-9\.]{1,4}))?|",
    ($d), $rr)) {
    if (!preg_match(
    "|^()()()(([0-9]{1,2}):?([0-9]{1,2}):?([0-9\.]{1,4}))?|",
    ($d), $rr)) {
      return NULL;
    }
  }


  if (($rr[0])==='') return NULL;
  #if (isset($rr[1]) and ($rr[1] <= 100 or $rr[2]<= 1)) return NULL;
  // h-m-s-MM-DD-YY
  if (!isset($rr[5])) {
    $d = adodb_mktime(0, 0, 0, $rr[2], $rr[3], $rr[1]);
  } elseif (!isset($rr[1])) {
    $d =  adodb_mktime($rr[5], $rr[6], $rr[7], 0, 0, 0);
  } else {
   $d =  @adodb_mktime($rr[5], $rr[6], $rr[7], $rr[2], $rr[3], $rr[1]);
  }

  return $d;
}

function getStdFormat($locale='', $prec=2)
{
  if (($prec > 0) and ($prec < 16)) {
    $zero = '0.' . str_repeat('0', $prec);
  } elseif (($prec === 0) || ($prec === '0')) {
    $zero = '0';
  } else {
    $zero = '';
  }
   
  if (!$locale) {
    $locale = substr(setlocale(LC_TIME, 0),0,2);
  }

  $t  = localeconv();
  
  if ($locale == "de") {
    return array(
      'general date'    =>  'dd.mm.yyyy hh:nn:ss',
      'long date'       =>  'dddd, d. mmmm yyyy',
      'medium date'     =>  'dd. mmm. yy',
      'short date'      =>  'dd.mm.yyyy',
      'long time'       =>  'hh:nn:ss',
      'medium time'     =>  'hh:nn',
      'short time'      =>  'hh:nn',
      'general number'  =>  '',
      'currency'        =>  '#,##0.00 DM',
      'euro'            =>  '#,##0.00 EUR',
      'fixed'           =>  $zero,
      'standard'        =>  '#,##' . $zero,
      'percent'         =>  $zero . '%',
      'scientific'      =>  $zero . 'E+00',
      'true/false'      =>  '"Wahr";"Wahr";"Falsch"',
      'yes/no'          =>  '"Ja";"Ja";"Nein"',
      'on/off'          =>  '"Ein";"Ein";"Aus"'
    );
  } else {
    return array(
      'general date'    =>  'mm/dd/yyyy hh:nn:ss',
      'long date'       =>  'dddd, mmmm d yyyy',
      'medium date'     =>  'mmm dd yy',
      'short date'      =>  'mm/dd/yyyy',
      'long time'       =>  'hh:nn:ss',
      'medium time'     =>  'hh:nn am/pm',
      'short time'      =>  'hh:nn',
      'general number'  =>  '#.#',
      'currency'        =>  '#,##0.00 $',
      'euro'            =>  '#,##0.00 EUR',
      'fixed'           =>  $zero,
      'standard'        =>  '#,##' . $zero,
      'percent'         =>  $zero . '%',
      'scientific'      =>  $zero . 'E+00',
      'true/false'      =>  '"True";"True";"False"',
      'yes/no'          =>  '"Yes";"Yes";"No"',
      'on/off'          =>  '"On";"On";"Off"'
    );
  }
}

?>
