<?php

/**
*
* @package Amber
* @subpackage Tests
*
*/

ini_set('include_path', ini_get('include_path') . ':' . dirname(__FILE__). '/../../Amber/');

require_once 'unit.php';
require_once '../Amber.php';

/**
*
* @package Amber
* @subpackage Tests
*
*/
class testReport extends PHPUnit_TestCase
{
  function test_makeSqlFilter()
  {
    $this->assertEquals('select * from table where (1 = 1) AND (NPNr = 13)',
      report::_makeSqlFilter('select * from table where 1=1', 'NPNr = 13'),      'Test1a');
    $this->assertEquals('select * from table where (1 = 1) AND (NPNr = 13)',
      report::_makeSqlFilter('select * from table where 1=1;', 'NPNr = 13'),     'Test1b');

    // No semicolon
    $this->assertEquals('select * from table where (1 = 1) AND (NPNr = 13) group by NPNR',
      report::_makeSqlFilter('select * from table where 1=1 group by NPNR', 'NPNr = 13'),      'Test2a');

    // One semicolon
    $this->assertEquals('select * from table where (1 = 1) AND (NPNr = 13) group by NPNR',
      report::_makeSqlFilter('select * from table where 1=1 group by NPNR;', 'NPNr = 13'),     'Test2a');

    // Two semicolons
    $this->assertEquals('select * from table where (1 = 1) AND (NPNr = 13) group by NPNR',
      report::_makeSqlFilter('select * from table where 1=1 group by NPNR;;', 'NPNr = 13'),     'Test2c');

    // Two semicolons separated by a blank
    $this->assertEquals('select * from table where (1 = 1) AND (NPNr = 13) group by NPNR',
      report::_makeSqlFilter('select * from table where 1=1 group by NPNR; ;', 'NPNr = 13'),     'Test2d');

    // Invalid statement but has to be parsed and modified as expected
    // 1. all semicolons and whitespaces at the end have to be removed
    // 2. semicolon in the middle must remain
    $this->assertEquals('select * from table where (; a) AND (NPNr = 13)',
      report::_makeSqlFilter('select * from table where ; a ;;; ;;;', 'NPNr = 13'),     'Test3a');
  }

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

  function test_ReportRuns()
  {
    $amber =& $this->setAmberConfig();

    ob_start();
    $amber->OpenReport('Bericht1', AC_NORMAL, '', 'html');
    $s = trim(ob_get_contents());
    ob_end_clean();
    $this->assertEquals('[<!DOCTYPE ]', "[" . substr($s, 0, 10) . "]",    'Test1a html, normal: <html> without leading stuff');
    $this->assertEquals("[</html>]", "[" . substr($s, -7) . "]",   'Test1b html, normal: </html>\n');

    ob_start();
    $amber->OpenReport('Bericht1', AC_DESIGN, '', 'html');
    $s = trim(ob_get_contents());
    ob_end_clean();
    $this->assertEquals('[<!DOCTYPE ]', "[" . substr($s, 0, 10) . "]",    'Test2a html, design: <html> without leading stuff');
    $this->assertEquals("[</html>]", "[" . substr($s, -7) . "]",   'Test2b html, design: </html>\n');

    ob_start();
    $amber->OpenReport('Bericht1', AC_NORMAL, '', 'testpdf');
    $s = ob_get_contents();
    ob_end_clean();
    $this->assertEquals('[%PDF-]', "[" . substr($s, 0, 5) . "]",  'Test3a pdf, normal');
    $this->assertEquals("[%%EOF\n]", "[" . substr($s, -6) . "]",     'Test3b pdf, normal');

    ob_start();
    $amber->OpenReport('Bericht1', AC_DESIGN, '', 'testpdf');
    $s = ob_get_contents();
    ob_end_clean();
    $this->assertEquals('[%PDF-]', "[" . substr($s, 0, 5) . "]",  'Test4a pdf, Design');
    $this->assertEquals("[%%EOF\n]", "[" . substr($s, -6) . "]",     'Test4b pdf, Design');


  }
}

$suite  = new PHPUnit_TestSuite("testReport");
$result = PHPUnit::run($suite);
echo $result->toHTML();

?>
