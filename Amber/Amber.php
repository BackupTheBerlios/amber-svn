<?php

define('__AMBER_BASE__', dirname(__FILE__));

require_once __AMBER_BASE__ . '/Report.php';
require_once 'adodb/adodb.inc.php';

define('AC_DESIGN', 1);
define('AC_NORMAL', 2);

class Amber
{
  var $_config;

  function init()
  {
    $db = Amber::currentDb();
    $m = new ModuleLoader();
    if ($this->_config->sys_objects['medium'] == 'db') {
      $m->loadFromDb($db);
    } else {
      $m->loadFromFile('modules/');
    }
  }

  /**
   * @return Amber reference to singleton
   */
  function &getInstance($config = null)
  {
    static $instance = null;

    if (is_null($instance)) {
      $instance = new Amber();
      $instance->_config = $config;
      $instance->init();
    }

    return $instance;
  }

  function &currentDb()
  {
    $amber =& Amber::getInstance();

    if (!isset($amber->_db)) {
      $dbCfg =& $amber->_config->database;
      $db =& ADONewConnection($dbCfg['driver']);
      $conResult = @$db->PConnect($dbCfg['host'], $dbCfg['username'], $dbCfg['pwd'], $dbCfg['dbname']);
      $db->SetFetchMode(ADODB_FETCH_ASSOC);
      if ($conResult == false) {
        Amber::showError('Database Error '  . $db->ErrorNo(), $db->ErrorMsg());
        die();
      }
      $amber->_db =& $db;
    }

    return $amber->_db;
  }


  function OpenReport($reportName, $mode = AC_NORMAL, $filter = '', $type = 'html', $noMargin = false)
  {
    $rep =& $this->LoadReport($reportName);

    $rep->Filter = $filter;
    if ($noMargin == true) {
      $rep->resetMargin();
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

  function &LoadReport($reportName)
  {
    $rep =& new Report();
    $rep->setConfig($this->_config);
    if ($this->_config->sys_objects['medium'] == 'db') {
      $rep->setLoader('db');
    } else {
      $rep->setReportDir('reports');
      //$rep->setCacheDir('cache');
      //$rep->setCacheEnabled(true);
      $rep->setLoader('file');
    }
    $rep->load($reportName);
    return $rep;
  }

  function dump($var)
  {
    echo '<div align="center"><pre style="text-align: left; width: 80%; border: solid 1px #ff0000; font-size: 9pt; color: #000000; background-color: #ffffff; padding: 5px; z-index: 99999; position: relative;">' . htmlentities(print_r($var, 1)) . '</pre></div>';
  }

  function showError($title, $text)
  {
    $id = 'Error' . mt_rand();

    echo '<div id="' . $id . '" style="border: solid 3px #ff0000; background-color: #eeeeee; padding: 5px; z-index: 99999; position: relative; margin-top: 10px;">';
    echo '<p align="center"><b>' . $title . '</b></p>';
    echo '<p align="center">' . $text .'</p>';
    echo '<p align="center"><input type="button" value="Ok" onclick="document.getElementById(\'' . $id . '\').style.display = \'none\';" style="width: 80px;" /></p>';
    echo '<p />';
    if (function_exists('debug_backtrace')) {
      Amber::dump(next(debug_backtrace()));
    }
    echo '</div>';
  }
}

?>
