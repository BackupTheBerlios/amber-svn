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
  var $_fontList = array('arial' => 'helvetica', 'ms sans serif' => 'helvetica', 'small fonts' => 'helvetica');

  var $_posY;

  /*********************************
   *  Report-pdf
   *********************************/
  function getPreamble(&$report)
  {
    parent::getPreamble($report);
    $this->_blankPage = true;
    $orient = $this->_pdf_orientation($report->Orientation);
    $size = array($report->PaperWidth, $report->PaperHeight);
    #dump($size);
    $this->_pdf =& new PDF($orient, 1/20, $size);
    $this->_pdf->SetCompression(false);    
    $this->_pdf->SetRightMargin($report->RightMargin);
    $this->_pdf->SetLeftMargin($report->LeftMargin);
    $this->_pdf->SetTopMargin($report->TopMargin);
    $this->_pdf->SetAutoPageBreak(false, $report->BottomMargin);
    if ($report->Controls) {
      foreach (array_keys($report->Controls) as $ctrlName) {
        if (!empty($report->Controls[$ctrlName]->FontName)) {
          $font = strtolower($report->Controls[$ctrlName]->FontName);
          if (!$this->_fontList[$font]) {
            // if You get
            // FPDF error: Could not include font definition file
            // uncomment the following line to find font-file
            //echo $font . '<br>';
            $this->_pdf->AddFont($font);
            $this->_pdf->AddFont($font, 'B');
            $this->_pdf->AddFont($font, 'I');
            $this->_pdf->AddFont($font, 'BI');
            $this->_fontList[$font] = $font;
          }
        }
      }
    }
    if ($this->DesignMode) {
      $this->_pdf->ReportStart($this, $report->Width);
    } else {
      $this->_pdf->ReportStart($this, $report->Width, $report->PageHeader->Height, $report->PageFooter->Height);
    } 
  }

  function getPostamble(&$report)
  {
    parent::getPostamble($report);
    #echo "pdf->Output();<br>";
    $this->_pdf->ReportEnd($report->Width);
    $this->_pdf->Output('out.pdf',"I");
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
    $this->_pdf->sectionStart();
    $height = 240; //12pt
    
    $this->_backColor(0xDDDDDD);
    $this->_textColor(0x000000);
    $this->_pdf->SetFont('helvetica', '', 8);
    $this->_pdf->SetLineWidth(10); // 0.5pt
    $this->_borderColor(0x000000);

    $border = 1;
    $this->_pdf->SetXY(0, 0);
    $this->_pdf->Cell($this->_report->Width, $height, $text, $border, 1, 'L', 1);

    $this->_pdf->sectionEnd($height+1);
  }  

  function sectionPrintStart(&$sec, $width, &$buffer)
  {
    $this->_pdf->sectionStart();
    $this->_backColor($sec->BackColor);
    $text = '';
    $border = 0;
    $ln = 0; //pos after printing
    $align = 'C';
    $fill = 1;
    $this->_pdf->Cell($sec->_parent->Width, $sec->Height, $text, $border, $ln, $align, $fill);
  }

  function sectionPrintEnd(&$sec, $height, &$buffer)
  {
#print "called<br>";
    if (!$sec->_PagePart or $this->DesignMode) {
      $this->_pdf->sectionEnd($height);
    } elseif ($sec->_PagePart == 'Foot') {  
      $this->_pdf->pageFooterEnd();
    } else {
      $this->_pdf->pageHeaderEnd();
    }    
  }

  function page() 
  {
    return $this->_pdf->_actPageNo + 1;
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
    // we let $this do the work, after all we only need printNormal and printPreview
  }

    
  function printNormal(&$control, &$buffer, $content)
  {
    if (!$control->Visible) {
      return;
    }

    $fstyle = '';
    if ($control->FontItalic) {
      $fstyle .= 'I';
    }
    if ($control->FontWeight >= 600) {
      $fstyle .= 'B';
    }
    if ($control->FontUnderline) {
      $fstyle .= 'U';
    }
    $fsize = $control->FontSize;

    //echo "'".$control->FontName."' => '".$this->_fontList[$control->FontName]."'<br>";
    $font = strtolower($control->FontName);
    $this->_pdf->SetFont($this->_fontList[$font], $fstyle, $fsize);
    // todo FontName     $control->FontName
    $falign = $this->_pdf_textalign($control->TextAlign);
    $x = ($control->Left +  $this->_secStartX);
    $y = ($control->Top + $this->_secStartY);
    $width = $control->Width;
    $height = $control->Height;
    $fill = $control->BackStyle;

    $this->_backColor($control->BackColor);
    $this->_textColor($control->ForeColor);
    $this->_pdf->SetXY($x, $y);
    $this->_pdf->SetClipping($x, $y, $width, $height);
    $this->_pdf->Cell($width, $height, $content, '0', 0, $falign, $fill);
    $this->_pdf->RemoveClipping();
    $this->_pdf->SetXY($x, $y);
    if ($control->BorderStyle <> 0) {
      $this->_borderColor($control->BorderColor);
      if ($control->BorderWidth == 0) {
        $this->_pdf->SetLineWidth(1);
      } else {
        $this->_pdf->SetLineWidth($control->BorderWidth * 20);
      }
      $this->_pdf->Cell($width, $height, '', 'RLTB', 0, $falign, 0);
    }
  }

  function printDesign(&$control, &$buffer, $content)
  {
    $this->printNormal(&$control, $buffer, $content);
  }

  /*********************************
   *  Helper functions - pdf
   *********************************/

  function dump($var)
  {
    $width = 0;
    $height = 240;
    $falign = 'C';
    $fill = 0;
    $this->_pdf->sectionStart();
    $this->_pdf->Cell($width, $height, print_r($var, 1), '0', 0, $falign, $fill);
    $this->_pdf->sectionEnd($height);
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


