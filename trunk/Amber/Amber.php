<?php

/**
*
* @package Amber
* @subpackage ReportEngine
*
*/

require_once 'ReportPaged.php';
require_once 'ObjectManager.php';
require_once 'adodb/adodb.inc.php';

define('AC_DESIGN', 1);
define('AC_NORMAL', 2);

/**
*
* @package Amber
* @subpackage ReportEngine
*
*/
class Amber
{
  var $_config;
  var $_objectManager;

  var $_db; // ADODB database containing data
  var $_sysdb; // ADODB database containig tx_amber_sys_objects

  var $_stdExporter; //std-exporter: the exporter the report was opened with

  /**
   * @static
   * @access protected
   *
   */
  function init()
  {
    $this->_objectManager =& new ObjectManager($this);
    $mgr =& $this->getObjectManager();

    /* Process all modules available */
    $moduleList = $mgr->getList('module');
    if (is_array($moduleList)) {
      foreach ($moduleList as $moduleName) {
        $moduleObj = $mgr->loadModule($moduleName);
        $moduleObj->run();
      }
    }
  }

  /**
   * @static
   * @access public
   * @param AmberConfig
   * @return Amber reference to singleton
   */
  function &getInstance($config = null)
  {
    static $instance = null;

    if (is_null($instance)) {
      if (is_null($config)) {
        // config parameter must be present on singleton creation
        die(Amber::showError('Error', 'Config parameter must not be null on first call to Amber::getInstance()', true));
      }
      $instance = new Amber();
      if (!is_a($config, 'AmberConfig')) {
        die(Amber::showError('Error', 'Given parameter is not an instance of AmberConfig', true));
      }
      $instance->_config = $config;
      $instance->init();
    }

    return $instance;
  }

  /**
   * @static
   * @access public
   * @return ObjectManager
   */
  function &getObjectManager()
  {
    if (!isset($this->_objectManager)) {
      $this->_objectManager = new ObjectManager($this);
    }

    return $this->_objectManager;
  }

  /**
   * @static
   * @access public
   * @return ADOConnection Connection to database containing the tables used by reports for data retrieval
   */ 
  function &currentDb()
  {
    $amber =& Amber::getInstance();

    if (!isset($amber->_db)) {
      $dbCfg =& $amber->_config->get('db');
      $db =& ADONewConnection($dbCfg['driver']);
      $conResult = @$db->NConnect($dbCfg['host'], $dbCfg['username'], $dbCfg['password'], $dbCfg['dbname']);
      $db->SetFetchMode(ADODB_FETCH_ASSOC);
      if ($conResult == false) {
        Amber::showError('Database Error '  . $db->ErrorNo(), $db->ErrorMsg());
        die();
      }
      $amber->_db =& $db;
    }
    if (empty($amber->_db->_connectionID)) {
      Amber::showError('Internal Error', ' Lost connection to current database');
      die();
    }
    if (isset($amber->_sysdb) && empty($amber->_sysdb->_connectionID)) {
      Amber::showError('Internal Error', ' Lost connection to system database');
      die();
    }

    return $amber->_db;
  }

  /**
   * @static
   * @access public
   * @return ADOConnection Connection to database containing the table where the report definitions are stored
   * or null if Amber has not been configured to use a system database
   */ 
  function &sysDb()
  {
    $amber =& Amber::getInstance();

    /* Return null if Amber has not been configured to read object definitions from database */
    if ($amber->_config->get('sys/medium') != 'db') {
      $amber->_sysdb = null;
      return $amber->_sysdb;
    }

    if (!isset($amber->_sysdb)) {
      $sysdbCfg =& $amber->_config->get('sys/database');
      $sysdb =& ADONewConnection($sysdbCfg['driver']);
      $sysdb->SetFetchMode(ADODB_FETCH_ASSOC);
      $conResult = @$sysdb->NConnect($sysdbCfg['host'], $sysdbCfg['username'], $sysdbCfg['password'], $sysdbCfg['dbname']);
      if ($conResult == false) {
        Amber::showError('Database Error '  . $sysdb->ErrorNo(), $sysdb->ErrorMsg());
        die();
      }
      $amber->_sysdb =& $sysdb;
    }
    if (empty($amber->_sysdb->_connectionID)) {
      Amber::showError('Internal Error', ' Lost connection to system database');
      die();
    }
    if (isset($amber->_db) && empty($amber->_db->_connectionID)) {
      Amber::showError('Internal Error', ' Lost connection to current database');
      die();
    }

    return $amber->_sysdb;
  }

  /**
   * @static
   * @param string
   * @param
   * @param string
   * @param string
   * @param bool
   * @access public
   */
  function openReport($reportName, $mode = AC_NORMAL, $where = '', $type = 'html', $noMargin = false)
  {
    $mgr =& $this->getObjectManager();
    
    $rep =& $mgr->loadReport($reportName);
    if (!$rep) {
      Amber::showError('Amber', 'Error while loading report "' . $reportName . '"');
      return false;
    }

    $rep->setWhere($where);
    $rep->setNoMargins($noMargin);

    if (($type == 'html') || ($type == 'typo3')) {
      $rep->setNoAutoPage(true);
    }

    // Run it
    switch ($mode) {
      case AC_DESIGN:
        $rep->printDesign($type);
        break;
      case AC_NORMAL:
      default:
        $rep->run($type);
        break;
    }
  }

  function &loadObject($type, $name)
  {
    $mgr =& Amber::getObjectManager();
    $result =& $mgr->loadObject($type, $name);

    return $result;
  }

  /**
   * @static
   * @access public
   * @param mixed
   * @param bool
   * @return If set to true the output will be returned as string, otherwise it will be echoed
   */
  function dump($var, $ret = false)
  {
    $v = $var;

    if ($ret) {
      return Amber::_dumpArray($v);
    } else {
      echo Amber::_dumpArray($v);
    }
  }

  /**
   * @static
   * @access protected
   * @param mixed
   */
  function _dumpScalar($var)
  {
    return '<div align="center"><pre style="text-align: left; width: 80%; border: solid 1px #ff0000; font-size: 9pt; color: #000000; background-color: #ffffff; padding: 5px; z-index: 99999; position: relative;">' . htmlentities(print_r($var, 1)) . '</pre></div>';
  }

  /**
   * @static
   * @access protected
   * @param mixed
   */
  function _dumpArray(&$var)
  {
    static $level = 0;

    $level++;
    if ($level > 5) {
      $level--;
      return '<font color="red" size="1">' . htmlspecialchars('<...>') . '</font>';
    }

    if (is_object($var)) {
      $v = (array)$var;
    } else {
      $v = $var;
    }

    if (is_array($v)) {
      $result = '<table border="1" cellpadding="1" cellspacing="0" bgcolor="white">';
      if (!count($v)) {
        $result .= '<tr><td><font color="green" size="1">Array()</font></td></tr>';
      }
      while (list($key, $val) = each($v)) {
        $result .= '<tr><td><font size="1" color="blue">' . htmlspecialchars($key) . '</font></td><td>';
        if (is_array($val) || is_object($val) || is_resource($val)) {
          $result .= Amber::_dumpArray($val);
        } else {
          if (empty($val)) {
            $result .= '<font color="lightgrey" size="1">' . htmlspecialchars('<Empty>') . '</font><br />';
          } else {
            $result .= '<font color="green" size="1">' . nl2br(htmlspecialchars($val)) . '</font><br />';
          }
        }
        $result .= "</td></tr>\n";
      }
      $result .= '</table>';
    } else {
      $result = Amber::_dumpScalar($v);
    }
    $level--;

    return $result;
  }

  /**
   * @static
   * @access public
   * @param string
   * @param string
   * @param bool If set to true the output will be returned as string, otherwise it will be echoed
   */
  function showError($title, $text, $ret = false)
  {
    $id = 'AmberError' . mt_rand();
    $btId = 'AmberBacktrace' . mt_rand();

    $out = '<div id="' . $id . '" style="margin: 20px; border: solid 2px #ff0000; background-color: yellow; padding: 20px; z-index: 99999; position: relative; margin-top: 10px;">';
    $out .= '<p align="center"><b>' . $title . '</b></p>';
    $out .= '<p align="center">' . $text .'</p>';
    $out .= '<p align="center"><input type="button" value="Backtrace" onclick="document.getElementById(\'' . $btId . '\').style.display = \'\';" style="width: 80px;" />';
    $out .= '&nbsp;<input type="button" value="Close" onclick="document.getElementById(\'' . $id . '\').style.display = \'none\';" style="width: 80px;" /></p>';
    $out .= '<p />';
    if (function_exists('debug_backtrace')) {
      $out .= '<div id="' . $btId . '" align="center" style="display:none;">' . Amber::dump(next(debug_backtrace()), true) . '</div>';
    }
    $out .= '</div>';

    if ($ret == true) {
      return $out;
    } else {
      echo $out;
    }
  }
}

?>