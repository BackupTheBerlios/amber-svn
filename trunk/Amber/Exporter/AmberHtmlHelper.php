<?php
/**
 *
 * @package Amber
 * @subpackage Exporter
 *
 */
 
 
class AmberHtmlHelper
{
  function _out($s)
  {
    echo $s;
  }

  function bufferStart()
  {
    ob_start();
  }
  
  function bufferEnd()
  {
    return ob_get_clean();
  }      

  function &getInstance(&$layout, $reset)
  {
    static $instance = null;
    if (is_null($instance) or $reset) {
      $instance = new AmberHtmlHelper();
    }
    return $instance;
  }
}

?>