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
  var $pdf;

  var $subReportIndex = 0;
  var $subReportbuff;
  var $sectionIndex = 0;
  var $sectionBuff;
  
  var $reportBuff;
  var $sectionType;    // 'Head', 'Foot' or ''
  var $layout;
  
  function mayflower(&$layout, &$pdf)
  {
    $this->actpageNo = -1;
    $this->layout =& $layout;
    $this->pdf =& $pdf;
  }  

  function &getInstance(&$layout, &$pdf, $reset)
  {
    static $instance;
    if (is_null($instance) || $reset) {
      $instance = new mayflower($layout, $pdf);
    }  
    return $instance;
  }

  function _setOutBuff()
  { 
    if ($this->sectionIndex > $this->subReportIndex) {
      $this->pdf->setOutBuffer($this->sectionBuff[$this->sectionIndex], 'section');
    } elseif ($this->subReportIndex > 0) {
      $this->pdf->setOutBuffer($this->subReportbuff[$this->subReportIndex], 'subReport');
    } elseif ($this->inReport()) {
      $this->pdf->setOutBuffer($this->reportPages[$this->actpageNo][$this->sectionType], "report page".$this->sectionType.$this->actpageNo);
    } else {
      $this->pdf->unsetBuffer();
    }  
  }
  
  function page()
  {
    return $this->actpageNo + 1;
  }
  
  function newPage()
  {
    $this->posY = ($this->actpageNo + 1) * $this->layout->printHeight;
  }

  function posYinPage()
  {
    return ($this->posY - ($this->actpageNo * $this->layout->printHeight));
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
   
  function inSubReport()
  {
    return  ($this->subReportIndex > 0);
  }   

  function subReportPush()
  {
   $this->subReportIndex++;
   $this->subReportBuff[$this->subReportIndex] = '';
   $this->_setOutBuff();
  }
   
  function subReportPop()
  {
    $this->subReportIndex--;
    $this->_setOutBuff();
    return $this->subReportbuff[$this->subReportIndex + 1];
  }
  
  function sectionPush()
  {
    $this->sectionIndex++;
    $this->sectionBuff[$this->sectionIndex] = '';
    $this->_setOutBuff();
  }
  
  function sectionPop()
  {
    $this->sectionIndex--;
    $this->_setOutBuff();
    return $this->sectionBuff[$this->sectionIndex + 1];
  }
  
  function getSectionIndexForCommentOnly()
  {
    return '';
  } 
  
  
  function startReportBuffering()
  {
    if ($this->inReport()) {
      Amber::showError('Error', 'startReport: a report is already started!');
      die();
    }  
    $this->posY = 0;
  }
  
  function endReportBuffering()
  {
    if (!$this->inReport()) {
      Amber::showError('Error', 'endReport: no report open');
      die();
    }  
    $this->posY = 0;
    $this->actpageNo = -1;
    $this->_setOutBuff();
  }

  function inReport()
  {
    return ($this->actpageNo >= 0);
  }      
}
