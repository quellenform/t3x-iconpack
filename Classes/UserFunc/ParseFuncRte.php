<?php

declare(strict_types=1);

namespace Quellenform\Iconpack\UserFunc;

/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Quellenform\Iconpack\Html\IconpackHtmlParser;
use TYPO3\CMS\Core\Html\HtmlParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class ParseFuncRte
{
    /**
     * This replaces all <span> tags that contain the "data-iconfig" attribute.
     *
     * @param  string The content to be processed.
     * @param  array  TypoScript properties.
     * @return string The content with replaced icon-tags.
     */
    public function replaceIcons(string $content, array $conf): string
    {
        /** @var HtmlParser $htmlParser */
        $htmlParser = GeneralUtility::makeInstance(HtmlParser::class);
        return GeneralUtility::makeInstance(IconpackHtmlParser::class)->transformIconsForOutput(
            $content,
            $htmlParser
        );
    }
}
