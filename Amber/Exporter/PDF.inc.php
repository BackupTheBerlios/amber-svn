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
  var $_buff;
  function _out($s)
  {
    if ($this->_inSection) {
      $this->_buff .= $s . "\n";
      #parent::_out($s);
    } else {
      parent::_out($s);
    }
  }
  
  function sectionFlush()
  {
    parent::_out($this->_buff);   
  }  

  function sectionStart()
  {
    $this->_buff = '';
    $this->_inSection = true;
  }
  
  function sectionEnd($xstart, $height, $width)
  {
    $this->_inSection = false;
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
?>