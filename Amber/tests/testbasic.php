<?php

/**
*
* @package PHPReport
* @subpackage Tests
*
*/
ini_set('include_path', ini_get('include_path') . ':' . dirname(__FILE__). '/../../lib/');
ini_set('include_path', ini_get('include_path') . ':' . dirname(__FILE__). '/../../Amber/');

require_once 'unit.php';
require_once '../basic.php';

/**
*
* @package PHPReport
* @subpackage Tests
*
*/
class myTestCase extends PHPUnit_TestCase
{
  function assertEEquals($expected, $given, $message='')
  {
    parent::assertSame($expected, $given, $message . "  expected:" . $expected . "; given:" . $given . "; ");
  }
}

/**
*
* @package PHPReport
* @subpackage Tests
*
*/
class Basic_Format_internals extends myTestCase
{
  // test the lexer
  function test_Format()
  {
    $Format = new _Format('char');

    $this->assertEquals('char', $Format->fmt);
    $this->assertEquals(4, $Format->len);
  }
}

/**
*
* @package PHPReport
* @subpackage Tests
*
*/
class Basic_Format extends myTestCase
{
  function testFormat_Nullfmt()
  {
    $this->assertEquals('42',  Format(42, ''),   'Test1');
    $this->assertEquals('-42', Format(-42, ''),  'Test2');
    $this->assertEquals('',    Format('', ''),   'Test3');
    $this->assertEquals('0',   Format(0, ''),    'Test4');
    $this->assertEquals('',    Format(Null, ''), 'Test5');
  }
  function testFormat_NullValue()
  {
    $this->assertEEquals('',   Format(null,"x1;x2;x3"),     'Test1');

    $this->assertEEquals('x4',  Format(null,"x1;x2;x3;x4"), 'Test2a');
    $this->assertEEquals('',    Format(''  ,"x1;x2;x3;x4"), 'Test2b');
    $this->assertEEquals('x3',  Format(0   ,"x1;x2;x3;x4"), 'Test2c');
  }
  function testFormat_0Value()
  {
    $this->assertEquals('0',   Format(0, ''),            'Test1');
    $this->assertEquals('x3',  Format(0, "x1;x2;x3;x4"), 'Test2');

    $this->assertEquals('x1',  Format(0, "x1;x2;;x4"),   'Test3a');
    $this->assertEquals('x1',  Format(0, "x1"),          'Test3b');
  }




  function testFormat_Literals()
  {
    $this->assertEquals('   ', Format(42, '   '));
    $this->assertEquals('dd',  Format('2004-02-01', '\d\d'));
    $this->assertEquals('dd',  Format('2004-02-01', '"dd"'));
    $this->assertEquals('\\',  Format('2004-02-01', '\\\\'));
    $this->assertEquals('"',  Format('2004-02-01', '\"'));
  }





  //------------------
  // Date and Time
  //------------------

  function testFormat_Date_Basics()
  {
    $loc_de = setlocale (LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');

    # non-date: return value
    $this->assertEquals('asdf',          Format("asdf", "xyz 00000"), 'none-Date');

    # handle 'negative date' like 'positive date'
    $this->assertEquals('14.10.1066',   Format("1066-10-14", "dd.mm.yyyy;dd.mm.yy;-0-;-Null-"), 'MS neg-Date');

    $this->assertEquals('-0-',          Format("1899-12-30", "dd.mm.yyyy;dd.mm.yy;-0-;-Null-"), 'MS Zero-Date-1');
    $this->assertEquals('30.12.99--',   Format("1899-12-30", "dd.mm.yy--;dd.mm.yy;;-Null-"),    'MS Zero-Date-2');
    $this->assertEquals('',             Format(null,         "dd.mm.yyyy;dd.mm.yy;-0-;-Null-"), 'Null-Date-1');
    $this->assertEquals('',             Format(null,         "dd.mm.yyyy;dd.mm.yy;-0-"),        'Null-Date-2');
  }

  function testFormat_Date_DayC()
  {
    $loc_de = setlocale (LC_ALL, 'C');
    $this->assertEquals('1',               Format('2004-02-01', 'd')      ,'Test: d');
    $this->assertEquals('01',              Format('2004-02-01', 'dd')     ,'Test: dd');
    $this->assertEquals('Sun',             Format('2004-02-01', 'ddd')    ,'Test: ddd');
    $this->assertEquals('Sunday',          Format('2004-02-01', 'dddd')   ,'Test: dddd');
    $this->assertEquals('01.02.04',        Format('2004-02-01', 'ddddd')  ,'Test: ddddd');  //dd.mm.yy
    $this->assertEquals('01.February.2004',Format('2004-02-01', 'dddddd') ,'Test: dddddd'); //dd.mmmm.yyyy
  }
  function testFormat_Date_DayGerman()
  {
    $loc_de = setlocale (LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');
    $this->assertEquals('1',               Format('2004-02-01', 'd')      ,'Test-de: d');
    $this->assertEquals('01',              Format('2004-02-01', 'dd')     ,'Test-de: dd');
    $this->assertEquals('So',              Format('2004-02-01', 'ddd')    ,'Test-de: ddd');
    $this->assertEquals('Sonntag',         Format('2004-02-01', 'dddd')   ,'Test-de: dddd');
    $this->assertEquals('01.02.04',        Format('2004-02-01', 'ddddd')  ,'Test-de: ddddd'); //dd.mm.yy
    $this->assertEquals('01.Februar.2004', Format('2004-02-01', 'dddddd') ,'Test-de: dddddd'); //dd.mmmm.yyyy
  }
  function testFormat_Date_Month()
  {
    $loc_de = setlocale (LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');
    $this->assertEquals('2',               Format('2004-02-01', 'm'));
    $this->assertEquals('02',              Format('2004-02-01', 'mm'));
    $this->assertEquals('Feb',             Format('2004-02-01', 'mmm'));
    $this->assertEquals('Februar',         Format('2004-02-01', 'mmmm'));
  }
  function testFormat_Date_Year()
  {
    $this->assertEquals('1',               Format('2004-01-01', 'y'));
    $this->assertEquals('32',              Format('2004-02-01', 'y'));
    $this->assertEquals('366',             Format('2004-12-31', 'y'));
    $this->assertEquals('04',              Format('2004-01-01', 'yy'));
    $this->assertEquals('2004',            Format('2004-01-01', 'yyyy'));
  }

  function testFormat_Date_Time()
  {
    $this->assertEquals('6',               Format('06:07:08', 'h'),  'Test: h');
    $this->assertEquals('06',              Format('06:07:08', 'hh'), 'Test: hh');
    $this->assertEquals('7',               Format('06:07:08', 'n'),  'Test: n');
    $this->assertEquals('07',              Format('06:07:08', 'nn'), 'Test: nn');
    $this->assertEquals('8',               Format('06:07:08', 's'),  'Test: s');
    $this->assertEquals('08',              Format('06:07:08', 'ss'), 'Test: ss');

  }

  function testFormat_Date_misc()
  {
    setlocale (LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');
    $this->assertEquals('Hallo: Mo, 11.Mai.1959',  Format('1959-05-11', "\"Hallo:\" ddd, dd.mmm.yyyy"));
    setlocale (LC_ALL, 'C');
    $this->assertEquals('Hello: Mon, 11.May.1959', Format('1959-05-11', "\"Hello:\" ddd, dd.mmm.yyyy"));

    setlocale (LC_ALL, 'C');
    $this->assertEquals('Hello: Mon, 11.05.1959', Format('1959-05-11', "\"Hello:\" ddd, dd/mm/yyyy"));
    $this->assertEquals('Hello: Mon, 05/11/1959', Format('1959-05-11', "\"Hello:\" ddd, mm/dd/yyyy"));
    setlocale (LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');
    $this->assertEquals('Hello: Mo, 11.05.1959', Format('1959-05-11', "\"Hello:\" ddd, dd/mm/yyyy"));
    $this->assertEquals('Hello: Mo, 05/11/1959', Format('1959-05-11', "\"Hello:\" ddd, mm/dd/yyyy"));

  }



  //------------------
  // Numbers
  //------------------
  function testFormat_Numeric_Basics()
  {
    $this->assertEquals('asdf',             Format('asdf', "00000"));
    $this->assertEquals('946681200',        Format('2000-01-01', "00000"));
    $this->assertEquals('asdf',             Format("asdf", "xxx 00000"));
    $this->assertEquals('1x1',              Format( 1,    "#x1;x2;x3;x4"), "Test3-+");
    $this->assertEquals('x2',               Format(-1,    "#x1;x2;x3;x4"), "Test3--");
    $this->assertEquals('x3',               Format( 0,    "#x1;x2;x3;x4"), "Test3-0");
    $this->assertEquals('x4',               Format( null, "#x1;x2;x3;x4"), "Test3-Null");
  }
  function testFormat_Numeric_Vorkommastellen()
  {
    $this->assertEEquals('00009',           Format(9, "00000"),    'Test1');
    $this->assertEEquals('-013',            Format(-13, "000"),    'Test2a');
    $this->assertEEquals('-Val 13',         Format(-13, "Val #0"), 'Test2b');
    $this->assertEEquals('123_4_5_6',       Format(123456, "0_#_#_0"), 'Test4a');
    $this->assertEEquals('0_1_2_3',         Format(123,    "0_#_#_0"), 'Test4b');
  }

  function testFormat_Numeric_Nachkommastellen_Runden()
  {
    setlocale (LC_ALL, 'C');
    $locale_info = localeconv();
    $this->assertEquals('123456789.12',     Format(123456789.12499, "0.00"), "Test 1 - C");
    $this->assertEquals('123456789.14',     Format(123456789.135,   "0.00"), "Test 2 - C");
    $this->assertEquals('123456789.14',     Format(123456789.145,   "0.00"), "Test 3 - C");

    setlocale (LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');

    $this->assertEquals('123456789,12',     Format(123456789.12499, "0.00"), "Test 1 - DE");
    $this->assertEquals('123456789,14',     Format(123456789.135,   "0.00"), "Test 2 - DE");
    $this->assertEquals('123456789,14',     Format(123456789.145,   "0.00"), "Test 3 - DE");
  }

  function testFormat_Numeric_Exponent()
  {
    setlocale (LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');
    $this->assertEquals('1,23E+08',         Format(123456789.12499, "0.00E+00"), "Test 1 - DE");
    $this->assertEquals('1,23e+08',         Format(123456789.12499, "0.00e+00"), "Test 2 - DE");
    $this->assertEquals('1,23E08',          Format(123456789.12499, "0.00E-00"), "Test 3 - DE");
    $this->assertEquals('1,23E8',           Format(123456789.12499, "0.00E-"),   "Test 4 - DE");
    $this->assertEquals('xxx1,23E8xxx',     Format(123456789.12499, "xxx0.00E-xxx"),   "Test 4 - DE");

 }

  function testFormat_Numeric_ThousandSeparator()
  {
    setlocale (LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');
    $this->assertEquals('123.456.789',         Format(123456789.12499, "#,0"), "Test 1 - DE");
    $this->assertEquals('123.456.789',         Format(123456789.12499, "##,0"), "Test 2 - DE");
    $this->assertEquals('123.456.789',         Format(123456789.12499, "###,0"), "Test 3 - DE");
    $this->assertEquals('123.456.789',         Format(123456789.12499, "####,0"), "Test 4 - DE");
    $this->assertEquals('123.456.789',         Format(123456789.12499, "#####,0"), "Test 5 - DE");
    $this->assertEquals('123.456.789',         Format(123456789.12499, "###############,0"), "Test 6 - DE");
  }

  function testFormat_Numeric_misc()
  {
    setlocale (LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');
    $this->assertEquals('123456789,9',       Format(123456789.9, ""),                  'Test1');
    $this->assertEquals('4 2,3E+002 46',     Format(4234.567, "0 0.0E+000 00"),        'Test2');

    $this->assertEquals('1234   56-789-000', Format(123456789000, "#   0#-###-##0"),   'Test3');
    $this->assertEquals('1   23-456-789e+3', Format(123456789000, "#   0#-###-##0e+"), 'Test4');
    $this->assertEquals('1   20-000-000E-7', Format(12,           "#   0#-###-##0E+"), 'Test5');
  }

  function testStdFormat()
  {
    setlocale (LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');
    $this->assertEquals('03.02.2004 13:34:00',        Format("2004-2-3 13:34:00","General Date"), 'General Date');
    $this->assertEquals('Dienstag, 3. Februar 2004',  Format("2004-2-3 13:34:00", "Long Date"),   'Long Date');
    $this->assertEquals('03. Feb. 04',                Format("2004-2-3 13:34:00", "Medium Date"), 'Medium Date');
    $this->assertEquals('03.02.2004',                 Format("2004-2-3 13:34:00", "Short Date"),  'Short Date');
    $this->assertEquals('13:34:00',                   Format("2004-2-3 13:34:00", "Long Time"),   'Long Time');
    $this->assertEquals('13:34',                      Format("2004-2-3 13:34:00", "Medium Time"), 'Medium Time');
    $this->assertEquals('13:34',                      Format("2004-2-3 13:34:00", "Short Time"),  'Short Time');

    $this->assertEquals('1234,5678',     Format(1234.5678, "General Number"),  "General Number");
    $this->assertEquals('1.234,57 DM',   Format(1234.5678, "Currency"),        "Currency");
    $this->assertEquals('1.234,57 €',    Format(1234.5678, "Euro"),            "Euro");
    $this->assertEquals('1.234,57',      Format(1234.5678, "Standard"),         "Standard");
    $this->assertEquals('123456,78%',    Format(1234.5678, "Percent"),   "Percent");
    $this->assertEquals('1,23E+03',     Format(1234.5678, "Scientific"),   "Scientific");

    $this->assertEquals('Wahr',     Format(true, "True/False"), "True/False: true");
    $this->assertEquals('Ja',       Format(true, "Yes/No"),     "Yes/No: true");
    $this->assertEquals('Ein',      Format(true, "On/Off"),     "On/Off: true");
    $this->assertEquals('Falsch',   Format(false, "True/False"), "True/False: false");
    $this->assertEquals('Nein',     Format(false, "Yes/No"),     "Yes/No: false");
    $this->assertEquals('Aus',      Format(false, "On/Off"),     "On/Off: false");



  }


/*  function testFormat_String()
  {
    $this->assertEquals('asd', Format('asdfjklo', '@@@;@@@@;"";"NULL"'));
    $this->assertEquals('Hello: World', Format('World is round', '"Hello:" @@@@@'));
  }

*/


  function testFormat_Mix()
  // Format mixes
  {
  #  $this->assertEquals('a s-  tt.mm.jjdf',  Format("as df", "@ @-@ tt.mm.jj"), 'Test1');
  #  $this->assertEquals('1 2-3 tt.mm.jj456',  Format('123456', '@ @-@ tt.mm.jj'), 'Test2');

    $this->assertEquals('123456',       Format("123456", "#"), 'Test11');
  #  $this->assertEquals('12-34#56',     Format("123456", "&&-&&#"), 'Test12');
    $this->assertEquals('12345&&-&&6',  Format("123456", "#&&-&&#"), 'Test13');
    $this->assertEquals('123456&&-&&',  Format("123456", "#&&-&&"), 'Test14');


    $this->assertEquals('1#&&-&&',  Format("123456", "m#&&-&&"), 'Test21');


    //###dispens### $this->assertEquals('03.01.2238#&&-&&',  Format("123456", "dd.mm.yyyy#&&-&&"), 'Test22');

    // there are actually 3 distinct Format functions for date, number and string
    // each behaves diffrent. decision, which to call is made by the first Format relevant character on the Left

    //###dispens### $this->assertEquals(' birt03a3: #&&-&& 03.01.2238',      Format("123456", " birthday: #&&-&& dd.mm.yyyy"), 'Test31');
  #  $this->assertEquals('birthday: #34-56 dd.mm.yyyy',       Format("123456", "!birthday: #&&-&& dd.mm.yyyy"), 'Test32');
    $this->assertEquals('12345birthday: 6&&-&& dd,mm.yyyy',  Format("123456", "#birthday: #&&-&& dd.mm.yyyy"), 'Test33');


    // separation of Format parts (positive, negative, zero and NULL) is done inside the different Format functions
    $this->assertEquals('123456,00',      Format("+123456", "#.00;&&-&&-&&"), 'Test41');
    $this->assertEquals('&&-&&-&&',       Format("-123456", "#.00;&&-&&-&&"), 'Test42');
  #  $this->assertEquals('+1-23-456',      Format("+123456", "&&-&&-&&;#.00"), 'Test43');
  #  $this->assertEquals('-1-23-456',      Format("-123456", "&&-&&-&&;#.00"), 'Test44');

    //###dispens### $this->assertEquals('.&&-00-01',      Format("2004-01-01","/&&-00-dd"), 'Test51');
    $this->assertEquals(':&&-00-01',      Format("2004-01-01",":&&-00-dd"), 'Test52');
  }

  function testiif()
  {
    $this->assertEquals('true',      Iif(true,'true','false'),    'Test1');
    $this->assertEquals('true',      Iif(-1,'true','false'),      'Test2');

    $this->assertEquals('false',     Iif(false,'true','false'),   'Test3');
    $this->assertEquals('false',     Iif(null,'true','false'),    'Test4');
    $this->assertEquals('false',     Iif('','true','false'),      'Test5');
    $this->assertEquals('false',     Iif(0,'true','false'),       'Test6');
  }

  function testmid()
  {
    $this->assertEquals('s',       Mid("asdf", 2, 1), 'Test1');
    $this->assertEquals('sdf',     Mid("asdf", 2, 5), 'Test2');
    $this->assertEquals('df',      Mid("asdf", 3),    'Test3');
    $this->assertEquals('asdf',    Mid("asdf", 1),    'Test4');
  }

  function testLeft()
  {
    $this->assertEquals('as',   Left("asdf", 2), 'Test1');
    $this->assertEquals('',     Left("asdf", 0), 'Test2');
  }

  function testRight()
  {
    $this->assertEquals('df',   Right("asdf", 2), 'Test1');
    $this->assertEquals('',     Right("asdf", 0), 'Test2');
  }
}

$suite  = new PHPUnit_TestSuite("Basic_Format_internals");
$result = PHPUnit::run($suite);
echo $result -> toHTML();

$suite  = new PHPUnit_TestSuite("Basic_Format");
$result = PHPUnit::run($suite);
echo $result -> toHTML();
?>
