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
  }

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
    if ($this->layout->asSubReport) {
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
  
  function Bookmark($txt,$level=0,$y=0)
  {
    $this->_exporter->Bookmark($txt, $level, $y, $this->page(), $this->layout->getposYinPage());
  }
  
  function newPage()
  {
    $this->layout->fillRestOfPage();
  }

  
///////////////////////////
//
// ex mayflower: report part
//
///////////////////////////

      
  function page()
  {
    return $this->layout->pageNo + 1;
  }
}







class pageLayout
{
  var $unit;          //unit in pt
  var $designMode;    // bool: I am designmode  (no page-header/-footer)
  
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

  function pageLayout(&$report, $asSubReport, $designMode, $continous=false)
  { 
    $this->designMode = $designMode;
    $this->asSubReport = $asSubReport;   
    if ($this->asSubReport) {
      $this->noAutoPageY = true;
      $this->noAutoPageX = true;
      $this->noMargins = true;
      $this->noHeadFoot = true;
    }    
    if ($this->designMode) {
      $this->noHeadFoot = true;
    }    
    if ($continous) {
      $this->noAutoPageY = true;
      $this->noAutoPageX = true;
    }
    
    $this->unit = 1/20;
    $this->reportWidth = $report->Width;
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
    
    if ($this->noAutoPageX) {
      $this->printWidth  = $this->reportWidth; 
      $this->pagesHorizontal = 1;
    } else {
      $this->printWidth  = ($this->paperWidth - $this->leftMargin - $this->rightMargin); //width of printable area of page (w/o morgins)
      $this->pagesHorizontal = floor($this->reportWidth / $this->printWidth) + 1; // No of pages needed to print report
    }

    if (!$this->noAutoPageY) {
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
    if ($this->noAutoPageY) {
      $this->pagePosY = $this->posY;
    } else {  
      $this->pagePosY = $this->pageNo * $this->printHeight;
    }  
  }
  
  function getPageWithOffset($offset)
  {
    if ($this->noAutoPageY) {
      if ($this->pageNo < 0) {
        return 0;
      } else {
        return $this->pageNo + $this->newpage;
      }    
    } else { 
      return floor(($offset + $this->posY) / $this->printHeight);
    }  
  }
  
  function posYinPage()
  {
    return $this->posY - $this->pagePosY;
  }  
  
  function fillrestOfPage()
  {
    $this->newpage = 1;
    if (!$this->noAutoPageY) {
      $this->posY = $this->pagePosY + $this->printHeight;  //move posY to end of page (fill rest of page with space)
    } else {
      $this->printHeight = $this->posY - $this->pagePosY;  //set page size to actual position 
    }  
  }
  
  function addHeight($height)
  {
    $this->posY += $height;
  }  
  
  function paperHeight()
  {
    if ($this->noAutoPageY) {
      return $this->topMargin + $this->bottomMargin + $this->pageHeaderHeight + $this->pageFooterHeight + ($this->posY - $this->pagePosY);
    } else {
      return $this->paperHeight;
    }
  }
  
  function newPageAvoidsSectionSplit($sectionHeight)  // end of section will be on same page, wether with or without pagebreak
  {
    if ($this->noAutoPageY) {
      return false;
    } else {  
      $startNewPage =  (floor($this->posY / $this->printHeight) + 1) * $this->printHeight;
      $endPageWithoutNewPage = floor(($this->posY + $sectionHeight) / $this->printHeight);
      $endPageWithNewPage = floor(($startNewPage + $sectionHeight) / $this->printHeight);
      return ($endPageWithoutNewPage == $endPageWithNewPage);
    }
  }

}