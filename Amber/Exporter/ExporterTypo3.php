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
    $this->_blankPage = true;

    $ret = '';
    if (is_array($report->Controls)) {
      foreach ($report->Controls as $ctrl) {
        $ctrl->_exporter->_saveStdValues($ctrl);
        $ret .= $this->getCssStyle($ctrl, $this->cssClassPrefix) . "\n";
      }
    }

    $GLOBALS['TSFE']->setCSS('AmberReport', $ret);
  }

  function endReport(&$report)
  {
    parent::endReport($report);
  }
}


?>
