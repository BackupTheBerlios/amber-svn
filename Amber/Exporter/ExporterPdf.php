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
  var $_blankPage;
  var $_pageNo = 1;

  var $_posY;

  /*********************************
   *  Report-pdf
   *********************************/
  function _exporterInit()
  {
    $report =& $this->_report;

    $orient = $this->_pdf_orientation($report->Orientation);
    $size = array($report->PaperWidth, $report->PaperHeight);
    #Amber::dump($size);
    $this->_pdf =& PDF::getInstance($orient, 1/20, $size);
    if ($report->Controls) {
      foreach (array_keys($report->Controls) as $ctrlName) {
        if (!empty($report->Controls[$ctrlName]->FontName)) {
          $font = strtolower($report->Controls[$ctrlName]->FontName);
          if (!$this->_pdf->_fontList[$font]) {
            // if You get
            // FPDF error: Could not include font definition file
            // uncomment the following line to find font-file
            //echo $font . '<br>';
            $this->_pdf->AddFont($font);
            $this->_pdf->AddFont($font, 'B');
            $this->_pdf->AddFont($font, 'I');
            $this->_pdf->AddFont($font, 'BI');
            $this->_pdf->_fontList[$font] = $font;
          }
        }
      }
    }
    if ($this->_asSubreport) {
      $this->_pdf->startSubReport();
    } else {  
      $this->_pdf->SetCompression(false);
      $this->_pdf->SetRightMargin($report->RightMargin);
      $this->_pdf->SetLeftMargin($report->LeftMargin);
      $this->_pdf->SetTopMargin($report->TopMargin);
      $this->_pdf->SetAutoPageBreak(false, $report->BottomMargin);
      if ($this->DesignMode) {
        $this->_pdf->startReport($this, $report->Width);
      } else {
        $this->_pdf->startReport($this, $report->Width, $report->PageHeader->Height, $report->PageFooter->Height);
      }
    }
  }

  function _exporterExit()
  {
    #echo "pdf->Output();<br>";
    if ($this->_asSubreport) {
      $this->_pdf->endSubReport();
    } else {  
      $this->_pdf->endReport($this->_report->Width);
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

    $this->_backColor(0xDDDDDD);
    $this->_textColor(0x000000);
    $this->_pdf->SetFont('helvetica', '', 8);
    $this->_pdf->SetLineWidth(10); // 0.5pt
    $this->_borderColor(0x000000);

    $border = 1;
    $this->_pdf->SetXY(0, 0);
    $this->_pdf->Cell($this->_report->Width, $height, $text, $border, 1, 'L', 1);

    $this->_pdf->endSection($height+1, true);
  }

  function startSection(&$section, $width, &$buffer)
  {
    parent::startSection($section, $width, $buffer);
    $this->_pdf->startSection();
    $this->_backColor($section->BackColor);
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



  function printBox(&$para)
  {
    if ($para->italic) {
      $fstyle .= 'I';
    }
    if ($para->bold) {
      $fstyle .= 'B';
    }
    if ($para->underline) {
      $fstyle .= 'U';
    }
    $para->font = strtolower($para->font);

    //echo "'".$control->FontName."' => '".$this->_fontList[$control->FontName]."'<br>";
    $this->_pdf->SetFont($this->_fontList[$para->font], $fstyle, $para->fsize);

    $this->_backColor($para->backcolor);
    $this->_textColor($para->forecolor);
    $this->_pdf->SetXY($para->x, $para->y);
    $this->_pdf->SetClipping($para->x, $para->y, $para->width, $para->height);
    $this->_pdf->Cell($para->width, $para->height, $para->content, '0', 0, $para->falign, $para->backstyle);
    $this->_pdf->RemoveClipping();
    $this->_pdf->SetXY($para->x, $para->y);
    if ($para->borderstyle <> 0) {
      $this->_borderColor($para->bordercolor);
      if ($para->borderwidth == 0) {
        $this->_pdf->SetLineWidth(1);
      } else {
        $this->_pdf->SetLineWidth($para->borderwidth);
      }
      $this->_pdf->Cell($para->width, $para->height, '', 'RLTB', 0, $para->falign, 0);
    }
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
    $para->x = ($control->Left +  $this->_secStartX);
    $para->y = ($control->Top + $this->_secStartY);
    $para->width = $control->Width;
    $para->height = $control->Height;
    $para->backstyle= $control->BackStyle;

    $para->content = $content;
    $para->forecolor = $control->ForeColor;
    $para->backcolor = $control->BackColor;

    $para->borderstyle = $control->BorderStyle;
    $para->bordercolor = $control->BorderColor;
    $para->borderwidth = $control->BorderWidth * 20;

    $this->printBox($para);
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
    $para->x = ($control->Left +  $this->_secStartX);
    $para->y = ($control->Top + $this->_secStartY);
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

    $this->printBox($para);
  }
  
  function printNormalSubReport(&$control, &$buffer, $content)
  {
    if (!$control->isVisible()) {
      return;
    }
    $para = new printBoxparameter;
    
    $para->x = ($control->Left +  $this->_secStartX);
    $para->y = ($control->Top + $this->_secStartY);
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
      $this->_borderColor($para->bordercolor);
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

  function _backColor($color)
  {
    $r = ($color >> 16) & 255;
    $g = ($color >>  8) & 255;
    $b = ($color) & 255;
    $this->_pdf->SetFillColor($r, $g, $b);
    //echo "pdf->SetFillColor($r, $g, $b);<br>";
  }
  function _textColor($color)
  {
    $r = ($color >> 16) & 255;
    $g = ($color >>  8) & 255;
    $b = ($color) & 255;
    $this->_pdf->SetTextColor($r, $g, $b);
    //echo "pdf->SetFillColor($r, $g, $b);<br>";
  }
  function _borderColor($color)
  {
    $r = ($color >> 16) & 255;
    $g = ($color >>  8) & 255;
    $b = ($color) & 255;
    $this->_pdf->SetDrawColor($r, $g, $b);
    //echo "pdf->SetFillColor($r, $g, $b);<br>";
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

  function _pdf_orientation($orientation)
  {
    $orientations = array('portrait' => 'p', 'landscape' => 'l');
    if (!isset($orientations[$orientation])) {
      return 'p';
    } else {
      return $orientations[$orientation];
    }
  }
}


/**
 *
 * @package PHPReport
 * @subpackage Exporter
 *  parameter class for exporterFPdf's printBox
 */

  class printBoxParameter
  {
    var $content;               // the content to display

    var $x;                     // x-position in userspace units
    var $y;                     // y-position in userspace units
    var $width;                 // width in userspace units
    var $height;                // height in userspace units

    var $forecolor = 0xFFFFFF;  // text color in rgb
    var $fsize = 10;            // fontsize in pt
    var $falign = 'L';          // alignment
    var $font = 'helvetica';    // font
    var $italic = false;        // bool: italics
    var $bold = false;          // bool: bold
    var $underline = false;     // bool: underline

    var $backstyle = 1;         // 0 - background transparent, 1 - use backgroundcolor
    var $backcolor = 0;         // background color in rgb

    var $borderstyle = 1;       // 0 - border transparent, 1 - use bordercolor
    var $bordercolor = 0;       // border color in rgb
    var $borderwidth = 0;       // border width in pt ***** *20
  }
