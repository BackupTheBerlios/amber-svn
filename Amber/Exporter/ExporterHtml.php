<?php

/**
 *
 * @package PHPReport
 * @subpackage Exporter
 *
 */

define('__SCALE__', 1.1); // used in html_twips(), FontBox::load()

ExporterFactory::register('html', 'ExporterHtml');

/**
 *
 * @package PHPReport
 * @subpackage Exporter
 *
 */
class ExporterHtml extends Exporter
{

  var $type = 'html';
  var $_pageNo = 1;
  var $_blankPage;

  var $_CtrlStdValues;

  var $_posY; //

  // Report - html

  function startReport(&$report)
  {
    parent::startReport($report);
    $this->_blankPage = true;

    $ret = "<html>\n<head>\n";
    $ret .= "\t<title>" . $this->_docTitle . "</title>\n";
    $ret .= "\t<style type=\"text/css\">\n";
    if (is_array($report->Controls)) {
      foreach ($report->Controls as $ctrl) {
        $ctrl->_exporter->_saveStdValues($ctrl);
        $ret .= $this->getCssStyle($ctrl) . "\n";
      }
    }
    $ret .= "</style>\n";
    $ret .= "</head>\n";
    $ret .= "<body style=\"background-color: #aaaaaa;\">\n";
    echo $ret;
  }

  function endReport(&$report)
  {
    parent::endReport($report);
    echo "</body>\n</html>\n";
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
      $type = 'subreport';  // FIXME: Null-Object for unknown Controltypes
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
#    $control->Properties['isVisible'] = $control->isVisible();
    $control->Properties['isVisible'] = $control->Properties['Visible'];
    $nil = array('ForeColor' => 16777216, 'BackColor' => 16777216, 'BorderColor' => 16777216, 'BorderWidth' => -9999); // illegal values
    return '.s' . $control->id . "\t/* " . $control->Name . ' */ { position: absolute; overflow:hidden; ' . $control->_exporter->getStyle($control, $control->Properties, $nil) . '}';
  }
}

/**
 *
 * @package PHPReport
 * @subpackage Exporter
 *
 */
Class ControlExporterHtml
{
  var $_stdValues;
  function _saveStdValues(&$ctrl)
  {
    // Attributwerte als Standard sichern
    foreach ($ctrl->Properties as $key => $value) {
      $this->_stdValues[$key] = $ctrl->Properties[$key];
    }
  }

  function printNormal(&$control, &$buffer, $content)
  {
    $buffer .= $this->getTag($control, $content);
  }

  function printDesign(&$control, &$buffer, $content)
  {
    $this->printNormal($control, $buffer, $content);
  }

  function getTag(&$control, $value=Null)
  {
    //$out =  "\t\t<!-- " . $control->Name . " --><div class=\"s" . $control->id . '"';
    $out =  "\t\t<div class=\"s" . $control->id . '"';

    $this->_stdValues['Value'] =  $control->Properties['Value'];
    $control->Properties['isVisible'] = $control->isVisible();
    if ($control->Properties == $this->_stdValues) {
      //echo "###GLEICH###";
    } else {
      $style = $this->getStyle($control, $control->Properties, $this->_stdValues);
      $out .= ' style="' . trim($style) . '"';
    }
    $out .= ">";

    $out .= isset($value) ? htmlspecialchars($value) : '&nbsp;';
    $out .= "</div>\n";

    return $out;
  }

  function getStyle(&$ctrl, &$value, &$std)
  {
    $out = '';

    // Position
    if ($value['Top'] <> $std['Top']) {
      $out .= 'top: ' . ExporterHTML::_html_twips($ctrl->Properties['Top']) . '; ';
    }
    if ($value['Left'] <> $std['Left']) {
      $out .= 'left: ' . ExporterHTML::_html_twips($ctrl->Properties['Left']) . '; ';
    }
    if ($value['Height'] <> $std['Height']) {
      $out .= 'height: ' . ExporterHTML::_html_twips($ctrl->Properties['Height']) . '; ';
    }
    if ($value['Width'] <> $std['Width']) {
      $out .= 'width: ' . ExporterHTML::_html_twips($ctrl->Properties['Width']) . '; ';
    }

    // Backstyle
    if (($value['BackColor'] <> $std['BackColor']) || ($value['BackStyle'] <> $std['BackStyle'])) {
      if ($ctrl->Properties['BackStyle'] != 0) {
          $out .= 'background-color: ' . ExporterHTML::_html_color($ctrl->Properties['BackColor']) . '; ';
      }
    }

    // Border
    if ($value['BorderWidth'] <> $std['BorderWidth']) {
      if ($value['BorderWidth'] == 0) {
        $out .= 'border-width: 1px; ';
      } else {
        $out .= 'border-width:' . $value['BorderWidth'] . 'pt; ';
      }
    }
    if ($value['BorderColor'] <> $std['BorderColor']) {
        $out .= 'border-color:' . ExporterHTML::_html_color($ctrl->Properties['BorderColor']) . '; ';
    }
    if ($value['BorderStyle'] <> $std['BorderStyle']) {
      $out .= 'border-style:' . $this->_html_borderstyle($ctrl->Properties['BorderStyle'], $ctrl->Properties['BorderLineStyle']) . '; ';
    }

    if ($value['zIndex'] <> $std['zIndex']) {
      $out .= 'z-index: ' . $ctrl->Properties['zIndex'] . '; ';
    }

    // Visible
#    if ($value['isVisible'] <> $std['isVisible']) {
      if ($ctrl->Properties['isVisible'] == false) {
        $out .= 'visibility: hidden; ';
      } else {
        $out .= 'visibility: visible; ';
      }
#    }
    return $out;
  }

  function _html_borderstyle($style, $linestyle)
  {
    // 0 transparent
    // 1 normal
    // 2 strichlinie
    // 3 kurze strichlinien
    // 4 punkte
    // 5 wenig punkte
    // 6  ..-..-..
    // 7 --.--.--
    $styles = array(0 => 'none', 'solid', 'dashed', 'dashed', 'dotted', 'dotted', 'dotted', 'dashed');
    if (!isset($styles[$style])) {
      return 'solid';
    }

    return $styles[$style];
  }

  function _html_textalign($textalign)
  {
    $alignments = array(1 => 'left', 'center', 'right', 'justify');

    if (!isset($alignments[$textalign])) {
      return 'left';
    }
    return $alignments[$textalign];
  }

}

/**
 *
 * @package PHPReport
 * @subpackage Exporter
 *
 */
class RectangleExporterHtml extends ControlExporterHtml
{
}

/**
 *
 * @package PHPReport
 * @subpackage Exporter
 *
 */
class FontBoxExporterHtml extends ControlExporterHtml
{
  function getStyle(&$ctrl, &$value, &$std)
  {
    $out = parent::getStyle($ctrl, $value, $std);

    if ($value['FontItalic'] <> $std['FontItalic']) {
      if ($ctrl->Properties['FontItalic'] == true) {
        $out .= 'font-style: italic; ';
      } else {
        $out .= 'font-style: normal; ';
      }
    }

    if ($value['FontWeight'] <> $std['FontWeight']) {
      if ($ctrl->Properties['FontWeight'] == 400) {
        $out .= 'font-weight: normal; ';
      } else {
        $out .= 'font-weight: ' . $ctrl->Properties['FontWeight'] . '; ';
      }
    }

    if ($value['FontSize'] <> $std['FontSize']) {
      $out .= 'font-size: ' . floor(__SCALE__ * $ctrl->Properties['FontSize']) . 'pt; ';
    }

    if ($value['FontName'] <> $std['FontName']) {
      $out .= 'font-family: \'' . $ctrl->Properties['FontName'] . '\'; ';
    }

    if ($value['FontUnderline'] <> $std['FontUnderline']) {
      if ($ctrl->Properties['FontUnderline'] == -1) {
        $out .= 'text-decoration: underline; ';
      } else {
        $out .= 'text-decoration: none; ';
      }
    }

    if ($value['TextAlign'] <> $std['TextAlign']) {
      $out .= 'text-align: ' . $this->_html_textalign($ctrl->Properties['TextAlign']) . '; ';
    }

    if ($value['ForeColor'] <> $std['ForeColor']) {
      $out .= 'color: ' . ExporterHTML::_html_color($ctrl->Properties['ForeColor']) . '; ';
    }
    return $out;
  }
}

/**
 *
 * @package PHPReport
 * @subpackage Exporter
 *
 */
class	TextBoxExporterHtml extends FontBoxExporterHtml
{
  function getStyle(&$ctrl, &$value, &$std)
  {
    $out = parent::getStyle($ctrl, $value, $std);

    // CanGrow
    if ($value['CanGrow'] <> $std['CanGrow']) {
      if ($std['CanGrow'] == false) {
        $out .= 'overflow: hidden; ';
#      } else {
#        $out .= 'overflow: auto; ';
      }
    }
    return $out;
  }
}

/**
 *
 * @package PHPReport
 * @subpackage Exporter
 *
 */
class	LabelExporterHtml extends FontBoxExporterHtml
{
  function getTag(&$control, $value=Null)
  {
    $value = str_replace("&&", "&", $value);
    return parent::getTag($control, $value);
  }
}

/**
 *
 * @package PHPReport
 * @subpackage Exporter
 *
 */
class SubReportExporterHtml extends ControlExporterHtml
{
}

/**
 *
 * @package PHPReport
 * @subpackage Exporter
 *
 */

class ComboBoxExporterHtml extends ControlExporterHtml
{
  function getTag(&$control, $value=Null)
  {
    $out = "<select>";
    foreach ($value as $idx => $row) {
      $out .= "  " . '<option id="' . htmlspecialchars($idx) . '">';
      $out .= htmlspecialchars($row) . "</option>\n";
    }
    $out .= "</select>\n";
    echo $out;
  }
}

/**
 *
 * @package PHPReport
 * @subpackage Exporter
 *
 */
class DummyExporterHtml extends ControlExporterHtml
{
}

?>
