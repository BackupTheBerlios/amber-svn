<?php

/**
*
* @package PHPReport
* @subpackage ReportEngine
*
*/

require_once 'XMLLoader.php';

/**
*
* @package PHPReport
* @subpackage ReportEngine
*
*/
class AmberConfig
{
  var $database;
  var $sys_objects;

  function fromXML($fileName)
  {
    if (!file_exists($fileName)) {
      showError('Error', 'Config file not found: ' . htmlspecialchars($fileName));
      die();
    }

    $loader = new XMLLoader(false);
    $res = $loader->getArray($fileName);

    if (is_array($res)) {
      foreach ($res['config'] as $key => $value) {
            $this->$key = $value;
      }
    }
  }

  function toXML($fileName)
  {

    $properties = array(
      'database' => array('username', 'password', 'host', 'driver', 'dbname'),
      'sys_objects' => array('medium')
    );

    $fp = fopen($fileName, 'w');
    fwrite($fp, '<?xml version="1.0" encoding="iso-8859-1"?>' . "\n");
    fwrite($fp, "<config>\n");
    writeArray($fp, $properties);
    fwrite($fp, "</config>\n");
    fclose($fp);
  }

  function writeArray($filehandle, $confArray)
  {
    static $indent = '';

    $indent .= '  ';
    foreach ($confArray as $key => $prop) {
      if (is_array($prop)) {
        fwrite($filehandle, $indent . "<$key>\n");
        writeArray($filehandle, $prop);
        fwrite($filehandle, $indent . "</$key>\n");
      } else {
        $value = htmlentities($this->$prop);
        fwrite($filehandle, $indent. "<$prop>" . $value . "</$prop>\n");
      }
    }
    $indent = substr($indent, 0, count($indent) - 3);
  }
}

/**
*
* @package PHPReport
* @subpackage ReportEngine
*
*/
class AmberConfigNull extends AmberConfig
{
  var $database = array();
  var $sys_objects = array();
}

?>
