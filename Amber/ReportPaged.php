<?php


/**
 *
 * @package PHPReport
 * @subpackage ReportEngine
 *  this class handles paged report types 
 */

class reportPaged extends Report
{

  /**
   * @access private
   */
  function _startReport($isSubreport)
  {
    if (isset($this->_exporter)) {
      $this->_exporter->startReport($this, $isSubreport);
    }
  }

  /**
   * @access private
   */
  function _endReport()
  {
    if (isset($this->_exporter)) {
      $this->_exporter->endReport($this);
    }
  }

}