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


  function _startReport($isSubreport)
  {
    parent::_startReport($isSubreport);
    $this->actPage =& new WidePage($this->layout);
  }

  /** 
   * @access private
   */
  function _endReport()
  {
    if (!$this->designMode) {
      $this->_printNormalSection($this->PageFooter);
    }  

    $this->actPage->body = $this->_exporter->bufferEnd();
    $this->outPage($this->actPage);
    
    $this->_exporter->endReport($this);
  }

  function outPage(&$page)
  {
    for($pageX = 0; $pageX <= $this->layout->pagesHorizontal - 1; $pageX++) {
      $this->_exporter->AddPage();
      $deltaX = $pageX * $this->layout->printWidth;
      $x = $this->layout->leftMargin;
      $w = $this->layout->printWidth;

      $y = $this->layout->topMargin;
      $h = $this->layout->pageHeaderHeight;
      $this->_exporter->outWindowRelative($deltaX, $x, $y, $w, $h, $page->header);

      $y = $this->layout->topMargin + $this->layout->pageHeaderHeight;
      $h = $this->layout->printHeight;
      $this->_exporter->outWindowRelative($deltaX, $x, $y, $w, $h, $page->body);

      $y = $this->layout->topMargin + $this->layout->pageHeaderHeight + $this->layout->printHeight;
      $h = $this->layout->pageFooterHeight;
      $this->_exporter->outWindowRelative($deltaX, $x, $y, $w, $h, $page->footer);
    }
  }
  
  function _startSection(&$section, $width)
  {
    $this->_exporter->bufferStart();
    $this->_exporter->comment('Start Section:');
  }  

  function _endSection(&$section, $height)
  {
    if ($this->designMode) {
      $this->endNormalSection($section, $height, false);
    } elseif ($section->_PagePart == '') {
      $this->endNormalSection($section, $height, $section->KeepTogether);
    } elseif ($section->_PagePart == 'Foot') {
      $this->pageFooterEnd($section);
    } else {
      $this->pageHeaderEnd($section);
    }
  }
  
  
  function _newPageAvoidsSectionSplit($sectionHeight)           // end of section will be on same page, wether with or without pagebreak
  {
    $endPageWithoutNewPage = floor(($this->posY + $sectionHeight) / $this->layout->printHeight);
    $startNewPage =  (floor($this->posY / $this->layout->printHeight) + 1) * $this->layout->printHeight;
    $endPageWithNewPage = floor(($startNewPage + $sectionHeight) / $this->layout->printHeight);
    return ($endPageWithoutNewPage == $endPageWithNewPage);           // end of section will be on same page, wether with or without pagebreak
  }
  
  function endNormalSection(&$section, $sectionHeight, $keepTogether)
  {
    $this->_exporter->comment("end Body-Section:1\n");
    $secBuff = $this->_exporter->bufferEnd();
    if ($keepTogether) {                 // section doesn't fit on page and keepTogether
      if ($this->_newPageAvoidsSectionSplit($sectionHeight)) {
        $this->newPage();
      }
    }
    $startPage = floor($this->posY / $this->layout->printHeight);
    $endPage   = floor(($this->posY + $sectionHeight) / $this->layout->printHeight);

    for ($page = $startPage; $page <= $endPage; $page++) {
      if (($page <> $this->actPage->pageNo)) {
        if ($this->actPage->pageNo >= 0) {
          $this->printPageFooter();
          $this->actPage->body = $this->_exporter->bufferEnd();
          $this->outPage($this->actPage);
        }
        $this->actPage->nextPage();
        $this->_exporter->bufferStart();
        $this->printPageHeader();
      }
      $this->outSection($page - $startPage + 1, $this->posY - $this->actPage->posY, $sectionHeight, $secBuff, $section);
    }
    $this->posY += $sectionHeight;
  }
  
  function pageHeaderEnd(&$section)
  {
    $buff = $this->_exporter->bufferEnd();
    $this->_exporter->bufferStart();
    $this->outSection(1, 0, $this->layout->pageHeaderHeight, $buff, $section);
    $this->actPage->header = $this->_exporter->bufferEnd();
  }

  function pageFooterEnd(&$section)
  {
    $buff = $this->_exporter->bufferEnd();
    $this->_exporter->bufferStart();
    $this->outSection(1, 0, $this->layout->pageFooterHeight, $buff, $section);
    $this->actPage->footer = $this->_exporter->bufferEnd();
  }
  
  function printPageFooter()
  {
    if (!$this->designMode) {  
      $this->_printNormalSection($this->PageFooter);
    }  
  }
  
  function printPageHeader()
  {
    if (!$this->designMode) {  
      $this->_printNormalSection($this->PageHeader);
    }  
  }
  
  function Bookmark($txt,$level=0,$y=0)
  {
    $posYinPage = ($this->posY - $this->actpage->posY);
    $this->_exporter->Bookmark($txt, $level, $y, $this->page(), $posYinPage);
  }
  
  function newPage()
  {
    $this->posY = $this->actpage->posY + $this->layout->printHeight;
  }

  
///////////////////////////
//
// ex mayflower: report part
//
///////////////////////////

      
  function page()
  {
    return $this->actPage->pageNo + 1;
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
    $this->pagesHorizontal = floor($this->reportWidth / $this->printWidth) + 1; // No of pages needed to print report
  }
}


/**
 *
 * @package PHPReport
 * @subpackage ReportEngine
 * this class represents a 'wide' page with the parts header, body and footer
 *  
 */


class WidePage
{
  var $header;
  var $body;
  var $footer;
  
  var $layout;
  var $pageNo = -1;
  
  function WidePage(&$layout)
  {
    $this->layout =& $layout;
    $this->pageNo = -1;
  }  
  
  function nextPage()
  {
    $this->header = '';
    $this->body = '';
    $this->footer = '';
    $this->pageNo++;
    $this->posY = $this->pageNo * $this->layout->printHeight;
  }
}