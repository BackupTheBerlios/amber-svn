<?php

/**
*
* @package PHPReport
* @subpackage Controls
*
*/

ControlFactory::register('100', 'Label');
ControlFactory::register('101', 'Rectangle');
ControlFactory::register('102', 'Dummy'); // Line
//ControlFactory::register('104', 'CommandButton');
ControlFactory::register('106', 'CheckBox');
ControlFactory::register('109', 'TextBox');
ControlFactory::register('111', 'ComboBox');
ControlFactory::register('112', 'SubReport');
//ControlFactory::register('122', 'ToggleButton');

/**
*
* @package PHPReport
* @subpackage Controls
*
*/
class Control
{
  var $id; // unique numeric id

  var $Properties =
    array(
      'Name' => '',
      'ControlType' => 0,
      'Left' => 0,
      'Top' => 0,
      'Width' => 0,
      'Height' => 0,
      'Visible' => true,
      'BackStyle' => 0,
      'BackColor' => 16777215, //white
      'BorderStyle' => 0,
      'BorderColor' => 0, // black
      'BorderWidth' => 0, // as small as possible ("Haarlinie")
      'BorderLineStyle' => 0,
      'zIndex' => 0,
      'Value' => '',
      '_OldValue' => ''
    );
  var $_exporter;
  var $_hReport;

  /**
  *
  * @access public
  *
  */
  function Control($hReport)
  {
    static $id = 0;

    $this->id = $id++;
    $this->_hReport = $hReport;

    // "Connect" class attributes with properties array
    foreach (array_keys($this->Properties) as $key) {
      $this->$key =& $this->Properties[$key];
    }
  }

  /**
  *
  * @access public
  * @param string name of property
  * @param mixed value
  *
  */
  function setProperty($name, $value)
  {
    $specialProperties = array('BackColor', 'BorderColor');

    if (array_key_exists($name, $this->Properties)) {
      $this->Properties[$name] = $value;
      if (in_array($name, $specialProperties)) {
        $this->Properties[$name] = MSColor($this->Properties[$name]);
      }
    }
  }

  /**
  *
  * @access public
  * @param array
  *
  */
  function setProperties(&$prop)
  {
    foreach ($prop as $key => $value) {
      $this->setProperty($key, $value);
    }
  }

  /**
  *
  * @access public
  * @abstract
  * @param mixed buffer passed thru for exporter's usage
  *
  */
  function printNormal(&$buffer) { }

  /**
  *
  * @access public
  * @abstract
  * @param int
  *
  */
  function printDesign() { }


  /**
  * Used by printDesign: return modified copy of $this for printing in design mode
  *
  *
  * @access public
  * @abstract
  * @param int
  *
  */
  function prepareDesign()
  {
    $ctrl = $this;
    $ctrl->visible = true;
    if ($ctrl->BorderStyle == 0) {
      $ctrl->BorderStyle = 1;
      $ctrl->BorderColor = 0xcccccc;
    }
    return $ctrl;
  }

  /**
  * Used by printNormal: return std-height of control
  * @access public
  *
  **/
  function stdHeight()
  {
    if (!$this->isVisible()) {
      $ret = 0;
    } elseif ($this->BorderStyle == 0) { //Borderstyle none
      $ret = $this->Top + $this->Height;
    } elseif ($this->BorderWidth == 0) { //BorderWidth 'as small as possible -- we leave 1/2 pt
      $ret = $this->Top + $this->Height +  10;
    } else {
      $ret = $this->Top + $this->Height +  $this->BorderWidth * 20;
    }
    return $ret;
  }

  function isVisible()
  {
    return $this->Visible;
  }

  /**
  *
  * May be called by subclasses if they implement additional properties
  *
  * <b>Note:</b> The subclass should call this method <b>after</b> calling its
  * parent's constructor!
  *
  * @access protected
  * @param array
  *
  */
  function _registerProperties(&$newProperties)
  {
    $this->Properties =& array_merge($this->Properties, $newProperties);

    // "Connect" class attributes with new properties
    foreach (array_keys($newProperties) as $key) {
      $this->$key =& $this->Properties[$key];
    }
    /*
      // Alternative implementation (little bit slower)
      foreach ($newProperties as $key => $value) {
      $this->Properties[$key] = $value;
      $this->$key =& $this->Properties[$key];
    }*/
  }
}

/**
*
* @package PHPReport
* @subpackage Controls
*
*/
class Rectangle extends Control
{

  function Rectangle($hReport)
  {
    parent::Control($hReport);
  }

  function printNormal(&$buffer)
  {
    $this->_exporter->printNormal($this, $buffer, '');
    return $this->stdHeight();
  }

  function printDesign(&$buffer)
  {
    $ctrl = $this->prepareDesign();
    $this->_exporter->printDesign($ctrl, $buffer, '');
  }
}

/**
*
* @package PHPReport
* @subpackage Controls
*
*/
/*class Line extends Control
{
  function echoReport()
  {
    if ($this->BorderWidth == 0) {
      $this->BorderWidth = 1;
    }
    if ($this->Height == 0) {  // horizontal
      $moveUp = ceil($this->BorderWidth / 2);
      $this->Top -= $moveUp * 20;
      $this->BeginStartTag();
      $out = 'border-bottom: 0px; line-height: 0; padding: 0px; margin: 0px; ';
      $this->EndTag();
    } else if ($this->Width == 0) { // vertical
      $this->Width = 1; // else it wouldn't be visible at all
      $this->BeginStartTag();
      $out = 'border-right: 0px; line-height: 0; padding: 0px; margin: 0px; ';
      $this->EndTag();
    }

    echo $out;
  }
}*/

/**
*
* @package PHPReport
* @subpackage Controls
*
*/
class FontBox extends Control
{
  /**
  *
  * @access public
  *
  */
  function FontBox($hReport)
  {
    parent::Control($hReport);

    $newProperties = array(
      'ForeColor' => 0,
      'FontName' => 'Arial',
      'FontSize' => 10,
      'FontWeight' => 500,
      'TextAlign' => 0,
      'FontItalic' => false,
      'FontUnderline' => false
    );
    $this->_registerProperties($newProperties);
  }

  /**
  *
  * @access public
  * @param string name of property
  * @param mixed value
  *
  */
  function setProperty($name, $value)
  {
    parent::setProperty($name, $value);

    $specialProperties = array('ForeColor');
    if (in_array($name, $specialProperties)) {
      $this->Properties[$name] = MSColor($this->Properties[$name]);
    }
  }
}

/**
*
* @package PHPReport
* @subpackage Controls
*
*/
class TextBox extends FontBox
{
  var $_sum;

  /**
  *
  * @access public
  *
  */
  function TextBox($hReport)
  {
    parent::FontBox($hReport);

    $newProperties =
      array(
        'ControlSource' => '',
        'Format' => '',
        'DecimalPlaces' => 0,
        'CanGrow' => false,
        'CanShrink' => false,
        'HideDuplicates' => false,
        'RunningSum' => 0
      );
    $this->_registerProperties($newProperties);
  }

  function printNormal(&$buffer)
  {
    if ($this->Format) {
      $this->_exporter->printNormal($this, $buffer, Format($this->Value, strval($this->Format), $this->DecimalPlaces));
    } else {
      $this->_exporter->printNormal($this, $buffer, $this->Value);
    }
    return $this->stdHeight(); ### FIX THIS: CanGrow.....
  }

  function printDesign(&$buffer)
  {
    $ctrl = $this->prepareDesign();
    $this->_exporter->printDesign($ctrl, $buffer, $ctrl->ControlSource);
  }

  function isVisible()
  {
    if (!$this->Visible) {
      return false;
    } elseif ($this->HideDuplicates and ($this->Value === $this->_OldValue)) {
      return false;
    } else {
      return true;
    }
  }

  function _runningSum()
  {
    if ($this->RunningSum) {
      $this->_sum += $this->Value;
      $this->Value = $this->_sum;
    }
  }

  function _resetRunningSum()
  {
    if ($this->RunningSum == 1) {
      $this->_sum = 0;
    }
  }
}

/**
*
* @package PHPReport
* @subpackage Controls
*
*/
class Label extends FontBox
{
  /**
  *
  * @access public
  *
  */
  function Label($hReport)
  {
    parent::FontBox($hReport);

    $extProperties = array(
      'Caption' => ''
    );

    $this->_registerProperties($extProperties);
  }

  function printNormal(&$buffer)
  {
    $this->_exporter->printNormal($this, $buffer, $this->Caption);
    return $this->stdHeight();
  }

  function printDesign(&$buffer)
  {
    $ctrl = $this->prepareDesign();
    $this->_exporter->printDesign($ctrl, $buffer, $ctrl->Caption);
  }

  function isVisible()
  {
    if (!$this->Visible) {
      return false;
    } elseif ($this->Parent) {
      $parentCtrl =& $this->Parent;
      amber::dump($this);
      return $parentCtrl->isVisible();   // label gets invisible if its parent control get invisible
    } else {
      return true;
    }
  }

}

/**
*
* @package PHPReport
* @subpackage Controls
*
**/
class SubReport extends Control
{
  /**
  *
  * @access public
  *
  */
  function SubReport($hReport)
  {
    parent::Control($hReport);

    $newProperties =
      array(
        'CanGrow'   => false,
        'CanShrink' => false,
        'SourceObject'     => '',
        'LinkChildFields'  => '',
        'LinkMasterFields' => ''
      );
    $this->_registerProperties($newProperties);
  }

  var $report;
  function printNormal(&$buffer)
  {
    if (!$this->SourceObject) {
      $this->_exporter->printNormal($this, $buffer, '');
      return $this->stdHeight(); ##FIX ME: actual height
    }

    if (!$this->report) {
      $amber =& Amber::getInstance();
      ob_start();
      $amber->OpenReport($this->SourceObject, AC_NORMAL, '', 'typo3');
      $buffer .= ob_get_contents();
      ob_end_clean();
    }

    # $this->report->setFilter(....)
    # $this->_exporter->printSubReport().....

    //$this->_exporter->printNormal($this, $buffer, $this->Name);
    return $this->stdHeight(); ##FIX ME: actual height
  }

  function printDesign(&$buffer)
  {
    $ctrl = $this->prepareDesign();
    $this->_exporter->printDesign($ctrl, $buffer, $ctrl->Name);
  }
}

/**
*
* @package PHPReport
* @subpackage Controls
*
**/
class ComboBox extends Control
{
  /**
  *
  * @access public
  *
  */
  function ComboBox($hReport)
  {
    parent::Control($hReport);

    $newProperties =
      array(
        'RowSourceType'   => '',
        'RowSource' => '',
        'BoundColumn'     => 1
      );
    $this->_registerProperties($newProperties);
  }

  function printNormal(&$buffer)
  {
    $this->_doQuery();
    $this->_exporter->printNormal($this, $buffer, $this->_data);

    return $this->stdHeight(); ##FIX ME: actual height
  }

  function printDesign(&$buffer)
  {
    $this->_doQuery();
    $this->_exporter->printDesign($this, $buffer, $this->_data);

    return $this->stdHeight(); ##FIX ME: actual height
  }

  function requery()
  {
    $this->_doQuery();
  }

  function _doQuery()
  {
    $db =& Amber::currentDb();

    if ($this->RowSourceType == 'Table/Query') {
      $db->SetFetchMode(ADODB_FETCH_BOTH);
      $data =& $db->GetAll($this->RowSource);
    }

    if (isset($this->BoundColumn)) {
      // Indexes in Access start with 1
      $bound = $this->BoundColumn - 1;
    } else {
      $bound = 0;
    }

    // FIXME: Determine first visible row -> option value

    $this->_data = array();
    if (is_array($data)) {
      foreach ($data as $idx => $row) {
        $this->_data[$row[$bound]] = $row[1];
      }
    }
  }
}

/**
*
* @package PHPReport
* @subpackage Controls
*
**/
class CheckBox extends Control
{
  /**
  *
  * @access public
  *
  */
  function Checkbox($hReport)
  {
    parent::Control($hReport);

    $newProperties =
      array(
        'ControlSource'   => '',
        'HideDuplicates' => false
      );

    $this->_registerProperties($newProperties);
  }

  function printNormal(&$buffer)
  {
    $this->_exporter->printNormal($this, $buffer, $this->Value);
  }

  function printDesign(&$buffer)
  {
    $this->_exporter->printDesign($this, $buffer, true);
  }
}

/**
*
* @package PHPReport
* @subpackage Controls
*
**/
class Dummy extends Control
{
  /**
  *
  * @access public
  *
  */
  function Dummy($hReport)
  {
    parent::Control($hReport);
  }

  function printNormal(&$buffer)
  {
    return 0;
  }

  function printDesign(&$buffer)
  {
    return 0;
  }
}

?>
