<?php

/**
*
* @package Amber
* @subpackage Tests
*
*/

ini_set('include_path', ini_get('include_path') . ':' . dirname(__FILE__). '/../../Amber/');
ini_set('max_execution_time', 600);
ini_set('memory_limit', '48M');

require_once 'unit.php';
require_once 'reportTestBuilder.php';
require_once '../Amber.php';

/**
*
* @package Amber
* @subpackage Tests
*
* usage: 
*/
class testUserFunctions1 extends myTestCase
{
  function runTestReport($CodeClassName)
  { 
    $testClass =& new $CodeClassName;
    $testClass->test =& $this;
    $reportArray =& $testClass->getLayout();
    
    $report =& new ReportPaged();
    $report->Name = $reportArray->Name;
    $report->hReport = objectHandler::getHandle($report);
    
    $report->_Code =& $testClass;

    $report->initialize_report($reportArray);

    if (method_exists($testClass, 'getdata')) {
      $report->RecordSource = '[Array]';
      $report->_data = $testClass->getData();
    }

    ob_start();
    $report->run('html');
    $s = ob_get_contents();
    ob_end_clean();
    
    $testClass->assert($s);
    return $s;
  }
  
  function test_TestReport1()
  {
    setlocale (LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');
    $this->runTestReport('TestReport1');
    $this->runTestReport('TestReportSum');
  }
  
}

////////////////////////////////////////////////////////////////////////
//
// Test classes are user call back functions, extended by
//      - getLayout() which produces the layout of the form
//      - assert which does the tests
//      - data which returns the recordset [FIXME: this doesn't work now]
//
////////////////////////////////////////////////////////////////////////


class TestReport1 extends AmberReport_UserFunctions
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
  
  function assert($html)
  { 
    $test =& $this->test;
    $test->assertContains('Test1Test', $html, 'TestReport1 Text1');
    $test->assertContains('Test2Test', $html, 'TestReport1 Text2');
    $test->assertContains('Test3Test', $html, 'TestReport1 Text3');
    $test->assertContains('Test4Test', $html, 'TestReport1 Text4');
    $test->assertContains('Test5Test', $html, 'TestReport1 Text5');
  }
  
  function getLayout()
  {
    $rep =& new reportTestBuilder('TestReport1');
    $sec =& $rep->getSection('Detail');
    $ctl =& $rep->createControl('textfield', 'Text1', $sec, 0, 0*1440, 0*240);
    $ctl =& $rep->createControl('textfield', 'Text2', $sec, 0, 1*1440, 0*240);
    $ctl =& $rep->createControl('textfield', 'Text3', $sec, 0, 0*1440, 1*240);
    $ctl =& $rep->createControl('textfield', 'Text4', $sec, 0, 1*1440, 1*240);
    $ctl =& $rep->createControl('textfield', 'Text5', $sec, 0, 0*1440, 2*240);
    return $rep->getXML();
  }
}



class TestReportSum extends AmberReport_UserFunctions
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
  function assert($html)
  {
    $test =& $this->test;
    $test->assertContains('161.015,00',    $html, get_class($this) . ' sumSoll9-2');
    $test->assertContains('1.234.567,00',  $html, get_class($this) . ' sumHaben9-2');
    $test->assertContains('-1.073.552,00', $html, get_class($this) . ' sumSaldo9-2');
  }
  
  function getLayout()
  {
    $rep =& new reportTestBuilder('TestReport1');
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

class ExampleInvoiceList extends AmberReport_UserFunctions
// a simple report in the docu
{
  function Report_Open(&$Cancel)
  {
    $this->Text18->createAggregate('sum');       
  }

  function Report_EvaluateExpressions()
  {
    $val =& $this->val;
    $col =& $this->col;

    $val['name'] = $col['firstname'] . ' ' . $col['lastname'];
    
    $this->Text18->addValue($val['amount']);        
    
    $val['Text26'] = 'Page  ' . $report->Page();

  }
                       
  
//TEST  
  function assert($html)
  {
    $test =& $this->test;
    $test->assertContains('161.015,00',    $html, get_class($this) . ' sumSoll9-2');
    $test->assertContains('1.234.567,00',  $html, get_class($this) . ' sumHaben9-2');
    $test->assertContains('-1.073.552,00', $html, get_class($this) . ' sumSaldo9-2');
  }
  
  function getLayout()
  {
    $rep =& new reportTestBuilder('TestReport1');
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
    $customer = array(
      'id'=>1, 'title'=>'Mr.', lastname=>'Jackson', firstname=>'John',  	   	   	
      'id'=>2, 'title'=>'Mr.', lastname=>'Bown', firstname=>'Bob', 	  	  	
      'id'=>3, 'title'=>'Mrs.', lastname=>'Anderson', firstname=>'Alice', 	  	  	
      'id'=>4, 'title'=>'Ms.', lastname=>'Smith', firstname=>'Susan', 	  	  	
      'id'=>5, 'title'=>'Mr.', lastname=>'Tompson', firstname=>'Terry', 	  	  	
      'id'=>6, 'title'=>'Mr.', lastname=>'Bean', firstname=>'Ben', 	  	  	
      'id'=>7, 'title'=>'Mr.', lastname=>'Smith', firstname=>'Sam', 	  	  	
      'id'=>8, 'title'=>'Mrs.', lastname=>'Clark', firstname=>'Catherine' 	  	  	
    );  
  }
}
$suite  = new PHPUnit_TestSuite("testUserFunctions1");
$result =& PHPUnit::run($suite);
echo $result->toHTML();

?>
