<?php

//error_reporting(E_ALL | E_NOTICE);
ini_set('max_execution_time', '600');
ini_set('include_path', ini_get('include_path') . ':' . dirname(__FILE__). '/../lib/');
ini_set('include_path', ini_get('include_path') . ':' . dirname(__FILE__). '/../Amber/');

require_once 'Amber.php';

if (isset($_GET['rep'])) {
  $repName = $_GET['rep'];
} else {
  $repName = 'GLS';
}
if (isset($_GET['export'])) {
  $type = $_GET['export'];
} else {
  $type = 'html';            //aktuelle Werte vgl. Exporter.php und die dazugehörigen includes.
}
if (isset($_GET['mode'])) {
  $mode = $_GET['mode'];     // 'design' or 'normal'
}
if ($mode != 'design') {
  $mode = 'normal';
}

$configs = array(
  'intern-dev.sbz.de' => 'config-sbz.xml',
  'josh.intern' => 'config-josh.xml',
  'pock3' => 'config-pock3.xml'
);
$cfg = new Config();
$cfg->fromXML($configs[$_SERVER['SERVER_NAME']]);

setlocale (LC_CTYPE, 'de_DE', 'de_DE@euro');
setlocale (LC_TIME, 'de_DE', 'de_DE@euro'); // needed for date, time
setlocale (LC_MONETARY, 'de_DE', 'de_DE@euro'); // needed for numbers
#setlocale (LC_ALL, 'de_DE', 'de_DE@euro');

include_modules();

#$filter = 'BetreutePsy.NPNr > 2500';
$amber = new Amber($cfg);

if ($mode == 'normal') {
  $amber->OpenReport($repName, AC_NORMAL, $filter, $type);
} else {
  $amber->OpenReport($repName, AC_DESIGN, null, $type);
}


function include_modules()
{
  $modPath = 'modules/';
  foreach (glob($modPath . '*.php') as $filename) {
    include_once $modpath . $filename;
  }
}

?>
