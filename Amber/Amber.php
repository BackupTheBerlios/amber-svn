<?php

require_once 'Report.php';

define('AC_DESIGN', 1);
define('AC_NORMAL', 2);

class Amber
{
  var $_config;

  function Amber($config)
  {
    $this->_config = $config;
  }

  function OpenReport($reportName, $mode = AC_NORMAL, $filter = '', $type = 'html')
  {
    $rep =& new Report();
    $rep->setConfig($this->_config);
    $rep->setCacheDir('cache');
    $rep->setCacheEnabled(true);
    $rep->setReportDir('reports');
    //$rep->loadFile($reportName);
    $rep->loadDb($reportName);
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
}

?>