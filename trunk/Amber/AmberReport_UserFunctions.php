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

  function setReport(&$report)
  {
    $this->report =& $report;
  }
  
  function &getReport()
  {
    return $this->report;
  }

  function Report_Open(&$Cancel) { }
  function Report_NoData(&$Cancel) { $Cancel = true; }
  function Report_OnLoadData(&$Cancel) { $Cancel = false; }
  function Report_EvaluateExpressions() { }
  function Report_OnNextRecord() { }
  function Report_Page() { }
  function Report_Close() { }
  function Report_Sort(&$a, &$b) { return 'noSort!'; }
  function allSections_Format(&$Cancel, $FormatCount = 1) { } // see also Class SectionNull
  function allSections_Print(&$Cancel, $FormatCount = 1) { } // see also Class SectionNull
}

?>
