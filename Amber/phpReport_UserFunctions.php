<?php

/**
 * @ignore
 */

/**
 * @ignore
 */
Class phpReport_UserFunctions
{
  function Report_FirstFormat(&$report)
  {
  }

  function Report_Open(&$report, &$Cancel)
  {
  }

  function Report_NoData(&$report, &$Cancel)
  {
    $Cancel = true;
  }

  function Report_Close(&$report)
  {
  }

  function Report_Page(&$report)
  {
  }

  function allSections_Format(&$report, &$Cancel, $FormatCount=1) // see also Class SectionNull
  {
  }
  
  function allSections_Print(&$report, &$Cancel, $FormatCount=1) // see also Class SectionNull
  {
  }
}
