<?php

/**
 *
 * @package Amber
 * @subpackage Exporter
 *
 */

define('FPDF_FONTPATH','fpdf/font/');

ExporterFactory::register('pdf', 'ExporterFPdf');
ExporterFactory::register('.pdf', 'ExporterFPdf');
ExporterFactory::register('fpdf', 'ExporterFPdf');
ExporterFactory::register('testpdf', 'ExporterFPdf');


/**
 *
 * @package Amber
 * @subpackage Exporter
 *
 */




class ExporterFPdf extends Exporter
{
  var $type = 'fpdf';
  var $_pdf;

  var $firstPage = true;


  function &getExporterBasicClass(&$layout, $reset)
  {
    require_once('fpdf/fpdf.php');
    require_once('PDF.inc.php');

    return PDF::getInstance($layout, $reset);
  }



  /*********************************
   *  Report-pdf
   *********************************/

  function startReportSubExporter(&$report, $asSubreport = false)
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


  ////////////////////////
  //
  // outWindowRelative -- place Section in $dataBuff on Page
  // section's x-coordinate is relative to report's left (==0)
  // section's y-coordinate is relative to report's top (ever growing)
  //
  // x, y: coordinates page area (header, body footer) relative to paper left/top (coordinates include left/top margins)
  // w, h: width/height of page area (header, body footer)
  // deltaX coordinates of page body start (left corner) relative to report's left side
  //
  // purpose:
  //    1. clip area to get printed on page
  //    2. transform placement of section from report-relative to page-relative
  //
  ////////////////////////


  function outWindowRelative($deltaX, $x, $y, $w, $h, &$dataBuff)
  {
    $this->_pdf->SetClipping($x, $y, $w, $h);
    $this->_pdf->SetCoordinate($x - $deltaX, -$y);
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
    $this->_pdf->SetClipping(0, 0, $w, $h + SectionBorder);

    $this->_pdf->SetXY(0, 0 + SectionBorder);
    $this->_pdf->_backColor($backColor);
    $fill = true;
    $text = '';
    $border = 0;
    $ln = 0; //pos after printing
    $align = 'C';
    $backstyle= 1;
    $this->_pdf->Cell($w, $h, $text, $border, $ln, $align, $fill);
  }


  function Bookmark($txt,$level=0, $pageNo, $posYinPage)
  {  
    $this->_pdf->Bookmark($txt, $level, $pageNo, $posYinPage);
  }

  function startPage()
  {
    if (!$this->firstPage) {
      $this->_pdf->AddPage();
    }
    $this->firstPage = false;
  }

  function endPage()
  {
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





  function printNormal(&$control, $content)
  {
    if (!$control->isVisible()) {
      return;
    }

    $type = strtolower(get_class($control));
    #echo $type;
    if ($type == 'checkbox') {
      return $this->printNormalCheckBox($control, $content);
    } elseif ($type == 'subreport') {
      return $this->printNormalSubReport($control, $content);
    }
    #$content = $type;

    $para = new printBoxparameter;

    $para->italic = $control->FontItalic;
    $para->bold  = ($control->FontWeight >= 600);
    $para->underline = $control->FontUnderline;
    $para->fsize = $control->FontSize;

    $para->font = $control->FontName;
    if (($type == 'textbox') || ($type == 'label')) {
      $para->falign = $this->_pdf_textalign($control->TextAlign());
    }  
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

  function printNormalCheckBox(&$control, $content)
  {
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

  function printNormalSubReport(&$control, $content)
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
      $rep->run('pdf');
      $para->content = "\n%Start SubReport\n" . $rep->subReportBuff . "\n%End SubReport\n";
    }
    #$para->content = "(TEST)";
    $this->_pdf->printBoxPdf($para);

  }

  function printDesign(&$control, $content)
  {
    $this->printNormal($control, $content);
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

    if (isset($alignments[$textalign])) {
      return $alignments[$textalign];
    } else {
      return 'L';
    }
  }

}
