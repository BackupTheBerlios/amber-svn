<?php

/**
 *
 * @package Amber
 * @subpackage ReportEngine
 *
 */

require_once 'AmberConfig.php';
require_once 'AmberObject.php';
require_once 'ObjectLoader.php';
require_once 'ObjectHandler.php';
require_once 'SimpleSQLParser.php';
require_once 'Section.php';
require_once 'Exporter/ExporterFactory.php';
require_once 'Controls/ControlFactory.php';
require_once 'AmberReport_UserFunctions.php';
require_once 'basic.php';
require_once 'misc.php';

/**
 *
 * @package Amber
 * @subpackage ReportEngine
 *
 */
class Report extends AmberObject
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

  var $ReportHeader;
  var $PageHeader;
  var $GroupHeaders;
  var $GroupLevels;
  var $Detail;
  var $GroupFooters;
  var $PageFooter;
  var $ReportFooter;

  var $Filter;
  var $Where; // where-clause (applied before filter)

  var $Controls;      //List of all Controls
  var $ControlValues; // ControlValues[Name] is shortcut for Controls[Name]->Value

  var $Cols;

  //////////////////////////////////////////////////////////////////
  // PRIVATE PROPERTIES
  //////////////////////////////////////////////////////////////////

  var $_data; // Holds result of the database query
  var $_groupFields; // contains field names by which to group
  var $_exporter;
  var $layout;

  var $_Code;     // user defined call back methods
  var $_ClassName;

  var $_globalConfig;

  var $asSubReport;
  var $ignoreOnPrint;
  var $ignoreKeepTogether;
  var $noAutoPage;
  var $noMargins;
  var $noHeadFoot;
  var $printHeadFootAsNormalSection;

  //////////////////////////////////////////////////////////////////
  // PUBLIC METHODS
  //////////////////////////////////////////////////////////////////

   /**
   * A filter may be applied <b>after</b> the query has been executed.
   *
   * You may specify a filter similar to {@link setWhere}. In contrast to {@link setWhere}
   * this <b>does not</b> affect the RecordSource directly but is applied separately <b>after</b>
   * the initial data retrieval.
   *
   * Example:
   * <pre>
   *    $report->setFilter('name = Bob');
   * </pre>
   *
   * @access public
   * @param string
   * @see setWhere()
   *
   */
  function setFilter($filter)
  {
    if (is_string($filter)) {
      $this->Filter = $filter;
    }
  }

  /**
   * Extends the WHERE clause of the query when data for the report is being fetched.
   *
   * Parameter $where will be appended (AND) to an already existing WHERE clause of RecordSource
   * if necessary.
   *
   * Example:
   * <pre>
   *    $report->setWhere('name = "Alice" OR name = "Bob"');
   * </pre>
   *
   * @access public
   * @param string
   * @see setFilter()
   *
   */
  function setWhere($where)
  {
    if (is_string($where)) {
      $this->Where = $where;
    }
  }

  /**
   *
   * @access public
   * @param AmberObjectRaw
   *
   */
  function initialize(&$data)
  {
    $this->Name = $data->name;
    $this->hReport = objectHandler::getHandle($this);
    $res =& XMLLoader::_makeXMLTree($data->design);
    $xml = $res['report'];

    $classLoaded = false;
    $className = $data->class;

    if ((isset($className)) && (!empty($className)) && (!class_exists($className))) {
      //eval($data->code); // code in database is currently being stored without php tags! fix this!
      eval(' ?' . '>' . $data->code . '<' . '?php ');
    }
    if (class_exists($className)) {
      $this->_Code =& new $className;
      $this->_Code->setReport($this);   // this breaks in PHP4; switch to PHP5!
      $classLoaded = true;
    } else {
      Amber::showError('Error', 'Cannot instantiate undefined class "' . $className . '"');
    }
    if (!$classLoaded) {
      $this->_Code =& new AmberReport_UserFunctions();
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
        $this->$secName->load($this, $xml[$secName]);
      } else {
        $this->$secName =& new SectionNull($secName);
        $this->$secName->load($this, array());
      }
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
     * in labels: change property Parent from name (string) to reference (&obj)
     */

    //FIXME: this should be moved to Control-Class (with a few parameter-changes.....)

    if (is_array($this->Controls)) {
      foreach ($this->Controls as $i => $ctrl) {
        if (isset($ctrl->Parent)) {
          if (!isset($this->Controls[$ctrl->Parent])) {
            Amber::showError('Internal Error', 'Referenced parent control with name="' . $ctrl->Parent . '" does not exist.');
            die();
          }
          $this->Controls[$i]->Parent =& $this->Controls[$ctrl->Parent];
          $this->Controls[$i]->Properties['Parent'] =& $this->Controls[$ctrl->Parent];
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
   * @param string 'html', 'pdf' (synonyms: '.pdf', 'fpdf') depending on which exporter to use
   *
   */
  function run($type)
  {
    $this->_installExporter($type);
    $this->_setDocumentTitle($this->Name);

    $this->_startReport();

    $this->OnOpen($cancel);
    if ($cancel) {
      $this->_endReport();
      return;
    }
    $this->_fetchDataFromDatabase();
    if ($this->_HasData() == 0) {
      $this->OnNoData($cancel);
      if ($cancel) {
        $this->_endReport();
        return;
      }
    }
    
    $maxLevel = count($this->_groupFields);
    $isFirstRecord = true;
    
    if (is_null($this->RecordSource)) {   // if no data expected print Detail only once
      $this->EvaluateExpressions();  
      $this->_printNormalSection($this->Detail);
    }
    else  // Loop through all records
    {                              
      $keys = array_keys($this->_data);

      foreach ($keys as $rowNumber) {
        $this->Cols =& $this->_data[$rowNumber];

        // Load Data
        $this->onLoadData($Cancel);
        if (!$Cancel) {
          if ($isFirstRecord) {
            $level = 0;
          } else {
            $level = $this->_getGroupLevel($this->Cols, $oldRow);
            $this->_printNormalGroupFooters($maxLevel, $level);
          }

          // Evaluate Expressions
          $this->_setControlValues($this->Cols);
          $this->_resetRunningSum($maxLevel, $level);
          $this->EvaluateExpressions();
          $this->_runningSum($maxLevel, $level);

          // Next Record
          $this->OnNextRecord();
          if ($isFirstRecord) {
            $this->_printNormalSection($this->ReportHeader);
          }
          $this->_printNormalGroupHeaders($maxLevel, $level);

          // Detail
          $this->_printNormalSection($this->Detail);

          $oldRow =& $this->Cols;
          $isFirstRecord = false;
        }
      }
    }

    $this->_printNormalGroupFooters($maxLevel, 0);
    $this->_printNormalSection($this->ReportFooter);
    $this->newPage();
    $this->OnClose();
    $this->_endReport();
    unset($this->Cols);
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
    $this->_setDocumentTitle($this->Name);

    $this->_setDesignMode(true);
    $this->initDesignHeader();

    $this->_startReport();
    $maxLevel = count($this->_groupFields);

    $this->_printDesignSection($this->ReportHeader);
    $this->_printDesignSection($this->PageHeader);
    $this->_printDesignGroupHeaders($maxLevel, 0);

    $this->_printDesignSection($this->Detail);

    $this->_printDesignGroupFooters($maxLevel, 0);
    $this->_printDesignSection($this->ReportFooter);
    $this->_printDesignSection($this->PageFooter);
    $this->newPage();
    $this->_endReport();
  }

  //////////////////////////////////////////////////////////////////
  // PRIVATE METHODS
  //////////////////////////////////////////////////////////////////

  /**
   *
   * @param string SQL statement
   * @param string Additional WHERE-clause that needs to be appended
   * @access protected
   *
   */
  function _makeSqlFilter($sql, $where)
  {
    $parser = new SimpleSelectParser($sql);
    $sqlParts = $parser->parse();
    if ($sqlParts == false) {
      Amber::showError('Error', __CLASS__ . '::' . __FUNCTION__ . '(): Not a select query');
      die();
    }

    // Apply filter if necessary
    if (!empty($where)) {
      if ($sqlParts['where'] == '') {
        $sqlParts['where'] = $where;
      } else {
        $sqlParts['where'] = '(' . $sqlParts['where'] . ') AND (' . $where . ')';
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
   * @access protected
   *
   */
  function _fetchDataFromDatabase()
  {
    static $uniqueId = 0;
    if (empty($this->RecordSource)) {
      return;
    }
    $db =& Amber::currentDb();
    $createdTemporaryTable = false;

    // Apply where clause
    $sql = $this->_makeSqlFilter($this->RecordSource, $this->Where);

    // Select into temporary table if necessary
    // NOTE: Filter is only implemented for use with MySQL
    if (($this->Filter != '') && ($db->databaseType == 'mysql')) {
      $uniqueId++;
      $createdTemporaryTable = true;
      $sql = 'CREATE TEMPORARY TABLE temp' . $uniqueId . ' (' . $sql . ')';
      $db->Execute($sql);
      if ($db->errorNo() != 0) {
        Amber::showError('Database error while trying to create temporary table ('
          . $db->ErrorNo() . ')', $db->ErrorMsg());
        die();
      }

      // Apply filter
      $sql = 'SELECT * FROM temp' . $uniqueId;
      $sql = $this->_makeSqlFilter($sql, $this->Filter);
    }
    // Get records
    $this->_data =& $db->GetAll($sql);
    if (empty($this->_data)) {
      if ($db->ErrorNo() != 0) {
        Amber::showError('Database Error ' . $db->ErrorNo(), $db->ErrorMsg());
        die();
      }
    }

    if ($createdTemporaryTable) {
      $sql = 'DROP TEMPORARY TABLE IF EXISTS temp' . $uniqueId;
      $db->Execute($sql);
    }
  }

  /**
   * @access protected
   * @return int
   */
  function _HasData()
  {
    if (is_null($this->RecordSource)) {
      return -1;
    } elseif (!is_array($this->_data)) {
      return 0;
    } elseif (count($this->_data) > 0) {
      return 1;
    } else {
      return 0;
    }
  }

  /**
   * @access protected
   * @param array
   */
  function _setControlValues($values)
  { // set values of control bound to columns
    if (is_array($this->Controls)) {
      $keys = array_keys($this->Controls);
      foreach ($keys as $index) {
        $ctrl  =& $this->Controls[$index];
        if (isset($ctrl->ControlSource)) {  // Control can be bound
          $ctrl->setControlValue($values);
        }
      }
    }
  }
    
  /**
   * @access protected
   * @param int
   */
  function OnOpen(&$cancel)
  {
    // Design is loaded, Data not yet loaded
    $cancel = false;
    $this->_Code->Report_Open($cancel);
  }

  /**
   * @access protected
   * @param int
   */
  function OnNoData(&$cancel)
  {
    //Data expected but none given
    $cancel = false;
    $this->_Code->Report_NoData($cancel);
  }

  /**
   * @access protected
   * @param int
   */
  function OnLoadData(&$Cancel)
  {
    // Datarow has been fetched
    $Cancel = false;
    $this->_Code->Report_OnLoadData($Cancel);
  }

  /**
   * @access protected
   */
  function EvaluateExpressions()
  {
    $this->_Code->Report_EvaluateExpressions();
  }

    /**
   * @access protected
   */
  function OnNextRecord()
  {
    $this->_Code->Report_OnNextRecord();
  }

  /**
   * @access protected
   */
  function OnPage()
  {
    // NewPage is executed; gets only called from class pdage...
    $this->_Code->Report_Page();
  }

  /**
   * @access protected
   */
  function OnClose()
  {
    $this->_Code->Report_Close();
  }

  /**
   * @access protected
   * @param Section
   */
  function _printNormalSection(&$section)
  {
    if ($section->isVisible()) {
      $height = 0;
    } else {
      if ($section->hasForceNewPageBefore()) {
        $this->newPageIfDirty();
      }
      $this->_startSection($section, $this->Width);
      $height = $section->printNormal();
      $this->_endSection($section, $height);

      if ($section->hasForceNewPageAfter()) {
        $this->newPageIfDirty();
      }
      $this->_prepareDuplicates($section);
    }
  }

   /**
   * @access protected
   * @param Section
   * @param string
   */
  function _printDesignSection(&$section, $GroupByName='')
  {
    if ($section->isNull()) {
      return 0;
    } else {
      if ($GroupByName) {
        $this->sectionPrintDesignHeader($section->EventProcPrefix . ' - ' . $GroupByName);
      } else {
        $this->sectionPrintDesignHeader($section->EventProcPrefix);
      }
      $this->_startSection($section, $this->Width);
      $section->printDesign();
      $this->_endSection($section, $section->Height);
    }
  }

 /**
   * @access protected
   * @param int
   * @param int
   */
  function _printNormalGroupHeaders($maxLevel, $level)
  {
    for ($i = $level; $i < $maxLevel; $i++) {
      if (isset($this->GroupHeaders[$i])) {
        $this->_printNormalSection($this->GroupHeaders[$i]);
      }
    }
  }

  /**
   * @access protected
   * @param int
   * @param int
   */
  function _printDesignGroupHeaders($maxLevel, $level)
  {
    for ($i = $level; $i < $maxLevel; $i++) {
      if (isset($this->GroupHeaders[$i])) {
        $this->_printDesignSection($this->GroupHeaders[$i], $this->GroupLevels[$i]->ControlSource);
      }
    }
  }

  /**
   * @access protected
   * @param int
   * @param int
   */
  function _printNormalGroupFooters($maxLevel, $level)
  {
    for ($i = $maxLevel-1; $i >= $level; $i--) {
      if (isset($this->GroupFooters[$i])) {
        $this->_printNormalSection($this->GroupFooters[$i]);
      }
    }
  }

  /**
   * @access protected
   * @param int
   * @param int
   */
  function _printDesignGroupFooters($maxLevel, $level)
  {
    for ($i = $maxLevel-1; $i >= $level; $i--) {
      if (isset($this->GroupFooters[$i])) {
        $this->_printDesignSection($this->GroupFooters[$i], $this->GroupLevels[$i]->ControlSource);
      }
    }
  }

  /**
   * @access protected
   * @param Section
   */
  function _prepareDuplicates(&$section)
  {
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

  /**
   * @access protected
   * @param int
   * @param int
   */
  function _resetRunningSum($maxLevel, $level)
  {
    $resetLevel = $maxLevel;
    for ($i = $maxLevel - 1; $i >= $level; $i--) {
      if (isset($this->GroupHeaders[$i]) or (isset($this->GroupFooters[$i]))) {
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
   * @access protected
   * @param int
   * @param int
   */
  function _runningSum($maxLevel, $level)
  {
    for ($i = $level; $i < $maxLevel; $i++) {
      if (isset($this->GroupHeaders[$i])) {
        $this->GroupHeaders[$i]->_RunningSum();
      }
    }

    for ($i = $level; $i < $maxLevel; $i++) {
      if (isset($this->GroupFooters[$i])) {
        $this->GroupFooters[$i]->_RunningSum();
      }
    }

    $this->Detail->_RunningSum();
  }
  
  /**
   * @access protected
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
   * @access protected
   */
  function initDesignHeader()
  {
    $this->_designSection =& new section('');
    $this->_designSection->Name = '<designBorder>';
    $this->_designSection->Height = 240;
    $this->_designSection->Visible = true;
    $this->_designSection->BackColor = 0xDDDDDD;
    $this->_designSection->CanGrow = false;
    $this->_designSection->CanShrink = false;
    $this->_designSection->KeepTogether = false;
    $this->_designSection->EventProcPrefix = '';
    $this->_designSection->_parent =& $this;
    $this->_designSection->_OnFormatFunc = 'allSections_Format';

    $ctlProp = array(
      'Name' => '',
      'Left' => 0,
      'Top' => 0,
      'Width' => $this->Width,
      'Height' => 240,
      'Visible' => true,
      'BackStyle' => 1,
      'BackColor' => 0xDDDDDD, //gray
      'BorderStyle' => 0,
      'BorderColor' => 0, // black
      'BorderWidth' => 1, // 1pt
      'BorderLineStyle' => 0,
      'zIndex' => 0,
      'Value' => '',
      '_OldValue' => '',

      'ForeColor' => 0x000000,
      'FontName' => 'Arial',
      'FontSize' => 8,
      'FontWeight' => 500,
      'TextAlign' => 0,
      'FontItalic' => false,
      'FontUnderline' => false,

      'Caption' => 'Test'
    );

    $ctl =& ControlFactory::create(100, $ctlProp, $this->hReport);
    $this->_exporter->setControlExporter($ctl);
    $this->_designSection->Controls['label'] =& $ctl;
  }

  /**
   * @access protected
   * @param string
   */
  function sectionPrintDesignHeader($text)
  {
    $this->_designSection->Controls['label']->Caption = $text;
    $buffer = '';

    $this->_startSection($this->_designSection, $this->Width);
    $height = $this->_designSection->printNormal();
    $this->_endSection($this->_designSection, $height);
  }

  /**
   * @access protected
   * @param string
   */
  function _setDocumentTitle($name)
  {
    $this->_exporter->setDocumentTitle($name);
  }


//////////////////////////////////////////////////
//
// functions to encapsulate calls to _exporter
//
//////////////////////////////////////////////////

  /**
   * @access public
   * @param bool
   */
  function _setDesignMode($value)
  {
    $this->setNoHeadFoot($value);
    $this->ignoreOnPrint = $value;
    $this->ignoreKeepTogether = $value;
    $this->printHeadFootAsNormalSection = $value;
  }

  /**
   * @access public
   * @param bool
   */
  function setSubReport($value)
  {
    $this->asSubReport = $value;
    $this->setNoAutoPage($value);
    $this->setNoMargins($value);
    $this->setNoHeadFoot($value);
  }

  /**
   * @access public
   * @param bool
   */
  function setNoAutoPage($value)
  {
    $this->noAutoPage = $value;
  }

  /**
   * @access public
   * @param bool
   */
  function setNoMargins($value)
  {
    $this->noMargins = $value;
  }

  /**
   * @access public
   * @param bool
   */
  function setNoHeadFoot($value)
  {
    $this->noHeadFoot = $value;
  }

  /**
   * @access protected
   */
  function _startReport()
  {
    $this->layout =& new pageLayout($this);

    $this->_exporter->startReport($this, $this->asSubReport, true);
  }

  /**
   * @access public
   * @param int
   * @param int
   * @param int
   * @param string
   * @param Section
   */
  function outSection($formatCount, $posY, $sectionHeight, &$secBuff, &$section)
  {
    $this->_exporter->outSectionStart($posY, $this->layout->reportWidth, $sectionHeight, $section->BackColor, $section->Name);
    if ($this->ignoreOnPrint) {
      $this->_exporter->out($secBuff);
    } else {
      $section->_onPrint($cancel, $formatCount);
      if (!$cancel) {
        $this->_exporter->out($secBuff);
      }
    }
    $this->_exporter->outSectionEnd();
  }
}


?>
