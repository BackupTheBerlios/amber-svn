<?php
/**
 *
 * @package PHPReport
 * @subpackage Exporter
 *
 */
 
 
class HTML
{

  function _out($s)
  {
    if ($this->incache) {
      $this->cache .= $s . "\n";
    } else {
      echo $s;
    }
  }
  
  function setOutBuffer(&$buff, $info)
  {
    //info parameter for testing only -- remove if no longer needed
    $this->cache =& $buff;
    $this->incache = true;
  }
  
  function unsetBuffer()
  {
    unset($this->cache);
    $this->incache = false;
  }    
  
  function &getInstance(&$layout, $reset)
  {
    static $instance = null;
    if (is_null($instance) or $reset) {
      $instance = new HTML();
    }
    return $instance;
  }

  function AddPage()
  {
  }
  
}








?>
