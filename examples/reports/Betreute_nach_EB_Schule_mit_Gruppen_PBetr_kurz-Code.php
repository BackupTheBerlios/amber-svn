<?php

Class Betreute_nach_EB_Schule_mit_Gruppen_PBetr_kurz extends phpReport_UserFunctions
{

  var $interfaceVersion = 2; //Do not change this

  /***********************************
   * Formulae from calculated fields 
   ***********************************/
  function Report_FirstFormat(&$report)
  {
    $Me   =& $report->ControlValues;  //now $Me[Text1] is a shorthand for $this->Controls[Text1]->Value
    $col  =& $report->Columns;

    $Me['Lfd.Nummer insgesamt'] = 1;
    $Me['LfdNr in der Klasse'] = 1;
    $Me['Feld60'] = substr(wennleer($Me['BewWVAbknam'],"kein Bew."), 1, 9);
    $Me['Feld29'] = 'Report: Betreute nach EB Schule mit Gruppen PBetr kurz';
    $Me['Feld30'] = "Seite " . $report->Page(); //Page;
  }

}

