<?php

require_once 'adodb/adodb.inc.php';
require_once 'Report.php';

class ObjectLoader
{
  var $_lastError = 'ObjectLoader: No error message set';
  var $objectTypes = array('report' => 1, 'module' => 2);

  /**
   * @access public
   * @param string
   * @param string
   */
  function &load($type, $objectName)
  {
    $types = array_keys($this->objectTypes);

    if (!in_array($type, $types)) {
      Amber::showError('Error', 'Requested loading of unsupported object type: "' . $type . '"');
      return false;
    }

    switch ($type) {
      case 'module':
        return $this->loadModule($objectName);
        break;
      case 'report':
        return $this->loadReport($objectName);
        break;
    }

    $this->_lastError = 'ObjectLoader::load(): An unknown error occured';
    return false;
  }

  /**
   * @access public
   * @return string
   */
  function getLastError()
  {
    return $this->_lastError;
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
}

/*
 *
 * DATABASE
 *
 */
class ObjectLoaderDb extends ObjectLoader
{
  var $_db;
  var $_data;
  var $sysTable = 'tx_amber_sys_objects';

  function setDatabase($db)
  {
    if (!is_object($db)) {
      $this->_lastError = 'Parameter given is not an ADODB database object';
      return;
    }

    $this->_db =& $db;
  }

  function loadModule()
  {
    if (!isset($this->_db)) {
      $this->_lastError = 'ObjectLoader: Database needs to be set before attempting to load an object';
      return false;
    }

    $dict = NewDataDictionary($this->_db);
    $sql = 'Select * from ' . $dict->TableName($this->sysTable) . ' where type=' . $this->objectTypes['module'];

    $rs = $this->_db->Execute($sql);
    while ($row = $rs->FetchRow()) {
      eval($row['code']);
    }

    return true;
  }

  /*
   * @returns true on success, false on error
   */
  function &loadReport($reportName)
  {
    if (!isset($this->_db)) {
      $this->_lastError = 'ObjectLoader: Database needs to be set before attempting to load an object';
      return false;
    }

    $dict = NewDataDictionary($this->_db);
    $sql = 'Select * from ' . $dict->TableName($this->sysTable) . ' where name=' . $this->_db->qstr($reportName);
    $sql .= ' AND type=' . $this->objectTypes['report'];

    $rs = $this->_db->SelectLimit($sql, 1);
    if (!$rs) {
      $this->_lastError = 'Query failed: "' . $sql . '"';
      return false;
    }
    $data = $rs->FetchRow();

    if (!$data) {
      $this->_lastError = 'Report "' . $reportName . '" not found in database';
      return false;
    }

    $data['code'] = '<?php ' . $data['code'] . ' ?>';

    $report =& new Report();
    $report->setConfig($this->_globalConfig);
    $report->initialize($data);

    return $report;
  }
}

/*
 *
 * FILE
 *
 */
class ObjectLoaderFile extends ObjectLoader
{
  var $_basePath;

  function setBasePath($path)
  {
    if (empty($path)) {
      $this->_basePath = '.';
    }

    if (!is_dir($path)) {
      Amber::showError('ObjectLoaderFile::setBasePath(): Argument given is not a directory: ' . htmlentities($path));
      die();
    }
    $this->_basePath = $path;
  }

  function loadModule()
  {
    $modPath = $this->_basePath . '/modules/';

    // There's no modules directory...
    if (!is_dir($modPath)) {
      return true;
    }

    $modFiles = glob($modPath . '*.php');
    if (is_array($modFiles)) {
      foreach ($modFiles as $filename) {
        include_once $modpath . $filename;
      }
    }

    return true;
  }

  function &loadReport($reportName)
  {
    $repPath = $this->_basePath . '/reports/';
    $xmlLoader = new XMLLoader();

    $res =& $xmlLoader->getArray($repPath . '/' . $reportName . '.xml');
    $param = $res['report'];
    if (isset($param['Name'])) {
      $data['name'] = $param['Name'];
    }

    $data['design'] = file_get_contents($repPath . '/' . $param['FileNameDesign']);

    if (isset($param['FileNameCode']) && isset($param['ClassName'])) {
      $data['class'] = $param['ClassName'];
      $data['code'] = file_get_contents($repPath . '/' . $param['FileNameCode']);
    }

    $report =& new Report();
    $report->setConfig($this->_globalConfig);
    $report->initialize($data);

    return $report;
  }
}

?>
