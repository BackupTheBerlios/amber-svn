<?php


/////////////////////////////////////////
//
// testReportConstant -- usage
//
// before starting some refactoring, clear the subfolder adhoc from any files, 
// then run this test. You will get a lot of errors, ignore them for now.
// for each report tested, 4 files will appear in the folder:
// html-normal, html-design, pdf-normal, pdf-design, each with the result of the report.
// now rename these files from *.new to *.old
// 
// now you can start refactoring. 
// Each time You run this test, a bunch of *.new files will be created from Your code
// and compared to the *.old files. Any changes produces an error.
//
// You may wish to compare *.old and *.new with a diff tool.
//
// Be careful not to place any reports with sensible data in this test 
// the result might get published 
//
// Add new reports to test at the end of the class
//
//////////////////////////////////////////


/**
*
* @package Amber
* @subpackage Tests
*
*/

ini_set('include_path', ini_get('include_path') . ':' . dirname(__FILE__). '/../../Amber/');
ini_set('max_execution_time', 600);
ini_set('memory_limit', '48M');

require_once '../Report.php';
require_once 'unit.php';
require_once '../Amber.php';

/**
*
* @package Amber
* @subpackage Tests
*
* usage: 
*/
class testReportConstant extends PHPUnit_TestCase
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

  function str2file($filename, $somecontent)
  {
    if (!$handle = fopen($filename, "w+")) {
      print "Kann die Datei $filename nicht �fnen";
    } elseif (!fwrite($handle, $somecontent)) {
      print "Kann in die Datei $filename nicht schreiben";
    } else {
      fclose($handle);
    }
  }

  function removeCreationDateFromPdfString(&$s)
  {
    $s = ereg_replace('/CreationDate \(D:[0-9]{14}\)','', $s);
  }
  
  function Compare($reportname)
  {
    $path = 'adhoc/';

    print 'Test1 Html, Normal ' . $reportname . "<br>";
    $amber =& $this->setAmberConfig();
    ob_start();
    $amber->OpenReport($reportname, AC_NORMAL, '', 'html');
    $s = ob_get_contents();
    ob_end_clean();
    $this->str2file($path . $reportname."-HtmlNormal.new", $s);
    $f = file_get_contents ($path . $reportname."-HtmlNormal.old");
    $this->assertEquals($f, $s,  'Test1 Html, Normal ' . $reportname);

    print 'Test2 Html, Design ' . $reportname . "<br>";
    $amber =& $this->setAmberConfig();
    ob_start();
    $amber->OpenReport($reportname, AC_DESIGN, '', 'html');
    $s = ob_get_contents();
    ob_end_clean();
    $this->str2file($path . $reportname."-HtmlDesign.new", $s);
    $f = file_get_contents ($path . $reportname."-HtmlDesign.old");
    $this->assertEquals($f, $s,  'Test2 Html, Design ' . $reportname);

    print 'Test3 pdf, Design ' . $reportname . "<br>";
    $amber =& $this->setAmberConfig();
    ob_start();
    $amber->OpenReport($reportname, AC_DESIGN, '', 'testpdf');
    $s = ob_get_contents();
    ob_end_clean();
    $this->removeCreationDateFromPdfString($s);
    $this->str2file($path . $reportname."-PdfDesign.new", $s);
    $f = file_get_contents ($path . $reportname."-PdfDesign.old");
    $this->assertEquals($f, $s,  'Test3 pdf, Design ' . $reportname);

    print 'Test4 pdf, Normal ' . $reportname . "<br>";
    $amber =& $this->setAmberConfig();
    ob_start();
    $amber->OpenReport($reportname, AC_NORMAL, '', 'testpdf');
    $s = ob_get_contents();
    ob_end_clean();
    $this->removeCreationDateFromPdfString($s);
    $this->str2file($path . $reportname."-PdfNormal.new", $s);
    $f = file_get_contents ($path . $reportname."-PdfNormal.old");
    $this->assertEquals($f, $s,  'Test4 pdf, Normal ' . $reportname);
    
    print date('h.i.s');
  }
  
  function test_TestReport()
  {
    $this->Compare('TestReport');
  }
  
}

print "This is not a normal test. If You get any errors, check comments inside testReportConstant.php first!<br>\n";

$suite  = new PHPUnit_TestSuite("testReportConstant");
$result = PHPUnit::run($suite);
echo $result->toHTML();

?>
