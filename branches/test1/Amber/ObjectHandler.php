<?php

/**
 *
 * @package PHPReport
 * @subpackage ObjectHandler
 *
 */

/**
 *
 * @package PHPReport
 * @subpackage ObjectHandler
 * Singleton ObjectHandler: reference objects by handle instead direct ref 
 * This avoids weird dumps of circular references 
 */
class ObjectHandler
{
  //////////////////////////////////////////////////////////////////
  // PUBLIC PROPERTIES
  //////////////////////////////////////////////////////////////////
  

  //////////////////////////////////////////////////////////////////
  // PRIVATE PROPERTIES
  //////////////////////////////////////////////////////////////////
  var $_list;
  var $_id = 0;

  //////////////////////////////////////////////////////////////////
  // PUBLIC METHODS
  //////////////////////////////////////////////////////////////////

  /**
   *
   * @access public
   * @param  &object object to register
   * @return integer handle of registered object
   *
   */
  function getHandle(&$obj)
  {
    $me =& ObjectHandler::getInstance();
    $me->_id ++;
    $me->_list[$me->_id] =& $obj;
    return $me->_id;
  }
  
  /**
   *
   * @access public
   * @param  integer handle of object to return
   * @return &object 
   *
   */
  function &getObject($handle)
  {
    $me =& ObjectHandler::getInstance();
    return $me->_list[$handle];
  }       
  
  
  //////////////////////////////////////////////////////////////////
  // PRIVATE METHODS
  //////////////////////////////////////////////////////////////////

  function &getInstance()
  {
    static $instance = null;

    if (is_null($instance)) {
      $instance = new ObjectHandler();
    }

    return $instance;
  }
}
