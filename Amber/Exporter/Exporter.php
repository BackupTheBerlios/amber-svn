<?php

/**
 *
 * @package PHPReport
 * @subpackage Exporter
 *
 */

//require_once 'ExporterFactory.php';

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
  var $_section;
  var $DesignMode = false;

  /**
   * @access public
   * @param bool
   */
  function setDesignMode($value)
  {
    $this->DesignMode = $value;
  }

  function setDocumentTitle($title)
  {
    $this->_docTitle = $title;
  }

  // Report
  function startReport(&$report, $asSubreport = false)
  {
    $this->_start = microtime();
    $this->_report =& $report;
    $this->_asSubreport = $asSubreport;
    $this->_blankPage = true;
    $this->_exporterInit();
  }

  function endReport(&$report)
  {
    $this->newPage();
    //$this->dump('Exec time: ' . microtime_diff($this->_start, microtime()));
    $this->_exporterExit();
  }

  // Section
  function startSection(&$section, $width, &$buffer)
  {
    $this->_sections[] =& $section;
  }

  function endSection(&$section, $height, &$buffer)
  {
    array_pop($this->_sections);
  }

  function sectionPrintDesignHeader($text='') {}

  // Page handling
  function newPage() {} // Close page, prepare a new one
  function page() {} // return page number

  // Call back functions
  function onPrint(&$cancel, $formatCount)
  {
    $top =& $this->_sections[count($this->_sections) - 1];
    if (!is_null($top)) {
      $top->_OnPrint($cancel, $formatCount);
    }
  }

  // Controls
  function setControlExporter(&$ctrl)
  {
    // set the exporter of Control $ctrl
    // do something like $ctrl->_exporter =& new ControlExporter;
    Amber::showError('Error', 'Abstract function called: Exporter::setControlExporter, type: ' . $this->type);
    die();
  }
  
  /*
   * This method will be called by Exporter::startReport(). This is the place where
   * exporters can do individual initialization
   *
   * @access protected
   * @return void
   */
  function _exporterInit()
  {
  }

  /*
   * This method will be called by Exporter::endReport(). This is the place where
   * exporters can do cleanup tasks after the report has been run and is about to exit
   *
   * @access protected
   * @return void
   */
  function _exporterExit()
  {
  }
}

?>
