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
    $reportArray =& $testClass->getLayout();
    
    $report =& new ReportPaged();
    $report->Name = $reportArray->Name;
    $report->hReport = objectHandler::getHandle($report);
    
    $report->_Code =& $testClass;

    $report->initialize_report($reportArray);
    ob_start();
    $report->run('html');
    $s = ob_get_contents();
    ob_end_clean();
    
    $testClass->assert($this, $s);
    return $s;
  }
  
  function test_TestReport1()
  {
    $this->runTestReport('TestReport1');
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
  
  function assert(&$test, $html)
  {
    $test->assertContains('Test1Test', $html, 'TestReport1 Text1');
    $test->assertContains('Test2Test', $html, 'TestReport1 Text2');
    $test->assertContains('Test3Test', $html, 'TestReport1 Text3');
    $test->assertContains('Test4Test', $html, 'TestReport1 Text4');
    $test->assertContains('Test5Test', $html, 'TestReport1 Text5');
  }
  
  function getLayout()
  {
    $r =& new reportTestBuilder('TestReport1');
    $ctl =& $r->createControl('textfield', 'Text1', 'Detail', 0, 0*1440, 0*240);
    $ctl =& $r->createControl('textfield', 'Text2', 'Detail', 0, 1*1440, 0*240);
    $ctl =& $r->createControl('textfield', 'Text3', 'Detail', 0, 0*1440, 1*240);
    $ctl =& $r->createControl('textfield', 'Text4', 'Detail', 0, 1*1440, 1*240);
    $ctl =& $r->createControl('textfield', 'Text5', 'Detail', 0, 0*1440, 2*240);
    return $r->report;
  }    
}


$suite  = new PHPUnit_TestSuite("testUserFunctions1");
$result =& PHPUnit::run($suite);
echo $result->toHTML();

?>
