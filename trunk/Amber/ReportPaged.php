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
  var $_pdf;       //fixme: rename this!

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
    $this->_pdf =& $this->_exporter->getExporterBasicClass($this->layout, !$isSubreport);
    $this->mayflower =& mayflower::getInstance($this->layout, $this->_pdf, !$isSubreport);
    $this->_exporter->startReport($this, $isSubreport, $isDesignMode);
    if ($isSubreport) {
      $this->mayflower->subReportPush();
      $this->_pdf->startcomment("StartSubreport"); // remove this!!!
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
      $this->_pdf->comment("EndSubreport");
      $this->subReportBuff = $this->mayflower->subReportPop(); //a real copy
      return;
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

  function _startSection(&$section, $width, &$buffer)
  {
    $this->_exporter->startSection($section, $width, $buffer);
    $this->mayflower->sectionPush();
    $this->_pdf->comment('Start Section:' . ($this->mayflower->getSectionIndexForCommentOnly()));
    $this->_pdf->fillBackColorInWindow($section->BackColor, $section->_report->Width, $section->Height);
  }  

  function _endSection(&$section, $height, &$buffer)
  {
    if (!$section->_PagePart) {
      $this->endNormalSection($height, $section->KeepTogether);
    } elseif ($this->layout->designMode) {
      $this->endNormalSection($height, false);
    } elseif ($section->_PagePart == 'Foot') {
      $this->pageFooterEnd();
    } else {
      $this->pageHeaderEnd();
    }
    $this->_exporter->endSection($section, $height, $buffer);
  }
  
  function sectionPrintDesignHeader($text)
  {
    $this->mayflower->sectionPush();
    $this->_pdf->comment('Start Section:' . ($this->mayflower->getSectionIndexForCommentOnly()));
    $height = 240; //12pt

    $this->_pdf->_backColor(0xDDDDDD);
    $this->_pdf->_textColor(0x000000);
    $this->_pdf->SetFont('helvetica', '', 8);
    $this->_pdf->SetLineWidth(10); // 0.5pt
    $this->_pdf->_borderColor(0x000000);

    $border = 1;
    $this->_pdf->SetXY(0, 0);
    $this->_pdf->Cell($this->Width, $height, $text, $border, 1, 'L', 1);

    $this->endNormalSection($height+1, true);
  }
  
  function endNormalSection($sectionHeight, $keepTogether)
  {
    if ($this->mayflower->inSubReport()) {
      $this->endSectionInSubReport($sectionHeight, $keepTogether);
      return;
    }  
    $this->_pdf->comment("end Body-Section:1\n");
    $secBuff = $this->mayflower->sectionPop();
    $startPage = floor($this->mayflower->posY / $this->mayflower->layout->printHeight);
    $endPage   = floor(($this->mayflower->posY + $sectionHeight) / $this->mayflower->layout->printHeight);
    if ($keepTogether and ($startPage <> $endPage)) {
      if ($this->mayflower->posY > ($startPage * $this->mayflower->layout->printHeight)) { // page not blank
        $this->mayflower->newPage();
        $startPage = floor($this->mayflower->posY / $this->mayflower->layout->printHeight);
        $endPage   = floor(($this->mayflower->posY + $sectionHeight) / $this->mayflower->layout->printHeight);
      }
    }

    for ($page = $startPage; $page <= $endPage; $page++) {
      if (($page <> $this->mayflower->getPageIndex())) {
        if ($this->mayflower->getPageIndex() >= 0) {
          $this->printPageFooter();
        }
        $this->mayflower->setPageIndex($page);
        $this->printPageHeader();
      }
      $this->mayflower->reportStartPageBody();
      if (!$this->_exporter->DesignMode) {
        #$this->outSectionWithCallback(0, $this->mayflower->posY, $this->layout->reportWidth, $sectionHeight, $page - $startPage + 1, $this->_exporter, $secBuff);
        $formatCount = $page - $startPage + 1;
        $this->_exporter->onPrint($cancel, $formatCount);
        if (!$cancel) {
          $this->_pdf->outSection(0, $this->mayflower->posY, $this->mayflower->layout->reportWidth, $sectionHeight, $secBuff);
        }
      } else {
        $this->_pdf->outSection(0, $this->mayflower->posY, $this->mayflower->layout->reportWidth, $sectionHeight, $secBuff);
      }      
    }
    $this->mayflower->posY += $sectionHeight;
  }
  
  function endSectionInSubReport($sectionHeight, $keepTogether)
  {
    $this->_pdf->comment("end Subreport-Body-Section:2\n");
    $buff = $this->mayflower->sectionPop();

    $this->mayflower->reportStartPageBody();

    $formatCount = 1;
    $this->_exporter->onPrint($cancel, $formatCount);
    if (!$cancel) {
      $this->_pdf->outSection(0, $this->mayflower->posY, $this->mayflower->layout->reportWidth, $sectionHeight, $buff);
    }

    $this->mayflower->posY += $sectionHeight;
  }

  function pageHeaderEnd()
  {
   $this->mayflower->reportStartPageHeader();
   $buff = $this->mayflower->sectionPop();
   $this->_pdf->_pageHeaderOrFooterEnd($this->mayflower->getPageIndex() * $this->mayflower->layout->printHeight, $this->mayflower->layout->reportWidth, $this->mayflower->layout->pageHeaderHeight, $buff);
  }

  function pageFooterEnd()
  {
    $this->mayflower->reportStartPageFooter();
    $buff = $this->mayflower->sectionPop();
    $this->_pdf->_pageHeaderOrFooterEnd($this->mayflower->getPageIndex() * $this->mayflower->layout->printHeight, $this->mayflower->layout->reportWidth, $this->mayflower->layout->pageFooterHeight, $buff);
  }
  
  function printPageFooter()
  {
    if (!$this->layout->designMode) {  
      $this->_printNormalSection($this->PageFooter);
    }  
  }
  
  function printpageHeader()
  {
    if (!$this->layout->designMode) {  
      $this->_printNormalSection($this->PageHeader);
    }  
  }
  
  function Bookmark($txt,$level=0,$y=0)
  {
    $this->_pdf->Bookmark($txt, $level, $y, $this->mayflower->page(), $this->mayflower->posYinPage(), $this->mayflower->inReport());
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

class mayflower
{
  var $pdf;

  var $subReportIndex = 0;
  var $subReportbuff;
  var $sectionIndex = 0;
  var $sectionBuff;
  
  var $reportBuff;
  var $sectionType;    // 'Head', 'Foot' or ''
  var $layout;
  
  function mayflower(&$layout, &$pdf)
  {
    $this->actpageNo = -1;
    $this->layout =& $layout;
    $this->pdf =& $pdf;
  }  

  function &getInstance(&$layout, &$pdf, $reset)
  {
    static $instance;
    if (is_null($instance) || $reset) {
      $instance = new mayflower($layout, $pdf);
    }  
    return $instance;
  }

  function _setOutBuff()
  { 
    if ($this->sectionIndex > $this->subReportIndex) {
      $this->pdf->setOutBuffer($this->sectionBuff[$this->sectionIndex], 'section');
    } elseif ($this->subReportIndex > 0) {
      $this->pdf->setOutBuffer($this->subReportbuff[$this->subReportIndex], 'subReport');
    } elseif ($this->inReport()) {
      $this->pdf->setOutBuffer($this->reportPages[$this->actpageNo][$this->sectionType], "report page".$this->sectionType.$this->actpageNo);
    } else {
      $this->pdf->unsetBuffer();
    }  
  }
  
  function page()
  {
    return $this->actpageNo + 1;
  }
  
  function newPage()
  {
    $this->posY = ($this->actpageNo + 1) * $this->layout->printHeight;
  }

  function posYinPage()
  {
    return ($this->posY - ($this->actpageNo * $this->layout->printHeight));
  }
  
  function setPageIndex($index)
  {
    $this->actpageNo = $index;
    $this->_setOutBuff();
  }        
  
  function getPageIndex()
  {
    return $this->actpageNo;
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
   
  function inSubReport()
  {
    return  ($this->subReportIndex > 0);
  }   

  function subReportPush()
  {
   $this->subReportIndex++;
   $this->subReportBuff[$this->subReportIndex] = '';
   $this->_setOutBuff();
  }
   
  function subReportPop()
  {
    $this->subReportIndex--;
    $this->_setOutBuff();
    return $this->subReportbuff[$this->subReportIndex + 1];
  }
  
  function sectionPush()
  {
    $this->sectionIndex++;
    $this->sectionBuff[$this->sectionIndex] = '';
    $this->_setOutBuff();
  }
  
  function sectionPop()
  {
    $this->sectionIndex--;
    $this->_setOutBuff();
    return $this->sectionBuff[$this->sectionIndex + 1];
  }
  
  function getSectionIndexForCommentOnly()
  {
    return $this->sectionIndex;
  } 
  
  
  function startReportBuffering()
  {
    if ($this->inReport()) {
      Amber::showError('Error', 'startReport: a report is already started!');
      die();
    }  
    $this->posY = 0;
  }
  
  function endReportBuffering()
  {
    if (!$this->inReport()) {
      Amber::showError('Error', 'endReport: no report open');
      die();
    }  
    $this->posY = 0;
    $this->actpageNo = -1;
    $this->_setOutBuff();
  }

  function inReport()
  {
    return ($this->actpageNo >= 0);
  }      
}
