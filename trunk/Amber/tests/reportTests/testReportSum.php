<?php
////////////////////////////////////////////////////////////////////////
//
// Test classes are user call back functions, extended by
//      - getLayout() which produces the layout of the form
//      - assert which does the tests
//      - getData which returns the recordset
//
////////////////////////////////////////////////////////////////////////


class testReportSum extends AmberReport_UserFunctions
// a simple report with groupfooter and sum
{

  function Report_EvaluateExpressions()
  {
    $val =& $this->val;
    
    $val['Saldo'] = $val['Soll'] - $val['Haben'];
    $this->sumSoll1->sum($val['Soll']);
    $this->sumHaben1->sum($val['Haben']);
    $this->sumSaldo1->sum($val['Saldo']); 

    $this->sumSoll9->sum($val['Soll']);
    $this->sumHaben9->sum($val['Haben']);
    $this->sumSaldo9->sum($val['Saldo']); 

    $test =& $this->test;
    $test->assertEquals($val['sumSaldo9'], $val['sumSoll9'] - $val['sumHaben9'], get_class($this) . 'sumSaldo9-1');
    $test->assertEquals($val['sumSaldo1'], $val['sumSoll1'] - $val['sumHaben1'], get_class($this) . 'sumSaldo1-1');
  }
                       
  
//TEST  
  function assertHtml($html)
  {
    $test =& $this->test;
    $id = get_class($this) . '->assertHtml'; 
    $test->assertContains('161.015,00',    $html, $id . ' sumSoll9-2');
    $test->assertContains('1.234.567,00',  $html, $id  . ' sumHaben9-2');
    $test->assertContains('-1.073.552,00', $html, $id  . ' sumSaldo9-2');
  }
  
  function assertPdf($html)
  {
    $test =& $this->test;
    $id = get_class($this) . '->assertPdf'; 
    $test->assertContains('161.015,00',    $html, $id . ' sumSoll9-2');
    $test->assertContains('1.234.567,00',  $html, $id  . ' sumHaben9-2');
    $test->assertContains('-1.073.552,00', $html, $id  . ' sumSaldo9-2');
  }
  
  function getLayout()
  {
    $rep =& new reportTestBuilder('testReportSum');
    $rep->createReportSections();
    $sec =& $rep->getSection('ReportHeader');
    $sec['Height'] = 0;
    
    $sec =& $rep->getSection('Detail');
    $sec['Height'] = 240;
    
    $rep->defaultControl['TextAlign'] = 0;
    $rep->defaultControl['Format'] = '####';
    $ctl =& $rep->createControl('textfield', 'Text1', $sec, 0*1440, 0*240);
    $ctl['ControlSource'] = 'Konto';

    $rep->defaultControl['TextAlign'] = 3;
    $rep->defaultControl['Format'] = '#,##0.00';
    $ctl =& $rep->createControl('textfield', 'Soll', $sec, 2*1440, 0*240);
    $ctl['ControlSource'] = 'Soll';
    $ctl =& $rep->createControl('textfield', 'Haben', $sec, 3*1440, 0*240);
    $ctl['ControlSource'] = 'Haben';
    $ctl =& $rep->createControl('textfield', 'Saldo', $sec, 4*1440, 0*240);
    
    $id = $rep->createGroup('Gruppe', false, true);
    $sec =& $rep->getSection('GroupFooters', $id);
    $sec['Height'] = 300;
    $rep->defaultControl['FontWeight'] = 700;
    $rep->defaultControl['TextAlign'] = 0;
    $rep->defaultControl['Format'] = '####';
    $ctl =& $rep->createControl('textfield', 'Text2', $sec, 0*1440, 0*240);
    $ctl['ControlSource'] = 'Gruppe';

    $rep->defaultControl['TextAlign'] = 3;
    $rep->defaultControl['Format'] = '#,##0.00';
    $ctl =& $rep->createControl('textfield', 'sumSoll1', $sec, 2*1440, 0*240);
    $ctl =& $rep->createControl('textfield', 'sumHaben1', $sec, 3*1440, 0*240);
    $ctl =& $rep->createControl('textfield', 'sumSaldo1', $sec, 4*1440, 0*240);
    
    
    $sec =& $rep->getSection('ReportFooter');
    $sec['Height'] = 400;
    $rep->defaultControl['FontWeight'] = 700;
    $ctl =& $rep->createControl('textfield', 'sumSoll9', $sec, 2*1440, 0*240);
    $ctl =& $rep->createControl('textfield', 'sumHaben9', $sec, 3*1440, 0*240);
    $ctl =& $rep->createControl('textfield', 'sumSaldo9', $sec, 4*1440, 0*240);
    return $rep->getXML();
  }
  
  function getData()
  {
    $a = array(
      array('Konto' => 3110, 'Soll'=>    11, 'Haben'=>1000000),
      array('Konto' => 3120, 'Soll'=>   234, 'Haben'=> 200000),
      array('Konto' => 3130, 'Soll'=>   245, 'Haben'=>  30000),
      array('Konto' => 3210, 'Soll'=> 34562, 'Haben'=>   4000),
      array('Konto' => 3220, 'Soll'=> 95629, 'Haben'=>    500),
      array('Konto' => 4110, 'Soll'=>  3958, 'Haben'=>     60),
      array('Konto' => 4120, 'Soll'=> 26376, 'Haben'=>      7)
    );   
    $keys = array_keys($a);
    foreach ($keys as $key) {
      $a[$key]['Gruppe'] = floor($a[$key]['Konto'] / 100);
      $a[$key]['Klasse'] = floor($a[$key]['Konto'] / 1000);
    }
    return $a;  
  }
} 

?>
