<?php

/**
*
* @package Amber
* @subpackage ReportEngine
*
*/

require_once 'ObjectLoader.php';

class AmberObjectRaw
{
  var $name;
  var $class;
  var $code;
  var $design;
}

class ObjectManager
{
  var $amber;
  var $objectLoader;
  var $_config;
  var $_lastError = 'ObjectManager: No error message set';
  var $objectTypes = array('report' => 1, 'module' => 2);

  function ObjectManager(&$amber)
  {
    $this->amber =& $amber;
    $this->_config =& $amber->_config;
    
    if (!isset($this->objectLoader)) {
      $medium = $this->_config->get('sys/medium');
      if ($medium == 'db') {
        $this->objectLoader =& new ObjectLoaderDb();
        $this->objectLoader->setDatabase(Amber::sysDb());
      } else {
        $this->objectLoader =& new ObjectLoaderFile();
        $this->objectLoader->setBasePath($this->_config->get('sys/basepath'));
      }
    }
  }

  /**
   *
   * @access public
   * @abstract
   * @param string
   * @return array
   *
   */
  function getList($type)
  {
    return $this->objectLoader->getList($type);
  }

  /**
   * @access public
   * @param string
   * @param string
   * @return AmberObjectRaw
   */
  function &loadObject($type, $name)
  {
    $types = array_keys($this->objectTypes);

    if (!in_array($type, $types)) {
      Amber::showError('ObjectManager', 'Requested loading of unsupported object type: "' . $type . '"');
      die();
    }
  
    $obj =& $this->objectLoader->load($type, $name);

    if (!$obj) {
      Amber::showError('ObjectManager', 'Unable to load object "' . $name . '"');
      die();
    }

    return $obj;
  }

  function &loadReport($name)
  {
    $obj =& $this->loadObject('report', $name);
    
    $report =& new ReportPaged();
    $report->setConfig($this->_config);
    $report->initialize($obj);
    
    return $report;
  }

  /**
   * @access public
   * @return bool
   */
  function &loadModule($name)
  {
    $obj =& $this->loadObject('module', $name);
    
    $module =& new Module;
    $module->initialize($obj);
    
    return $module;
  }
}

?>