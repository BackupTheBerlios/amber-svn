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
   * @static
   * @access private
   * @return ExporterFactory Reference to singleton
   */
  function &getInstance()
  {
    static $instance = null;

    if (is_null($instance)) {
      $instance = new ExporterFactory();
    }

    return $instance;
  }

  /**
   * @static
   * @access public
   * @param string
   * @param Report
   * @return Exporter
   */
  function &create($type, &$report)
  {
    $factory =& ExporterFactory::getInstance();
    $className = $factory->_classList[$type];
    if (!$className) {
      $className = $factory->_classList['null'];
    }

    $ex =& new $className;
    $ex->_report =& $report;
    $ex->createdAs = $type;

    return $ex;
  }

  /**
   * Create association between an exporter type string and the class which
   * will be responsible to handle output.
   *
   * @static
   * @access public
   * @param string Unique type
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
