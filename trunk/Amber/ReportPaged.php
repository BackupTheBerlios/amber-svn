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
  var $mayflower;

  /**
   * @access private
   */
  function _startReport($isSubreport, $isDesignMode)
  {
    $this->_asSubReport = $isSubreport;
    if (!isset($this->_exporter)) {
      return;
    }
    $this->layout =& new pageLayout($this, $isSubreport, $isDesignMode);
    $this->_exporter->startReport($this, $isSubreport, $isDesignMode);
    $this->mayflower=& $this->_exporter->mayflower;
    if ($isSubreport) {
      $this->mayflower->subReportPush();
      $this->_exporter->startcomment("StartSubreport"); // remove this!!!
    } else {  
      $this->mayflower->StartReportBuffering();
    }
  }
  
  /**
   * @access private
   */
  function _endReport()
  {
    if (!isset($this->_exporter)) {
      return;
    }
    if ($this->_asSubReport) {
      $this->newPage();
      $this->_exporter->comment("EndSubreport");
      return $this->mayflower->subReportPop();
    } else {
      if (!$this->layout->designMode) {
        $this->_printNormalSection($this->PageFooter);
      }  
   
      $this->mayflower->endReportBuffering();
    
      $firstPage = true;  //first page is out
  
      $endPageX = floor($this->layout->_reportWidth / $this->layout->printWidth);
      foreach(array_keys($this->mayflower->reportPages) as $pageY) {
        for($pageX = 0; $pageX <= $endPageX; $pageX++) {
          if (!$firstPage) {
            $this->_exporter->_pdf->AddPage();
          }
          $firstPage = false;
  
          $this->outPageHeader($pageY, $pageX, $this->_exporter->_pdf);  
          $this->outPage($pageY, $pageX, $this->_exporter->_pdf);  
          $this->outPageFooter($pageY, $pageX, $this->_exporter->_pdf);  
        }
      }
    }
    $this->_exporter->endReport($this);
  }

  function page()
  {
    return $this->mayflower->page();
  }

  function newPage()
  {
    $this->mayflower->newPage();
  }

  function outPageHeader($pageY, $pageX, &$exporter)
  {
    $x = $this->layout->leftMargin;
    $y = $this->layout->topMargin;
    $w = $this->layout->printWidth;
    $h = $this->layout->pageHeaderHeight;
    $deltaX = $this->layout->leftMargin - $pageX * $this->layout->printWidth;
    $deltaY = $pageY * $this->layout->printHeight - $y;
    $exporter->outWindowRelative($deltaX, $deltaY, $x, $y, $w, $h, $this->mayflower->reportPages[$pageY]['Head']);
  }
  function outPage($pageY, $pageX, &$exporter)
  {
    $x = $this->layout->leftMargin;
    $y = $this->layout->topMargin + $this->layout->pageHeaderHeight;
    $w = $this->layout->printWidth;
    $h = $this->layout->printHeight;
    $deltaX = $this->layout->leftMargin - $pageX * $this->layout->printWidth;
    $deltaY = $pageY * $this->layout->printHeight - $y;
    $exporter->outWindowRelative($deltaX, $deltaY, $x, $y, $w, $h, $this->mayflower->reportPages[$pageY]['']);
  }
  function outPageFooter($pageY, $pageX, &$exporter)
  {
    $x = $this->layout->leftMargin;
    $y = $this->layout->topMargin + $this->layout->pageHeaderHeight + $this->layout->printHeight;
    $w = $this->layout->printWidth;
    $h = $this->layout->pageFooterHeight;
    $deltaX = $this->layout->leftMargin - $pageX * $this->layout->printWidth;
    $deltaY = $pageY * $this->layout->printHeight - $y;
    $exporter->outWindowRelative($deltaX, $deltaY, $x, $y, $w, $h, $this->mayflower->reportPages[$pageY]['Foot']);
  }

}


class pageLayout
{
  var $unit;          //unit in pt
  var $designMode;    // bool: I am designmode  (no page-header/-footer)
  
  var $paperwidth;
  var $paperheight;
  
  var $rightMargin;
  var $leftMargin;
  var $topMargin;
  var $bottomMargin;
  
  var $pageHeaderHeight;
  var $pageFooterHeight;
  
  var $reportWidth;
  
  //////////////////////
  // 'calculated' fields
  /////////////////////
  var $printWidth;
  var $printHeight;

  function pageLayout(&$report, $asSubReport, $designMode)
  { 
    $this->designMode = $designMode;
    $this->asSubReport = $asSubReport;   
    $this->unit = 1/20;
    $this->reportWidth = $report->Width;
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

