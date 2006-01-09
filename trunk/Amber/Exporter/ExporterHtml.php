<?php

/**
 *
 * @package Amber
 * @subpackage Exporter
 *
 */

require_once 'HTML.inc.php';

define('__SCALE__', 1);           // scaling factor. Don't use fractions or errors will occur:
                                  // fractions in border-width are not shown with current browsers
define('__HAIRLINEWIDTH__', 20);  // width of hairline in twips; here 1pt


ExporterFactory::register('html', 'ExporterHtml');

/**
 *
 * @package Amber
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
    $this->_base->_out('<!-- ' . htmlentities($s) . " -->\n");
  }

  function outWindowRelative($deltaX, $x, $y, $w, $h, &$dataBuff)
  {
    $out = "\t<div ";
    $style1['left'] = $this->_html_twips($x);;
    $style1['top'] = $this->_html_twips($y);
    $style1['height'] = $this->_html_twips($h + 2* $this->SectionSlip);
    $style1['width'] = $this->_html_twips($w + 2* $this->SectionSlip);
    $style1['overflow'] = 'hidden';

    $out .=  'style="' . $this->arrayToStyle($style1) . "\">\n";

    $out .= "\t<div ";
    $style2['left'] = $this->_html_twips(-$deltaX);
    $style2['overflow'] = 'visible';

    $out .=  'style="' . $this->arrayToStyle($style2) . "\">\n";

    $out .= $dataBuff;

    $out .= "\t</div></div>\n";
    $this->_base->_out($out);
  }

  function out(&$secBuff)
  {
    $this->_base->_out($secBuff);
  }

  function outSectionEnd()
  {
    $this->_base->_out("\t</div>\n");
  }

  function outSectionStart($y, $w, $h, $backColor, $sectionName='')
  {
    $style['left'] = 0;
    $style['top'] = $this->_html_twips($y);
    $style['height'] = $this->_html_twips($h + 2 * $this->SectionSlip);
    $style['width'] = $this->_html_twips($w + 2 * $this->SectionSlip);

    $this->_base->_out("\t<div style=\"" . $this->arrayToStyle($style) . "\">");

    //background Box
    if ($backColor <> 0xFFFFFF) { //not white
      $style['top'] = $this->_html_twips(2 * $this->SectionSlip);
      $style['height'] = $this->_html_twips($h);
      $style['background-color'] = $this->_html_color($backColor);
      $this->_base->_out("\t<div style=\"" . $this->arrayToStyle($style) . "\">&nbsp;</div>");
    }
    $this->_base->_out("\n");
  }


  function startPage($paperHeight)
  {
    $this->comment('###PAGE###');
    $style['position'] = 'relative';
    $style['background-color'] = "#ffffff";
    $style['height'] = $this->_html_twips($paperHeight);
    $style['width'] = $this->_html_twips($this->layout->paperWidth);
    $this->_base->_out("<div style=\"" . $this->arrayToStyle($style) . "\">\n");
  }

  function endPage()
  {
    echo "</div>\n";
    $style['position'] = 'relative';
    $style['height'] = '2pt';
    $style['width'] = '1pt';
    $this->_base->_out("<div style=\"" . $this->arrayToStyle($style) . "\"> &nbsp; </div>\n");
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

  // Page handling - html

  function printTopMargin($posY)
  {
    $out = "\t<div title=\"TopMargin\"";

    $style = array();
    $style['top'] = $this->_html_twips($posY);
    $style['height'] = $this->_html_twips($this->layout->topMargin);
    $style['left'] = '0';
    $style['width'] = $this->_html_twips($this->layout->leftMargin + $this->layout->reportWidth + $this->layout->rightMargin);
    $style['background-color'] = '#ffffff';

    $out .=  ' style="' . $this->arrayToStyle($style) . "\">\n";
    $out .= "&nbsp;</div>\n";

    $this->_base->_out($out);
  }

  function printBottomMargin($posY)
  {
    $out .= "\t<div title=\"BottomMargin\"";

    $style = array();
    $style['page-break-after'] = 'always';
    $style['top'] = $this->_html_twips($posY);
    $style['height'] = $this->_html_twips($this->layout->bottomMargin);
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
    $styleString = '';
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
      return '0pt';
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
    return $cssClassName . "\t/* " . $control->Name . ' */ { ' . $control->_exporter->getStyle($control, $nil) . '}';
  }
}

/**
 *
 * @package Amber
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
    echo $this->getTag($control, $content);
  }

  function printDesign(&$control, $content)
  {
    $this->printNormal($control, $content);
  }

  function getTag(&$control, $value = null, $encode = true)
  {
    $cssClassName = $this->cssClassPrefix . $control->id;
    $out =  "\t\t<div class=\"" . $cssClassName . '"';

    $this->_stdValues['Value'] =  $control->Properties['Value'];
    $style = $this->getStyle($control, $this->_stdValues);
    if (!empty($style)) {
      $out .= ' style="' . trim($style) . '"';
    }
    $out .= ">";

    if ($encode) {
      $out .= isset($value) ? nl2br(htmlentities($value)) : '&nbsp;';
    } else {
      $out .= isset($value) ? $value : ' ';
    }
    $out .= "</div>\n";

    return $out;
  }

  function getStyle(&$ctrl, &$std)
  {
    $out = '';
    $value =& $ctrl->Properties;
    $LeftPaddingHtml = 0;  // 0
    $RightPaddingHtml = 0; // 0
    $TopPaddingHtml = 0;
    $BottomPaddingHtml = 0;
    
    if ($value['BorderStyle'] == 0) {   // transparent border
      $BorderWidthHtml = 0;
    } elseif ($value['BorderWidth'] == 0) {
      $BorderWidthHtml = __HAIRLINEWIDTH__; // 1/pt
    } else {
      $BorderWidthHtml = $value['BorderWidth'] * 20;
    }
    
    $borderWidthHtmlChanged = (($value['BorderWidth'] <> $std['BorderWidth']) or ($value['BorderStyle'] <> $std['BorderStyle']));
    // Position
    if (($value['Top'] <> $std['Top']) or $borderWidthHtmlChanged) {
      $topHtml = $value['Top'] - 1/2 * $BorderWidthHtml - $TopPaddingHtml + $ctrl->_SectionSlip;
      $out .= 'top: ' . ExporterHTML::_html_twips($topHtml) . '; ';
    }
    if (($value['Left'] <> $std['Left']) or $borderWidthHtmlChanged) {
      $leftHtml = $value['Left'] - 1/2 * $BorderWidthHtml - $LeftPaddingHtml + $ctrl->_SectionSlip;
      $out .= 'left: ' . ExporterHTML::_html_twips($leftHtml) . '; ';
    }

    // Height & width
    if (($value['Height'] <> $std['Height']) or $borderWidthHtmlChanged) {
      $heightHtml = $value['Height'] - $BorderWidthHtml - $TopPaddingHtml - $BottomPaddingHtml;
      if ($heightHtml < 0) {
        $heightHtml = 0;
      }
      $out .= 'height: ' . ExporterHTML::_html_twips($heightHtml) . '; ';
    }

    if (($value['Width'] <> $std['Width']) or $borderWidthHtmlChanged) {
      $widthHtml = $value['Width'] - $BorderWidthHtml - $LeftPaddingHtml- $RightPaddingHtml;
      if ($widthHtml < 0) {
        $widthHtml = 0;
      }
      $out .= 'width: ' . ExporterHTML::_html_twips($widthHtml) . '; ';
    }

    // Backstyle
    if (($value['BackColor'] <> $std['BackColor']) || ($value['BackStyle'] <> $std['BackStyle'])) {
      if ($ctrl->Properties['BackStyle'] != 0) {
          $out .= 'background-color: ' . ExporterHTML::_html_color($ctrl->Properties['BackColor']) . '; ';
      }
    }

    // Border
    if (($ctrl->Properties['BorderStyle'] != 0) && ($value['BorderWidth'] <> $std['BorderWidth'])) {
      $out .= 'border: ';    
      if ($value['BorderWidth'] == 0) {
        $out .= ExporterHTML::_html_twips(__HAIRLINEWIDTH__) . ' ';
      } else {
        $out .= $value['BorderWidth'] * __SCALE__ . 'pt ';
      }
      
      if ($value['BorderColor'] <> $std['BorderColor']) {
        $out .= ExporterHTML::_html_color($ctrl->Properties['BorderColor']) . ' ';
      }
      if ($value['BorderStyle'] <> $std['BorderStyle']) {
        $out .= $this->_html_borderstyle($ctrl->Properties['BorderStyle'], $ctrl->Properties['BorderLineStyle']) . ' ';
      }
      $out .= '; ';      
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
 * @package Amber
 * @subpackage Exporter
 *
 */
class RectangleExporterHtml extends ControlExporterHtml
{
}

/**
 *
 * @package Amber
 * @subpackage Exporter
 *
 */
class FontBoxExporterHtml extends ControlExporterHtml
{
  function getStyle(&$ctrl, &$std)
  {
    $out = parent::getStyle($ctrl, $std);
    $value =& $ctrl->Properties;

    $fontProperties = array();
    
    if ($value['FontItalic'] <> $std['FontItalic']) {
      if ($ctrl->Properties['FontItalic'] == true) {
        $fontProperties['style'] = 'italic';
      } else {
        $fontProperties['style'] = 'normal';
      }
    }

    if ($value['FontWeight'] <> $std['FontWeight']) {
      if ($ctrl->Properties['FontWeight'] == 400) {
        $fontProperties['weight'] = 'normal';
      } else {
        $fontProperties['weight'] = $ctrl->Properties['FontWeight'];
      }
    }

    if ($value['FontSize'] <> $std['FontSize']) {
      $fontProperties['size'] = floor(__SCALE__ * $ctrl->Properties['FontSize']) . 'pt';
    }

    if ($value['FontName'] <> $std['FontName']) {
      $fontProperties['family'] = '"' . $ctrl->Properties['FontName'] . '"';
    }
    
    if (count($fontProperties) > 0) {
      $out .= 'font: ' . implode(' ', $fontProperties) . '; ';
    }

    if ($value['FontUnderline'] <> $std['FontUnderline']) {
      if ($ctrl->Properties['FontUnderline'] <> 0) {
        $out .= 'text-decoration: underline; ';
      } else {
        $out .= 'text-decoration: none; ';
      }
    }

    $align = $ctrl->TextAlign();
    if ($align <> $std['TextAlign']) {
      $out .= 'text-align: ' . $this->_html_textalign($align) . '; ';
    }

    if ($value['ForeColor'] <> $std['ForeColor']) {
      $out .= 'color: ' . ExporterHTML::_html_color($ctrl->Properties['ForeColor']) . '; ';
    }

    return $out;
  }
}

/**
 *
 * @package Amber
 * @subpackage Exporter
 *
 */
class TextBoxExporterHtml extends FontBoxExporterHtml
{
  function getStyle(&$ctrl, &$std)
  {
    $out = parent::getStyle($ctrl, $std);
    $value =& $ctrl->Properties;

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
 * @package Amber
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
 * @package Amber
 * @subpackage Exporter
 *
 */
class SubReportExporterHtml extends ControlExporterHtml
{
  function getTag(&$control, $value = null)
  {
    return parent::getTag($control, $value, false);
  }
}

/**
 *
 * @package Amber
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

  function getStyle(&$ctrl, &$std)
  {
    $out = parent::getStyle($ctrl, $std);

    $out .= " font-family: 'small fonts', sans-serif; ";
    $out .= ' font-size: ' . (6 * __SCALE__) . 'pt; ';
    $out .= ' font-weight: 700; ';
    $out .= ' text-align: center;';
    $out .= ' border-width: ' . (1 * __SCALE__) . 'pt;';      //border properties do exist, but Access doesn't care about them
    $out .= ' border-color:#000000;';
    $out .= ' border-style:solid;';
    return $out;
  }
}

/**
 *
 * @package Amber
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
      $out .= htmlentities($row) . "</option>\n";
    }
    $out .= "</select>\n";

    return $out;
  }
}

/**
 *
 * @package Amber
 * @subpackage Exporter
 *
 */
class DummyExporterHtml extends ControlExporterHtml
{
}

?>
