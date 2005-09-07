<?php

//error_reporting(E_ALL | E_NOTICE);
ini_set('max_execution_time', '600');
ini_set('memory_limit', '32M');

require_once '../Amber/Amber.php';
require_once '../Amber/AmberConfig.php';

if (isset($_GET['rep'])) {
  $repName = $_GET['rep'];
} else {
  $repName = 'GLS';
}
if (isset($_GET['export'])) {
  $type = $_GET['export'];
} else {
  $type = 'html';  // for a list of possible values see ExporterFactory.php and corresponding include files
}

$mode = 'normal';
if (isset($_GET['mode'])) {
  $mode = $_GET['mode'];  // 'design' or 'normal'
}

$cfgFileName = '../Amber/conf/localconf.xml';
if (!file_exists($cfgFileName)) {
  Amber::showError('Error: localconf.xml does not exist', 'Amber needs to be configured before you can use it. <br>Use the <a href="../Amber/install/index.php" target="_blank">install tool</a> to set up the database connection.');
  die();
}


$cfg = new AmberConfig;
$cfg->fromXML($cfgFileName);

setlocale (LC_CTYPE, 'de_DE', 'de_DE@euro');
setlocale (LC_TIME, 'de_DE', 'de_DE@euro'); // needed for date, time
setlocale (LC_MONETARY, 'de_DE', 'de_DE@euro'); // needed for numbers
//setlocale (LC_ALL, 'de_DE', 'de_DE@euro');

if (isset($_GET['filter'])) {
  $filter = $_GET['filter'];
}
$amber =& Amber::getInstance($cfg);

if ($mode == 'normal') {
  $amber->OpenReport($repName, AC_NORMAL, $filter, $type);
} else {
  $amber->OpenReport($repName, AC_DESIGN, $filter, $type);
}

?>
