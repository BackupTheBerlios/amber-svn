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
      Amber::showError('Error', 'Config file not found: ' . htmlspecialchars($fileName));
      die();
    }

    $loader = new XMLLoader(false);
    $res = $loader->getArray($fileName);

    if (is_array($res) && is_array($res['config'])) {
      foreach ($res['config'] as $key => $value) {
            $this->$key = $value;
      }
    }
  }

  function toXML($fileName)
  {
    $properties = array(
      'database', 'sys_objects'
    );

    $fp = fopen($fileName, 'w');
    if (!$fp) {
      return false;
    }
    fwrite($fp, '<?xml version="1.0" encoding="iso-8859-1"?>' . "\n");
    fwrite($fp, "<config>\n");
    $this->writeArray($fp, '', $this);
    fwrite($fp, "</config>\n");
    fclose($fp);

    return true;
  }

  function getUsername()
  {
    return $this->database['username'];
  }

  function setUsername($value)
  {
    $this->database['username'] = $value;
  }

  function getPassword()
  {
    return $this->database['password'];
  }

  function setPassword($value)
  {
    $this->database['password'] = $value;
  }

  function getHost()
  {
    return $this->database['host'];
  }

  function setHost($value)
  {
    $this->database['host'] = $value;
  }

  function getDriver()
  {
    return $this->database['driver'];
  }

  function setDriver($value)
  {
    $this->database['driver'] = $value;
  }

  function getDbName()
  {
    return $this->database['dbname'];
  }

  function setDbName($value)
  {
    $this->database['dbname'] = $value;
  }

  function getMedium()
  {
    return $this->sys_objects['medium'];
  }

  function setMedium($value)
  {
    $this->sys_objects['medium'] = $value;
  }

  function getBasePath()
  {
    return $this->sys_objects['basepath'];
  }

  function setBasePath($value)
  {
    $this->sys_objects['basepath'] = $value;
  }

  /**
   * @private
   */
  function writeArray($filehandle, $name, $confArray)
  {
    static $indent = '';
    static $stack = array();

    $indent .= '  ';
    foreach ($confArray as $key => $prop) {
      if (is_array($prop)) {
        fwrite($filehandle, $indent . "<$key>\n");
        $this->writeArray($filehandle, $key, $prop);
        fwrite($filehandle, $indent . "</$key>\n");
      } else {
        htmlentities($this->$prop);
        fwrite($filehandle, $indent . "<$key>" . $prop . "</$key>\n");
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
