<?php

/**
*
* @package Amber
* @subpackage Tests
*
*/
ini_set('include_path', ini_get('include_path') . ':' . dirname(__FILE__). '/../../Amber/');

require_once 'unit.php';
require_once '../AggregateFactory.php';
require_once '../Aggregate.php';

/**
*
* @package Amber
* @subpackage Tests
*
*/
class Aggregate_Basic extends PHPUnit_TestCase
{
  function test()
  {
    $agg = AggregateFactory::create('sum');
    $this->assertNull($agg->Value,       'test Sum 0');
    
    $list = array(1, 2, 3, 4, -4, '2', '', null);
    $agg->reset();
    foreach ($list as $item) { $agg->addvalue($item); }
    $this->assertEquals(8, $agg->Value,       'test Sum 1');

    $list = array('', null);
    $agg->reset();
    $this->assertNull($agg->Value,       'test Sum 2');
    foreach ($list as $item) { $agg->addvalue($item); }
    $this->assertNull($agg->Value,       'test Sum 3');
  
  
//  function test_Count()
  
    $agg = AggregateFactory::create('count');
    $this->assertEquals(0, $agg->Value,       'test count 0');
    
    $list = array(1, 2, 3, 4, -4, '2', '', null);
    $agg->reset();
    foreach ($list as $item) { $agg->addvalue($item); }
    $this->assertEquals(6, $agg->Value,       'test count 1');

    $list = array('', null);
    $agg->reset();
    $this->assertEquals(0, $agg->Value,       'test count 2');
    foreach ($list as $item) { $agg->addvalue($item); }
    $this->assertEquals(0, $agg->Value,       'test count 3');
  
  
//  function test_Avg()
  
    $agg = AggregateFactory::create('avg');
    $this->assertEquals(null, $agg->Value,       'test avg 0');
    
    $list = array(1, 2, 3, 4, -4, '2', '', null);
    $agg->reset();
    foreach ($list as $item) { $agg->addvalue($item); }
    $this->assertEquals(4, 3*$agg->Value,     'test avg 1');

    $list = array('', null);
    $agg->reset();
    $this->assertNull($agg->Value,       'test avg 2');
    foreach ($list as $item) { $agg->addvalue($item); }
    $this->assertEquals(null, $agg->Value,       'test avg 3');
  
  
//  function test_Min()
  
    $agg = AggregateFactory::create('min');
    $this->assertEquals(null, $agg->Value, 'test min 0');
    
    $agg->reset();
    $list = array(1, 2, 3, 4, -4, '2', '', null);
    $agg->reset();
    foreach ($list as $item) { $agg->addvalue($item); }
    $this->assertEquals(-4, $agg->Value,   'test min 1');

    $list = array(1, 2, 3, 4, '2', '', null);
    $agg->reset();
    foreach ($list as $item) { $agg->addvalue($item); }
    $this->assertEquals(1, $agg->Value,    'test min 1a');

    $list = array('', null);
    $agg->reset();
    $this->assertNull($agg->Value,         'test min 2');
    foreach ($list as $item) { $agg->addvalue($item); }
    $this->assertEquals(null, $agg->Value, 'test min 3');


//  function test_Max()
  
    $agg = AggregateFactory::create('max');
    $this->assertEquals(null, $agg->Value, 'test max 0');
    
    $agg->reset();
    $list = array(1, 2, 3, 4, -4, '2', '', null);
    $agg->reset();
    foreach ($list as $item) { $agg->addvalue($item); }
    $this->assertEquals(4, $agg->Value,    'test max 1');

    $list = array(-1, -2, -3, -4, '-2', '', null);
    $agg->reset();
    foreach ($list as $item) { $agg->addvalue($item); }
    $this->assertEquals(-1, $agg->Value,   'test max 1a');

    $list = array('', null);
    $agg->reset();
    $this->assertNull($agg->Value,         'test max 2');
    foreach ($list as $item) { $agg->addvalue($item); }
    $this->assertEquals(null, $agg->Value, 'test max 3');
  
 
//  function test_First()
  
    $agg = AggregateFactory::create('first');
    $this->assertEquals(null, $agg->Value, 'test first 0');
    
    $agg->reset();
    $list = array(1, 2, 3, 4, -4, '2', '', null);
    $agg->reset();
    foreach ($list as $item) { $agg->addvalue($item); }
    $this->assertEquals(1, $agg->Value,    'test first 1');

    $list = array(null, -1, -2, -3, -4, '-2', '', null);
    $agg->reset();
    foreach ($list as $item) { $agg->addvalue($item); }
    $this->assertNull($agg->Value,         'test first 1a');

    $list = array('', null);
    $agg->reset();
    $this->assertNull($agg->Value,         'test first 2');
    foreach ($list as $item) { $agg->addvalue($item); }
    $this->assertEquals('', $agg->Value,   'test first 3');
  

//  function test_Last()
  
    $agg = AggregateFactory::create('last');
    $this->assertEquals(null, $agg->Value, 'test last 0');
    
    $agg->reset();
    $list = array(1, 2, 3, 4, -4, '2', '', null, 1);
    $agg->reset();
    foreach ($list as $item) { $agg->addvalue($item); }
    $this->assertEquals(1, $agg->Value,    'test last 1');

    $list = array(null, -1, -2, -3, -4, '-2', '', null);
    $agg->reset();
    foreach ($list as $item) { $agg->addvalue($item); }
    $this->assertNull($agg->Value,         'test last 1a');

    $list = array(null, '');
    $agg->reset();
    $this->assertNull($agg->Value,         'test last 2');
    foreach ($list as $item) { $agg->addvalue($item); }
    $this->assertEquals('', $agg->Value,   'test last 3');
  

//  function test_Var()
  
    $agg = AggregateFactory::create('var');
    $this->assertEquals(null, $agg->Value,  'test var 0');
    
    $agg->reset();
    $list = array(9, 1, 2, '', null);
    $agg->reset();
    foreach ($list as $item) { $agg->addvalue($item); }
    $this->assertEquals(19, $agg->Value,     'test var 1');

    $list = array(9);
    $agg->reset();
    foreach ($list as $item) { $agg->addvalue($item); }
    $this->assertNull($agg->Value,           'test var 1a');

    $list = array(null, '');
    $agg->reset();
    $this->assertNull($agg->Value,           'test var 2');
    foreach ($list as $item) { $agg->addvalue($item); }
    $this->assertEquals(null, $agg->Value,   'test var 3');
  

//  function test_Stdev()
  
    $agg = AggregateFactory::create('stdev');
    $this->assertEquals(null, $agg->Value,   'test stdev 0');
    
    $agg->reset();
    $list = array(9, 1, 2, '', null);
    $agg->reset();
    foreach ($list as $item) { $agg->addvalue($item); }
    $this->assertEquals(sqrt(19), $agg->Value,'test stdev 1');

    $list = array(9);
    $agg->reset();
    foreach ($list as $item) { $agg->addvalue($item); }
    $this->assertNull($agg->Value,           'test stdev 1a');

    $list = array(null, '');
    $agg->reset();
    $this->assertNull($agg->Value,           'test stdev 2');
    foreach ($list as $item) { $agg->addvalue($item); }
    $this->assertEquals(null, $agg->Value,   'test stdev 3');
  }
}

/**
*
* @package Amber
* @subpackage Tests
*
*/

$suite  = new PHPUnit_TestSuite("Aggregate_Basic");
$result = PHPUnit::run($suite);
$s = $result->toHTML();
if (strpos($s, 'failed')) {
   print $s;
}   
?>
