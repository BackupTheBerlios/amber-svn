<?php
/**
 *
 * @package PHPReport
 * @subpackage Exporter
 *
 */
 
class subReportBuff
{
  var $subReportsubReportIndex;
  var $subReportbuff;
  var $sectionIndex;
  var $sectionBuff;
  
  function out($s)
  { 
    if ($this->sectionIndex > $this->subReportIndex) {
      $this->sectionBuff[$this->sectionIndex] .= $s . "\n";
    } elseif ($this->subReportIndex) {
      $this->subReportbuff[$this->subReportIndex] .= $s . "\n";
    }  
  }

  function inSectionOrSubReport()
  {
    return (($this->sectionIndex > $this->subReportIndex) or ($this->subReportIndex > 0));
  }
  


  function subReportPush()
  {
   $this->subReportIndex++;
   $this->subReportBuff[$this->subReportIndex] = '';
  }
   
  function subReportPop()
  {
    $this->subReportIndex--;
    return $this->subReportbuff[$this->subReportIndex + 1];
  }
  
  function subReportGetPopped()
  {
    return $this->subReportbuff[$this->subReportIndex + 1];  
  }
  
  function sectionPush()
  {
    $this->sectionIndex++;
    $this->sectionBuff[$this->sectionIndex] = '';
  }
  
  function sectionPop()
  {
    $this->sectionIndex--;
    return $this->sectionBuff[$this->sectionIndex + 1];
  }
  
  function getSectionIndexForCommentOnly()
  {
    return $this->sectionIndex;
  }  
    
} 
 
class PDF extends FPDF
{

////////////////////////////////////////////////////////
//
// stuff to move
//
//////////////////////////////////////////////////////// 

  var $_inReport;       // bool 


  //////////////////////////////////////////////////////////////////////////
  //
  // startReport / endReport 
  //
  // inside a report the FPDF's output gets cached in 
  //      $this->reportBuff->_reportPages[$this->reportBuff->actpageNo][$this->reportBuff->sectionType]
  // where 
  //      $this->reportBuff->actpageNo     is the current page number and
  //      $this->reportBuff->sectionType   is 'Head' for page header, 'Foot' for page footer or '' for page body
  //
  // when the report ends, the output is processed and possible divided horizontal
  // among several pages, if the report is wider than one page
  //
  //////////////////////////////////////////////////////////////////////////
       


  function pageHeaderEnd()
  {
   $this->reportBuff->sectionType = 'Head';
   $this->_pageHeaderOrFooterEnd($this->reportBuff->actpageNo * $this->layout->printHeight, $this->layout->pageHeaderHeight);
  }

  function pageFooterEnd()
  {
    $this->reportBuff->sectionType = 'Foot';
    $this->_pageHeaderOrFooterEnd($this->reportBuff->actpageNo * $this->layout->printHeight, $this->layout->pageFooterHeight);
  }

        
////////////////////////////////////////////////////////
//
// stuff to sort out
//
//////////////////////////////////////////////////////// 

  
  //////////////////////////////////////////////////////////////////////////
  //
  //  _out - overwriting FPDF's private _out method used for "printing"
  //
  //  see startSection/endSection and startReport/endReport below
  //
  //////////////////////////////////////////////////////////////////////////
  function _out($s)
  {
    if($this->state <> 2) {
      parent::_out($s);
    } elseif ($this->subReportBuff->inSectionOrSubReport()) {
      $this->subReportBuff->out($s);
    } elseif ($this->_inReport) {
      $this->reportBuff->out($s);
    } else {
      parent::_out($s);
    }
  }
  
  function startReportBuffering(&$reportBuff)
  {
    if ($this->_inReport) {
      Amber::showError('Error', 'startReport: a report is already started!');
      die();
    }  
    $this->_inReport = true;
    $this->reportBuff =& $reportBuff;
  }

  function endReportBuffering()
  {
    if (!$this->_inReport) {
      Amber::showError('Error', 'endReport: no report open');
      die();
    }  
    $this->_inReport = false;
  }
  
  
       

  //////////////////////////////////////////////////////////////////////////
  //
  // startSection / endSection 
  //
  // inside a section the FPDF's output gets cached in $this->_buff
  // any positioning inside a section is relative to the section, not the page
  //
  // when the section ends, the output is processed and possible divided vertical
  // among several pages (page header and footer are output when needed)
  // the section's KeepTogether property is taken into account
  //
  //////////////////////////////////////////////////////////////////////////
  
  
  function startSection()
  {
    $this->subReportBuff->sectionPush();
    $this->comment('Start Section:' . ($this->subReportBuff->getSectionIndexForCommentOnly()));
    $this->SetXY(0, 0);
  }
  
  function endSection($sectionHeight, $keepTogether)
  {
    if ($this->subReportBuff->subReportIndex) {
      $this->endSectionSubReport($sectionHeight, $keepTogether);
      return;
    }  
    $this->comment("end Body-Section:" . ($this->subReportBuff->getSectionIndexForCommentOnly()) . "\n");
    $secBuff = $this->subReportBuff->sectionPop();
    $startPage = floor($this->reportBuff->posY / $this->layout->printHeight);
    $endPage   = floor(($this->reportBuff->posY + $sectionHeight) / $this->layout->printHeight);
    if ($keepTogether and ($startPage <> $endPage)) {
      if ($this->reportBuff->posY > ($startPage * $this->layout->printHeight)) { // page not blank
        $this->reportBuff->newPage();
        $startPage = floor($this->reportBuff->posY / $this->layout->printHeight);
        $endPage   = floor(($this->reportBuff->posY + $sectionHeight) / $this->layout->printHeight);
      }
    }

    for ($page = $startPage; $page <= $endPage; $page++) {
      if (($page <> $this->reportBuff->actpageNo)) {
        if ($this->reportBuff->actpageNo >= 0) {
          $this->_exporter->printPageFooter();
        }
        $this->reportBuff->actpageNo = $page;
        $this->_exporter->printPageHeader();
      }
      $this->reportBuff->sectionType = '';
      $this->outSection(0, $this->reportBuff->posY, $this->layout->reportWidth, $sectionHeight, $page - $startPage + 1, $this->_exporter, $secBuff);
    }
    $this->reportBuff->posY += $sectionHeight;
  }

  function endSectionSubReport($sectionHeight, $keepTogether)
  {
    $this->comment("end Subreport-Body-Section:" . ($this->subReportBuff->getSectionIndexForCommentOnly()) . "\n");
    $buff = $this->subReportBuff->sectionPop();

    $this->reportBuff->sectionType = '';
    $this->SetCoordinate(0, -$this->reportBuff->posY);
    $this->SetClipping(0, 0, $this->layout->reportWidth, $sectionHeight);

    $formatCount = 1;
    $this->_exporter->onPrint($cancel, $formatCount);
    if (!$cancel) {
      $this->_out($buff);
    }

    $this->RemoveClipping();
    $this->RemoveCoordinate();
    $this->reportBuff->posY += $sectionHeight;
  }

  function _pageHeaderOrFooterEnd($posY, $height)
  {
    $buff = $this->subReportBuff->sectionPop();
    $this->SetCoordinate(0, -$posY);
    $this->SetClipping(0, 0, $this->layout->reportWidth, $height);
    $this->comment("end Head/Foot-Section:" . (getSectionIndexForCommentOnly + 1) . "\n");
    $this->_out($buff);
    $this->RemoveClipping();
    $this->RemoveCoordinate();
  }
  
  

////////////////////////////////////////////////////////
//
// stuff to stay
//
//////////////////////////////////////////////////////// 
        
   var $_fontList = array(
        'arial' => 'helvetica', 
        'ms sans serif' => 'helvetica', 
        'small fonts' => 'helvetica',
        
        'courier new' => 'courier');
  
  /**
   *
   * @access public
   * @param string orientation: 'P' - portrait, 'L' - Landscape
   * @param string or number 'pt' point, 'mm' millimeter, cm centimeter, in inch, 
            or number/fraction of points to use in usercoordinates
   * @param string format 
   * @return &object the PDF-instance
   *
   * PDF is singleton: one instance to handle report and subreports
   *
   */
   
  function &getInstance(&$layout, $reset)
  {
    static $instance = null;

    if (is_null($instance) or $reset) {
      $size = array($layout->paperWidth, $layout->paperHeight);
      $instance = new PDF('p', $layout->unit, $size);
      $instance->subReportBuff =& new subReportBuff();
    }

    return $instance;
  }

  function init(&$exporter, &$layout)
  {
    $this->_exporter =& $exporter;
    $this->layout = $layout;
    $this->SetCompression(false);
    $this->reportBuff->posY = 0;

    $this->SetCompression(false);
    $this->SetRightMargin($layout->rightMargin);
    $this->SetLeftMargin($layout->leftMargin);
    $this->SetTopMargin($layout->topMargin);
    $this->SetAutoPageBreak(false, $layout->bottomMargin);

    $this->SetFont('helvetica');    // need to set font, drawcolor, fillcolor before AddPage
    $this->SetDrawColor(0, 0, 0);   // else we get strange errors. prb fpdf does some optimisations which we break
    $this->SetFillColor(0, 0, 0);
    $this->AddPage();
  }
  
  function comment($s)
  {
    $this->_out("\n%$s\n");
  }
  
  function startComment($s)
  // this function mere for identity during refactoring. replace with comment
  {
    $this->_out("\n\n%$s");
  }      
  
  function outWindowRelative($deltaX, $deltaY, $x, $y, $w, $h, &$dataBuff)
  {
    $this->SetClipping($x, $y, $w, $h);
    $this->SetCoordinate($deltaX, $deltaY);
    $this->_out($dataBuff);
    $this->RemoveCoordinate();
    $this->RemoveClipping();
  }
  
  function outSection($x, $y, $w, $h, $callCnt, &$exporter, &$secBuff)
  {
    $this->SetCoordinate(-$x, -$y);
    $this->SetClipping(0, 0, $w, $h);

    if (!$exporter->DesignMode) {
      $exporter->onPrint($cancel, $callCnt);
      if (!$cancel) {
        $this->_out($secBuff);
      }
    } else {
      $this->_out($secBuff);
    } 
    $this->RemoveClipping();
    $this->RemoveCoordinate();
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

    //echo "'".$para->font."' => '".$this->_fontList[$control->FontName]."'<br>";
    $this->SetFont($this->_fontList[$para->font], $fstyle, $para->fsize);

    $this->_backColor($para->backcolor);
    $this->_textColor($para->forecolor);
    $this->SetXY($para->x, $para->y);
    $this->SetClipping($para->x, $para->y, $para->width, $para->height);
    $this->Cell($para->width, $para->height, $para->content, '0', 0, $para->falign, $para->backstyle);
    $this->RemoveClipping();
    $this->SetXY($para->x, $para->y);
    if ($para->borderstyle <> 0) {
      $this->_borderColor($para->bordercolor);
      if ($para->borderwidth == 0) {
        $this->SetLineWidth(1);
      } else {
        $this->SetLineWidth($para->borderwidth);
      }
      $this->Cell($para->width, $para->height, '', 'RLTB', 0, $para->falign, 0);
    }
  }
  
  function printBoxPdf(&$para)
  {
    $this->SetXY($para->x, $para->y);
    $this->SetClipping($para->x, $para->y, $para->width, $para->height);
    $this->SetCoordinate(-$para->x, -$para->y);
    
    $this->_out($para->content);
    
    $this->RemoveCoordinate();
    $this->RemoveClipping();
    $this->SetXY($para->x, $para->y);
    if ($para->borderstyle <> 0) {
      $this->_borderColor($para->bordercolor);
      if ($para->borderwidth == 0) {
        $this->SetLineWidth(1);
      } else {
        $this->SetLineWidth($para->borderwidth);
      }
      $this->Cell($para->width, $para->height, '', 'RLTB', 0, $para->falign, 0);
    }
  }

  function _backColor($color)
  {
    $r = ($color >> 16) & 255;
    $g = ($color >>  8) & 255;
    $b = ($color) & 255;
    $this->SetFillColor($r, $g, $b);
    //echo "pdf->SetFillColor($r, $g, $b);<br>";
  }
  function _textColor($color)
  {
    $r = ($color >> 16) & 255;
    $g = ($color >>  8) & 255;
    $b = ($color) & 255;
    $this->SetTextColor($r, $g, $b);
    //echo "pdf->SetFillColor($r, $g, $b);<br>";
  }
  function _borderColor($color)
  {
    $r = ($color >> 16) & 255;
    $g = ($color >>  8) & 255;
    $b = ($color) & 255;
    $this->SetDrawColor($r, $g, $b);
    //echo "pdf->SetFillColor($r, $g, $b);<br>";
  }

  function registerFontFamily($name)
  {
    $font = strtolower($name);
    if (!$this->_fontList[$font]) {
      // if You get
      // FPDF error: Could not include font definition file
      // uncomment the following line to find font-file
      //echo $font . '<br>';
      $this->AddFont($font);
      $this->AddFont($font, 'B');
      $this->AddFont($font, 'I');
      $this->AddFont($font, 'BI');
      $this->_fontList[$font] = $font;
    }
  }


  
  
  /**
  *
  * Origin of coordinates is moved to (x,y)
  *
  * @access public
  * @param  number x-coordinate of origin
  * @param  number y-coordinate of origin
  */
  function SetCoordinate($x, $y)
  {
    $this->_out(sprintf('q 1 0 0 1 %.2f %.2f cm', $x * $this->k, $y * $this->k));
  }

  function RemoveCoordinate()
  {
    $this->_out('Q');
  }



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
    if (!$this->_inReport) {
      if($y==-1)
        $y=$this->GetY();
      $this->outlines[]=array('t'=>$txt,'l'=>$level,'y'=>$y,'p'=>$this->PageNo());
    } else {
      if($y == -1)
        $y = $this->reportBuff->posY - ($this->reportBuff->actpageNo * $this->layout->printHeight);
      $p = $this->reportBuff->actpageNo + 1;
      if ($p <= 0)
        $p = 1;

      $this->outlines[]=array('t'=>$txt,'l'=>$level,'y'=>$y,'p'=>$p);
    }
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

  /*****************************************
  * special Bookmark function from Ron Korving (see http://www.fpdf.org/en/script/script49.php)
  *****************************************/

  function WordWrap(&$text, $maxwidth)
  {
      $text = trim($text);
      if ($text==='')
          return 0;
      $space = $this->GetStringWidth(' ');
      $lines = explode("\n", $text);
      $text = '';
      $count = 0;
  
      foreach ($lines as $line)
      {
          $words = preg_split('/ +/', $line);
          $width = 0;
  
          foreach ($words as $word)
          {
              $wordwidth = $this->GetStringWidth($word);
              if ($width + $wordwidth <= $maxwidth)
              {
                  $width += $wordwidth + $space;
                  $text .= $word.' ';
              }
              else
              {
                  $width = $wordwidth + $space;
                  $text = rtrim($text)."\n".$word.' ';
                  $count++;
              }
          }
          $text = rtrim($text)."\n";
          $count++;
      }
      $text = rtrim($text);
      return $count;
  }
  
  

  /********************************************
  *
  *  changed methods of fpdf
  *
  *********************************************/



 /*********
  *  constructor (add new unit)
  *
  * + elseif($unit=='twips')
  * + $this->k=1/20;
  *
  */


  function PDF($orientation='P',$unit='mm',$format='A4')
{
	//Some checks
	$this->_dochecks();
	//Initialization of properties
	$this->page=0;
	$this->n=2;
	$this->buffer='';
	$this->pages=array();
	$this->OrientationChanges=array();
	$this->state=0;
	$this->fonts=array();
	$this->FontFiles=array();
	$this->diffs=array();
	$this->images=array();
	$this->links=array();
	$this->InFooter=false;
	$this->lasth=0;
	$this->FontFamily='';
	$this->FontStyle='';
	$this->FontSizePt=12;
	$this->underline=false;
	$this->DrawColor='0 G';
	$this->FillColor='0 g';
	$this->TextColor='0 g';
	$this->ColorFlag=false;
	$this->ws=0;
	//Standard fonts
	$this->CoreFonts=array('courier'=>'Courier','courierB'=>'Courier-Bold','courierI'=>'Courier-Oblique','courierBI'=>'Courier-BoldOblique',
		'helvetica'=>'Helvetica','helveticaB'=>'Helvetica-Bold','helveticaI'=>'Helvetica-Oblique','helveticaBI'=>'Helvetica-BoldOblique',
		'times'=>'Times-Roman','timesB'=>'Times-Bold','timesI'=>'Times-Italic','timesBI'=>'Times-BoldItalic',
		'symbol'=>'Symbol','zapfdingbats'=>'ZapfDingbats');
	//Scale factor
	if($unit=='pt')
		$this->k=1;
	elseif($unit=='mm')
		$this->k=72/25.4;
	elseif($unit=='cm')
		$this->k=72/2.54;
	elseif($unit=='in')
		$this->k=72;
  elseif(is_numeric($unit))
    $this->k=$unit;
	else
		$this->Error('Incorrect unit: '.$unit);
	//Page format
	if(is_string($format))
	{
		$format=strtolower($format);
		if($format=='a3')
			$format=array(841.89,1190.55);
		elseif($format=='a4')
			$format=array(595.28,841.89);
		elseif($format=='a5')
			$format=array(420.94,595.28);
		elseif($format=='letter')
			$format=array(612,792);
		elseif($format=='legal')
			$format=array(612,1008);
		else
			$this->Error('Unknown page format: '.$format);
		$this->fwPt=$format[0];
		$this->fhPt=$format[1];
	}
	else
	{
		$this->fwPt=$format[0]*$this->k;
		$this->fhPt=$format[1]*$this->k;
	}
	$this->fw=$this->fwPt/$this->k;
	$this->fh=$this->fhPt/$this->k;
	//Page orientation
	$orientation=strtolower($orientation);
	if($orientation=='p' or $orientation=='portrait')
	{
		$this->DefOrientation='P';
		$this->wPt=$this->fwPt;
		$this->hPt=$this->fhPt;
	}
	elseif($orientation=='l' or $orientation=='landscape')
	{
		$this->DefOrientation='L';
		$this->wPt=$this->fhPt;
		$this->hPt=$this->fwPt;
	}
	else
		$this->Error('Incorrect orientation: '.$orientation);
	$this->CurOrientation=$this->DefOrientation;
	$this->w=$this->wPt/$this->k;
	$this->h=$this->hPt/$this->k;
	//Page margins (1 cm)
	$margin=28.35/$this->k;
	$this->SetMargins($margin,$margin);
	//Interior cell margin (1 mm)
	$this->cMargin=$margin/10;
	//Line width (0.2 mm)
	$this->LineWidth=.567/$this->k;
	//Automatic page break
	$this->SetAutoPageBreak(true,2*$margin);
	//Full width display mode
	$this->SetDisplayMode('fullwidth');
	//Compression
	$this->SetCompression(true);
}



 /*********
  *  SetFont (remove optimisation)
  *
  *	- if($this->FontFamily==$family and $this->FontStyle==$style and $this->FontSizePt==$size)
  *	-    return;
  *
  */

 function SetFont($family,$style='',$size=0)
{
	//Select a font; size given in points
	global $fpdf_charwidths;

	$family=strtolower($family);
	if($family=='')
		$family=$this->FontFamily;
	if($family=='arial')
		$family='helvetica';
	elseif($family=='symbol' or $family=='zapfdingbats')
		$style='';
	$style=strtoupper($style);
	if(is_int(strpos($style,'U')))
	{
		$this->underline=true;
		$style=str_replace('U','',$style);
	}
	else
		$this->underline=false;
	if($style=='IB')
		$style='BI';
	if($size==0)
		$size=$this->FontSizePt;
	//Test if font is already selected
#	if($this->FontFamily==$family and $this->FontStyle==$style and $this->FontSizePt==$size)
#		return;
	//Test if used for the first time
	$fontkey=$family.$style;
	if(!isset($this->fonts[$fontkey]))
	{
		//Check if one of the standard fonts
		if(isset($this->CoreFonts[$fontkey]))
		{
			if(!isset($fpdf_charwidths[$fontkey]))
			{
				//Load metric file
				$file=$family;
				if($family=='times' or $family=='helvetica')
					$file.=strtolower($style);
				$file.='.php';
				if(defined('FPDF_FONTPATH'))
					$file=FPDF_FONTPATH.$file;
				include($file);
				if(!isset($fpdf_charwidths[$fontkey]))
					$this->Error('Could not include font metric file');
			}
			$i=count($this->fonts)+1;
			$this->fonts[$fontkey]=array('i'=>$i,'type'=>'core','name'=>$this->CoreFonts[$fontkey],'up'=>-100,'ut'=>50,'cw'=>$fpdf_charwidths[$fontkey]);
		}
		else
			$this->Error('Undefined font: '.$family.' '.$style);
	}
	//Select it
	$this->FontFamily=$family;
	$this->FontStyle=$style;
	$this->FontSizePt=$size;
	$this->FontSize=$size/$this->k;
	$this->CurrentFont=&$this->fonts[$fontkey];
	if($this->page>0)
		$this->_out(sprintf('BT /F%d %.2f Tf ET',$this->CurrentFont['i'],$this->FontSizePt));
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


?>