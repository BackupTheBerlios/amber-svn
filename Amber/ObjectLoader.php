<?php

class ObjectLoader
{
  var $sysTable = 'tx_amber_sys_objects';
  var $objectTypes = array('report' => 1, 'module' => 2);

  function load($objectName) {}
  function getName() {}
  function getType()
  {
    $types = array_flip($this->objectTypes);
    return $types[$this->_data['type']];
  }
}

class ModuleLoader extends ObjectLoader
{
  function loadFromDb($db)
  {
    $dict = NewDataDictionary($db);
    $sql = 'Select * from ' . $dict->TableName($this->sysTable) . ' where type=' . $this->objectTypes['module'];

    $rs = $db->Execute($sql);
    while ($row = $rs->FetchRow()) {
      eval($row['code']);
    }
  }

  function loadFromFile($dirName)
  {
    $files = glob($dirName . '/*.php');

    if (is_array($files)) {
      foreach ($files as $filename) {
        include_once $filename;
      }
    }
  }

  function getType()
  {
    $types = array_flip($this->objectTypes);
    return $types['module'];
  }
}

class ReportLoader extends ObjectLoader
{
  var $_db;
  var $_data;
  var $_lastError;

  /*
   * @returns true on success, false on error
   */
  function loadFromDb($db, $reportName)
  {
    $dict = NewDataDictionary($db);
    $sql = 'Select * from ' . $dict->TableName($this->sysTable) . ' where name=' . $db->qstr($reportName);
    $sql .= ' AND type=' . $this->objectTypes['report'];

    $rs = $db->SelectLimit($sql, 1);
    if (!$rs) {
      //Amber::showError('Database Error ' . $this->_db->ErrorNo(), $this->_db->ErrorMsg());
      $this->_lastError = 'Query failed: "' . $sql . '"';
      return false;
    }
    $this->_data = $rs->FetchRow();

    if (!$this->_data) {
      $this->_lastError = 'Report "' . $reportName . '" not found in databse';
      return false;
    }

    return true;
  }

  function loadFromFile($dirName, $reportName)
  {
    $xmlLoader = new XMLLoader();

    if (empty($dirName)) {
      $this->_reportDir = '.';
    }

    $res =& $xmlLoader->getArray($dirName . '/' . $reportName . '.xml');
    $param = $res['report'];
    if (isset($param['Name'])) {
      $this->_data['name'] = $param['Name'];
    }

    $this->_data['design'] = file_get_contents($dirName . '/' . $param['FileNameDesign']);

    if (isset($param['FileNameCode']) && isset($param['ClassName'])) {
      $this->_data['class'] = $param['ClassName'];
      $this->_data['code'] = file_get_contents($dirName . '/' . $param['FileNameCode']);
    }

    return true;
  }

  function getLastError()
  {
    return $this->_lastError;
  }

  function getType()
  {
    return $this->_data['type'];
  }

  function getName()
  {
    return $this->_data['name'];
  }

  function getDesign()
  {
    return XMLLoader::_makeXmlTree($this->_data['design']);
  }

  function getClassName()
  {
    return $this->_data['class'];
  }

  function getCode()
  {
    return $this->_data['code'];
  }

}

?>
