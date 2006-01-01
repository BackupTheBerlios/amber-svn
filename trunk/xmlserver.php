<?php

ini_set('include_path', dirname(__FILE__). '/lib' . PATH_SEPARATOR . ini_get('include_path'));

require_once 'adodb/adodb.inc.php';
include_once 'Amber/Amber.php';
include_once 'Amber/lib/IXR_Library.inc.php';
include_once 'Amber/AmberConfig.php';

class AmberXMLServer extends IXR_Server
{
  var $_globalConfig;
  var $sysTableName = 'tx_amber_sys_objects';
  var $objectTypes = array('report' => 1, 'module' => 2, 'form' => 3);
  var $amber;

  function AmberXMLServer()
  {
  }

  function processRequest()
  {
    $this->amber =& Amber::getInstance($this->_globalConfig);
    
    $this->IXR_Server(array(
      'Amber.ping' => 'this:ping',
      'Amber.writeReportXML' => 'this:writeReportXML',
      'Amber.fileExists' => 'this:fileExists',
      'Amber.getReportList' => 'this:getReportList',
      'Amber.getReport' => 'this:getReport',
      'Amber.setReportCode' => 'this:setReportCode',
      'Amber.getFormList' => 'this:getFormList',
      'Amber.getForm' => 'this:getForm',
      'Amber.getCode' => 'this:getCode',
      'ObjectManager.getList' => 'this:getList',
      'ObjectManager.loadObject' => 'this:loadObject',
      'ObjectManager.saveObject' => 'this:saveObject',
    ));
  }

  /**
   *
   * @access public
   * @param Config
   *
   */
  function setConfig(&$cfgObj)
  {
    if (is_object($cfgObj) && is_a($cfgObj, 'AmberConfig')) {
      $this->_globalConfig = $cfgObj;
    } else {
      die(Amber::showError('Error', 'Given parameter is not an instance of AmberConfig', true));
    }
  }

  function &currentDb()
  {
    $amber =& Amber::getInstance($this->_globalConfig);
    if (!is_object($amber)) {
      return false;
    }
    
    return $amber->currentDb();
  }
  
  function &sysDb()
  {
    $amber =& Amber::getInstance($this->_globalConfig);
    if (!is_object($amber)) {
      return false;
    }

    return $amber->sysDb();
  }

  function ping()
  {
    return 'pong';
  }

  function writeReportXML($param)
  {
    $repName = $param[0];
    $repDesign = $param[1];
    $repClass = $param[2];
    $repCode = $param[3];
    $repOverwrite = $param[4];

    $db =& $this->sysDb();
    $dict = NewDataDictionary($db);
    
    // if object exists do update else insert
    if ($this->objectExists($this->objectTypes['report'], $repName)) {
      $sql = 'UPDATE ' . $dict->TableName($this->sysTableName) . ' SET ';
      $sql .= 'design=' . $db->Quote($repDesign) . ', ';
      $sql .= 'class=' . $db->Quote($repClass);
      if ($repOverwrite == true) {
        $sql .= ', code=' . $db->Quote($repCode);
      }
      $sql .= ' WHERE name=' . $db->Quote($repName) . ' AND type=' . $this->objectTypes['report'];
    } else {
      $sql = 'INSERT INTO ' . $dict->TableName($this->sysTableName) . ' VALUES ';
      $sql .= '(null, 0, 0, 0, 0, 0, 0, 0, 0, ';
      $sql .= $db->Quote($repName) . ', ';
      $sql .= $db->Quote($repDesign) . ', ';
      $sql .= $db->Quote($repClass) . ', ';
      $sql .= $db->Quote($repCode) . ', 1, 1);';
    }
    
    if (!$db->Execute($sql)) {
      return new IXR_Error(1, 'Database Error: ' . $db->ErrorMsg());
    }
    
    return true;
  }

  function objectExists($type, $name)
  {
    $db =& $this->sysDb();
    $dict = NewDataDictionary($db);
    
    $sql = 'SELECT uid FROM ' . $dict->TableName($this->sysTableName);
    $sql .= ' WHERE name=' . $db->Quote($name) . ' AND type=' . $type;
    
    if (!$db->GetOne($sql)) {
      return false;
    }

    return true;
  }

  function fileExists($fileName)
  {
    if (!is_string($fileName)) {
      return new IXR_Error(1, 'Parameter Error: File name must be a string');
    }

    return file_exists($fileName);

  }

  function getReportList()
  {
    $db =& $this->sysDb();
    $dict = NewDataDictionary($db);
    $sql = 'Select name from ' . $dict->TableName($this->sysTableName) . ' WHERE type=' . $this->objectTypes['report'];

    return $db->GetAll($sql);
  }
  
  function getFormList()
  {
    $db =& $this->sysDb();
    $dict = NewDataDictionary($db);
    $sql = 'Select name from ' . $dict->TableName($this->sysTableName) . ' WHERE type=' . $this->objectTypes['form'];

    return $db->GetAll($sql);
  }
  
  function getForm($name)
  {
    $db =& $this->sysDb();
    $dict = NewDataDictionary($db);
    $sql = 'Select * from ' . $dict->TableName($this->sysTableName) . ' WHERE name=' . $db->Quote($name) . ' AND type=' . $this->objectTypes['form'];

    return $db->GetAll($sql);
  }
  
  function getReport($name)
  {
    $db =& $this->sysDb();
    $dict = NewDataDictionary($db);
    $sql = 'Select * from ' . $dict->TableName($this->sysTableName) . ' WHERE name=' . $db->Quote($name) . ' AND type=' . $this->objectTypes['report'];

    return $db->GetAll($sql);
  }
  
  function setReportCode($param)
  {
    $name = $param[0];
    $code = $param[1];

    $db =& $this->sysDb();
    $dict = NewDataDictionary($db);
    $sql = 'UPDATE ' . $dict->TableName($this->sysTableName) . ' SET code=' . $db->Quote($code) . ' WHERE name=' . $db->Quote($name) . ' AND type=' . $this->objectTypes['report'];

    $db->Execute($sql);
    return true;
  }

  function getCode($name)
  {
    $db =& $this->sysDb();
    $dict = NewDataDictionary($db);
    $sql = 'Select code from ' . $dict->TableName($this->sysTableName) . ' where name=' . $db->Quote($name);

    return $db->GetOne($sql);
  }
  
  function getList($type)
  {
    $mgr =& $this->amber->getObjectManager();
    return $mgr->getList($type);
  }
  
  function loadObject($param)
  {
    $type = $param[0];
    $name = $param[1];

    $mgr =& $this->amber->getObjectManager();
    return $mgr->loadObject($type, $name);
  }
  
  function saveObject($param)
  {
    $type = $param[0];
    $name = $param[1];
    $obj  = $param[2];

    $mgr =& $this->amber->getObjectManager();
    $mgr->saveObject($type, $name, $obj);
    
    return true;
  }
}

/////////////////////////////////////////////////////////////////////////////


$cfg =& new AmberConfig();
$cfg->fromXML(dirname(__FILE__) . '/Amber/conf/localconf.xml');

$server =& new AmberXMLServer();
$server->setConfig($cfg);
$server->processRequest();

/////////////////////////////////////////////////////////////////////////////

?>
