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
  var $actpageNo = -1;


  /** 
   * @access private
   */
  function _endReport()
  {
    if (!$this->designMode) {
      $this->_printNormalSection($this->PageFooter);
    }  

    $this->reportPages[$this->actpageNo][''] = $this->_exporter->bufferEnd();
    $this->endReportBuffering();
    
    $this->outPages();
    
    $this->_exporter->endReport($this);
  }

  function outPages()
  {
    $firstPage = true;  //first page is out
    foreach(array_keys($this->reportPages) as $pageY) {
      for($pageX = 0; $pageX <= $this->layout->pagesHorizontal - 1; $pageX++) {
        if (!$firstPage) {
          $this->_exporter->AddPage();
        }
        $firstPage = false;

        $this->outPageHeader($pageY, $pageX);  
        $this->outPage($pageY, $pageX);  
        $this->outPageFooter($pageY, $pageX);  
      }
    }
  }
  
  function outPageHeader($pageY, $pageX)
  {
    $x = $this->layout->leftMargin;
    $y = $this->layout->topMargin;
    $w = $this->layout->printWidth;
    $h = $this->layout->pageHeaderHeight;
    $deltaX = $this->layout->leftMargin - $pageX * $this->layout->printWidth;
    $deltaY = $pageY * $this->layout->printHeight - $y;
    $this->_exporter->outWindowRelative($deltaX, $deltaY, $x, $y, $w, $h, $this->reportPages[$pageY]['Head']);
  }
  function outPage($pageY, $pageX)
  {
    $x = $this->layout->leftMargin;
    $y = $this->layout->topMargin + $this->layout->pageHeaderHeight;
    $w = $this->layout->printWidth;
    $h = $this->layout->printHeight;
    $deltaX = $this->layout->leftMargin - $pageX * $this->layout->printWidth;
    $deltaY = $pageY * $this->layout->printHeight - $y;
    $this->_exporter->outWindowRelative($deltaX, $deltaY, $x, $y, $w, $h, $this->reportPages[$pageY]['']);
  }
  function outPageFooter($pageY, $pageX)
  {
    $x = $this->layout->leftMargin;
    $y = $this->layout->topMargin + $this->layout->pageHeaderHeight + $this->layout->printHeight;
    $w = $this->layout->printWidth;
    $h = $this->layout->pageFooterHeight;
    $deltaX = $this->layout->leftMargin - $pageX * $this->layout->printWidth;
    $deltaY = $pageY * $this->layout->printHeight - $y;
    $this->_exporter->outWindowRelative($deltaX, $deltaY, $x, $y, $w, $h, $this->reportPages[$pageY]['Foot']);
  }

  function _startSection(&$section, $width, &$buffer)
  {
    $this->_exporter->bufferStart();
    $this->_exporter->comment('Start Section:');
  }  

  function _endSection(&$section, $height, &$buffer)
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
      if (($page <> $this->actpageNo)) {
        if ($this->actpageNo >= 0) {
          $this->printPageFooter();
          $this->reportPages[$this->actpageNo][''] = $this->_exporter->bufferEnd();
        }
        $this->actpageNo = $page;
        $this->_exporter->bufferStart();
        $this->printPageHeader();
      }
      $this->outSection($page - $startPage + 1, $this->posY, $sectionHeight, $secBuff, $section);
    }
    $this->posY += $sectionHeight;
  }
  
  function pageHeaderEnd(&$section)
  {
   $buff = $this->_exporter->bufferEnd();
   $this->_exporter->bufferStart();
   $this->outSection(1, $this->actpageNo * $this->layout->printHeight, $this->layout->pageHeaderHeight, $buff, $section);
   $this->reportPages[$this->actpageNo]['Head'] = $this->_exporter->bufferEnd();
  }

  function pageFooterEnd(&$section)
  {
    $buff = $this->_exporter->bufferEnd();
    $this->_exporter->bufferStart();
    $this->outSection(1, $this->actpageNo * $this->layout->printHeight, $this->layout->pageFooterHeight, $buff, $section);
    $this->reportPages[$this->actpageNo]['Foot'] = $this->_exporter->bufferEnd();
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
    $posYinPage = ($this->posY - ($this->actpageNo * $this->layout->printHeight));
    $this->_exporter->Bookmark($txt, $level, $y, $this->page(), $posYinPage);
  }
  
  function newPage()
  {
    $this->posY = ($this->actpageNo + 1) * $this->layout->printHeight;
  }

  
///////////////////////////
//
// ex mayflower: report part
//
///////////////////////////

      
  function page()
  {
    return $this->actpageNo + 1;
  }
  
  function endReportBuffering()
  {
    $this->actpageNo = -1;
  }

  function inReport()
  {
    return ($this->actpageNo >= 0);
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
