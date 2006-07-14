<?php

/**
 *
 * @package Amber
 * @subpackage ReportEngine
 *
 */
class Section
{
  //////////////////////////////////////////////////////////////////
  // PUBLIC PROPERTIES
  //////////////////////////////////////////////////////////////////

  var $Name;
  var $Height;
  var $BackColor = 16777215;
  var $EventProcPrefix;
  var $Visible = true;
  var $ForceNewPage = 0;
  var $Controls;


  //////////////////////////////////////////////////////////////////
  // PRIVATE PROPERTIES
  //////////////////////////////////////////////////////////////////

  var $_onFormatFunc; //Function to call before opening section. only Set iff present
  var $_onPrintFunc;  //Function to call after opening the section, but before any controls are printed. only Set iff present
  var $_parent;   //The Report
  var $_PagePart; //'Head', 'Foot', ''
  var $_Aggregates; // Collected Aggregates
  
  //////////////////////////////////////////////////////////////////
  // PUBLIC METHODS
  //////////////////////////////////////////////////////////////////

  /**
   *
   * @access public
   * @param string section type ('PageHeader' or 'PageFooter')
   *
   */
  function Section($type)
  {
    $types = array('PageHeader' => 'Head', 'PageFooter' => 'Foot');
    if (array_key_exists($type, $types)) {
      $this->_PagePart = $types[$type];
    }
    $this->_Aggregates = array();
  }

  /**
   *
   * @access public
   * @param Report
   * @param array XML data
   *
   */
  function load(&$parent, $data)
  {
    $this->_parent =& $parent;
    $this->Name = $data['Name'];
    $this->Height = empty($data['Height']) ? 0 : $data['Height'];
    $this->ForceNewPage = empty($data['ForceNewPage']) ? 0 : $data['ForceNewPage'];

    if (isset($data['Visible'])) {
      $this->Visible = $data['Visible'];
    }
    if (isset($data['BackColor'])) {
      $this->BackColor = MSColor($data['BackColor']);
    }
    if (isset($data['CanGrow'])) {
      $this->CanGrow = $data['CanGrow'];
    }
    if (isset($data['CanShrink'])) {
      $this->CanShrink = $data['CanShrink'];
    }
    if (isset($data['KeepTogether'])) {
      $this->KeepTogether = $data['KeepTogether'];
    }
    if (isset($data['EventProcPrefix'])) {
      $this->EventProcPrefix = $data['EventProcPrefix'];
    } else {
      $this->EventProcPrefix = $data['Name'];
    }
    
    $s = $data['EventProcPrefix'] . '_Format';
    if (method_exists($this->_parent->_Code, $s)) {
      $this->_OnFormatFunc = $s;
    } else {
      $this->_OnFormatFunc = 'allSections_Format'; // null-OnFormat
    }

    $s = $data['EventProcPrefix'] . '_Print';
    if (method_exists($this->_parent->_Code, $s)) {
      $this->_OnPrintFunc = $s;
    } else {
      $this->_OnPrintFunc = 'allSections_Print'; // null-OnPrint
    }

    if (!empty($data['Controls'])) {
      foreach ($data['Controls'] as $c) {
        $ctl =& ControlFactory::create($c['ControlType'], $c, $parent->hReport);
        if ($ctl == false) {
          Amber::showError('Warning', 'Skipping unsupported control type: ' . htmlentities($c['ControlType']));
        } else {
          $this->Controls[] =& $ctl;
          $parent->Controls[$ctl->Name] =& $ctl;
          $parent->ControlValues[$ctl->Name] =& $ctl->Value;
          $ctl->_SectionSlip =& $parent->SectionSlip;
        }
      }
    }
  }

  function isVisible()
  {
    $this->_OnFormat($cancel);
    return (($cancel == false) && ($this->Visible == true));
  }
  
  function hasForceNewPageBefore()
  {
    if ($this->_PagePart) { // page-header or -footer
      return false;
    } else {  
      return (($this->ForceNewPage == 1) || ($this->ForceNewPage == 3));
    }  
  }

  function hasForceNewPageAfter()
  {
    if ($this->_PagePart) { // page-header or -footer
      return false;
    } else {  
      return (($this->ForceNewPage == 2) || ($this->ForceNewPage == 3));
    }  
  }
  
  /**
  *
  * @access public
  *
  */
  function printNormal()
  {
    if (isset($this->Controls)) {
      $keys = array_keys($this->Controls);
      $diffHeight = 0;
      foreach ($keys as $key) {
        if ($this->Controls[$key]->isVisible()) {
          $designHeight = $this->Controls[$key]->stdHeight();
          $printedHeight = $this->Controls[$key]->printNormal();
          if ($printedHeight <> $designHeight) {
            $diffHeight += $printedHeight - $designHeight;
          }
        }
      }
    }
    if (isset($this->CanGrow) && ($this->CanGrow) && ($diffHeight > 0)) {
      return $this->Height + $diffHeight;
    } elseif (isset($this->CanShrink) && ($this->CanShrink) && ($diffHeight < 0)) {
      return $this->Height + $diffHeight;
    } else {
      return $this->Height;
    }
  }

  /**
   *
   * @access public
   * @param obj GroupLevel
   *
   */
  function printDesign()
  {
    // print controls
    if (isset($this->Controls)) {
      reset($this->Controls);
      while (current($this->Controls)) {
        $key = key($this->Controls);
        $this->Controls[$key]->printDesign();
        next($this->Controls);
      }
    }
  }

  //////////////////////////////////////////////////////////////////
  // PRIVATE METHODS
  //////////////////////////////////////////////////////////////////

  /**
   * @access private
   * @param int
   */
  function _OnFormat(&$cancel)
  {
    $funct = $this->_OnFormatFunc;
    $obj =& $this->_parent->_Code;
    $cancel = false;
    $obj->$funct($cancel, 1);
  }

  /**
   * @access private
   * @param int
   */
  function _OnPrint(&$cancel, $formatCount) {
    $funct =$this->_OnPrintFunc;
    $obj =& $this->_parent->_Code;
    $cancel = false;
    $obj->$funct($cancel, $formatCount);
  }

  /**
   * @access private
   */
  function _runningSum()
  {
    if (is_array($this->Controls)) {
      $keys = array_keys($this->Controls);
      foreach($keys as $key) {
        $ctrl =& $this->Controls[$key];
        if (isset($ctrl->RunningSum)) { //optimisation
          $ctrl->_RunningSum();
        }
      }
    }
  }

  /**
   * @access public
   */
  function _resetRunningSum()
  {
    if (is_array($this->Controls)) {
      $keys = array_keys($this->Controls);
      foreach($keys as $key) {
        if (isset($this->Controls[$key]->RunningSum)) {
          $this->Controls[$key]->_resetRunningSum();
        }
      }
    }
  }

  /**
   * @access public
   * @param string type of Aggregate-Object to create (sum, avg, ...)
   * @return object 
   */
  function &createAggregate($type)
  { 
    $agg =& AggregateFactory::create($type);
    $this->_Aggregates[] =& $agg;
    return $agg; 
  }

  /**
   * @access public
   */
  function _resetAggregate()
  {
    if (is_array($this->_Aggregates)) {
      $keys = array_keys($this->_Aggregates);
      foreach($keys as $key) {
        $this->_Aggregates[$key]->reset();
      }
    }
    
    if (is_array($this->Controls)) {
      $keys = array_keys($this->Controls);
      foreach($keys as $key) {
        if (isset($this->Controls[$key]->_aggregate)) {
          $this->Controls[$key]->resetAggregate();
        }
      }
    }
  }

  /**
   * @access protected
   * @return bool
   */
  function isNull()
  {
    return false;
  }
}

/**
 *
 * @package Amber
 * @subpackage ReportEngine
 *
 */
class GroupSection extends Section
{
  var $ForceNewPage;

  /**
   *
   * @access public
   * @param string
   *
   */
  function GroupSection($type)
  {
    if (method_exists($this, 'Section')) {
      parent::Section($type);
    }
  }
}

/**
 *
 * @package Amber
 * @subpackage ReportEngine
 *
 */

class SectionNull extends Section
{
  /**
   *
   * @access public
   * @param Report
   * @param array XML data
   *
   */
  function load(&$parent, $data)
  {
    $this->_parent = $parent;
    $this->Name = 'NULL';
    $this->Height = 0;
    $this->ForceNewPage = 0;
    $this->Visible = false;
    $this->BackColor = 0;
    $this->EventProcPrefix = 'allSections';
    $this->_OnFormatFunc = 'allSections_Format'; //null-OnFormat
    $this->Controls = array();
  }

  /**
   *
   * @access public
   *
   */
  function printNormal()
  {
  }

  function printDesign()
  {
  }

  /**
   *
   * @access protected
   * @return bool
   *
   */
  function isNull()
  {
    return true;
  }
}

/**
 *
 * @package Amber
 * @subpackage ReportEngine
 *
 */

class GroupLevel
{
  //////////////////////////////////////////////////////////////////
  // PUBLIC PROPERTIES
  //////////////////////////////////////////////////////////////////

  var $index;
  var $ControlSource;
  var $SortOrder; // 0 = Ascending, -1 = Descending
  var $GroupHeader;
  var $GroupFooter;
  var $GroupOn; // abhängig vom Datentyp des Feldes; Bedeutung der Werte ist in Hilfedatei aufgelistet
  var $GroupInterval; // abhängig vom Datentyp des Feldes; Bedeutung der Werte ist in Hilfedatei aufgelistet
  var $KeepTogether; // 0 = Nein, 1 = Ganze Gruppe, 2 = Mit 1. Detaildatensatz

  //////////////////////////////////////////////////////////////////
  // PUBLIC METHODS
  //////////////////////////////////////////////////////////////////

  /**
   *
   * @access public
   * @param array XML data
   *
   */
  function load(&$data)
  {
    foreach ($data as $key => $value) {
      $this->$key = $value;
    }
  }
}

?>
