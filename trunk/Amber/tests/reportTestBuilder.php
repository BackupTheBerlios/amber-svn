<?php
  
/**
*
* @package Amber
* @subpackage Tests
*
* usage:  reportTestBuilder helps building the reports used for testing
*/

class reportTestBuilder
{

  var $report;
  var $zIndex;
  
  function reportTestBuilder($name)
  {
    $this->report = array(
      'RecordSource' => '',
      'Width' => 10093,
      'Name' => $name,
      'HasModule' => -1,
      'Printer' => array(
        'TopMargin'     => 720,
        'BottomMargin'  => 720,
        'LeftMargin'    => 720,
        'RightMargin'   => 720,
        'Orientation'   => 1,     //Portrait
        'PaperSize'     => 9      // DIN A4
      ),
      'Detail' => array(
        'EventProcPrefix' => 'Detail',
        'Name' => 'Detail',
        'ForceNewPage' => 0,
        'Height' => 2440
      )
    );
  }
  
  function createReportSections()
  {
  }
  
  function createPageSections()
  {
  }              
  
  function createGroup($groupSource, $withHeader, $withFooter)
  {
  }
  
  function createControl($type, $name, $sectionType, $id, $left=0, $top=0)
  {
    $sections = array('ReportHeader'=>1, 'PageHeader'=>1, 'Detail'=>1, 'ReportFooter'=>1, 'PageFooter'=>1, 'GroupHeaders'=>2, 'GroupFooters'=>2);
    if ($sections[$sectionType] == 1) {
      $sec =& $this->report[$sectionType];
    } elseif  ($sections[$sectionType] == 2) {
      $sec =& $this->report[$sectionType][$id];
    } else {
      die("Illegal section '$sectionType'");
    }  
    $ctl =& $sec['Controls'][$name];
    
    $this->zIndex += 10;
    if ($type = 'textfield') {
      $ctl = array(
        'EventProcPrefix' => $name,
        'Name' => $name,
        'ControlType' => 109,
        'ControlSource' => '',
        'Left' => $left,
        'Top' => $top,
        'Width' => 1440,
        'Height' => 240,
        'BackStyle' => 1,
        'BorderStyle' => 1,
        'BorderColor' => 8421504,
        'FontName' => 'Arial Narrow',
        'FontSize' => 8,
        'FontWeight' => 700,
        'TextAlign' => 2,
        'FontBold' => 0,
        'zIndex' => $this->zIndex
      );
    }
    return $ctl;  
  }  
}
    
    
    
?>