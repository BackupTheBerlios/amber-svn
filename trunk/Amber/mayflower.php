<?php


/**
 *
 * @package PHPReport
 * @subpackage mayflower
 *  interim class for refactoring
 */



class pageLayout
{
  var $unit;          //unit in pt
  var $designMode;    // bool: I am designmode  (no page-header/-footer)
  
  var $paperwidth;
  var $paperheight;
  
  var $rightMargin;
  var $leftMargin;
  var $topMargin;
  var $bottomMargin;
  
  var $pageHeaderHeight;
  var $pageFooterHeight;
  
  var $reportWidth;
  
  //////////////////////
  // 'calculated' fields
  /////////////////////
  var $printWidth;
  var $printHeight;

  function pageLayout(&$report, $asSubReport, $designMode)
  { 
    $this->designMode = $designMode;
    $this->asSubReport = $asSubReport;   
    $this->unit = 1/20;
    $this->reportWidth = $report->Width;
    if ($report->Orientation == 'portrait') {
      $this->paperWidth = $report->PaperWidth;
      $this->paperHeight = $report->PaperHeight;
    } else {
      $this->paperWidth = $report->PaperHeight;
      $this->paperHeight = $report->PaperWidth;
    }  
    #Amber::dump($size);
    if ($asSubReport) {
      $this->rightMargin = 0;
      $this->leftMargin = 0;
      $this->topMargin = 0;
      $this->bottomMargin = 0;
      $this->pageHeaderHeight = 0;
      $this->pageFooterHeight = 0;
    } else {  
      $this->rightMargin = $report->RightMargin;
      $this->leftMargin = $report->LeftMargin;
      $this->topMargin = $report->TopMargin;
      $this->bottomMargin = $report->BottomMargin;
      if ($designMode) {
        $this->pageHeaderHeight = 0;
        $this->pageFooterHeight = 0;
      } else {
        $this->pageHeaderHeight = $report->PageHeader->Height;
        $this->pageFooterHeight = $report->PageFooter->Height;
      }
    }
    $this->printWidth  = ($this->paperWidth - $this->leftMargin - $this->rightMargin); //width of printable area of page (w/o morgins)
    $this->printHeight = ($this->paperHeight - $this->topMargin - $this->bottomMargin - $this->pageHeaderHeight - $this->pageFooterHeight); //height of printable area of page (w/o morgins)
  }
}

class mayflower
{
  var $exporter;

  var $subReportIndex = 0;
  var $subReportbuff;
  var $bufferIndex = 0;
  var $buffer;
  
  var $reportBuff;
  var $sectionType;    // 'Head', 'Foot' or ''
  var $layout;
  
  function mayflower(&$exporter)
  {
    $this->actpageNo = -1;
    $this->exporter =& $exporter;
  }  

  function &getInstance(&$exporter, $reset)
  {
    static $instance;
    if (is_null($instance) || $reset) {
      $instance = new mayflower($exporter);
    }  
    return $instance;
  }

  function _setOutBuff()
  { 
    if ($this->bufferIndex) {
      $this->exporter->setOutBuffer($this->buffer[$this->bufferIndex], 'section');
    } elseif ($this->inReport()) {
      $this->exporter->setOutBuffer($this->reportPages[$this->actpageNo][$this->sectionType], "report page".$this->sectionType.$this->actpageNo);
    } else {
      $this->exporter->unsetBuffer();
    }  
  }
  
  function page()
  {
    return $this->actpageNo + 1;
  }
  
  function setPageIndex($index)
  {
    $this->actpageNo = $index;
    $this->_setOutBuff();
  }        
  
  function getPageIndex()
  {
    return $this->actpageNo;
  }  
  
  function reportStartPageHeader()
  {
    $this->sectionType = 'Head';
    $this->_setOutBuff();
  }
    
  function reportStartPageBody()
  {
    $this->sectionType = '';
    $this->_setOutBuff();
  }
    
  function reportStartPageFooter()
  {
    $this->sectionType = 'Foot';
    $this->_setOutBuff();
  }  
   
  function bufferPush()
  {
    $this->bufferIndex++;
    $this->buffer[$this->bufferIndex] = '';
    $this->_setOutBuff();
  }
  
  function bufferPop()
  {
    $this->bufferIndex--;
    $this->_setOutBuff();
    return $this->buffer[$this->bufferIndex + 1];
  }


  function startReportBuffering()
  {
    if ($this->inReport()) {
      Amber::showError('Error', 'startReport: a report is already started!');
      die();
    }  
  }
  
  function endReportBuffering()
  {
    if (!$this->inReport()) {
      Amber::showError('Error', 'endReport: no report open');
      die();
    }  
    $this->actpageNo = -1;
    $this->_setOutBuff();
  }

  function inReport()
  {
    return ($this->actpageNo >= 0);
  }      
}
