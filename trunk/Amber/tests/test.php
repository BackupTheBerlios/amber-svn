<?php
/**
*
* Run all tests
*
* @package Amber
* @subpackage Tests
*
*/

foreach (glob('test?*.php') as $filename) {
  echo '<h2>' . htmlentities($filename) . '</h2>';
  include_once $filename;
  echo '<hr>';
}

?>