<?php

/**
 * @ignore
 */

/**
 * @ignore
 */
class AmberReport_UserFunctions
{
  function Report_Open(&$Cancel) { }
  function Report_NoData(&$Cancel) { $Cancel = true; }
  function Report_OnLoadData(&$Cancel) { $Cancel = false; }
  function Report_EvaluateExpressions() { }
  function Report_OnNextRecord() { }
  function Report_Page() { }
  function Report_Close() { }
  function Report_Sort(&$a, &$b) { return 'noSort!'; }
  function allSections_Format(&$Cancel, $FormatCount = 1) { } // see also Class SectionNull
  function allSections_Print (&$Cancel, $FormatCount = 1) { } // see also Class SectionNull

  
  var $report;

  function initialize(&$report)
  { 
  
    $this->report =& $report;
  
    // Controls
    if ($report->Controls) {
      $keys = array_keys($report->Controls);
      foreach ($keys as $key) {
        $key2 = $report->Controls[$key]->EventProcPrefix;
        $this->$key2 =& $report->Controls[$key]; 
      }
    }
    $this->ctl =& $report->Controls;
    $this->val =& $report->ControlValues;
    
    // Columns
    $this->col =& $report->Cols;
    
    // Section
    $keys = array_keys($report->Sections);
    foreach ($keys as $key) {
      $key2 = $report->Sections[$key]->EventProcPrefix;
      $this->$key2 =& $report->Sections[$key];
    }
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
