<?php

#require_once 'PHPUnit.php';

require_once 'PHPUnit/PHPUnit.php';
/**
*
* @package Amber
* @subpackage Tests
*
*/
class myTestCase extends PHPUnit_TestCase
{
  function assertEEquals($expected, $given, $message='')
  {
    parent::assertEquals($expected, $given, $message . "  expected:" . $expected . "; given:" . $given . "; ");
  }    
  
  function assertContains($needle, $haystack, $message='')
  {
    parent::assertTrue(strpos($haystack, $needle), $message);  
  }

}


?>
