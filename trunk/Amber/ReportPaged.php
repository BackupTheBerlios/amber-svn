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
    $this->layout =& new pageLayout($this, $this->_asSubReport, $isDesignMode);
    $this->_pdf =& $this->_exporter->getExporterBasicClass($this->layout, !$this->_asSubReport);
    $this->mayflower =& mayflower::getInstance($this->_pdf, !$this->_asSubReport);
    $this->_exporter->startReport($this, $isSubreport, $isDesignMode);
    if ($isDesignMode) {
      $this->initDesignHeader();
    }  
    if ($isSubreport) {
      $this->mayflower->bufferPush();
      $this->_pdf->comment("StartSubreport"); // remove this!!!
    } else {  
      $this->mayflower->StartReportBuffering();
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
      $this->_pdf->comment("EndSubreport");
      $this->subReportBuff = $this->mayflower->bufferPop(); //a real copy
      return;
    } else {
      if (!$this->layout->designMode) {
        $this->_printNormalSection($this->PageFooter);
      }  
   
      $this->mayflower->endReportBuffering();
      $this->posY = 0;

      $firstPage = true;  //first page is out
      $endPageX = floor($this->layout->_reportWidth / $this->layout->printWidth);
      foreach(array_keys($this->mayflower->reportPages) as $pageY) {
        for($pageX = 0; $pageX <= $endPageX; $pageX++) {
          if (!$firstPage) {
            $this->_pdf->AddPage();
          }
          $firstPage = false;
  
          $this->outPageHeader($pageY, $pageX, $this->_pdf);  
          $this->outPage($pageY, $pageX, $this->_pdf);  
          $this->outPageFooter($pageY, $pageX, $this->_pdf);  
        }
      }
    }
    $this->_exporter->endReport($this);
  }

  function page()
  {
    return $this->mayflower->page();
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
    $this->mayflower->bufferPush();
    $this->_pdf->comment('Start Section:');
#    $this->_pdf->fillBackColorInWindow($section->BackColor, $section->_report->Width, $section->Height);
  }  

  function _endSection(&$section, $height, &$buffer)
  {
    if (!$section->_PagePart) {
      $this->endNormalSection($section, $height, $section->KeepTogether);
    } elseif ($this->layout->designMode) {
      $this->endNormalSection($section, $height, false);
    } elseif ($section->_PagePart == 'Foot') {
      $this->pageFooterEnd();
    } else {
      $this->pageHeaderEnd();
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

#    $this->mayflower->bufferPush();
#    $this->endNormalSection($height+1, true);
  }
  
  
  function endNormalSection(&$section, $sectionHeight, $keepTogether)
  {
    if ($this->_asSubReport) {
      $this->endSectionInSubReport($section, $sectionHeight, $keepTogether);
      return;
    }  
    $this->_pdf->comment("end Body-Section:1\n");
    $secBuff = $this->mayflower->bufferPop();
    $startPage = floor($this->posY / $this->layout->printHeight);
    $endPage   = floor(($this->posY + $sectionHeight) / $this->layout->printHeight);
    if ($keepTogether and ($startPage <> $endPage)) {
      if ($this->posY > ($startPage * $this->layout->printHeight)) { // page not blank
        $this->newPage();
        $startPage = floor($this->posY / $this->layout->printHeight);
        $endPage   = floor(($this->posY + $sectionHeight) / $this->layout->printHeight);
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
        #$this->outSectionWithCallback(0, $this->posY, $this->layout->reportWidth, $sectionHeight, $page - $startPage + 1, $this->_exporter, $secBuff);
        $formatCount = $page - $startPage + 1;
        $this->_exporter->onPrint($cancel, $formatCount);
        if (!$cancel) {
          $this->_pdf->outSection(0, $this->posY, $this->layout->reportWidth, $sectionHeight, $section->BackColor, $secBuff);
        }
      } else {
        $this->_pdf->outSection(0, $this->posY, $this->layout->reportWidth, $sectionHeight, $section->BackColor, $secBuff);
      }      
    }
    $this->posY += $sectionHeight;
  }
  
  function endSectionInSubReport(&$section, $sectionHeight, $keepTogether)
  {
    $this->_pdf->comment("end Subreport-Body-Section:2\n");
    $buff = $this->mayflower->bufferPop();

    $this->mayflower->reportStartPageBody();

    $formatCount = 1;
    $this->_exporter->onPrint($cancel, $formatCount);
    if (!$cancel) {
      $this->_pdf->outSection(0, $this->posY, $this->layout->reportWidth, $sectionHeight, $section->BackColor, $buff);
    }

    $this->posY += $sectionHeight;
  }

  function pageHeaderEnd()
  {
   $this->mayflower->reportStartPageHeader();
   $buff = $this->mayflower->bufferPop();
   $this->_pdf->_pageHeaderOrFooterEnd($this->mayflower->getPageIndex() * $this->layout->printHeight, $this->layout->reportWidth, $this->layout->pageHeaderHeight, $buff);
  }

  function pageFooterEnd()
  {
    $this->mayflower->reportStartPageFooter();
    $buff = $this->mayflower->bufferPop();
    $this->_pdf->_pageHeaderOrFooterEnd($this->mayflower->getPageIndex() * $this->layout->printHeight, $this->layout->reportWidth, $this->layout->pageFooterHeight, $buff);
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
    $this->_pdf->Bookmark($txt, $level, $y, $this->mayflower->page(), $this->posYinPage(), $this->mayflower->inReport());
  }
  
  function newPage()
  {
    $this->posY = ($this->mayflower->getPageIndex() + 1) * $this->layout->printHeight;
  }

  function posYinPage()
  {
    return ($this->posY - ($this->mayflower->getPageIndex() * $this->layout->printHeight));
  }
  

}
