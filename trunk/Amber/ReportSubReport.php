<?php


/**
 *
 * @package PHPReport
 * @subpackage ReportEngine
 *  this class handles paged report types 
 */

class ReportSubReport extends Report
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
    $startPage = floor($this->mayflower->posY / $this->layout->printHeight);
    $endPage   = floor(($this->mayflower->posY + $sectionHeight) / $this->layout->printHeight);
    if ($keepTogether and ($startPage <> $endPage)) {
      if ($this->mayflower->posY > ($startPage * $this->layout->printHeight)) { // page not blank
        $this->mayflower->newPage();
        $startPage = floor($this->mayflower->posY / $this->layout->printHeight);
        $endPage   = floor(($this->mayflower->posY + $sectionHeight) / $this->layout->printHeight);
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
          $this->_pdf->outSection(0, $this->mayflower->posY, $this->layout->reportWidth, $sectionHeight, $secBuff);
        }
      } else {
        $this->_pdf->outSection(0, $this->mayflower->posY, $this->layout->reportWidth, $sectionHeight, $secBuff);
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
   $this->_pdf->_pageHeaderOrFooterEnd($this->mayflower->getPageIndex() * $this->layout->printHeight, $this->mayflower->layout->reportWidth, $this->mayflower->layout->pageHeaderHeight, $buff);
  }

  function pageFooterEnd()
  {
    $this->mayflower->reportStartPageFooter();
    $buff = $this->mayflower->sectionPop();
    $this->_pdf->_pageHeaderOrFooterEnd($this->mayflower->getPageIndex() * $this->layout->printHeight, $this->mayflower->layout->reportWidth, $this->mayflower->layout->pageFooterHeight, $buff);
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
