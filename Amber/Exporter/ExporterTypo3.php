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
    Amber::showError('info', $this->getUserAgent());
    if ($this->getUserAgent() == 'msie') {
      $generalCSS = ".AmberReport { position: absolute; }\n";
    } else {
      $generalCSS = ".AmberReport { position: relative; }\n";
    }
    $generalCSS .= '.AmberReport div { position: absolute; overflow: hidden; }';

    $GLOBALS['TSFE']->setCSS('AmberReport', $generalCSS);

    // Styles relevant for this special report
    $GLOBALS['TSFE']->setCSS($uniqueId, $css);
  }
}


?>
