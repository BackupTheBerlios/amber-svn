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

  var $_base; // ref to pdf/html
  /**
   * @access public
   * @param bool
   */


  function setDocumentTitle($title)
  {
    $this->_docTitle = $title;
  }

  // Report
  function startReport(&$report, $asSubreport = false, $isDesignMode = false)
  {
    $this->_start = microtime();
    $this->_asSubreport = $asSubreport;
    $this->_base =& $this->getExporterBasicClass($report->layout, !$asSubreport);
    $this->startReportSubExporter($report, $asSubreport, $isDesignMode);
  }

  function endReport(&$report)
  {
    $this->newPage();
    //$this->dump('Exec time: ' . microtime_diff($this->_start, microtime()));
    $this->endReportSubExporter($report);
  }

  // Section
  function sectionPrintDesignHeader($text='') {}

  // Page handling
  function newPage() {} // Close page, prepare a new one
  function page() {} // return page number


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
  function startReportSubExporter(&$report, $asSubreport = false, $isDesignMode = false)
  {
  }

  /*
   * This method will be called by Exporter::endReport(). This is the place where
   * exporters can do cleanup tasks after the report has been run and is about to exit
   *
   * @access protected
   * @return void
   */
  function endReportSubExporter(&$report)
  {
  }
 
  function setOutBuffer(&$buff)
  {
    $this->_base->cache =& $buff;
    $this->_base->incache = true;
  }

  function &getOutBuffer()
  {
    return $this->_base->cache;
  }  
  
  function unsetBuffer()
  {
    unset($this->_base->cache);
    $this->_base->incache = false;
  }
}

?>
