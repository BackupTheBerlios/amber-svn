<?php

/**
 *
 * @package PHPReport
 * @subpackage ReportEngine
 *
 */

/**
 * Singleton ObjectHandler: reference objects by handle instead direct ref
 * This avoids weird dumps of circular references
 *
 * @package PHPReport
 * @subpackage ReportEngine
 * 
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
   * @param  mixed object to register
   * @return integer handle of registered object
   *
   */
  function getHandle(&$obj)
  {
    $me =& ObjectHandler::getInstance();
    $me->_id++;
    $me->_list[$me->_id] =& $obj;
    return $me->_id;
  }

  /**
   *
   * @access public
   * @param  integer handle of object to return
   * @return mixed
   *
   */
  function &getObject($handle)
  {
    $me =& ObjectHandler::getInstance();
    if (!isset($me->_list[$handle])) {
      Amber::showError('ObjectHandler::getObject()', 'Invalid handle: [' . $handle . ']');
      return null;
    }

    return $me->_list[$handle];
  }


  //////////////////////////////////////////////////////////////////
  // PRIVATE METHODS
  //////////////////////////////////////////////////////////////////

  /**
   *
   * @access private
   * @return ObjectHandler
   *
   */
  function &getInstance()
  {
    static $instance = null;

    if (is_null($instance)) {
      $instance = new ObjectHandler();
    }

    return $instance;
  }
}
