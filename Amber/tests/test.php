<?php
/**
*
* Run all tests
*
* @module test
*/

foreach (glob('test?*.php') as $filename) {
  echo '<h2>' . htmlentities($filename) . '</h2>';
  include_once $filename;
  echo '<hr>';
}

?>