<?php

ini_set('include_path', ini_get('include_path') . ':' . dirname(__FILE__). '/../../lib/');
ini_set('include_path', ini_get('include_path') . ':' . dirname(__FILE__). '/../../Amber/');

require_once '../Report.php';
require_once 'unit.php';

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

}

$suite  = new PHPUnit_TestSuite("testReport");
$result = PHPUnit::run($suite);
echo $result -> toHTML();

?>
