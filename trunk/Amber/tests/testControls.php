<?php

/**
*
* @package Amber
* @subpackage Tests
*
*/

require_once '../Controls/ControlFactory.php';
require_once '../Controls/Controls.php';
require_once 'unit.php';

/**
*
* @package Amber
* @subpackage Tests
*
*/
class testControls extends PHPUnit_TestCase
{
  var $ctrl;

  function setUp()
  {
    $this->ctrl = new Control(0);
  }

  function testVisibility()
  {
    $this->assertTrue($this->ctrl->isVisible(), 'default value');
    $this->ctrl->Visible = false;
    $this->assertFalse($this->ctrl->isVisible(), 'Visible set to false');
  }
  
  function testPropertyLink()
  {
    $this->assertEquals('', $this->ctrl->Properties['Name'], 'PropertyLink1');
    $this->ctrl->Properties['Name'] = 'Test';
    $this->assertEquals('Test', $this->ctrl->Properties['Name'], 'PropertyLink2');
    $this->assertEquals('Test', $this->ctrl->Name, 'PropertyLink3');
  }
}

$suites[]  = new PHPUnit_TestSuite("testControls");
?>

