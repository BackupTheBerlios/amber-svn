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

    $this->_exporter->bufferStart();
  }
  
    /** 
   * @access private
   */
  function _endReport()
  {
    #$this->newPage();
    $this->subReportBuff = $this->_exporter->bufferEnd(); //a real copy
  }
  
  function _startSection(&$section, $width)
  {
    $this->_exporter->bufferStart();
  }  
 
  function _endSection(&$section, $height)
  {
    $buff = $this->_exporter->bufferEnd();

    $this->outSection(1, $this->posY, $height, &$buff, &$section);

    $this->posY += $height;
  }

}
