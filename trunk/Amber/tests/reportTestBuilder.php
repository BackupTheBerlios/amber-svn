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
  var $zIndex;  //private
  var $groupID = -1; //private  
  
  var $defaultControl = array (
        'Width' => 1440,
        'Height' => 240,
        'BackStyle' => 1,
        'BorderStyle' => 0,
        'BorderColor' => 8421504,
        'FontName' => 'Arial',
        'FontSize' => 8,
        'FontWeight' => 400,
        'TextAlign' => 2
      );
  
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
        'Height' => 1440
      )
    );
  }
  
  function createReportSections()
  {
    $this->report['ReportHeader'] = array(
      'EventProcPrefix' => 'ReportHeader',
      'Height' => 1440
    );
    
    $this->report['ReportFooter'] = array(
      'EventProcPrefix' => 'ReportFooter',
      'Height' => 1440
    );
  }
  
  function createPageSections()
  {
    $this->report['PageHeader'] = array(
      'EventProcPrefix' => 'PageHeader',
      'Height' => 1440
    );
    $this->report['PageFooter'] = array(
      'EventProcPrefix' => 'PageFooter',
      'Height' => 1440
    );
  }              
  
  function createGroup($groupSource, $withHeader, $withFooter)
  {
    $this->groupID++;
    $this->report['GroupLevels'][$this->groupID] = array(
      'ControlSource' => $groupSource,
      'SortOrder' => 0,
      'GroupHeader' => $withHeader,
      'GroupFooter' => $withFooter
    );
    if ($withHeader) {
      $this->report['GroupHeaders'][$this->groupID] = array(
        'Name' => 'Gruppenkopf'.$this->groupID,
        'EventProcPrefix' => 'Gruppenkopf'.$this->groupID,
        'ForceNewPage' => 0,
        'Height' => 1440
      );
    }
    if ($withFooter) {
      $this->report['GroupFooters'][$this->groupID] = array(
        'Name' => 'Gruppenfu�'.$this->groupID,
        'EventProcPrefix' => 'Gruppenfu�'.$this->groupID,
        'ForceNewPage' => 0,
        'Height' => 1440
      );
    }
    return $this->groupID;
  }
  
  function &getSection($sectionType, $id=0)
  {
    $sections = array('ReportHeader'=>1, 'PageHeader'=>1, 'Detail'=>1, 'ReportFooter'=>1, 'PageFooter'=>1, 'GroupHeaders'=>2, 'GroupFooters'=>2);
    if ($sections[$sectionType] == 1) {
      return $this->report[$sectionType];
    } elseif  ($sections[$sectionType] == 2) {
      return $this->report[$sectionType][$id];
    } else {
      die("Illegal section '$sectionType'");
    }  
  }
  
  function &createControl($type, $name, &$section, $left=0, $top=0)
  {
    $ctl =& $section['Controls'][$name];
    
    $this->zIndex += 10;
    if ($type = 'textfield') {
      $ctl = $this->defaultControl;
      $ctl['EventProcPrefix'] = $name;
      $ctl['Name'] = $name;
      $ctl['ControlType'] = 109;
      $ctl['ControlSource'] = '';
      $ctl['Left'] = $left;
      $ctl['Top'] = $top;
      $ctl['zIndex'] = $this->zIndex;
    }
    return $ctl;  
  }  
}
    
    
    
?>