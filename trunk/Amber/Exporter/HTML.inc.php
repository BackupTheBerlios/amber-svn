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
