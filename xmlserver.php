<?php

require_once 'lib/adodb/adodb.inc.php';
include_once 'Amber/lib/IXR_Library.inc.php';
include_once 'Amber/Config.php';

class AmberXMLServer extends IXR_Server
{
  var $_globalConfig;

  function AmberXMLServer()
  {
  }

  function processRequest()
  {
    $this->IXR_Server(array(
      'Amber.writeXML' => 'this:writeXML',
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
    if (is_object($cfgObj) && is_a($cfgObj, 'Config')) {
      $this->_globalConfig = $cfgObj;
    }
  }

  function &currentDb()
  {
    if (!isset($this->_db)) {
      $cfg =& $this->_globalConfig;
      $db =& ADONewConnection($cfg->driver);
      $conResult = @$db->PConnect($cfg->host, $cfg->username, $cfg->pwd, $cfg->database);
      $db->SetFetchMode(ADODB_FETCH_ASSOC);
      if ($conResult == false) {
        showError('Database Error '  . $db->ErrorNo(), $db->ErrorMsg());
        die();
      }
      $this->_db =& $db;
    }

    return $this->_db;
  }


  function writeXML($a)
  {
    $fileName = $a[0];
    $xmlData = $a[1];

    if (!is_string($fileName)) {
      return new IXR_Error(1, 'Parameter Error: File name must be a string');
    }
    if (!is_string($xmlData)) {
      return new IXR_Error(2, 'Parameter Error: XML data must be a string');
    }

    $fp = fopen($fileName, 'w');
    if (!$fp) {
      return new IXR_Error(3, 'Unable to open file for writing');
    }
    fwrite($fp, $xmlData);
    fclose($fp);
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
    $sysTable = 'amber_sys_objects';
    $sql = 'Select name from ' . $dict->TableName($sysTable);

    return $db->GetAll($sql);
  }

  function getCode($name)
  {
    $db = $this->currentDb();
    $dict = NewDataDictionary($db);
    $sysTable = 'amber_sys_objects';
    $sql = 'Select code from ' . $dict->TableName($sysTable) . ' where name=' . $db->Quote($name);

    return $db->GetOne($sql);
  }
}

/////////////////////////////////////////////////////////////////////////////

$cfg = new Config();
$cfg->fromXML('Amber/conf/localconf.xml');

$server = new AmberXMLServer();
$server->setConfig($cfg);
$server->processRequest();

/////////////////////////////////////////////////////////////////////////////

?>
