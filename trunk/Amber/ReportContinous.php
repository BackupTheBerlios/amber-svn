<?php


/**
 *
 * @package PHPReport
 * @subpackage ReportEngine
 *  this class handles paged report types 
 */

class ReportContinous extends Report
{
  var $_blankPage = true;
  var $_pageNo = 1;


  function _startReport($isSubreport)
  {
    parent::_startReport($isSubreport);
  }

  function _endReport()
  {
    $this->_exporter->endReport($this);
  }
  
  function _startSection(&$section, $width, &$buffer)
  {
    if (!(($section->_PagePart) or ($this->designMode))) {
      if ($this->_blankPage) {
        $this->_blankPage = false;

        $this->_exporter->printTopMargin($this->_exporter->_posY);  
        
        $this->_exporter->_posY += $this->_exporter->layout->topMargin;
        $this->_printNormalSection($this->PageHeader); 
      }
    }
    $buffer = null;
  }

  function _endSection(&$section, $height, &$buffer)
  {
    $this->_exporter->outSectionStart($this->_exporter->_posY, $height, $section->BackColor, $section->Name);
    
    if ($this->designMode) {
        $this->_exporter->out($buffer);
    } else {
      $section->_onPrint($cancel, 1);
      if (!$cancel) {
        $this->_exporter->out($buffer);
      }
    }
    
    $this->_exporter->outSectionEnd();
    $this->_exporter->_posY += $height;
  }
  
  function page()
  {
    return $this->_pageNo;
  }

  
  function newPage()
  {
    if ((!$this->_blankPage) and (!$this->designMode)) {
      $this->_printNormalSection($this->PageFooter);
      
      $this->_exporter->printBottomMargin($this->_exporter->_posY);
      
      $this->_exporter->_posY += $this->layout->bottomMargin;
      $this->OnPage();
      $this->_pageNo++;
    }
    $this->_blankPage = true;
  }

}
?>