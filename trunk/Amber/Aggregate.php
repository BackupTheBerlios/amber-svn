<?php

/**
*
* @package Amber
* @subpackage Aggregate
*
*/
require_once 'AggregateFactory.php';

AggregateFactory::register('sum',   'AggregateSum');
AggregateFactory::register('min',   'AggregateMin');
AggregateFactory::register('max',   'AggregateMax');
AggregateFactory::register('count', 'AggregateCount');
AggregateFactory::register('avg',   'AggregateAvg');
AggregateFactory::register('first', 'AggregateFirst');
AggregateFactory::register('last',  'AggregateLast');
AggregateFactory::register('var',   'AggregateVar');
AggregateFactory::register('stdev', 'AggregateStdev');
AggregateFactory::register('null',  'Aggregate');



/**
*
* @package Amber
* @subpackage Aggregate
*
*/
class Aggregate
{
  /**
   * @access public
   * @var scalar
   */
  var $Value; 
  
  /**
   * constructor; resets class
   * @access public
   * @param anyType
   */
  function Aggregate()
  {
    $this->reset();
  }
  
  /**
   * adds value to aggregation; typically called from Report_EvaluateExpressions()
   * @access public
   * @param anyType
   */
  function addValue($value)
  {
  }
  
  /**
   * resets Aggregate; called from report
   * @access public
   * @param anyType
   */
  function reset()
  {
  }
}   
 
class AggregateSum  extends Aggregate
{
  /**
   * @access public
   * @param anyType
   */
  function addValue($value)
  {
    if ($value === '' || $value === null) {
    } else {
      $this->Value += $value;
    }  
    return $this->Value;
  }
  
  /**
   * @access public
   * @param anyType
   */
  function reset()
  {
    $this->Value = null;
  }
}

class AggregateCount  extends Aggregate
{
  /**
   * @access public
   * @param anyType
   */
  function addValue($value)
  {
    if ($value === '' || $value === null) {
    } else {
      $this->Value ++;
    }  
    return $this->Value;
  }
  
  /**
   * @access public
   * @param anyType
   */
  function reset()
  {
    $this->Value = 0;
  }
}

class AggregateAvg extends Aggregate
{
  /**
   * @access public
   * @param anyType
   */
  function addValue($value)
  {
    if ($value === '' || $value === null) {
    } else {
      $this->_cnt ++;
      $this->_sum += $value;
      $this->Value = $this->_sum / $this->_cnt;
    }  
    return $this->Value;
  }
  
  /**
   * @access public
   * @param anyType
   */
  function reset()
  {
    $this->_cnt = 0;
    $this->_sum = 0;
    $this->Value = null;
  }
}

class AggregateMin extends Aggregate
{
  /**
   * @access public
   * @param anyType
   */
  function addValue($value)
  {
    if ($value === '' || $value === null) {
    } elseif (!$this->Value) {
      $this->Value = $value;
    } elseif ($this->Value > $value) {
      $this->Value = $value;
    }    
    return $this->Value;
  }
  
  /**
   * @access public
   * @param anyType
   */
  function reset()
  {
    $this->Value = null;
  }
}

class AggregateMax extends Aggregate
{
  /**
   * @access public
   * @param anyType
   */
  function addValue($value)
  {
    if ($value === '' || $value === null) {
    } elseif (!$this->Value) {
      $this->Value = $value;
    } elseif ($this->Value < $value) {
      $this->Value = $value;
    }    
    return $this->Value;
  }
  
  /**
   * @access public
   * @param anyType
   */
  function reset()
  {
    $this->Value = null;
  }
}

class AggregateFirst extends Aggregate
{
  /**
   * @access public
   * @param anyType
   */
  function addValue($value)
  {
    if (!$this->called) {
       $this->called = true;
       $this->Value = $value;
    }   
    return $this->Value;
  }
  
  /**
   * @access public
   * @param anyType
   */
  function reset()
  {
    $this->called = false;
    $this->Value = null;
  }
}

class AggregateLast extends Aggregate   // not much use....
{
  /**
   * @access public
   * @param anyType
   */
  function addValue($value)
  {
    $this->Value = $value;
    return $this->Value;
  }
  
  /**
   * @access public
   * @param anyType
   */
  function reset()
  {
    $this->Value = null;
  }
}  

class AggregateVar extends Aggregate
{
  /**
   * @access public
   * @param anyType
   */
  function addValue($value)
  {
    if ($value === '' || $value === null) {
    } else {
      $this->_cnt ++;
      $this->_sum += $value;
      $this->_sum2 += $value * $value;
      if ($this->_cnt > 1) {
        $varN = (($this->_sum2 / $this->_cnt) - pow($this->_sum / $this->_cnt, 2)); 
        $this->Value = $varN / ($this->_cnt - 1) * $this->_cnt;
      }  
    }  
    return $this->Value;
  }
  
  /**
   * @access public
   * @param anyType
   */
  function reset()
  {
    $this->_cnt = 0;
    $this->_sum = 0;
    $this->_sum2 = 0;
    $this->Value = null;
  }
}

class AggregateStdev extends Aggregate
{
  /**
   * @access public
   * @param anyType
   */
  function addValue($value)
  {
    if ($value === '' || $value === null) {
    } else {
      $this->_cnt ++;
      $this->_sum += $value;
      $this->_sum2 += $value * $value;
      if ($this->_cnt > 1) {
        $varN = (($this->_sum2 / $this->_cnt) - pow($this->_sum / $this->_cnt, 2)); 
        $this->Value = sqrt($varN / ($this->_cnt - 1) * $this->_cnt);
      }
    }  
    return $this->Value;
  }
  
  /**
   * @access public
   * @param anyType
   */
  function reset()
  {
    $this->_cnt = 0;
    $this->_sum = 0;
    $this->_sum2 = 0;
    $this->Value = null;
  }
}
