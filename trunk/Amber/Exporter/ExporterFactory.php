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
   * @return ExporterFactory reference to singleton
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
   * @access public
   * @param string
   * @param string
   */
  function register($type, $className)
  {
    $instance =& ExporterFactory::getInstance();
    $instance->_classList[$type] = $className;
  }
}

?>
