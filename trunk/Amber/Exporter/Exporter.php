<?php

/**
 *
 * @package Amber
 * @subpackage Exporter
 *
 */

//require_once 'ExporterFactory.php';

ExporterFactory::register('null', 'Exporter');

define ("BorderCheat", 20);  // default value of Exporter->SectionSlip

/**
 *
 * @package Amber
 * @subpackage Exporter
 *
 */
class Exporter
{
/// Public Properties
  var $SectionSlip;    // width of area in twips by which controls may draw across section borders

/// Private Properties
  var $type = 'null';
  var $_docTitle;
  var $_section;
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
  function startReport(&$report, $asSubreport = false)
  {
    $this->_start = microtime();
    $this->_asSubreport = $asSubreport;
    $this->_base =& $this->getExporterBasicClass($report->layout, !$asSubreport);
    if (!$report->ignoreSectionSlip) {
      $this->SectionSlip = BorderCheat; 
    } else {
      $this->SectionSlip = 0;
    }
    $report->_Code->SectionSlip =& $this->SectionSlip;    
    $this->startReportSubExporter($report, $asSubreport);
  }

  function endReport(&$report)
  {
    //$this->dump('Exec time: ' . microtime_diff($this->_start, microtime()));
    $this->endReportSubExporter($report);
  }

  // Section
  function sectionPrintDesignHeader($text='') {}

  
  

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
  function startReportSubExporter(&$report, $asSubreport = false)
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
 
  function bufferStart()
  {
    $this->_base->bufferStart();
  }

  function bufferEnd()
  {
    return $this->_base->bufferEnd();
  }
  
  function Bookmark($txt, $level=0)
  {
    # only realized in pdf at the Moment.
    
  }
  

}

?>
