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
  
  function _startSection(&$section, $width, &$buffer)
  {
    $section->sectionStartBuffer($this->_exporter);
    $this->_exporter->comment('Start Section:');
  }  
 
  function _endSection(&$section, $height, &$buffer)
  {
    $this->_exporter->comment("end Subreport-Body-Section:2\n");
    $buff = $section->sectionEndBuffer($this->_exporter);

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
    $this->NewBuffer = '';
    $this->OldBuffer =& $this->_exporter->getOutBuffer();
    $this->_exporter->setOutBuffer($this->NewBuffer);
  }
 
  function subReportEndBuffer()
  {
    $this->_exporter->setOutBuffer($this->OldBuffer);
    return $this->NewBuffer;
  }



}
