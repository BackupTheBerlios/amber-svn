<?php


/**
 *
 * @package PHPReport
 * @subpackage ReportEngine
 *  this class handles paged report types 
 */

class ReportContinous extends ReportPaged
{
/*  var $_blankPage = true;
  var $_pageNo = 1;
  var $_posY;


  function _endReport()
  {
    $this->_exporter->endReport($this);
  }
  
  function _startSection(&$section, $width)
  {
    if (!(($section->_PagePart) or ($this->designMode))) {
      if ($this->_blankPage) {
        $this->_blankPage = false;

        $this->_exporter->printTopMargin($this->_posY);  
        
        $this->_posY += $this->layout->topMargin;
        $this->_printNormalSection($this->PageHeader); 
      }
    }
    $this->_exporter->bufferStart();
  }

  function _endSection(&$section, $height)
  {
    $buff = $this->_exporter->bufferEnd();
    $this->outSection(1, $this->_posY, $height, &$buff, &$section);
    $this->_posY += $height;
  }
  
  function page()
  {
    return $this->_pageNo;
  }

  
  function newPage()
  {
    if ((!$this->_blankPage) and (!$this->designMode)) {
      $this->_printNormalSection($this->PageFooter);
      
      $this->_exporter->printBottomMargin($this->_posY);
      
      $this->_posY += $this->layout->bottomMargin;
      $this->OnPage();
      $this->_pageNo++;
    }
    $this->_blankPage = true;
  }
*/
}
?>