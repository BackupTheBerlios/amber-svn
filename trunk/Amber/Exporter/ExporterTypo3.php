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
class ExporterTypo3 extends ExporterHtml
{
  var $type = 'typo3';

  // Report - Typo3

  function startReportSubExporter(&$report, $asSubreport = false)
  {
    $this->layout =& $report->layout;

    $css = $this->getReportCssStyles($report, $this->cssClassPrefix);
    $this->setCss($css);

    $tmp = "\n\n<!-- Start of AmberReport // -->\n\n<div class=\"AmberReport\">\n";
    $this->_base->_out($tmp);
  }

  function endReportSubExporter(&$report)
  {
    $this->_base->_out("\n</div>\n\n<!-- End of AmberReport // -->\n\n");
  }

  function setCSS($css)
  {
    $uniqueId = 'AmberReport' . mt_rand();

    // General styles
    $generalCSS = '';
    $generalCSS .= '.AmberReport div { position: absolute; overflow: hidden; }';

    $GLOBALS['TSFE']->setCSS('AmberReport', $generalCSS);

    // Styles relevant for this special report
    $GLOBALS['TSFE']->setCSS($uniqueId, $css);
  }
}


?>
