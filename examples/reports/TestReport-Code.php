<?php

Class TestReport extends AmberReport_UserFunctions
{

  var $interfaceVersion = 2; //Do not change this

  /***********************************
   * Formulae from calculated fields
   ***********************************/
  function Report_FirstFormat(&$report)
  {
    $Me   =& $report->ControlValues;  //now $Me[Text1] is a shorthand for $this->Controls[Text1]->Value
    $col  =& $report->Columns;

    $Me['Text28'] ="StringExpression";
    $Me['Text30'] =1;
    $Me['Text31'] =1;
    $Me['Text32'] =1;
    $Me['Text33'] ="StringExpression";
    $Me['Text34'] ="StringExpression";
    $Me['Text35'] ="StringExpression";

  }

  /***********************************
   * Here comes the report's module
   ***********************************/
  //Option Compare Database
  //Option Explicit

}
?>