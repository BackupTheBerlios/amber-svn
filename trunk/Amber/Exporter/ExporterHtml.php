<?php

/**
 *
 * @package PHPReport
 * @subpackage Exporter
 *
 */

require_once 'HTML.inc.php';

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
  var $cssClassPrefix = 's';
  var $_pageNo = 1;
  var $_blankPage;

  var $_CtrlStdValues;
  var $_posY;
  
  var $_html;
  
  // Report - html

  function &getExporterBasicClass(&$layout, $reset)
  {
    return HTML::getInstance($layout, $reset);
  }  

  function startReportSubExporter(&$report, $asSubreport = false, $isDesignMode = false)
  {
    $reset = (!$asSubreport);
    $this->_html =& $this->getExporterBasicClass($report->layout, $reset);

    $tmp = '';
    if (!$this->_asSubreport) {
      $tmp = "\n<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n<html>\n<head>\n";
      $tmp .= "\t<title>" . $this->_docTitle . "</title>\n";
      $this->_html->_out($tmp);

      $css = $this->getReportCssStyles($this->_report, $this->cssClassPrefix);
      $this->setCSS($css);

      $tmp = '';
      $tmp = "</head>\n";
      $tmp .= "<body style=\"background-color: #aaaaaa;\">\n";
      $tmp .= "\n\n<!-- Start of AmberReport // -->\n\n<div class=\"AmberReport\">\n";
      $this->_html->_out($tmp);
    } else {
      $css = $this->getReportCssStyles($this->_report, 'sub_' . $this->cssClassPrefix);
      $this->setCSS($css);
    }
  }
  
  function endReportSubExporter(&$report)
  {
    if (!$this->_asSubreport) {
      $this->_html->_out("\n</div>\n\n<!-- End of AmberReport // -->\n\n");
      $this->_html->_out("</body>\n</html>\n");
    }
  }

  function getReportCssStyles(&$report, $cssClassPrefix)
  {
    $this->cssClassPrefix = $cssClassPrefix;

    if (is_array($report->Controls)) {
      $css = '';
      foreach ($report->Controls as $ctrl) {
        $ctrl->_exporter->_saveStdValues($ctrl);
        $css .= $this->getCssStyle($ctrl, $cssClassPrefix) . "\n";
        $ctrl->_exporter->cssClassPrefix = $cssClassPrefix;
      }
    }
    return $css;
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

    $out .= "\t<div name=\"" . htmlspecialchars($text) . '-Header"';

    $style = array();
    $style['top'] = $this->_html_twips($this->_posY);
    $style['height'] = $height . 'pt';
    $style['left'] = '0';
    $style['width'] = '100%';
    $style['background-color'] = '#999999';
    $style['font'] = '8pt arial';
    $style['border'] = '#000000 solid 1px';
    $style['border-top'] = '#cccccc solid 1px';
    $style['border-left'] = '#cccccc solid 1px';
    $style['margin-bottom'] = '1px';
    $style['margin-top'] = '1px';

    $out .=  ' style="' . $this->arrayToStyle($style) . "\">";
    $out .= htmlspecialchars($text);
    $out .= "</div>\n";

    $this->_html->_out($out);
    $this->_posY += ($height + 2) * 20;
  }

  function startSection(&$section, $width, &$buffer)
  {
    if (!(($section->_PagePart) or ($this->DesignMode))) {
      if ($this->_blankPage) {
        $this->_blankPage = false;

        $this->printTopMargin();  
        
        $this->_posY += $this->_report->TopMargin;
        $this->_report->_printNormalSection($this->_report->PageHeader); // FIXME: this has to be done by the Report class!!!
      }
    }
    $buffer = null;
  }

  function endSection(&$section, $height, &$buffer)
  {
    $cheatWidth  = 59; // cheat: add 1.5pt to height and 3pt to width so borders get printed in Mozilla ###FIX ME
    if ($height == 0) {
      $cheatHeight = 0;
    } else {
      $cheatHeight = 15;
    }
    if (!$this->DesignMode) {
      $out = "\t<div name=\"" . $section->Name . '-border"';

      $style = array();
      $style['top'] = $this->_html_twips($this->_posY);
      $style['height'] = $this->_html_twips($height + $cheatHeight);
      $style['left'] = '0';
      $style['width'] = $this->_html_twips($this->_report->LeftMargin + $this->_report->Width + $this->_report->RightMargin);
      $style['background-color'] = '#ffffff';

      $out .=  ' style="' . $this->arrayToStyle($style) . "\">\n";
      $out .= "\t<div name=\"" . $section->Name . '"';

      $style = array();
      $style['position'] = 'absolute';
      $style['overflow'] = 'hidden';
      $style['height'] = $this->_html_twips($height);
      $style['left'] = $this->_html_twips($this->_report->LeftMargin);
      $style['width'] = $this->_html_twips($this->_report->Width + $cheatWidth);
      $style['background-color'] = $this->_html_color($section->BackColor);

      $out .=  ' style="' . $this->arrayToStyle($style) . "\">\n";
    } else {
      $out .= "\t<div name=\"" . $section->Name . '"';

      $style = array();
      //$style['position'] = 'absolute';
      //$style['overflow'] = 'hidden';
      $style['top'] = $this->_html_twips($this->_posY);
      $style['height'] = $this->_html_twips($height + $cheatHeight);
      $style['left'] = '0';
      $style['width'] = $this->_html_twips($this->_report->Width + $cheatWidth);
      $style['background-color'] = $this->_html_color($section->BackColor);

      $out .=  ' style="' . $this->arrayToStyle($style) . "\">\n";
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
    $this->_html->_out($out);
    $this->_posY += $height;
    parent::endSection($section, $height, $buffer);
  }

  // Page handling - html

  function newPage()
  {
    if ((!$this->_blankPage) and (!$this->DesignMode)) {
      $this->_report->_printNormalSection($this->_report->PageFooter);
      
      $this->printBottomMargin();
      
      $this->_posY += $this->_report->BottomMargin;
      $this->_report->OnPage();
      $this->_pageNo++;
    }
    $this->_blankPage = true;
  }

  function printTopMargin()
  {
    $out = "\t<div name=\"TopMargin\"";

    $style = array();
    $style['top'] = $this->_html_twips($this->_posY);
    $style['height'] = $this->_html_twips($this->_report->TopMargin + 20);
    $style['left'] = '0';
    $style['width'] = $this->_html_twips($this->_report->LeftMargin + $this->_report->Width + $this->_report->RightMargin);
    $style['background-color'] = '#ffffff';

    $out .=  ' style="' . $this->arrayToStyle($style) . "\">\n";
    $out .= "&nbsp;</div>\n";

    $this->_html->_out($out);
  }
  
  function printBottomMargin()
  {
    $out .= "\t<div name=\"BottomMargin\"";

    $style = array();
    $style['page-break-after'] = 'always';
    $style['top'] = $this->_html_twips($this->_posY);
    $style['height'] = $this->_html_twips($this->_report->BottomMargin + 20);
    $style['left'] = '0';
    $style['width'] = $this->_html_twips($this->_report->LeftMargin + $this->_report->Width + $this->_report->RightMargin);
    $style['background-color'] = '#ffffff';

    $out .=  ' style="' . $this->arrayToStyle($style) . "\">\n";
    $out .= "&nbsp;</div>\n";
    $this->_html->_out($out);
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
      'checkbox' => 'CheckboxExporterHtml',
      'dummy' => 'DummyExporterHtml'
    );
    $type = strtolower(get_class($ctrl));
    if (!array_key_exists($type, $classList)) {
      $type = 'dummy';  // Null-Object for unknown Controltypes
    }
    $objName = $classList[$type];
    $ctrl->_exporter =& new $objName;
  }

  // Helper functions - html

  function setCSS($css)
  {
    if (!$this->_asSubreport) {
      $ret = "\t<style type=\"text/css\">\n<!--\n";
      if ($this->getUserAgent() == 'msie') {
        $ret .= ".AmberReport { position: absolute; }\n";
      } else {
        $ret .= ".AmberReport { position: relative; }\n";
      }
      $ret .= ".AmberReport div { position: absolute; overflow: hidden; }\n";
      $ret .= $css;
      $ret .= "\n-->\n</style>\n";
    } else {
      $ret = "\t<style type=\"text/css\">\n<!--\n";
      $ret .= $css;
      $ret .= "\n//-->\n</style>\n";
    }

    $this->_html->_out($ret);
  }

  function dump($var)
  {
    $this->_html->_out('<div style=" position: absolute; overflow: hidden; align: center; width: 90%; top: ' . $this->_html_twips($this->_posY) . '"><pre style="text-align: left; width: 80%; border: solid 1px #ff0000; font-size: 9pt; background-color: #ffffff; padding: 5px;">' . htmlentities(print_r($var, 1)) . '</pre></div>');
  }

  function arrayToStyle(&$arr)
  {
    if (is_array($arr)) {
      foreach ($arr as $key => $style) {
        $styleString .= $key . ': ' . $style .'; ';
      }
    }

    return $styleString;
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

  /**
   * @static
   * @access public
   * @return string User agent
   */
  function getUserAgent()
  {
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);

    if (strstr($agent, 'konqueror')) {
      return 'konqu';
    } elseif (strstr($agent, 'opera')) {
      return 'opera';
    } elseif (strstr($agent, 'msie'))  {
      return 'msie';
    } elseif (strstr($agent, 'mozilla')) {
      return 'moz';
    }

    return '';
  }

  // Local functions
  /**
   * @access private
   * @param obj Control
   * @param string prefix
   * @return string  The control's default properies as CSS definition
   */

  function getCssStyle(&$control, $prefix)
  {
#    $control->Properties['isVisible'] = $control->isVisible();
    $control->Properties['isVisible'] = $control->Properties['Visible'];
    $nil = array('ForeColor' => 16777216, 'BackColor' => 16777216, 'BorderColor' => 16777216, 'BorderWidth' => -9999); // illegal values
    $cssClassName = '.' . $prefix . $control->id;

    return $cssClassName . "\t/* " . $control->Name . ' */ { ' . $control->_exporter->getStyle($control, $control->Properties, $nil) . '}';
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
    if (is_array($ctrl->Properties)) {
      foreach ($ctrl->Properties as $key => $value) {
        $this->_stdValues[$key] = $ctrl->Properties[$key];
      }
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

  function getTag(&$control, $value = null)
  {
    $cssClassName = $this->cssClassPrefix . $control->id;
    $out =  "\t\t<div class=\"" . $cssClassName . '"';

    $this->_stdValues['Value'] =  $control->Properties['Value'];
    $control->Properties['isVisible'] = $control->isVisible();
    if ($control->Properties == $this->_stdValues) {

    } else {
      $style = $this->getStyle($control, $control->Properties, $this->_stdValues);
      $out .= ' style="' . trim($style) . '"';
    }
    $out .= ">";

    $out .= isset($value) ? nl2br(htmlspecialchars($value)) : '&nbsp;';
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
      // Fix IE display bug
      if (($ctrl->Properties['Height'] == 0) && (ExporterHTML::getUserAgent() == 'msie')) {
        $out .= 'height: 1px;';
      } else {
        $out .= 'height: ' . ExporterHTML::_html_twips($ctrl->Properties['Height']) . '; ';
      }
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
        $out .= 'border-width: ' . $value['BorderWidth'] . 'pt; ';
      }
    }
    if ($value['BorderColor'] <> $std['BorderColor']) {
        $out .= 'border-color: ' . ExporterHTML::_html_color($ctrl->Properties['BorderColor']) . '; ';
    }
    if ($value['BorderStyle'] <> $std['BorderStyle']) {
      $out .= 'border-style: ' . $this->_html_borderstyle($ctrl->Properties['BorderStyle'], $ctrl->Properties['BorderLineStyle']) . '; ';
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
      $out .= 'font-family: "' . $ctrl->Properties['FontName'] . '"; ';
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
  function getTag(&$control, $value = null)
  {
    $rep =& $control->_subReport;
    if (is_null($rep)) {
      $out = parent::getTag($control, $value);
      return $out;
    }

    ob_start();
    $rep->resetMargin(true);
    $rep->run('html', true);
    $repHtml = ob_get_contents();
    ob_end_clean();

    // Get tags for subreport control
    $out = parent::getTag($control, '##CONTENT##');
    
    // Insert result of subreport execution
    $out = str_replace('##CONTENT##', $repHtml, $out);
    
    return $out;
  }
}

/**
 *
 * @package PHPReport
 * @subpackage Exporter
 *
 */
class CheckBoxExporterHtml extends ControlExporterHtml
{
  function getTag(&$control, $value=Null)
  {
    $tmpCtrl = $control;

    $tmpCtrl->Width = 11 * 15;
    $tmpCtrl->Height = 11 * 15;
    $tmpCtrl->FontWeight = 700;
    $tmpCtrl->BackStyle = true;
    
    if (($value === '0') || ($value === 0)) {
      $value = '';
      $tmpCtrl->BackColor = 0xffffff;
    } elseif (is_numeric($value)) {
      $value = 'X';
      $tmpCtrl->BackColor = 0xffffff;
    } else {
      $value = '';
      $tmpCtrl->BackColor = 0xcccccc;
    }

    return parent::getTag($tmpCtrl, $value);
  }

  function getStyle(&$ctrl, &$value, &$std)
  {
    $out = parent::getStyle($ctrl, $value, $std);

    $out .= ' font-family: "small fonts", sans-serif; ';
    $out .= ' font-size: 6pt; ';
    $out .= ' font-weight: 700; ';
    $out .= ' text-align: center;';
    $out .= ' border-width: 1px;';      //border properties do exist, but Access doesn't care about them
    $out .= ' border-color:#000000;';
    $out .= ' border-style:solid;';
    return $out;
  }
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

    return $out;
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
