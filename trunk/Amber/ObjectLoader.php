<?php

/**
*
* @package Amber
* @subpackage ReportEngine
*
*/

require_once 'adodb/adodb.inc.php';
require_once 'ReportPaged.php';
require_once 'Module.php';

/**
*
* This will be an interface (PHP5)
*
* @package Amber
* @subpackage ReportEngine
*
* @abstract
*
*/
class ObjectLoader
{
  var $objectTypes = array('report' => 1, 'module' => 2);

  /**
   * @access public
   * @abstract
   * @param string type of objects (values: "report", "module")
   *
   */
  function getList($type)
  {
  }

  /**
   * @access public
   * @abstract
   * @param string type of object to be loaded (values: "report", "module")
   * @param string name of object
   *
   */
  function &load($type, $objectName)
  {
  }

  /**
   * @access public
   * @abstract
   * @param string type of object to be loaded (values: "report", "module")
   * @param string name of object
   *
   */
  function &save($type, $objectName)
  {
  }
}

/**
*
* @package Amber
* @subpackage ReportEngine
*
*/
class ObjectLoaderDb extends ObjectLoader
{
  var $_db;
  var $_data;
  var $sysTable = 'tx_amber_sys_objects';

  /**
   *
   * @access public
   * @param ADOConnection
   *
   */
  function setDatabase(&$db)
  {
    if (!is_object($db)) {
      Amber::showError('ObjectLoaderDb', 'Parameter given is not an ADODB database object');
      die();
    }

    $this->_db =& $db;
  }

  /**
   *
   * @access public
   * @param string
   * @return array
   *
   */
  function getList($type)
  {
    $types = array_keys($this->objectTypes);

    if (!in_array($type, $types)) {
      Amber::showError('ObjectLoaderDb', 'Requested listing of unsupported object type: "' . $type . '"');
      die();
    }

    if (!isset($this->_db)) {
      Amber::showError('ObjectLoaderDb', 'Database needs to be set before attempting to load an object');
      die();
    }

    $dict = NewDataDictionary($this->_db);
    $sql = 'SELECT name FROM ' . $dict->TableName($this->sysTable) . ' WHERE';
    $sql .= ' type=' . $this->objectTypes[$type] . ' ORDER BY name';
    $result = $this->_db->GetAll($sql);

    foreach ($result as $row) {
      $list[] = $row['name'];
    }
    return $list;
  }

  function &load($type, $objectName)
  {
    $rs =& $this->fetchRecord($type, $objectName);

    $data = $rs->FetchRow();
    if (!$data) {
      Amber::showError('ObjectLoaderDb', 'Object (' . $type . ') "' . $objectName . '" not found in database');
      die();
    }

    $obj = new AmberObjectRaw;
    $obj->type = $data['type'];
    $obj->name = $data['name'];
    $obj->class = $data['class'];
    $obj->code = $data['code'];
    $obj->design = $data['design'];

    return $obj;
  }

  /**
   * @access public
   * @param string
   * @param string
   * @param AmberObjectRaw
   */
  function save($type, $objectName, &$obj)
  {
    $rs =& $this->fetchRecord($type, $objectName);

    $data = $rs->FetchRow();
    if (!$data) {
      Amber::showError('ObjectLoaderDb', 'Object (' . $type . ') "' . $objectName . '" not found in database');
      die();
    }

    $data = array();
    $data = (array)$obj;

    $sql = $this->_db->GetUpdateSql($rs, $data, false);
    $this->_db->Execute($sql);
    if ($this->_db->ErrorNo() != 0) {
      Amber::showError('Database Error ' . $this->_db->ErrorNo(), $this->_db->ErrorMsg());
      die();
    }
  }

  /**
   *
   * @access private
   * @param string type
   * @param string name
   * @return ADORecordSet
   *
   */
  function &fetchRecord($type, $name)
  {
     if (!isset($this->_db)) {
      Amber::showError('ObjectLoaderDb', 'Database needs to be set before attempting to load an object');
      die();
    }

    $dict = NewDataDictionary($this->_db);
    $sql = 'SELECT * FROM ' . $dict->TableName($this->sysTable) . ' WHERE ';
    $sql .= 'name=' . $this->_db->qstr($name) . ' AND ';
    $sql .= 'type=' . $this->objectTypes[$type];

    $rs =& $this->_db->Execute($sql);
    if (!$rs) {
      Amber::showError('ObjectLoaderDb', 'Query failed: "' . $sql . '", ErrorMsg: ' . $this->_db->ErrorMsg());
      die();
    }

    return $rs;
  }
}

/**
*
* @package Amber
* @subpackage ReportEngine
*
*/
class ObjectLoaderFile extends ObjectLoader
{
  var $_basePath;

  /**
   *
   * @access public
   * @param string
   *
   */
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

  /**
   *
   * @access public
   * @param string
   * @return array
   *
   */
  function getList($type)
  {
    switch ($type) {
      case 'module':
        return $this->getModuleList($type);
        break;
      case 'report':
        return $this->getReportList($type);
        break;
    }

    return null;
  }

  function getModuleList($type)
  {
    $path = $this->_basePath . '/modules/';

    $allFiles = glob($path . '/' . '*.php');
    if (is_array($allFiles)) {
      foreach ($allFiles as $file) {
        $files[] = basename($file, '.php');
      }
    }

    return $files;
  }

  function getReportList()
  {
    $path = $this->_basePath . '/reports/';

    $allFiles = glob($path . '/' . '*.xml');
    if (is_array($allFiles)) {
      foreach ($allFiles as $file) {
        if (!strstr($file, '-Design.xml')) {
          $files[] = basename($file, '.xml');
        }
      }
    }

    return $files;
  }

  function &load($type, $objectName)
  {
    switch ($type) {
      case 'module':
        $obj =& $this->loadModule($objectName);
        break;
      case 'report':
        $obj =& $this->loadReport($objectName);
        break;
    }

    return $obj;
  }

  /**
   * @access private
   * @return bool
   */
  function &loadModule($name)
  {
    $modPath = $this->_basePath . '/modules/';

    // There's no modules directory...
    if (!is_dir($modPath)) {
      return null;
    }

    $fileName = $modPath . '/' . $name . '.php';
    if (!is_file($fileName)) {
      Amber::showError('Error', 'Unable to open module "' . $name . '" from ' . $modPath);
      die();
    }

    $obj = new AmberObjectRaw;
    $obj->type = $this->objectTypes['module'];
    $obj->name = basename($name, '.php');
    $obj->code = file_get_contents($modPath . '/' . $name . '.php');

    return $obj;
  }

  /**
   * @access private
   * @param string name of the report
   * @return Report
   */
  function &loadReport($name)
  {
    $repPath = $this->_basePath . '/reports/';
    $xmlLoader = new XMLLoader();

    $res =& $xmlLoader->getArray($repPath . '/' . $name . '.xml');
    $param = $res['report'];

    $obj = new AmberObjectRaw;
    $obj->type = $this->objectTypes['report'];
    if (isset($param['Name'])) {
      $obj->name = $param['Name'];
    }

    $obj->design = file_get_contents($repPath . '/' . $param['FileNameDesign']);

    if (isset($param['FileNameCode']) && isset($param['ClassName'])) {
      $obj->class = $param['ClassName'];
      $obj->code = trim(file_get_contents($repPath . '/' . $param['FileNameCode']));
    }

    return $obj;
  }
}

?>
