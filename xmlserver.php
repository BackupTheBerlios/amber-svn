<?php

ini_set('include_path', ini_get('include_path') . ':' . dirname(__FILE__). '/lib/');

require_once 'adodb/adodb.inc.php';
include_once 'Amber/Amber.php';
include_once 'Amber/lib/IXR_Library.inc.php';
include_once 'Amber/AmberConfig.php';

class AmberXMLServer extends IXR_Server
{
  var $_globalConfig;
  var $sysTableName = 'tx_amber_sys_objects';
  var $objectTypes = array('report' => 1, 'module' => 2);

  function AmberXMLServer()
  {
  }

  function processRequest()
  {
    $this->IXR_Server(array(
      'Amber.writeReportXML' => 'this:writeReportXML',
      'Amber.fileExists' => 'this:fileExists',
      'Amber.getReportList' => 'this:getReportList',
      'Amber.getCode' => 'this:getCode'
    ));
  }

  /**
   *
   * @access public
   * @param Config
   *
   */
  function setConfig($cfgObj)
  {
    if (is_object($cfgObj) && is_a($cfgObj, 'AmberConfig')) {
      $this->_globalConfig = $cfgObj;
    }
  }

  function &currentDb()
  {
    if (!isset($this->_db)) {
      $cfg =& $this->_globalConfig;
      $db =& ADONewConnection($cfg->getDriver());
      $conResult = $db->PConnect($cfg->getHost(), $cfg->getUsername(), $cfg->getPassword(), $cfg->getDbName());
      $db->SetFetchMode(ADODB_FETCH_ASSOC);
      if ($conResult == false) {
        Amber::showError('Database Error '  . $db->ErrorNo(), $db->ErrorMsg());
        die();
      }
      $this->_db =& $db;
    }

    return $this->_db;
  }

  function writeReportXML($param)
  {
    $repName = $param[0];
    $repDesign = $param[1];
    $repClass = $param[2];
    $repCode = $param[3];
    $repOverwrite = $param[4];
    
    $db =& $this->currentDb();
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
    $db =& $this->currentDb();
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
    $db = $this->currentDb();
    $dict = NewDataDictionary($db);
    $sql = 'Select name from ' . $dict->TableName($this->sysTableName);

    return $db->GetAll($sql);
  }

  function getCode($name)
  {
    $db =& $this->currentDb();
    $dict = NewDataDictionary($db);
    $sql = 'Select code from ' . $dict->TableName($this->sysTableName) . ' where name=' . $db->Quote($name);

    return $db->GetOne($sql);
  }
}

/////////////////////////////////////////////////////////////////////////////


$cfg = new AmberConfig();
$cfg->fromXML(dirname(__FILE__) . '/Amber/conf/localconf.xml');

$server = new AmberXMLServer();
$server->setConfig($cfg);
$server->processRequest();

/////////////////////////////////////////////////////////////////////////////

?>
