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
    $this->_asSubReport = $isSubreport;
    if (!isset($this->_exporter)) {
      return;
    }
    $this->layout =& new pageLayout($this, $this->_asSubReport, $isDesignMode);
    $this->_exporter->startReport($this, $isSubreport, $isDesignMode);
    if ($isDesignMode) {
      $this->initDesignHeader();
    }  
    if ($isSubreport) {
      $this->subReportStartBuffer();
      $this->_exporter->comment("StartSubreport"); // remove this!!!
    } else {  
      $this->StartReportBuffering();
      $this->posY = 0;
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
      $this->subReportBuff = $this->subReportEndBuffer(); //a real copy
      return;
    } else {
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
  
          $this->outPageHeader($pageY, $pageX, $this->_exporter);  
          $this->outPage($pageY, $pageX, $this->_exporter);  
          $this->outPageFooter($pageY, $pageX, $this->_exporter);  
        }
      }
    }
    $this->_exporter->endReport($this);
  }

  function outPageHeader($pageY, $pageX, &$exporter)
  {
    $x = $this->layout->leftMargin;
    $y = $this->layout->topMargin;
    $w = $this->layout->printWidth;
    $h = $this->layout->pageHeaderHeight;
    $deltaX = $this->layout->leftMargin - $pageX * $this->layout->printWidth;
    $deltaY = $pageY * $this->layout->printHeight - $y;
    $exporter->outWindowRelative($deltaX, $deltaY, $x, $y, $w, $h, $this->reportPages[$pageY]['Head']);
  }
  function outPage($pageY, $pageX, &$exporter)
  {
    $x = $this->layout->leftMargin;
    $y = $this->layout->topMargin + $this->layout->pageHeaderHeight;
    $w = $this->layout->printWidth;
    $h = $this->layout->printHeight;
    $deltaX = $this->layout->leftMargin - $pageX * $this->layout->printWidth;
    $deltaY = $pageY * $this->layout->printHeight - $y;
    $exporter->outWindowRelative($deltaX, $deltaY, $x, $y, $w, $h, $this->reportPages[$pageY]['']);
  }
  function outPageFooter($pageY, $pageX, &$exporter)
  {
    $x = $this->layout->leftMargin;
    $y = $this->layout->topMargin + $this->layout->pageHeaderHeight + $this->layout->printHeight;
    $w = $this->layout->printWidth;
    $h = $this->layout->pageFooterHeight;
    $deltaX = $this->layout->leftMargin - $pageX * $this->layout->printWidth;
    $deltaY = $pageY * $this->layout->printHeight - $y;
    $exporter->outWindowRelative($deltaX, $deltaY, $x, $y, $w, $h, $this->reportPages[$pageY]['Foot']);
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
  
  function initDesignHeader()
  {
    $this->_designSection =& new section('');
    $this->_designSection->Name = '';
    $this->_designSection->Height = 240;
    $this->_designSection->Visible = true;
    $this->_designSection->BackColor = 0xFFFFFF;
    $this->_designSection->CanGrow = false;
    $this->_designSection->CanShrink = false;
    $this->_designSection->KeepTogether = false;
    $this->_designSection->EventProcPrefix = '';
    $this->_designSection->_parent =& $this;
    $this->_designSection->_OnFormatFunc = 'allSections_Format';
    
    $ctlProp = array(
      'Name' => '',
      'Left' => 2,
      'Top' => 2,
      'Width' => $this->Width-4,
      'Height' => 236,
      'Visible' => true,
      'BackStyle' => 1,
      'BackColor' => 0xDDDDDD, //gray
      'BorderStyle' => 1,
      'BorderColor' => 0, // black
      'BorderWidth' => 0, // as small as possible ("Haarlinie")
      'BorderLineStyle' => 0,
      'zIndex' => 0,
      'Value' => '',
      '_OldValue' => '',

      'ForeColor' => 0x000000,
      'FontName' => 'Arial',
      'FontSize' => 8,
      'FontWeight' => 500,
      'TextAlign' => 0,
      'FontItalic' => false,
      'FontUnderline' => false,
      
      'Caption' => 'Test'
    );

    $ctl =& ControlFactory::create(100, $ctlProp, $this->hReport);
    $this->_exporter->setControlExporter($ctl);
    $this->_designSection->Controls['Label'] =& $ctl;
  }
  
  function sectionPrintDesignHeader($text)
  {
    $this->_designSection->Controls['Label']->Caption = $text;
    $buffer = '';
    
    $this->_startSection($this->_designSection, $this->Width, $buffer);
    $height = $this->_designSection->printNormal($buffer);
    $this->_endSection($this->_designSection, $height, $buffer);
  }
  
  
  function endNormalSection(&$section, $sectionHeight, $keepTogether)
  {
    if ($this->_asSubReport) {
      $this->endSectionInSubReport($section, $sectionHeight, $keepTogether);
      return;
    }  
    $this->_exporter->comment("end Body-Section:1\n");
    $secBuff = $section->sectionEndBuffer($this->_exporter);
    $startPage = floor($this->posY / $this->layout->printHeight);
    $endPage   = floor(($this->posY + $sectionHeight) / $this->layout->printHeight);
    if ($keepTogether and ($startPage <> $endPage)) {                 // section doesn't fit on page and keepTogether
      $endPageWithoutNewPage = floor(($this->posY + $sectionHeight) / $this->layout->printHeight);
      $startNewPage =  (floor($this->posY / $this->layout->printHeight) + 1) * $this->layout->printHeight;
      $endPageWithNewPage = floor(($startNewPage + $sectionHeight) / $this->layout->printHeight);
      if ($endPageWithoutNewPage == $endPageWithNewPage) {          // end of section will be on same page, wether with or without pagebreak
        $this->newPage();
        $startPage = floor($this->posY / $this->layout->printHeight);
        $endPage   = floor(($this->posY + $sectionHeight) / $this->layout->printHeight);
      }
    }

    for ($page = $startPage; $page <= $endPage; $page++) {
      if (($page <> $this->getPageIndex())) {
        if ($this->getPageIndex() >= 0) {
          $this->printPageFooter();
        }
        $this->setPageIndex($page);
        $this->printPageHeader();
      }
      $this->reportStartPageBody();
      if (!$this->_exporter->DesignMode) {
        #$this->outSectionWithCallback(0, $this->posY, $this->layout->reportWidth, $sectionHeight, $page - $startPage + 1, $this->_exporter, $secBuff);
        $formatCount = $page - $startPage + 1;
        $this->_exporter->onPrint($cancel, $formatCount);
        if (!$cancel) {
          $this->_exporter->outSection(0, $this->posY, $this->layout->reportWidth, $sectionHeight, $section->BackColor, $secBuff);
        }
      } else {
        $this->_exporter->outSection(0, $this->posY, $this->layout->reportWidth, $sectionHeight, $section->BackColor, $secBuff);
      }      
    }
    $this->posY += $sectionHeight;
  }
  
  function endSectionInSubReport(&$section, $sectionHeight, $keepTogether)
  {
    $this->_exporter->comment("end Subreport-Body-Section:2\n");
    $buff = $section->sectionEndBuffer($this->_exporter);

    $formatCount = 1;
    $this->_exporter->onPrint($cancel, $formatCount);
    if (!$cancel) {
      $this->_exporter->outSection(0, $this->posY, $this->layout->reportWidth, $sectionHeight, $section->BackColor, $buff);
    }

    $this->posY += $sectionHeight;
  }

  function pageHeaderEnd(&$section)
  {
   $buff = $section->sectionEndBuffer($this->_exporter);
   $this->reportStartPageHeader();
   $this->_exporter->_pageHeaderOrFooterEnd($this->getPageIndex() * $this->layout->printHeight, $this->layout->reportWidth, $this->layout->pageHeaderHeight, $buff);
  }

  function pageFooterEnd(&$section)
  {
    $buff = $section->sectionEndBuffer($this->_exporter);
    $this->reportStartPageFooter();
    $this->_exporter->_pageHeaderOrFooterEnd($this->getPageIndex() * $this->layout->printHeight, $this->layout->reportWidth, $this->layout->pageFooterHeight, $buff);
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
    $this->_exporter->Bookmark($txt, $level, $y, $this->page(), $this->posYinPage(), $this->inReport());
  }
  
  function newPage()
  {
    $this->posY = ($this->getPageIndex() + 1) * $this->layout->printHeight;
  }

  function posYinPage()
  {
    return ($this->posY - ($this->getPageIndex() * $this->layout->printHeight));
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
   
  function startReportBuffering()
  {
    if ($this->inReport()) {
      Amber::showError('Error', 'startReport: a report is already started!');
      die();
    }  
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
