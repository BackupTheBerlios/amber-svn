<?

/**
 *
 * @package Amber
 * @subpackage ReportEngine
 *
 */

require_once 'Lexer.php';

/**
 *
 * @package Amber
 * @subpackage ReportEngine
 *
 */
class Parser
{
  var $loopCount;
  var $token;

  var $keywords;
  var $tree = array();

  function Parser($string = null) {
    $this->loopCount = 0;
    $this->lexer = new Lexer(trim($string), 1);
    $this->lexer->symbols =& $this->symbols;
    $this->initLexer();
  }

  function initLexer()
  {
  }

  function getTok()
  {
    $this->token = $this->lexer->lex();
  }

  function getTokText()
  {
    return $this->lexer->tokText;
  }

  function pushBack()
  {
    $this->lexer->pushBack();
  }

  function parse()
  {
  }
}

/**
 *
 * @package Amber
 * @subpackage ReportEngine
 *
 */
class SimpleSelectParser extends Parser
{
  function initLexer()
  {
    $this->keywords = array('select', 'from', 'join', 'inner', 'outer', 'left', 'right',
      'on', 'where', 'group', 'order', 'having', 'order', 'limit');
    $this->accessKeywords = array('with', 'owneraccess', 'option', 'true', 'false');
    $this->symbols = array_flip(array_merge($this->keywords, $this->accessKeywords));
  }

  function parse()
  {
    $this->tree = array();

    $tokenTextList = array();
    $delimiter = array('select', 'from', 'where', 'group', 'order', 'having', 'order', 'limit');

    foreach ($delimiter as $idx) {
      $this->tree[$idx] = '';
    }

    $this->getTok();
    if (($this->token == '') || ($this->token != 'select')) {
      return false;
    }
    $idx = 'select';
    while ($this->token != '') {

      $lastChar = '';
      if (strlen($tokenTextList) > 0) {
        $lastChar = $tokenTextList[strlen($tokenTextList) - 1];
      }

      $newText = '';
      if (!in_array($this->token, $delimiter)) {
        if ($this->token == '.') {
          $newText = '.';
        } else if ($this->token == 'text_val') {
          $newText = '"' . $this->getTokText() . '"';
        } else if ($this->token == 'name_val') {        
          $newText = '`' . $this->getTokText() . '`';
        } else if (in_array(strtolower($this->getTokText()) , $this->accessKeywords, true)) {
          $tmpTok = strtolower($this->getTokText());
          if ($tmpTok == 'false') {
            $newText = ' 0';
          } elseif ($tmpTok == 'true') {
            $newText = ' 1';
          }
          // drop access specific keywords
        } else {
          $newText = $this->getTokText();
        }
        
        if (($this->token == '.') || ($this->token == ',')
          || ($lastChar == '.') || ($this->token  == '('))
        {
          $tokenTextList .= $newText;
        } else {
          $tokenTextList .= ' ' . trim($newText);
        }
    
      } else {
        $this->tree[$idx] = trim($tokenTextList);
        $tokenTextList = '';
        $idx = $this->token;
      }
      $this->getTok();
    }

    // Now drop semicolons and maybe whitespaces at the end of the last clause
    if (!empty($tokenTextList)) {
      do {
        $deleted = false;

        $mbSupport = extension_loaded('mbstring');
        $char = substr($tokenTextList, -1);
        if (($char == ';') || ($char === ' ')) {
          if ($mbSupport) {
            $encoding = mb_detect_encoding($tokenTextList);
            $tokenTextList = mb_substr($tokenTextList, 0, mb_strlen($tokenTextList, $encoding) - 1, $encoding);
          } else {
            $tokenTextList = substr($tokenTextList, 0, strlen($tokenTextList) - 1);
          }
          $deleted = true;
        }
      } while ($deleted);
    }

    $this->tree[$idx] = trim($tokenTextList);

    return $this->tree;
  }
}

?>
