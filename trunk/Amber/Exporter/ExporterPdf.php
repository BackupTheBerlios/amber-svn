<?php

/**
 *
 * @package PHPReport
 * @subpackage Exporter
 *
 */

define('FPDF_FONTPATH','fpdf/font/');
require_once('fpdf/fpdf.php');
require_once('PDF.inc.php');

ExporterFactory::register('pdf', 'ExporterFPdf');
ExporterFactory::register('.pdf', 'ExporterFPdf');
ExporterFactory::register('fpdf', 'ExporterFPdf');
ExporterFactory::register('testpdf', 'ExporterFPdf');


/**
 *
 * @package PHPReport
 * @subpackage Exporter
 *
 */



class ExporterFPdf extends Exporter
{
  var $type = 'fpdf';
  var $_pdf;

  /*********************************
   *  Report-pdf
   *********************************/

  function startReportSubExporter(&$report, $asSubreport = false, $isDesignMode = false)
  {
    $reset = (!$asSubreport);
    $this->_pdf =& PDF::getInstance($report->layout, $reset);
    $this->mayflower =& $this->_pdf->mayflower;
    if ($report->Controls) {
      foreach (array_keys($report->Controls) as $ctrlName) {
        if (!empty($report->Controls[$ctrlName]->FontName)) {
          $this->_pdf->registerFontFamily($report->Controls[$ctrlName]->FontName);
        }
      }
    }
    if ($asSubreport) {
      $this->mayflower->subReportPush();
      $this->startcomment("StartSubreport");
    } else {  
      $this->_pdf->init($this, $report->layout);
      $this->mayflower->StartReportBuffering();
    }
  }
  
  function endReportSubExporter(&$report)
  {
    if ($this->_asSubreport) {
      $this->newPage();
      $this->comment("EndSubreport");
      return $this->mayflower->subReportPop();
    } else {
      if (!$this->_report->layout->designMode) {
        $this->_report->_printNormalSection('PageFooter');
      }  
   
      $this->mayflower->endReportBuffering();
    
      $firstPage = true;  //first page is out
  
      $endPageX = floor($this->_report->layout->_reportWidth / $this->_report->layout->printWidth);
      foreach(array_keys($this->_report->reportBuff->reportPages) as $pageY) {
        for($pageX = 0; $pageX <= $endPageX; $pageX++) {
          if (!$firstPage) {
            $this->_pdf->AddPage();
          }
          $firstPage = false;
  
          $this->_report->outPageHeader($pageY, $pageX, $this->_pdf);  
          $this->_report->outPage($pageY, $pageX, $this->_pdf);  
          $this->_report->outPageFooter($pageY, $pageX, $this->_pdf);  
        }
      }
      $this->_sendOutputFile();
    }
  }

  function _sendOutputFile()
  {
    if ($this->createdAs == 'testpdf') {
      print $this->_pdf->Output('out.txt', 'S');
    } else {
      if (isset($this->_docTitle)) {
        $this->_pdf->Output('"' . $this->_docTitle . '.pdf"', 'I');
      } else {
        $this->_pdf->Output('out.pdf', 'I');
      }
    }
  }
  
  function comment($s)
  {
    $this->_pdf->comment($s);
  }  

  function startcomment($s)
  {
    $this->_pdf->startcomment($s);
  }  
  
  /*********************************
   *  Section
   *********************************/


  /**
  *
  * for design mode: print border between sections
  *
  * @access public
  * @param  string name of header to print
  * @return integer height printed in twips
  */
  function sectionPrintDesignHeader($text)
  {
    $this->mayflower->sectionPush();
    $this->comment('Start Section:' . ($this->mayflower->getSectionIndexForCommentOnly()));
    $height = 240; //12pt

    $this->_pdf->_backColor(0xDDDDDD);
    $this->_pdf->_textColor(0x000000);
    $this->_pdf->SetFont('helvetica', '', 8);
    $this->_pdf->SetLineWidth(10); // 0.5pt
    $this->_pdf->_borderColor(0x000000);

    $border = 1;
    $this->_pdf->SetXY(0, 0);
    $this->_pdf->Cell($this->_report->Width, $height, $text, $border, 1, 'L', 1);

    $this->endSection1($height+1, true);
  }

  function startSection(&$section, $width, &$buffer)
  {
    parent::startSection($section, $width, $buffer);
    $this->mayflower->sectionPush();
    $this->comment('Start Section:' . ($this->mayflower->getSectionIndexForCommentOnly()));
    $this->_pdf->fillBackColorInWindow($section->BackColor, $section->_report->Width, $section->Height);
  }
  
  function endSection(&$section, $height, &$buffer)
  {
    if (!$section->_PagePart) {
      $this->endSection1($height, $section->KeepTogether);
    } elseif ($this->_report->layout->designMode) {
      $this->endSection1($height, false);
    } elseif ($section->_PagePart == 'Foot') {
      $this->pageFooterEnd();
    } else {
      $this->pageHeaderEnd();
    }
    parent::endSection($section, $height, $buffer);
  }
  
  function endSection1($sectionHeight, $keepTogether)
  {
    if ($this->mayflower->inSubReport()) {
      $this->endSectionInSubReport($sectionHeight, $keepTogether);
      return;
    }  
    $this->comment("end Body-Section:1\n");
    $secBuff = $this->mayflower->sectionPop();
    $startPage = floor($this->mayflower->reportBuff->posY / $this->mayflower->layout->printHeight);
    $endPage   = floor(($this->mayflower->reportBuff->posY + $sectionHeight) / $this->mayflower->layout->printHeight);
    if ($keepTogether and ($startPage <> $endPage)) {
      if ($this->mayflower->reportBuff->posY > ($startPage * $this->mayflower->layout->printHeight)) { // page not blank
        $this->mayflower->reportBuff->newPage();
        $startPage = floor($this->mayflower->reportBuff->posY / $this->mayflower->layout->printHeight);
        $endPage   = floor(($this->mayflower->reportBuff->posY + $sectionHeight) / $this->mayflower->layout->printHeight);
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
      if (!$this->DesignMode) {
        #$this->outSectionWithCallback(0, $this->mayflower->reportBuff->posY, $this->layout->reportWidth, $sectionHeight, $page - $startPage + 1, $this->_exporter, $secBuff);
        $formatCount = $page - $startPage + 1;
        $this->onPrint($cancel, $formatCount);
        if (!$cancel) {
          $this->_pdf->outSection(0, $this->mayflower->reportBuff->posY, $this->mayflower->layout->reportWidth, $sectionHeight, $secBuff);
        }
      } else {
        $this->_pdf->outSection(0, $this->mayflower->reportBuff->posY, $this->mayflower->layout->reportWidth, $sectionHeight, &$secBuff);
      }      
    }
    $this->mayflower->reportBuff->posY += $sectionHeight;
  }
  
  function endSectionInSubReport($sectionHeight, $keepTogether)
  {
    $this->comment("end Subreport-Body-Section:2\n");
    $buff = $this->mayflower->sectionPop();

    $this->mayflower->reportStartPageBody();

    $formatCount = 1;
    $this->onPrint($cancel, $formatCount);
    if (!$cancel) {
      $this->_pdf->outSection(0, $this->mayflower->reportBuff->posY, $this->mayflower->layout->reportWidth, $sectionHeight, $buff);
    }

    $this->mayflower->reportBuff->posY += $sectionHeight;
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

  /*
  * callback function for PDF: init printing of page footer if necessary
  *
  * @access public
  */
  function printPageFooter()
  {
    if (!$this->_report->layout->designMode) {  
      $this->_report->_printNormalSection('PageFooter');
    }  
  }
 
  /*
  * callback function for PDF: init printing of page header if necessary
  *
  * @access public
  */
  function printpageHeader()
  {
    if (!$this->_report->layout->designMode) {  
      $this->_report->_printNormalSection('PageHeader');
    }  
  }
  
  function Bookmark($txt,$level=0,$y=0)
  {
    $this->_pdf->Bookmark1($txt, $level, $y, $this->mayflower->pageNo(), $this->mayflower->posYinPage(), $this->mayflower->inReport());
  }


  /*********************************
   *  Controls - pdf
   *********************************/
  function setControlExporter(&$ctrl)
  {
    $ctrl->_exporter =& $this;
    // instead of creating a new Exporter for every Controltype
    // we let $this one do the work, after all we only need printNormal and printPreview
  }





  function printNormal(&$control, &$buffer, $content)
  {
    $type = strtolower(get_class($control));
    #echo $type;
    if ($type == 'checkbox') {
      return $this->printNormalCheckBox($control, $buffer, $content);
    } elseif ($type == 'subreport') {
      return $this->printNormalSubReport($control, $buffer, $content);
    }
    #$content = $type;
    if (!$control->isVisible()) {
      return;
    }
    $para = new printBoxparameter;

    $para->italic = $control->FontItalic;
    $para->bold  = ($control->FontWeight >= 600);
    $para->underline = $control->FontUnderline;
    $para->fsize = $control->FontSize;

    $para->font = $control->FontName;
    $para->falign = $this->_pdf_textalign($control->TextAlign);
    $para->x = $control->Left;
    $para->y = $control->Top;
    $para->width = $control->Width;
    $para->height = $control->Height;
    $para->backstyle= $control->BackStyle;

    $para->content = $content;
    $para->forecolor = $control->ForeColor;
    $para->backcolor = $control->BackColor;

    $para->borderstyle = $control->BorderStyle;
    $para->bordercolor = $control->BorderColor;
    $para->borderwidth = $control->BorderWidth * 20;

    $this->_pdf->printBox($para);
  }

  function printNormalCheckBox(&$control, &$buffer, $content)
  {
    if (!$control->isVisible()) {
      return;
    }
    $para = new printBoxparameter;

    #$para->italic = false;
    $para->bold  = true;
    #$para->underline = false;
    $para->fsize = 6;

    $para->font = 'helvetica';
    $para->falign = 'C';
    $para->x = $control->Left;
    $para->y = $control->Top;
    $para->width = 11 * 15;
    $para->height = 11 * 15;

    $para->content = $content;


    $para->backstyle = 1;
    if (($content === '0') || ($content === 0)) {
      $para->content = '';
      $para->backcolor = 0xFFFFFF;
    } elseif (is_numeric($content)) {
      $para->content = 'X';
      $para->backcolor = 0xFFFFFF;
    } else {
      $para->content = '';
      $para->backcolor = 0xCCCCCC;
    }

    $para->forecolor = 0;
    $para->borderstyle = 1;
    $para->bordercolor = 0;
    $para->borderwidth = 20;

    $this->_pdf->printBox($para);
  }
  
  function printNormalSubReport(&$control, &$buffer, $content)
  {
    if (!$control->isVisible()) {
      return;
    }
    $para = new printBoxparameter;
    
    $para->x = $control->Left;
    $para->y = $control->Top;
    $para->width = $control->Width;
    $para->height = $control->Height;

    $para->forecolor = 0;
    $para->backcolor = 0xFFFFFF;
    
    $para->borderstyle = $control->BorderStyle;
    $para->bordercolor = $control->Bordercolor;
    $para->borderwidth = $control->BorderWidth;
    
    $rep =& $control->_subReport;
    if (is_null($rep)) {
      $para->content = '';
    } else {
      $rep->resetMargin(true);
      $rep->run('pdf', true);
      $para->content = "\n%Start SubReport\n" . $this->mayflower->subReportGetPopped() . "\n%End SubReport\n"; 
    }
    #$para->content = "(TEST)";
    $this->_pdf->printBoxPdf($para);            

  }

  function printDesign(&$control, &$buffer, $content)
  {
    $this->printNormal($control, $buffer, $content);
  }

  /*********************************
   *  Helper functions - pdf
   *********************************/

  function dump($var)
  {
    $width = 0;
    $height = 240;
    $falign = 'C';
    $backstyle= 0;
    $this->_pdf->startSection();
    $this->_pdf->Cell($width, $height, print_r($var, 1), '0', 0, $falign, $fill);
    $this->_pdf->endSection1($height, false);
  }

  function _pdf_textalign($textalign)
  {
    $alignments = array(1 => 'L', 'C', 'R', 'J');

    if (!isset($alignments[$textalign])) {
      return 'L';
    } else {
      return $alignments[$textalign];
    }
  }

}
