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
     * Transform data on the way from RTE to DB.
     *
     * @param string $content
     * @param HtmlParser $htmlParser
     *
     * @return string
     */
    public function transformDb(string $content, HtmlParser $htmlParser): string
    {
        if (strpos($content, 'data-iconfig') === false) {
            return $content;
        } else {
            // Avoid never ending loops
            $this->transformSafecounter--;
            if ($this->transformSafecounter < 0) {
                return $content;
            }
            $blockSplit = $htmlParser->splitIntoBlock('SPAN,SVG', $content, true);
            foreach ($blockSplit as $position => $value) {
                if ($position % 2 === 0) {
                    continue;
                }
                $tag = $htmlParser->getFirstTag($value);
                $tagName = strtolower($htmlParser->getFirstTagName($value));
                $innerText = $htmlParser->removeFirstAndLastTag($value);

                // Iterate through nested SPAN-tags
                if (!empty($innerText) && $innerText !== ' ') {
                    $value = $tag . $this->transformDb(
                        $htmlParser->removeFirstAndLastTag($value),
                        $htmlParser
                    ) . '</' . $tagName . '>';
                }

                [$attributes] = $htmlParser->get_tag_attributes($htmlParser->getFirstTag($value), true);

                $iconfig = $attributes['data-iconfig'] ?? null;
                if (empty($iconfig)) {
                    // Rewrite the current block to allow nested elements
                    $blockSplit[$position] = $value;
                    continue;
                }

                /** @var IconpackFactory $iconpackFactory */
                $iconpackFactory = GeneralUtility::makeInstance(IconpackFactory::class);
                $iconElement = $iconpackFactory->getIconElement(
                    IconpackUtility::convertIconfigToArray('rte', $iconfig)
                );
                if ($iconElement) {
                    // Remove redundant attributes
                    $attributes = IconpackUtility::flattenAttributes(
                        IconpackUtility::removeDuplicateAttributes(
                            IconpackUtility::explodeAttributes($iconElement['attributes']),
                            IconpackUtility::explodeAttributes($attributes)
                        )
                    );

                    $blockSplit[$position]
                        = '<icon ' . GeneralUtility::implodeAttributes($attributes, true, true) . '></icon>';
                    // Move inner Text of SPAN-elements to next element
                    if ($tagName === 'span' && !empty($innerText) && $innerText !== ' ') {
                        $contentAfter = $blockSplit[$position + 1] ?? null;
                        if ($contentAfter) {
                            if (substr($contentAfter, 0, 6) === '&nbsp;') {
                                $blockSplit[$position + 1] = $innerText . ' ' . substr($contentAfter, 6);
                            }
                        } else {
                            $blockSplit[$position + 1] = ' ' . $innerText;
                        }
                    }
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
    public function transformRte(string $content, HtmlParser $htmlParser): string
    {
        if (strpos($content, 'data-iconfig') === false) {
            return $content;
        } else {
            /** @var IconpackFactory $iconpackFactory */
            $iconpackFactory = GeneralUtility::makeInstance(IconpackFactory::class);
            $blockSplit = $htmlParser->splitIntoBlock('ICON', $content);
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
                    $blockSplit[$position] = $iconpackFactory->getIconMarkup(
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
