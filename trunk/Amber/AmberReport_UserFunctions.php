<?php

/**
 * @ignore
 */

/**
 * @ignore
 */
class AmberReport_UserFunctions
{
  var $report;

  function Report_Open(&$Cancel) { }
  function Report_ComputeColumns(&$Cancel, &$col)  { $this->Report_OnLoadData($Cancel); }
  function Report_OnLoadData(&$Cancel) { $Cancel = false; }
  function Report_NoData(&$Cancel) { $Cancel = true; }
  function Report_EvaluateExpressions() { }
  function Report_OnNextRecord() { }
  function Report_Page() { }
  function Report_Close() { }
  function allSections_Format(&$Cancel, $FormatCount = 1) { } // see also Class SectionNull
  function allSections_Print (&$Cancel, $FormatCount = 1) { } // see also Class SectionNull

  function initialize(&$report)
  { 
    $this->report =& $report;
  
    // Controls
    if ($report->Controls) {
      $keys = array_keys($report->Controls);
      foreach ($keys as $key) {
        $prefix = $report->Controls[$key]->EventProcPrefix;
        $this->$prefix =& $report->Controls[$key];
      }
    }
    $this->ctl =& $report->Controls;
    $this->val =& $report->ControlValues;
    
    // Columns
    $this->col =& $report->Cols;
    
    // Sections
    $keys = array_keys($report->Sections);
    foreach ($keys as $key) {
      $prefix = $report->Sections[$key]->EventProcPrefix;
      $this->$prefix =& $report->Sections[$key];
    }
    
    // GroupLevels
    $this->GroupLevels =& $report->GroupLevels;
    $this->GroupLevel =& $report->GroupLevels;  // Access Compatability
    $this->RecordSource =& $report->RecordSource;
  }
  
  function &getReport()
  {
    return $this->report;
  }
  
  function Page()
  {
    return $this->report->page();
  }
    
  function Bookmark($txt, $level=0)
  {
    $this->report->Bookmark($txt, $level);
  }
}

?>
