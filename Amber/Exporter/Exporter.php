<?php

/**
 *
 * @package PHPReport
 * @subpackage Exporter
 *
 */

require_once 'ExporterFactory.php';

ExporterFactory::register('null', 'Exporter');

/**
 *
 * @package PHPReport
 * @subpackage Exporter
 *
 */
class Exporter
{
  var $type = 'null';
  var $_docTitle;
  var $_report;
  var $DesignMode = false;

  function setDesignMode()
  {
    $this->DesignMode = true;
  }  
  
  function setDocumentTitle($title)
  {
    $this->_docTitle = $title;
  }

  // Report
  function startReport(&$report)
  {
    $this->_start = microtime();
    $this->_report =& $report;
  }

  function endReport(&$report)
  {
    $this->newPage();
    $this->dump('Exec time: ' . microtime_diff($this->_start, microtime()));
  }

  // Section
  function startSection(&$section) {}
  function endSection(&$section) {}
  function sectionPrintDesignHeader($text='') {}

  // Page handling
  function newPage() {} // Close page, prepare a new one
  function page() {} // return page number

  // Controls
  function setControlExporter($ctrl)
  {
    // set the exporter of Control $ctrl
    // do something like $ctrl->_exporter =& new ControlExporter;
    die('Abstract function called: Exporter::setControlExporter, type: ' . $this->type);
  }

  // Helper functions
  function dump($var)
  {
    print_r($var, 1);
  }

}

?>
