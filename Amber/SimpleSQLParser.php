<?

/**
 *
 * @package PHPReport
 * @subpackage ReportEngine
 *
 */

require_once 'Lexer.php';

/**
 *
 * @package PHPReport
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
    if (is_string($string)) {
      $this->lexer = new Lexer(trim($string), 1);
      $this->lexer->symbols =& $this->symbols;
      $this->initLexer();
    }
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
 * @package PHPReport
 * @subpackage ReportEngine
 *
 */
class SimpleSelectParser extends Parser
{
  function initLexer()
  {
    $this->keywords = array('select', 'from', 'join', 'where', 'group', 'order', 'having', 'order', 'limit');
    $this->accessKeywords = array('with', 'owneraccess', 'option');
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
      Amber::showError('Error', __CLASS__ . '::' . __FUNCTION__ . '(): Not a select query');
      return;
    }
    $idx = 'select';
    while ($this->token != '') {
      if (!in_array($this->token, $delimiter)) {
        if ($this->token == '.') {
          $tokenTextList .= '.';
        } else if (in_array($this->getTokText() , $this->accessKeywords)) {
          // drop access specific keywords
        } else {
          if (strlen($tokenTextList) > 0) {
            $lastChar = $tokenTextList[strlen($tokenTextList) - 1];
          } else {
            $lastChar = '';
          }
          if (($lastChar == '.') || ($this->token  == '(')) {
            $tokenTextList .= $this->getTokText();
          } else {
            $tokenTextList .= ' ' . $this->getTokText();
          }
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
        $char = substr($tokenTextList, -1);
        if (($char == ';') || ($char == ' ')) {
          $tokenTextList = substr($tokenTextList, 0, strlen($tokenTextList) - 2);
          $deleted = true;
        }
      } while ($deleted);
    }

    $this->tree[$idx] = trim($tokenTextList);

    return ($this->tree);
  }
}

?>
