<?php

require_once 'AmberConfig.php';

class AmberObject
{
  var $_globalConfig;
  
  function AmberObject()
  {
    $this->_globalConfig = new AmberConfigNull();
  }
  
  /**
   *
   * @access public
   * @param AmberConfig
   *
   */
  function setConfig($cfgObj)
  {
    if (is_object($cfgObj) && is_a($cfgObj, 'AmberConfig')) {
      $this->_globalConfig = $cfgObj;
    } else {
      Amber::showError('Warning - Report::setConfig()', 'Invalid paramater');
    }
  }
  
  function initialize(&$data)
  {
    Amber::showError('Error', 'Virtual method AmberObject::initialize() not overridden');
  }
  
  function run($type)
  {
    Amber::showError('Error', 'Virtual method AmberObject::run() not overridden');
  }
  
  /**
   * @access private
   * @param string
   */
  function _installExporter($type)
  {
    $this->_exporter =& ExporterFactory::create($type, $this);
    if (is_array($this->Controls)) {
      $ctlNames = array_keys($this->Controls);
      foreach ($ctlNames as $ctlName) {
        $this->_exporter->setControlExporter($this->Controls[$ctlName]);
      }
    }
  }
}


?>
