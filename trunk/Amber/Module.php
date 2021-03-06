<?php

/**
 *
 * @package Amber
 * @subpackage ReportEngine
 *
 */

class Module
{
  var $name;
  var $code;
  

  function initialize(&$obj)
  {
    $this->name = $obj->name;
    $this->code = $obj->code;
  }

  function run()
  {
    Amber::evaluate('module "' . $this->name . '"', $this->code);
  }
}

?>