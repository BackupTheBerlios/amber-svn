<?php

/**
 *
 * @package PHPReport
 * @subpackage Exporter
 *
 */

ExporterFactory::register('typo3', 'ExporterTypo3');

/**
 *
 * @package PHPReport
 * @subpackage Exporter
 *
 */
class ExporterTypo3 extends Exporter
{

  var $type = 'typo3';
  var $_pageNo = 1;
  var $_blankPage;

  var $_CtrlStdValues;

  var $_posY;

  // Report - Typo3

  function startReport(&$report)
  {
    parent::startReport($report);
    $this->_blankPage = true;

    $ret = "\t<style type=\"text/css\">\n";
    if (is_array($report->Controls)) {
      foreach ($report->Controls as $ctrl) {
        $ctrl->_exporter->_saveStdValues($ctrl);
        $ret .= $this->getCssStyle($ctrl) . "\n";
      }
    }
    $ret .= "</style>\n";
    echo $ret;
  }

  function endReport(&$report)
  {
    parent::endReport($report);
  }

  // Section - html

  /**
  *
  * for design mode: print border between sections
  *
  * @access public
  * @param  string name of header to print
  * @return integer height printed in twips
  */
  function sectionPrintDesignHeader($text)
  {
    $cheatHeight = 29;
    $cheatWidth  = 59;

    $height = 12; //12pt

    $out .= "\t<div name=\"{$sec->Name}\" style = \"position: absolute; overflow: hidden; ";
    $out .= 'top: '    . $this->_html_twips($this->_posY) .'; ';
    $out .= 'height: ' . $height . 'pt; ';
    $out .= 'left: 0; ';
    $out .= 'width: 100%; ';
    $out .= 'background-color: #999999; ';
    $out .= 'font: 10 Arial; ';
    $out .= 'border: #000000 solid 1px; border-top: #cccccc solid 1px; border-left: #cccccc solid 1px; ';
    $out .= 'margin-bottom: 1px; margin-top: 1px;';
    $out .= "\">\n";
    $out .= $text;
    $out .= "\t</div>\n";
    echo $out;
    $this->_posY += ($height + 2) * 20;
  }

  function startSection(&$section, $width, &$buffer)
  {
    if (!(($section->_PagePart) or ($this->DesignMode))) {
      if ($this->_blankPage) {
        $this->_blankPage = false;
        $out = "\t<div name=\"TopMargin\"style = \"position: absolute; overflow: hidden; ";
        $out .= 'top: '    . $this->_html_twips($this->_posY) .'; ';
        $out .= 'height: ' . $this->_html_twips($this->_report->TopMargin + 20) . '; ';
        $out .= 'left: 0; ';
        $out .= 'width: '  . $this->_html_twips($this->_report->LeftMargin + $this->_report->Width + $this->_report->RightMargin) . '; ';
        $out .= 'background-color: #ffffff; ';
        $out .= "\">&nbsp;</div>\n";
        echo $out;
        $this->_posY += $this->_report->TopMargin;
        $this->_report->_printNormalSection('PageHeader'); // FIXME: this has to be done by the Report class!!!
      }
    }
    $buffer = null;
  }

  function endSection(&$section, $height, &$buffer)
  {
    parent::startSection($section, $width, $buffer);
    $cheatWidth  = 59; // cheat: add 1.5pt to height and 3pt to width so borders get printed in Mozilla ###FIX ME
    if ($height == 0) {
      $cheatHeight = 0;
    } else {
      $cheatHeight = 15;
    }
    if (!$this->DesignMode) {
      $out = "\t<div name=\"{$section->Name}-border\"style = \"position: absolute; overflow: hidden; ";
      $out .= 'top: '    . $this->_html_twips($this->_posY) .'; ';
      $out .= 'height: ' . $this->_html_twips($height + $cheatHeight) . '; ';
      $out .= 'left: 0; ';
      $out .= 'width: '  . $this->_html_twips($this->_report->LeftMargin + $this->_report->Width + $this->_report->RightMargin) . '; ';
      $out .= 'background-color: #ffffff; ';
      $out .= "\">\n";
      $out .= "\t<div name=\"{$section->Name}\" style = \"position: absolute; overflow: hidden; ";
      $out .= 'left: '   . $this->_html_twips($this->_report->LeftMargin) . '; ';
      $out .= 'height: ' . $this->_html_twips($height) . '; ';
      $out .= 'width: '  . $this->_html_twips($this->_report->Width + $cheatWidth) . '; ';
      $out .= 'background-color: ' . $this->_html_color($section->BackColor) . '; ';
      $out .= "\">\n";
    } else {
      $out .= "\t<div name=\"{$section->Name}\" style = \"position: absolute; overflow: hidden; ";
      $out .= 'top: '    . $this->_html_twips($this->_posY) .'; ';
      $out .= 'height: ' . $this->_html_twips($height + $cheatHeight) . '; ';
      $out .= 'left: 0; ';
      $out .= 'width: '  . $this->_html_twips($this->_report->Width + $cheatWidth) . '; ';
      $out .= 'background-color: ' . $this->_html_color($section->BackColor) . '; ';
      $out .= "\">\n";
    }

    if ($this->DesignMode) {
        $out .= $buffer;
    } else {
      $this->onPrint($cancel, 1);
      if (!$cancel) {
        $out .= $buffer;
      }
    }

    if ($this->DesignMode) {
      $out .= "\t</div>\n";
    } else {
      $out .= "\t</div></div>\n";
    }
    echo $out;
    $this->_posY += $height;
    parent::endSection($section, $height, $buffer);
  }

  // Page handling - html

  function newPage()
  {
    if ((!$this->_blankPage) and (!$this->DesignMode)) {
      $this->_report->_printNormalSection('PageFooter'); // FIXME: this has to be done by the Report class!!!
      $out = "\t<div name=\"BottomMargin\"style = \"position: absolute; overflow: hidden; page-break-after: always; ";
      $out .= 'top: '    . $this->_html_twips($this->_posY) .'; ';
      $out .= 'height: ' . $this->_html_twips($this->_report->BottomMargin + 20) . '; ';
      $out .= 'left: 0; ';
      $out .= 'width: '  . $this->_html_twips($this->_report->LeftMargin + $this->_report->Width + $this->_report->RightMargin) . '; ';
      $out .= 'background-color: #ffffff; ';
      $out .= "\">&nbsp;</div>\n";
      echo $out;
      $this->_posY += $this->_report->BottomMargin;
      $this->_report->OnPage();
      $this->_pageNo++;
    }
    $this->_blankPage = true;
  }

  function page()
  {
    return $this->_pageNo;
  }

  // Controls - html

  function setControlExporter(&$ctrl)
  {
    $classList = array(
      #'null'      => 'NullExporterHtml'
      'label'     => 'LabelExporterHtml',
      'rectangle' => 'RectangleExporterHtml',
      'textbox'   => 'TextBoxExporterHtml',
      'subreport' => 'SubReportExporterHtml',
      'combobox' => 'ComboBoxExporterHtml',
      'dummy' => 'DummyExporterHtml'
    );
    $type = strtolower(get_class($ctrl));
    if (!array_key_exists($type, $classList)) {
      $type = 'SubReport';  // FIXME: Null-Object for unknown Controltypes
    }
    $objName = $classList[$type];
    $ctrl->_exporter =& new $objName;
  }

  // Helper functions - html

  function dump($var)
  {
    echo '<div style=" position: absolute; overflow: hidden; align: center; width: 90%; top: ' . $this->_html_twips($this->_posY) . '"><pre style="text-align: left; width: 80%; border: solid 1px #ff0000; font-size: 9pt; background-color: #ffffff; padding: 5px;">' . htmlentities(print_r($var, 1)) . '</pre></div>';
  }

  function _html_twips($twips)
  {
    if (!is_numeric($twips)) {
      return '0px';
    }

    return number_format(__SCALE__ * $twips / 15, 0, '.', '') . 'px';
    //return number_format(__SCALE__ * $twips / 20, 0, '.', '') . 'pt';
    //return number_format(__SCALE__ * $twips / 1440, 5, '.', '') . 'in';
    //return number_format(__SCALE__ * $twips / 1440 * 2.54, 4, '.', '') . 'cm';
  }

  function _html_color($color)
  {
    if (!is_numeric($color)) {
      return '#ff0000';
    }

    return '#' . substr(('000000' . dechex($color)), -6);
  }

  // Local functions

  function getCssStyle(&$control)
  {
    $control->Properties['isVisible'] = $control->Properties['Visible'];
    $nil = array('ForeColor' => 16777216, 'BackColor' => 16777216, 'BorderColor' => 16777216, 'BorderWidth' => -9999); // illegal values
    return '.s' . $control->id . "\t/* " . $control->Name . ' */ { position: absolute; overflow:hidden; ' . $control->_exporter->getStyle($control, $control->Properties, $nil) . '}';
  }
}


?>
