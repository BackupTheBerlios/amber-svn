<?php

require_once 'Report.php';

define('AC_DESIGN', 1);
define('AC_NORMAL', 2);
define('__AMBER_BASE__', dirname(__FILE__));

class Amber
{
  var $_config;

  /**
   * @return Amber reference to singleton
   */
  function &getInstance($config=null) {
    static $instance = null;

    if (is_null($instance)) {
      $instance = new Amber();
      $instance->_config = $config;
    }
    return $instance;
  }

  function OpenReport($reportName, $mode = AC_NORMAL, $filter = '', $type = 'html')
  {
    $rep =& $this->Loadreport($reportName);
    $rep->Filter = $filter;

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
    if ($this->_config->medium == 'db') {
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
}

?>
