<?php

/**
 *
 * @package PHPReport
 * @subpackage Controls
 *
 */

include_once 'Controls.php';

/**
 *
 * @package PHPReport
 * @subpackage Controls
 * @static
 *
 */
class ControlFactory
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
   * @return ControlFactory reference to singleton
   */
  function &getInstance() {
    static $instance = null;

    if (is_null($instance)) {
      $instance = new ControlFactory();
    }
    return $instance;
  }

  /**
   * @access public
   * @param string
   * @param array Array generated from XML
   * @return Control
   */
  function create($type, &$data)
  {
    $factory =& ControlFactory::getInstance();
    $className = $factory->_classList[$type];
    if (!$className) {
      $className = $factory->_classList['null'];
    }
    // if classname is still invalid
    if (!class_exists($className)) {
      // FIXME: raise error
      return false;
    }

    $ctl =& new $className();
    $ctl->setProperties($data);

    return $ctl;
  }

  /**
   * @access public
   * @param string
   * @param string
   */
  function register($type, $className)
  {
    $instance =& ControlFactory::getInstance();
    $instance->_classList[$type] = $className;
  }
}

?>
