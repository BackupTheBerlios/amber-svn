<?php

/**
 *
 * @package PHPReport
 * @subpackage ReportEngine
 *
 */

require_once 'misc.php';
require_once 'Section.php';
require_once 'Config.php';
require_once 'XMLLoader.php';
require_once 'SimpleSQLParser.php';
require_once 'Exporter/ExporterFactory.php';
require_once 'Controls/ControlFactory.php';
require_once 'phpReport_UserFunctions.php';
require_once 'basic.php';

require_once 'adodb/adodb.inc.php';

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
  var $_xml; // XML tree
  var $_groupFields; // contains field names by which to group
  var $_exporter;

  var $_Code;     // user defined call back methods
  var $_ClassName;


  var $_xmlLoader; // holds XMLLoader object
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
    $this->_globalConfig = new ConfigNull();
    $this->_xmlLoader = new XMLLoader();
  }

  /**
   *
   * @access public
   * @param Config
   *
   */
  function setConfig($cfgObj)
  {
    if (is_object($cfgObj) && is_a($cfgObj, 'Config')) {
      $this->_globalConfig = $cfgObj;
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
      $this->_xmlLoader->setCacheEnabled($value);
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
    return $this->_xmlLoader->getCacheEnabled();
  }

  /**
   * Sets the directory where files used for caching will be written to.
   *
   * Specifying an invalid (non-existant or not writable directory) will result in
   * an error message to be shown AND caching being turned off.
   *
   * @see getCacheDir(), setCacheEnabled(), getCacheEnabled()
   *
   * @access public
   * @param string name of the directory
   *
   */
  function setCacheDir($dirName)
  {
    if (!is_dir($dirName)) {
      showError('Error', 'setCacheDir(): Directory name given is not a directory or does not exist: ' . htmlentities($dirName));
      $this->_xmlLoader->setCacheEnabled(false);
    } elseif (!is_writable($dirName)) {
      showError('Error', 'setCacheDir(): Cannot write to cache directory: ' . htmlentities($dirName));
      $this->_xmlLoader->setCacheEnabled(false);
    } else{
      $this->_xmlLoader->setCacheDir($dirName);
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
    return $this->_xmlLoader->getCacheDir();
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
      showError('Error', 'setReportDir(): Directory name given is not a directory or does not exist: ' . htmlentities($dirName));
    } elseif (!is_readable($dirName)) {
      showError('Error', 'setReportDir(): Cannot write to cache directory: ' . htmlentities($dirName));
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
   * @see setReportDir()
   * @return string
   *
   */
  function getReportDir()
  {
    return $this->_reportDir;
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

  function &currentDb()
  {
    if (!isset($this->_db)) {
      $cfg =& $this->_globalConfig;
      $db =& ADONewConnection($cfg->driver);
      $conResult = @$db->PConnect($cfg->host, $cfg->user, $cfg->pwd, $cfg->database);
      $db->SetFetchMode(ADODB_FETCH_ASSOC);
      if ($conResult == false) {
        showError('Database Error '  . $db->ErrorNo(), $db->ErrorMsg());
        die();
      }
      $this->_db =& $db;
    }

    return $this->_db;
  }

  /**
   *
   * @access public
   * @param string file name of XML report config file
   *
   */
  function loadDb($reportName)
  {
    $db = $this->currentDb();
    $dict = NewDataDictionary($db);
    $sysTable = 'amber_sys_objects';
    $sql = 'Select * from ' . $dict->TableName($sysTable) . ' where name=' . $db->qstr($reportName);

    $rs = $db->SelectLimit($sql, 1);
    if (!$rs) {
      showError('Database Error ' . $db->ErrorNo(), $db->ErrorMsg());
      die();
    }
    $arr = $rs->FetchRow();
    if (!$arr) {
      showError('Error ', 'Report not found in database');
      die();
    }

    if (isset($arr['name'])) {
      $this->Name = $arr['name'];
    }

    // FIXME: That's not a nice solution! Rewrite XMLLoader?
    $res =& XMLLoader::_makeXmlTree($arr['design']);
    $this->_xml = $res['report'];

    $classLoaded = false;
    $className = $arr['class'];
    if ($className) {
      eval($arr['code']);
      if (class_exists($className)) {
        $this->_Code =& new $arr['class'];
        $classLoaded = true;
      } else {
        showError('Error', 'Cannot instatiate undefined class "' . $className . '"');
      }
    }
    if (!$classLoaded) {
      $this->_Code =& new phpReport_UserFunctions();
    }
    $this->_ClassName = get_class($this->_Code);

    // Continue with common initialization
    $this->_afterLoad();
  }

  /**
   *
   * @access public
   * @param string file name of XML report config file
   *
   */
  function loadFile($reportName)
  {
    if (empty($this->_reportDir)) { // This should never happen!
      $this->_reportDir = '.';
    }

    // Load XML
    $res =& $this->_xmlLoader->getArray($this->_reportDir . '/' . $reportName . '.xml');
    $param = $res['report'];
    if (isset($param['Name'])) {
      $this->Name = $param['Name'];
    }

    $res =& $this->_xmlLoader->getArray($this->_reportDir . '/' . $param['FileNameDesign']);
    $this->_xml = $res['report'];

    if (isset($param['FileNameCode']) && isset($param['ClassName'])) {
      $this->_ClassName = $param['ClassName'];
      include_once $this->_reportDir . '/' . $param['FileNameCode'];
      $this->_Code =& new $param['ClassName'];
    } else {
      $this->_Code =& new phpReport_UserFunctions();
    }

    // Continue with common initialization
    $this->_afterLoad();
  }

  function _afterLoad()
  {
    $this->Width = $this->_xml['Width'];
    if (isset($this->_xml['Printer'])) {
      $this->LeftMargin = wennleer($this->_xml['Printer']['LeftMargin'], 720);
      $this->RightMargin = wennleer($this->_xml['Printer']['RightMargin'], 720);
      $this->TopMargin = wennleer($this->_xml['Printer']['TopMargin'], 720);
      $this->BottomMargin = wennleer($this->_xml['Printer']['BottomMargin'], 720);
      $this->Orientation = MSPageOrientation($this->_xml['Printer']['Orientation']);
      MSPageSize($this->_xml['Printer']['PaperSize'], $this->PaperSize, $this->PaperWidth, $this->PaperHeight);
    }

    if (isset($this->_xml['RecordSource']) && ($this->_xml['RecordSource'] != '')) {
      $this->RecordSource = $this->_xml['RecordSource'];
    }

    /*
     * Sections
     */
    $sections = array('ReportHeader', 'PageHeader', 'Detail', 'ReportFooter', 'PageFooter');
    foreach ($sections as $secName) {
      if (isset($this->_xml[$secName])) {
        $this->$secName = new Section($secName);
      } else {
        $this->$secName = new SectionNull($secName);
      }
      $this->$secName->load($this, $this->_xml[$secName]);
    }

    /*
     * Group Sections
     */
    $groupSections = array('GroupHeaders', 'GroupFooters');
    foreach ($groupSections as $groupSecName) {
      if (is_array($this->_xml[$groupSecName])) {
        foreach ($this->_xml[$groupSecName] as $i => $sectionXML) {
          $t =& $this->$groupSecName; // reference to array
          $t[$i] = new GroupSection($groupSecName);
          $t[$i]->load($this, $sectionXML);
        }
      }
    }

    /*
     * Group Levels
     */
    if (is_array($this->_xml['GroupLevels'])) {
      foreach ($this->_xml['GroupLevels'] as $i => $levelXML) {
        $this->GroupLevels[$i] = new GroupLevel();
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
    //dump($this->_exporter->type);
    $this->_exporter->setDocumentTitle($this->Name);

    $this->OnOpen($cancel);
    if ($cancel) {
      return;
    }
    $this->_fetchDataFromDatabase();
    $this->_preamble();
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
    $this->_postamble();
    $this->OnClose();
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

    $this->_preamble();

    $maxLevel = count($this->_groupFields);

    $this->_printDesignSection('ReportHeader');
    $this->_printDesignSection('PageHeader');
    $this->_printDesignGroupHeaders($maxLevel, 0);

    $this->_printDesignSection('Detail');

    $this->_printDesignGroupFooters($maxLevel, 0);
    $this->_printDesignSection('ReportFooter');
    $this->_printDesignSection('PageFooter');
    $this->_exporter->newPage();
    $this->_postamble();
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
    $db = $this->currentDb();
    $this->_data =& $db->GetAll($sql);
    if (empty($this->_data)) {
      if ($db->ErrorNo() != 0) {
        showError('Database Error ' . $db->ErrorNo(), $db->ErrorMsg());
        die();
      }
    }
    $db->Close();
  }

  /**
   * @access private
   */
  function _preamble(){
    if (isset($this->_exporter)) {
      $this->_exporter->getPreamble($this);
    }
  }

  /**
   * @access private
   */
  function _postamble(){
    if (isset($this->_exporter)) {
      $this->_exporter->getPostamble($this);
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
  function _printNormalSection($sectionName) {
    $this->$sectionName->printNormal();
  }

   /**
   * @access private
   * @param string
   */
  function _printDesignSection($sectionName) {
    $this->$sectionName->printDesign();
  }

 /**
   * @access private
   * @param int
   * @param int
   */
  function _printNormalGroupHeaders($maxLevel, $level) {
    for ($i = $level; $i < $maxLevel; $i++) {
      if (isset($this->GroupHeaders[$i])) {
        $this->GroupHeaders[$i]->printNormal();
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
    $this->_exporter = ExporterFactory::create($type, $this);
    if (is_array($this->Controls)) {
      foreach (array_keys($this->Controls) as $ctlName) {
        $this->_exporter->setControlExporter($this->Controls[$ctlName]);
      }
    }
  }
}

?>
