<?php

Class Bericht1 extends AmberReport_UserFunctions
{

  var $interfaceVersion = 2; //Do not change this

  /***********************************
   * Formulae from calculated fields 
   ***********************************/
  function Report_FirstFormat(&$report, $Cancel)
  {
    $Me   =& $report->ControlValues;  //now $Me[Text1] is a shorthand for $this->Controls[Text1]->Value
    $col  =& $report->Cols;

    //$Me['TextBox'] =13;

  }

  /*********************************** 
   * Here comes the report's module    
   ***********************************/
  //Option Compare Database
  //Option Explicit

}
?>
