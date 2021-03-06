<?php

/**
*
* @package Amber
* @subpackage Controls
*
*/

ControlFactory::register('100', 'Label');
ControlFactory::register('101', 'Rectangle');
ControlFactory::register('102', 'Dummy'); // Line
//ControlFactory::register('104', 'CommandButton');
ControlFactory::register('106', 'CheckBox');
ControlFactory::register('109', 'TextBox');
ControlFactory::register('111', 'Dummy'); // ComboBox
ControlFactory::register('112', 'SubReport');
//ControlFactory::register('122', 'ToggleButton');

/**
*
* @package Amber
* @subpackage Controls
*
*/
class Control
{
  var $id; // unique numeric id

  var $Properties =
    array(
      'Name' => '',
      'EventProcPrefix' => '',
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
  *
  */
  function printNormal() { }

  /**
  *
  * @access public
  * @abstract
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
  function &prepareDesign()
  {
    $ctrl = $this;
    $ctrl->Visible = true;
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
  function stdHeight($height = null)
  {
    if (!$this->isVisible()) {
      return 0;
    }
    
    if (is_null($height)) {
      $height = $this->Height;
    }
    
    if ($this->BorderStyle == 0) { //Borderstyle none
      $ret = $height;
    } elseif ($this->BorderWidth == 0) { //BorderWidth 'as small as possible -- we leave 1/2 pt
      $ret = $height +  20;
    } else {
      $ret = $height +  2 * $this->BorderWidth * 20;
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
  }
}

/**
*
* @package Amber
* @subpackage Controls
*
*/
class Rectangle extends Control
{

  function Rectangle($hReport)
  {
    parent::Control($hReport);
  }

  function printNormal()
  {
    $this->_exporter->printNormal($this, '');
    return $this->stdHeight();
  }

  function printDesign()
  {
    $ctrl =& $this->prepareDesign();
    $this->_exporter->printDesign($ctrl, '');
  }
}

/**
*
* @package Amber
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
* @package Amber
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

  function TextAlign()
  {
    return $this->TextAlign;
  }  
}

/**
*
* @package Amber
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

  function printNormal()
  {
    // FIXME: This is specific to MySQL!!
    if ($this->Value == '0000-00-00 00:00:00') {
      $this->Value = null;
    }

    $this->_exporter->printNormal($this, Format($this->Value, strval($this->Format), $this->DecimalPlaces));
    return $this->stdHeight(); ### FIX THIS: CanGrow.....
  }

  function printDesign()
  {
    $ctrl =& $this->prepareDesign();
    $this->_exporter->printDesign($ctrl, $ctrl->ControlSource."\n{".$ctrl->Name."}");
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
  
  function TextAlign()
  {
    if ($this->TextAlign) {
      return $this->TextAlign;
    } elseif (!is_string($this->Value)) {
      return 3; // right justify numbers
    } elseif (preg_match(
        "|^([0-9]{4})-([0-9]{2})-([0-9]{2})[ -]?(([0-9]{1,2}):([0-9]{1,2}):([0-9\.]{1,4}))?|",
    	$this->Value)) {
      return 3; // right justify dates
    } else {
      return 1; // left justify strings
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
  
  function createAggregate($type)
  {
    $this->_aggregate =& AggregateFactory::create($type);
    $this->_aggregate->Value =& $this->Value;
  }
  
  function resetAggregate()
  {
    if ($this->_aggregate) {
      $this->_aggregate->reset();
    }
  }
  
  function addValue($value, $type='')
  {
    if (!$this->_aggregate) {       
      $this->createAggregate($type);
    }
    $this->_aggregate->addValue($value);
  }     

  function sum($value)
  {
    $this->addvalue($value, 'sum');
  }
  
  //FIXME: add shortcuts for other aggregates  
}

/**
*
* @package Amber
* @subpackage Controls
*
*/
class Label extends FontBox
{
  var $Parent;

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

  function printNormal()
  {
    $this->_exporter->printNormal($this, $this->Caption);
    return $this->stdHeight();
  }

  function printDesign()
  {
    $ctrl =& $this->prepareDesign();
    $this->_exporter->printDesign($ctrl, $ctrl->Caption);
  }

  function isVisible()
  {
    if (!$this->Visible) {
      return false;
    } elseif ($this->Parent) {
      $parentCtrl =& $this->Parent;
      return $parentCtrl->isVisible();   // label gets invisible if its parent control get invisible
    } else {
      return true;
    }
  }

}

/**
*
* @package Amber
* @subpackage Controls
*
**/
class SubReport extends Control
{
  var $_subReport;

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

  function printNormal()
  {
    if (!$this->SourceObject) {
      $this->_exporter->printNormal($this, '');
      return $this->stdHeight();
    }

    // Convert SourceObject to name and type
    $source = explode('.', $this->SourceObject);
    $type = $source[0];
    $name = $source[1];

    // Try to load report
    $amber =& Amber::getInstance();
    $rep =& ObjectHandler::getObject($this->_hReport);
    $mgr =& $amber->getObjectManager();
    $this->_subReport =& $mgr->loadReport($name);
    if (!$this->_subReport) {
      $this->_exporter->printNormal($this, '');
      return $this->stdHeight();
    }

    // Construct filter
    if (($this->LinkChildFields != null) && ($this->LinkMasterFields != null)) {
      $linkChild = explode(';', $this->LinkChildFields);
      $linkMaster = explode(';', $this->LinkMasterFields);
      foreach ($linkChild as $idx => $lchild) {
        $lmaster = $linkMaster[$idx];
        if (!array_key_exists($lmaster, $rep->Cols)) {
          Amber::showError('Error', 'LinkMasterField "' . htmlspecialchars($lmaster) . '" does not exist.');
          die();
        }
        $value = $rep->Cols[$lmaster];
        // FIXME:
        // - filter value has to be handled according to it's type
        //   We need to have the recordset instead of a plain array here
        if ($value === null) {
          $reportFilterArray[] = '(' . $lchild . ' is null)';
        } else {
          $reportFilterArray[] = '(' . $lchild . '=' . $rep->Cols[$lmaster] . ')';
        }
      }
      $this->_subReport->Filter = implode(' AND ', $reportFilterArray);
    }

    $this->_subReport->setSubReport(true);
    $this->_subReport->run($rep->exporterType);
    
    // Adjust control height before passing it to the exporter
    $originalHeight = $this->Height;
    $requestedHeight = $this->Height;
    $subReportHeight = $this->_subReport->getTotalHeight();
    if ($this->CanShrink) {
      if ($subReportHeight == 0) {
        // No output at all
        return 0;
      }
      
      // Shrink
      if ($this->Height > $subReportHeight) {
        $requestedHeight = $subReportHeight;
      }
    }
    // Grow
    if (($this->CanGrow) && ($this->Height < $subReportHeight) ) {
      $requestedHeight = $subReportHeight;
    }
    $this->Height = $requestedHeight;
    
    $this->_exporter->printNormal($this, $this->_subReport->subReportBuff);

    // Reset control height
    $this->Height = $originalHeight;

    return $this->stdHeight($requestedHeight);
  }

  function printDesign()
  {
    $ctrl =& $this->prepareDesign();
    $this->_exporter->printDesign($ctrl, $ctrl->Name);
  }
}

/**
*
* @package Amber
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

  function printNormal()
  {
    $this->_doQuery();
    $this->_exporter->printNormal($this, $this->_data);

    return $this->stdHeight(); ##FIX ME: actual height
  }

  function printDesign()
  {
    $this->_doQuery();
    $this->_exporter->printDesign($this, $this->_data);

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
* @package Amber
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

  function printNormal()
  {
    $this->_exporter->printNormal($this, $this->Value);
    
    return $this->stdHeight();
  }

  function printDesign()
  {
    $this->_exporter->printDesign($this, true);
    
    return $this->stdHeight();
  }
}

/**
*
* @package Amber
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

  function printNormal()
  {
    return $this->stdHeight();
  }

  function printDesign()
  {
    return $this->stdHeight();
  }
}

?>
