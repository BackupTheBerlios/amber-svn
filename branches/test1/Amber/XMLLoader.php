<?php

/**
 *
 * @package PHPReport
 * @subpackage ReportEngine
 *
 */

 /**
 *
 * @package PHPReport
 * @subpackage ReportEngine
 *
 */
class XMLLoader
{
  var $_cacheEnabled;
  var $_cacheDir = '.';

  /**
   *
   * @access public
   * @param bool
   * @return string
   *
   */
  function XMLLoader($cacheEnabled = false, $cacheDir = '')
  {
    $this->_cacheEnabled = false;

    if (is_bool($cacheEnabled)) {
      $this->_cacheEnabled = $cacheEnabled;
      if ($cacheEnabled == true) {
        $this->_cacheDir = $cacheDir;
      }
    }
  }

  /**
   * Enables caching if $value is true, otherwise disables it if $value is false.
   * Caching is disabled by default.
   *
   * @see getCacheEnabled(), setCacheDir(), getCacheDir()
   *
   * @access public
   * @param bool
   *
   */
  function setCacheEnabled($value)
  {
    if (is_bool($value)) {
      $this->_cacheEnabled = $value;
    }
  }

  /**
   *
   * @see setCacheEnabled(), setCacheDir(), getCacheDir()
   * @return bool True or false depending on whether caching is enabled or disabled.
   *
   */
  function getCacheEnabled()
  {
    return $this->_cacheEnabled;
  }

    /**
   * Sets the directory where files used for caching will be written to.
   *
   * @see getCacheDir(), setCacheEnabled(), getCacheEnabled()
   *
   * @access public
   * @param string name of the directory
   *
   */
  function setCacheDir($dirName)
  {
    if (empty($dirName)) {
      $this->_cacheDir = '.';
    } else {
      $this->_cacheDir = $dirName;
    }
  }

  /**
   *
   * @see setCacheDir(), setCacheEnabled(), getCacheEnabled()
   * @return string Current directory in which files for caching purposes will be stored.
   *
   */
  function getCacheDir()
  {
    return $this->_cacheDir;
  }

  /**
   *
   * @access private
   * @param string name of XML file to load
   * @return array parsed XML data
   *
   */
  function &getArray($fileName)
  {
    if (empty($this->_cacheDir)) { // This should never happen!
      $this->_cacheDir = '.';
    }
    $cacheFileName = $this->_cacheDir . '/' . md5($fileName);
    //$cacheFileName = $this->_cacheDir . '/' . basename($fileName);

    if (!file_exists($fileName)) {
      Amber::showError('Error', 'XML file not found: ' . htmlspecialchars($fileName));
      die();
    }

    if ($this->_cacheEnabled == true) {
      if (filemtime($fileName) > @filemtime($cacheFileName)) {
        $res =& $this->_makeXMLTree(file_get_contents($fileName));
        $fp = @fopen($cacheFileName, 'w');
        if ($fp != false) {
          fwrite($fp, serialize($res));
          fclose($fp);
        }
      } else {
        $res =& unserialize(file_get_contents($cacheFileName));
      }
    } else {
      $res =& $this->_makeXMLTree(file_get_contents($fileName));
    }

    return $res;
  }

  /**
   * @access private
   * @param string XML
   * @return array
   */
  function _makeXMLTree($data)
  {
    $ret = array();

    $parser = xml_parser_create('');
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'iso-8859-1');
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, $data, $values, $tags);
    xml_parser_free($parser);

    $hash_stack = array();
    foreach ($values as $key => $val)
    {
      switch ($val['type'])
      {
        case 'open':
          if ($val['tag'] != 'item') {
            array_push($hash_stack, $val['tag']);
          } else {
            array_push($hash_stack, $val['attributes']['id']);
          }
          break;
        case 'close':
          array_pop($hash_stack);
          break;
        case 'complete':
          array_push($hash_stack, $val['tag']);
          eval("\$ret['" . implode($hash_stack, "']['") . "'] = '" . trim($val['value']) . "';");
          array_pop($hash_stack);
          break;
      }
    }
    return $ret;
  }
}

?>
