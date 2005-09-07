<?php

/**
*
* @package Amber
* @subpackage Tests
*
*/

require_once '../SimpleSQLParser.php';
require_once 'unit.php';

/**
*
* @package Amber
* @subpackage Tests
*
*/
class testSimpleSelectParser extends PHPUnit_TestCase
{
  var $parser;

  function setUp()
  {

  }

  function testNull()
  {
    $parser =& new SimpleSelectParser(null);
    $result = $parser->parse();
    $this->assertFalse($result);
  }

  function testArrayKeys()
  {
    $parser =& new SimpleSelectParser('SELECT');
    $result = $parser->parse();

    $this->assertTrue(array_key_exists('select', $result));
    $this->assertTrue(array_key_exists('from', $result));
    $this->assertTrue(array_key_exists('where', $result));
    $this->assertTrue(array_key_exists('group', $result));
    $this->assertTrue(array_key_exists('order', $result));
    $this->assertTrue(array_key_exists('having', $result));
    $this->assertTrue(array_key_exists('limit', $result));
  }

  function testZeroValue()
  {
    $parser =& new SimpleSelectParser('SELECT * FROM a WHERE b=0');
    $result = $parser->parse();

    $this->assertEquals('b = 0', $result['where']);
  }

  function testStrings()
  {
    $parser =& new SimpleSelectParser('SELECT *, "column-name" FROM [table 2] WHERE b=\'0\'');
    $result = $parser->parse();

    $this->assertEquals('*, "column-name"', $result['select']);
    $this->assertEquals('`table 2`', $result['from']);
    $this->assertEquals('b = "0"', $result['where']);

    $parser =& new SimpleSelectParser('SELECT *, "column-name" FROM "table 2" WHERE b=0');
    $result = $parser->parse();

    $this->assertEquals('*, "column-name"', $result['select']);
    $this->assertEquals('"table 2"', $result['from']);
  }

  function testBoolConversion()
  {
    $parser =& new SimpleSelectParser('SELECT * FROM a WHERE b=false AND c = true');
    $result = $parser->parse();

    $this->assertEquals('b = 0 AND c = 1', $result['where']);

    $parser =& new SimpleSelectParser('SELECT * FROM a WHERE b=FALSE AND c = tRue');
    $result = $parser->parse();

    $this->assertEquals('b = 0 AND c = 1', $result['where']);
  }

  function testAccessTableNames()
  {
    $parser =& new SimpleSelectParser('SELECT customer.*, bill.* FROM customer INNER JOIN bill ON [customer].[id]=[bill].[customer];');
    $result = $parser->parse();

    $expected = 'customer.*, bill.*';
    $this->assertEquals($expected, $result['select']);

    $expected = "customer INNER JOIN bill ON `customer`.`id` = `bill`.`customer`";
    $this->assertEquals($expected, $result['from']); 
  }
  
  function testAccessKeywordRemoval()
  {
    $parser =& new SimpleSelectParser('SELECT * FROM a WITH OWNERACCESS OPTION;');
    $result = $parser->parse();

    $expected = '*';
    $this->assertEquals($expected, $result['select']);
    
    $expected = 'a';
    $this->assertEquals($expected, $result['from']);
  }
  
  function testGermanUmlauts()
  {
    $currentLocale = setlocale(LC_CTYPE, null);
    setlocale(LC_CTYPE, 'de_DE', 'de_DE@euro');
    $parser =& new SimpleSelectParser('SELECT a.a, a.xxäöüÄÖÜxx, a.b FROM a, b WHERE a.b = 1;');
    $result = $parser->parse();

    $expected = 'a.a, a.xxäöüÄÖÜxx, a.b';
    $this->assertEquals($expected, $result['select']);
    
    $expected = 'a, b';
    $this->assertEquals($expected, $result['from']);
    
    // switch back to previous locale
    setlocale(LC_CTYPE, $currentLocale);
  } 
}

$suites[]  = new PHPUnit_TestSuite("testSimpleSelectParser");
?>

