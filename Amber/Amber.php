<?php

define('__AMBER_BASE__', dirname(__FILE__));

require_once __AMBER_BASE__ . '/Report.php';
require_once 'adodb/adodb.inc.php';
require_once 'ObjectLoader.php';

define('AC_DESIGN', 1);
define('AC_NORMAL', 2);

class Amber
{
  var $_config;
  var $_objectLoader;

  function init()
  {
    $this->loadObject('module', '');
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
    $rep =& $this->loadObject('report', $reportName);
    if ($rep == false) {
      return;
    }

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

  function &loadObject($type, $name)
  {
    if (!isset($this->_objectLoader)) {
      if ($this->_config->getMedium() == 'db') {
        $this->_objectLoader =& new ObjectLoaderDb();
        $this->_objectLoader->setDatabase(Amber::currentDb());
      } else {
        $this->_objectLoader =& new ObjectLoaderFile();
        //$this->_objectLoader->setBasePath('modules/');
        Amber::showError('Error', 'Loading from file not yet done');
        die();
      }
      $this->_objectLoader->setConfig($this->_config);
    }

    $result =& $this->_objectLoader->load($type, $name);
    if ($result == false) {
      Amber::showError('Error', $this->_objectLoader->getLastError());
    }

    return $result;
  }

  function dump($var)
  {
    $v = $var;

    echo Amber::_dumpArray($v);
  }
  
  function _dumpScalar($var)
  {
    return '<div align="center"><pre style="text-align: left; width: 80%; border: solid 1px #ff0000; font-size: 9pt; color: #000000; background-color: #ffffff; padding: 5px; z-index: 99999; position: relative;">' . htmlentities(print_r($var, 1)) . '</pre></div>';
  }

  function _dumpArray(&$var)
  {
    static $level = 0;
    $level++;
    if ($level > 5) {
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
        if (is_array($v[$key]) || is_object($v[$key])) {
          $result .= Amber::_dumpArray($v[$key]);
          $level--;
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

    return $result;
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
      echo '<p align="center">';
      Amber::dump(next(debug_backtrace()));
      echo '</p>';
    }
    echo '</div>';
  }
}

?>
