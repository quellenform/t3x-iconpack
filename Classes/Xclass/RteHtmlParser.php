<?php

declare(strict_types=1);

namespace Quellenform\Iconpack\Xclass;

/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Quellenform\Iconpack\Html\IconpackHtmlParser;
use TYPO3\CMS\Core\Html\RteHtmlParser as CoreRteHtmlParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * @extensionScannerIgnoreFile
 */
class RteHtmlParser extends CoreRteHtmlParser
{
    /**
     * Main entry point for transforming RTE content in the database so the Rich Text Editor can deal with
     * e.g. links.
     *
     * @param string $value
     * @param array $processingConfiguration
     * @return string
     */
    public function transformTextForRichTextEditor(string $value, array $processingConfiguration): string
    {
        $this->setProcessingConfiguration($processingConfiguration);
        $modes = $this->resolveAppliedTransformationModes('rte');
        $value = $this->streamlineLineBreaksForProcessing($value);
        // If an entry HTML cleaner was configured, pass the content through the HTMLcleaner
        $value = $this->runHtmlParserIfConfigured($value, 'entryHTMLparser_rte');
        // Traverse modes
        foreach ($modes as $cmd) {
            // Checking for user defined transformation:
            if (
                version_compare(VersionNumberUtility::getCurrentTypo3Version(), '12', '<') &&
                !empty($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_parsehtml_proc.php']['transformation'][$cmd])
            ) {
                trigger_error(
                    'The hook "t3lib/class.t3lib_parsehtml_proc.php->transformation"' .
                    ' will be removed in TYPO3 v12. ',
                    E_USER_DEPRECATED
                );
                $_procObj = GeneralUtility::makeInstance($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_parsehtml_proc.php']['transformation'][$cmd]);
                $_procObj->pObj = $this;
                $value = $_procObj->transform_rte($value, $this);
            } else {
                // ... else use defaults:
                switch ($cmd) {
                    case 'detectbrokenlinks':
                        $value = $this->markBrokenLinks($value);
                        break;
                    case 'css_transform':
                        $value = $this->TS_transform_rte($value);
                        break;
                    default:
                        // Do nothing
                }
            }
        }
        // Convert icon elements to HTML markup
        $value = GeneralUtility::makeInstance(IconpackHtmlParser::class)->transformIconsForOutput($value, $this);
        // If an exit HTML cleaner was configured, pass the content through the HTMLcleaner
        $value = $this->runHtmlParserIfConfigured($value, 'exitHTMLparser_rte');
        // Final clean up of linebreaks
        $value = $this->streamlineLineBreaksAfterProcessing($value);
        return $value;
    }

    /**
     * Called to process HTML content before it is stored in the database.
     *
     * @param string $value
     * @param array $processingConfiguration
     * @return string
     */
    public function transformTextForPersistence(string $value, array $processingConfiguration): string
    {
        $this->setProcessingConfiguration($processingConfiguration);
        $modes = $this->resolveAppliedTransformationModes('db');
        $value = $this->streamlineLineBreaksForProcessing($value);
        // If an entry HTML cleaner was configured, pass the content through the HTMLcleaner
        $value = $this->runHtmlParserIfConfigured($value, 'entryHTMLparser_db');
        // Convert HTML markup to icon elements
        $value = GeneralUtility::makeInstance(IconpackHtmlParser::class)->transformIconsForPersistence($value, $this);
        // Traverse modes
        foreach ($modes as $cmd) {
            // Checking for user defined transformation:
            if (
                version_compare(VersionNumberUtility::getCurrentTypo3Version(), '12', '<') &&
                !empty($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_parsehtml_proc.php']['transformation'][$cmd])
            ) {
                trigger_error(
                    'The hook "t3lib/class.t3lib_parsehtml_proc.php->transformation"' .
                    ' will be removed in TYPO3 v12. ',
                    E_USER_DEPRECATED
                );
                $_procObj = GeneralUtility::makeInstance($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_parsehtml_proc.php']['transformation'][$cmd]);
                $_procObj->pObj = $this;
                $_procObj->transformationKey = $cmd;
                $value = $_procObj->transform_db($value, $this);
            } else {
                // ... else use defaults:
                switch ($cmd) {
                    case 'detectbrokenlinks':
                        $value = $this->removeBrokenLinkMarkers($value);
                        break;
                    case 'ts_links':
                        $value = $this->TS_links_db($value);
                        break;
                    case 'css_transform':
                        // Transform empty paragraphs into spacing paragraphs
                        $value = str_replace('<p></p>', '<p>&nbsp;</p>', $value);
                        // Double any trailing spacing paragraph so that it does not get removed by divideIntoLines()
                        $value = preg_replace('/<p>&nbsp;<\/p>$/', '<p>&nbsp;</p><p>&nbsp;</p>', $value) ?? $value;
                        $value = $this->TS_transform_db($value);
                        break;
                    default:
                        // Do nothing
                }
            }
        }
        // htmlSanitize() is available since TYPO3 v11.3.2
        if (method_exists(self::class, 'htmlSanitize')) {
            // process markup with HTML Sanitizer
            $value = $this->htmlSanitize($value, $this->procOptions['HTMLparser_db.'] ?? []);
        }
        // If an exit HTML cleaner was configured, pass the content through the HTMLcleaner
        $value = $this->runHtmlParserIfConfigured($value, 'exitHTMLparser_db');
        // Final clean up of linebreaks
        $value = $this->streamlineLineBreaksAfterProcessing($value);
        return $value;
    }
}
