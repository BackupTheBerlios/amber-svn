<?php


/**
 *
 * @package PHPReport
 * @subpackage ReportEngine
 *  this class handles paged report types 
 */

class ReportSubReport extends ReportPaged
{
  function _startReport($isSubreport)
  {
    parent::_startReport($isSubreport);

    $this->subReportStartBuffer();
    $this->_exporter->comment("StartSubreport"); // remove this!!!
  }
  
    /** 
   * @access private
   */
  function _endReport()
  {
    #$this->newPage();
    $this->_exporter->comment("EndSubreport");
    $this->subReportBuff = $this->subReportEndBuffer(); //a real copy
  }
  
  function _startSection(&$section, $width)
  {
    $this->_exporter->bufferStart();
    $this->_exporter->comment('Start Section:');
  }  
 
  function _endSection(&$section, $height)
  {
    $this->_exporter->comment("end Subreport-Body-Section:2\n");
    $buff = $this->_exporter->bufferEnd();

    $this->outSection(1, $this->posY, $height, &$buff, &$section);

    $this->posY += $height;
  }
  
///////////////////////////
//
// ex mayflower: subReport part
//
///////////////////////////
  
  
  
  function subReportStartBuffer()
  {
    $this->_exporter->bufferStart();
  }
 
  function subReportEndBuffer()
  {
    return $this->_exporter->bufferEnd();
  }



}
