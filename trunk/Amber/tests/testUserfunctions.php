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

#require_once '../Report.php';
require_once 'unit.php';
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
  function &setAmberConfig()
  {
    $cfgFileName = '../conf/localconf.xml';
    if (!file_exists($cfgFileName)) {
      Amber::showError('Error: localconf.xml does not exist', 'Amber needs to be configured before you can use it. <br>Use the <a href="../Amber/install/index.php" target="_blank">install tool</a> to set up the database connection.');
    }
    $cfg = new AmberConfig;
    $cfg->fromXML($cfgFileName);

    setlocale (LC_CTYPE, 'de_DE', 'de_DE@euro');
    setlocale (LC_TIME, 'de_DE', 'de_DE@euro'); // needed for date, time
    setlocale (LC_MONETARY, 'de_DE', 'de_DE@euro'); // needed for numbers

    return Amber::getInstance($cfg);
  }

  function createTestReport($CodeClassName)
  { 
    $testClass =& new $CodeClassName;
    $reportArray =& $testClass->getLayout();
    
    $report =& new ReportPaged();
    $report->Name = $reportArray->Name;
    $report->hReport = objectHandler::getHandle($report);
    
    $report->_Code =& $testClass;

    $report->initialize_report($reportArray);
    return $report; 
  }
  
  function test_TestReport1()
  {
    
    $report =& $this->createTestReport('TestReport1');
    ob_start();
    $report->run('html');
    $s = ob_get_contents();
    ob_end_clean();
    $this->assertContains('Test1Test', $s, 'see TestReport1 Text 1');
    $this->assertContains('Test2Test', $s, 'see TestReport1 Text 2');
    $this->assertContains('Test3Test', $s, 'see TestReport1 Text 3');
    $this->assertContains('Test4Test', $s, 'see TestReport1 Text 4');
  }
  
}

class TestReport1 extends AmberReport_UserFunctions
{
  function Report_EvaluateExpressions()
  {
    $report =& $this->report;
    $me =& $report->ControlValues;       // old access
    $ctl =& $this->ctl;
    $val =& $this->val;
    
    $me['Text 1'] = $me['Text 1'] . 'Test1Test';

    $this->Text_2->Value = $this->Text_2->Value . 'Test2Test';
    $ctl['Text 3']->Value = $ctl['Text 3']->Value . 'Test3Test';
    $val['Text 4'] = $val['Text 4'] . 'Test4Test';
  }

  function getLayout()
  {
    $mm = 1440 / 25.4;
    $pReport = array(
      'RecordSource' => '',
      'Width' => 10093,
      'Name' => 'TestReport1',
      'HasModule' => -1,
      'Printer' => array(
        'TopMargin'     => 720,
        'BottomMargin'  => 720,
        'LeftMargin'    => 720,
        'RightMargin'   => 720,
        'Orientation'   => 1,     //Portrait
        'PaperSize'     => 9      // DIN A4
      ),
      'Detail' => array(
        'EventProcPrefix' => 'Detail1',
        'Name' => 'Detail1',
        'ForceNewPage' => 0,
        'Height' => 2440,
        'Controls' => array(
          'Text 1' => array(
            'EventProcPrefix' => 'Text_1',
            'Name' => 'Text 1',
            'ControlType' => 109,
            'ControlSource' => '',
            'Left' => 1440,
            'Top' => 720,
            'Width' => 1800,
            'Height' => 240,
            'BackStyle' => 1,
            'BorderStyle' => 1,
            'BorderColor' => 8421504,
            'FontName' => 'Arial Narrow',
            'FontSize' => 8,
            'FontWeight' => 700,
            'TextAlign' => 2,
            'FontBold' => 0,
            'zIndex' => 10
          ),
          'Text 2' => array(
            'EventProcPrefix' => 'Text_2',
            'Name' => 'Text 2',
            'ControlType' => 109,
            'ControlSource' => '',
            'Left' => 2880,
            'Top' => 720,
            'Width' => 1800,
            'Height' => 240,
            'BackStyle' => 1,
            'BorderStyle' => 1,
            'BorderColor' => 8421504,
            'FontName' => 'Arial Narrow',
            'FontSize' => 8,
            'FontWeight' => 700,
            'TextAlign' => 2,
            'FontBold' => 0,
            'zIndex' => 20
          ),
          'Text 3' => array(
            'EventProcPrefix' => 'Text_3',
            'Name' => 'Text 3',
            'ControlType' => 109,
            'ControlSource' => '',
            'Left' => 1440,
            'Top' => 1440,
            'Width' => 1800,
            'Height' => 240,
            'BackStyle' => 1,
            'BorderStyle' => 1,
            'BorderColor' => 8421504,
            'FontName' => 'Arial Narrow',
            'FontSize' => 8,
            'FontWeight' => 700,
            'TextAlign' => 2,
            'FontBold' => 0,
            'zIndex' => 10
          ),
          'Text 4' => array(
            'EventProcPrefix' => 'Text_4',
            'Name' => 'Text 4',
            'ControlType' => 109,
            'ControlSource' => '',
            'Left' => 2880,
            'Top' => 1440,
            'Width' => 1800,
            'Height' => 240,
            'BackStyle' => 1,
            'BorderStyle' => 1,
            'BorderColor' => 8421504,
            'FontName' => 'Arial Narrow',
            'FontSize' => 8,
            'FontWeight' => 700,
            'TextAlign' => 2,
            'FontBold' => 0,
            'zIndex' => 10
          )
          

        )  
      )
    );
    return $pReport;
  }    
}


$suite  = new PHPUnit_TestSuite("testUserFunctions1");
$result =& PHPUnit::run($suite);
echo $result->toHTML();

?>
