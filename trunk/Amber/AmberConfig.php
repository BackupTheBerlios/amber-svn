<?php

/**
*
* @package Amber
* @subpackage ReportEngine
*
*/

require_once 'XMLLoader.php';

/**
*
* @package Amber
* @subpackage ReportEngine
*
*/
class AmberConfig
{
  var $database;
  var $sys_objects;

  /**
   * Read current configuration from disk
   *
   * @access public
   * @param string name of the configuration file
   * @return bool true on success
   */
  function fromXML($fileName)
  {
    if (!file_exists($fileName)) {
      Amber::showError('Error', 'Config file not found: ' . htmlspecialchars($fileName));
      return false;
    }

    $loader = new XMLLoader(false);
    $res = $loader->getArray($fileName);

    if (is_array($res) && is_array($res['config'])) {
      foreach ($res['config'] as $key => $value) {
            $this->$key = $value;
      }
    }
    
    return true;
  }

  /**
   * Write current configuration to disk
   *
   * @access public
   * @param string name of the configuration file
   * @return bool true on success
   */
  function toXML($fileName)
  {
    $properties = array(
      'database', 'sys_objects'
    );

    $fp = @fopen($fileName, 'w');
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

  function get($str)
  {
    $path = split('/', $str);

    if ($path[0] == 'db') {
      $conf =& $this->database;
    } else if ($path[0] == 'sys') {
      $conf =& $this->sys_objects;
    } else {
      Amber::showError('AmberConfig::get()', 'Invalid root: "' . $path[0] . '"');
    }
    unset($path[0]);

    foreach ($path as $p) {
      if (!isset($conf[$p])) {
        //Amber::showError('AmberConfig::get()', 'No such element: "' . $p . '"');
        return '';
      }
      $conf =& $conf[$p];
    }

    return $conf;
  }

  function setUsername($value)
  {
    $this->database['username'] = $value;
  }

  function setPassword($value)
  {
    $this->database['password'] = $value;
  }

  function setHost($value)
  {
    $this->database['host'] = $value;
  }

  function setDriver($value)
  {
    $this->database['driver'] = $value;
  }

  function setDbName($value)
  {
    $this->database['dbname'] = $value;
  }

  /**
   * @param string medium type, supported values: 'db' or 'file'
   * @access public
   */
  function setMedium($value)
  {
    $this->sys_objects['medium'] = $value;
  }

  function setBasePath($value)
  {
    $this->sys_objects['basepath'] = $value;
  }

  function setSysUsername($value)
  {
    $this->sys_objects['database']['username'] = $value;
  }

  function setSysPassword($value)
  {
    $this->sys_objects['database']['password'] = $value;
  }

  function setSysHost($value)
  {
    $this->sys_objects['database']['host'] = $value;
  }

  function setSysDriver($value)
  {
    $this->sys_objects['database']['driver'] = $value;
  }

  function setSysDbName($value)
  {
    $this->sys_objects['database']['dbname'] = $value;
  }

  /**
   * @access private
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
        fwrite($filehandle, $indent . "<$key>" . htmlspecialchars($prop) . "</$key>\n");
      }
    }
    $indent = substr($indent, 0, count($indent) - 3);
  }
}

/**
*
* @package Amber
* @subpackage ReportEngine
*
*/
class AmberConfigNull extends AmberConfig
{
  var $database = array();
  var $sys_objects = array();
}

?>
