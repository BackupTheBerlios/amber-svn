<?php

/**
 *
 * @package PHPReport
 * @subpackage Controls
 *
 */

ControlFactory::register('100', 'Label');
ControlFactory::register('101', 'Rectangle');
ControlFactory::register('109', 'TextBox');
ControlFactory::register('112', 'SubReport');

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
      'Value' => ''
    );
  var $_exporter;

  /**
   *
   * @access public
   *
   */
  function Control()
  {
    static $id = 0;

    $this->id = $id++;

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
    if (!$this->Visible) {
      $ret = 0;
    } elseif ($this->BorderStyle == 0) { //Borderstyle none
      $ret = $this->Top + $this->Height;
    } elseif ($this->BorderWidth == 0) { //BorderWidth 'as small as possible -- we leave 1/2 pt
      $ret = $this->Top + $this->Height +  1 * 20;
    } else {
      $ret = $this->Top + $this->Height +  2* $this->BorderWidth * 20;
    }
    return $ret;
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
  function FontBox()
  {
    parent::Control();

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
  var $_fmt;   //cache for format-function

  /**
   *
   * @access public
   *
   */
  function TextBox()
  {
    parent::FontBox();

    $newProperties =
      array(
        'ControlSource' => '',
        'Format' => '',
        'DecimalPlaces' => 0,
        'CanGrow' => false,
        'CanShrink' => false,
        'RunningSum' => 0
      );
    $this->_registerProperties($newProperties);
  }

  function printNormal(&$buffer)
  {
    if ($this->Format) {
      if (!$this->_fmt) {
        $this->_fmt = new _format($this->Format, $this->DecimalPlaces);
      }
      $this->_exporter->printNormal($this, $buffer, $this->_fmt->format($this->Value, strval($this->Format), $this->DecimalPlaces));
    } else {
      $this->_exporter->printNormal($this, $buffer, _format::stdFormat($this->Value));
    }
    return $this->stdHeight(); ### FIX THIS: CanGrow.....
  }

  function printDesign(&$buffer)
  {
    $ctrl = $this->prepareDesign();
    $this->_exporter->printDesign($ctrl, $buffer, $ctrl->ControlSource);
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
  function Label()
  {
    parent::FontBox();

    $extProperties = array('Caption' => '');
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
  function SubReport()
  {
    parent::Control();

    $newProperties =
      array(
        'CanGrow' => false,
        'CanShrink' => false,
      );
    $this->_registerProperties($newProperties);
  }
  function printNormal()
  {
    $this->_exporter->printNormal($this, $buffer, $this->Name);
    return $this->stdHeight(); ##FIX ME: actual height
  }

  function printDesign(&$buffer)
  {
    $ctrl = $this->prepareDesign();
    $this->_exporter->printDesign($ctrl, $buffer, $ctrl->Name);
  }
}

?>
