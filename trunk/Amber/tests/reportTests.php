<?php

/**
*
* @package Amber
* @subpackage Tests
*
*/

#ini_set('include_path', ini_get('include_path') . ':' . dirname(__FILE__). '../../Amber/');
ini_set('max_execution_time', 5);

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
class testReports extends myTestCase
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
    if (method_exists($testClass, 'assertHtml')) {
      ob_start();
      $report->run('html');
      $s = ob_get_contents();
      ob_end_clean();
      $testClass->assertHtml($s);
    }
    if (method_exists($testClass, 'assertPdf')) {
      ob_start();
      $report->run('testpdf');
      $s = ob_get_contents();
      ob_end_clean();
      $testClass->assertPdf($s);
    }
  }
  
  function tstOneReport($filename)
  {
    setlocale (LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');
    include_once $filename;
    $className = basename($filename, '.php');
    $this->runTestReport($className);
  }
  
  function test1()
  {
     $this->tstOneReport('reportTests/exampleInvoiceList.php');
  }   
  
/*  function test_TestReports()
  {
    setlocale (LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');
    foreach (glob('reportTests/test?*.php') as $filename) {
      $this->tstOneReport($filename);
    }
    foreach (glob('reportTests/example?*.php') as $filename) {
      $this->tstOneReport($filename);
    }
  }
*/  
}

$suite  = new PHPUnit_TestSuite("testreports");
$result =& PHPUnit::run($suite);
echo $result->toHTML();

?>
