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
  function _startReport($isSubreport, $isDesignMode)
  {
    parent::_startReport($isSubreport, $isDesignMode);
    
    $this->posY = 0;
  }
  
  /** 
   * @access private
   */
  function _endReport()
  {
    if (!$this->layout->designMode) {
      $this->_printNormalSection($this->PageFooter);
    }  
 
    $this->endReportBuffering();
    $this->posY = 0;

    $firstPage = true;  //first page is out
    $endPageX = floor($this->layout->_reportWidth / $this->layout->printWidth);
    foreach(array_keys($this->reportPages) as $pageY) {
      for($pageX = 0; $pageX <= $endPageX; $pageX++) {
        if (!$firstPage) {
          $this->_exporter->AddPage();
        }
        $firstPage = false;

        $this->outPageHeader($pageY, $pageX);  
        $this->outPage($pageY, $pageX);  
        $this->outPageFooter($pageY, $pageX);  
      }
    }
    $this->_exporter->endReport($this);
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
    $this->_exporter->startSection($section, $width, $buffer);
    $section->sectionStartBuffer($this->_exporter);
    $this->_exporter->comment('Start Section:');
  }  

  function _endSection(&$section, $height, &$buffer)
  {
    if (!$section->_PagePart) {
      $this->endNormalSection($section, $height, $section->KeepTogether);
    } elseif ($this->layout->designMode) {
      $this->endNormalSection($section, $height, false);
    } elseif ($section->_PagePart == 'Foot') {
      $this->pageFooterEnd($section);
    } else {
      $this->pageHeaderEnd($section);
    }
    $this->_exporter->endSection($section, $height, $buffer);
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
    $secBuff = $section->sectionEndBuffer($this->_exporter);
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
        }
        $this->setPageIndex($page);
        $this->printPageHeader();
      }
      $this->reportStartPageBody();
      if ($this->_exporter->DesignMode) {
        $this->_exporter->outSectionStart(0, $this->posY, $this->layout->reportWidth, $sectionHeight, $section->BackColor);
        $this->_exporter->out($secBuff);
        $this->_exporter->outSectionEnd();
      } else {
        $this->_exporter->outSectionStart(0, $this->posY, $this->layout->reportWidth, $sectionHeight, $section->BackColor);
        $formatCount = $page - $startPage + 1;
        $section->_onPrint($cancel, $formatCount);
        if (!$cancel) {
          $this->_exporter->out($secBuff);
        }
        $this->_exporter->outSectionEnd();
      }      
    }
    $this->posY += $sectionHeight;
  }
  
  function outSection($x, $y, $w, $h, $backColor, &$secBuff)
  {
  }

  function pageHeaderEnd(&$section)
  {
   $buff = $section->sectionEndBuffer($this->_exporter);
   $this->reportStartPageHeader();
   $this->_exporter->_pageHeaderOrFooterEnd($this->actpageNo * $this->layout->printHeight, $this->layout->reportWidth, $this->layout->pageHeaderHeight, $buff);
  }

  function pageFooterEnd(&$section)
  {
    $buff = $section->sectionEndBuffer($this->_exporter);
    $this->reportStartPageFooter();
    $this->_exporter->_pageHeaderOrFooterEnd($this->actpageNo * $this->layout->printHeight, $this->layout->reportWidth, $this->layout->pageFooterHeight, $buff);
  }
  
  function printPageFooter()
  {
    if (!$this->layout->designMode) {  
      $this->_printNormalSection($this->PageFooter);
    }  
  }
  
  function printPageHeader()
  {
    if (!$this->layout->designMode) {  
      $this->_printNormalSection($this->PageHeader);
    }  
  }
  
  function Bookmark($txt,$level=0,$y=0)
  {
    $posYinPage = ($this->posY - ($this->actpageNo * $this->layout->printHeight));
    $this->_exporter->Bookmark($txt, $level, $y, $this->page(), $posYinPage, $this->inReport());
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

      
  function _setOutBuff()
  { 
    if ($this->inReport()) {
      $this->_exporter->setOutBuffer($this->reportPages[$this->actpageNo][$this->sectionType], "report page".$this->sectionType.$this->actpageNo);
    } else {
      $this->_exporter->unsetBuffer();
    }  
  }
  
  function page()
  {
    return $this->actpageNo + 1;
  }
  
  function setPageIndex($index)
  {    
    $this->actpageNo = $index;
    $this->_setOutBuff();
  }        
  
  function reportStartPageHeader()
  {
    $this->sectionType = 'Head';
    $this->_setOutBuff();
  }
    
  function reportStartPageBody()
  {
    $this->sectionType = '';
    $this->_setOutBuff();
  }
    
  function reportStartPageFooter()
  {
    $this->sectionType = 'Foot';
    $this->_setOutBuff();
  }  
   

  function endReportBuffering()
  {
    if (!$this->inReport()) {
      Amber::showError('Error', 'endReport: no report open');
      die();
    }  
    $this->actpageNo = -1;
    $this->_setOutBuff();
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
  }
}
