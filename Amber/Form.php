<?php

/**
 *
 * @package PHPReport
 * @subpackage ReportEngine
 *
 */

require_once 'misc.php';
require_once 'AmberConfig.php';
require_once 'AmberObject.php';
require_once 'AmberFormSection.php';

/**
 *
 * @package PHPReport
 * @subpackage ReportEngine
 *
 */
class Form extends AmberObject
{
  var $Name;

  var $Filter;

  var $_exporter;
  var $_Code;
  var $_ClassName;

   /**
   * @access public
   */
  function Form()
  {
    parent::AmberObject();
  }

  function initialize(&$data)
  {
    $this->Name = $data['name'];

    $res =& XMLLoader::_makeXMLTree($data['design']);
    $xml = $res['form'];

    // TODO
    $classLoaded = false;
    $className = $data['class'];

    if ((isset($className)) && (!empty($className)) && (!class_exists($className))) {
      //eval($data['code']); // code in database is currently being stored without php tags! fix this!
      eval(' ?' . '>' . $data['code'] . '<' . '?php ');
      if (class_exists($className)) {
        $this->_Code =& new $className;
        $classLoaded = true;
      } else {
        Amber::showError('Warning', 'Cannot instantiate undefined class "' . $className . '"');
      }
    }
    if (!$classLoaded) {
      $this->_Code =& new AmberForm_UserFunctions();
    }
    $this->_ClassName = get_class($this->_Code);

    if (isset($xml['RecordSource']) && ($xml['RecordSource'] != '')) {
      $this->RecordSource = $xml['RecordSource'];
    }

    /*
     * Sections
     */
    //$sections = array('ReportHeader', 'PageHeader', 'Detail', 'ReportFooter', 'PageFooter');
    $sections = array('Detail');
    foreach ($sections as $secName) {
      if (isset($xml[$secName])) {
        $this->$secName =& new AmberFormSection($secName);
      } else {
        $this->$secName =& new AmberFormSectionNull($secName);
      }
      $this->$secName->load($this, $xml[$secName]);
    }
  }

  function run($type)
  {
    $this->_installExporter($type);
    $this->_exporter->setDocumentTitle($this->Name);

    $this->_printNormalSection('Detail');
  }

  /**
   * @access private
   * @param string
   */
  function _printNormalSection($sectionName)
  {
    $this->$sectionName->printNormal();
  }
}


?>
