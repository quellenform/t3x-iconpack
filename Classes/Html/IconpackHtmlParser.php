<?php

declare(strict_types=1);

namespace Quellenform\Iconpack\Html;

/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Quellenform\Iconpack\Exception\IconpackException;
use Quellenform\Iconpack\IconpackFactory;
use Quellenform\Iconpack\Utility\IconpackUtility;
use TYPO3\CMS\Core\Html\HtmlParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class IconpackHtmlParser
{
    /**
     * Run-away brake for recursive calls.
     *
     * @var int
     */
    protected $transformSafecounter = 100;

    /**
     * @var IconpackFactory
     */
    protected $iconpackFactory;

    public function __construct(
        IconpackFactory $iconpackFactory
    ) {
        $this->iconpackFactory = $iconpackFactory;
    }

    /**
     * Transform data on the way from RTE to DB.
     *
     * @param string $content
     * @param HtmlParser $htmlParser
     *
     * @return string
     */
    public function transformIconsForPersistence(string $content, HtmlParser $htmlParser): string
    {
        if (strpos($content, 'data-iconfig') === false) {
            return $content;
        } else {
            // Avoid never ending loops
            $this->transformSafecounter--;
            if ($this->transformSafecounter < 0) {
                return $content;
            }

            // Correct short opened tags in iconpack elements
            $pattern = '/(\\<img[^>]*)([ \\/]?\\>)/si';
            $replacement = '${1}></img>';
            $content = preg_replace($pattern, $replacement, $content);

            $blockSplit = $htmlParser->splitIntoBlock('SPAN,SVG,IMG', $content, true);
            foreach ($blockSplit as $position => $value) {
                if ($position % 2 === 0) {
                    continue;
                }

                [$attributes] = $htmlParser->get_tag_attributes($htmlParser->getFirstTag($value), true);

                $iconfig = $attributes['data-iconfig'] ?? null;
                if (empty($iconfig)) {
                    // Rewrite the current block to allow nested elements
                    $blockSplit[$position] = $value;
                    continue;
                }

                $iconElement = $this->iconpackFactory->getIconElement(
                    IconpackUtility::convertIconfigToArray('rte', $iconfig)
                );
                if ($iconElement) {
                    $attributes = IconpackUtility::flattenAttributes(
                        IconpackUtility::removeDuplicateAttributes(
                            IconpackUtility::explodeAttributes($iconElement['attributes']),
                            IconpackUtility::explodeAttributes(
                                IconpackUtility::filterAttributes($attributes)
                            )
                        )
                    );

                    // Unset empty alt attribute
                    // Will be added again in frontend output, but we don't need it in the DB
                    if (isset($attributes['alt']) && $attributes['alt'] === '') {
                        unset($attributes['alt']);
                    }

                    $blockSplit[$position]
                        = '<span ' . GeneralUtility::implodeAttributes($attributes, true, true) . '></span>';
                }
            }
            $this->transformSafecounter++;
            return implode('', $blockSplit);
        }
    }

    /**
     * Transform data on the way from DB to RTE/frontend.
     *
     * @param string $content
     * @param HtmlParser $htmlParser
     *
     * @return string
     */
    public function transformIconsForOutput(string $content, HtmlParser $htmlParser): string
    {
        if (strpos($content, 'data-iconfig') === false) {
            return $content;
        } else {
            $blockSplit = $htmlParser->splitIntoBlock('SPAN,ICON', $content);
            foreach ($blockSplit as $position => $value) {
                if ($position % 2 === 0) {
                    continue;
                }
                [$attributes] = $htmlParser->get_tag_attributes($htmlParser->getFirstTag($value), true);
                $iconfig = $attributes['data-iconfig'] ?? null;
                if (empty($iconfig)) {
                    continue;
                }
                try {
                    $blockSplit[$position] = $this->iconpackFactory->getIconMarkup(
                        $iconfig,
                        'rte',
                        $attributes
                    );
                } catch (IconpackException $e) {
                    $blockSplit[$position] = '';
                }
            }
            return implode('', $blockSplit);
        }
    }
}
