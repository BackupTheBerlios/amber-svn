<?php
/**
*
* Run all tests
*
* @package Amber
* @subpackage Tests
*
*/

ini_set('include_path', dirname(__FILE__). '/../../lib/' . ':' . ini_get('include_path'));


require_once 'PHPUnit/GUI/HTML.php';

$gui =& new PHPUnit_GUI_HTML();

foreach (glob('test?*.php') as $filename) {
  include_once $filename;                    // each file creates a test suit and adds it to $suites[]
}
include_once("reportTests.php");

$gui->addSuites($suites);
$gui->show();

?>
