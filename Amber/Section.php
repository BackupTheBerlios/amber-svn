<?php

/**
 *
 * @package PHPReport
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

  /**
  *
  * @access public
  * @param integer  start position (relative to report) to place section in twips
  *
  */
  function printNormal()
  {
    $this->_RunningSum();
    $this->_OnFormat($cancel);
    if (($cancel == true) || ($this->Visible == false)) {
      $height = 0;
      $this->printed = false;
    } else {
      $this->_startSection($buffer);
      // print controls
      $maxHeight = 0;
      if ((isset($this->Controls)) && (!$cancel)) {
        $keys = array_keys($this->Controls);
        foreach ($keys as $key) {
          $height = $this->Controls[$key]->printNormal($buffer);
          if ($height > $maxHeight) {
            $maxHeight = $height;
          }
        }
      }
      if (isset($this->CanGrow) && ($this->CanGrow) && ($this->Height < $maxHeight)) {
        $height = $maxHeight;
      } elseif (isset($this->CanShrink) && ($this->CanShrink) && ($this->Height > $maxHeight)) {
        $height = $maxHeight;
      } else {
        $height = $this->Height;
      }
      $this->_endSection($height, $buffer);
      $this->printed = true;
    }
  }

  /**
   *
   * @access public
   * @param obj GroupLevel
   *
   */
  function printDesign($GroupLevel=Null)
  {
    if ($this->isNull()) {
      return 0;
    } else {
      if ($GroupLevel) {
        $this->_sectionPrintDesignHeader($this->EventProcPrefix . ' - ' . $GroupLevel->ControlSource);
      } else {
        $this->_sectionPrintDesignHeader($this->EventProcPrefix);
      }
      $this->_startSection($buffer);
      // print controls
      if (isset($this->Controls)) {
        reset($this->Controls);
        while (current($this->Controls)) {
          $key = key($this->Controls);
          $this->Controls[$key]->printDesign($buffer);
          next($this->Controls);
        }
      }
      $this->_endSection($this->Height, $buffer);
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
  *
  * for design mode: print border between sections
  *
  * @access private
  * @param  string name of header to print
  * @return integer height printed in twips
  */
  function _sectionPrintDesignHeader($name)
  {
    $this->_parent->_exporter->sectionPrintDesignHeader($name);
  }

   /**
   * @access private
   */
  function _startSection(&$buffer)
  {
    $exporter =& $this->_parent->_exporter;
    if ((!$this->_PagePart) && (!$exporter->DesignMode)) {
      if (($this->ForceNewPage == 1) || ($this->ForceNewPage == 3)) {
        $exporter->newPage();
      }
    }
    $exporter->startSection($this, $this->_parent->Width, $buffer);
  }

  /**
   * @access private
   */
  function _endSection($height, &$buffer)
  {
    $exporter =& $this->_parent->_exporter;
    $exporter->endSection($this, $height, $buffer);
    if ((!$this->_PagePart) && (!$exporter->DesignMode)) {
      if (($this->ForceNewPage == 2) || ($this->ForceNewPage == 3)) {
        $exporter->newPage();
      }
    }
  }

  /**
   * @access private
   */
  function _RunningSum()
  {
    if (is_array($this->Controls)) {
      $keys = array_keys($this->Controls);
      foreach($keys as $key) {
        if (isset($this->Controls[$key]->RunningSum)) {
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
 * @package PHPReport
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
 * @package PHPReport
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
 * @package PHPReport
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
