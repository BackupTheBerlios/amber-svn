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
    $size = array($report->PaperWidth / 1440, $report->PaperHeight / 1440);
    #dump($size);
    $this->_pdf =& new PDF($orient, 'in', $size);
    $this->_pdf->SetCompression(false);    
    $this->_pdf->SetRightMargin($report->RightMargin / 1440);
    $this->_pdf->SetLeftMargin($report->LeftMargin / 1440);
    $this->_pdf->SetTopMargin($report->TopMargin / 1440);
    $this->_pdf->SetAutoPageBreak(false, $report->BottomMargin / 1440);
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

  function getPostamble(&$report)
  {
    parent::getPostamble($report);
    #echo "pdf->Output();<br>";
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
    $height = 240; //12pt
    
    if ($this->_blankPage) {
        $this->_pdf->AddPage();
        $this->_blankPage = false;
    }  
    $this->_secStartX = $this->_pdf->GetX();
    $this->_secStartY = $this->_pdf->GetY();

    $this->_backColor(0xDDDDDD);
    $this->_textColor(0x000000);

    $this->_pdf->SetFont('helvetica', '', 8);
    $border = 0;
    $this->_pdf->Cell($this->_report->Width / 1440, $height / 1440, $text, $border, 1, 'L', 1);

    $this->_secEndX = $this->_pdf->GetX();
    $this->_secEndY = $this->_pdf->GetY();
    if ($this->_secEndY < $this->_secStartY) { #we had a page break
      $this->_secStartY = ($this->_secEndY - $sec->Height / 1440);
    }
    $this->_pdf->SetXY($this->_secEndX, $this->_secEndY + 1/72);
    
    $this->_pdf->SetXY($this->_secStartX, $this->_secStartY);
    $this->_pdf->SetLineWidth($control->BorderWidth / 1440);
    $this->_borderColor(0x000000);
    $this->_pdf->Cell($this->_report->Width / 1440, $height / 1440, '', 'TLRB', 0, 'L', 0);

    $this->_pdf->SetXY($this->_secEndX, $this->_secEndY + 1/72);
    $this->_posY += $height;
  }  

  function sectionPrintStart(&$sec, $width, &$buffer)
  {
    $this->_pdf->sectionStart();
    if (($sec->_PagePart == 'Foot') and (!$this->DesignMode)) {
      $this->_pdf->SetY(-($sec->Height / 1440 + $this->_report->BottomMargin/1440));
    }

    $this->_secStartX = $this->_pdf->GetX();
    $this->_secStartY = $this->_pdf->GetY();

    $this->_backColor($sec->BackColor);
    $border = 0;
    $this->_pdf->Cell($sec->_parent->Width / 1440, $sec->Height / 1440, '', $border, 1, 'C', 1);

    $this->_secEndX = $this->_pdf->GetX();
    $this->_secEndY = $this->_pdf->GetY();

#    if ($this->_secEndY < $this->_secStartY) { #we had a page break
#      $this->_secStartY = ($this->_secEndY - $sec->Height / 1440);
#    }


  }

  function sectionPrintEnd(&$sec, $height, &$buffer)
  {

    $this->_pdf->sectionFlush();
    $this->_pdf->sectionEnd($xstart, $height, $width);


    $this->_pdf->SetXY($this->_secEndX, $this->_secEndY + 1/72);
    
    $this->_posY += $height;
  }

  /*********************************
   *  Page handling
   *********************************/
  function newPage()
  {
    if (!$this->_blankPage) {
      if (!$this->DesignMode) {
        $this->_report->_printNormalSection('PageFooter'); // FIXME: this has to be done by the Report class!!!
        $this->_report->OnPage();
      }  
      $this->_pageNo++;
    }
    $this->_blankPage = true;
  }

  function Page()
  {
    return $this->_pageNo;
  }

  function beforePrinting(&$section)
  {
    if ($this->DesignMode) {
      $y = $this->_pdf->GetY();
      if (($y + $section->Height / 1440) > ($this->_pdf->PageBreakTrigger)) {
        $this->newPage();
      }
      if ($this->_blankPage) {
        $this->_pdf->AddPage();
         //echo "pdf->AddPage();<br>";
        $this->_blankPage = false;
      }
    } else {
      if (($section->ForceNewPage == 1) or ($section->ForceNewPage == 3)) {
        $this->newPage();
      } else {
        $y = $this->_pdf->GetY();
        if (($y + $section->Height / 1440) > ($this->_pdf->PageBreakTrigger - $this->_report->PageFooter->Height / 1440)) {
          $this->newPage();
        }
      }
      if ($this->_blankPage) {
        $this->_pdf->AddPage();
         //echo "pdf->AddPage();<br>";
        $this->_blankPage = false;
        $this->_report->_printNormalSection('PageHeader'); // FIXME: this has to be done by the Report class!!!
      }
    }  
  }

  function afterPrinting(&$section, &$doItAgain)
  {
    if (!$this->DesignMode) {    
      if (($section->ForceNewPage == 2) or ($section->ForceNewPage == 3)) {
        $this->newPage();
      }
    }  
    $doItAgain = false;
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
    $x = ($control->Left / 1440+  $this->_secStartX);
    $y = ($control->Top / 1440 + $this->_secStartY);
    $width = $control->Width / 1440;
    $height = $control->Height / 1440;
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
        $this->_pdf->SetLineWidth(1 / 1440);
      } else {
        $this->_pdf->SetLineWidth($control->BorderWidth / 72);
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
    $height = 0.25;
    $falign = 'C';
    $fill = 1;
    $this->_pdf->Cell($width, $height, print_r($var, 1), '0', 0, $falign, $fill);
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


