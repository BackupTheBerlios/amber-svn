<?php

/**
*
* @package PHPReport
* @subpackage ReportEngine
*
*/

require_once 'adodb/adodb.inc.php';
require_once 'Report.php';
require_once 'Form.php';

/**
*
* @package PHPReport
* @subpackage ReportEngine
*
*/
class ObjectLoader
{
  var $_lastError = 'ObjectLoader: No error message set';
  var $objectTypes = array('report' => 1, 'module' => 2, 'form' => 3);

  /**
   * @access public
   * @param string
   * @param string
   */
  function &load($type, $objectName)
  {
    $types = array_keys($this->objectTypes);

    if (!in_array($type, $types)) {
      $this->_lastError = 'Requested loading of unsupported object type: "' . $type . '"';
      return false;
    }

    $obj = null;
    switch ($type) {
      case 'module':
        $obj =& $this->loadModule($objectName);
        break;
      case 'report':
        $obj = $this->loadReport($objectName);
        break;
      case 'form':
        $obj = $this->loadForm($objectName);
        break;
    }

    if (is_null($obj)) {
      $this->_lastError = 'ObjectLoader::load(): An unknown error occured';
    }
    return $obj;
  }

  /**
   * @access public
   * @return string
   */
  function getLastError()
  {
    $tmp = $this->_lastError;
    $this->_lastError = 'ObjectLoader: No error message set';
    return $tmp;
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

/**
*
* @package PHPReport
* @subpackage ReportEngine
*
*/
class ObjectLoaderDb extends ObjectLoader
{
  var $_db;
  var $_data;
  var $sysTable = 'tx_amber_sys_objects';

  function setDatabase(&$db)
  {
    if (!is_object($db)) {
      $this->_lastError = 'Parameter given is not an ADODB database object';
      return;
    }

    $this->_db =& $db;
  }

  function loadModule()
  {
    $rs =& $this->fetchRecord('module');
    if(!$rs) {
      return true; // Try to continue even if it has failed
    }

    if ($rs->numRows() > 0) {
      while ($row = $rs->FetchRow()) {
        eval($row['code']);
      }
    }

    return true;
  }

  function &loadForm($formName)
  {
    $rs =& $this->fetchRecord('form', $formName);
    if(!$rs) {
      return false;
    }

    $data = $rs->FetchRow();
    if (!$data) {
      $this->_lastError = 'Form "' . $formName . '" not found in database';
      return false;
    }

    $data['code'] = '<?php ' . $data['code'] . ' ?>';

    $form =& new Form();
    $form->setConfig($this->_globalConfig);
    $form->initialize($data);

    return $form;
  }

  /*
   * @returns true on success, false on error
   */
  function &loadReport($reportName)
  {
    $rs =& $this->fetchRecord('report', $reportName);
    if(!$rs) {
      return false;
    }

    $data = $rs->FetchRow();
    if (!$data) {
      $this->_lastError = 'Report "' . $reportName . '" not found in database';
      return false;
    }

    $data['code'] = '<?php ' . $data['code'] . ' ?>';

    $report =& new ReportPaged();
    $report->setConfig($this->_globalConfig);
    $report->initialize($data);
    return $report;
  }

  function &fetchRecord($type, $name = '')
  {
     if (!isset($this->_db)) {
      $this->_lastError = 'ObjectLoader: Database needs to be set before attempting to load an object';
      return false;
    }

    $dict = NewDataDictionary($this->_db);
    $sql = 'SELECT * FROM ' . $dict->TableName($this->sysTable) . ' WHERE';
    if ($type != 'module') {
      $sql .= ' name=' . $this->_db->qstr($name) . ' AND';
    }
    $sql .= ' type=' . $this->objectTypes[$type];

    $rs =& $this->_db->Execute($sql);
    if (!$rs) {
      $this->_lastError = 'Query failed: "' . $sql . '", ErrorMsg: ' . $this->_db->ErrorMsg();
      return false;
    }

    return $rs;
  }
}

/**
*
* @package PHPReport
* @subpackage ReportEngine
*
*/
class ObjectLoaderFile extends ObjectLoader
{
  var $_basePath;

  function setBasePath($path)
  {
    if (empty($path)) {
      $path = '.';
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

    $report =& new ReportPaged();
    $report->setConfig($this->_globalConfig);
    $report->initialize($data);

    return $report;
  }

  function &loadForm($formName)
  {
    $formPath = $this->_basePath . '/forms/';
    $xmlLoader = new XMLLoader();

    $res =& $xmlLoader->getArray($formPath . '/' . $formName . '.xml');
    $param = $res['form'];
    if (isset($param['Name'])) {
      $data['name'] = $param['Name'];
    }

    $data['design'] = file_get_contents($formPath . '/' . $param['FileNameDesign']);

    if (isset($param['FileNameCode']) && isset($param['ClassName'])) {
      $data['class'] = $param['ClassName'];
      $data['code'] = file_get_contents($formPath . '/' . $param['FileNameCode']);
    } else {
      $data['class'] = '';
    }

    $form =& new Form();
    $form->setConfig($this->_globalConfig);
    $form->initialize($data);

    return $form;
  }
}

?>
