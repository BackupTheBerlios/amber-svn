<?php

/**
 *
 * @package PHPReport
 * @subpackage ReportEngine
 *
 */

class AmberFormSection
{
  function load(&$parent, $data)
  {
    $this->_parent =& $parent;

    if (!empty($data['Controls'])) {
      foreach ($data['Controls'] as $c) {
        $ctl =& ControlFactory::create($c['ControlType'], $c);
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
   * @param int
   *
   */
  function printNormal()
  {
    $this->_startSection($buffer);
    // print controls
    if ((isset($this->Controls)) && (!$cancel)) {
      $keys = array_keys($this->Controls);
      foreach ($keys as $key) {
        $height = $this->Controls[$key]->printNormal($buffer);
        if ($height > $maxHeight) {
          $maxHeight = $height;
        }
      }
    }
    $this->_endSection($height, $buffer);
  }

  /**
   *
   * @access public
   * @param int
   *
   */
  function printDesign($GroupLevel = null)
  {
  }

  /**
   * @access protected
   * @return bool
   */
  function isNull()
  {
    return false;
  }


  // FIXME: included to get it running, must be removed later
  function _onPrint()
  {
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
}

/**
 *
 * @package PHPReport
 * @subpackage ReportEngine
 *
 */

class AmberFormSectionNull extends AmberFormSection
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
  function printNormal()
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

?>
