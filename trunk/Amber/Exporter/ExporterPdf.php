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


  function &getExporterBasicClass(&$layout, $reset)
  {
    return PDF::getInstance($layout, $reset);
  }  



  /*********************************
   *  Report-pdf
   *********************************/

  function startReportSubExporter(&$report, $asSubreport = false, $isDesignMode = false)
  {
    $reset = (!$asSubreport);
    $this->_pdf =& $this->getExporterBasicClass($report->layout, $reset);

    if ($report->Controls) {
      foreach (array_keys($report->Controls) as $ctrlName) {
        if (!empty($report->Controls[$ctrlName]->FontName)) {
          $this->_pdf->registerFontFamily($report->Controls[$ctrlName]->FontName);
        }
      }
    }
    if (!$asSubreport) {
      $this->_pdf->SetCompression(false);
      $this->_pdf->SetRightMargin($layout->rightMargin);
      $this->_pdf->SetLeftMargin($layout->leftMargin);
      $this->_pdf->SetTopMargin($layout->topMargin);
      $this->_pdf->SetAutoPageBreak(false, $layout->bottomMargin);
  
      $this->_pdf->SetFont('helvetica');    // need to set font, drawcolor, fillcolor before AddPage
      $this->_pdf->SetDrawColor(0, 0, 0);   // else we get strange errors. prb fpdf does some optimisations which we break
      $this->_pdf->SetFillColor(0, 0, 0);
      $this->_pdf->AddPage();
    }
  }

  function endReportSubExporter(&$report)
  {
    if (!$this->_asSubreport) {
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

  function comment($s)
  {
    $this->_pdf->_out("\n%$s\n");
  }
  
  function _pageHeaderOrFooterEnd($posY, $width, $height, &$buff)
  {
    $this->_pdf->SetCoordinate(0, -$posY);
    $this->_pdf->SetClipping(0, 0, $width, $height);
    $this->comment("end Head/Foot-Section:1\n");
    $this->_pdf->_out($buff);
    $this->_pdf->RemoveClipping();
    $this->_pdf->RemoveCoordinate();
  }
  
  function outWindowRelative($deltaX, $deltaY, $x, $y, $w, $h, &$dataBuff)
  {
    $this->_pdf->SetClipping($x, $y, $w, $h);
    $this->_pdf->SetCoordinate($deltaX, $deltaY);
    $this->_pdf->_out($dataBuff);
    $this->_pdf->RemoveCoordinate();
    $this->_pdf->RemoveClipping();
  }
  
  function out(&$secBuff)
  {
    $this->_pdf->_out($secBuff);
  }
    
  function outSectionEnd()
  {
    $this->_pdf->RemoveClipping();
    $this->_pdf->RemoveCoordinate();
  }
  
  function outSectionStart($y, $w, $h, $backColor, $sectionName='')
  {
    $this->_pdf->SetCoordinate(0, -$y);
    $this->_pdf->SetClipping(0, 0, $w, $h);
    
    $this->_pdf->SetXY(0, 0);
    $this->_pdf->_backColor($backColor);
    $fill = true;
    $text = '';
    $border = 0;
    $ln = 0; //pos after printing
    $align = 'C';
    $backstyle= 1;
    $this->_pdf->Cell($w, $h, $text, $border, $ln, $align, $fill);
  }

  
  function Bookmark($txt,$level=0,$y=0, $pageNo, $posYinPage, $inReport)
  {
    $this->_pdf->Bookmark($txt,$level,$y, $pageNo, $posYinPage, $inReport);
  }
  
  function AddPage()
  {
    $this->_pdf->AddPage();
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
      $para->content = "\n%Start SubReport\n" . $rep->subReportBuff . "\n%End SubReport\n"; 
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
