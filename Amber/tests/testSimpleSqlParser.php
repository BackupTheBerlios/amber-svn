<?php

/**
*
* @package PHPReport
* @subpackage Tests
*
*/

require_once '../SimpleSQLParser.php';
require_once 'unit.php';

/**
*
* @package PHPReport
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

  function testBoolConversion()
  {
    $parser =& new SimpleSelectParser('SELECT * FROM a WHERE b=false AND c = true');
    $result = $parser->parse();

    $this->assertEquals('b = 0 AND c = 1', $result['where']);

    $parser =& new SimpleSelectParser('SELECT * FROM a WHERE b=FALSE AND c = tRue');
    $result = $parser->parse();

    $this->assertEquals('b = 0 AND c = 1', $result['where']);

    print_r($result);
  }

}

$suite  = new PHPUnit_TestSuite("testSimpleSelectParser");
$result = PHPUnit::run($suite);
echo $result->toHTML();

?>

