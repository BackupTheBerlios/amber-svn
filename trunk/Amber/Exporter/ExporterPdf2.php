<?php

/**
 *
 * @package PHPReport
 * @subpackage Exporter
 *
 */

define('FPDF_FONTPATH','fpdf/font/');
require_once('fpdf/fpdf.php');

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


  /*********************************
   *  Report-pdf
   *********************************/
  function getPreamble(&$report)
  {
    parent::getPreamble($report);
    $this->_blankPage = true;
    $orient = $this->_pdf_orientation($report->Orientation);
    $size = array($report->PaperWidth / 1440, $report->PaperHeight / 1440);
    #Amber::dump($size);
    $this->_pdf =& new PDF($orient, 'in', $size);
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
  function sectionPrintDesignHeader($text)
  {
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
    $this->_pdf->Cell($this->_report->Width / 1440, 12/72, $text, $border, 1, 'L', 1);

    $this->_secEndX = $this->_pdf->GetX();
    $this->_secEndY = $this->_pdf->GetY();
    if ($this->_secEndY < $this->_secStartY) { #we had a page break
      $this->_secStartY = ($this->_secEndY - $sec->Height / 1440);
    }
    $this->_pdf->SetXY($this->_secEndX, $this->_secEndY + 1/72);

    $this->_pdf->SetXY($this->_secStartX, $this->_secStartY);
    $this->_pdf->SetLineWidth($control->BorderWidth / 1440);
    $this->_borderColor(0x000000);
    $this->_pdf->Cell($this->_report->Width / 1440, 12/72, '', 'TLRB', 0, 'L', 0);

    $this->_pdf->SetXY($this->_secEndX, $this->_secEndY + 1/72);
  }

  function sectionPrintStart(&$sec)
  {
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
    if ($this->_secEndY < $this->_secStartY) { #we had a page break
      $this->_secStartY = ($this->_secEndY - $sec->Height / 1440);
    }
  //    echo  'pdf->Cell(' . $sec->_parent->Width/1440 . ', ' . $sec->Height/1440 . ',"",1,1,"C",1);<br>';
}

  function sectionPrintEnd(&$sec, $Height, &$buffer)
  {
    $this->_pdf->SetXY($this->_secEndX, $this->_secEndY + 1/72);
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
/**
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 * @package PHPReport
 * @subpackage Exporter
 *
 */
class PDF extends FPDF
{

  /*****************************************
   * special Clipping function from Olivier
   *****************************************/

  function SetClipping($x,$y,$w,$h)
  {
    $this->_out(sprintf('q %.2f %.2f %.2f %.2f re W n',$x*$this->k,($this->h-$y)*$this->k,$w*$this->k,-$h*$this->k));
  }

  function RemoveClipping()
  {
    $this->_out('Q');
  }

  /*****************************************
  * special Bookmark function from Olivier (see fpdf.org, scripts
  *****************************************/
  var $outlines=array();
  var $OutlineRoot;

  function Bookmark($txt,$level=0,$y=0)
  {
      if($y==-1)
          $y=$this->GetY();
      $this->outlines[]=array('t'=>$txt,'l'=>$level,'y'=>$y,'p'=>$this->PageNo());
  }

  function _putbookmarks()
  {
      $nb=count($this->outlines);
      if($nb==0)
          return;
      $lru=array();
      $level=0;
      foreach($this->outlines as $i=>$o)
      {
          if($o['l']>0)
          {
              $parent=$lru[$o['l']-1];
              //Set parent and last pointers
              $this->outlines[$i]['parent']=$parent;
              $this->outlines[$parent]['last']=$i;
              if($o['l']>$level)
              {
                  //Level increasing: set first pointer
                  $this->outlines[$parent]['first']=$i;
              }
          }
          else
              $this->outlines[$i]['parent']=$nb;
          if($o['l']<=$level and $i>0)
          {
              //Set prev and next pointers
              $prev=$lru[$o['l']];
              $this->outlines[$prev]['next']=$i;
              $this->outlines[$i]['prev']=$prev;
          }
          $lru[$o['l']]=$i;
          $level=$o['l'];
      }
      //Outline items
      $n=$this->n+1;
      foreach($this->outlines as $i=>$o)
      {
          $this->_newobj();
          $this->_out('<</Title '.$this->_textstring($o['t']));
          $this->_out('/Parent '.($n+$o['parent']).' 0 R');
          if(isset($o['prev']))
              $this->_out('/Prev '.($n+$o['prev']).' 0 R');
          if(isset($o['next']))
              $this->_out('/Next '.($n+$o['next']).' 0 R');
          if(isset($o['first']))
              $this->_out('/First '.($n+$o['first']).' 0 R');
          if(isset($o['last']))
              $this->_out('/Last '.($n+$o['last']).' 0 R');
          $this->_out(sprintf('/Dest [%d 0 R /XYZ 0 %.2f null]',1+2*$o['p'],($this->h-$o['y'])*$this->k));
          $this->_out('/Count 0>>');
          $this->_out('endobj');
      }
      //Outline root
      $this->_newobj();
      $this->OutlineRoot=$this->n;
      $this->_out('<</Type /Outlines /First '.$n.' 0 R');
      $this->_out('/Last '.($n+$lru[0]).' 0 R>>');
      $this->_out('endobj');
  }

  function _putresources()
  {
      parent::_putresources();
      $this->_putbookmarks();
  }

  function _putcatalog()
  {
      parent::_putcatalog();
      if(count($this->outlines)>0)
      {
          $this->_out('/Outlines '.$this->OutlineRoot.' 0 R');
          $this->_out('/PageMode /UseOutlines');
      }
  }


  /*var $_paperSize =
    ( '4A0' => array('w'=>	1682	, 'h' =>	2378	),
      '2A0' => array('w'=>	1189	, 'h' =>	1682	),
      'A0'  => array('w'=>	 841	, 'h' =>	1189	),
      'A1'  => array('w'=>	 594	, 'h' =>	 841	),
      'A2'  => array('w'=>	 420	, 'h' =>	 594	),
      'A3'  => array('w'=>	 297	, 'h' =>	 420	),
      'A4'  => array('w'=>	 210	, 'h' =>	 297	),
      'A5'  => array('w'=>	 148	, 'h' =>	 210	),
      'A6'  => array('w'=>	 105	, 'h' =>	 148	),
      'A7'  => array('w'=>	  74	, 'h' =>	 105	),
      'A8'  => array('w'=>	  52	, 'h' =>	  74	),
      'A9'  => array('w'=>	  37	, 'h' =>	  52	),
      'A10' => array('w'=>	  26	, 'h' =>	  37	),
      'B0'  => array('w'=>	1000	, 'h' =>	1414	),
      'B1'  => array('w'=>	 707	, 'h' =>	1000	),
      'B2'  => array('w'=>	 500	, 'h' =>	 707	),
      'B3'  => array('w'=>	 353	, 'h' =>	 500	),
      'B4'  => array('w'=>	 250	, 'h' =>	 353	),
      'B5'  => array('w'=>	 176	, 'h' =>	 250	),
      'B6'  => array('w'=>	 125	, 'h' =>	 176	),
      'B7'  => array('w'=>	  88	, 'h' =>	 125	),
      'B8'  => array('w'=>	  62	, 'h' =>	  88	),
      'B9'  => array('w'=>	  44	, 'h' =>	  62	),
      'B10' => array('w'=>	  31	, 'h' =>	  44	),
      'C0'  => array('w'=>	 917	, 'h' =>	1297	),
      'C1'  => array('w'=>	 648	, 'h' =>	 917	),
      'C2'  => array('w'=>	 458	, 'h' =>	 648	),
      'C3'  => array('w'=>	 324	, 'h' =>	 458	),
      'C4'  => array('w'=>	 229	, 'h' =>	 324	),
      'C5'  => array('w'=>	 162	, 'h' =>	 229	),
      'C6'  => array('w'=>	 114	, 'h' =>	 162	),
      'C7'  => array('w'=>	  81	, 'h' =>	 114	),
      'C8'  => array('w'=>	  57	, 'h' =>	  81	),
      'C9'  => array('w'=>	  40	, 'h' =>	  57	),
      'C10' => array('w'=>	  28	, 'h' =>	  40	),
      'Letter' => array('w'=>	216	, 'h' =>	279	),
      'Legal' => array('w'=>	216	, 'h' =>	356	),
      'Executive' => array('w'=>	190	, 'h' =>	254	),
      'Ledger/Tabloid' => array('w'=>	279	, 'h' =>	432	));
*/

}
