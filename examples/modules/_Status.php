<?php

function druckstatusR(&$r)
{
  $tname = $r->RecordSource;
  If (IsNull($tname)) {
       $tname = "Keine Tabelle";
  }     
  If (Left($tname, 7) == "SELECT ") {
       $tname = "SQL direkt";
  }
  
  $Rname = $r->Name;
  $d = " am " . Format(Now(), "ddd dd.mmm.yyyy") . " um " . Format(Now(), "hh.nn");
  return  "Ausdruck von Bericht:[" . $Rname . "] Tabelle/Abfrage: (" . $tname . ")" . $d . ")";

}


/*
'druckstatusR: Ausdruck von=Dateiname, am=Datum, um=Uhrzeit
'             mit Stichtag etc.
'
Function druckstatusR(r As Report) As String

Dim Rname, tname, d As Variant
Dim db As Database

Set db = DBEngine(0)(0)

tname = r.RecordSource
If IsNull(tname) Then
     tname = "Keine Tabelle"
End If
If Left$(tname, 7) = "SELECT " Then
     tname = "SQL direkt"
End If

Rname = r.Name
d = " am " + Format$(Now, "ddd dd.mmm.yyyy") + " um " + Format$(Now, "hh.nn")
druckstatusR = "Ausdruck von Bericht:[" + Rname + "] Tabelle/Abfrage: (" + tname + ")" + d + "   DB:[" + db.Name + "] (MSAcc" + db.Version + ")"
End Function
*/
?>