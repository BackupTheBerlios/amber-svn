<?php

define('__AMBER_BASE__', dirname(__FILE__));

require_once __AMBER_BASE__ . '/Report.php';

define('AC_DESIGN', 1);
define('AC_NORMAL', 2);

class Amber
{
  var $_config;

  /**
   * @return Amber reference to singleton
   */
  function &getInstance($config = null)
  {
    static $instance = null;

    if (is_null($instance)) {
      $instance = new Amber();
      $instance->_config = $config;
    }
    
    return $instance;
  }

  function OpenReport($reportName, $mode = AC_NORMAL, $filter = '', $type = 'html', $noMargin = false)
  {
    $rep =& $this->Loadreport($reportName);

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
    echo '<div align="center"><pre style="text-align: left; width: 80%; border: solid 1px #ff0000; font-size: 9pt; color: #000000; background-color: #ffffff; padding: 5px;">' . htmlentities(print_r($var, 1)) . '</pre></div>';
  }
}

?>
