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


class pageLayout
{
  var $unit;          //unit in pt 
  
  var $paperwidth;
  var $paperheight;
  
  var $rightMargin;
  var $leftMargin;
  var $topMargin;
  var $bottomMargin;
  
  var $pageHeaderHeight;
  var $pageFooterHeight;
  
  //////////////////////
  // 'calculated' fields
  /////////////////////
  var $printWidth;
  var $printHeight;

  function set_orientation($orientation, $width, $height)
  {
    if ($orientation == 'portrait') {
      $this->paperWidth = $width;
      $this->paperHeight = $height;
    } else {
      $this->paperWidth = $height;
      $this->paperHeight = $width;
    } 
  }
  function calcPrintableArea()
  {
    $this->printWidth  = ($this->paperWidth - $this->leftMargin - $this->rightMargin); //width of printable area of page (w/o morgins)
    $this->printHeight = ($this->paperHeight - $this->topMargin - $this->bottomMargin - $this->pageHeaderHeight - $this->pageFooterHeight); //height of printable area of page (w/o morgins)
  }
}




class ExporterFPdf extends Exporter
{
  var $type = 'fpdf';
  var $_pdf;
#  var $_blankPage;
#  var $_pageNo = 1;

#  var $_posY;

  /*********************************
   *  Report-pdf
   *********************************/
  function _exporterInit()
  {
    $report =& $this->_report;
    $layout =& new pageLayout();
    $layout->unit = 1/20;
    $layout->set_orientation($report->Orientation, $report->PaperWidth, $report->PaperHeight);
    #Amber::dump($size);
    $reset = (!$this->_asSubreport);
    $this->_pdf =& PDF::getInstance($layout, $reset);
    if ($report->Controls) {
      foreach (array_keys($report->Controls) as $ctrlName) {
        if (!empty($report->Controls[$ctrlName]->FontName)) {
          $this->_pdf->registerFontFamily($report->Controls[$ctrlName]->FontName);
        }
      }
    }
    if ($this->_asSubreport) {
      $this->_pdf->startSubReport();
    } else {  
      $this->_pdf->SetCompression(false);
      $layout->rightMargin = $report->RightMargin;
      $layout->leftMargin = $report->LeftMargin;
      $layout->topMargin = $report->TopMargin;

      $layout->bottomMargin = $report->BottomMargin;
      $this->_pdf->_actPageNo = -1;
      if ($this->DesignMode) {
        $layout->pageHeaderHeight = 0;
        $layout->pageFooterHeight = 0;
      } else {
        $layout->pageHeaderHeight = $report->PageHeader->Height;
        $layout->pageFooterHeight = $report->PageFooter->Height;
      }
      $layout->calcPrintableArea();
      $this->_pdf->init($this, $report->Width, $layout);
      $this->_pdf->StartReportBuffering();
    }
  }
  
  function _exporterExit()
  {
    #echo "pdf->Output();<br>";
    if ($this->_asSubreport) {
      $this->_pdf->endSubReport();
    } else {  
      $this->endReport1($this->_report->Width);
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
  }
  
  function endReport1()
  {
    $this->printPageFooter();

    $this->_pdf->endReportBuffering();
    
    $firstPage = true;  //first page is out

    $endPageX = floor($this->_pdf->_reportWidth / $this->_pdf->_printWidth);
    foreach(array_keys($this->_pdf->_reportPages) as $pageY) {
      for($pageX = 0; $pageX <= $endPageX; $pageX++) {
        if (!$firstPage) {
          $this->_pdf->AddPage();
        }
        $firstPage = false;

        $this->_pdf->outPageHeader($pageY, $pageX, $this->_pdf->_reportPages[$pageY]['Head']);  
        $this->_pdf->outPage($pageY, $pageX, $this->_pdf->_reportPages[$pageY]['']);  
        $this->_pdf->outPageFooter($pageY, $pageX, $this->_pdf->_reportPages[$pageY]['Foot']);  
      }
    }
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
    $this->_pdf->startSection();
    $height = 240; //12pt

    $this->_pdf->_backColor(0xDDDDDD);
    $this->_pdf->_textColor(0x000000);
    $this->_pdf->SetFont('helvetica', '', 8);
    $this->_pdf->SetLineWidth(10); // 0.5pt
    $this->_pdf->_borderColor(0x000000);

    $border = 1;
    $this->_pdf->SetXY(0, 0);
    $this->_pdf->Cell($this->_report->Width, $height, $text, $border, 1, 'L', 1);

    $this->_pdf->endSection($height+1, true);
  }

  function startSection(&$section, $width, &$buffer)
  {
    parent::startSection($section, $width, $buffer);
    $this->_pdf->startSection();
    $this->_pdf->_backColor($section->BackColor);
    $fill = true;
    $text = '';
    $border = 0;
    $ln = 0; //pos after printing
    $align = 'C';
    $backstyle= 1;
    $this->_pdf->Cell($section->_report->Width, $section->Height, $text, $border, $ln, $align, $fill);
  }

  function endSection(&$section, $height, &$buffer)
  {
    if (!$section->_PagePart) {
      $this->_pdf->endSection($height, $section->KeepTogether);
    } elseif ($this->DesignMode) {
      $this->_pdf->endSection($height, false);
    } elseif ($section->_PagePart == 'Foot') {
      $this->_pdf->pageFooterEnd();
    } else {
      $this->_pdf->pageHeaderEnd();
    }
    parent::endSection($section, $height, $buffer);
  }

  function page()
  {
    return $this->_pdf->page();
  }

  function newPage()
  {
    $this->_pdf->newPage();
  }

  /*
  * callback function for PDF: init printing of page footer if necessary
  *
  * @access public
  */
  function printPageFooter()
  {
    if (!$this->DesignMode) {
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
    if (!$this->DesignMode) {
      $this->_report->_printNormalSection('PageHeader');
    }
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
      $pdf =& $rep->_exporter->_pdf; 
      $para->content = "\n%Start SubReport\n" . $pdf->_subReportBuff[_inSubReport+1] . "\n%End SubReport\n"; 
    }
    #$para->content = "(TEST)";
            
    $this->_pdf->SetXY($para->x, $para->y);
    $this->_pdf->SetClipping($para->x, $para->y, $para->width, $para->height);
    $this->_pdf->SetCoordinate(-$para->x, -$para->y);
    
    $this->_pdf->_out($para->content);
    
    $this->_pdf->RemoveCoordinate();
    $this->_pdf->RemoveClipping();
    $this->_pdf->SetXY($para->x, $para->y);
    if ($para->borderstyle <> 0) {
      $this->_pdf->_borderColor($para->bordercolor);
      if ($para->borderwidth == 0) {
        $this->_pdf->SetLineWidth(1);
      } else {
        $this->_pdf->SetLineWidth($para->borderwidth);
      }
      $this->_pdf->Cell($para->width, $para->height, '', 'RLTB', 0, $para->falign, 0);
    }

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
    $this->_pdf->endSection($height, false);
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
