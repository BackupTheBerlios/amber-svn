<?php
////////////////////////////////////////////////////////////////////////
//
// Test classes are user call back functions, extended by
//      - getLayout() which produces the layout of the form
//      - assert which does the tests
//      - getData which returns the recordset
//
////////////////////////////////////////////////////////////////////////


class testReport1 extends AmberReport_UserFunctions
// this tests the various access methods to controls inside Evaluate_Expression
{
  function Report_EvaluateExpressions()
  {
    $report =& $this->report;
    $me =& $report->ControlValues;       // depreciated
    $ctls =&$report->Controls;           // depreciated
    $ctl =& $this->ctl;
    $val =& $this->val;
    
    $me['Text1'] = $me['Text1'] . 'Test1Test';
    $ctls['Text2']->Value = $ctls['Text2']->Value . 'Test2Test';
    
    $this->Text3->Value = $this->Text3->Value . 'Test3Test';
    $ctl['Text4']->Value = $ctl['Text4']->Value . 'Test4Test';
    $val['Text5'] = $val['Text5'] . 'Test5Test';
  }
  
  function assertHtml($html)
  { 
    $test =& $this->test;
    $id = get_class($this) . '->assertHtml '; 
    $test->assertContains('Test1Test', $html, $id . 'Text1');
    $test->assertContains('Test2Test', $html, $id . 'Text2');
    $test->assertContains('Test3Test', $html, $id . 'Text3');
    $test->assertContains('Test4Test', $html, $id . 'Text4');
    $test->assertContains('Test5Test', $html, $id . 'Text5');
  }
  
  function assertPdf($html)
  { 
    $test =& $this->test;
    $id = get_class($this) . '->assertPdf '; 
    $test->assertContains('Test1Test', $html, $id . 'Text1');
    $test->assertContains('Test2Test', $html, $id . 'Text2');
    $test->assertContains('Test3Test', $html, $id . 'Text3');
    $test->assertContains('Test4Test', $html, $id . 'Text4');
    $test->assertContains('Test5Test', $html, $id . 'Text5');
  }
  
  function getLayout()
  {
    $rep =& new reportTestBuilder('testReport1');
    $sec =& $rep->getSection('Detail');
    $ctl =& $rep->createControl('textfield', 'Text1', $sec, 0, 0*1440, 0*240);
    $ctl =& $rep->createControl('textfield', 'Text2', $sec, 0, 1*1440, 0*240);
    $ctl =& $rep->createControl('textfield', 'Text3', $sec, 0, 0*1440, 1*240);
    $ctl =& $rep->createControl('textfield', 'Text4', $sec, 0, 1*1440, 1*240);
    $ctl =& $rep->createControl('textfield', 'Text5', $sec, 0, 0*1440, 2*240);
    return $rep->getXML();
  }
}
?>
