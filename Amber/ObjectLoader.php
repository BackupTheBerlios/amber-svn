<?php

class ObjectLoader
{
  function load($objectName) {}
  function getName() {}
  function getType() {}
}

class ReportLoader extends ObjectLoader
{
  var $_db;
  var $_data;

  /*
   * @returns true on success, false on error
   */
  function loadFromDb($db, $reportName)
  {
    $dict = NewDataDictionary($db);
    $sysTable = 'amber_sys_objects';
    $sql = 'Select * from ' . $dict->TableName($sysTable) . ' where name=' . $db->qstr($reportName);

    $rs = $db->SelectLimit($sql, 1);
    if (!$rs) {
      //showError('Database Error ' . $this->_db->ErrorNo(), $this->_db->ErrorMsg());
      return false;
    }
    $this->_data = $rs->FetchRow();

    if (!$this->_data) {
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

  function getType()
  {
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