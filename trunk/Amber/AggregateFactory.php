<?php

/**
 *
 * @package Amber
 * @subpackage Aggregates
 *
 */

include_once 'Aggregate.php';

/**
 *
 * @package Amber
 * @subpackage Aggregates
 * @static
 *
 */
class AggregateFactory
{
  //////////////////////////////////////////////////////////////////
  // PRIVATE PROPERTIES
  //////////////////////////////////////////////////////////////////
  /**
   * @access private
   * @var array
   */
  var $_classList;

  //////////////////////////////////////////////////////////////////
  // PUBLIC METHODS
  //////////////////////////////////////////////////////////////////

  /**
   * @return AggregateFactory reference to singleton
   */
  function &getInstance()
  {
    static $instance = null;

    if (is_null($instance)) {
      $instance = new AggregateFactory();
    }

    return $instance;
  }

  /**
   * @access public
   * @param string
   * @return Aggregate
   */
  function &create($type)
  {
    // if classname is still invalid
    $factory =& AggregateFactory::getInstance();
    $className = $factory->_classList[$type];
    if (!$className) {
      Amber::showError('Error', 'Aggregate "' . $type . "\" not found. Valid types are:\n" . implode(', ', array_keys($factory->_classList)));
      return false;
    } else {
      return new $className();
    }
  }
  
  /**
   * @access public
   * @param string
   * @param string
   */
  function register($type, $className)
  {
    $instance =& AggregateFactory::getInstance();
    if (!class_exists($className)) {
      Amber::showError('Warning', 'Missing declaration for class "' . $className . '", aggregate type = ' . $type);
      return false;
    }
    $instance->_classList[$type] = $className;
  }
}

?>
