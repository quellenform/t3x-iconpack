<?php

declare(strict_types=1);

namespace Quellenform\Iconpack\DataProcessing;

/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Quellenform\Iconpack\Html\IconpackHtmlParser;
use Quellenform\Iconpack\IconpackFactory;
use TYPO3\CMS\Core\Html\HtmlParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

class IconpackProcessor implements DataProcessorInterface
{

    /**
     * Preprocess fields from database-record for use in fluid-templates.
     *
     * @param ContentObjectRenderer $cObj The data of the content element or page
     * @param array $contentObjectConfiguration The configuration of Content Object
     * @param array $processorConfiguration The configuration of this processor
     * @param array $processedData Key/value store of processed data (e.g. to be passed to a Fluid View)
     * @return array the processed data as key/value store
     */
    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData): array
    {
        $fieldName = (string) $cObj->stdWrapValue('fieldName', $processorConfiguration, '');
        if (!empty($fieldName) && isset($processedData['data'][$fieldName]) && !empty($processedData['data'][$fieldName])) {
            $fieldType = (string) $cObj->stdWrapValue('fieldType', $processorConfiguration, 'native');
            switch ($fieldType) {
                case 'rte':
                    /** @var HtmlParser $htmlParser */
                    $htmlParser = GeneralUtility::makeInstance(HtmlParser::class);
                    $processedData['data'][$fieldName] =
                        GeneralUtility::makeInstance(IconpackHtmlParser::class)
                        ->transformRte($processedData['data'][$fieldName], $htmlParser);
                    break;
                case 'native':
                    $processedData['data'][$fieldName] =
                        GeneralUtility::makeInstance(IconpackFactory::class)
                        ->getIconMarkup($processedData['data'][$fieldName]);
                    break;
            }
        }

        return $processedData;
    }
}
