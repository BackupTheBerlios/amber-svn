<?php


/**
 *
 * @package PHPReport
 * @subpackage ReportEngine
 *  this class handles paged report types 
 */

class reportPaged extends Report
{
  var $layout;
  var $reportBuff;

  /**
   * @access private
   */
  function _startReport($isSubreport, $isDesignMode)
  {
    if (isset($this->_exporter)) {
      $this->layout =& new pageLayout($this, $isSubreport, $isDesignMode);
      if (!$isSubReport) {
        $this->reportBuff =& new reportBuff($this->layout);
      }
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

  function page()
  {
    return $this->reportBuff->page();
  }

  function newPage()
  {
    $this->reportBuff->newPage();
  }


}

class reportBuff
{
  var $reportPages;    // buffer when inReport
  var $actpageNo;      // pageNumber
  var $sectionType;    // 'Head', 'Foot' or ''
  
  var $pageLayout;
  
  var $posY;
  
  function reportBuff($layout)
  {
    $this->actpageNo = -1;
    $this->_report->layout = $layout; 
  }
  
  function out(&$s)
  {
    $this->reportPages[$this->actpageNo][$this->sectionType] .= $s . "\n";
  }
  
  function newPage()
  {
    $this->posY = ($this->actpageNo + 1) * $this->_report->layout->printHeight;
  }
  
  function page()
  {
    return $this->actpageNo + 1;
  }

}



class pageLayout
{
  var $unit;          //unit in pt 
  
  var $paperwidth;
  var $paperheight;
  
  var $rightMargin;
  var $leftMargin;
  var $topMargin;
  var $bottomMargin;
  
  var $pageHeaderHeight;
  var $pageFooterHeight;
  
  //////////////////////
  // 'calculated' fields
  /////////////////////
  var $printWidth;
  var $printHeight;

  function pageLayout(&$report, $asSubReport, $designMode)
  {    
    $this->unit = 1/20;
    if ($report->Orientation == 'portrait') {
      $this->paperWidth = $report->PaperWidth;
      $this->paperHeight = $report->PaperHeight;
    } else {
      $this->paperWidth = $report->PaperHeight;
      $this->paperHeight = $report->PaperWidth;
    }  
    #Amber::dump($size);
    if ($asSubReport) {
      $this->rightMargin = 0;
      $this->leftMargin = 0;
      $this->topMargin = 0;
      $this->bottomMargin = 0;
      $this->pageHeaderHeight = 0;
      $this->pageFooterHeight = 0;
    } else {  
      $this->rightMargin = $report->RightMargin;
      $this->leftMargin = $report->LeftMargin;
      $this->topMargin = $report->TopMargin;
      $this->bottomMargin = $report->BottomMargin;
      if ($designMode) {
        $this->pageHeaderHeight = 0;
        $this->pageFooterHeight = 0;
      } else {
        $this->pageHeaderHeight = $report->PageHeader->Height;
        $this->pageFooterHeight = $report->PageFooter->Height;
      }
    }
    $this->printWidth  = ($this->paperWidth - $this->leftMargin - $this->rightMargin); //width of printable area of page (w/o morgins)
    $this->printHeight = ($this->paperHeight - $this->topMargin - $this->bottomMargin - $this->pageHeaderHeight - $this->pageFooterHeight); //height of printable area of page (w/o morgins)
  }
}

