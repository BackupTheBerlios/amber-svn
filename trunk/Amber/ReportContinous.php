<?php


/**
 *
 * @package PHPReport
 * @subpackage ReportEngine
 *  this class handles paged report types 
 */

class ReportContinous extends Report
{
  function _startReport($isSubreport, $isDesignMode)
  {
    parent::_startReport($isSubreport, $isDesignMode);
  }

  function _endReport()
  {
    $this->_exporter->endReport($this);
  }
  
  function _startSection(&$section, $width, &$buffer)
  {
    $this->_exporter->startSection($section, $width, $buffer);
  }  

  function _endSection(&$section, $height, &$buffer)
  {
    $this->_exporter->endSection($section, $height, $buffer);
  }  


  
  
  
  
  function sectionPrintDesignHeader($text)
  {
    $this->_exporter->sectionPrintDesignHeader($text);
  }

  function page()
  {
    return $this->_exporter->page();
  }
  
  function newPage()
  {
    $this->_exporter->newPage();
  }  

}
?>