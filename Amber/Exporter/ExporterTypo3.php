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
  }

  function setCSS($css)
  {
    $uniqueId = 'AmberReport' . mt_rand();

    // General styles
    $generalCSS = ".AmberReport { position: absolute; }\n";
    $generalCSS .= '.AmberReport div { position: absolute; overflow: hidden; }';
    $GLOBALS['TSFE']->setCSS('AmberReport', $generalCSS);
    
    // Styles relevant for this special report
    $GLOBALS['TSFE']->setCSS($uniqueId, $css);
  }
}


?>
