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
  var $driver;
  var $host;
  var $database;
  var $username;
  var $password;
  var $medium;

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
}

/**
*
* @package PHPReport
* @subpackage ReportEngine
*
*/
class AmberConfigNull extends AmberConfig
{
  var $driver ='';
  var $host = '';
  var $database = '';
  var $username = '';
  var $password = '';
}

?>
