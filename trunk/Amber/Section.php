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


  var $printed;               // set by printNormal: section got printed (or not...)

  //////////////////////////////////////////////////////////////////
  // PRIVATE PROPERTIES
  //////////////////////////////////////////////////////////////////

  var $_onFormatFunc; //Function to call before opening section. only Set iff present
  var $_onPrintFunc;  //Function to call after opening the section, but before any controls are printed. only Set iff present
  var $_parent;   //The Report
  var $_PagePart; //'Head', 'Foot', ''

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
    $this->_PagePart = $types[$type];
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
    if (isset($this->$data['EventProcPrefix'])) {
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
        }
      }
    }
  }

  function isVisible()
  {
    $this->_OnFormat($cancel);
    return (($cancel == true) || ($this->Visible == false));
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
  * @param integer  start position (relative to report) to place section in twips
  *
  */
  function printNormal()
  {
    $maxHeight = 0;
    if ((isset($this->Controls)) && (!$cancel)) {
      $keys = array_keys($this->Controls);
      foreach ($keys as $key) {
        $height = $this->Controls[$key]->printNormal();
        if ($height > $maxHeight) {
          $maxHeight = $height;
        }
      }
    }
    if (isset($this->CanGrow) && ($this->CanGrow) && ($this->Height < $maxHeight)) {
      return $maxHeight;
    } elseif (isset($this->CanShrink) && ($this->CanShrink) && ($this->Height > $maxHeight)) {
      return $maxHeight;
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
    $obj->$funct($this->_parent, $cancel, 1);
  }

  /**
   * @access private
   * @param int
   */
  function _OnPrint(&$cancel, $formatCount) {
    $funct =$this->_OnPrintFunc;
    $obj =& $this->_parent->_Code;
    $cancel = false;
    $obj->$funct($this->_parent, $cancel, $formatCount);
  }

  /**
   * @access private
   */
  function _RunningSum()
  {
    if (is_array($this->Controls)) {
      $keys = array_keys($this->Controls);
      foreach($keys as $key) {
        if (isset($this->Controls[$key]->RunningSum)) { //optimisation
          $this->Controls[$key]->_RunningSum();
        }
      }
    }
  }

  /**
   * @access private
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
   * @param int
   *
   */
  function printReport()
  {
  }

  function printDesign($GroupLevel = null)
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
  var $GroupOn; // abh�gig vom Datentyp des Feldes; Bedeutung der Werte ist in Hilfedatei aufgelistet
  var $GroupInterval; // abh�gig vom Datentyp des Feldes; Bedeutung der Werte ist in Hilfedatei aufgelistet
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
