<?php
// Modul GLS Farbe Datenbank: SBZPsyFS

function RGB($R=0, $G=0, $B=0) 
{
  return ($R << 16) | ($G << 8) | $B;
}  


function GLSfarbe_FS1(&$ctrl) 
{ 
  GLSfarbe_FS2($ctrl->Value, $ctrl);
}

function GLSfarbe_FS2($val, &$ctrl) 
{
  $ctrl->ForeColor = 0;
  $ctrl->BorderColor = 0;
  
  $switch = wennleer($val, 0);
  if ($switch > 6) 
    $ctrl->BackColor = RGB(255, 160, 255);
  elseif ($switch == 6)  
    $ctrl->BackColor = RGB(255, 128, 128);
  elseif ($switch == 5)  
    $ctrl->BackColor = RGB(255, 255, 128);
  elseif ($switch == 4)  
    $ctrl->BackColor = RGB(128, 255, 128);
  elseif ($switch == 3)  
    $ctrl->BackColor = RGB(165, 255, 165);
  elseif ($switch == 2)  
    $ctrl->BackColor = RGB(192, 255, 192);
  elseif ($switch == 1)  
    $ctrl->BackColor = RGB(224, 255, 224);
  else
    $ctrl->BackColor = RGB(255, 255, 255);
}    


/*
Option Explicit

Sub GLSfarbe_FS1(c As Control)
'   Bewertung, Geleisenoten

GLSfarbe_FS2 c.Value, c

End Sub

Sub GLSfarbe_FS2(v As Variant, c As Control)
' z.B.: (Bewertung, Abk�rzname), (Bewertung, KritischerWert) ...
' 20.11.98 andere Farben: Vordergrund schwarz, Rahmen schwarz
    
    Dim schw As Long
    Dim hrma As Long
    Dim wei� As Long
    
    schw = QBColor(0)               'schw
    hrma = QBColor(13)              'magenta-rot hell
    wei� = QBColor(15)              'wei� leuchtend

   c.ForeColor = schw
   c.BorderColor = schw
   Select Case wennleer(v, 0)
   Case Is > 6
      c.BackColor = RGB(255, 160, 255)
   Case 6
      c.BackColor = RGB(255, 128, 128)
   Case 5
      c.BackColor = RGB(255, 255, 128)
   Case 4
      c.BackColor = RGB(128, 255, 128)
   Case 3
      c.BackColor = RGB(165, 255, 165)
   Case 2
      c.BackColor = RGB(192, 255, 192)
   Case 1
      c.BackColor = RGB(224, 255, 224)

        Case Else

            c.BackColor = wei�

    End Select

End Sub

Sub GLSfarbe_FS3(v As Variant, c As Control)
' urspr�ngliche Version von glsfarbe_fs2
' z.B.: (Bewertung, Abk�rzname), (Bewertung, KritischerWert) ...
    
    Dim schw As Long, blau As Long, gr�n As Long, cyan As Long
    Dim nrot As Long, mrot As Long, gelb As Long, wei� As Long
    Dim grau As Long, hbla As Long, hgr� As Long, hcya As Long
    Dim hrot As Long, hrma As Long, hgel As Long, lwei As Long
    
    schw = QBColor(0)               'schw
    blau = QBColor(1)               'blau
    gr�n = QBColor(2)               'gr�n
    cyan = QBColor(3)               'cyan
    nrot = QBColor(4)               'normal rot
    mrot = QBColor(5)               'magenta-rot
    gelb = QBColor(6)               'gelb
    wei� = QBColor(7)               'wei�
    grau = QBColor(8)               'grau
    hbla = QBColor(9)               'blau hell
    hgr� = QBColor(10)              'gr�n hell
    hcya = QBColor(11)              'cyan hell
    hrot = QBColor(12)              'rot hell
    hrma = QBColor(13)              'magenta-rot hell
    hgel = QBColor(14)              'gelb hell
    lwei = QBColor(15)              'wei� leuchtend

    Select Case wennleer(v, 0)
        Case Is > 6
            c.BackColor = hrma
            c.BorderColor = hrot
            c.ForeColor = schw
        
        Case 6
            c.BackColor = hrot
            c.BorderColor = schw
            c.ForeColor = schw

        Case 5
            c.BackColor = hgel
            c.BorderColor = schw
            c.ForeColor = schw

        Case 1 To 4
            c.BackColor = hgr�
            c.BorderColor = schw
            c.ForeColor = schw

        Case Else

            c.BackColor = lwei
            c.BorderColor = schw
            c.ForeColor = schw

    End Select

End Sub

*/
?>
