<?php

/**
 *
 * @package PHPReport
 * @subpackage Exporter
 *
 */

require_once 'HTML.inc.php';

define('__SCALE__', 1);           // scaling factor. Don't use fractions or errors will occur:
                                  // fractions in border-width are not shown with current browsers
define('__HAIRLINEWIDTH__', 15);  // width of hairline in twips; here 1px


ExporterFactory::register('html', 'ExporterHtml');
ExporterFactory::register('phtml', 'ExporterHtml');

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

  var $_CtrlStdValues;

  var $_html;

  // Report - html

  function &getExporterBasicClass(&$layout, $reset)
  {
    return HTML::getInstance($layout, $reset);
  }

  function startReportSubExporter(&$report, $asSubreport = false)
  {
    $this->layout =& $report->layout;

    $tmp = '';
    if (!$this->_asSubreport) {
      $tmp = "\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html>\n<head>\n";
      $tmp .= "\t<title>" . $this->_docTitle . "</title>\n";
      $this->_base->_out($tmp);

      $css = $this->getReportCssStyles($report, $this->cssClassPrefix);
      $this->setCSS($css);

      $tmp = '';
      $tmp = "</head>\n";
      $tmp .= "<body style=\"background-color: #aaaaaa;\">\n";
      $tmp .= "\n\n<!-- Start of AmberReport // -->\n\n<div class=\"AmberReport\">\n";
      $this->_base->_out($tmp);
    } else {
      $css = $this->getReportCssStyles($report, 'sub_' . $this->cssClassPrefix);
      $this->setCSS($css);
    }
  }

  function endReportSubExporter(&$report)
  {
    if (!$this->_asSubreport) {
      $this->_base->_out("\n</div>\n\n<!-- End of AmberReport // -->\n\n");
      $this->_base->_out("</body>\n</html>\n");
    }
  }

  function comment($s)
  {
    $this->_base->_out('<!-- ' . htmlspecialchars($s) . " -->\n");
  }

  function outWindowRelative($deltaX, $x, $y, $w, $h, &$dataBuff)
  {
    $this->comment("***deltaX: $deltaX, x: $x, y: $y");
    $out = "\t<div ";
    $style1['left'] = $this->_html_twips($x);;
    $style1['top'] = $this->_html_twips($y);
    $style1['height'] = $this->_html_twips($h);
    $style1['width'] = $this->_html_twips($w);
    $style1['overflow'] = 'hidden';

    $out .=  ' style="' . $this->arrayToStyle($style1) . "\">\n";

    $out .= "\t<div ";
    $style2['left'] = $this->_html_twips(-$deltaX);
    $style2['overflow'] = 'visible';

    $out .=  ' style="' . $this->arrayToStyle($style2) . "\">\n";

    $out .= $dataBuff;

    $out .= "\t</div></div>\n";
    echo $out;
  }

  function out(&$secBuff)
  {
    echo $secBuff;
  }

  function outSectionEnd()
  {
    echo "\t</div name=\"sectionEND\">\n";
  }

  function outSectionStart($y, $w, $h, $backColor, $sectionName='')
  {
    $style['left'] = 0;
    $style['top'] = $this->_html_twips($y);
    $style['height'] = $this->_html_twips($h);
    $style['width'] = $this->_html_twips($w);
    $style['background-color'] = $this->_html_color($backColor);

    echo "\t<div  name=\"sectionStart\" style=\"" . $this->arrayToStyle($style) . "\">\n";
  }


  function startPage($paperHeight)
  {
    $style['position'] = 'relative';
    #$style['left'] = 0;
    #$style['top'] = 0;
    $style['background-color'] = "#ffffff";
    $style['height'] = $this->_html_twips($paperHeight);
    $style['width'] = $this->_html_twips($this->layout->paperWidth);
    echo "<div style=\"" . $this->arrayToStyle($style) . "\">\n";
    $this->comment('###PAGE Start###');
  }

  function endPage()
  {
    $this->comment('###PAGE End ###');
    echo "</div>\n";
    $style['position'] = 'relative';
    #$style['left'] = 0;
    #$style['top'] = 0;
    #$style['background-color'] = "#DDDDDD";
    $style['height'] = '2px';
    $style['width'] = '1px';
    echo "<div style=\"" . $this->arrayToStyle($style) . "\"> &nbsp; </div>\n";

  }



  function getReportCssStyles(&$report, $cssClassPrefix)
  {
    $this->cssClassPrefix = $cssClassPrefix;

    if (is_array($report->Controls)) {
      $css = '';
      foreach ($report->Controls as $cname => $ctrl) {
        $ctrl->_exporter->_saveStdValues($ctrl);
        $css .= $this->getCssStyle($ctrl, $cssClassPrefix) . "\n";
        $ctrl->_exporter->cssClassPrefix = $cssClassPrefix;
      }
    }
    return $css;
  }

  // Section - html

  function outSectionStart1($y, $width, $height, $backColor, $sectionName='')
  {
    $cheatWidth  = 59; // cheat: add 1.5pt to height and 3pt to width so borders get printed in Mozilla ###FIX ME
    if ($height == 0) {
      $cheatHeight = 0;
    } else {
      $cheatHeight = 15;
    }
    $out = "\t<div name=\"" . $sectionName . '-border"';

    $style = array();
    $style['top'] = $this->_html_twips($y);
    $style['height'] = $this->_html_twips($height + $cheatHeight);
    $style['left'] = '0';
    $style['width'] = $this->_html_twips($this->layout->leftMargin + $width + $this->layout->rightMargin);
    $style['background-color'] = '#ffffff';

    $out .=  ' style="' . $this->arrayToStyle($style) . "\">\n";
    $out .= "\t<div name=\"" . $sectionName . '"';

    $style = array();
    $style['position'] = 'absolute';
    $style['overflow'] = 'hidden';
    $style['height'] = $this->_html_twips($height);
    $style['left'] = $this->_html_twips($this->layout->leftMargin);
    $style['width'] = $this->_html_twips($width + $cheatWidth);
    $style['background-color'] = $this->_html_color($backColor);

    $out .=  ' style="' . $this->arrayToStyle($style) . "\">\n";

    $this->_base->_out($out);

  }

  function outSectionEnd1()
  {
    $out .= "\t</div></div>\n";
    $this->_base->_out($out);
  }

  // Page handling - html

  function printTopMargin($posY)
  {
    $out = "\t<div name=\"TopMargin\"";

    $style = array();
    $style['top'] = $this->_html_twips($posY);
    $style['height'] = $this->_html_twips($this->layout->topMargin + 20);
    $style['left'] = '0';
    $style['width'] = $this->_html_twips($this->layout->leftMargin + $this->layout->reportWidth + $this->layout->rightMargin);
    $style['background-color'] = '#ffffff';

    $out .=  ' style="' . $this->arrayToStyle($style) . "\">\n";
    $out .= "&nbsp;</div>\n";

    $this->_base->_out($out);
  }

  function printBottomMargin($posY)
  {
    $out .= "\t<div name=\"BottomMargin\"";

    $style = array();
    $style['page-break-after'] = 'always';
    $style['top'] = $this->_html_twips($posY);
    $style['height'] = $this->_html_twips($this->layout->bottomMargin + 20);
    $style['left'] = '0';
    $style['width'] = $this->_html_twips($this->layout->leftMargin + $this->layout->reportWidth + $this->layout->rightMargin);
    $style['background-color'] = '#ffffff';

    $out .=  ' style="' . $this->arrayToStyle($style) . "\">\n";
    $out .= "&nbsp;</div>\n";
    $this->_base->_out($out);
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
      $ret .= ".AmberReport div { position: absolute; overflow: hidden; }\n";
      $ret .= $css;
      $ret .= "\n-->\n</style>\n";
    } else {
      $ret = "\t<style type=\"text/css\">\n<!--\n";
      $ret .= $css;
      $ret .= "\n//-->\n</style>\n";
    }

    $this->_base->_out($ret);
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

    //return number_format(__SCALE__ * $twips / 15, 0, '.', '') . 'px';
    return number_format(__SCALE__ * $twips / 20, 2, '.', '') . 'pt';
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

  function printNormal(&$control, $content)
  {
    if (!$control->isVisible()) {
      return;
    }

    echo $this->getTag($control, $content);
  }

  function printDesign(&$control, $content)
  {
    $this->printNormal($control, $content);
  }

  function getTag(&$control, $value = null)
  {
    $cssClassName = $this->cssClassPrefix . $control->id;
    $out =  "\t\t<div class=\"" . $cssClassName . '"';

    $this->_stdValues['Value'] =  $control->Properties['Value'];
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

    $LeftPaddingHtml = 0;  // 0
    $RightPaddingHtml = 0; // 0
    $TopPaddignHtml = 0;
    $Bottompaddinghtml = 0;
    if ($value['BorderWidth'] == 0) {
      $BorderWidthHtml = __HAIRLINEWIDTH__; // 1/pt
    } else {
      $BorderWidthHtml = $value['BorderWidth'] * 20;
    }


    // Position
    if ($value['Top'] <> $std['Top']) {
      $out .= 'top: ' . ExporterHTML::_html_twips($ctrl->Properties['Top']) . '; ';
    }
    if (($value['Left'] <> $std['Left']) or ($value['BorderWidth'] <> $std['BorderWidth'])) {
      $leftHtml = $value['Left'] - 1/2 * $BorderWidthHtml - $LeftPaddingHtml + 15;
      $out .= 'left: ' . ExporterHTML::_html_twips($leftHtml) . '; ';
    }

    // Height & width
    if ($value['Height'] <> $std['Height']) {
      $out .= 'height: ' . ExporterHTML::_html_twips($ctrl->Properties['Height']) . '; ';
    }
      
    if (($value['Width'] <> $std['Width']) or ($value['BorderWidth'] <> $std['BorderWidth'])) {
      $widthHtml = $value['Width'] - $BorderWidthHtml - $LeftPaddingHtml- $RightPaddingHtml;
      $out .= 'width: ' . ExporterHTML::_html_twips($widthHtml) . '; ';
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
        $out .= 'border-width:  ' . __HAIRLINEWIDTH__ / 15 * __SCALE__ . 'px; ';
      } else {
        $out .= 'border-width: ' . $value['BorderWidth'] * __SCALE__ . 'pt; ';
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
      $out .= "font-family: '" . $ctrl->Properties['FontName'] . "'; ";
    }

    if ($value['FontUnderline'] <> $std['FontUnderline']) {
      if ($ctrl->Properties['FontUnderline'] <> 0) {
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
class TextBoxExporterHtml extends FontBoxExporterHtml
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
class LabelExporterHtml extends FontBoxExporterHtml
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

    $rep->run('html');
    $repHtml = $rep->subReportBuff;

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
