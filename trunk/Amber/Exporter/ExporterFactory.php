<?php

/**
 *
 * @package Amber
 * @subpackage Exporter
 *
 */

include_once 'Exporter.php';
include_once 'ExporterHtml.php';
include_once 'ExporterTypo3.php';
include_once 'ExporterPdf.php';

/**
 *
 * @package Amber
 * @subpackage Exporter
 * @static
 *
 */
class ExporterFactory
{
  /**
   * @access private
   * @var array
   */
  var $_classList;

  /**
   * @static
   * @access private
   * @return ExporterFactory Reference to singleton
   */
  function &getInstance()
  {
    static $instance = null;

    if (is_null($instance)) {
      $instance = new ExporterFactory();
      $instance->_classList = array();
    }

    return $instance;
  }

  /**
   * @static
   * @access public
   * @param string Exporter type string
   * @return Exporter
   */
  function &create($type)
  {
    $factory =& ExporterFactory::getInstance();
    $className = $factory->_classList[$type];
    if (!$className) {
      $className = $factory->_classList['null'];
    }

    $ex =& new $className;
    $ex->createdAs = $type;

    return $ex;
  }

  /**
   * Create association between an exporter type string and the class which
   * will be responsible to handle output.
   *
   * @static
   * @access public
   * @param string Exporter type string
   * @param string Name of the class which must be instantiated for this type of exporter
   */
  function register($type, $className)
  {
    $instance =& ExporterFactory::getInstance();

    if (array_key_exists($type, $instance->_classList)) {
      Amber::showError('Error', 'Duplicate exporter type: "' . $type . '"');
      die();
    }
    $instance->_classList[$type] = $className;
  }
}

?>