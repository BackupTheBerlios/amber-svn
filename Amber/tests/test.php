<?php
/**
*
* run all tests
*
* @author < >
* @version 
* @module test
*/
  foreach (glob('test*.php') as $filename) {
    include_once $filename;
  }

?>