<?php

/**
*
* @package Amber
* @subpackage Tests
*
*/

require_once '../Lexer.php';
require_once 'unit.php';

/**
*
* @package Amber
* @subpackage Tests
*
*/
class testLexer extends PHPUnit_TestCase
{
  var $lexer;

  function setUp()
  {

  }

  function createLexer($string, $lookahead=0)
  {
    $lexer = new Lexer($string, $lookahead);
    $this->assertNotNull($lexer);
    $this->assertEquals($lexer->tokText, '');

    return $lexer;
  }

  function testGetToken()
  {
    $lexer = $this->createLexer('Hello World!');

    $token = $lexer->lex();
    $this->assertEquals('ident', $token);
    $this->assertEquals('Hello', $lexer->tokText);

    $token = $lexer->lex();
    $this->assertEquals('ident', $token);
    $this->assertEquals('World', $lexer->tokText);

    $token = $lexer->lex();
    $this->assertEquals($token, '!');
    $this->assertEquals($lexer->tokText, '!');
  }

  function testPushBack()
  {
    $lexer = $this->createLexer('Hello World!', 1);

    $token = $lexer->lex();
    $this->assertEquals('ident', $token);
    $this->assertEquals('Hello', $lexer->tokText);

    $lexer->pushBack();
    $token = $lexer->lex();
    $this->assertEquals('ident', $token);
    $this->assertEquals('Hello', $lexer->tokText);
  }

  function testDoubleQuotes()
  {
    $lexer = $this->createLexer('select "customer no" from table', 1);

    $token = $lexer->lex();
    $token = $lexer->lex();
    $this->assertEquals('text_val', $token);
    $this->assertEquals('customer no', $lexer->tokText);
  }

  function testSingleQuotes()
  {
    $lexer = $this->createLexer("select 'customer no' from table", 1);

    $token = $lexer->lex();
    $token = $lexer->lex();
    $this->assertEquals('text_val', $token);
    $this->assertEquals('customer no', $lexer->tokText);
  }

  function testSquareBrackets()
  {
    $lexer = $this->createLexer('select [customer no] from table', 1);

    $token = $lexer->lex();
    $token = $lexer->lex();
    $this->assertEquals('name_val', $token);
    $this->assertEquals('customer no', $lexer->tokText);
  }
}

$suite  = new PHPUnit_TestSuite("testLexer");
$result = PHPUnit::run($suite);
$s = $result->toHTML();
if (strpos($s, 'failed')) {
   print $s;
}   

?>

