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
require_once 'ObjectLoader.php';
require_once 'SimpleSQLParser.php';
require_once 'Section.php';
require_once 'Exporter/ExporterFactory.php';
require_once 'Controls/ControlFactory.php';
require_once 'phpReport_UserFunctions.php';
require_once 'basic.php';
require_once 'ObjectHandler.php';
require_once 'mayflower.php';

/**
 *
 * @package PHPReport
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
    parent::AmberObject();
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
    $this->hReport = objectHandler::getHandle($this);
    $res =& XMLLoader::_makeXMLTree($data['design']);
    $xml = $res['report'];

    $classLoaded = false;
    $className = $data['class'];

    if ((isset($className)) && (!empty($className)) && (!class_exists($className))) {
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
  function run($type, $isSubreport)
  {
    $this->_installExporter($type);
    $this->_setDocumentTitle($this->Name);
    $this->_startReport($isSubreport, false);

    $this->OnOpen($cancel);
    if ($cancel) {
      $this->_endReport($isSubreport);
      return;
    }
    $this->_fetchDataFromDatabase();
    if ($this->_HasData() == 0) {
      $this->OnNoData($cancel);
      if ($cancel) {
        $this->_endReport($isSubreport);
        return;
      }
    }
    $maxLevel = count($this->_groupFields);
    $this->Cols =& $this->_data[0];
    $this->_setControlValues();
    $this->OnFirstFormat($cancel);
    
    if (!$cancel) {
      $this->_printNormalSection($this->ReportHeader);
      $this->_printNormalGroupHeaders($maxLevel, 0);
    }
    
    if (is_null($this->RecordSource)) {   // if no data expected print Detail only once
      $this->_printNormalSection($this->Detail);
    } else {                              // Loop through all records
      $oldRow =& $this->Cols;
      $keys = array_keys($this->_data);
      foreach ($keys as $rowNumber) {
        $this->Cols =& $this->_data[$rowNumber];
        $level = $this->_getGroupLevel($this->Cols, $oldRow);
        $this->_setControlValues();
        $this->OnFirstFormat($cancel);
        if (!$cancel) {
          $this->_printNormalGroupFooters($maxLevel, $level);
          $this->_printNormalGroupHeaders($maxLevel, $level);
          $this->_resetRunningSum($level, $maxLevel);
          $this->_printNormalSection($this->Detail);
          $oldRow =& $this->Cols;
        }  
      }
    }
    $this->_printNormalGroupFooters($maxLevel, 0);
    $this->_printNormalSection($this->ReportFooter);
    $this->newPage();
    $this->OnClose();
    $this->_endReport($isSubreport);
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
  function printDesign($type, $isSubreport)
  {
    $this->_installExporter($type);
    $this->_setDocumentTitle($this->Name);

    $this->_startReport($isSubreport, true);

    $maxLevel = count($this->_groupFields);

    $this->_printDesignSection($this->ReportHeader);
    $this->_printDesignSection($this->PageHeader);
    $this->_printDesignGroupHeaders($maxLevel, 0);

    $this->_printDesignSection($this->Detail);

    $this->_printDesignGroupFooters($maxLevel, 0);
    $this->_printDesignSection($this->ReportFooter);
    $this->_printDesignSection($this->PageFooter);
    $this->newPage();
    $this->_endReport($isSubreport);
  }

  //////////////////////////////////////////////////////////////////
  // PRIVATE METHODS
  //////////////////////////////////////////////////////////////////

  /**
   *
   * @access private
   *
   */
  function _makeSqlFilter($sql, $filter)
  {
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
        $sqlParts['where'] = '(' . $sqlParts['where'] . ') AND (' . $filter . ')';
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
    $db =& Amber::currentDb();
    $this->_data =& $db->GetAll($sql);
    if (empty($this->_data)) {
      if ($db->ErrorNo() != 0) {
        Amber::showError('Database Error ' . $db->ErrorNo(), $db->ErrorMsg());
        die();
      }
    }
  }

  /**
   * @access private
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
   * @access private
   */
  function _setControlValues()
  {
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
  function OnOpen(&$cancel)
  {
    // Design is loaded, Data will come now
    $cancel = false;
    $this->_Code->Report_Open($this, $cancel);
  }

  /**
   * @access private
   */
  function OnFirstFormat(&$cancel)
  {
    // Datarow has changed
    $cancel = false;
    $this->_Code->Report_FirstFormat($this, $cancel);
  }

  /**
   * @access private
   */
  function OnNoData(&$cancel)
  {
    //Data expected but none given
    $cancel = false;
    $this->_Code->Report_NoData($this, $cancel);
  }

  /**
   * @access private
   */
  function OnClose()
  {
    // Datarow has changed
    $this->_Code->Report_Close($this);
  }

  /**
   * @access private
   */
  function OnPage()
  {
    // NewPage is executed; gets only called from class pdage...
    $this->_Code->Report_Page($this);
  }

  /**
   * @access private
   * @param string
   */
  function _printNormalSection(&$section)
  {
    //Amber::dumpArray($this);
    $section->_RunningSum();
    if ($section->isVisible()) {
      $height = 0;
    } else {
      if ($section->hasForceNewPageBefore()) {
        $this->newPage();
      }
      $this->_startSection($section, $this->Width, $buffer);
      $height = $section->printNormal($buffer);
      $this->_endSection($section, $height, $buffer);


      if ($section->hasForceNewPageAfter()) {
        $this->newPage();
      }
      $this->_prepareDuplicates($section);
    }
  }

   /**
   * @access private
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
      $this->_startSection($section, $this->Width, $buffer);
      $section->printDesign($buffer);
      $this->_endSection($section, $section->Height, $buffer);
    }
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
        $this->_printNormalSection($this->GroupHeaders[$i]);
      }
    }
  }

/**
   * @access private
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
   * @access private
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
   * @access private
   * @param int
   * @param int
   */
  function _printDesignGroupFooters($maxLevel, $level)
  {
    for ($i = $maxLevel-1; $i >= $level; $i--) {
      if (isset($this->GroupFooters[$i])) {
        $this->GroupFooters[$i]->printDesign($this->GroupLevels[$i]->ControlSource);
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

//////////////////////////////////////////////////
// 
// functions to encapsulate calls to _exporter
//
//////////////////////////////////////////////////

  /**
   * @access private
   */
  function _startReport($isSubreport, $isDesignMode)
  {
    if (isset($this->_exporter)) {
      $this->_exporter->startReport($this, $isSubreport, $isDesignMode);
    }
  }

  /**
   * @access private
   */
  function _endReport()
  {
    if (isset($this->_exporter)) {
      $this->_exporter->endReport($this);
    }
  }
  
  function _setDocumentTitle($name)
  {
    $this->_exporter->setDocumentTitle($name);
  } 

  function _startSection(&$section, $width, &$buffer)
  {
    $this->_exporter->startSection($section, $width, $buffer);
  }  

  function _endSection(&$section, $height, &$buffer)
  {
    $this->_exporter->endSection($section, $height, $buffer);
  }  

  function sectionPrintDesignHeader($title)
  {
    $this->_exporter->sectionPrintDesignHeader($title);
  }   


  /**
   *
   * @access public
   * @return int current page number
   *
   */
  function page()
  {
    return $this->_exporter->page();
  }
  
  function newPage()
  {
    $this->_exporter->newPage();
  }  
}


?>
