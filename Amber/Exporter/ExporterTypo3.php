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

  function startReport(&$report)
  {
    parent::startReport($report, true);

    $ret = '';
    if (is_array($report->Controls)) {
      foreach ($report->Controls as $ctrl) {
        $ctrl->_exporter->_saveStdValues($ctrl);
        $ret .= $this->getCssStyle($ctrl, $this->cssClassPrefix) . "\n";
      }
    }
  }

  function endReport(&$report)
  {
    parent::endReport($report);
  }

  function setCSS($css)
  {
    $uniqueId = 'AmberReport' . mt_rand();

    $GLOBALS['TSFE']->setCSS($uniqueId, $css);
  }
}


?>
