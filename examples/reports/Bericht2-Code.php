<?php

Class Bericht2 extends phpReport_UserFunctions
{

  var $interfaceVersion = 2; //Do not change this

  /***********************************
   * Formulae from calculated fields 
   ***********************************/
  function Report_FirstFormat(&$report)
  {
    $Me   =& $report->ControlValues;  //now $Me[Text1] is a shorthand for $this->Controls[Text1]->Value
    $col  =& $report->Columns;

    //$Me['Text1'] =12345.6789;

  }

}

