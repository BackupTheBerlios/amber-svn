<?php

/**
 *
 * @package PHPReport
 * @subpackage ReportEngine
 *
 */

require_once 'misc.php';
require_once 'AmberConfig.php';
require_once 'Section.php';
require_once 'ObjectLoader.php';
require_once 'SimpleSQLParser.php';
require_once 'Exporter/ExporterFactory.php';
require_once 'Controls/ControlFactory.php';
require_once 'phpReport_UserFunctions.php';
require_once 'basic.php';

/**
 *
 * @package PHPReport
 * @subpackage ReportEngine
 *
 */
class Report
{
  //////////////////////////////////////////////////////////////////
  // PUBLIC PROPERTIES
  //////////////////////////////////////////////////////////////////

  var $Name;
  var $Width;
  var $RecordSource;
  var $LeftMargin;
  var $RightMargin;
  var $TopMargin;
  var $BottomMargin;
  var $Orientation;
  var $PaperSize;
  var $PaperHeight;
  var $PaperWidth;

  var $HasData;
  var $ReportHeader;
  var $PageHeader;
  var $GroupHeaders;
  var $GroupLevels;
  var $Detail;
  var $GroupFooters;
  var $PageFooter;
  var $ReportFooter;

  var $Filter;

  var $Controls;      //List of all Controls
  var $ControlValues; // ControlValues[Name] is shortcut for Controls[Name]->Value

  var $Cols;

  //////////////////////////////////////////////////////////////////
  // PRIVATE PROPERTIES
  //////////////////////////////////////////////////////////////////

  var $_data; // Holds result of the database query
  var $_groupFields; // contains field names by which to group
  var $_exporter;

  var $_Code;     // user defined call back methods
  var $_ClassName;

  var $_reportDir = '.';
  var $_globalConfig;

  //////////////////////////////////////////////////////////////////
  // PUBLIC METHODS
  //////////////////////////////////////////////////////////////////

  /**
   * @access public
   */
  function Report()
  {
    $this->_globalConfig = new AmberConfigNull();
  }

  /**
   *
   * @access public
   * @param AmberConfig
   *
   */
  function setConfig($cfgObj)
  {
    if (is_object($cfgObj) && is_a($cfgObj, 'AmberConfig')) {
      $this->_globalConfig = $cfgObj;
    } else {
      Amber::showError('Warning - Report::setConfig()', 'Invalid paramater');
    }
  }

  /**
   * Sets the directory that contains the report files
   *
   * Specifying an invalid (non-existant or not readable directory) will result in
   * an error message to be shown
   *
   * @see getReportDir()
   *
   * @access public
   * @param string name of the directory
   *
   */
  function setReportDir($dirName)
  {
    if (!is_dir($dirName)) {
      Amber::showError('Error', 'setReportDir(): Directory name given is not a directory or does not exist: ' . htmlentities($dirName));
    } elseif (!is_readable($dirName)) {
      Amber::showError('Error', 'setReportDir(): Cannot write to cache directory: ' . htmlentities($dirName));
    } else{
      if (empty($dirName)) {
        $this->_reportDir = '';
      } else {
        $this->_reportDir = $dirName;
      }
    }
  }

  /**
   *
   * @access public
   * @see setFilter()
   *
   */
  function setFilter($filter)
  {
    if (is_string($filter)) {
      $this->Filter = $filter;
    }
  }

  /**
   *
   * @access public
   * @param string name of the report to load
   *
   */
  function initialize(&$data)
  {
    $this->Name = $data['name'];

    $res =& XMLLoader::_makeXMLTree($data['design']);
    $xml = $res['report'];

    $classLoaded = false;
    $className = $data['class'];

    if ((isset($className)) && (!class_exists($className))) {
      //eval($data['code']); // code in database is currently being stored without php tags! fix this!
      eval(' ?' . '>' . $data['code'] . '<' . '?php ');
      if (class_exists($className)) {
        $this->_Code =& new $className;
        $classLoaded = true;
      } else {
        Amber::showError('Error', 'Cannot instantiate undefined class "' . $className . '"');
      }
    }
    if (!$classLoaded) {
      $this->_Code =& new phpReport_UserFunctions();
    }
    $this->_ClassName = get_class($this->_Code);

    //
    // Continue with common initialization
    //
    $this->Width = $xml['Width'];
    if (isset($xml['Printer'])) {
      $prt =& $xml['Printer'];
      $this->LeftMargin = empty($prt['LeftMargin']) ? 720 : $prt['LeftMargin'];
      $this->RightMargin = empty($prt['RightMargin']) ? 720 : $prt['RightMargin'];
      $this->TopMargin = empty($prt['RigTopMargin']) ? 720 : $prt['TopMargin'];
      $this->BottomMargin = empty($prt['BottomMargin']) ? 720 : $prt['BottomMargin'];
      $this->Orientation = MSPageOrientation($prt['Orientation']);
      MSPageSize($prt['PaperSize'], $this->PaperSize, $this->PaperWidth, $this->PaperHeight);
    }

    if (isset($xml['RecordSource']) && ($xml['RecordSource'] != '')) {
      $this->RecordSource = $xml['RecordSource'];
    }

    /*
     * Sections
     */
    $sections = array('ReportHeader', 'PageHeader', 'Detail', 'ReportFooter', 'PageFooter');
    foreach ($sections as $secName) {
      if (isset($xml[$secName])) {
        $this->$secName =& new Section($secName);
      } else {
        $this->$secName =& new SectionNull($secName);
      }
      $this->$secName->load($this, $xml[$secName]);
    }

    /*
     * Group Sections
     */
    $groupSections = array('GroupHeaders', 'GroupFooters');
    foreach ($groupSections as $groupSecName) {
      if (is_array($xml[$groupSecName])) {
        foreach ($xml[$groupSecName] as $i => $sectionXML) {
          $t =& $this->$groupSecName; // reference to array
          $t[$i] =& new GroupSection($groupSecName);
          $t[$i]->load($this, $sectionXML);
        }
      }
    }

    /*
     * Group Levels
     */
    if (is_array($xml['GroupLevels'])) {
      foreach ($xml['GroupLevels'] as $i => $levelXML) {
        $this->GroupLevels[$i] =& new GroupLevel();
        $this->GroupLevels[$i]->load($levelXML);
      }
    }

    // Create a list of fields by which to group
    $this->_groupFields = array();
    if (is_array($this->GroupLevels)) {
      foreach ($this->GroupLevels as $group) {
        array_push($this->_groupFields, $group->ControlSource);
      }
    }
  }

  /**
   *
   * @access public
   * @return int current page number
   *
   */
  function Page()
  {
    return $this->_exporter->page();
  }

  /**
   *
   * @access public
   * @param string 'html', 'pdf' (synonyms: '.pdf', 'fpdf') depending on which exporter to use
   *
   */
  function run($type)
  {
    $this->_installExporter($type);
    $this->_exporter->setDocumentTitle($this->Name);

    $this->OnOpen($cancel);
    if ($cancel) {
      return;
    }
    $this->_fetchDataFromDatabase();
    $this->_startReport();
    $this->_setHasData();
    if ($this->HasData == 0) {
      $this->OnNoData($cancel);
      if ($cancel) {
        return;
      }
    }
    $maxLevel = count($this->_groupFields);
    $this->Cols =& $this->_data[0];
    $this->_setControlValues();
    $this->OnFirstFormat();

    $this->_printNormalSection('ReportHeader');
    $this->_printNormalGroupHeaders($maxLevel, 0);

    if (is_null($this->RecordSource)) {   // if no data expected print Detail only once
      $this->_printNormalSection('Detail');
    } else {                              // Loop through all records
      $oldRow =& $this->Cols;
      $keys = array_keys($this->_data);
      foreach ($keys as $rowNumber) {
        $this->Cols =& $this->_data[$rowNumber];
        $level = $this->_getGroupLevel($this->Cols, $oldRow);
        $this->_setControlValues();
        $this->OnFirstFormat();
        $this->_printNormalGroupFooters($maxLevel, $level);
        $this->_printNormalGroupHeaders($maxLevel, $level);
        $this->_resetRunningSum($level, $maxLevel);
        $this->_printNormalSection('Detail');
        $oldRow =& $this->Cols;
      }
    }
    $this->_printNormalGroupFooters($maxLevel, 0);
    $this->_printNormalSection('ReportFooter');
    $this->_exporter->newPage();
    $this->_endReport();
    $this->OnClose();
  }

  function resetMargin()
  {
    $this->LeftMargin = 0;
    $this->RightMargin = 0;
    $this->TopMargin = 0;
    $this->BottomMargin = 0;
  }

  /**
   *
   * Print the report in 'design' mode (of course no modification possible)
   *
   * @access public
   * @param string 'html', 'pdf' (synonyms: '.pdf', 'fpdf') depending on which exporter to use
   *
   */
  function printDesign($type)
  {
    $this->_installExporter($type);
    //dump($this->_exporter->type);
    $this->_exporter->setDocumentTitle($this->Name);
    $this->_exporter->setDesignMode();

    $this->_startReport();

    $maxLevel = count($this->_groupFields);

    $this->_printDesignSection('ReportHeader');
    $this->_printDesignSection('PageHeader');
    $this->_printDesignGroupHeaders($maxLevel, 0);

    $this->_printDesignSection('Detail');

    $this->_printDesignGroupFooters($maxLevel, 0);
    $this->_printDesignSection('ReportFooter');
    $this->_printDesignSection('PageFooter');
    $this->_exporter->newPage();
    $this->_endReport();
  }

  //////////////////////////////////////////////////////////////////
  // PRIVATE METHODS
  //////////////////////////////////////////////////////////////////

  /**
   *
   * @access private
   *
   */
  function _makeSqlFilter($sql, $filter) {
    $parser = new SimpleSelectParser($sql);
    $sqlParts = $parser->parse();
    if ($sqlParts == false) {
      Amber::showError('Error', __CLASS__ . '::' . __FUNCTION__ . '(): Not a select query');
      die();
    }

    // Apply filter if necessary
    if (!empty($filter)) {
      if ($sqlParts['where'] == '') {
        $sqlParts['where'] = $filter;
      } else {
        $sqlParts['where'] = '(' . $sqlParts['where'] .') AND (' . $filter . ')';
      }
    }

    // Rebuild sql statement
    $sql = '';
    foreach ($sqlParts as $name => $value) {
      if (!empty($value)) {
        $sql .= $name . ' ' . $value . ' ';
      }
    }

    return trim($sql);
  }


  /**
   *
   * @access private
   *
   */
  function _fetchDataFromDatabase()
  {
    if (empty($this->RecordSource)) {
      return;
    }
    $sql = $this->_makeSqlFilter($this->RecordSource, $this->Filter);

    // Execute query
    $db = Amber::currentDb();
    $this->_data =& $db->GetAll($sql);
    if (empty($this->_data)) {
      if ($db->ErrorNo() != 0) {
        Amber::showError('Database Error ' . $db->ErrorNo(), $db->ErrorMsg());
        die();
      }
    }
    $db->Close();
  }

  /**
   * @access private
   */
  function _startReport(){
    if (isset($this->_exporter)) {
      $this->_exporter->startReport($this);
    }
  }

  /**
   * @access private
   */
  function _endReport(){
    if (isset($this->_exporter)) {
      $this->_exporter->endReport($this);
    }
  }

  /**
   * @access private
   */
  function _setHasData(){
    if (is_null($this->RecordSource)) {
      $this->HasData = -1;
    } elseif (!is_array($this->_data)) {
      $this->HasData = 0;
    } elseif (count($this->_data) > 0) {
      $this->HasData = 1;
    } else {
      $this->HasData = 0;
    }
  }

  /**
   * @access private
   */
  function _setControlValues() {
    // set values of control bound to columns
    if (is_array($this->Controls)) {
      $keys = array_keys($this->Controls);
      foreach ($keys as $index) {
        $ctrl  =& $this->Controls[$index];
        if (!isset($ctrl->ControlSource)) {
          # $ctrl->Value = '#NoValue#';
        } else if ($ctrl->ControlSource == '') {
          $ctrl->Value = Null;
        } else if ($ctrl->ControlSource[0] == '=') {
          $ctrl->Value = '#exp';
        } else {
          $ctrl->Value = $this->Cols[$ctrl->ControlSource];
        }
      }
    }
  }

  /**
   * @access private
   * @param int
   */
  function OnOpen(&$cancel) { // Design is loaded, Data will come now
    $cancel = false;
    $this->_Code->Report_Open($this, $cancel);
  }

  /**
   * @access private
   */
  function OnFirstFormat() { // Datarow has changed
    $this->_Code->Report_FirstFormat($this);
  }

  /**
   * @access private
   */
  function OnNoData(&$cancel) { //Data expected but none given
    $cancel = false;
    $this->_Code->Report_NoData($this, $cancel);
  }

  /**
   * @access private
   */
  function OnClose() { // Datarow has changed
    $this->_Code->Report_Close($this);
  }

  /**
   * @access private
   */
  function OnPage() { // NewPage is executed; gets only called from class pdage...
    $this->_Code->Report_Page($this);
  }

  /**
   * @access private
   * @param string
   */
  function _printNormalSection($sectionName)
  {
    //Amber::dumpArray($this);
    $this->$sectionName->printNormal();
    $this->_prepareDuplicates($this->$sectionName);
  }

   /**
   * @access private
   * @param string
   */
  function _printDesignSection($sectionName)
  {
    $this->$sectionName->printDesign();
  }

 /**
   * @access private
   * @param int
   * @param int
   */
  function _printNormalGroupHeaders($maxLevel, $level)
  {
    for ($i = $level; $i < $maxLevel; $i++) {
      if (isset($this->GroupHeaders[$i])) {
        $this->GroupHeaders[$i]->printNormal();
        $this->_prepareDuplicates($this->GroupHeaders[$i]);
      }
    }
  }

/**
   * @access private
   * @param int
   * @param int
   */
  function _printDesignGroupHeaders($maxLevel, $level) {
    for ($i = $level; $i < $maxLevel; $i++) {
      if (isset($this->GroupHeaders[$i])) {
        $this->GroupHeaders[$i]->printDesign($this->GroupLevels[$i]);
      }
    }
  }

  /**
   * @access private
   * @param int
   * @param int
   */
  function _printNormalGroupFooters($maxLevel, $level) {
    for ($i = $maxLevel-1; $i >= $level; $i--) {
      if (isset($this->GroupFooters[$i])) {
        $this->GroupFooters[$i]->printNormal();
        $this->_prepareDuplicates($this->GroupFooters[$i]);
      }
    }
  }

  /**
   * @access private
   * @param int
   * @param int
   */
  function _printDesignGroupFooters($maxLevel, $level) {
    for ($i = $maxLevel-1; $i >= $level; $i--) {
      if (isset($this->GroupFooters[$i])) {
        $this->GroupFooters[$i]->printDesign($this->GroupLevels[$i]);
      }
    }
  }

  /**
   * @access private
   * @param bool printed: section was printed
   * @param obj  section printed
   */
  function _prepareDuplicates(&$section)
  {
    if ($section->printed) {
      //nullify all _oldValue of report
      if (is_array($this->Controls)) {
        $keys = array_keys($this->Controls);
        foreach ($keys as $index) {
          $ctrl  =& $this->Controls[$index];
          $ctrl->_OldValue = null;
        }
      }    
      //set _oldValue of section
      if (is_array($section->Controls)) {
        $keys = array_keys($section->Controls);
        foreach ($keys as $index) {
          $ctrl  =& $section->Controls[$index];
          $ctrl->_OldValue = $ctrl->Value;
        }
      }    
    }
  }
  
  /**
   * @access private
   * @param int
   * @param int
   */
  function _resetRunningSum($level, $maxLevel)
  {
    $s = '';
    $resetLevel = $maxLevel;
    for ($i = $maxLevel-1; $i >= $level; $i--) {
      if (isset($this->GroupHeaders[$i]) or (isset($this->GroupFooters[$i]))) {
        $s .= $i;
        $resetLevel = $i;
      }
    }

    for ($i = $resetLevel + 1; $i < $maxLevel; $i++) {
      if (isset($this->GroupHeaders[$i])) {
        $this->GroupHeaders[$i]->_resetRunningSum();
      }
      if (isset($this->GroupFooters[$i])) {
        $this->GroupFooters[$i]->_resetRunningSum();
      }
    }

    if (($resetLevel < $maxLevel) and (isset($this->Detail))) {
      $this->Detail->_resetRunningSum();
    }
  }

  /**
   * @access private
   * @param array
   * @param array
   * @return int
   */
  function _getGroupLevel(&$row, &$oldRow)
  {
    foreach ($this->_groupFields as $idx => $fieldName) {
      if ($row[$fieldName] != $oldRow[$fieldName]) {
        #echo "new level: $idx - $row[$fieldName] - $oldRow[$fieldName] <br>";
        return $idx;
      }
    }

    return count($this->_groupFields);
  }

  /**
   * @access private
   * @param string
   */
  function _installExporter($type)
  {
    $this->_exporter =& ExporterFactory::create($type, $this);
    if (is_array($this->Controls)) {
      $ctlNames = array_keys($this->Controls);
      foreach ($ctlNames as $ctlName) {
        $this->_exporter->setControlExporter($this->Controls[$ctlName]);
      }
    }
  }
}

?>
