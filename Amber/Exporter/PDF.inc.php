<?php
/**
 *
 * @package PHPReport
 * @subpackage Exporter
 *
 */
class PDF extends FPDF
{
  
  var $_inSection;
  var $_inReport;
  var $_buff;
  var $_reportPages;
  var $_actPageNo;
  
    
  function _out($s)
  {
    if($this->state <> 2) {
      parent::_out($s);      
    } elseif ($this->_inSection) {
      $this->_buff .= $s . "\n";
      #parent::_out($s);
    } elseif ($this->_inReport) {
      $this->_reportPages[$this->_actPageNo] .= $s . "\n";
    } else {
      parent::_out($s);
    }
  }
  
  function sectionStart()
  {
    $this->_buff = '';
    $this->_inSection = true;
    $this->_out("% SectionStart");
    $this->SetXY(0, 0);
  }
  
  function sectionEnd($sectionHeight)
  {
    $this->_out("% SectionEnd");
    $this->_inSection = false;
    $startPage = floor($this->_posY / $this->_printHeight);
    $endPage   = floor(($this->_posY + $sectionHeight) / $this->_printHeight);

    for ($page = $startPage; $page <= $endPage; $page++) {
      if ($page <> $this->_actPageNo) {
        if ($this->_actPageNo > 0) {
          $this->_exporter->printPageFooter();
          $this->_out("% pageFooter: ".$this->_actPageNo);
        }  
        $this->_actPageNo = $page;
        $this->_out("% [pageHeader: ".$this->_actPageNo);
        $this->_exporter->printPageHeader();
        $this->_out("% ]pageHeader: ".$this->_actPageNo);
      }
                  
      $this->SetCoordinate(0, -$this->_posY);
      $this->SetClipping(0, 0, $this->_reportWidth, $sectionHeight);
      $this->_out($this->_buff);
      $this->RemoveClipping();   
      $this->RemoveCoordinate();
    }
    $this->_posY += $sectionHeight;
  }  

  function pageHeaderEnd()
  {
#    $this->_pageHeaderOrFooterEnd($this->_actPageNo * $this->_printHeight - $this->tMargin, $this->_headerHeight);
    $this->_pageHeaderOrFooterEnd($this->_actPageNo * $this->_printHeight, $this->_headerHeight);
  }  
  
  function pageFooterEnd()
  {
    $this->_pageHeaderOrFooterEnd($this->_actPageNo * $this->_printHeight + $this->_printHeight, $this->_footerHeight);
  }
  
  function _pageHeaderOrFooterEnd($posY, $height)
  {
    $this->_inSection = false;
    $this->SetCoordinate(0, -$posY);
    $this->SetClipping(0, 0, $this->_reportWidth, $height);
    $this->_out($this->_buff);
    $this->RemoveClipping();   
    $this->RemoveCoordinate();
  }
  
      
  function reportStart(&$exporter, $width, $headerHeight=0, $footerHeight=0)
  {
    $this->_exporter =& $exporter;
    $this->_reportWidth = $width;
    $this->_headerHeight = $headerHeight;
    $this->_footerHeight = $footerHeight;
    $this->_printWidth  = ($this->w - $this->lMargin - $this->rMargin); //width of printable area of page (w/o morgins)
    $this->_printHeight = ($this->h - $this->tMargin - $this->bMargin - $this->_footerHeight - $this->_headerHeight); //height of printable area of page (w/o morgins)
    $this->_posY = 0;
    $this->_actPageNo = 0;

    $this->SetFont('helvetica');    // need to set font, drawcolor, fillcolor before AddPage 
    $this->SetDrawColor(0, 0, 0);   // else we get strange errors. prb fpdf does some optimisations which we break
    $this->SetFillColor(0, 0, 0);
    $this->AddPage();
    $this->_inReport = true;
  }
  
  function reportEnd()
  {
    $this->_exporter->printPageFooter();
    $this->_inReport = false;
    $firstPage = true;  //first page is out
    
    $endPageX = floor($this->_reportWidth / $this->_printWidth);
    foreach(array_keys($this->_reportPages) as $pageY) {
      for($pageX = 0; $pageX <= $endPageX; $pageX++) {
        if (!$firstPage) {
          $this->AddPage();
        }  
        $firstPage = false;
#        $this->SetClipping($this->lMargin, $this->tMargin + $this->_headerHeight, $this->_printWidth, $this->_printHeight);
        $deltaX = $this->lMargin - $pageX * $this->_printWidth;
        $deltaY = $pageY * $this->_printHeight - $this->tMargin - $this->_headerHeight;
        $this->SetCoordinate($deltaX, $deltaY);
        $this->_out($this->_reportPages[$pageY]);
        $this->RemoveCoordinate();
#        $this->RemoveClipping();
      }
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
?>