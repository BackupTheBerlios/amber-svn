<?php

class GLS extends AmberReport_UserFunctions
{

  var $interfaceVersion = 2; //Do not change this

  function Report_FirstFormat(&$report, &$cancel)
  {
    $Me   =& $report->ControlValues;  //now $Me[Text1] is a shorthand for $this->Controls[Text1]->Value
    $col  =& $report->Cols;

    $Me['Feld17'] = 'GLS-Liste nach Gruppen Sam 29.Mai 2004';
    $Me['Feld21'] = 1; #Counter....
    $Me['AntrNr'] = ($Me['antrnumm'] > 0) ? 'A' : '';
    $Me['MBIndikator'] = ($Me['MetaBew'] == 0) ? '' : 'M';
    $Me['Wiedervorlegender'] = Mid(wennleer($Me['MetaBewWVAbknam'],''),1,9);
    $Me['Feld91'] = druckStatusR($report);
    $Me['Feld20'] = $report->Page(); //Page
    $Me['BAbkname1'] = Mid(wennleer($Me['BAbkn'],""),1,9);
    
    if ($col['SchMax'] < 5) {
      $cancel = true;
    }    
  }

  function Report_Open(&$report, &$Cancel)
  {
    #print "Report_Open called<br>\n";
    #$report->RecordSource = 'select * from Betreute where 1=0;';
  }

  function Report_NoData(&$report, &$Cancel)
  {
    print "Report_NoData called<br>\n";
    $Cancel = true;
  }

  function Report_Close(&$report)
  {
    #print "Report_Close called<br>\n";
  }

  function Report_Page(&$report)
  {
    #print "--- page --- <br>\n";
  }


  function Gruppenkopf1_Print(&$report, &$Cancel, $FormatCount)
  {
    $Me   =& $report->ControlValues;
    //kleine Schweinerei die aber sein muss...
    if ($FormatCount == 1) {
      if ($report->_exporter->type == 'fpdf') {
        $report->_exporter->_pdf->BookMark($Me['PBetreuer'],0,-1);
      }
    }
  }

  function Detail1_Print(&$report, &$Cancel, $FormatCount)
  {
    //$Cancel = true;
  }

  function Detail1_Format(&$report, &$Cancel, $FormatCount)
  {
    $ctrl =& $report->Controls;       //now $ctrl[Text1]->BackColor is a shorthand for $this->Controls[Text1]->Backcolor
    $Me   =& $report->ControlValues;  //now $Me[Text1] is a shorthand for $this->Controls[Text1]->Value
#return;
    #Wenn Bewertung notwendig, wird WVorleger sichtbar
    If (! $Me['BewNotwendig']) {
      $vis = False;
    } Else {
      $vis = ($Me['SchMax'] >= 5);
    }

    $vis = true;
    $ctrl['Wiedervorlegender']->Visible = $vis;
    $ctrl['MetaBewWVDat']->Visible = $vis;

    #Wenn Antrag vorliegt, wird AntrNr sichtbar
    If (is_null($Me['AntrNr'])) {
      $ctrl['AntrNr']->Visible = 0;
    } else {
      $ctrl['AntrNr']->Visible = -1;
    }


    #Wenn Wenn Metabewertung, wird m sichtbar
    If ($Me['MetaBew'] <> 0) {
      $ctrl['MBIndikator']->Visible = -1;
    } Else {
      $ctrl['MBIndikator']->Visible = 0;
    }

    GLSfarbe_FS1($ctrl['HGem']);
    GLSfarbe_FS1($ctrl['HLei']);
    GLSfarbe_FS1($ctrl['HSel']);
    GLSfarbe_FS1($ctrl['SGem']);
    GLSfarbe_FS1($ctrl['SLei']);
    GLSfarbe_FS1($ctrl['SSel']);
    GLSfarbe_FS1($ctrl['PGem']);
    GLSfarbe_FS1($ctrl['PLei']);
    GLSfarbe_FS1($ctrl['PSel']);

    GLSfarbe_FS2($ctrl['BBew']->Value, $ctrl['BBew']);

    GLSfarbe_FS2($ctrl['SchMax']->Value, $ctrl['Abkürzname']);
    GLSfarbe_FS2($ctrl['SchMax']->Value, $ctrl['SchMax']);
    GLSfarbe_FS2($ctrl['SchMax']->Value, $ctrl['AHS']);
    GLSfarbe_FS2($ctrl['SchMax']->Value, $ctrl['KrZahl']);

    #$Cancel = true;
    #$report->Visible = true;
  }



}
 ?>