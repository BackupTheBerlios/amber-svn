<?php

require_once 'Report.php';

/**
 * This class handles paged report types
 *
 * @package Amber
 * @subpackage ReportEngine
 * 
 */

class ReportPaged extends Report
{
  var $layout;


  /** 
   * @access private
   */
  function _endReport()
  {
    $this->closePage();
    $this->_exporter->endReport($this);
  }

  function closePage()
  {
    if ($this->layout->pageNo >= 0) {
      if (!$this->layout->noHeadFoot) {  
        $this->_printNormalSection($this->PageFooter);
      }  
      $this->layout->body = $this->_exporter->bufferEnd();
      $this->outPage();
    }  
  }
  
  function outPage()
  {
    if ($this->asSubReport) {
      $this->subReportBuff = $this->layout->body;
    } else {
      for($pageX = 0; $pageX <= $this->layout->pagesHorizontal - 1; $pageX++) {
        $this->_exporter->startPage($this->layout->paperHeight());
        $deltaX = $pageX * $this->layout->printWidth;
        $x = $this->layout->leftMargin;
        $w = $this->layout->printWidth;
  
        $y = $this->layout->topMargin;
        $h = $this->layout->pageHeaderHeight;
        $this->_exporter->outWindowRelative($deltaX, $x, $y, $w, $h, $this->layout->header);
  
        $y = $this->layout->topMargin + $this->layout->pageHeaderHeight;
        $h = $this->layout->printHeight;
        $this->_exporter->outWindowRelative($deltaX, $x, $y, $w, $h, $this->layout->body);
  
        $y = $this->layout->topMargin + $this->layout->pageHeaderHeight + $this->layout->printHeight;
        $h = $this->layout->pageFooterHeight;
        $this->_exporter->outWindowRelative($deltaX, $x, $y, $w, $h, $this->layout->footer);
        $this->_exporter->endPage();
      }
    }
  }
  
  function _startSection(&$section, $width)
  {
    $this->_exporter->bufferStart();
  }  

  function _endSection(&$section, $height)
  {
    if ($this->printHeadFootAsNormalSection) {
      $this->endNormalSection($section, $height, !$this->ignoreKeepTogether);
    } elseif ($section->_PagePart == '') {
      $this->endNormalSection($section, $height, $section->KeepTogether);
    } elseif ($section->_PagePart == 'Foot') {
      $this->pageFooterEnd($section);
    } else {
      $this->pageHeaderEnd($section);
    }
  }
  
  function endNormalSection(&$section, $sectionHeight, $keepTogether)
  {
    $secBuff = $this->_exporter->bufferEnd();
    if ($keepTogether) {                 // section doesn't fit on page and keepTogether
      if ($this->layout->newPageAvoidsSectionSplit($sectionHeight)) {
        $this->newPage();
      }
    }
    $startPage = $this->layout->getPageWithOffset(0);
    $endPage   = $this->layout->getPageWithOffset($sectionHeight);

    for ($page = $startPage; $page <= $endPage; $page++) {
     if (($page <> $this->layout->pageNo)) {
        $this->closePage();
        $this->layout->nextPage();
        $this->_exporter->bufferStart();
        $this->printPageHeader();
      }
      $this->outSection($page - $startPage + 1, $this->layout->posYinPage(), $sectionHeight, $secBuff, $section);
    }
    $this->layout->addHeight($sectionHeight);
  }
  
  function pageHeaderEnd(&$section)
  {
    $buff = $this->_exporter->bufferEnd();
    $this->_exporter->bufferStart();
    $this->outSection(1, 0, $this->layout->pageHeaderHeight, $buff, $section);
    $this->layout->header = $this->_exporter->bufferEnd();
  }

  function pageFooterEnd(&$section)
  {
    $buff = $this->_exporter->bufferEnd();
    $this->_exporter->bufferStart();
    $this->outSection(1, 0, $this->layout->pageFooterHeight, $buff, $section);
    $this->layout->footer = $this->_exporter->bufferEnd();
  }
  
  function printPageFooter()
  {
    if (!$this->layout->noHeadFoot) {
      $this->_printNormalSection($this->PageFooter);
    }  
  }
  
  function printPageHeader()
  {
    if (!$this->layout->noHeadFoot) {
      $this->_printNormalSection($this->PageHeader);
    }  
  }

  function newPage()
  {
    $this->layout->fillRestOfPage();
  }
  
  function newPageIfDirty()
  {
    $this->layout->fillRestOfPageIfDirty();
  }
  
  /**
   *
   * @access public
   * @return int current page number
   *
   */
  function page()
  {
    return $this->layout->pageNo + 1;
  }
}


/**
 *
 * @package Amber
 * @subpackage ReportEngine
 *
 */
class pageLayout
{
  var $unit;          //unit in pt
  
  var $paperWidth;
  var $paperHeight;
  
  var $rightMargin;
  var $leftMargin;
  var $topMargin;
  var $bottomMargin;
  
  var $pageHeaderHeight;
  var $pageFooterHeight;
  
  var $reportWidth;
  

  var $header;
  var $body;
  var $footer;
  var $pageNo = -1;
  var $pagePosY;
  var $posY;
  
  //////////////////////
  // 'calculated' fields
  /////////////////////
  var $printWidth;
  var $printHeight;

  
  
  
  function pageLayout(&$report)
  { 
    $this->noAutoPage = $report->noAutoPage;
    $this->noMargins = $report->noMargins;
    $this->noHeadFoot = $report->noHeadFoot;
    
    $this->unit = 1/20;
    $this->reportWidth = $report->Width + 30;     //cheat: add 1.5pt in width for border
    if ($report->Orientation == 'portrait') {
      $this->paperWidth = $report->PaperWidth;
      $this->paperHeight = $report->PaperHeight;
    } else {
      $this->paperWidth = $report->PaperHeight;
      $this->paperHeight = $report->PaperWidth;
    }  

    if ($this->noMargins) {
      $this->rightMargin = 0;
      $this->leftMargin = 0;
      $this->topMargin = 0;
      $this->bottomMargin = 0;
      $this->paperWidth = $this->paperWidth - $report->LeftMargin - $report->RightMargin;
      $this->paperHeight = $this->paperHeight - $report->TopMargin - $report->BottomMargin;
    } else {
      $this->rightMargin = $report->RightMargin;
      $this->leftMargin = $report->LeftMargin;
      $this->topMargin = $report->TopMargin;
      $this->bottomMargin = $report->BottomMargin;
    }
      
    if ($this->noHeadFoot) {
      $this->pageHeaderHeight = 0;
      $this->pageFooterHeight = 0;
    } else {
      $this->pageHeaderHeight = $report->PageHeader->Height;
      $this->pageFooterHeight = $report->PageFooter->Height;
    }
    
    if ($this->noAutoPage) {
      $this->printWidth  = $this->reportWidth; 
      $this->pagesHorizontal = 1;
    } else {
      $this->printWidth  = ($this->paperWidth - $this->leftMargin - $this->rightMargin); //width of printable area of page (w/o morgins)
      if ($this->printWidth <= 0) { 
        $msg = 'paper width: ' . (int)$this->paperWidth . '; left margin:' . (int)$this->leftMargin . '; right margin: ' . (int)$this->rightMargin . ';';
        die(Amber::showError('Error: Width of printable area too small', $msg, true));
      }  
      $this->pagesHorizontal = floor($this->reportWidth / $this->printWidth) + 1; // No of pages needed to print report
      $this->printHeight = ($this->paperHeight - $this->topMargin - $this->bottomMargin - $this->pageHeaderHeight - $this->pageFooterHeight); //height of printable area of page (w/o margins)
    }

    $this->pageNo = -1;
  }
  
  function nextPage()
  {
    $this->header = '';
    $this->body = '';
    $this->footer = '';
    $this->pageNo++;
    $this->newpage = 0;
    if ($this->noAutoPage) {
      $this->pagePosY = $this->posY;
    } else {  
      $this->pagePosY = $this->pageNo * $this->printHeight;
    }  
  }
  
  function getPageWithOffset($offset)
  {
    if (!$this->noAutoPage) {
      return floor(($offset + $this->posY) / $this->printHeight);
    } elseif ($this->pageNo < 0) {
      return 0;
    } else {
      return $this->pageNo + $this->newpage;
    }  
  }
  
  function posYinPage()
  {
    return $this->posY - $this->pagePosY;
  }  
  
  function fillRestOfPage()
  {
    $this->newpage = 1;
    if (!$this->noAutoPage) {
      $this->posY = $this->pagePosY + $this->printHeight + 1;  //move posY to end of page (fill rest of page with space)
                                                               // +1: make sure, we are on new page
    } else {
      $this->printHeight = $this->posY - $this->pagePosY;  //set page size to actual position 
    }
  }
  
  function fillRestOfPageIfDirty()
  {
    if ($this->pagePosY <> $this->posY) {
      $this->fillRestOfPage();
    }  
  }
  
  function addHeight($height)
  {
    $this->posY += $height;
  }  
  
  function paperHeight()
  {
    if ($this->noAutoPage) {
      return $this->topMargin + $this->bottomMargin + $this->pageHeaderHeight + $this->pageFooterHeight + ($this->posY - $this->pagePosY);
    } else {
      return $this->paperHeight;
    }
  }
  
  function newPageAvoidsSectionSplit($sectionHeight)  // end of section will be on same page, wether with or without pagebreak
  {
    if ($this->noAutoPage) {
      return false;
    } else {  
      $startNewPage =  (floor($this->posY / $this->printHeight) + 1) * $this->printHeight +1; // +1 to make sure, we are on a new page (strange rounding problem: X=3, floor(X)=2)
      $endPageWithoutNewPage = floor(($this->posY + $sectionHeight) / $this->printHeight);
      $endPageWithNewPage = floor(($startNewPage + $sectionHeight) / $this->printHeight);
      return ($endPageWithoutNewPage == $endPageWithNewPage);
    }
  }

}
